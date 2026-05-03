<?php
require 'config/db.php';

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$type || !$id) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$table = '';
if ($type === 'product') {
    $table = 'products';
} elseif ($type === 'category') {
    $table = 'categories';
} elseif ($type === 'user') {
    $table = 'users';
} else {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$stmt = $conn->prepare("SELECT image_url FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = stmt_fetch_assoc($stmt);

if (!$row || empty($row['image_url'])) {
    // Serve a transparent pixel or 404
    header("HTTP/1.0 404 Not Found");
    exit;
}

$image_url = $row['image_url'];

// Check if it's base64
if (strpos($image_url, 'data:') === 0) {
    // Extract mime type and base64 data
    // Format: data:image/jpeg;base64,....
    $parts = explode(';', $image_url);
    if (count($parts) >= 2) {
        $mime_part = explode(':', $parts[0]);
        $mime = $mime_part[1] ?? 'image/jpeg';
        
        if (strpos($mime, 'image/') !== 0) {
            $mime = 'image/jpeg';
        }
        
        $data_part = explode(',', $parts[1]);
        $base64_data = $data_part[1] ?? '';
        
        $binary = base64_decode($base64_data);
        
        header("Content-Type: $mime");
        header("Cache-Control: public, max-age=86400"); // Cache for 1 day
        echo $binary;
        exit;
    }
}

// If it's a relative URL, redirect to it
if (strpos($image_url, 'http') !== 0) {
    header("Location: " . $image_url);
    exit;
}

header("Location: " . $image_url);
exit;
