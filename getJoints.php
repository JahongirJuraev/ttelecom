<?php
// getJoints.php — Все муфты для карты
require_once 'config.php';

$sql = "
    SELECT j.id, j.name, j.lat, j.lng, j.address,
        COUNT(DISTINCT c.id)    AS cable_count,
        COUNT(DISTINCT f.id)    AS fiber_total,
        SUM(f.status='free')    AS fiber_free,
        SUM(f.status='used')    AS fiber_used,
        SUM(f.status='damaged') AS fiber_damaged
    FROM joints j
    LEFT JOIN cables  c ON c.joint_id = j.id
    LEFT JOIN modules m ON m.cable_id = c.id
    LEFT JOIN fibers  f ON f.module_id = m.id
    GROUP BY j.id
    ORDER BY j.created_at DESC
";
$result = $conn->query($sql);
$joints = [];
while ($row = $result->fetch_assoc()) {
    $row['lat']           = (float)$row['lat'];
    $row['lng']           = (float)$row['lng'];
    $row['cable_count']   = (int)$row['cable_count'];
    $row['fiber_total']   = (int)$row['fiber_total'];
    $row['fiber_free']    = (int)$row['fiber_free'];
    $row['fiber_used']    = (int)$row['fiber_used'];
    $row['fiber_damaged'] = (int)$row['fiber_damaged'];
    $joints[] = $row;
}
echo json_encode(['success'=>true,'data'=>$joints]);
$conn->close();
