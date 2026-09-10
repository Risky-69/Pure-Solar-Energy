<?php
// Always start the session first
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

// Session state check
$isLoggedIn = isset($_SESSION['user_id']); 

// Category filtering
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
            <a href="../../MAIN.php" class="return-main-btn">Return to Main</a>
        </div>
    </aside>

    <!-- MAIN CATALOG INTERFACE -->
    <main>
        <!-- TOP HEADER BAR -->
        <header>
            <h2 id="view-title">
                <?= isset($_GET['type']) && $_GET['type'] !== 'All' ? htmlspecialchars($_GET['type']) . 's' : 'All Products' ?> 
            </h2>
            <div class="search-container">
                <input type="text" id="catalog-search" placeholder="Search products..." oninput="searchCatalog()">
            </div>
        </header>
        
        <!-- PRODUCT CATALOG GRID -->
        <div class="product-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card">
                    <div>
                        <span class="badge-type" style="color: #00f2fe; font-size: 0.75rem; text-transform: uppercase; font-weight: bold;"><?= htmlspecialchars($item['product_type']) ?></span>
                        <h3 style="margin: 10px 0; font-size: 1.1rem; color: #fff;"><?= htmlspecialchars($item['product_name']) ?></h3>
                        <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.4; margin-bottom: 15px;"><?= htmlspecialchars($item['product_info']) ?></p>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #00f2fe;">$<?= number_format($item['price'], 2) ?></span>
                            <span style="font-size: 0.8rem; color: #64748b;">Stock: <?= htmlspecialchars($item['quantity']) ?></span>
                        </div>
                        
                        <button type="button" 
                                class="btn-cart"
                                onclick="showLoginModal()"
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

    <!-- AUTHENTICATION REQUIRED MODAL -->
    <div id="loginModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">&#128274;</div>
            <h2>Authentication Required</h2>
            <p>Please log in to your account to add items to your shopping cart.</p>
            <div class="modal-actions">
            <a href="/MainPhp/Authentication/Login.php" class="modal-btn btn-login">Login / Sign Up</a>                <button type="button" class="modal-btn btn-close" onclick="closeLoginModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div class="toast" id="toast-banner">Added to Quote!</div>

    <!-- SINGLE SCRIPT SOURCE -->
<script>
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('active');
    }
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.remove('active');
    }
}

function addToCart(productId) {
    console.log("Adding product " + productId + " to cart.");
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target === modal) {
        closeLoginModal();
    }
});
</script>
</body>
</html>