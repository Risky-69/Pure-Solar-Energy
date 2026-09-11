<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['is_admin']);
    unset($_SESSION['admin_user']);
    session_destroy();
    header("Location: AdminLogin.php");
    exit();
}

// Security Guard
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: AdminLogin.php");
    exit();
}

// Database Connection
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

// ==========================================
// ACTION HANDLERS (POST REQUESTS)
// ==========================================
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Update Order Status
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_order_status') {
        $orderId = intval($_POST['order_id']);
        $newStatus = trim($_POST['status']);
        $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $orderId]);
        $message = "Order #$orderId status updated to '$newStatus'.";
    }

    // 2. Delete Order
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_order') {
        $orderId = intval($_POST['order_id']);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        $message = "Order #$orderId was deleted successfully.";
    }

    // 3. Update Product Stock
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'update_stock') {
        $productId = intval($_POST['product_id']);
        $newStock = intval($_POST['quantity']);
        $stmt = $pdo->prepare("UPDATE products SET quantity = :quantity WHERE id = :id");
        $stmt->execute(['quantity' => $newStock, 'id' => $productId]);
        $message = "Stock updated successfully.";
    }

    // 4. Add New Product
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'add_product') {
        $pName = trim($_POST['product_name']);
        $pType = trim($_POST['product_type']);
        $pPrice = floatval($_POST['price']);
        $pQty = intval($_POST['quantity']);

        $stmt = $pdo->prepare("INSERT INTO products (product_name, product_type, price, quantity) VALUES (:name, :type, :price, :qty)");
        $stmt->execute([
            'name'  => $pName,
            'type'  => $pType,
            'price' => $pPrice,
            'qty'   => $pQty
        ]);
        $message = "New product '$pName' added successfully.";
    }

    // 5. Remove Product
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_product') {
        $productId = intval($_POST['product_id']);
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute(['id' => $productId]);
        $message = "Product #$productId was successfully removed.";
    }

    // 6. Delete User
    if (isset($_POST['action_type']) && $_POST['action_type'] === 'delete_user') {
        $userId = intval($_POST['user_id']);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $message = "User #$userId was deleted successfully.";
    }
}

// Data Queries
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$lowStockProducts = $pdo->query("SELECT * FROM products WHERE quantity <= 5 ORDER BY quantity ASC")->fetchAll();

