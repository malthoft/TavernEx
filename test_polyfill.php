<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli('localhost', 'root', '', 'tavernex_db');

function stmt_get_all($stmt) {
    $meta = $stmt->result_metadata();
    if (!$meta) return [];
    
    $fields = [];
    $row = [];
    while ($field = $meta->fetch_field()) {
        $fields[] = $field->name;
        $row[$field->name] = null;
    }
    
    $refs = [];
    foreach ($fields as $f) {
        $refs[] = &$row[$f];
    }
    
    call_user_func_array([$stmt, 'bind_result'], $refs);
    
    $results = [];
    while ($stmt->fetch()) {
        $copy = [];
        foreach ($fields as $f) {
            $copy[$f] = $row[$f];
        }
        $results[] = $copy;
    }
    
    $meta->free();
    return $results;
}

$stmt = $conn->prepare("SELECT id, username FROM users LIMIT 2");
$stmt->execute();
$res = stmt_get_all($stmt);
print_r($res);
