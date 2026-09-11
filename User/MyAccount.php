<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Include database connection settings
if (file_exists("../db.php")) {
    require_once "../db.php";
} elseif (file_exists("db.php")) {
    require_once "db.php";
}

// Set default connection credentials if not defined in db.php
$host     = $host ?? "127.0.0.1";
$port     = $port ?? 3306;
$username = $username ?? $user ?? "root";
$pass     = $pass ?? ""; 
$dbname   = $dbname ?? $db ?? "puresolarenergy";
$charset  = $charset ?? "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $pass, $options);
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
$sessionUsername = $_SESSION['username'] ?? '';

// If user_id isn't in session yet, look it up by username or email
if (!$userId && !empty($sessionUsername)) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$sessionUsername, $sessionUsername]);
    $userData = $stmt->fetch();
    if ($userData) {
        $userId = $userData['id'];
        $_SESSION['user_id'] = $userId;
    }
}

// Fetch user profile info safely
$currentUser = ['username' => $sessionUsername, 'email' => 'N/A'];
if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $fetchedUser = $stmt->fetch();
    if ($fetchedUser) {
        $currentUser = $fetchedUser;
    }
}

// Active tab and search filter
$activeStatus = $_GET['status'] ?? 'All';
$searchQuery = trim($_GET['search'] ?? '');

// Fetch user orders with filters
$orders = [];
try {
    $query = "SELECT * FROM orders WHERE user_id = :user_id";
    $params = ['user_id' => $userId];

    if ($activeStatus !== 'All') {
        $statusKey = strtolower($activeStatus);
        
        // Map User Dashboard Tabs to Admin Status Dropdown Values
        if ($statusKey === 'to_pay') {
            $query .= " AND LOWER(status) = 'pending'";
        } elseif ($statusKey === 'to_ship') {
            $query .= " AND LOWER(status) = 'processing'";
        } elseif ($statusKey === 'to_receive') {
            $query .= " AND LOWER(status) = 'shipped'";
        } elseif ($statusKey === 'completed') {
            $query .= " AND (LOWER(status) = 'delivered' OR LOWER(status) = 'completed')";
        } elseif ($statusKey === 'cancelled') {
            $query .= " AND LOWER(status) = 'cancelled'";
        } else {
            // Fallback for Return Refund or exact matches
            $query .= " AND LOWER(status) = :status";
            $params['status'] = str_replace('_', ' ', $activeStatus);
        }
    }

    $query .= " ORDER BY order_date DESC";
    $orderStmt = $pdo->prepare($query);
    $orderStmt->execute($params);
    $rawOrders = $orderStmt->fetchAll();

    // In-memory search filter for Order ID or Product Items
    if (!empty($searchQuery)) {
        foreach ($rawOrders as $order) {
            $orderIdMatch = stripos((string)($order['id'] ?? ''), $searchQuery) !== false;
            $itemsMatch = stripos($order['items'] ?? '', $searchQuery) !== false;
            if ($orderIdMatch || $itemsMatch) {
                $orders[] = $order;
            }
        }
    } else {
        $orders = $rawOrders;
    }
} catch (PDOException $e) {
    $orders = [];
}

// Fetch active vouchers
$vouchers = [];
try {
    $voucherStmt = $pdo->prepare("SELECT * FROM vouchers WHERE user_id = ? AND status = 'active'");
    $voucherStmt->execute([$userId]);
    $vouchers = $voucherStmt->fetchAll();
} catch (PDOException $e) {
    $vouchers = [];
}

