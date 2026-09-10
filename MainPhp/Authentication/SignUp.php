<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$status  = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Pure Solar Energy</title>
    
    <!-- Root-relative paths matching Login.php -->
      <link rel="stylesheet" href="Login.css">

</head>
<body>
 

    <main class="auth-container">
        <div class="modal-box standalone-box">
            <h2>Sign Up</h2>
            <p class="modal-subtitle">Create an account to access your PureSolar energy dashboard.</p>

            <?php if ($status === 'registered'): ?>
                <p style="color: #4CAF50; margin-bottom: 15px; text-align: center; font-weight: bold;">Account registered successfully! Please log in.</p>
            <?php elseif ($status === 'error'): ?>
                <p style="color: #f44336; margin-bottom: 15px; text-align: center; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST" action="../Function.php" class="auth-form" onsubmit="return validateSignUp()">
                <input type="hidden" name="action" value="signup">

                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">👁️</button>

                </div>

                <div class="input-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">👁️</button>
                    
                </div>

                <button type="submit" class="btn-submit">Proceed</button>
            </form>

            <p style="margin-top: 15px; text-align: center; color: #8ca3ba; font-size: 0.9rem;">
                Already have an account? <a href="Login.php" style="color: #ff9900;">Log In</a>
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
function validateSignUp() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Check password complexity (At least 1 uppercase letter and 1 special character)
    const hasUpperCase = /[A-Z]/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
    const hasNumber = /[123456789]/.test(password);

    if (!hasUpperCase) {
        alert('Password must contain at least one uppercase letter (A-Z).');
        return false;
    }

    if (!hasSpecialChar) {
        alert('Password must contain at least one special character (e.g., !@#$%^&*).');
        return false;
    }

    if (!hasNumber) {
        alert('Password must contain at least one Number .');
        return false;
    }

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return false;
    }

    return true;
}

// Extract submitted inputs
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Check if inputs contain at least 1 uppercase letter and 1 special character
if (!preg_match('/[A-Z]/', $password)) {
    header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Password must contain at least one uppercase letter."));
    exit;
}

if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\\\|,.<>\/?]/', $password)) {
    header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Password must contain at least one special character."));
    exit;
}

if (!preg_match('/123456789/', $password)) {
    header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Password must contain at least one number."));
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Passwords do not match."));
    exit;
}
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    </script>
</body>
</html>