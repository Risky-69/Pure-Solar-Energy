<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in as admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: Admin.php");
    exit();
}

// SPECIFIC ADMIN CREDENTIALS
define('ADMIN_USER', 'admin@puresolar.com');
define('ADMIN_PASS', 'SolarAdmin_1');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === ADMIN_USER && $password === ADMIN_PASS) {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_user'] = $email;
        header("Location: Admin.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pure Solar Energy</title>
    <style>
        :root {
            --bg-main: #070d18;
            --bg-card: #0d1726;
            --border-card: #1b2a40;
            --solar-cyan: #00f2fe;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 30px;
            width: 360px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .login-title {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
            color: var(--solar-cyan);
            font-size: 1.4rem;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            background: #111a2e;
            border: 1px solid var(--border-card);
            border-radius: 6px;
            color: #fff;
            box-sizing: border-box;
            outline: none;
        }

        input:focus {
            border-color: var(--solar-cyan);
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
            border: none;
            border-radius: 6px;
            font-weight: bold;
            color: #000;
            cursor: pointer;
            margin-top: 10px;
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2 class="login-title">☀️ Admin Portal</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="AdminLogin.php">
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" required placeholder="admin@puresolar.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-submit">Login as Admin</button>
    </form>
</div>

</body>
</html>