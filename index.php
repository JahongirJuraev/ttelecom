<?php
//  index.php — TT Map (ISP GIS)
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
$username = htmlspecialchars($_SESSION['username'] ?? 'admin');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Map</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="icon" href="tt_png.png" type="image/x-icon">
    <style>
        /* ══ БАЗА ══ */
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f2557 0%, #2F9BEB 60%, #0f2557 100%);
            background-attachment: fixed;
        }

        /* ══ GLASSMORPHISM УТИЛИТЫ ══ */
        .glass {
            background: rgba(255,255,255,0.13);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.22);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        }
        .glass-white {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }

        /* ══ NAVBAR ══ */
        .navbar {
            background: rgba(255,255,255,0.10);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.20);
            box-shadow: 0 4px 24px rgba(0,0,0,0.2);
        }
        .navbar-brand { font-weight: 700; color: #fff !important; }
        .user-pill {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 20px;
            padding: 4px 12px 4px 8px;
            color: #fff;
            font-size: 0.82rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .user-pill img { width:24px; height:24px; border-radius:50%; object-fit:cover; }
        #btnAdd {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            border-radius: 12px;
            font-size: 0.88rem;
            transition: all 0.2s;
        }
        #btnAdd:hover { background: rgba(255,255,255,0.25); color:#fff; }
        .btn-logout {
            background: rgba(239,68,68,0.2);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 12px;
            font-size: 0.82rem;
            padding: 5px 12px;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(239,68,68,0.35); color:#fff; }

        /* ══ КАРТА-БЛОК ══ */
        #mapSection { margin-top: 16px; }
        #mapCard {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            transition: all 0.4s ease;
        }
        #mapToolbar {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            padding: 8px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            border-bottom: 1px solid rgba(0,0,0,0.07);
        }
        .map-title { font-size: 0.85rem; color: #555; font-weight: 600; }
        .map-title i { color: #2F9BEB; }
        #map { height: 320px; transition: height 0.4s ease; }

        /* Развёрнутая карта */
        #mapCard.expanded {
            position: fixed;
            inset: 0;
            z-index: 1040;
            border-radius: 0;
            margin: 0 !important;
        }
        #mapCard.expanded #map { height: calc(100vh - 48px); }

        /* Легенда */
        .legend { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .legend-item { display:flex; align-items:center; gap:5px; font-size:0.78rem; color:#555; }
        .legend-dot { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
        .dot-mufta  { background:#22c55e; }
        .dot-client { background:#3b82f6; }
        .dot-damage { background:#ef4444; }
        .dot-other  { background:#9ca3af; }

        /* Long-press подсказка */
        #longpressHint {
            position:absolute; bottom:12px; left:50%; transform:translateX(-50%);
            background:rgba(0,0,0,0.65); color:#fff;
            padding:5px 14px; border-radius:20px;
            font-size:0.78rem; pointer-events:none; z-index:1000; white-space:nowrap;
        }

        /* Поиск */
        #searchWrapper { position:relative; }
        #searchInput {
            border-radius:20px; padding-left:34px;
            border:2px solid #dee2e6; font-size:0.88rem;
            transition:border-color 0.2s;
        }
        #searchInput:focus { border-color:#2F9BEB; box-shadow:none; }
        #searchResults {
            display:none; position:absolute; top:calc(100% + 4px); left:0; right:0;
            background:#fff; border:1px solid #dee2e6; border-radius:12px;
            box-shadow:0 8px 24px rgba(0,0,0,0.12);
            max-height:280px; overflow-y:auto; z-index:9999;
        }
        .search-item { padding:10px 14px; cursor:pointer; border-bottom:1px solid #f0f0f0; transition:background 0.15s; }
        .search-item:last-child { border-bottom:none; }
        .search-item:hover { background:#f0f7ff; }

        /* ══ ТАБЛИЦА ══ */
        #objectsPanel {
            border-radius: 20px;
            overflow: hidden;
            margin-top: 16px;
        }
        .panel-header {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            padding: 12px 16px;
            border-bottom: 1px solid rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .panel-title { font-weight:700; font-size:0.9rem; color:#333; }
        .panel-title i { color:#2F9BEB; }
        #objectsTable { background:rgba(255,255,255,0.88); }
        #objectsTable thead th {
            background: rgba(47,155,235,0.08);
            font-size:0.82rem; font-weight:700; color:#444;
            padding:9px 12px; position:sticky; top:0; z-index:2;
            border-bottom: 2px solid rgba(47,155,235,0.15);
        }
        #objectsTable tbody tr {
            cursor:pointer; transition:background 0.12s; font-size:0.84rem;
        }
        #objectsTable tbody tr:hover { background:rgba(47,155,235,0.06); }
        #objectsTable td { padding:8px 12px; vertical-align:middle; border-color:rgba(0,0,0,0.05); }
        .type-badge {
            display:inline-block; padding:3px 10px;
            border-radius:20px; color:#fff; font-size:0.74rem; font-weight:600;
        }
        .filter-btn { transition:all 0.15s; border-radius:20px !important; font-size:0.78rem; padding:3px 12px !important; }
        .filter-btn:not(.active) { opacity:0.6; }
        .filter-btn.active { opacity:1; box-shadow:0 0 0 2px #fff,0 0 0 4px #2F9BEB; }
        .filter-btn[data-filter="all"].active { background:#495057; color:#fff; border-color:#495057; }

        /* ══ MODAL (Glassmorphism) ══ */
        .modal-backdrop { backdrop-filter:blur(4px); }
        .modal-glass .modal-content {
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .modal-glass .modal-header {
            background: linear-gradient(135deg, #1a3a6e, #2F9BEB);
            color: #fff;
            border:none; padding:16px 20px;
        }
        .modal-glass .modal-header .btn-close { filter:invert(1); }
        .modal-glass .modal-footer { border-top:1px solid rgba(0,0,0,0.08); }
        .form-label { font-weight:600; font-size:0.87rem; color:#444; }
        .form-control, .form-select {
            border-radius:12px; border:1.5px solid #e0e7ef;
            font-size:0.9rem; transition:border-color 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color:#2F9BEB;
            box-shadow:0 0 0 3px rgba(47,155,235,0.15);
        }

        /* Фото превью */
        #photoPreviewWrap {
            position:relative; display:inline-block;
            border-radius:12px; overflow:hidden;
        }
        #photoPreview {
            width:100%; max-height:160px;
            object-fit:cover; border-radius:12px;
            border:2px solid #e0e7ef;
        }
        #btnDeletePhoto {
            position:absolute; top:6px; right:6px;
            background:rgba(239,68,68,0.85);
            border:none; border-radius:8px;
            color:#fff; padding:3px 8px;
            font-size:0.78rem; cursor:pointer;
            backdrop-filter:blur(4px);
        }
        .photo-upload-zone {
            border:2px dashed #c0d8f0;
            border-radius:12px; padding:18px;
            text-align:center; cursor:pointer;
            transition:all 0.2s; color:#888; font-size:0.85rem;
            background:rgba(47,155,235,0.03);
        }
        .photo-upload-zone:hover { border-color:#2F9BEB; background:rgba(47,155,235,0.06); color:#2F9BEB; }
        .photo-upload-zone i { font-size:1.8rem; display:block; margin-bottom:4px; }

        /* ══ TOAST ══ */
        #toastContainer {
            position:fixed; bottom:24px; right:24px;
            z-index:99999; display:flex; flex-direction:column; gap:8px; pointer-events:none;
        }
        .fm-toast {
            min-width:260px; max-width:340px;
            padding:12px 16px; border-radius:14px;
            color:#fff; font-size:0.88rem; font-weight:500;
            box-shadow:0 4px 20px rgba(0,0,0,0.25);
            display:flex; align-items:center; gap:10px;
            animation:toastIn 0.3s ease; pointer-events:all;
            backdrop-filter:blur(8px);
        }
        .fm-toast.success { background:rgba(34,197,94,0.92); }
        .fm-toast.error   { background:rgba(239,68,68,0.92); }
        .fm-toast.info    { background:rgba(47,155,235,0.92); }
        .fm-toast.warning { background:rgba(245,158,11,0.92); }
        @keyframes toastIn {
            from { opacity:0; transform:translateY(16px) scale(0.95); }
            to   { opacity:1; transform:translateY(0) scale(1); }
        }
        @keyframes slideUp {
            from { transform:translateY(100%); }
            to   { transform:translateY(0); }
        }

        /* ══ АДАПТИВ ══ */
        @media (max-width:576px) {
            #map { height:260px; }
            .legend { display:none; }
            #toastContainer { bottom:12px; right:12px; left:12px; }
            .fm-toast { min-width:unset; max-width:100%; }
        }
    </style>
</head>
<body>
<div class="container my-3">

    <!-- ══ NAVBAR ══ -->
    <nav class="navbar py-2 px-3">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <a class="navbar-brand mb-0" href="#">
                <img src="tt_png.png" alt="Logo" style="height:38px;">
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="user-pill">
                <img src="tt_png.png" alt="">
                <?= $username ?>
            </div>
            <button class="btn btn-sm" id="btnAdd">
                <i class="bi bi-plus-circle me-1"></i>Добавить
            </button>
            <a href="logout.php" class="btn btn-sm btn-logout">
                <i class="bi bi-box-arrow-right me-1"></i>Выход
            </a>
        </div>
    </nav>

    <!-- ══ ПАНЕЛЬ ОБЪЕКТОВ ══ -->
    <div id="objectsPanel" class="glass">

        <div class="panel-header">
            <span class="panel-title">
                <i class="bi bi-list-ul me-2"></i>Список объектов
                <span id="objCount" class="badge ms-1" style="background:#2F9BEB; font-size:0.72rem;">0</span>
            </span>
            <div class="d-flex gap-1 flex-wrap" id="filterBtns">
                <button class="btn btn-sm filter-btn active" data-filter="all"
                    style="background:#6c757d; color:#fff; border-color:#6c757d;">Все</button>
                <button class="btn btn-sm filter-btn" data-filter="mufta"
                    style="background:#22c55e; color:#fff; border-color:#22c55e;">🟢 Муфта</button>
                <button class="btn btn-sm filter-btn" data-filter="client"
                    style="background:#3b82f6; color:#fff; border-color:#3b82f6;">🔵 Клиент</button>
                <button class="btn btn-sm filter-btn" data-filter="damage"
                    style="background:#ef4444; color:#fff; border-color:#ef4444;">🔴 Авария</button>
                <button class="btn btn-sm filter-btn" data-filter="other"
                    style="background:#9ca3af; color:#fff; border-color:#9ca3af;">⚫ Другой</button>
            </div>
        </div>

        <div style="overflow-x:auto; max-height:420px; overflow-y:auto;">
            <table class="table table-hover table-sm mb-0" id="objectsTable">
                <thead>
                    <tr>
                        <th>#</th><th>Тип</th><th>Адрес</th>
                        <th>Интернет</th><th>Дата</th><th>Фото</th><th>Действия</th>
                    </tr>
                </thead>
                <tbody id="objectsTableBody">
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>Загрузка...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ КАРТА ══ -->
    <div id="mapSection">
        <div id="mapCard">
            <div id="mapToolbar">
                <span class="map-title">
                    <i class="bi bi-geo-alt-fill me-1"></i>Интерактивная карта Худжанда
                </span>

                <!-- Поиск -->
                <div id="searchWrapper" style="flex:1; max-width:360px; min-width:160px;">
                    <div style="position:relative;">
                        <i class="bi bi-search" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#aaa;font-size:0.85rem;"></i>
                        <input type="text" id="searchInput" class="form-control form-control-sm"
                            placeholder="Поиск по адресу..." autocomplete="off">
                    </div>
                    <div id="searchResults"></div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="legend d-none d-md-flex">
                        <div class="legend-item"><div class="legend-dot dot-mufta"></div>Муфта</div>
                        <div class="legend-item"><div class="legend-dot dot-client"></div>Клиент</div>
                        <div class="legend-item"><div class="legend-dot dot-damage"></div>Авария</div>
                        <div class="legend-item"><div class="legend-dot dot-other"></div>Другой</div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="btnExpand">
                        <i class="bi bi-arrows-fullscreen" id="expandIcon"></i>
                    </button>
                </div>
            </div>

            <div id="map" style="position:relative;">
                <div id="longpressHint">🖱️ Зажмите 1 сек для добавления</div>
            </div>
        </div>
    </div>

    <!-- ══ MODAL ДОБАВИТЬ / РЕДАКТИРОВАТЬ ══ -->
    <div class="modal fade modal-glass" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Добавить объект
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="fieldLat">
                    <input type="hidden" id="fieldLng">

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-tag me-1 text-primary"></i>Тип объекта</label>
                        <select class="form-select" id="fieldType">
                            <option value="mufta">🟢 Муфта</option>
                            <option value="client">🔵 Клиент</option>
                            <option value="damage">🔴 Авария</option>
                            <option value="other">⚫ Другой</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-geo-alt me-1 text-primary"></i>Адрес</label>
                        <input type="text" class="form-control" id="fieldAddress" placeholder="Например: ул. Ленина 12, кв. 5">
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-wifi me-1 text-primary"></i>Тип интернета</label>
                        <select class="form-select" id="fieldInet">
                            <option value="gpon">GPON (оптика)</option>
                            <option value="utp">UTP (медь)</option>
                            <option value="ip">IP (беспроводной)</option>
                            <option value="нет">Нет подключения</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-calendar-date me-1 text-primary"></i>Дата</label>
                        <input type="date" class="form-control" id="fieldDate">
                    </div>

                    <!-- ── ФОТО ── -->
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-image me-1 text-primary"></i>Фото <span class="text-muted fw-normal">(необязательно)</span></label>

                        <!-- Превью существующего/нового фото -->
                        <div id="photoPreviewWrap" class="mb-2 d-none w-100">
                            <img id="photoPreview" src="" alt="Фото объекта">
                            <button type="button" id="btnDeletePhoto">
                                <i class="bi bi-x"></i> Удалить
                            </button>
                        </div>

                        <!-- Зона загрузки -->
                        <div class="photo-upload-zone" id="photoUploadZone">
                            <i class="bi bi-cloud-upload"></i>
                            Нажмите или перетащите фото
                            <input type="file" id="fieldPhoto" accept="image/*" style="display:none;">
                        </div>
                    </div>

                    <div id="coordInfo" class="alert alert-info py-2 mb-0 d-none" style="font-size:0.82rem; border-radius:12px;">
                        <i class="bi bi-crosshair me-1"></i>Координаты: <span id="coordText"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius:12px;">
                        <i class="bi bi-x me-1"></i>Отмена
                    </button>
                    <button type="button" class="btn btn-primary" id="btnSave" style="border-radius:12px; background:#2F9BEB; border-color:#2F9BEB;">
                        <i class="bi bi-check-circle me-1"></i>Сохранить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ TOAST ══ -->
    <div id="toastContainer"></div>

    <!-- ══ CONFIRM MODAL ══ -->
    <div class="modal fade modal-glass" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(135deg,#7f1d1d,#ef4444);">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle me-2"></i>Подтверждение
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-3">
                    <p class="mb-0" id="confirmText">Вы уверены?</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" style="border-radius:10px;">
                        <i class="bi bi-x me-1"></i>Отмена
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirmOkBtn" style="border-radius:10px;">
                        <i class="bi bi-trash me-1"></i>Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ JOINT MODAL (схема муфты) ══ -->
    <div id="jointModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(6px);z-index:3000;align-items:flex-end;justify-content:center;">
        <div id="jointModalBox" style="width:100%;max-width:620px;height:94vh;background:linear-gradient(135deg,#0f2557,#1a3a6e);border-radius:24px 24px 0 0;border:1px solid rgba(255,255,255,0.2);overflow:hidden;display:flex;flex-direction:column;animation:slideUp 0.35s cubic-bezier(0.34,1.56,0.64,1);">
            <div style="display:flex;align-items:center;padding:10px 16px;background:rgba(255,255,255,0.07);border-bottom:1px solid rgba(255,255,255,0.1);flex-shrink:0;">
                <button onclick="closeJointModal()" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);border-radius:10px;color:#fff;padding:6px 14px;font-size:0.82rem;cursor:pointer;">← Закрыть</button>
                <span id="jointModalTitle" style="color:#fff;font-weight:700;font-size:0.95rem;margin-left:12px;"></span>
            </div>
            <iframe id="jointFrame" src="" style="flex:1;border:none;width:100%;background:transparent;"></iframe>
        </div>
    </div>

</div><!-- /container -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ══════════════════ КОНФИГ ══════════════════
const KHUJAND = [40.2833, 69.6333];
const BASE = (() => { const p=window.location.pathname; return p.substring(0,p.lastIndexOf('/')+1); })();
const API = {
    get       : BASE+'get.php',
    add       : BASE+'add.php',
    delete    : BASE+'delete.php',
    search    : BASE+'search.php',
    update    : BASE+'update.php',
    getJoints : BASE+'getJoints.php',
    getJoint  : BASE+'getJoint.php',
};
const COLORS = { mufta:'#22c55e', client:'#3b82f6', damage:'#ef4444', other:'#9ca3af' };
const TYPE_LABELS = { mufta:'🟢 Муфта', client:'🔵 Клиент', damage:'🔴 Авария', other:'⚫ Другой' };
const INET_LABELS = { gpon:'GPON (оптика)', utp:'UTP (медь)', ip:'IP (беспроводной)', 'нет':'Нет подключения' };

// ══════════════════ TOAST ══════════════════
const TOAST_ICONS = { success:'bi-check-circle-fill', error:'bi-x-circle-fill', info:'bi-info-circle-fill', warning:'bi-exclamation-triangle-fill' };
function showToast(msg, type='info', dur=3500) {
    const c=document.getElementById('toastContainer');
    const t=document.createElement('div');
    t.className=`fm-toast ${type}`;
    t.innerHTML=`<i class="bi ${TOAST_ICONS[type]}" style="font-size:1.1rem;flex-shrink:0;"></i><span>${msg}</span>`;
    c.appendChild(t);
    setTimeout(()=>{t.style.transition='opacity 0.3s,transform 0.3s';t.style.opacity='0';t.style.transform='translateY(8px)';setTimeout(()=>t.remove(),320);},dur);
}

// ══════════════════ CONFIRM ══════════════════
function showConfirm(msg, onOk) {
    document.getElementById('confirmText').textContent = msg;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const btn   = document.getElementById('confirmOkBtn');
    const h = () => { modal.hide(); btn.removeEventListener('click',h); onOk(); };
    btn.addEventListener('click', h);
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', ()=>btn.removeEventListener('click',h), {once:true});
    modal.show();
}

// ══════════════════ КАРТА ══════════════════
const map = L.map('map', { center:KHUJAND, zoom:14 });
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:'© OpenStreetMap contributors', maxZoom:19
}).addTo(map);
const markersLayer = L.layerGroup().addTo(map);
const markersMap   = {};

function makeIcon(type) {
    const c = COLORS[type]||COLORS.other;
    return L.divIcon({
        className:'',
        html:`<div style="width:15px;height:15px;border-radius:50%;background:${c};border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>`,
        iconSize:[15,15], iconAnchor:[7.5,7.5]
    });
}

function makePopup(obj) {
    const photoHtml = obj.photo
        ? `<div style="margin-bottom:8px;"><img src="${BASE}${obj.photo}" style="width:100%;max-height:130px;object-fit:cover;border-radius:10px;border:1px solid #eee;" onclick="window.open('${BASE}${obj.photo}','_blank')" title="Открыть фото"></div>`
        : '';
    return `
    <div style="min-width:210px;font-family:'Segoe UI',sans-serif;">
        ${photoHtml}
        <div style="font-weight:700;font-size:1rem;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
            <span style="width:11px;height:11px;border-radius:50%;background:${COLORS[obj.type]||'#aaa'};display:inline-block;flex-shrink:0;"></span>
            ${TYPE_LABELS[obj.type]||obj.type}
        </div>
        <table style="width:100%;font-size:0.83rem;border-collapse:collapse;">
            <tr><td style="color:#888;padding:3px 8px 3px 0;white-space:nowrap;">📍 Адрес</td><td><b>${obj.address}</b></td></tr>
            <tr><td style="color:#888;padding:3px 8px 3px 0;white-space:nowrap;">🌐 Интернет</td><td>${INET_LABELS[obj.internet_type]||obj.internet_type}</td></tr>
            <tr><td style="color:#888;padding:3px 8px 3px 0;white-space:nowrap;">📅 Дата</td><td>${obj.custom_date||'—'}</td></tr>
        </table>
        <hr style="margin:8px 0;">
        <div style="display:flex;gap:6px;">
            <button onclick="openEditModal(${obj.id})"
                style="flex:1;padding:6px;background:#2F9BEB;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:0.82rem;">
                ✏️ Редактировать
            </button>
            <button onclick="deleteObject(${obj.id})"
                style="flex:1;padding:6px;background:#ef4444;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:0.82rem;">
                🗑 Удалить
            </button>
        </div>
    </div>`;
}

function addMarkerToMap(obj) {
    const m = L.marker([obj.lat,obj.lng],{icon:makeIcon(obj.type)})
        .bindPopup(makePopup(obj), {maxWidth:240})
        .addTo(markersLayer);
    markersMap[obj.id] = m;
}

// ══════════════════ ЗАГРУЗКА ══════════════════
let allObjects = [], activeFilter = 'all';

async function loadObjects() {
    try {
        const res  = await fetch(API.get);
        const json = await res.json();
        if (!json.success) return;
        allObjects = json.data;
        markersLayer.clearLayers();
        Object.keys(markersMap).forEach(k=>delete markersMap[k]);
        allObjects.forEach(addMarkerToMap);
        renderTable(activeFilter);
    } catch(e) {
        showToast('Ошибка загрузки объектов','error');
        console.error(e);
    }
}
loadObjects();

// ══════════════════ УДАЛЕНИЕ ══════════════════
async function deleteObject(id) {
    showConfirm('Удалить этот объект с карты?', async () => {
        try {
            const res  = await fetch(`${API.delete}?id=${id}`);
            const json = await res.json();
            if (json.success) {
                if (markersMap[id]) { markersLayer.removeLayer(markersMap[id]); delete markersMap[id]; }
                allObjects = allObjects.filter(o=>o.id!=id);
                renderTable(activeFilter);
                showToast('Объект удалён','success');
            } else showToast('Ошибка: '+(json.error||'?'),'error');
        } catch(e) { showToast('Ошибка сети','error'); }
    });
}

// ══════════════════ MODAL / ФОТО ══════════════════
const addModal   = new bootstrap.Modal(document.getElementById('addModal'));
const fieldLat   = document.getElementById('fieldLat');
const fieldLng   = document.getElementById('fieldLng');
const coordInfo  = document.getElementById('coordInfo');
const coordText  = document.getElementById('coordText');

// Фото
let photoFile = null;       // новый файл
let deletePhoto = false;    // флаг удаления

document.getElementById('photoUploadZone').addEventListener('click', () => {
    document.getElementById('fieldPhoto').click();
});

document.getElementById('fieldPhoto').addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    photoFile = file;
    deletePhoto = false;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('photoPreview').src = ev.target.result;
        document.getElementById('photoPreviewWrap').classList.remove('d-none');
        document.getElementById('photoUploadZone').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

// Drag & drop
const zone = document.getElementById('photoUploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='#2F9BEB'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor=''; });
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor='';
    const file = e.dataTransfer.files[0];
    if (!file) return;
    document.getElementById('fieldPhoto').files; // just trigger
    photoFile = file;
    deletePhoto = false;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('photoPreview').src = ev.target.result;
        document.getElementById('photoPreviewWrap').classList.remove('d-none');
        document.getElementById('photoUploadZone').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

document.getElementById('btnDeletePhoto').addEventListener('click', () => {
    deletePhoto = true;
    photoFile   = null;
    document.getElementById('photoPreviewWrap').classList.add('d-none');
    document.getElementById('photoUploadZone').style.display = '';
    document.getElementById('fieldPhoto').value = '';
});

function resetPhotoUI(existingPhoto) {
    photoFile   = null;
    deletePhoto = false;
    document.getElementById('fieldPhoto').value = '';
    if (existingPhoto) {
        document.getElementById('photoPreview').src = BASE + existingPhoto;
        document.getElementById('photoPreviewWrap').classList.remove('d-none');
        document.getElementById('photoUploadZone').style.display = 'none';
    } else {
        document.getElementById('photoPreviewWrap').classList.add('d-none');
        document.getElementById('photoUploadZone').style.display = '';
    }
}

function openAddModal(lat, lng) {
    document.getElementById('addModalLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Добавить объект';
    document.getElementById('btnSave').dataset.editId = '';
    document.getElementById('btnSave').innerHTML = '<i class="bi bi-check-circle me-1"></i>Сохранить';
    document.getElementById('fieldAddress').value = '';
    document.getElementById('fieldType').value    = 'mufta';
    document.getElementById('fieldInet').value    = 'gpon';
    document.getElementById('fieldDate').value    = new Date().toISOString().split('T')[0];
    resetPhotoUI(null);
    if (lat != null) {
        fieldLat.value = lat.toFixed(6);
        fieldLng.value = lng.toFixed(6);
        coordText.innerText = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
        coordInfo.classList.remove('d-none');
    } else {
        fieldLat.value = ''; fieldLng.value = '';
        coordInfo.classList.add('d-none');
    }
    addModal.show();
}

function openEditModal(id) {
    const obj = allObjects.find(o=>o.id==id);
    if (!obj) return;
    document.getElementById('addModalLabel').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Редактировать объект';
    document.getElementById('btnSave').dataset.editId = id;
    document.getElementById('btnSave').innerHTML = '<i class="bi bi-check-circle me-1"></i>Сохранить изменения';
    document.getElementById('fieldType').value    = obj.type;
    document.getElementById('fieldAddress').value = obj.address;
    document.getElementById('fieldInet').value    = obj.internet_type;
    if (obj.custom_date && obj.custom_date.includes('.')) {
        const [d,m,y] = obj.custom_date.split('.');
        document.getElementById('fieldDate').value = `${y}-${m}-${d}`;
    } else {
        document.getElementById('fieldDate').value = obj.custom_date || '';
    }
    fieldLat.value = obj.lat;
    fieldLng.value = obj.lng;
    coordText.innerText = `${parseFloat(obj.lat).toFixed(5)}, ${parseFloat(obj.lng).toFixed(5)}`;
    coordInfo.classList.remove('d-none');
    resetPhotoUI(obj.photo || null);
    addModal.show();
}

document.getElementById('btnAdd').addEventListener('click', () => openAddModal(KHUJAND[0], KHUJAND[1]));
document.getElementById('addModal').addEventListener('hidden.bs.modal', () => {
    document.getElementById('addModalLabel').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Добавить объект';
    document.getElementById('btnSave').dataset.editId = '';
    document.getElementById('btnSave').innerHTML = '<i class="bi bi-check-circle me-1"></i>Сохранить';
    resetPhotoUI(null);
});

// Long press
let pressTimer = null;
map.on('mousedown touchstart', e => {
    pressTimer = setTimeout(() => { const {lat,lng}=e.latlng; openAddModal(lat,lng); }, 900);
});
map.on('mouseup mousemove touchend touchmove', () => clearTimeout(pressTimer));

// ══════════════════ СОХРАНЕНИЕ ══════════════════
document.getElementById('btnSave').addEventListener('click', async () => {
    const type         = document.getElementById('fieldType').value;
    const address      = document.getElementById('fieldAddress').value.trim();
    const internet_type= document.getElementById('fieldInet').value;
    const custom_date  = document.getElementById('fieldDate').value;
    const lat          = parseFloat(fieldLat.value);
    const lng          = parseFloat(fieldLng.value);
    const editId       = document.getElementById('btnSave').dataset.editId;

    if (!address) {
        document.getElementById('fieldAddress').classList.add('is-invalid');
        document.getElementById('fieldAddress').focus();
        return;
    }
    document.getElementById('fieldAddress').classList.remove('is-invalid');

    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Сохранение...';

    // Строим FormData (поддержка файлов)
    const fd = new FormData();
    fd.append('type', type);
    fd.append('address', address);
    fd.append('internet_type', internet_type);
    fd.append('custom_date', custom_date);
    fd.append('lat', lat);
    fd.append('lng', lng);
    if (photoFile)    fd.append('photo', photoFile);
    if (deletePhoto)  fd.append('delete_photo', '1');

    try {
        let url = editId ? API.update : API.add;
        if (editId) fd.append('id', editId);

        const res  = await fetch(url, { method:'POST', body: fd });
        if (!res.ok) {
            const txt = await res.text();
            console.error('HTTP', res.status, txt);
            showToast(`Ошибка сервера (${res.status})`, 'error');
            btn.disabled = false;
            btn.innerHTML = editId
                ? '<i class="bi bi-check-circle me-1"></i>Сохранить изменения'
                : '<i class="bi bi-check-circle me-1"></i>Сохранить';
            return;
        }
        const json = await res.json();

        if (json.success) {
            addModal.hide();
            if (editId) {
                // Обновляем в массиве
                const idx = allObjects.findIndex(o=>o.id==editId);
                if (idx !== -1) {
                    allObjects[idx] = { ...allObjects[idx], type, address, internet_type,
                        custom_date: custom_date ? custom_date.split('-').reverse().join('.') : '',
                        lat, lng, photo: json.photo !== undefined ? json.photo : allObjects[idx].photo };
                }
                if (markersMap[editId]) { markersLayer.removeLayer(markersMap[editId]); delete markersMap[editId]; }
                addMarkerToMap(allObjects[idx]);
                renderTable(activeFilter);
                showToast('Объект обновлён!', 'success');
            } else {
                const newObj = { id:json.id, type, address, internet_type,
                    custom_date: custom_date ? custom_date.split('-').reverse().join('.') : '',
                    lat, lng, photo: json.photo || null };
                allObjects.unshift(newObj);
                addMarkerToMap(newObj);
                map.setView([lat,lng], Math.max(map.getZoom(),16));
                renderTable(activeFilter);
                showToast('Объект добавлен!', 'success');
            }
        } else {
            showToast('Ошибка: '+(json.error||'?'), 'error');
        }
    } catch(e) {
        console.error(e);
        showToast(`Ошибка сети: ${e.message}`, 'error');
    }

    btn.disabled = false;
    btn.innerHTML = editId
        ? '<i class="bi bi-check-circle me-1"></i>Сохранить изменения'
        : '<i class="bi bi-check-circle me-1"></i>Сохранить';
});

// ══════════════════ РАЗВЕРНУТЬ КАРТУ ══════════════════
const mapCard   = document.getElementById('mapCard');
const expandBtn = document.getElementById('btnExpand');
const expandIcon= document.getElementById('expandIcon');
let isExpanded  = false;
expandBtn.addEventListener('click', () => {
    isExpanded = !isExpanded;
    mapCard.classList.toggle('expanded', isExpanded);
    expandIcon.className = isExpanded ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
    setTimeout(() => map.invalidateSize(), 420);
});
document.addEventListener('keydown', e => { if (e.key==='Escape' && isExpanded) expandBtn.click(); });

// ══════════════════ ТАБЛИЦА ══════════════════
function renderTable(filter) {
    activeFilter = filter;
    const tbody    = document.getElementById('objectsTableBody');
    const filtered = filter==='all' ? allObjects : allObjects.filter(o=>o.type===filter);
    document.getElementById('objCount').textContent = filtered.length;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.toggle('active', b.dataset.filter===filter));

    if (!filtered.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-inbox me-2"></i>Нет объектов</td></tr>`;
        return;
    }
    const tc = { mufta:'#22c55e', client:'#3b82f6', damage:'#ef4444', other:'#9ca3af' };
    tbody.innerHTML = filtered.map((obj,i) => `
        <tr data-id="${obj.id}" onclick="focusOnMap(${obj.lat},${obj.lng},${obj.id})">
            <td style="color:#aaa;">${i+1}</td>
            <td><span class="type-badge" style="background:${tc[obj.type]||'#aaa'};">${TYPE_LABELS[obj.type]||obj.type}</span></td>
            <td style="font-weight:500;">${obj.address}</td>
            <td style="color:#555;">${INET_LABELS[obj.internet_type]||obj.internet_type}</td>
            <td style="color:#888;white-space:nowrap;">${obj.custom_date||'—'}</td>
            <td onclick="event.stopPropagation();">
                ${obj.photo
                    ? `<img src="${BASE}${obj.photo}" style="width:36px;height:36px;object-fit:cover;border-radius:8px;cursor:pointer;border:1.5px solid #ddd;" onclick="window.open('${BASE}${obj.photo}','_blank')" title="Открыть фото">`
                    : `<span style="color:#ccc;font-size:0.8rem;">—</span>`}
            </td>
            <td onclick="event.stopPropagation();" style="white-space:nowrap;">
                <button class="btn btn-sm" style="padding:3px 9px;font-size:0.75rem;background:#2F9BEB;color:#fff;border:none;border-radius:8px;margin-right:4px;"
                    onclick="openEditModal(${obj.id})">✏️</button>
                <button class="btn btn-sm" style="padding:3px 9px;font-size:0.75rem;background:#ef4444;color:#fff;border:none;border-radius:8px;"
                    onclick="deleteObject(${obj.id})">🗑</button>
            </td>
        </tr>`).join('');
}

function focusOnMap(lat, lng, id) {
    map.setView([lat,lng], 17);
    if (markersMap[id]) markersMap[id].openPopup();
    window.scrollTo({ top:0, behavior:'smooth' });
}

document.getElementById('filterBtns').addEventListener('click', e => {
    const b = e.target.closest('.filter-btn');
    if (b) renderTable(b.dataset.filter);
});

// ══════════════════ ПОИСК ══════════════════
const searchInput   = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
let searchTimeout   = null;

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const q = searchInput.value.trim();
    if (q.length < 2) { searchResults.style.display='none'; return; }
    searchTimeout = setTimeout(async () => {
        try {
            const res  = await fetch(`${API.search}?q=${encodeURIComponent(q)}`);
            const json = await res.json();
            if (!json.success || !json.data.length) {
                searchResults.innerHTML = '<div class="search-item text-muted">Ничего не найдено</div>';
                searchResults.style.display = 'block';
                return;
            }
            searchResults.innerHTML = json.data.map(obj=>`
                <div class="search-item" data-lat="${obj.lat}" data-lng="${obj.lng}" data-id="${obj.id}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill" style="background:${COLORS[obj.type]||'#aaa'};">${TYPE_LABELS[obj.type]||obj.type}</span>
                        <span class="fw-semibold" style="font-size:0.88rem;">${obj.address}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:0.78rem;">${INET_LABELS[obj.internet_type]||obj.internet_type}${obj.custom_date?' · '+obj.custom_date:''}</div>
                </div>`).join('');
            searchResults.style.display = 'block';
            searchResults.querySelectorAll('.search-item').forEach(item => {
                item.addEventListener('click', () => {
                    const lat=parseFloat(item.dataset.lat), lng=parseFloat(item.dataset.lng), id=parseInt(item.dataset.id);
                    map.setView([lat,lng], 18);
                    if (markersMap[id]) markersMap[id].openPopup();
                    searchResults.style.display = 'none';
                    searchInput.value = item.querySelector('.fw-semibold').innerText;
                });
            });
        } catch(e) { console.error(e); }
    }, 350);
});

document.addEventListener('click', e => {
    if (!e.target.closest('#searchWrapper')) searchResults.style.display='none';
});

// ══════════════════ МУФТЫ СЕТИ (Joint Markers) ══════════════════
const jointMarkersLayer = L.layerGroup().addTo(map);

function makeJointIcon(stats) {
    const total = stats.fiber_total || 0;
    const free  = stats.fiber_free  || 0;
    const ratio = total > 0 ? free / total : 1;
    // Цвет по заполненности: зелёный → жёлтый → красный
    const color = ratio > 0.3 ? '#22c55e' : ratio > 0 ? '#f59e0b' : '#ef4444';
    return L.divIcon({
        className: '',
        html: `<div style="
            width:26px;height:26px;border-radius:50%;
            background:${color};border:3px solid #fff;
            box-shadow:0 2px 10px rgba(0,0,0,0.5);
            display:flex;align-items:center;justify-content:center;
            font-size:11px;color:#fff;font-weight:900;
            font-family:'Segoe UI',sans-serif;
        ">M</div>`,
        iconSize: [26,26], iconAnchor: [13,13]
    });
}

async function loadJointMarkers() {
    try {
        const res  = await fetch(API.getJoints);
        const json = await res.json();
        if (!json.success) return;
        jointMarkersLayer.clearLayers();
        json.data.forEach(j => {
            L.marker([j.lat, j.lng], { icon: makeJointIcon(j) })
             .bindPopup(`
                <div style="min-width:190px;font-family:'Segoe UI',sans-serif;">
                    <div style="font-weight:700;font-size:0.95rem;margin-bottom:6px;">🔧 ${j.name}</div>
                    <div style="font-size:0.8rem;color:#666;margin-bottom:8px;">${j.address||''}</div>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px;">
                        <span style="background:#dcfce7;color:#166534;padding:2px 8px;border-radius:20px;font-size:0.72rem;">🟢 ${j.fiber_free} св.</span>
                        <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:20px;font-size:0.72rem;">🔵 ${j.fiber_used} исп.</span>
                        <span style="background:#f3f4f6;color:#374151;padding:2px 8px;border-radius:20px;font-size:0.72rem;">📦 ${j.cable_count} кабелей</span>
                    </div>
                    <button
                        onclick="openJointModal(${j.id},'${j.name.replace(/'/g,"\\'")}');map.closePopup();"
                        style="width:100%;padding:8px;background:#2F9BEB;color:#fff;border:none;border-radius:10px;cursor:pointer;font-weight:600;font-size:0.85rem;">
                        📋 Открыть схему муфты
                    </button>
                </div>
             `, { maxWidth: 220 })
             .addTo(jointMarkersLayer);
        });
    } catch(e) { console.error('loadJointMarkers:', e); }
}

loadJointMarkers();

// ══════════════════ JOINT MODAL ══════════════════
function openJointModal(jointId, name) {
    const overlay = document.getElementById('jointModalOverlay');
    document.getElementById('jointModalTitle').textContent = name;
    document.getElementById('jointFrame').src = `${BASE}joint.php?id=${jointId}`;
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeJointModal() {
    const overlay = document.getElementById('jointModalOverlay');
    overlay.style.display = 'none';
    document.getElementById('jointFrame').src = '';
    document.body.style.overflow = '';
    loadJointMarkers(); // обновляем статистику маркеров
}
window.closeJointModal = closeJointModal;

// Закрыть по клику на фон
document.getElementById('jointModalOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('jointModalOverlay')) closeJointModal();
});
</script>
</body>
</html>
