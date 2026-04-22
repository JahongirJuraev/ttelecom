<?php
// assignFibers.php — Назначить жилы на сторону
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d         = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$side_id   = (int)($d['side_id']??0);
$fiber_ids = $d['fiber_ids'] ?? [];
if (!$side_id||empty($fiber_ids)) { echo json_encode(['success'=>false,'error'=>'side_id and fiber_ids required']); exit; }

$side = $conn->query("SELECT joint_id FROM sides WHERE id=$side_id")->fetch_assoc();
if (!$side) { echo json_encode(['success'=>false,'error'=>'Side not found']); exit; }
$joint_id = (int)$side['joint_id'];

$conn->begin_transaction();
try {
    $assigned=0;
    foreach ($fiber_ids as $fid) {
        $fid=(int)$fid;
        $check=$conn->query("SELECT sf.side_id FROM side_fibers sf WHERE sf.fiber_id=$fid")->fetch_assoc();
        if ($check&&$check['side_id']!=$side_id) continue;
        $stmt=$conn->prepare("INSERT IGNORE INTO side_fibers (side_id,fiber_id) VALUES (?,?)");
        $stmt->bind_param('ii',$side_id,$fid);
        $stmt->execute();
        $conn->query("UPDATE fibers SET status='used' WHERE id=$fid AND status='free'");
        $assigned++;
    }
    $conn->commit();
    echo json_encode(['success'=>true,'assigned'=>$assigned]);
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
