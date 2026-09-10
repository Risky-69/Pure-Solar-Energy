<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
    die("Database Connection Failed: " . $e->getMessage());
}

$isLoggedIn = isset($_SESSION['user_id']); 

$selectedType = $_GET['type'] ?? 'All';

if ($selectedType !== 'All' && !empty($selectedType)) {
    $typeQuery = rtrim($selectedType, 's'); 
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_type LIKE :type ORDER BY id DESC");
    $stmt->execute(['type' => "%$typeQuery%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
}

$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Solutions Dashboard</title>
    <link rel="stylesheet" href="./Products/style.css">
    <style>
        /* Centered Cart Notification Modal */
        .cart-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.25s ease-in-out;
        }

        .cart-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .cart-modal-card {
            background: #111a2e;
            border: 1px solid #00f2fe;
            box-shadow: 0 0 25px rgba(0, 242, 254, 0.3);
            border-radius: 12px;
            padding: 30px;
            width: 320px;
            text-align: center;
            transform: scale(0.8);
            transition: transform 0.25s ease-in-out;
        }

        .cart-modal-overlay.active .cart-modal-card {
            transform: scale(1);
        }

        .cart-modal-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .cart-modal-title {
            color: #fff;
            font-size: 1.25rem;
            margin-bottom: 8px;
        }

        .cart-modal-text {
            color: #8ca3ba;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .cart-modal-btn {
            background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%);
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: bold;
            color: #000;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- LEFT NAVIGATION BAR -->
    <aside>
        <div class="brand">
            <div class="brand-icon">☀️</div>
            <div class="brand-text">
                <h1>PURE SOLAR ENERGY</h1>
            </div>
        </div>
        
        <nav>
            <a href="Products.php?type=All" class="nav-btn <?= (!isset($_GET['type']) || $_GET['type'] == 'All') ? 'active' : '' ?>">Home</a>
            <a href="Products.php?type=Solar Panel" class="nav-btn <?= (isset($_GET['type']) && $_GET['type'] == 'Solar Panel') ? 'active' : '' ?>">Solar Panels</a>
            <a href="Products.php?type=Inverter" class="nav-btn <?= (isset($_GET['type']) && $_GET['type'] == 'Inverter') ? 'active' : '' ?>">Inverters</a>
            <a href="Products.php?type=Battery" class="nav-btn <?= (isset($_GET['type']) && $_GET['type'] == 'Battery') ? 'active' : '' ?>">Batteries</a>
            <a href="Products.php?type=Charge Controller" class="nav-btn <?= (isset($_GET['type']) && $_GET['type'] == 'Charge Controller') ? 'active' : '' ?>">Charge Controllers</a>
        </nav>

        <div class="sidebar-footer">
            <a href="../../UserIndex.php" class="return-main-btn">Return to Main</a>
        </div>
    </aside>

    <!-- MAIN CATALOG INTERFACE -->
    <main>
        <header>
            <h2 id="view-title">
                <?= isset($_GET['type']) && $_GET['type'] !== 'All' ? htmlspecialchars($_GET['type']) . 's' : 'All Products' ?> 
            </h2>
            <div class="search-container">
                <input type="text" id="catalog-search" placeholder="Search products...">
            </div>
        </header>
        
        <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <div>
                        <span class="badge-type" style="color: #00f2fe; font-size: 0.75rem; text-transform: uppercase; font-weight: bold;"><?= htmlspecialchars($item['product_type']) ?></span>
                        <h3 style="margin: 10px 0; font-size: 1.1rem; color: #fff;"><?= htmlspecialchars($item['product_name']) ?></h3>
                        <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.4; margin-bottom: 15px;"><?= htmlspecialchars($item['product_info'] ?? '') ?></p>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #00f2fe;">$<?= number_format($item['price'], 2) ?></span>
                            <span style="font-size: 0.8rem; color: #64748b;">Stock: <?= htmlspecialchars($item['quantity']) ?></span>
                        </div>
                        
                        <button type="button" 
                                class="btn-cart"
                                onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>')"
                                style="width: 100%; margin-top: 15px; padding: 10px; background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); border: none; border-radius: 6px; color: #000; font-weight: bold; cursor: pointer;">
                                Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #94a3b8;">No products found in the database.</p>
        <?php endif; ?>
        </div>
    </main>

    <!-- CENTERED ADD TO CART SUCCESS MODAL -->
    <div id="cartModal" class="cart-modal-overlay">
        <div class="cart-modal-card">
            <div class="cart-modal-icon">🛒</div>
            <h3 class="cart-modal-title">Item Added!</h3>
            <p class="cart-modal-text" id="cartModalText">Product has been added to your shopping cart.</p>
            <button type="button" class="cart-modal-btn" onclick="closeCartModal()">Continue Shopping</button>
        </div>
    </div>

<script>
function addToCart(productId, productName) {
    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('add_to_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cartModalText').innerText = `"${productName}" was added to your cart.`;
            document.getElementById('cartModal').classList.add('active');
        } else {
            alert(data.message || 'Error adding item to cart.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}

function closeCartModal() {
    document.getElementById('cartModal').classList.remove('active');
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('cartModal');
    if (event.target === modal) {
        closeCartModal();
    }
});
</script>
</body>
</html>