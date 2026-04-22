<?php
// getJoint.php — Полная схема одной муфты
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'error'=>'id required']); exit; }

$j = $conn->query("SELECT * FROM joints WHERE id=$id")->fetch_assoc();
if (!$j) { echo json_encode(['success'=>false,'error'=>'Not found']); exit; }
$j['lat'] = (float)$j['lat'];
$j['lng'] = (float)$j['lng'];

// Стороны
$sides = [];
$sRes = $conn->query("SELECT s.*,j2.name AS linked_joint_name FROM sides s LEFT JOIN joints j2 ON j2.id=s.linked_joint_id WHERE s.joint_id=$id ORDER BY s.id");
while ($side = $sRes->fetch_assoc()) {
    $side['id'] = (int)$side['id'];
    $fRes = $conn->query("SELECT f.*,sf.id AS sf_id FROM side_fibers sf JOIN fibers f ON f.id=sf.fiber_id WHERE sf.side_id={$side['id']} ORDER BY f.cable_id,f.module_id,f.number");
    $side['fibers'] = [];
    while ($f=$fRes->fetch_assoc()) {
        $f['id']=(int)$f['id'];$f['module_id']=(int)$f['module_id'];$f['cable_id']=(int)$f['cable_id'];$f['number']=(int)$f['number'];
        $side['fibers'][]=$f;
    }
    $sides[]=$side;
}
$j['sides']=$sides;

// Кабели → модули → жилы
$cables=[];
$cRes=$conn->query("SELECT * FROM cables WHERE joint_id=$id ORDER BY id");
while ($cable=$cRes->fetch_assoc()) {
    $cable['id']=(int)$cable['id'];$cable['fiber_count']=(int)$cable['fiber_count'];
    $stat=$conn->query("SELECT SUM(f.status='free') AS free,SUM(f.status='used') AS used,SUM(f.status='damaged') AS damaged,COUNT(*) AS total FROM fibers f JOIN modules m ON m.id=f.module_id WHERE m.cable_id={$cable['id']}")->fetch_assoc();
    $cable['stats']=['free'=>(int)$stat['free'],'used'=>(int)$stat['used'],'damaged'=>(int)$stat['damaged'],'total'=>(int)$stat['total']];
    $modules=[];
    $mRes=$conn->query("SELECT * FROM modules WHERE cable_id={$cable['id']} ORDER BY number");
    while ($mod=$mRes->fetch_assoc()) {
        $mod['id']=(int)$mod['id'];$mod['number']=(int)$mod['number'];
        $fRes=$conn->query("SELECT f.*,(SELECT sf.side_id FROM side_fibers sf WHERE sf.fiber_id=f.id LIMIT 1) AS side_id FROM fibers f WHERE f.module_id={$mod['id']} ORDER BY f.number");
        $fibers=[];
        while ($f=$fRes->fetch_assoc()) {
            $f['id']=(int)$f['id'];$f['number']=(int)$f['number'];$f['side_id']=$f['side_id']?(int)$f['side_id']:null;
            $fibers[]=$f;
        }
        $mod['fibers']=$fibers;$modules[]=$mod;
    }
    $cable['modules']=$modules;$cables[]=$cable;
}
$j['cables']=$cables;

// Соединения
$conns=[];
$connRes=$conn->query("SELECT c.*,fi.color_hex AS color_in,fo.color_hex AS color_out FROM connections c JOIN fibers fi ON fi.id=c.fiber_id_in JOIN fibers fo ON fo.id=c.fiber_id_out WHERE c.joint_id=$id");
while ($r=$connRes->fetch_assoc()) {
    $r['id']=(int)$r['id'];$r['fiber_id_in']=(int)$r['fiber_id_in'];$r['fiber_id_out']=(int)$r['fiber_id_out'];
    $conns[]=$r;
}
$j['connections']=$conns;

// Все муфты для связи
$all=[];
$aRes=$conn->query("SELECT id,name FROM joints WHERE id!=$id ORDER BY name");
while ($a=$aRes->fetch_assoc()) $all[]=$a;
$j['all_joints']=$all;

echo json_encode(['success'=>true,'data'=>$j]);
$conn->close();
