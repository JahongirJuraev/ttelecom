<?php
// trace.php — Трассировка сигнала
require_once 'config.php';

$fiber_id=(int)($_GET['fiber_id']??0);
if (!$fiber_id) { echo json_encode(['success'=>false,'error'=>'fiber_id required']); exit; }

$visited=[];$path=[];

function traceFiber($fid,&$conn,&$visited,&$path,$depth=0) {
    if ($depth>100||in_array($fid,$visited)) return;
    $visited[]=$fid;
    $fiber=$conn->query("SELECT f.*,m.color AS module_color,m.color_hex AS module_hex,m.number AS module_number,c.name AS cable_name,c.joint_id,j.name AS joint_name,j.lat,j.lng FROM fibers f JOIN modules m ON m.id=f.module_id JOIN cables c ON c.id=m.cable_id JOIN joints j ON j.id=c.joint_id WHERE f.id=$fid")->fetch_assoc();
    if (!$fiber) return;
    $sideInfo=$conn->query("SELECT s.name AS side_name,s.linked_joint_id FROM side_fibers sf JOIN sides s ON s.id=sf.side_id WHERE sf.fiber_id=$fid")->fetch_assoc();
    $path[]=['fiber_id'=>(int)$fiber['id'],'fiber_number'=>(int)$fiber['number'],'fiber_color'=>$fiber['color'],'fiber_hex'=>$fiber['color_hex'],'fiber_status'=>$fiber['status'],'fiber_notes'=>$fiber['notes'],'module_number'=>(int)$fiber['module_number'],'module_color'=>$fiber['module_color'],'module_hex'=>$fiber['module_hex'],'cable_name'=>$fiber['cable_name'],'joint_id'=>(int)$fiber['joint_id'],'joint_name'=>$fiber['joint_name'],'lat'=>(float)$fiber['lat'],'lng'=>(float)$fiber['lng'],'side_name'=>$sideInfo['side_name']??null,'linked_joint'=>$sideInfo['linked_joint_id']?(int)$sideInfo['linked_joint_id']:null];
    $connRow=$conn->query("SELECT * FROM connections WHERE fiber_id_in=$fid OR fiber_id_out=$fid")->fetch_assoc();
    if ($connRow) {
        $next=($connRow['fiber_id_in']==$fid)?(int)$connRow['fiber_id_out']:(int)$connRow['fiber_id_in'];
        traceFiber($next,$conn,$visited,$path,$depth+1);
    }
}

traceFiber($fiber_id,$conn,$visited,$path);
echo json_encode(['success'=>true,'steps'=>count($path),'path'=>$path]);
$conn->close();
