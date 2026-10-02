<?php
require '../db.php';

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
}

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($pass, $row['password_hash'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['email']     = $email;
            header('Location: ../upload/');
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please enter email and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eve Fitzsimons</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: black;
            color: #e7e1e1;
            font-family: 'Loved by the King', cursive;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; }

        @keyframes heartbeat {
            0%   { transform: scale(1); }
            20%  { transform: scale(1.2); }
            40%  { transform: scale(0.9); }
            60%  { transform: scale(1); }
            100% { transform: scale(1); }
        }

        /* ── Nav ── */
        .site-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .nav-logo {
            font-family: 'Loved by the King', cursive;
            font-size: 1.6rem;
            color: #e7e1e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-heart {
            width: 28px; height: 28px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .nav-links { display: flex; gap: 2rem; }

        .nav-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-family: 'Loved by the King', cursive;
            font-size: 0.95rem;
            letter-spacing: 0.05em;
            transition: color 0.2s;
        }

        .nav-link:hover { color: white; }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }

        .hamburger span {
            display: block;
            width: 24px; height: 2px;
            background: white;
            border-radius: 2px;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            inset: 0;
            background: black;
            z-index: 600;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3rem;
        }

        .mobile-menu.open { display: flex; }

        .mobile-link {
            font-family: 'Loved by the King', cursive;
            font-size: 2.5rem;
            color: #e7e1e1;
            text-decoration: none;
        }

        .mobile-close {
            position: absolute;
            top: 1.5rem; right: 1.5rem;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }

        @media (max-width: 640px) {
            .nav-links { display: none; }
            .hamburger { display: flex; }
            .site-nav { padding: 1rem 1.25rem; }
        }

        /* ── Login form ── */
        .login-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6rem 1.5rem 4rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 3rem 2.5rem;
            background: rgba(255,255,255,0.02);
        }

        .login-title {
            font-family: 'Loved by the King', cursive;
            font-size: 2.6rem;
            color: #e7e1e1;
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-title-heart {
            width: 36px; height: 36px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .error-msg {
            background: rgba(200,50,50,0.15);
            border: 1px solid rgba(200,50,50,0.4);
            color: #ff9999;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
        }

        .form-group { margin-bottom: 1.4rem; }

        label {
            display: block;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.5rem;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 6px;
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(255,255,255,0.5);
        }

        button[type="submit"] {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.9rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.3rem;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.25s, border-color 0.25s;
        }

        button[type="submit"]:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.5);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: rgba(255,255,255,0.35);
            font-size: 0.9rem;
            letter-spacing: 0.04em;
            transition: color 0.2s;
        }

        .back-link a:hover { color: rgba(255,255,255,0.7); }

        footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 2rem;
            text-align: center;
            color: rgba(255,255,255,0.3);
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    <nav class="site-nav">
        <a href="../" class="nav-logo">
            Eve Fitzsimons
            <img src="../pictures/one heart.png" class="logo-heart" alt="">
        </a>
        <div class="nav-links">
            <a href="../" class="nav-link">Home</a>
            <a href="../gallery/" class="nav-link">Gallery</a>
        </div>
        <button class="hamburger" aria-label="Menu" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" onclick="toggleMenu()">&times;</button>
        <a href="../" class="mobile-link">Home</a>
        <a href="../gallery/" class="mobile-link">Gallery</a>
    </div>

    <div class="login-wrap">
        <div class="login-card">
            <h1 class="login-title">
                Login
                <img src="../pictures/one heart.png" class="login-title-heart" alt="">
            </h1>

            <?php if ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit">Enter</button>
            </form>

            <div class="back-link">
                <a href="../">← Back to Home</a>
            </div>
        </div>
    </div>

    <footer>&copy; 2026 Eve Fitzsimons</footer>

    <script>
        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('open');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('open') ? 'hidden' : '';
        }
    </script>
</body>
</html>