$searchParam = !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #080e1e; color: #fff; min-height: 100vh; padding-bottom: 60px; }

        /* Navigation Header */
        .topbar {
            background: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            border-bottom: 1px solid #1e293b;
        }
        .topbar-left { font-weight: 700; color: #00f2fe; font-size: 1.1rem; }
        .topbar-right { display: flex; align-items: center; gap: 15px; }
        .btn-nav { color: #94a3b8; text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
        .btn-nav:hover { color: #00f2fe; }

        /* Layout Grid */
        .portal-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 25px;
        }

        /* Sidebar Style */
        .sidebar-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 20px;
        }
        .avatar-lg {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00f2fe, #4facfe);
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }
        .user-meta .username { font-weight: 700; color: #fff; font-size: 0.95rem; }
        .user-meta .edit-profile { font-size: 0.8rem; color: #94a3b8; text-decoration: none; }
        .user-meta .edit-profile:hover { color: #00f2fe; }

        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.92rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #111a2e;
            color: #00f2fe;
            font-weight: 600;
        }

        /* Status Navigation Tabs */
        .status-tabs {
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            overflow-x: auto;
            margin-bottom: 15px;
        }
        .status-tab {
            flex: 1;
            text-align: center;
            padding: 16px 12px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            transition: all 0.2s;
        }
        .status-tab:hover { color: #fff; }
        .status-tab.active {
            color: #00f2fe;
            border-bottom-color: #00f2fe;
            background: rgba(0, 242, 254, 0.03);
        }

        /* Search Filter Bar */
        .search-bar-wrapper {
            margin-bottom: 20px;
        }
        .search-form {
            display: flex;
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 8px;
            overflow: hidden;
        }
        .search-input {
            width: 100%;
            padding: 12px 16px;
            background: transparent;
            border: none;
            color: #fff;
            outline: none;
            font-size: 0.9rem;
        }
        .search-btn {
            background: #1e293b;
            color: #00f2fe;
            border: none;
            padding: 0 20px;
            cursor: pointer;
            font-weight: 600;
        }
        .search-btn:hover { background: #16223b; }

        /* Order Card Styling */
        .order-card {
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px solid #1e293b;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
        .shop-title { font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
        .order-status {
            text-transform: uppercase;
            font-weight: 700;
            font-size: 0.8rem;
            color: #00f2fe;
            letter-spacing: 0.5px;
        }

        .product-item {
            display: flex;
            gap: 15px;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed #1e293b;
        }
        .product-item:last-child { border-bottom: none; }
        .product-img {
            width: 70px;
            height: 70px;
            background: #1e293b;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .product-details { flex: 1; }
        .product-name { color: #fff; font-size: 0.95rem; font-weight: 600; margin-bottom: 4px; }
        .product-qty { color: #64748b; font-size: 0.85rem; }
        .product-price { color: #00f2fe; font-weight: 700; font-size: 1rem; }

        .order-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #1e293b;
        }
        .order-total-label { color: #94a3b8; font-size: 0.9rem; }
        .order-total-val { color: #00f2fe; font-size: 1.3rem; font-weight: 700; margin-left: 6px; }
        
        .action-btns { display: flex; gap: 10px; }
        .btn-action {
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-primary { background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); color: #000; }
        .btn-secondary { background: #1e293b; color: #cbd5e1; border: 1px solid #334155; }
        .btn-secondary:hover { background: #334155; color: #fff; }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 10px;
            color: #64748b;
        }

        /* Responsive UI */
        @media (max-width: 850px) {
            .portal-container { grid-template-columns: 1fr; }
            .status-tabs { overflow-x: scroll; }
        }
    </style>
</head>
<body>

    <!-- Top Navigation Header -->
    <div class="topbar">
        <div class="topbar-left">
            ⚡ Pure Solar Energy
        </div>
        <div class="topbar-right">
            <a href="UserIndex.php" class="btn-nav">🏠 Home</a>
            <a href="Cart.php" class="btn-nav">🛒 Cart</a>
            <a href="MyAccount.php?logout=true" class="btn-nav" style="color: #ef4444;">🚪 Logout</a>
        </div>
    </div>

    <div class="portal-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-profile">
                <div class="avatar-lg"><?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?></div>
                <div class="user-meta">
                    <div class="username"><?= htmlspecialchars($currentUser['username'] ?? 'User') ?></div>
                    <!-- <a href="ViewProfile.php" class="edit-profile">✏️ Edit Profile</a> -->
                </div>
            </div>

            <ul class="sidebar-menu">
                <li><a href="MyAccount.php" class="active">📦 My Purchase</a></li>
                <li><a href="#">🔔 Notifications</a></li>
                <li><a href="#">🏷️ My Vouchers (<?= count($vouchers) ?>)</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            
            <!-- Status Navigation Tabs -->
            <nav class="status-tabs">
                <a href="MyAccount.php?status=All<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'All' ? 'active' : '' ?>">All</a>
                <a href="MyAccount.php?status=To_Pay<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'To_Pay' ? 'active' : '' ?>">To Pay</a>
                <a href="MyAccount.php?status=To_Ship<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'To_Ship' ? 'active' : '' ?>">To Ship</a>
                <a href="MyAccount.php?status=To_Receive<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'To_Receive' ? 'active' : '' ?>">To Receive</a>
                <a href="MyAccount.php?status=Completed<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'Completed' ? 'active' : '' ?>">Completed</a>
                <a href="MyAccount.php?status=Cancelled<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'Cancelled' ? 'active' : '' ?>">Cancelled</a>
                <a href="MyAccount.php?status=Return_Refund<?= $searchParam ?>" class="status-tab <?= $activeStatus === 'Return_Refund' ? 'active' : '' ?>">Return Refund</a>
            </nav>

            <!-- Search Filter Bar -->
            <div class="search-bar-wrapper">
                <form method="GET" action="MyAccount.php" class="search-form">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($activeStatus) ?>">
                    <input type="text" name="search" class="search-input" placeholder="You can search by Order ID or Product name" value="<?= htmlspecialchars($searchQuery) ?>">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>

            <!-- Order Cards List -->
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): 
                    $orderId = $order['id'] ?? 'N/A';
                    $items = json_decode($order['items'] ?? '[]', true) ?: [];
                    $orderStatus = $order['status'] ?? 'Processing';
                    $totalAmount = $order['total_amount'] ?? 0;
                ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div class="shop-title">
                                🏬 Pure Solar Official Store
                            </div>
                            <div class="order-status"><?= htmlspecialchars($orderStatus) ?></div>
                        </div>

                        <div class="order-card-body">
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                    <div class="product-item">
                                        <div class="product-img">☀️</div>
                                        <div class="product-details">
                                            <div class="product-name"><?= htmlspecialchars($item['product_name'] ?? 'Solar Product') ?></div>
                                            <div class="product-qty">x<?= htmlspecialchars($item['quantity'] ?? 1) ?></div>
                                        </div>
                                        <div class="product-price">$<?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="product-item">
                                    <div class="product-img">📦</div>
                                    <div class="product-details">
                                        <div class="product-name">Order #<?= htmlspecialchars($orderId) ?></div>
                                        <div class="product-qty">Placed on <?= htmlspecialchars($order['order_date'] ?? 'N/A') ?></div>
                                    </div>
                                    <div class="product-price">$<?= number_format($totalAmount, 2) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="order-card-footer">
                            <div class="order-total">
                                <span class="order-total-label">Order Total:</span>
                                <span class="order-total-val">$<?= number_format($totalAmount, 2) ?></span>
                            </div>
                            <div class="action-btns">
                                <!-- Included 'shipped' here so the button shows up under To Receive -->
                                <?php if (in_array(strtolower($orderStatus), ['to receive', 'delivered', 'shipped'])): ?>
                                    <button class="btn-action btn-primary">Order Received</button>
                                <?php endif; ?>
                                <button class="btn-action btn-secondary">Contact Support</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-orders">
                    <p style="font-size: 1.1rem; margin-bottom: 8px;">No orders found</p>
                    <p style="font-size: 0.85rem; color: #475569;">When you place an order, it will appear here under "<?= htmlspecialchars(str_replace('_', ' ', $activeStatus)) ?>".</p>
                </div>
            <?php endif; ?>

        </main>
    </div>

</body>
</html>