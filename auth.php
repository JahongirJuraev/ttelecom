<?php
// auth.php — Проверка авторизации
// Подключать в начале защищённых страниц: require_once 'auth.php';
// ВСЕ ФАЙЛЫ В КОРНЕ — редирект на login.php (без ../)

session_start();

if (empty($_SESSION['logged_in'])) {
    // Для API запросов — JSON ответ
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success'=>false,'error'=>'Unauthorized']);
        exit;
    }
    // Для страниц — редирект
    header('Location: login.php');
    exit;
}
