<?php
// add.php — Добавить объект (с фото)
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit;
}

$allowed_types = ['mufta','client','damage','other'];
$allowed_inet  = ['gpon','utp','ip','нет'];

$type          = in_array($_POST['type']??'', $allowed_types) ? $_POST['type'] : 'other';
$address       = trim($_POST['address'] ?? '');
$internet_type = in_array($_POST['internet_type']??'', $allowed_inet) ? $_POST['internet_type'] : 'нет';
$custom_date   = !empty($_POST['custom_date']) ? $_POST['custom_date'] : null;
$lat           = (float)($_POST['lat'] ?? 0);
$lng           = (float)($_POST['lng'] ?? 0);

if (!$address) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'address required']); exit;
}

// Фото
$photo = null;
if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $filename = uniqid('obj_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir.$filename)) {
            $photo = 'uploads/' . $filename;
        }
    }
}

$stmt = $conn->prepare(
    "INSERT INTO objects (type, address, internet_type, custom_date, lat, lng, photo)
     VALUES (?,?,?,?,?,?,?)"
);
$stmt->bind_param('ssssdds', $type, $address, $internet_type, $custom_date, $lat, $lng, $photo);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'id'=>$conn->insert_id,'photo'=>$photo]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
$conn->close();
