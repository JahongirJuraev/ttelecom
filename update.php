<?php
// update.php — Обновить объект
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit;
}
require_once 'config.php';

$id            = (int)($_POST['id'] ?? 0);
$allowed_types = ['mufta','client','damage','other'];
$allowed_inet  = ['gpon','utp','ip','нет'];
$type          = in_array($_POST['type']??'', $allowed_types) ? $_POST['type'] : 'other';
$address       = trim($_POST['address'] ?? '');
$internet_type = in_array($_POST['internet_type']??'', $allowed_inet) ? $_POST['internet_type'] : 'нет';
$custom_date   = !empty($_POST['custom_date']) ? $_POST['custom_date'] : null;
$lat           = (float)($_POST['lat'] ?? 0);
$lng           = (float)($_POST['lng'] ?? 0);
$del_photo     = ($_POST['delete_photo'] ?? '') === '1';

if (!$id || !$address) {
    echo json_encode(['success'=>false,'error'=>'id and address required']); exit;
}

$cur   = $conn->query("SELECT photo FROM objects WHERE id=$id")->fetch_assoc();
$photo = $cur['photo'] ?? null;

if ($del_photo && $photo) {
    $fp = __DIR__.'/'.$photo;
    if (file_exists($fp)) unlink($fp);
    $photo = null;
}

if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    if ($photo) { $fp=__DIR__.'/'.$photo; if(file_exists($fp)) unlink($fp); }
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $filename = uniqid('obj_', true).'.'.$ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir.$filename)) {
            $photo = 'uploads/'.$filename;
        }
    }
}

$stmt = $conn->prepare(
    "UPDATE objects SET type=?,address=?,internet_type=?,custom_date=?,lat=?,lng=?,photo=? WHERE id=?"
);
$stmt->bind_param('ssssddsi', $type,$address,$internet_type,$custom_date,$lat,$lng,$photo,$id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'photo'=>$photo]);
} else {
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
$conn->close();
