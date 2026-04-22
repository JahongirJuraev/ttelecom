<?php
// updateFiber.php — Обновить статус и заметки жилы
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d      = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$id     = (int)($d['id']??0);
$notes  = trim($d['notes']??'');
$status = $d['status']??null;

if (!$id) { echo json_encode(['success'=>false,'error'=>'id required']); exit; }
if ($status&&!in_array($status,['free','used','damaged'])) { echo json_encode(['success'=>false,'error'=>'Invalid status']); exit; }

if ($status) {
    $stmt=$conn->prepare("UPDATE fibers SET notes=?,status=? WHERE id=?");
    $stmt->bind_param('ssi',$notes,$status,$id);
} else {
    $stmt=$conn->prepare("UPDATE fibers SET notes=? WHERE id=?");
    $stmt->bind_param('si',$notes,$id);
}
echo $stmt->execute() ? json_encode(['success'=>true]) : json_encode(['success'=>false,'error'=>$stmt->error]);
$conn->close();
