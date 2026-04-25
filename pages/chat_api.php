<?php
require_once '../config/db.php';

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$action = $_GET['action'] ?? '';

if($action == 'get_list') {
    $list = [];
    if($role == 'admin') {
        $q = "SELECT t.id as trx_id, p.title, b.username as buyer, s.username as seller FROM transactions t JOIN products p ON t.product_id=p.id JOIN users b ON t.buyer_id=b.id JOIN users s ON p.seller_id=s.id WHERE t.status != 'completed'";
    } elseif($role == 'seller') {
        $q = "SELECT t.id as trx_id, p.title, b.username as buyer, s.username as seller FROM transactions t JOIN products p ON t.product_id=p.id JOIN users b ON t.buyer_id=b.id JOIN users s ON p.seller_id=s.id WHERE p.seller_id='$user_id'";
    } else {
        $q = "SELECT t.id as trx_id, p.title, b.username as buyer, s.username as seller FROM transactions t JOIN products p ON t.product_id=p.id JOIN users b ON t.buyer_id=b.id JOIN users s ON p.seller_id=s.id WHERE t.buyer_id='$user_id'";
    }
    
    $res = $conn->query($q);
    while($row = $res->fetch_assoc()) {
        $title = ($role == 'admin') ? "TRX #".$row['trx_id'] : (($role == 'seller') ? "Pembeli: ".$row['buyer'] : "Toko: ".$row['seller']);
        $list[] = [
            'trx_id' => $row['trx_id'],
            'title' => $title,
            'desc' => $row['title']
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $list]);
    exit;
}

if($action == 'get_messages') {
    $trx_id = $conn->real_escape_string($_GET['trx_id'] ?? '');
    $res = $conn->query("SELECT * FROM chat_messages WHERE transaction_id='$trx_id' ORDER BY created_at ASC");
    $messages = [];
    while($row = $res->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_role' => $row['sender_role'],
            'sender_name' => $row['sender_name'],
            'message' => $row['message'],
            'time' => date('H:i', strtotime($row['created_at']))
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $messages, 'trx_id' => $trx_id]);
    exit;
}

if($action == 'send_message' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $trx_id = $conn->real_escape_string($input['trx_id'] ?? '');
    $message = trim($input['message'] ?? '');
    $sender_name = $_SESSION['username'];
    if(!empty($message) && !empty($trx_id)) {
        $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, ?, ?, ?)");
        $ins->bind_param("ssss", $trx_id, $role, $sender_name, $message);
        if($ins->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Empty message or trx']);
    }
    exit;
}
if($action == 'handle_action' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $trx_id = $conn->real_escape_string($input['trx_id'] ?? '');
    $type = $input['type'] ?? '';
    $username = $_SESSION['username'];
    
    // Fetch transaction details
    $res = $conn->query("SELECT t.*, s.username as seller_name FROM transactions t JOIN products p ON t.product_id=p.id JOIN users s ON p.seller_id=s.id WHERE t.id='$trx_id'");
    $trx = $res->fetch_assoc();

    if(!$trx) {
        echo json_encode(['status' => 'error', 'msg' => 'Transaction not found']);
        exit;
    }

    if($type == 'upload_proof' && $role == 'buyer') {
        $msg = "[$username telah mengunggah bukti transfer]\nMenunggu Admin memverifikasi dana.";
        $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'system', 'TavernEx Bot', ?)");
        $ins->bind_param("ss", $trx_id, $msg);
        $ins->execute();
        echo json_encode(['status' => 'success']);
    } 
    elseif($type == 'verify_payment' && $role == 'admin') {
        $conn->query("UPDATE transactions SET status='processing' WHERE id='$trx_id'");
        $msg = "[DANA TERVERIFIKASI]\nDana sudah masuk ke rekening TavernEx. Kepada Penjual (".$trx['seller_name']."), silakan berikan data email & password akun ke grup ini.";
        $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'admin', ?, ?)");
        $ins->bind_param("sss", $trx_id, $username, $msg);
        $ins->execute();
        echo json_encode(['status' => 'success']);
    }
    elseif($type == 'finish_transaction' && $role == 'admin') {
        $conn->query("UPDATE transactions SET status='completed' WHERE id='$trx_id'");
        // Increment sold_count and decrease stock (if not unlimited)
        $conn->query("UPDATE products p JOIN transactions t ON p.id=t.product_id 
                      SET p.sold_count = p.sold_count + 1, 
                          p.stock = IF(p.stock > 0, p.stock - 1, p.stock) 
                      WHERE t.id='$trx_id'");
        
        $msg = "TRANSAKSI SELESAI!\n\nPembeli telah mengamankan akun. Dana telah diteruskan ke saldo penjual.";
        $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'system', 'TavernEx Bot', ?)");
        $ins->bind_param("ss", $trx_id, $msg);
        $ins->execute();
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Unauthorized or invalid action']);
    }
    exit;
}

if($action == 'submit_delivery' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $trx_id = $conn->real_escape_string($_POST['trx_id'] ?? '');
    $sensitive = $conn->real_escape_string($_POST['sensitive_data'] ?? '');
    $username = $_SESSION['username'];

    if(!empty($_FILES['delivery_image']['name'])) {
        $target_dir = "../uploads/proofs/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = pathinfo($_FILES["delivery_image"]["name"], PATHINFO_EXTENSION);
        $file_name = "delivery_" . $trx_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $file_name;
        
        if(move_uploaded_file($_FILES["delivery_image"]["tmp_name"], $target_file)) {
            $proof_url = "uploads/proofs/" . $file_name;
            
            $conn->query("UPDATE transactions SET delivery_proof='$proof_url', sensitive_data='$sensitive' WHERE id='$trx_id'");
            
            $msg = "[$username telah mengirimkan pesanan]\nMenunggu Admin memverifikasi bukti pengiriman.";
            $ins = $conn->prepare("INSERT INTO chat_messages (transaction_id, sender_role, sender_name, message) VALUES (?, 'system', 'TavernEx Bot', ?)");
            $ins->bind_param("ss", $trx_id, $msg);
            $ins->execute();
            
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Gagal mengunggah gambar.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Bukti pengiriman wajib diisi.']);
    }
    exit;
}