try {
    $orders = $pdo->query("
        SELECT orders.*, users.username, users.email 
        FROM orders 
        LEFT JOIN users ON orders.user_id = users.id 
        ORDER BY orders.id DESC
    ")->fetchAll();
} catch (Exception $e) {
    $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
}

$totalRevenue = 0;
foreach ($orders as $ord) {
    $totalRevenue += floatval($ord['total_price'] ?? $ord['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pure Solar Energy</title>
    <style>
        :root {
            --bg: #070d18;
            --sidebar-bg: #0b1322;
            --card-bg: #0d1726;
            --border: #1b2a40;
            --cyan: #00f2fe;
            --text: #f8fafc;
            --muted: #94a3b8;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }

        /* Left Sidebar Nav */
        aside {
            width: 240px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
        }

        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .brand-icon { font-size: 1.5rem; }
        .brand-text h1 { font-size: 0.95rem; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Sidebar Search Bar */
        .sidebar-search {
            margin-bottom: 20px;
        }
        .sidebar-search input {
            width: 100%;
            background: #111a2e;
            border: 1px solid var(--border);
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.82rem;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .sidebar-search input:focus {
            border-color: var(--cyan);
        }
        .sidebar-search input::placeholder {
            color: #475569;
        }

        nav { display: flex; flex-direction: column; gap: 8px; }
        .nav-btn {
            background: transparent;
            border: 1px solid transparent;
            color: var(--muted);
            padding: 10px 14px;
            border-radius: 6px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .nav-btn:hover { color: #fff; background: rgba(255, 255, 255, 0.05); }
        .nav-btn.active { background: var(--cyan); color: #000; font-weight: bold; border-color: var(--cyan); }

        .sidebar-footer { border-top: 1px solid var(--border); padding-top: 15px; }
        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px solid var(--cyan);
            color: var(--cyan);
            background: transparent;
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: bold;
            transition: 0.2s;
        }
        .logout-btn:hover { background: var(--cyan); color: #000; }

        /* Main Area */
        main { margin-left: 240px; flex: 1; padding: 30px; }

        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-title { font-size: 1.5rem; color: #fff; }

        /* Metric Cards Grid */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .metric-card { background: var(--card-bg); border: 1px solid var(--border); padding: 18px; border-radius: 8px; }
        .metric-title { font-size: 0.8rem; color: var(--muted); text-transform: uppercase; margin-bottom: 6px; }
        .metric-val { font-size: 1.6rem; font-weight: bold; color: var(--cyan); }

        /* Tab Content */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Cards and Tables */
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
        th { background: #111a2e; color: var(--muted); padding: 12px; font-weight: 600; border-bottom: 1px solid var(--border); }
        td { padding: 12px; border-bottom: 1px solid var(--border); color: #cbd5e1; }

        select, input[type="number"], input[type="text"] { background: #111a2e; border: 1px solid var(--border); color: #fff; padding: 6px 10px; border-radius: 6px; outline: none; font-size: 0.85rem; }
        select:focus, input:focus { border-color: var(--cyan); }

        .btn { background: var(--cyan); border: none; color: #000; padding: 6px 12px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 0.8rem; }
        .btn-danger { background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger); color: var(--danger); }
        .btn-danger:hover { background: var(--danger); color: #fff; }

        /* Live Stock Static Badge */
        .live-stock-badge {
            display: inline-block;
            background: #111a2e;
            border: 1px solid var(--border);
            color: var(--cyan);
            padding: 5px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 0.85rem;
            min-width: 45px;
            text-align: center;
        }

        .alert { background: rgba(0, 242, 254, 0.1); border: 1px solid var(--cyan); color: var(--cyan); padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.7); justify-content: center; align-items: center; z-index: 1000; }
        .modal.active { display: flex; }
        .modal-body { background: var(--card-bg); border: 1px solid var(--border); border-radius: 10px; padding: 25px; width: 400px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: 0.8rem; color: var(--muted); margin-bottom: 4px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; background: #111a2e; border: 1px solid var(--border); color: #fff; padding: 8px; border-radius: 6px; box-sizing: border-box; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px; }
    </style>
</head>
<body>

    <!-- LEFT NAVIGATION BAR -->
    <aside>
        <div>
            <!-- Brand -->
            <div class="brand">
                <div class="brand-icon">☀️</div>
                <div class="brand-text">
                    <h1>Pure Solar Admin</h1>
                </div>
            </div>

            <!-- SEARCH BAR -->
            <div class="sidebar-search">
                <input type="text" id="adminSearchInput" onkeyup="filterTables()" placeholder="🔍 Search dashboard...">
            </div>
            
            <!-- Navigation Links -->
            <nav>
                <button class="nav-btn active" onclick="switchTab('home')">Home</button>
                <button class="nav-btn" onclick="switchTab('orders')">Orders</button>
                <button class="nav-btn" onclick="switchTab('products')">Products</button>
                <button class="nav-btn" onclick="switchTab('users')">Users</button>
            </nav>
        </div>

        <div class="sidebar-footer">
            <a href="Admin.php?action=logout" class="logout-btn">Logout</a>
        </div>
    </aside>

    <!-- MAIN DASHBOARD CONTENT -->
    <main>
        <header>
            <h2 class="page-title" id="view-title">Dashboard Overview</h2>
            <span style="color: var(--muted); font-size: 0.85rem;"><?= htmlspecialchars($_SESSION['admin_user']) ?></span>
        </header>

        <?php if (!empty($message)): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- TAB 1: HOME (SUMMARY & LOW STOCK) -->
        <div id="tab-home" class="tab-content active">
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-title">Total Products</div>
                    <div class="metric-val"><?= count($products) ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-title">Total Orders</div>
                    <div class="metric-val"><?= count($orders) ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-title">Registered Users</div>
                    <div class="metric-val"><?= count($users) ?></div>
                </div>
                <div class="metric-card">
                    <div class="metric-title">Total Revenue</div>
                    <div class="metric-val" style="color: var(--cyan);">$<?= number_format($totalRevenue, 2) ?></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 style="color: var(--warning);">⚠️ Low Stock Alerts (5 or fewer left)</h3>
                </div>
                <table class="searchable-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Type</th>
                            <th>Current Stock</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $lowItem): ?>
                                <tr>
                                    <td>#<?= $lowItem['id'] ?></td>
                                    <td style="color: #fff; font-weight: 600;"><?= htmlspecialchars($lowItem['product_name']) ?></td>
                                    <td><?= htmlspecialchars($lowItem['product_type']) ?></td>
                                    <td style="color: var(--danger); font-weight: bold;"><?= $lowItem['quantity'] ?> units remaining</td>
                                    <td>$<?= number_format($lowItem['price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--muted);">All products have sufficient stock.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: ORDERS -->
        <div id="tab-orders" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Customer Orders</h3>
                </div>
                <table class="searchable-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total Price</th>
                            <th>Update Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['username'] ?? $order['email'] ?? 'User #'.$order['user_id']) ?></td>
                                    <td style="color: var(--cyan); font-weight: bold;">$<?= number_format($order['total_price'] ?? $order['total'] ?? 0, 2) ?></td>
                                    <td>
                                        <form method="POST" style="display: flex; gap: 6px;">
                                            <input type="hidden" name="action_type" value="update_order_status">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status">
                                                <?php 
                                                $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Return Refund'];                                                $currentStatus = $order['status'] ?? 'Pending';
                                                foreach ($statuses as $st): 
                                                ?>
                                                    <option value="<?= $st ?>" <?= $currentStatus === $st ? 'selected' : '' ?>><?= $st ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn">Update</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Delete this order?');">
                                            <input type="hidden" name="action_type" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--muted);">No orders found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: PRODUCTS -->
        <div id="tab-products" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Product Inventory</h3>
                    <button class="btn" onclick="openModal()">+ Add New Product</button>
                </div>
                <table class="searchable-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Live Stock</th>
                            <th>Stock Count</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td>#<?= $prod['id'] ?></td>
                                <td style="color: #fff; font-weight: 500;"><?= htmlspecialchars($prod['product_name']) ?></td>
                                <td><span style="color: var(--cyan);"><?= htmlspecialchars($prod['product_type']) ?></span></td>
                                <td>$<?= number_format($prod['price'], 2) ?></td>
                                
                                <!-- Read-Only Live Stock Badge -->
                                <td>
                                    <span class="live-stock-badge"><?= $prod['quantity'] ?></span>
                                </td>

                                <!-- Editable Stock Form -->
                                <td>
                                    <form method="POST" style="display: flex; gap: 6px; align-items: center;">
                                        <input type="hidden" name="action_type" value="update_stock">
                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                        <input type="number" name="quantity" value="<?= $prod['quantity'] ?>" style="width: 70px;" min="0">
                                        <button type="submit" class="btn">Save Stock</button>
                                    </form>
                                </td>

                                <td>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to remove this product?');">
                                        <input type="hidden" name="action_type" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: USERS -->
        <div id="tab-users" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Registered Accounts</h3>
                </div>
                <table class="searchable-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td>#<?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['username'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($u['created_at'] ?? 'N/A') ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="action_type" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-danger">Delete User</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--muted);">No users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ADD PRODUCT MODAL -->
    <div id="productModal" class="modal">
        <div class="modal-body">
            <h3 style="margin-bottom: 15px; color: var(--cyan);">Add New Product</h3>
            <form method="POST">
                <input type="hidden" name="action_type" value="add_product">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" required placeholder="e.g., 400W Solar Panel">
                </div>
                <div class="form-group">
                    <label>Product Type</label>
                    <select name="product_type" required>
                        <option value="Solar Panel">Solar Panel</option>
                        <option value="Inverter">Inverter</option>
                        <option value="Battery">Battery</option>
                        <option value="Charge Controller">Charge Controller</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" required placeholder="199.99">
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="quantity" required placeholder="10">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-danger" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const titles = {
            'home': 'Dashboard Overview',
            'orders': 'Manage Orders',
            'products': 'Inventory & Products',
            'users': 'Registered Users'
        };

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));

            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');

            document.getElementById('view-title').innerText = titles[tabName] || 'Dashboard';

            filterTables();
        }

        function openModal() {
            document.getElementById('productModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function filterTables() {
            const query = document.getElementById('adminSearchInput').value.toLowerCase();
            const activeTab = document.querySelector('.tab-content.active');
            if (!activeTab) return;

            const rows = activeTab.querySelectorAll('.searchable-table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>