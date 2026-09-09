<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in or is not an admin
if (!isset($_SESSION['username']) || (($_SESSION['role'] ?? '') !== 'admin' && strtolower($_SESSION['username']) !== 'admin')) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

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

// Fetch overview metrics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCartItems = $pdo->query("SELECT SUM(quantity) FROM cart")->fetchColumn() ?: 0;
$totalRevenueResult = $pdo->query("SELECT SUM(c.quantity * p.price) AS revenue FROM cart c JOIN products p ON c.product_id = p.product_id")->fetch();
$totalCartValue = $totalRevenueResult['revenue'] ?? 0;

// Fetch detailed data lists
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$products = $pdo->query("SELECT * FROM products")->fetchAll();
$activeCarts = $pdo->query("
    SELECT u.username, p.product_name, c.quantity, p.price, (c.quantity * p.price) AS total 
    FROM cart c 
    JOIN users u ON c.user_id = u.id 
    JOIN products p ON c.product_id = p.product_id
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #f4f6f9; color: #333333; margin: 0; padding: 0; }
        
        /* Top Navigation Bar */
        .topbar { background: #ffffff; display: flex; justify-content: space-between; align-items: center; padding: 15px 30px; border-bottom: 1px solid #e2e8f0; }
        .topbar-left { display: flex; align-items: center; gap: 20px; }
        .search-box { position: relative; }
        .search-box input { padding: 8px 15px 8px 35px; border: 1px solid #cbd5e1; border-radius: 6px; width: 250px; font-size: 0.9rem; background: #f8fafc; }
        .topbar-right { display: flex; align-items: center; gap: 20px; }
        .avatar { width: 35px; height: 35px; border-radius: 50%; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }

        /* Hero Banner Section */
        .hero-banner { background: #6366f1; padding: 40px 40px 90px 40px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .hero-banner h1 { margin: 0; font-size: 1.8rem; font-weight: 600; }
        .btn-create { background: white; color: #1e293b; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

        /* Metric Summary Cards */
        .metrics-container { max-width: 1300px; margin: -50px auto 30px auto; padding: 0 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
        .metric-card { background: white; border-radius: 10px; padding: 20px 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); position: relative; border-top: 3px solid #6366f1; }
        .metric-card h3 { margin: 0; font-size: 0.85rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .metric-card .value { font-size: 1.8rem; font-weight: 700; color: #0f172a; margin: 10px 0 5px 0; }
        .metric-card .subtext { font-size: 0.85rem; color: #94a3b8; }

        /* Main Content Layout */
        .content-container { max-width: 1300px; margin: 0 auto 40px auto; padding: 0 30px; display: flex; flex-direction: column; gap: 30px; }
        .card-box { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); }
        .card-box h2 { margin-top: 0; font-size: 1.2rem; color: #1e293b; margin-bottom: 20px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid #e2e8f0; letter-spacing: 0.5px; }
        td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; }
        tr:hover { background-color: #f8fafc; }
        .empty-row { text-align: center; color: #94a3b8; padding: 30px; }
    </style>
</head>
<body>

    <!-- Top Bar Navbar -->
    <div class="topbar">
        <div class="topbar-left">
            <span style="font-size: 1.2rem; cursor: pointer;">&#9776;</span>
            <div class="search-box">
                <input type="text" placeholder="Search...">
            </div>
        </div>
        <div class="topbar-right">
            <span>🔔</span>
            <div class="avatar">A</div>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="hero-banner">
        <h1>Projects & System Dashboard</h1>
        <button class="btn-create">Create New Project</button>
    </div>

    <!-- Overview Metric Cards -->
    <div class="metrics-container">
        <div class="metric-card">
            <h3>Total Users</h3>
            <div class="value"><?= $totalUsers ?></div>
            <div class="subtext">Registered Accounts</div>
        </div>
        <div class="metric-card">
            <h3>Total Products</h3>
            <div class="value"><?= $totalProducts ?></div>
            <div class="subtext">Catalog Inventory</div>
        </div>
        <div class="metric-card">
            <h3>Active Cart Items</h3>
            <div class="value"><?= $totalCartItems ?></div>
            <div class="subtext">Items across active baskets</div>
        </div>
        <div class="metric-card">
            <h3>Cart Value Revenue</h3>
            <div class="value">$<?= number_format($totalCartValue, 2) ?></div>
            <div class="subtext">Potential checkout value</div>
        </div>
    </div>

    <!-- Detailed Record Sections -->
    <div class="content-container">
        
        <!-- Active User Carts Table -->
        <div class="card-box">
            <h2>Active User Carts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($activeCarts)): ?>
                        <tr><td colspan="5" class="empty-row">No active user carts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($activeCarts as $cart): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($cart['username']) ?></strong></td>
                                <td><?= htmlspecialchars($cart['product_name']) ?></td>
                                <td><?= $cart['quantity'] ?></td>
                                <td>$<?= number_format($cart['price'], 2) ?></td>
                                <td><strong>$<?= number_format($cart['total'], 2) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Registered Users Table -->
        <div class="card-box">
            <h2>Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="3" class="empty-row">No registered users available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?? $user['user_id'] ?? 'N/A' ?></td>
                                <td><strong><?= htmlspecialchars($user['username'] ?? 'N/A') ?></strong></td>
                                <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Product Inventory Table -->
        <div class="card-box">
            <h2>Products Inventory</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="3" class="empty-row">No products listed in database.</td></tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['product_id'] ?></td>
                                <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                <td>$<?= number_format($product['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>