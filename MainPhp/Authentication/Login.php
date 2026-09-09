<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. INCLUDE YOUR DATABASE CONNECTION FILE HERE
// Adjust relative path if your db connection file is located elsewhere
// Correct relative path to root db.php
if (file_exists('../../db.php')) {
    require_once '../../db.php';
} elseif (file_exists('../db.php')) {
    require_once '../db.php';
}

$message = "";
$message_type = "";

$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Get and sanitize inputs
    $username        = trim($_POST["username"] ?? "");
    $email           = trim($_POST["email"] ?? "");
    $password        = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    
    /* =========================================
       1. REQUIRED FIELD VALIDATION
    ========================================= */
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
        $message_type = "error";
        
    /* =========================================
       2. EMAIL FORMAT VALIDATION
    ========================================= */
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";

    /* =========================================
       3. PASSWORD MATCH VALIDATION
    ========================================= */
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $message_type = "error";

    } else {
        
        /* =========================================
           4. FIND USER & VERIFY CREDENTIALS
        ========================================= */
        if (isset($conn)) {
            // FIXED: Table name updated to 'users' and removed non-existent columns (full_name, role)
            $stmt = $conn->prepare(
                "SELECT id, username, email, password
                 FROM `users`
                 WHERE username = ? AND email = ?"
            );
            
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {
                    // $_SESSION["user_id"]  = $user["id"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["email"]    = $user["email"];

                    header("Location: ../../User/UserIndex.php");
                    exit;
                } else {
                    $message = "Invalid login credentials.";
                    $message_type = "error";
                }
            } else {
                $message = "Invalid login credentials.";
                $message_type = "error";
            }
            $stmt->close();
        } else {
            $message = "Database connection missing. Check your db.php include path.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Pure Solar Energy</title>
    <link rel="stylesheet" href="Login.css">
</head>
<body>

<div class="login-card">
    <h2>Sign In</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Username or Email</label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>

    <main class="auth-container">
        <div class="modal-box standalone-box">
            <h2>Log In</h2>
            <p class="modal-subtitle">Access your PureSolar energy dashboard account.</p>

            <?php if (!empty($message)): ?>
                <p style="color: <?= $message_type === 'error' ? '#f44336' : '#4CAF50' ?>; margin-bottom: 15px; text-align: center; font-weight: bold;">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="Login.php" onsubmit="return validateLogin()">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Enter your username" required autocomplete="username">
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Enter your email" required autocomplete="email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                </div>

                <div class="input-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-submit">Proceed</button>
            </form>

            <p style="margin-top: 15px; text-align: center; color: #8ca3ba; font-size: 0.9rem;">
                Don't have an account? <a href="SignUp.php" style="color: #ff9900;">Sign Up</a>
            </p>

            <!-- RETURN TO HOME BUTTON -->
            <div class="return-home-container">
                <a href="/index.php" class="btn-return-home">
                    &larr; Return to Home
                </a>
            </div>
        </div>
    </main>

    <script>
    function validateLogin() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        const hasUpperCase = /[A-Z]/.test(password);
        const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        const hasNumber = /[0-9]/.test(password);

        if (!hasUpperCase) {
            alert('Password must contain at least one uppercase letter (A-Z).');
            return false;
        }

        if (!hasSpecialChar) {
            alert('Password must contain at least one special character.');
            return false;
        }

        if (!hasNumber) {
            alert('Password must contain at least one number (0-9).');
            return false;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }

        return true;
    }
    </script>
</body>
</html>