<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force output to JSON format
header('Content-Type: application/json');

// Prevent raw HTML errors from corrupting the JSON payload
ini_set('display_errors', 0);
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
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$productId = $_POST['product_id'] ?? null;
$userId    = $_SESSION['user_id'] ?? null;

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Product ID missing.']);
    exit;
}

try {
    // Check if the item is already added
    if ($userId) {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id");
        $stmt->execute(['user_id' => $userId, 'product_id' => $productId]);
    } else {
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id IS NULL AND product_id = :product_id");
        $stmt->execute(['product_id' => $productId]);
    }

    $existingCartItem = $stmt->fetch();

    if ($existingCartItem) {
        // Increment quantity if product already exists in cart
        $newQuantity = $existingCartItem['quantity'] + 1;
        $updateStmt  = $pdo->prepare("UPDATE cart SET quantity = :quantity WHERE id = :id");
        $updateStmt->execute(['quantity' => $newQuantity, 'id' => $existingCartItem['id']]);
    } else {
        // Insert new row if item is not in cart
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, 1)");
        $insertStmt->execute([
            'user_id'    => $userId,
            'product_id' => $productId
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Product added to cart successfully.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database operation failed: ' . $e->getMessage()]);
}
?>