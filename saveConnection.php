<?php
// saveConnection.php — Создать / удалить соединение жил
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d      = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$action = $d['action'] ?? 'save';

if ($action === 'save') {
    $joint_id=(int)($d['joint_id']??0);$fIn=(int)($d['fiber_id_in']??0);$fOut=(int)($d['fiber_id_out']??0);
    if (!$joint_id||!$fIn||!$fOut) { echo json_encode(['success'=>false,'error'=>'joint_id,fiber_id_in,fiber_id_out required']); exit; }
    if ($fIn===$fOut) { echo json_encode(['success'=>false,'error'=>'Cannot connect fiber to itself']); exit; }
    $exists=$conn->query("SELECT id FROM connections WHERE (fiber_id_in=$fIn OR fiber_id_out=$fIn OR fiber_id_in=$fOut OR fiber_id_out=$fOut) AND joint_id=$joint_id")->fetch_assoc();
    if ($exists) { echo json_encode(['success'=>false,'error'=>'Одна из жил уже соединена']); exit; }
    $stmt=$conn->prepare("INSERT INTO connections (joint_id,fiber_id_in,fiber_id_out) VALUES (?,?,?)");
    $stmt->bind_param('iii',$joint_id,$fIn,$fOut);
    if ($stmt->execute()) {
        $conn->query("UPDATE fibers SET status='used' WHERE id IN ($fIn,$fOut)");
        echo json_encode(['success'=>true,'id'=>$conn->insert_id]);
    } else echo json_encode(['success'=>false,'error'=>$stmt->error]);
    exit;
}
if ($action === 'delete') {
    $id=(int)($d['id']??0);
    if (!$id) { echo json_encode(['success'=>false,'error'=>'id required']); exit; }
    $row=$conn->query("SELECT * FROM connections WHERE id=$id")->fetch_assoc();
    if (!$row) { echo json_encode(['success'=>false,'error'=>'Not found']); exit; }
    $conn->query("DELETE FROM connections WHERE id=$id");
    foreach ([$row['fiber_id_in'],$row['fiber_id_out']] as $fid) {
        $inConn=$conn->query("SELECT id FROM connections WHERE fiber_id_in=$fid OR fiber_id_out=$fid")->num_rows;
        $inSide=$conn->query("SELECT id FROM side_fibers WHERE fiber_id=$fid")->num_rows;
        if (!$inConn&&!$inSide) $conn->query("UPDATE fibers SET status='free' WHERE id=$fid");
    }
    echo json_encode(['success'=>true]);
    exit;
}
echo json_encode(['success'=>false,'error'=>'Unknown action']);
