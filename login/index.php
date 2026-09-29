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
    <title>Login - Eve Fitz Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: black;
            color: white;
            font-family: 'Loved by the King', cursive;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        a { text-decoration: none; }

        .nav-buttons {
            position: fixed;
            top: 20px; right: 20px;
            display: flex;
            gap: 10px;
        }

        .nav-button {
            background-color: transparent;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            width: 120px; height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Loved by the King', cursive;
        }

        .nav-button::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 40px; height: 40px;
            background-image: url('../pictures/other heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: background-image 0.3s ease;
        }

        .nav-button:hover::before {
            background-image: url('../pictures/one heart.png');
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
        }

        h1 {
            font-size: 2.5em;
            color: #e7e1e1;
            text-align: center;
            margin-bottom: 2rem;
        }

        h1::after {
            content: '';
            display: inline-block;
            vertical-align: middle;
            width: 50px; height: 50px;
            background-image: url('../pictures/one heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-left: 8px;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .form-group { margin-bottom: 1.5rem; }

        label {
            display: block;
            font-size: 1.1em;
            margin-bottom: 0.4rem;
            color: rgba(255,255,255,0.8);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(255,255,255,0.7);
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.85rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.2em;
            cursor: pointer;
            border-radius: 8px;
            margin-top: 0.5rem;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover { background: rgba(255,255,255,0.2); }

        .error-message {
            background: rgba(200,50,50,0.2);
            border: 1px solid rgba(200,50,50,0.5);
            color: #ff9999;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1em;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: rgba(255,255,255,0.5);
            font-size: 0.95em;
            transition: color 0.3s;
        }

        .back-link a:hover { color: white; }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../"><button class="nav-button">Home</button></a>
        <a href="../gallery/"><button class="nav-button">Gallery</button></a>
    </div>

    <div class="login-container">
        <h1>Login</h1>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Enter</button>
        </form>

        <div class="back-link">
            <a href="../">← Back to Home</a>
        </div>
    </div>
</body>
</html>
