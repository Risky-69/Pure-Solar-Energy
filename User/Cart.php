<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    require_once "../db.php";
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// $host    = 'localhost';
// $db      = 'puresolarenergy';
// $user    = 'root';
// $pass    = 'Password'; 
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

// 1. Resolve user ID
$userId = $_SESSION['user_id'] ?? null;

if (!$userId && isset($_SESSION['username'])) {
    try {
        // Double check if your table uses 'id' or 'user_id' as the primary key
        $stmt = $pdo->prepare("SELECT id AS user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$_SESSION['username'], $_SESSION['username']]);
        $userData = $stmt->fetch();
        if ($userData) {
            $userId = $userData['user_id'];
            $_SESSION['user_id'] = $userId; 
        }
    } catch (PDOException $e) {
        // If 'users' query fails, output error for debugging
        die("User lookup failed: " . $e->getMessage());
    }
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* =========================================================
   HANDLE CART ACTIONS (ADD, UPDATE, REMOVE, CLEAR)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity   = intval($_POST['quantity'] ?? 1);

    if ($action === 'add') {
        $quantity = max(1, $quantity);
        if ($product_id > 0) {
            if ($userId) {
                // Database Cart for Logged-In Users
                $stmt = $pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
                ");
                $stmt->execute([$userId, $product_id, $quantity]);
            } else {
                // Session Cart for Guests
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $stmt = $pdo->prepare("SELECT product_id, product_name, price FROM products WHERE product_id = ?");
                    $stmt->execute([$product_id]);
                    if ($product = $stmt->fetch()) {
                        $_SESSION['cart'][$product_id] = [
                            'id'       => $product['product_id'],
                            'name'     => $product['product_name'],
                            'price'    => $product['price'],
                            'quantity' => $quantity
                        ];
                    }
                }
            }
        }
    } elseif ($action === 'update') {
        if ($userId) {
            if ($quantity > 0) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $userId, $product_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $product_id]);
            }
        } else {
            if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    } elseif ($action === 'remove') {
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $product_id]);
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($action === 'clear') {
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$userId]);
        } else {
            $_SESSION['cart'] = [];
        }
    }

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['status' => 'success']);
        exit;
    }

    header("Location: Cart.php");
    exit;
}

// Fetch Cart Items for Display
$cartItems = [];
$subtotal = 0;
$totalItems = 0;

if ($userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.product_name, p.price 
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        $cartRows = $stmt->fetchAll();

        foreach ($cartRows as $row) {
            $cartItems[$row['product_id']] = [
                'name'     => $row['product_name'],
                'price'    => $row['price'],
                'quantity' => $row['quantity']
            ];
            $subtotal += $row['price'] * $row['quantity'];
            $totalItems += $row['quantity'];
        }
    } catch (PDOException $e) {
        die("Error fetching cart data: " . $e->getMessage());
    }
} else {
    foreach ($_SESSION['cart'] as $id => $item) {
        $cartItems[$id] = $item;
        $subtotal += $item['price'] * $item['quantity'];
        $totalItems += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #0b132b; color: #ffffff; margin: 0; padding: 20px; }
        .cart-container { max-width: 1000px; margin: 30px auto; background: #1c2541; border-radius: 10px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h1 { color: #4cc9f0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #3a506b; }
        th { background-color: #0b132b; color: #4cc9f0; }
        .qty-input { width: 60px; padding: 5px; border-radius: 4px; border: 1px solid #3a506b; background: #0b132b; color: #fff; text-align: center; }
        .btn { padding: 8px 14px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-update { background-color: #3a506b; color: white; }
        .btn-remove { background-color: #e63946; color: white; }
        .btn-clear { background-color: #6c757d; color: white; }
        .btn-checkout { background-color: #4cc9f0; color: #0b132b; float: right; font-size: 1.1rem; }
        .cart-summary { background: #0b132b; padding: 20px; border-radius: 8px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
        .empty-cart { text-align: center; padding: 40px 0; color: #8ca3ba; }
        .empty-cart a { color: #4cc9f0; text-decoration: none; }
    </style>
</head>
<body>

<div class="cart-container">
    <h1>Your Shopping Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <h3>Your cart is empty.</h3>
            <p><a href="/User/UserOptions/View%20Products/Products.php">&larr; Continue Shopping</a></p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $id => $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form method="POST" action="Cart.php" style="display: flex; gap: 5px;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="qty-input">
                                <button type="submit" class="btn btn-update">Update</button>
                            </form>
                        </td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                        <td>
                            <form method="POST" action="Cart.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-remove">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div>
                <form method="POST" action="Cart.php" style="display: inline;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn btn-clear">Clear Cart</button>
                </form>
                <a href="/User/UserOptions/View%20Products/Products.php" style="color: #8ca3ba; margin-left: 15px; text-decoration: none;">&larr; Continue Shopping</a>
            </div>
            <div>
                <h3>Total (<?= $totalItems ?> items): <span style="color: #4cc9f0;">$<?= number_format($subtotal, 2) ?></span></h3>
                <a href="#" class="btn btn-checkout">Proceed to Checkout &rarr;</a>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>