<?php
// get.php — Все объекты карты
require_once 'config.php';

$result = $conn->query(
    "SELECT id, type, address, internet_type,
            DATE_FORMAT(custom_date, '%d.%m.%Y') AS custom_date,
            lat, lng, photo
     FROM objects
     ORDER BY created_at DESC"
);

$objects = [];
while ($row = $result->fetch_assoc()) {
    $row['lat'] = (float)$row['lat'];
    $row['lng'] = (float)$row['lng'];
    $objects[]  = $row;
}
echo json_encode(['success'=>true,'data'=>$objects]);
$conn->close();
