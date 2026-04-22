<?php
// addCable.php — Добавить кабель (авто: модули + жилы)
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d           = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$joint_id    = (int)($d['joint_id']??0);
$name        = trim($d['name']??'');
$fiber_count = (int)($d['fiber_count']??48);

if (!$joint_id||!$name) { echo json_encode(['success'=>false,'error'=>'joint_id and name required']); exit; }
if (!in_array($fiber_count,[8,16,24,48,96])) $fiber_count=48;

$module_count  = getModuleCount($fiber_count);
$module_colors = MODULE_COLORS;
$fiber_colors  = FIBER_COLORS;

$conn->begin_transaction();
try {
    $stmt=$conn->prepare("INSERT INTO cables (joint_id,name,fiber_count) VALUES (?,?,?)");
    $stmt->bind_param('isi',$joint_id,$name,$fiber_count);
    $stmt->execute();
    $cable_id=$conn->insert_id;
    $created_modules=[];

    for ($m=1;$m<=$module_count;$m++) {
        $mc    = $module_colors[$m] ?? $module_colors[($m%12)?:12];
        $mName = "Модуль $m";
        $stmt=$conn->prepare("INSERT INTO modules (cable_id,number,color,color_hex,name) VALUES (?,?,?,?,?)");
        $stmt->bind_param('iisss',$cable_id,$m,$mc['name'],$mc['hex'],$mName);
        $stmt->execute();
        $module_id=$conn->insert_id;
        $mod_fibers=[];

        for ($f=1;$f<=8;$f++) {
            $fc=$fiber_colors[$f];
            $status='free';
            $stmt=$conn->prepare("INSERT INTO fibers (module_id,cable_id,number,color,color_hex,status) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('iiisss',$module_id,$cable_id,$f,$fc['name'],$fc['hex'],$status);
            $stmt->execute();
            $mod_fibers[]=['id'=>$conn->insert_id,'number'=>$f,'color'=>$fc['name'],'color_hex'=>$fc['hex'],'status'=>'free'];
        }
        $created_modules[]=['id'=>$module_id,'number'=>$m,'color'=>$mc['name'],'color_hex'=>$mc['hex'],'name'=>$mName,'fibers'=>$mod_fibers];
    }
    $conn->commit();
    echo json_encode(['success'=>true,'cable_id'=>$cable_id,'module_count'=>$module_count,'fiber_count'=>$fiber_count,'modules'=>$created_modules]);
} catch(Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
