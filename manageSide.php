<?php
// manageSide.php — Управление сторонами муфты
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d      = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$action = $d['action'] ?? '';

if ($action === 'add') {
    $joint_id = (int)($d['joint_id']??0);
    $name     = trim($d['name']??'');
    $linked   = !empty($d['linked_joint_id']) ? (int)$d['linked_joint_id'] : null;
    if (!$joint_id||!$name) { echo json_encode(['success'=>false,'error'=>'joint_id and name required']); exit; }
    $angle = 0;
    $stmt = $conn->prepare("INSERT INTO sides (joint_id,name,linked_joint_id,position_angle) VALUES (?,?,?,?)");
    $stmt->bind_param('isis',$joint_id,$name,$linked,$angle);
    echo $stmt->execute() ? json_encode(['success'=>true,'id'=>$conn->insert_id]) : json_encode(['success'=>false,'error'=>$stmt->error]);
    exit;
}
if ($action === 'edit') {
    $id     = (int)($d['id']??0);
    $name   = trim($d['name']??'');
    $linked = !empty($d['linked_joint_id']) ? (int)$d['linked_joint_id'] : null;
    if (!$id||!$name) { echo json_encode(['success'=>false,'error'=>'id and name required']); exit; }
    $stmt = $conn->prepare("UPDATE sides SET name=?,linked_joint_id=? WHERE id=?");
    $stmt->bind_param('sii',$name,$linked,$id);
    echo $stmt->execute() ? json_encode(['success'=>true]) : json_encode(['success'=>false,'error'=>$stmt->error]);
    exit;
}
if ($action === 'delete') {
    $id = (int)($d['id']??0);
    if (!$id) { echo json_encode(['success'=>false,'error'=>'id required']); exit; }
    $stmt = $conn->prepare("DELETE FROM sides WHERE id=?");
    $stmt->bind_param('i',$id);
    echo $stmt->execute() ? json_encode(['success'=>true]) : json_encode(['success'=>false,'error'=>$stmt->error]);
    exit;
}
echo json_encode(['success'=>false,'error'=>'Unknown action']);
