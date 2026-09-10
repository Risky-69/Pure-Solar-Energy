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

// Handle Item Removal
if (isset($_POST['remove_id'])) {
    $removeStmt = $pdo->prepare("DELETE FROM cart WHERE id = :id");
    $removeStmt->execute(['id' => $_POST['remove_id']]);
    header("Location: Cart.php");
    exit;
}

// Fetch cart items joined with products
if ($userId) {
    $stmt = $pdo->prepare("
        SELECT c.id AS cart_id, c.quantity, p.product_name, p.product_type, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
} else {
    $stmt = $pdo->query("
        SELECT c.id AS cart_id, c.quantity, p.product_name, p.product_type, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id IS NULL
    ");
}

$cartItems = $stmt->fetchAll();
$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pure Solar Energy</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background-color: #080e1e;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .cart-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #1e293b;
        }
        .cart-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
        }
        .header-actions {
            display: flex;
            gap: 12px;
        }
        .btn-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #111a2e;
            color: #00f2fe;
            border: 1px solid #1e293b;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-nav:hover {
            border-color: #00f2fe;
            background: #16223b;
        }
        .btn-home {
            background: #1e293b;
            color: #fff;
        }
        .btn-home:hover {
            background: #334155;
            border-color: #475569;
        }
        .cart-card {
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            text-align: left;
            padding: 14px 16px;
            color: #00f2fe;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #1e293b;
        }
        td {
            padding: 18px 16px;
            border-bottom: 1px solid #1e293b;
            color: #cbd5e1;
            font-size: 0.95rem;
        }
        .badge-type {
            color: #00f2fe;
            background: rgba(0, 242, 254, 0.1);
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #64748b;
        }
        .empty-cart p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        .btn-remove {
            background: transparent;
            border: none;
            color: #ef4444;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-remove:hover {
            text-decoration: underline;
        }
        .cart-summary {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 20px;
            padding-top: 20px;
            border-top: 1px solid #1e293b;
        }
        .grand-total-label {
            font-size: 1rem;
            color: #94a3b8;
        }
        .grand-total-val {
            font-size: 1.6rem;
            font-weight: 700;
            color: #00f2fe;
        }
        .btn-checkout {
            padding: 12px 32px;
            background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
            border: none;
            border-radius: 8px;
            color: #000;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="cart-wrapper">
    <div class="cart-header">
        <h1 class="cart-title">Your Shopping Cart</h1>
        <div class="header-actions">
            <a href="../MAIN.php" class="btn-nav btn-home">🏠 Return to Home</a>
            <a href="./UserOptions/View Products/Products.php?type=All" class="btn-nav">&larr; Continue Shopping</a>
        </div>
    </div>

    <div class="cart-card">
        <?php if (!empty($cartItems)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                        $grandTotal += $subtotal;
                    ?>
                    <tr>
                        <td style="color: #fff; font-weight: 600;"><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><span class="badge-type"><?= htmlspecialchars($item['product_type']) ?></span></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td style="color: #fff; font-weight: 600;">$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="remove_id" value="<?= $item['cart_id'] ?>">
                                <button type="submit" class="btn-remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div>
                    <span class="grand-total-label">Grand Total: </span>
                    <span class="grand-total-val">$<?= number_format($grandTotal, 2) ?></span>
                </div>
                <button type="button" class="btn-checkout">Checkout</button>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Your shopping cart is currently empty.</p>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <a href="UserIndex.php" class="btn-nav btn-home">Return to Home</a>
                    <a href="./UserOptions/View Products/Products.php?type=All" class="btn-nav">Browse Products</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>