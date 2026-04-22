<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title>Схема муфты</title>
<style>
/* ══ БАЗА ══════════════════════════════════════════════════ */
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent;}
body{
  font-family:'Segoe UI',-apple-system,sans-serif;
  background:linear-gradient(135deg,#0f2557 0%,#2F9BEB 60%,#0f2557 100%);
  min-height:100vh; overflow-x:hidden;
}

/* ══ GLASSMORPHISM ═════════════════════════════════════════ */
.glass{
  background:rgba(255,255,255,0.12);
  backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);
  border:1px solid rgba(255,255,255,0.22);
  box-shadow:0 8px 32px rgba(0,0,0,0.2);
}
.glass-white{
  background:rgba(255,255,255,0.93);
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,0.5);
  box-shadow:0 4px 20px rgba(0,0,0,0.12);
}

/* ══ HEADER ════════════════════════════════════════════════ */
.hdr{
  padding:12px 16px;border-radius:0 0 20px 20px;
  display:flex;align-items:center;gap:10px;
  background:rgba(255,255,255,0.10);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid rgba(255,255,255,0.15);
  position:sticky;top:0;z-index:100;
}
.hdr-title{color:#fff;font-weight:700;font-size:1rem;flex:1;}
.hdr-sub{color:rgba(255,255,255,0.6);font-size:0.75rem;}
.btn-back{background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);
  border-radius:10px;color:#fff;padding:6px 12px;font-size:0.82rem;cursor:pointer;}

/* ══ СТАТИСТИКА КАБЕЛЕЙ ════════════════════════════════════ */
.cable-stats{
  display:flex;gap:6px;overflow-x:auto;padding:12px 16px;
  scrollbar-width:none;
}
.cable-stats::-webkit-scrollbar{display:none;}
.stat-card{
  flex-shrink:0;padding:10px 14px;border-radius:14px;min-width:140px;
  background:rgba(255,255,255,0.12);
  border:1px solid rgba(255,255,255,0.2);
  color:#fff;
}
.stat-card-name{font-size:0.78rem;font-weight:600;margin-bottom:6px;opacity:0.9;}
.stat-pills{display:flex;gap:4px;flex-wrap:wrap;}
.pill{font-size:0.7rem;padding:2px 8px;border-radius:20px;font-weight:600;}
.pill-free{background:rgba(34,197,94,0.25);color:#86efac;border:1px solid rgba(34,197,94,0.3);}
.pill-used{background:rgba(47,155,235,0.25);color:#93c5fd;border:1px solid rgba(47,155,235,0.3);}
.pill-dmg{background:rgba(239,68,68,0.25);color:#fca5a5;border:1px solid rgba(239,68,68,0.3);}

/* ══ ПАНЕЛИ ДЕЙСТВИЙ ═══════════════════════════════════════ */
.actions{display:flex;gap:8px;padding:0 16px 12px;flex-wrap:wrap;}
.btn-action{
  padding:8px 14px;border-radius:12px;border:none;font-size:0.82rem;
  font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;
  transition:all 0.2s;
}
.btn-primary{background:#2F9BEB;color:#fff;}
.btn-primary:hover{background:#1a7acc;}
.btn-success{background:#22c55e;color:#fff;}
.btn-danger{background:#ef4444;color:#fff;}
.btn-glass{background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.25);}
.btn-glass:hover{background:rgba(255,255,255,0.25);}

/* ══ ФИЛЬТРЫ ═══════════════════════════════════════════════ */
.filters{display:flex;gap:6px;padding:0 16px 12px;overflow-x:auto;scrollbar-width:none;}
.filters::-webkit-scrollbar{display:none;}
.filter-btn{
  flex-shrink:0;padding:5px 14px;border-radius:20px;border:none;
  font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;
  background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.8);
}
.filter-btn.active{background:#2F9BEB;color:#fff;box-shadow:0 2px 10px rgba(47,155,235,0.4);}

/* ══ СХЕМА МУФТЫ (SVG-холст) ═══════════════════════════════ */
.schema-wrap{
  margin:0 16px 16px;border-radius:20px;overflow:hidden;
  background:rgba(255,255,255,0.07);
  border:1px solid rgba(255,255,255,0.15);
  position:relative;
  min-height:420px;
}
#schemaCanvas{width:100%;display:block;touch-action:none;}

/* ══ СТОРОНЫ (DOM overlay) ════════════════════════════════ */
.side-node{
  position:absolute;
  background:rgba(255,255,255,0.12);
  backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
  border:1px solid rgba(255,255,255,0.25);
  border-radius:14px;padding:8px 10px;
  min-width:90px;max-width:130px;
  cursor:pointer;transition:all 0.2s;
  transform:translate(-50%,-50%);
  user-select:none;
}
.side-node:hover{border-color:#2F9BEB;background:rgba(47,155,235,0.2);}
.side-node.selected{border-color:#2F9BEB;background:rgba(47,155,235,0.25);box-shadow:0 0 0 2px #2F9BEB;}
.side-name{font-size:0.75rem;font-weight:700;color:#fff;margin-bottom:5px;text-align:center;}
.side-fibers-row{display:flex;gap:2px;flex-wrap:wrap;justify-content:center;}

/* ══ ЖИЛЫ (кружки) ════════════════════════════════════════ */
.fiber-dot{
  width:14px;height:14px;border-radius:50%;
  border:2px solid rgba(255,255,255,0.3);
  cursor:pointer;transition:all 0.15s;
  flex-shrink:0;position:relative;
}
.fiber-dot:hover{transform:scale(1.3);border-color:#fff;z-index:10;}
.fiber-dot.selected-conn{
  border:3px solid #fff;
  box-shadow:0 0 0 3px #2F9BEB,0 0 10px #2F9BEB;
  transform:scale(1.4);
}
.fiber-dot.status-free{opacity:1;}
.fiber-dot.status-used{opacity:1;}
.fiber-dot.status-damaged{
  opacity:0.5;
  background:repeating-linear-gradient(45deg,#ef4444,#ef4444 2px,transparent 2px,transparent 6px) !important;
}
.fiber-dot.highlighted{
  animation:pulse 0.8s ease infinite;
  border-color:#fff;
  box-shadow:0 0 0 4px rgba(255,220,0,0.6);
}
@keyframes pulse{
  0%,100%{box-shadow:0 0 0 3px rgba(255,220,0,0.4);}
  50%{box-shadow:0 0 0 6px rgba(255,220,0,0.1);}
}
.fiber-dot.filtered-out{opacity:0.1;pointer-events:none;}

/* ══ ЦЕНТР МУФТЫ ═══════════════════════════════════════════ */
.joint-center{
  position:absolute;
  background:radial-gradient(circle,rgba(47,155,235,0.4),rgba(47,155,235,0.1));
  border:2px solid rgba(47,155,235,0.6);
  border-radius:50%;
  transform:translate(-50%,-50%);
  display:flex;align-items:center;justify-content:center;
  flex-direction:column;gap:2px;
}
.joint-center-name{font-size:0.7rem;font-weight:700;color:#fff;text-align:center;padding:0 4px;}

/* ══ МОДАЛЬНЫЕ ОКНА ════════════════════════════════════════ */
.overlay{
  display:none;position:fixed;inset:0;
  background:rgba(0,0,0,0.5);
  backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
  z-index:1000;align-items:flex-end;justify-content:center;
}
.overlay.show{display:flex;}
.sheet{
  width:100%;max-width:500px;
  background:rgba(20,40,80,0.95);
  backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  border:1px solid rgba(255,255,255,0.2);
  border-radius:24px 24px 0 0;
  padding:20px;
  max-height:90vh;overflow-y:auto;
  animation:slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
}
@keyframes slideUp{from{transform:translateY(100%);}to{transform:translateY(0);}}
.sheet-handle{width:40px;height:4px;background:rgba(255,255,255,0.3);border-radius:2px;margin:0 auto 16px;}
.sheet-title{color:#fff;font-weight:700;font-size:1.1rem;margin-bottom:16px;}

.form-group{margin-bottom:14px;}
.form-label{color:rgba(255,255,255,0.7);font-size:0.82rem;font-weight:600;margin-bottom:5px;display:block;}
.form-input{
  width:100%;padding:11px 14px;
  background:rgba(255,255,255,0.1);
  border:1px solid rgba(255,255,255,0.2);
  border-radius:12px;color:#fff;font-size:0.9rem;
  outline:none;transition:border-color 0.2s;
}
.form-input:focus{border-color:#2F9BEB;}
.form-input option{background:#1a2a5e;color:#fff;}
.form-select{appearance:none;-webkit-appearance:none;}

/* ══ СЕТКА ВЫБОРА ЖИЛ ═════════════════════════════════════ */
.fiber-grid-wrap{margin-top:10px;}
.module-row{margin-bottom:12px;}
.module-label{
  font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:6px;
  display:flex;align-items:center;gap:6px;
}
.module-tube{width:12px;height:12px;border-radius:3px;flex-shrink:0;}
.fiber-grid{display:flex;gap:6px;flex-wrap:wrap;}
.fiber-pick{
  width:32px;height:32px;border-radius:50%;
  border:2px solid rgba(255,255,255,0.2);
  cursor:pointer;transition:all 0.15s;
  display:flex;align-items:center;justify-content:center;
  font-size:0.62rem;color:#fff;font-weight:700;
  position:relative;
}
.fiber-pick:hover{transform:scale(1.15);}
.fiber-pick.selected{border:3px solid #fff;box-shadow:0 0 0 3px #2F9BEB;}
.fiber-pick.already-used{opacity:0.3;cursor:not-allowed;}
.fiber-pick .fp-num{position:absolute;bottom:-14px;font-size:0.6rem;color:rgba(255,255,255,0.5);white-space:nowrap;}

/* ══ ТРАССИРОВКА ═══════════════════════════════════════════ */
.trace-step{
  padding:10px 12px;border-radius:12px;margin-bottom:8px;
  background:rgba(255,255,255,0.07);
  border:1px solid rgba(255,255,255,0.1);
}
.trace-joint{font-weight:700;color:#2F9BEB;font-size:0.88rem;}
.trace-fiber{font-size:0.78rem;color:rgba(255,255,255,0.7);margin-top:2px;}
.trace-arrow{text-align:center;color:rgba(255,255,255,0.3);font-size:1.2rem;margin:2px 0;}

/* ══ TOAST ═════════════════════════════════════════════════ */
#toastWrap{position:fixed;bottom:24px;right:16px;left:16px;z-index:9999;display:flex;flex-direction:column;gap:6px;pointer-events:none;}
.toast-msg{
  padding:11px 16px;border-radius:14px;color:#fff;font-size:0.84rem;font-weight:500;
  backdrop-filter:blur(8px);display:flex;align-items:center;gap:8px;
  animation:toastIn 0.3s ease;pointer-events:all;
  box-shadow:0 4px 20px rgba(0,0,0,0.3);
}
.toast-msg.ok{background:rgba(34,197,94,0.9);}
.toast-msg.err{background:rgba(239,68,68,0.9);}
.toast-msg.info{background:rgba(47,155,235,0.9);}
@keyframes toastIn{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}

/* ══ CONNECTION MODE HINT ══════════════════════════════════ */
#connHint{
  display:none;
  position:sticky;top:60px;z-index:50;
  margin:0 16px 8px;padding:10px 14px;
  background:rgba(255,220,0,0.2);
  border:1px solid rgba(255,220,0,0.4);
  border-radius:12px;color:#fde68a;
  font-size:0.82rem;text-align:center;
}

/* ══ SIDE ACTIONS (edit/delete dots) ══════════════════════ */
.side-actions{display:flex;justify-content:center;gap:4px;margin-top:5px;}
.side-act-btn{
  background:rgba(255,255,255,0.15);border:none;border-radius:6px;
  color:#fff;font-size:0.65rem;padding:2px 6px;cursor:pointer;
}
.side-act-btn:hover{background:rgba(255,255,255,0.3);}

/* ══ SPINNER ═══════════════════════════════════════════════ */
.spinner{width:32px;height:32px;border:3px solid rgba(255,255,255,0.2);
  border-top-color:#2F9BEB;border-radius:50%;animation:spin 0.7s linear infinite;margin:0 auto;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ══ FIBER DETAIL POPUP ════════════════════════════════════ */
.fiber-popup{
  position:absolute;z-index:200;
  background:rgba(20,40,80,0.97);
  border:1px solid rgba(255,255,255,0.2);
  border-radius:14px;padding:12px 14px;
  min-width:180px;
  box-shadow:0 8px 32px rgba(0,0,0,0.4);
  animation:fadeIn 0.15s ease;
  pointer-events:all;
}
@keyframes fadeIn{from{opacity:0;transform:scale(0.9);}to{opacity:1;transform:scale(1);}}
.fp-title{font-weight:700;color:#fff;font-size:0.85rem;margin-bottom:6px;}
.fp-status{font-size:0.75rem;margin-bottom:6px;}
.fp-notes{font-size:0.75rem;color:rgba(255,255,255,0.6);margin-bottom:8px;}
.fp-btns{display:flex;gap:4px;flex-wrap:wrap;}
.fp-btn{font-size:0.72rem;padding:4px 8px;border-radius:8px;border:none;cursor:pointer;font-weight:600;}
</style>
</head>
<body>

<!-- HEADER -->
<div class="hdr">
  <button class="btn-back" onclick="window.parent.closeJointModal && window.parent.closeJointModal(); history.back();">← Назад</button>
  <div>
    <div class="hdr-title" id="hdrTitle">Загрузка...</div>
    <div class="hdr-sub" id="hdrSub"></div>
  </div>
  <button class="btn-action btn-primary" style="font-size:0.75rem;padding:6px 10px;" onclick="showPrintModal()">
    🖨️ Печать
  </button>
</div>

<!-- СТАТИСТИКА КАБЕЛЕЙ -->
<div class="cable-stats" id="cableStats">
  <div style="color:rgba(255,255,255,0.5);font-size:0.8rem;padding:10px;">Загрузка кабелей...</div>
</div>

<!-- КНОПКИ ДЕЙСТВИЙ -->
<div class="actions">
  <button class="btn-action btn-primary" onclick="showAddSideModal()">+ Сторона</button>
  <button class="btn-action btn-glass"   onclick="showAddCableModal()">📦 Кабель</button>
  <button class="btn-action btn-glass"   onclick="toggleConnMode()" id="btnConnMode">🔗 Соединить</button>
  <button class="btn-action btn-glass"   onclick="showAssignModal()">📍 Назначить жилы</button>
</div>

<!-- ФИЛЬТРЫ -->
<div class="filters">
  <button class="filter-btn active" data-f="all"     onclick="applyFilter('all',this)">Все</button>
  <button class="filter-btn"        data-f="free"    onclick="applyFilter('free',this)">🟢 Свободные</button>
  <button class="filter-btn"        data-f="used"    onclick="applyFilter('used',this)">🔵 Используемые</button>
  <button class="filter-btn"        data-f="damaged" onclick="applyFilter('damaged',this)">🔴 Повреждённые</button>
  <button class="filter-btn"        data-f="transit" onclick="applyFilter('transit',this)">⚡ Транзит</button>
</div>

<!-- ПОДСКАЗКА РЕЖИМА СОЕДИНЕНИЯ -->
<div id="connHint">
  🔗 Режим соединения: нажмите первую жилу, затем вторую
  <button onclick="toggleConnMode()" style="margin-left:10px;background:rgba(239,68,68,0.3);border:none;border-radius:8px;color:#fca5a5;padding:2px 8px;cursor:pointer;">Отмена</button>
</div>

<!-- СХЕМА МУФТЫ -->
<div class="schema-wrap" id="schemaWrap">
  <div style="display:flex;align-items:center;justify-content:center;height:200px;">
    <div class="spinner"></div>
  </div>
</div>

<!-- TOAST -->
<div id="toastWrap"></div>

<!-- ═══════════════════ МОДАЛКИ ══════════════════════════ -->

<!-- Добавить сторону -->
<div class="overlay" id="modalAddSide">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title" id="sideModalTitle">Добавить сторону</div>
    <input type="hidden" id="editSideId">
    <div class="form-group">
      <label class="form-label">Название направления</label>
      <input class="form-input" id="sideName" placeholder="Например: Дом А, Муфта-3">
    </div>
    <div class="form-group">
      <label class="form-label">Связать с муфтой (опционально)</label>
      <select class="form-input form-select" id="sideLinkedJoint">
        <option value="">— Не связана —</option>
      </select>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px;">
      <button class="btn-action btn-glass" style="flex:1;" onclick="closeModal('modalAddSide')">Отмена</button>
      <button class="btn-action btn-primary" style="flex:1;" onclick="saveSide()">Сохранить</button>
    </div>
  </div>
</div>

<!-- Добавить кабель -->
<div class="overlay" id="modalAddCable">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">Добавить кабель</div>
    <div class="form-group">
      <label class="form-label">Название кабеля</label>
      <input class="form-input" id="cableName" placeholder="Например: Кабель к ТП-7">
    </div>
    <div class="form-group">
      <label class="form-label">Тип кабеля (жил)</label>
      <select class="form-input form-select" id="cableFiberCount">
        <option value="8">Core 8  (1 модуль × 8)</option>
        <option value="16">Core 16 (2 модуля × 8)</option>
        <option value="24">Core 24 (3 модуля × 8)</option>
        <option value="48" selected>Core 48 (6 модулей × 8)</option>
        <option value="96">Core 96 (12 модулей × 8)</option>
      </select>
    </div>
    <div id="cablePreview" style="padding:10px;background:rgba(255,255,255,0.07);border-radius:12px;margin-bottom:12px;">
      <div style="color:rgba(255,255,255,0.6);font-size:0.8rem;">Будет создано: <b style="color:#fff;">6 модулей × 8 жил = 48 жил</b></div>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-action btn-glass" style="flex:1;" onclick="closeModal('modalAddCable')">Отмена</button>
      <button class="btn-action btn-success" style="flex:1;" onclick="saveCable()">✅ Создать</button>
    </div>
  </div>
</div>

<!-- Назначить жилы на сторону -->
<div class="overlay" id="modalAssign">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">Назначить жилы на сторону</div>
    <div class="form-group">
      <label class="form-label">Сторона (направление)</label>
      <select class="form-input form-select" id="assignSideSelect" onchange="renderFiberGrid()"></select>
    </div>
    <div class="form-group">
      <label class="form-label">Кабель</label>
      <select class="form-input form-select" id="assignCableSelect" onchange="renderFiberGrid()"></select>
    </div>
    <div class="form-group">
      <label class="form-label">Выберите жилы <span id="assignCount" style="color:#2F9BEB;">(0 выбрано)</span></label>
      <div id="fiberGrid"></div>
    </div>
    <div style="display:flex;gap:8px;margin-top:8px;">
      <button class="btn-action btn-glass" style="flex:1;" onclick="closeModal('modalAssign')">Отмена</button>
      <button class="btn-action btn-primary" style="flex:1;" onclick="saveAssign()">Назначить</button>
    </div>
  </div>
</div>

<!-- Детали жилы (редактирование) -->
<div class="overlay" id="modalFiber">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title" id="fiberModalTitle">Жила</div>
    <input type="hidden" id="editFiberId">
    <div class="form-group">
      <label class="form-label">Статус</label>
      <select class="form-input form-select" id="fiberStatus">
        <option value="free">🟢 Свободная</option>
        <option value="used">🔵 Используется</option>
        <option value="damaged">🔴 Повреждённая</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Заметки (клиент, адрес...)</label>
      <input class="form-input" id="fiberNotes" placeholder="Клиент Иванов, кв.45">
    </div>
    <div class="form-group">
      <button class="btn-action btn-glass" style="width:100%;" onclick="traceFromFiber(document.getElementById('editFiberId').value)">
        🔍 Трассировать сигнал
      </button>
    </div>
    <div style="display:flex;gap:8px;">
      <button class="btn-action btn-glass" style="flex:1;" onclick="closeModal('modalFiber')">Отмена</button>
      <button class="btn-action btn-primary" style="flex:1;" onclick="saveFiber()">Сохранить</button>
    </div>
  </div>
</div>

<!-- Трассировка -->
<div class="overlay" id="modalTrace">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">🔍 Трассировка сигнала</div>
    <div id="traceResult"></div>
    <button class="btn-action btn-glass" style="width:100%;margin-top:12px;" onclick="closeModal('modalTrace');clearHighlight();">Закрыть</button>
  </div>
</div>

<!-- Печать / Экспорт -->
<div class="overlay" id="modalPrint">
  <div class="sheet">
    <div class="sheet-handle"></div>
    <div class="sheet-title">🖨️ Печать схемы</div>
    <p style="color:rgba(255,255,255,0.7);font-size:0.85rem;margin-bottom:16px;">
      Откроется диалог печати браузера. Схема будет распечатана в текущем виде.
    </p>
    <div style="display:flex;gap:8px;">
      <button class="btn-action btn-glass" style="flex:1;" onclick="closeModal('modalPrint')">Отмена</button>
      <button class="btn-action btn-primary" style="flex:1;" onclick="window.print();closeModal('modalPrint');">Печать</button>
    </div>
  </div>
</div>

<script>
// ══════════════════════════════════════════════════════════
//  КОНФИГУРАЦИЯ
// ══════════════════════════════════════════════════════════
const BASE = (() => {
    const p = window.location.pathname;
    const parts = p.split('/');
    parts.pop(); // убираем filename
    return parts.join('/') + '/';
})();
const API = {
    getJoint      : BASE + 'getJoint.php',
    manageSide    : BASE + 'manageSide.php',
    addCable      : BASE + 'addCable.php',
    assignFibers  : BASE + 'assignFibers.php',
    saveConn      : BASE + 'saveConnection.php',
    updateFiber   : BASE + 'updateFiber.php',
    trace         : BASE + 'trace.php',
};

// Получаем joint_id из URL (?id=X) или от родителя
const urlParams  = new URLSearchParams(window.location.search);
let   JOINT_ID   = parseInt(urlParams.get('id') || window.JOINT_ID || 0);

let jointData    = null;   // полные данные муфты
let connMode     = false;  // режим соединения
let connFirst    = null;   // первая выбранная жила {id, el}
let activeFilter = 'all';
let selectedFibers = new Set(); // для назначения

// ══════════════════════════════════════════════════════════
//  ЗАГРУЗКА ДАННЫХ
// ══════════════════════════════════════════════════════════
async function loadJoint() {
    if (!JOINT_ID) {
        document.getElementById('schemaWrap').innerHTML =
            '<div style="padding:40px;text-align:center;color:rgba(255,255,255,0.5);">ID муфты не указан</div>';
        return;
    }
    try {
        const res  = await fetch(`${API.getJoint}?id=${JOINT_ID}`);
        const json = await res.json();
        if (!json.success) { toast('Ошибка загрузки: '+json.error,'err'); return; }
        jointData = json.data;
        renderAll();
    } catch(e) {
        toast('Ошибка сети','err');
        console.error(e);
    }
}

// ══════════════════════════════════════════════════════════
//  РЕНДЕР ВСЕГО
// ══════════════════════════════════════════════════════════
function renderAll() {
    if (!jointData) return;
    document.getElementById('hdrTitle').textContent = jointData.name;
    document.getElementById('hdrSub').textContent   = jointData.address || '';
    renderCableStats();
    renderSchema();
    populateSideSelects();
    populateLinkedJoints();
}

// ── Статистика кабелей ────────────────────────────────────
function renderCableStats() {
    const el = document.getElementById('cableStats');
    if (!jointData.cables.length) {
        el.innerHTML = '<div style="color:rgba(255,255,255,0.4);font-size:0.8rem;padding:10px;">Кабелей нет — добавьте кабель</div>';
        return;
    }
    el.innerHTML = jointData.cables.map(c => `
        <div class="stat-card">
            <div class="stat-card-name">📦 ${c.name}</div>
            <div style="font-size:0.7rem;color:rgba(255,255,255,0.5);margin-bottom:4px;">
                Core${c.fiber_count} · ${c.stats.total} жил
            </div>
            <div class="stat-pills">
                <span class="pill pill-free">🟢 ${c.stats.free} св.</span>
                <span class="pill pill-used">🔵 ${c.stats.used} исп.</span>
                ${c.stats.damaged ? `<span class="pill pill-dmg">🔴 ${c.stats.damaged} пов.</span>` : ''}
            </div>
        </div>
    `).join('');
}

// ── Основная схема ─────────────────────────────────────────
function renderSchema() {
    const wrap = document.getElementById('schemaWrap');
    wrap.innerHTML = '';

    const W = wrap.offsetWidth  || 360;
    const H = Math.max(420, jointData.sides.length * 80 + 160);
    wrap.style.height = H + 'px';

    const cx = W / 2;
    const cy = H / 2;
    const R  = Math.min(W, H) * 0.32; // радиус расположения сторон

    // SVG для линий соединений
    const svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
    svg.setAttribute('id','connSvg');
    svg.setAttribute('width', W);
    svg.setAttribute('height', H);
    svg.style.cssText = 'position:absolute;top:0;left:0;pointer-events:none;';
    wrap.appendChild(svg);

    // Центр муфты
    const cSize = 64;
    const center = document.createElement('div');
    center.className = 'joint-center glass';
    center.style.cssText = `left:${cx}px;top:${cy}px;width:${cSize}px;height:${cSize}px;`;
    center.innerHTML = `<div class="joint-center-name">${jointData.name.replace('Муфта','М.')}</div>
                        <div style="font-size:0.6rem;color:rgba(255,255,255,0.5);">${jointData.sides.length} ст.</div>`;
    wrap.appendChild(center);

    // Стороны
    const N = jointData.sides.length;
    jointData.sides.forEach((side, i) => {
        const angle = (i * 360 / N) - 90; // начинаем с верхушки
        const rad   = angle * Math.PI / 180;
        const sx    = cx + R * Math.cos(rad);
        const sy    = cy + R * Math.sin(rad);

        // DOM-узел стороны
        const node = document.createElement('div');
        node.className  = 'side-node';
        node.id         = `side-${side.id}`;
        node.style.left = sx + 'px';
        node.style.top  = sy + 'px';

        // Жилы стороны
        const fibersHtml = side.fibers.map(f => `
            <div class="fiber-dot status-${f.status}"
                 id="fd-${f.id}"
                 data-fid="${f.id}"
                 data-status="${f.status}"
                 title="${f.color} жила ${f.number}\n${f.notes||''}"
                 style="background:${f.color_hex};"
                 onclick="onFiberClick(event,${f.id})"
                 ondblclick="onFiberDblClick(${f.id})"
            ></div>
        `).join('');

        node.innerHTML = `
            <div class="side-name">${side.name}</div>
            ${side.linked_joint_name ? `<div style="font-size:0.6rem;color:rgba(255,255,255,0.4);text-align:center;margin-bottom:3px;">→ ${side.linked_joint_name}</div>` : ''}
            <div class="side-fibers-row">${fibersHtml || '<span style="font-size:0.65rem;color:rgba(255,255,255,0.3);">жил нет</span>'}</div>
            <div class="side-actions">
                <button class="side-act-btn" onclick="event.stopPropagation();editSide(${side.id})">✏️</button>
                <button class="side-act-btn" onclick="event.stopPropagation();deleteSide(${side.id})">🗑</button>
            </div>
        `;
        wrap.appendChild(node);

        // Линия от центра к стороне
        const line = document.createElementNS('http://www.w3.org/2000/svg','line');
        line.setAttribute('x1', cx); line.setAttribute('y1', cy);
        line.setAttribute('x2', sx); line.setAttribute('y2', sy);
        line.setAttribute('stroke','rgba(47,155,235,0.3)');
        line.setAttribute('stroke-width','1.5');
        line.setAttribute('stroke-dasharray','4,4');
        svg.appendChild(line);
    });

    // Линии соединений
    renderConnectionLines();
    applyFilter(activeFilter);
}

// ── Линии соединений на SVG ───────────────────────────────
function renderConnectionLines() {
    const svg  = document.getElementById('connSvg');
    const wrap = document.getElementById('schemaWrap');
    if (!svg || !wrap) return;

    // Удаляем старые линии соединений
    svg.querySelectorAll('.conn-line').forEach(el => el.remove());

    jointData.connections.forEach(conn => {
        const el1 = document.getElementById(`fd-${conn.fiber_id_in}`);
        const el2 = document.getElementById(`fd-${conn.fiber_id_out}`);
        if (!el1 || !el2) return;

        const r1 = el1.getBoundingClientRect();
        const rW = wrap.getBoundingClientRect();
        const r2 = el2.getBoundingClientRect();

        const x1 = r1.left - rW.left + r1.width/2;
        const y1 = r1.top  - rW.top  + r1.height/2;
        const x2 = r2.left - rW.left + r2.width/2;
        const y2 = r2.top  - rW.top  + r2.height/2;

        const path = document.createElementNS('http://www.w3.org/2000/svg','path');
        const mx   = (x1+x2)/2;
        const my   = (y1+y2)/2 - 30;
        path.setAttribute('d', `M${x1},${y1} Q${mx},${my} ${x2},${y2}`);
        path.setAttribute('stroke', conn.color_in || '#2F9BEB');
        path.setAttribute('stroke-width','2');
        path.setAttribute('fill','none');
        path.setAttribute('stroke-opacity','0.8');
        path.classList.add('conn-line');
        path.setAttribute('data-conn-id', conn.id);
        path.style.cursor = 'pointer';
        path.addEventListener('click', () => confirmDeleteConn(conn.id));
        svg.appendChild(path);
    });
}

// ══════════════════════════════════════════════════════════
//  КЛИК ПО ЖИЛЕ
// ══════════════════════════════════════════════════════════
function onFiberClick(e, fiberId) {
    e.stopPropagation();

    // Режим соединения
    if (connMode) {
        if (!connFirst) {
            // Первая жила
            connFirst = fiberId;
            const el = document.getElementById(`fd-${fiberId}`);
            if (el) el.classList.add('selected-conn');
            toast('Теперь нажмите вторую жилу', 'info');
        } else if (connFirst !== fiberId) {
            // Вторая жила — создаём соединение
            connectFibers(connFirst, fiberId);
            connFirst = null;
        }
        return;
    }

    // Обычный режим — открыть детали жилы
    const fiber = findFiber(fiberId);
    if (!fiber) return;
    document.getElementById('editFiberId').value = fiberId;
    document.getElementById('fiberModalTitle').textContent =
        `Жила: ${fiber.color} (модуль ${fiber.module_id})`;
    document.getElementById('fiberStatus').value = fiber.status;
    document.getElementById('fiberNotes').value  = fiber.notes || '';
    showOverlay('modalFiber');
}

// Двойной клик — трассировка
function onFiberDblClick(fiberId) {
    if (connMode) return;
    traceFromFiber(fiberId);
}

// ══════════════════════════════════════════════════════════
//  РЕЖИМ СОЕДИНЕНИЯ
// ══════════════════════════════════════════════════════════
function toggleConnMode() {
    connMode  = !connMode;
    connFirst = null;
    const btn  = document.getElementById('btnConnMode');
    const hint = document.getElementById('connHint');

    // Снимаем выделения
    document.querySelectorAll('.fiber-dot.selected-conn')
            .forEach(el => el.classList.remove('selected-conn'));

    if (connMode) {
        btn.style.background  = 'rgba(255,220,0,0.3)';
        btn.style.borderColor = 'rgba(255,220,0,0.5)';
        hint.style.display = 'block';
        toast('Выберите первую жилу', 'info');
    } else {
        btn.style.background  = '';
        btn.style.borderColor = '';
        hint.style.display = 'none';
    }
}

async function connectFibers(fid1, fid2) {
    try {
        const res  = await fetch(API.saveConn, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ action:'save', joint_id:JOINT_ID, fiber_id_in:fid1, fiber_id_out:fid2 })
        });
        const json = await res.json();
        if (json.success) {
            // Снимаем выделение
            document.querySelectorAll('.fiber-dot.selected-conn')
                    .forEach(el => el.classList.remove('selected-conn'));
            toast('Соединение создано ✅','ok');
            await loadJoint();
        } else {
            toast(json.error || 'Ошибка','err');
            document.querySelectorAll('.fiber-dot.selected-conn')
                    .forEach(el => el.classList.remove('selected-conn'));
        }
    } catch(e) { toast('Ошибка сети','err'); }
}

async function confirmDeleteConn(connId) {
    if (!confirm('Удалить это соединение?')) return;
    try {
        const res  = await fetch(API.saveConn, {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ action:'delete', id:connId })
        });
        const json = await res.json();
        if (json.success) { toast('Соединение удалено','ok'); await loadJoint(); }
        else toast(json.error,'err');
    } catch(e) { toast('Ошибка сети','err'); }
}

// ══════════════════════════════════════════════════════════
//  ТРАССИРОВКА
// ══════════════════════════════════════════════════════════
async function traceFromFiber(fiberId) {
    closeModal('modalFiber');
    const res  = await fetch(`${API.trace}?fiber_id=${fiberId}`);
    const json = await res.json();
    if (!json.success) { toast('Ошибка трассировки','err'); return; }

    // Подсвечиваем жилы на схеме
    clearHighlight();
    json.path.forEach(step => {
        const el = document.getElementById(`fd-${step.fiber_id}`);
        if (el) el.classList.add('highlighted');
    });

    // Отображаем результат
    const trEl = document.getElementById('traceResult');
    if (!json.path.length) {
        trEl.innerHTML = '<div style="color:rgba(255,255,255,0.5);text-align:center;padding:20px;">Маршрут не найден</div>';
    } else {
        trEl.innerHTML = json.path.map((step,i) => `
            ${i > 0 ? '<div class="trace-arrow">↕</div>' : ''}
            <div class="trace-step">
                <div class="trace-joint">📍 ${step.joint_name}</div>
                <div class="trace-fiber">
                    Кабель: ${step.cable_name} |
                    Модуль ${step.module_number}
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${step.module_hex};"></span> |
                    Жила ${step.fiber_number}
                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${step.fiber_hex};"></span>
                    ${step.fiber_color}
                    ${step.fiber_notes ? `<br><span style="color:#fde68a;">📝 ${step.fiber_notes}</span>` : ''}
                    ${step.side_name ? `<br>→ Сторона: ${step.side_name}` : ''}
                </div>
            </div>
        `).join('');
    }
    showOverlay('modalTrace');
}

function clearHighlight() {
    document.querySelectorAll('.fiber-dot.highlighted')
            .forEach(el => el.classList.remove('highlighted'));
}

// ══════════════════════════════════════════════════════════
//  ФИЛЬТР ЖИЛ
// ══════════════════════════════════════════════════════════
function applyFilter(f, btn) {
    activeFilter = f;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    document.querySelectorAll('.fiber-dot').forEach(el => {
        const status = el.dataset.status;
        let show = false;
        if (f === 'all')     show = true;
        if (f === 'free')    show = status === 'free';
        if (f === 'used')    show = status === 'used';
        if (f === 'damaged') show = status === 'damaged';
        if (f === 'transit') show = el.classList.contains('transit');
        el.classList.toggle('filtered-out', !show);
    });
}

// ══════════════════════════════════════════════════════════
//  УПРАВЛЕНИЕ СТОРОНАМИ
// ══════════════════════════════════════════════════════════
function showAddSideModal() {
    document.getElementById('sideModalTitle').textContent = 'Добавить сторону';
    document.getElementById('editSideId').value = '';
    document.getElementById('sideName').value   = '';
    document.getElementById('sideLinkedJoint').value = '';
    showOverlay('modalAddSide');
}

function editSide(sideId) {
    const side = jointData.sides.find(s => s.id == sideId);
    if (!side) return;
    document.getElementById('sideModalTitle').textContent = 'Редактировать сторону';
    document.getElementById('editSideId').value  = sideId;
    document.getElementById('sideName').value    = side.name;
    document.getElementById('sideLinkedJoint').value = side.linked_joint_id || '';
    showOverlay('modalAddSide');
}

async function saveSide() {
    const editId    = document.getElementById('editSideId').value;
    const name      = document.getElementById('sideName').value.trim();
    const linkedId  = document.getElementById('sideLinkedJoint').value;
    if (!name) { toast('Введите название','err'); return; }

    const body = editId
        ? { action:'edit', id:editId, name, linked_joint_id: linkedId||null }
        : { action:'add', joint_id:JOINT_ID, name, linked_joint_id: linkedId||null };

    const res  = await fetch(API.manageSide, {
        method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)
    });
    const json = await res.json();
    if (json.success) {
        toast(editId ? 'Сторона обновлена' : 'Сторона добавлена', 'ok');
        closeModal('modalAddSide');
        await loadJoint();
    } else toast(json.error, 'err');
}

async function deleteSide(sideId) {
    const side = jointData.sides.find(s => s.id == sideId);
    if (!confirm(`Удалить сторону "${side?.name}"? Жилы будут откреплены.`)) return;
    const res  = await fetch(API.manageSide, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'delete', id:sideId })
    });
    const json = await res.json();
    if (json.success) { toast('Сторона удалена','ok'); await loadJoint(); }
    else toast(json.error,'err');
}

// ══════════════════════════════════════════════════════════
//  ДОБАВИТЬ КАБЕЛЬ
// ══════════════════════════════════════════════════════════
function showAddCableModal() {
    document.getElementById('cableName').value = '';
    document.getElementById('cableFiberCount').value = '48';
    updateCablePreview();
    showOverlay('modalAddCable');
}

function updateCablePreview() {
    const cnt = parseInt(document.getElementById('cableFiberCount').value);
    const mod = cnt / 8;
    document.getElementById('cablePreview').innerHTML =
        `<div style="color:rgba(255,255,255,0.6);font-size:0.8rem;">
            Будет создано: <b style="color:#fff;">${mod} модулей × 8 жил = ${cnt} жил</b>
         </div>`;
}
document.getElementById('cableFiberCount').addEventListener('change', updateCablePreview);

async function saveCable() {
    const name       = document.getElementById('cableName').value.trim();
    const fiberCount = parseInt(document.getElementById('cableFiberCount').value);
    if (!name) { toast('Введите название кабеля','err'); return; }

    const res  = await fetch(API.addCable, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ joint_id:JOINT_ID, name, fiber_count:fiberCount })
    });
    const json = await res.json();
    if (json.success) {
        toast(`Кабель создан: ${json.module_count} модулей × 8 жил`, 'ok');
        closeModal('modalAddCable');
        await loadJoint();
    } else toast(json.error, 'err');
}

// ══════════════════════════════════════════════════════════
//  НАЗНАЧИТЬ ЖИЛЫ НА СТОРОНУ
// ══════════════════════════════════════════════════════════
function showAssignModal() {
    selectedFibers.clear();
    populateSideSelects();
    renderFiberGrid();
    showOverlay('modalAssign');
}

function populateSideSelects() {
    const sel = document.getElementById('assignSideSelect');
    if (!sel || !jointData) return;
    sel.innerHTML = jointData.sides.map(s =>
        `<option value="${s.id}">${s.name}</option>`
    ).join('') || '<option>Нет сторон</option>';

    const csel = document.getElementById('assignCableSelect');
    if (!csel) return;
    csel.innerHTML = jointData.cables.map(c =>
        `<option value="${c.id}">${c.name} (Core${c.fiber_count}) — свободно: ${c.stats.free}</option>`
    ).join('') || '<option>Нет кабелей</option>';
}

function populateLinkedJoints() {
    const sel = document.getElementById('sideLinkedJoint');
    if (!sel || !jointData) return;
    const opts = (jointData.all_joints || []).map(j =>
        `<option value="${j.id}">${j.name}</option>`
    ).join('');
    sel.innerHTML = '<option value="">— Не связана —</option>' + opts;
}

function renderFiberGrid() {
    selectedFibers.clear();
    document.getElementById('assignCount').textContent = '(0 выбрано)';

    const cableId = parseInt(document.getElementById('assignCableSelect')?.value);
    if (!cableId || !jointData) return;

    const cable = jointData.cables.find(c => c.id === cableId);
    if (!cable) return;

    const sideId = parseInt(document.getElementById('assignSideSelect')?.value);

    const grid = document.getElementById('fiberGrid');
    grid.innerHTML = cable.modules.map(mod => `
        <div class="module-row">
            <div class="module-label">
                <div class="module-tube" style="background:${mod.color_hex};"></div>
                ${mod.name} (${mod.color})
            </div>
            <div class="fiber-grid">
                ${mod.fibers.map(f => {
                    const isMine   = f.side_id === sideId;
                    const isOther  = f.side_id && f.side_id !== sideId;
                    const disabled = isOther ? 'already-used' : '';
                    const title    = isOther ? 'Назначена на другую сторону' : f.color;
                    return `
                        <div class="fiber-pick ${disabled}"
                             id="fp-${f.id}"
                             data-fid="${f.id}"
                             title="${title}"
                             style="background:${f.color_hex};"
                             onclick="${disabled ? '' : `toggleFiberPick(${f.id})`}">
                            <span class="fp-num">${f.number}</span>
                        </div>`;
                }).join('')}
            </div>
        </div>
    `).join('');
}

function toggleFiberPick(fid) {
    const el = document.getElementById(`fp-${fid}`);
    if (!el || el.classList.contains('already-used')) return;
    if (selectedFibers.has(fid)) {
        selectedFibers.delete(fid);
        el.classList.remove('selected');
    } else {
        selectedFibers.add(fid);
        el.classList.add('selected');
    }
    document.getElementById('assignCount').textContent = `(${selectedFibers.size} выбрано)`;
}

async function saveAssign() {
    if (!selectedFibers.size) { toast('Выберите жилы','err'); return; }
    const sideId = parseInt(document.getElementById('assignSideSelect').value);
    if (!sideId) { toast('Выберите сторону','err'); return; }

    const res  = await fetch(API.assignFibers, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ side_id:sideId, fiber_ids:[...selectedFibers] })
    });
    const json = await res.json();
    if (json.success) {
        toast(`${json.assigned} жил назначено`, 'ok');
        closeModal('modalAssign');
        await loadJoint();
    } else toast(json.error, 'err');
}

// ══════════════════════════════════════════════════════════
//  СОХРАНИТЬ ЖИЛУ
// ══════════════════════════════════════════════════════════
async function saveFiber() {
    const id     = document.getElementById('editFiberId').value;
    const status = document.getElementById('fiberStatus').value;
    const notes  = document.getElementById('fiberNotes').value.trim();
    const res    = await fetch(API.updateFiber, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id, status, notes })
    });
    const json = await res.json();
    if (json.success) {
        toast('Жила обновлена', 'ok');
        closeModal('modalFiber');
        await loadJoint();
    } else toast(json.error, 'err');
}

// ══════════════════════════════════════════════════════════
//  УТИЛИТЫ
// ══════════════════════════════════════════════════════════
function findFiber(fid) {
    for (const cable of jointData.cables) {
        for (const mod of cable.modules) {
            const f = mod.fibers.find(x => x.id == fid);
            if (f) return f;
        }
    }
    // Также ищем в сторонах
    for (const side of jointData.sides) {
        const f = side.fibers.find(x => x.id == fid);
        if (f) return f;
    }
    return null;
}

function showOverlay(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id)   { document.getElementById(id).classList.remove('show'); }
function showPrintModal() { showOverlay('modalPrint'); }

// Закрыть overlay по клику на фон
document.querySelectorAll('.overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov.id); });
});

function toast(msg, type='info', dur=3000) {
    const c = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = `toast-msg ${type}`;
    t.textContent = msg;
    c.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transform='translateY(8px)'; t.style.transition='all 0.3s'; setTimeout(()=>t.remove(),320); }, dur);
}

// Перерисовать линии после рендера DOM
function onResized() {
    if (jointData) setTimeout(renderConnectionLines, 100);
}
window.addEventListener('resize', onResized);

// ══════════════════════════════════════════════════════════
//  СТАРТ
// ══════════════════════════════════════════════════════════
loadJoint();
</script>
</body>
</html>
