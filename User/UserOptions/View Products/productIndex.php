<?php
// Display errors for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Connection
$host    = 'localhost';
$db      = 'puresolarenergy';
$user    = 'root';
$pass    = 'Password'; // Replace with your MySQL password
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

// Fetch all products from database
$stmt = $pdo->query("SELECT * FROM products ORDER BY product_id DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Pure Solar Energy</title>
    <link rel="stylesheet" href="../../../CSS/MainHeader.css">
    <style>
        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-type {
            font-size: 0.8rem;
            color: #ff9900;
            text-transform: uppercase;
            font-weight: bold;
        }
        .product-name {
            font-size: 1.25rem;
            margin: 10px 0;
            color: #333333;
        }
        .product-info {
            font-size: 0.9rem;
            color: #666666;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .product-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        .product-price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #222222;
        }
        .product-stock {
            font-size: 0.85rem;
            color: #28a745;
        }
        .btn-add-cart {
            margin-top: 15px;
            background-color: #ff9900;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
        }
        .btn-add-cart:hover {
            background-color: #e68a00;
        }
    </style>
</head>
<body>

    <div class="products-container">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div>
                        <span class="product-type"><?= htmlspecialchars($product['product_type']) ?></span>
                        <h3 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h3>
                        <p class="product-info"><?= htmlspecialchars($product['product_info']) ?></p>
                    </div>
                    <div>
                        <div class="product-bottom">
                            <span class="product-price">$<?= number_format($product['price'], 2) ?></span>
                            <span class="product-stock">Stock: <?= htmlspecialchars($product['quantity']) ?></span>
                        </div>
                        <button class="btn-add-cart">Add to Cart</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found in the database.</p>
        <?php endif; ?>
    </div>

  <!-- <?php include 'Options/View Products/Products.php'; ?> -->

</body>
</html>