<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host     = "127.0.0.1";
$port     = 3306;
$username = "root";
$pass     = ""; 
$dbname   = "puresolarenergy";
$charset  = "utf8mb4";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $pass, $options);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC");
    $stmt->execute(['user_id' => $userId]);
} else {
    $stmt = $pdo->query("SELECT * FROM orders WHERE user_id IS NULL ORDER BY order_date DESC");
}

$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #080e1e; color: #fff; font-family: system-ui, sans-serif; min-height: 100vh; padding: 40px 60px; }
        .orders-wrapper { max-width: 1000px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-actions { display: flex; align-items: center; gap: 12px; }
        .btn-nav { display: inline-block; padding: 10px 18px; background: #111a2e; color: #00f2fe; border: 1px solid #1e293b; border-radius: 8px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: background 0.2s ease; }
        .btn-nav:hover { background: #16223b; }
        
        /* User Dropdown Menu Styles */
        .user-menu-wrapper {
            position: relative;
            display: inline-block;
            z-index: 1001;
        }

        .auth-signup-btn {
            background-color: #f59e0b;
            color: #000;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.2s ease;
        }

        .auth-signup-btn:hover {
            background-color: #d97706;
        }

        .user-dropdown-menu {
            position: absolute;
            top: 125%;
            right: 0;
            width: 200px;
            background: #0d1726;
            border: 1px solid #1e293b;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 242, 254, 0.1);
            display: none;
            flex-direction: column;
            z-index: 1003;
            overflow: hidden;
            padding: 6px 0;
        }

        .user-dropdown-menu.active {
            display: flex !important;
        }

        .dropdown-header {
            padding: 10px 16px;
            font-size: 0.8rem;
            color: #94a3b8;
            border-bottom: 1px solid #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .dropdown-item {
            padding: 10px 16px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .dropdown-item:hover {
            background: #16223b;
            color: #00f2fe;
        }

        .dropdown-divider {
            height: 1px;
            background: #1e293b;
            margin: 4px 0;
        }

        .logout-link {
            color: #ef4444;
        }

        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Orders Content Styles */
        .order-card { background: #111a2e; border: 1px solid #1e293b; border-radius: 12px; padding: 25px; margin-bottom: 20px; }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b; padding-bottom: 15px; margin-bottom: 15px; }
        .order-id { font-weight: bold; color: #fff; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; background: rgba(0, 242, 254, 0.1); color: #00f2fe; border: 1px solid #00f2fe; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem; color: #cbd5e1; }
        .order-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #1e293b; font-size: 0.9rem; color: #94a3b8; }
        .total-price { font-size: 1.2rem; font-weight: bold; color: #00f2fe; }
        .empty-orders { text-align: center; padding: 50px 0; color: #64748b; }
    </style>
</head>
<body>

<div class="orders-wrapper">
    <div class="header">
        <h1>My Orders</h1>
        <div class="header-actions">
            <a href="UserIndex.php" class="btn-nav">🏠 Return to Home</a>

            <!-- Me Dropdown Button -->
            <div class="user-menu-wrapper">
                <button type="button" id="meBtn" class="auth-link auth-signup-btn">
                    Me ▾
                </button>

                <div id="userDropdownMenu" class="user-dropdown-menu">
                    <div class="dropdown-header">My Account</div>
                    <a href="/User/MyAccount.php" class="dropdown-item">👤 View Profile</a>
                    <a href="Orders.php" class="dropdown-item">📦 View Orders</a>
                    <a href="Cart.php" class="dropdown-item">🛒 Shopping Cart</a>
                    <div class="dropdown-divider"></div>
                    <a href="Logout.php" class="dropdown-item logout-link">🚪 Log Out</a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): 
            $items = json_decode($order['items'], true) ?? [];
        ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <span class="order-id">Order #<?= $order['id'] ?></span>
                        <div style="font-size: 0.8rem; color: #64748b; margin-top: 4px;"><?= $order['order_date'] ?></div>
                    </div>
                    <span class="status-badge"><?= htmlspecialchars($order['status']) ?></span>
                </div>

                <div class="items-list">
                    <?php foreach ($items as $item): ?>
                        <div class="item-row">
                            <span><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-footer">
                    <div>
                        <span>Ship To: <strong><?= htmlspecialchars($order['address']) ?></strong></span>
                    </div>
                    <div>
                        Total: <span class="total-price">$<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="order-card empty-orders">
            <p>You haven't placed any orders yet.</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const meBtn = document.getElementById("meBtn");
    const dropdownMenu = document.getElementById("userDropdownMenu");

    if (meBtn && dropdownMenu) {
        meBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle("active");
        });

        document.addEventListener("click", function(event) {
            if (!dropdownMenu.contains(event.target) && !meBtn.contains(event.target)) {
                dropdownMenu.classList.remove("active");
            }
        });
    }
});
</script>

</body>
</html>