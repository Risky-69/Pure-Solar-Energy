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

// Fetch cart items joined with products
if ($userId) {
    $stmt = $pdo->prepare("
        SELECT c.id AS cart_id, c.product_id, c.quantity, p.product_name, p.price, p.quantity AS stock_qty
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
} else {
    $stmt = $pdo->query("
        SELECT c.id AS cart_id, c.product_id, c.quantity, p.product_name, p.price, p.quantity AS stock_qty
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id IS NULL
    ");
}

$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header("Location: Cart.php");
    exit;
}

$grandTotal = 0;
foreach ($cartItems as $item) {
    $grandTotal += $item['price'] * $item['quantity'];
}

// Handle Order Submission & Stock Deduction
// Handle Order Submission & Stock Deduction
$orderSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    
    // 1. Prepare order details JSON
    $orderedItemsList = [];
    foreach ($cartItems as $item) {
        $orderedItemsList[] = [
            'product_name' => $item['product_name'],
            'quantity'     => $item['quantity'],
            'price'        => $item['price']
        ];
    }

    // 2. Insert order record into database
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (user_id, fullname, email, address, payment_method, items, total_amount, status) 
        VALUES (:user_id, :fullname, :email, :address, :payment_method, :items, :total_amount, 'Processing')
    ");
    $orderStmt->execute([
        'user_id'        => $userId,
        'fullname'       => $_POST['fullname'] ?? '',
        'email'          => $_POST['email'] ?? '',
        'address'        => $_POST['address'] ?? '',
        'payment_method' => $_POST['payment_method'] ?? 'COD',
        'items'          => json_encode($orderedItemsList),
        'total_amount'   => $grandTotal
    ]);

    // 3. Deduct quantity from products table
    $updateStockStmt = $pdo->prepare("
        UPDATE products 
        SET quantity = GREATEST(0, quantity - :ordered_qty) 
        WHERE id = :product_id
    ");

    foreach ($cartItems as $item) {
        $updateStockStmt->execute([
            'ordered_qty' => $item['quantity'],
            'product_id'  => $item['product_id']
        ]);
    }

    // 4. Clear items from cart
    if ($userId) {
        $clearStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
        $clearStmt->execute(['user_id' => $userId]);
    } else {
        $pdo->query("DELETE FROM cart WHERE user_id IS NULL");
    }

    $orderSuccess = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pure Solar Energy</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: #080e1e; color: #fff; font-family: system-ui, sans-serif; min-height: 100vh; padding: 40px 60px; }
        .checkout-wrapper { max-width: 1000px; margin: 0 auto; }
        .checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; margin-top: 25px; }
        .card { background: #111a2e; border: 1px solid #1e293b; border-radius: 12px; padding: 25px; }
        h2 { color: #00f2fe; margin-bottom: 20px; font-size: 1.2rem; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.85rem; color: #94a3b8; margin-bottom: 6px; }
        input, select { width: 100%; padding: 10px 14px; background: #080e1e; border: 1px solid #1e293b; border-radius: 6px; color: #fff; outline: none; }
        input:focus, select:focus { border-color: #00f2fe; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.9rem; color: #cbd5e1; }
        .total-row { display: flex; justify-content: space-between; padding-top: 15px; border-top: 1px solid #1e293b; font-weight: bold; font-size: 1.2rem; color: #00f2fe; margin-top: 15px; }
        .btn-order { width: 100%; margin-top: 20px; padding: 14px; background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); border: none; border-radius: 6px; color: #000; font-weight: bold; font-size: 1rem; cursor: pointer; }
        .btn-back { color: #00f2fe; text-decoration: none; font-size: 0.9rem; display: inline-block; margin-bottom: 15px; }
        
        /* Modal */
        .modal { position: fixed; inset: 0; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center; z-index: 999; }
        .modal-card { background: #111a2e; border: 1px solid #00f2fe; border-radius: 12px; padding: 30px; text-align: center; max-width: 400px; }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <a href="Cart.php" class="btn-back">&larr; Back to Cart</a>
    <h1>Checkout</h1>

    <form method="POST" class="checkout-grid">
        <!-- Shipping Details -->
        <div class="card">
            <h2>Shipping & Payment Details</h2>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="john@example.com">
            </div>
            <div class="form-group">
                <label>Shipping Address</label>
                <input type="text" name="address" required placeholder="123 Street Name">
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method">
                    <option value="gcash">GCash</option>
                    <option value="maya">Maya</option>
                    <option value="cod">Cash on Delivery</option>
                    <option value="card">Credit/Debit Card</option>
                </select>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="card">
            <h2>Order Summary</h2>
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <span><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</span>
                    <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="total-row">
                <span>Total:</span>
                <span>$<?= number_format($grandTotal, 2) ?></span>
            </div>
            <button type="submit" name="place_order" class="btn-order">Place Order</button>
        </div>
    </form>
</div>

<?php if ($orderSuccess): ?>
<div class="modal">
    <div class="modal-card">
        <div style="font-size: 3rem; margin-bottom: 10px;">🎉</div>
        <h2 style="color:#fff;">Order Placed!</h2>
        <p style="color: #94a3b8; margin: 10px 0 20px;">Thank you for your purchase. Your order is being processed.</p>
        <a href="UserOptions/View Products/Products.php?type=All" style="display:inline-block; padding: 10px 24px; background: #00f2fe; color: #000; font-weight: bold; text-decoration: none; border-radius: 6px;">Return to Products</a>
    </div>
</div>
<?php endif; ?>

</body>
</html>