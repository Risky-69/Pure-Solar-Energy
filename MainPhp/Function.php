<?php
// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configure secure session cookie settings
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
$host    = 'localhost';
$db      = 'puresolarenergy';
$user    = 'root';
$pass    = 'Password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // 1. SIGNUP LOGIC
    // ==========================================
    if ($action === 'signup') {
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("All fields are required."));
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Invalid email format."));
            exit();
        }

        if (strlen($password) < 8) {
            header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Password must be at least 8 characters long."));
            exit();
        }

        if ($password !== $confirmPassword) {
            header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Passwords do not match."));
            exit();
        }

        $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([
            'username' => $username,
            'email'    => $email
        ]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            if ($existingUser['username'] === $username) {
                header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Username is already taken."));
            } else {
                header("Location: Authentication/SignUp.php?status=error&message=" . urlencode("Email is already registered."));
            }
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        
        if ($stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword])) {
            header("Location: Authentication/Login.php?status=registered");
            exit();
        }

// ==========================================
    // LOGIN LOGIC
    // ==========================================
    } elseif ($action === 'login') {
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['eMail'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Check if any field is empty
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            header("Location: Authentication/Login.php?status=error&message=" . urlencode("Please fill in all fields."));
            exit();
        }

        // Email format check
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: Authentication/Login.php?status=error&message=" . urlencode("Invalid email format."));
            exit();
        }

        // Confirm Password match check
        if ($password !== $confirmPassword) {
            header("Location: Authentication/Login.php?status=error&message=" . urlencode("Passwords do not match."));
            exit();
        }

        // Database lookup
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND email = :email LIMIT 1");
        $stmt->execute([
            'username' => $username,
            'email'    => $email
        ]);
        $user = $stmt->fetch();

        // Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email']    = $user['email'];

            // Redirect to User/UserIndex.php upon successful login
            header("Location: ../User/UserIndex.php");
            exit();
        } else {
            header("Location: Authentication/Login.php?status=error&message=" . urlencode("Invalid credentials."));
            exit();
        }
    }
}
?>