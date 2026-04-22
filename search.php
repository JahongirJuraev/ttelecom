<?php
// search.php — Поиск по адресу
require_once 'config.php';

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode(['success'=>true,'data'=>[]]);
    exit;
}

$like = '%' . $conn->real_escape_string($q) . '%';
$result = $conn->query(
    "SELECT id, type, address, internet_type,
            DATE_FORMAT(custom_date, '%d.%m.%Y') AS custom_date,
            lat, lng, photo
     FROM objects
     WHERE address LIKE '$like'
     ORDER BY created_at DESC
     LIMIT 20"
);

$objects = [];
while ($row = $result->fetch_assoc()) {
    $row['lat'] = (float)$row['lat'];
    $row['lng'] = (float)$row['lng'];
    $objects[]  = $row;
}
echo json_encode(['success'=>true,'data'=>$objects]);
$conn->close();
