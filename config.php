<?php
// config.php — БД + TIA-598 константы
// Все PHP файлы лежат в корне — подключают этот файл напрямую

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'telecom_map');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success'=>false,'error'=>'DB: '.$conn->connect_error]));
}
$conn->set_charset('utf8mb4');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// TIA-598 цвета трубок модулей
define('MODULE_COLORS', [
    1  => ['name'=>'Синий',      'hex'=>'#1e90ff'],
    2  => ['name'=>'Оранжевый',  'hex'=>'#ff8c00'],
    3  => ['name'=>'Зелёный',    'hex'=>'#22c55e'],
    4  => ['name'=>'Коричневый', 'hex'=>'#8b4513'],
    5  => ['name'=>'Серый',      'hex'=>'#9ca3af'],
    6  => ['name'=>'Белый',      'hex'=>'#e5e7eb'],
    7  => ['name'=>'Красный',    'hex'=>'#ef4444'],
    8  => ['name'=>'Чёрный',     'hex'=>'#1f2937'],
    9  => ['name'=>'Жёлтый',     'hex'=>'#eab308'],
    10 => ['name'=>'Фиолетовый', 'hex'=>'#8b5cf6'],
    11 => ['name'=>'Розовый',    'hex'=>'#ec4899'],
    12 => ['name'=>'Голубой',    'hex'=>'#06b6d4'],
]);

// TIA-598 цвета жил (8 штук в модуле)
define('FIBER_COLORS', [
    1 => ['name'=>'Синий',      'hex'=>'#1e90ff'],
    2 => ['name'=>'Оранжевый',  'hex'=>'#ff8c00'],
    3 => ['name'=>'Зелёный',    'hex'=>'#22c55e'],
    4 => ['name'=>'Коричневый', 'hex'=>'#8b4513'],
    5 => ['name'=>'Серый',      'hex'=>'#9ca3af'],
    6 => ['name'=>'Белый',      'hex'=>'#e5e7eb'],
    7 => ['name'=>'Красный',    'hex'=>'#ef4444'],
    8 => ['name'=>'Чёрный',     'hex'=>'#1f2937'],
]);

function getModuleCount(int $fiberCount): int {
    return (int)($fiberCount / 8);
}
