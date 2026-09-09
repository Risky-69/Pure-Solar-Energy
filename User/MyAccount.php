<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require_once "../db.php";

// $host    = 'localhost';
// $db      = 'puresolarenergy';
// $user    = 'root';
// $pass    = ''; 
// $charset = 'utf8mb4';

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

// Handle Logout action
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? '';

// If user_id isn't in session yet, look it up
if (!$userId && !empty($username)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $userData = $stmt->fetch();
    if ($userData) {
        $userId = $userData['id'] ?? $userData['user_id'] ?? null;
        $_SESSION['user_id'] = $userId;
    }
}

// Fetch user profile info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? OR user_id = ?");
$stmt->execute([$userId, $userId]);
$currentUser = $stmt->fetch() ?: ['username' => $username, 'email' => 'N/A'];

// Fetch user orders (Safely handle case if orders table doesn't exist yet)
$orders = [];
try {
    $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $orderStmt->execute([$userId]);
    $orders = $orderStmt->fetchAll();
} catch (PDOException $e) {
    // Orders table might not be created yet; fallback to empty array
    $orders = [];
}

// Fetch user vouchers (Safely handle case if vouchers table doesn't exist yet)
$vouchers = [];
try {
    $voucherStmt = $pdo->prepare("SELECT * FROM vouchers WHERE user_id = ? AND status = 'active'");
    $voucherStmt->execute([$userId]);
    $vouchers = $voucherStmt->fetchAll();
} catch (PDOException $e) {
    // Vouchers table might not be created yet; fallback to empty array
    $vouchers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #f4f6f9; color: #333333; margin: 0; padding: 0; }
        
        /* Top Navigation Bar */
        .topbar { background: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 1px solid #e2e8f0; }
        .topbar-left { display: flex; align-items: center; gap: 20px; font-weight: 600; color: #1e293b; }
        .topbar-right { display: flex; align-items: center; gap: 20px; }
        .btn-logout { background: #fee2e2; color: #991b1b; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 0.9rem; }
        .btn-logout:hover { background: #fecaca; }
        .avatar { width: 35px; height: 35px; border-radius: 50%; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        /* Hero Banner Section */
        .hero-banner { background: #6366f1; padding: 40px 40px 80px 40px; color: white; }
        .hero-banner h1 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .hero-banner p { margin: 5px 0 0 0; opacity: 0.9; font-size: 0.95rem; }

        /* Main Layout Container */
        .content-container { max-width: 1200px; margin: -40px auto 40px auto; padding: 0 30px; display: flex; flex-direction: column; gap: 30px; }
        
        /* Profile Summary Card */
        .profile-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); display: flex; justify-content: space-between; align-items: center; }
        .profile-info h3 { margin: 0; color: #0f172a; font-size: 1.2rem; }
        .profile-info p { margin: 5px 0 0 0; color: #64748b; font-size: 0.9rem; }

        /* Card Sections */
        .card-box { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
        .card-box h2 { margin-top: 0; font-size: 1.2rem; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }

        /* Voucher Grid */
        .voucher-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .voucher-item { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 15px; position: relative; }
        .voucher-item h4 { margin: 0 0 5px 0; color: #4f46e5; font-size: 1rem; }
        .voucher-item p { margin: 0; font-size: 0.85rem; color: #64748b; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
        tr:hover { background-color: #f8fafc; }
        .empty-row { text-align: center; color: #94a3b8; padding: 30px; }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <div class="topbar">
        <div class="topbar-left">
            <span>Pure Solar Energy &mdash; Account Dashboard</span>
        </div>
        <div class="topbar-right">
            <div class="avatar"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></div>
            <a href="my account.php?logout=true" class="btn-logout">Log Out</a>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="hero-banner">
        <h1>Welcome back, <?= htmlspecialchars($currentUser['username'] ?? 'Customer') ?>!</h1>
        <p>Manage your orders, active vouchers, and personal information from your account hub.</p>
    </div>

    <!-- Main Content Grid -->
    <div class="content-container">
        
        <!-- Account Summary -->
        <div class="profile-card">
            <div class="profile-info">
                <h3>Account Details</h3>
                <p><strong>Username:</strong> <?= htmlspecialchars($currentUser['username'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($currentUser['email'] ?? 'N/A') ?></p>
            </div>
            <div>
                <a href="my account.php?logout=true" class="btn-logout" style="padding: 10px 20px;">Logout of Account</a>
            </div>
        </div>

        <!-- My Vouchers Section -->
        <div class="card-box">
            <h2>My Vouchers & Discounts</h2>
            <?php if (empty($vouchers)): ?>
                <div class="empty-row" style="padding: 20px;">You currently have no active vouchers available.</div>
            <?php else: ?>
                <div class="voucher-grid">
                    <?php foreach ($vouchers as $voucher): ?>
                        <div class="voucher-item">
                            <h4><?= htmlspecialchars($voucher['code']) ?></h4>
                            <p><?= htmlspecialchars($voucher['description'] ?? 'Discount Voucher') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Orders Section -->
        <div class="card-box">
            <h2>Order History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="4" class="empty-row">You haven't placed any orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['id'] ?? $order['order_id']) ?></td>
                                <td><?= htmlspecialchars($order['created_at'] ?? 'N/A') ?></td>
                                <td>$<?= number_format($order['total_amount'] ?? 0, 2) ?></td>
                                <td><span style="font-weight: 600; color: #059669;"><?= htmlspecialchars($order['status'] ?? 'Processing') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>