<?php
// ============================================================
//  login.php — Страница входа (Glassmorphism iOS стиль)
// ============================================================
session_start();

// Если уже вошёл — редирект на карту
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

// Учётные данные (можно вынести в config)
define('LOGIN_USER', 'admin');
define('LOGIN_PASS', 'admin123');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === LOGIN_USER && $p === LOGIN_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username']  = $u;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TT Map — Вход</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" href="tt_png.png" type="image/x-icon">
    <style>
        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f2557 0%, #2F9BEB 50%, #0f2557 100%);
            background-size: 400% 400%;
            animation: bgShift 8s ease infinite;
            overflow: hidden;
        }

        /* Анимация фона */
        @keyframes bgShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Декоративные пузыри */
        .bubble {
            position: fixed;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: float linear infinite;
            pointer-events: none;
        }
        @keyframes float {
            0%   { transform: translateY(110vh) scale(0.8); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-10vh) scale(1.1); opacity: 0; }
        }

        /* ── Карточка логина (Glassmorphism) ── */
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 40px 36px 32px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow:
                0 8px 40px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255,255,255,0.3);
            animation: cardIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            position: relative;
            z-index: 10;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(30px) scale(0.95); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Лого */
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo img {
            width: 72px;
            height: 72px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }
        .login-logo h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.4rem;
            margin: 10px 0 2px;
            letter-spacing: 0.3px;
        }
        .login-logo p {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            margin: 0;
        }

        /* Поля формы */
        .glass-input-wrap {
            position: relative;
            margin-bottom: 16px;
        }
        .glass-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            z-index: 2;
        }
        .glass-input {
            width: 100%;
            padding: 13px 16px 13px 42px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            color: #fff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s;
            backdrop-filter: blur(8px);
        }
        .glass-input::placeholder { color: rgba(255,255,255,0.45); }
        .glass-input:focus {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 0 3px rgba(47, 155, 235, 0.35);
        }

        /* Кнопка показать пароль */
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            transition: color 0.2s;
            z-index: 2;
        }
        .toggle-pass:hover { color: #fff; }

        /* Кнопка войти */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2F9BEB, #1a7acc);
            border: none;
            border-radius: 14px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(47,155,235,0.4);
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(47,155,235,0.5);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        /* Ошибка */
        .login-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 12px;
            color: #fca5a5;
            padding: 10px 14px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake 0.35s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%,60%  { transform: translateX(-6px); }
            40%,80%  { transform: translateX(6px); }
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.4);
            font-size: 0.78rem;
        }
    </style>
</head>
<body>

<!-- Декоративные пузыри -->
<div class="bubble" style="width:120px;height:120px;left:10%;animation-duration:12s;animation-delay:0s;"></div>
<div class="bubble" style="width:60px;height:60px;left:30%;animation-duration:9s;animation-delay:2s;"></div>
<div class="bubble" style="width:90px;height:90px;left:60%;animation-duration:14s;animation-delay:1s;"></div>
<div class="bubble" style="width:40px;height:40px;left:80%;animation-duration:10s;animation-delay:3s;"></div>
<div class="bubble" style="width:150px;height:150px;left:75%;animation-duration:16s;animation-delay:0.5s;"></div>

<!-- Карточка логина -->
<div class="login-card">

    <!-- Лого -->
    <div class="login-logo">
        <img src="tt_png.png" alt="TT Map">
        <h4>TT Map</h4>
        <p>ISP GIS — Интерактивная карта сети</p>
    </div>

    <!-- Ошибка -->
    <?php if ($error): ?>
    <div class="login-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- Форма -->
    <form method="POST" id="loginForm">

        <div class="glass-input-wrap">
            <i class="bi bi-person-fill"></i>
            <input
                type="text"
                name="username"
                class="glass-input"
                placeholder="Логин"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                autocomplete="username"
                required>
        </div>

        <div class="glass-input-wrap">
            <i class="bi bi-lock-fill"></i>
            <input
                type="password"
                name="password"
                id="passField"
                class="glass-input"
                placeholder="Пароль"
                autocomplete="current-password"
                required>
            <button type="button" class="toggle-pass" id="togglePass">
                <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
        </div>

        <button type="submit" class="btn-login" id="btnLogin">
            <i class="bi bi-box-arrow-in-right me-2"></i>Войти
        </button>

    </form>

    <div class="login-footer">
        © TT Map ISP GIS
    </div>

</div>

<script>
    // Показать/скрыть пароль
    document.getElementById('togglePass').addEventListener('click', () => {
        const f = document.getElementById('passField');
        const e = document.getElementById('eyeIcon');
        if (f.type === 'password') {
            f.type = 'text';
            e.className = 'bi bi-eye-slash';
        } else {
            f.type = 'password';
            e.className = 'bi bi-eye';
        }
    });

    // Spinner при сабмите
    document.getElementById('loginForm').addEventListener('submit', () => {
        const btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Вход...';
    });
</script>

</body>
</html>
