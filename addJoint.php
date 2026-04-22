<?php
// addJoint.php — Добавить муфту сети
session_start();
if (empty($_SESSION['logged_in'])) { http_response_code(401); echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
require_once 'config.php';

$d    = json_decode(file_get_contents('php://input'),true) ?: $_POST;
$name = trim($d['name'] ?? '');
$lat  = (float)($d['lat'] ?? 0);
$lng  = (float)($d['lng'] ?? 0);
$addr = trim($d['address'] ?? '');

if (!$name || !$lat || !$lng) { echo json_encode(['success'=>false,'error'=>'name,lat,lng required']); exit; }

$stmt = $conn->prepare("INSERT INTO joints (name,lat,lng,address) VALUES (?,?,?,?)");
$stmt->bind_param('sdds', $name,$lat,$lng,$addr);
echo $stmt->execute()
    ? json_encode(['success'=>true,'id'=>$conn->insert_id])
    : json_encode(['success'=>false,'error'=>$stmt->error]);
