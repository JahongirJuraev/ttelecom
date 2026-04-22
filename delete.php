<?php
// delete.php — Удалить объект
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'error'=>'id required']); exit; }

// Удаляем фото если есть
$row = $conn->query("SELECT photo FROM objects WHERE id=$id")->fetch_assoc();
if ($row && $row['photo'] && file_exists(__DIR__.'/'.$row['photo'])) {
    unlink(__DIR__.'/'.$row['photo']);
}

$stmt = $conn->prepare("DELETE FROM objects WHERE id=?");
$stmt->bind_param('i', $id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Not found']);
}
$conn->close();
