<?php
// Always start the session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// 1. UPDATED: Check for user_id OR username to accurately confirm session state
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['username']); 

// Category filtering
$selectedType = $_GET['type'] ?? 'All';

if ($selectedType !== 'All' && !empty($selectedType)) {
    $typeQuery = rtrim($selectedType, 's'); 
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_type LIKE :type ORDER BY product_id DESC");
    $stmt->execute(['type' => "%$typeQuery%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY product_id DESC");
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
            <a href="../../../User/UserIndex.php" class="return-main-btn">Return to Main</a>
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
                        
                        <!-- 2. UPDATED: Unified button logic that fires JS depending on login state -->
                        <button type="button" 
                                class="btn-cart"
                                onclick="<?= $isLoggedIn ? "addToCart(" . $item['product_id'] . ")" : "showLoginModal()" ?>"
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
    <!-- <div id="loginModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">&#128274;</div>
            <h2>Authentication Required</h2>
            <p>Please log in to your account to add items to your shopping cart.</p>
            <div class="modal-actions">
                <a href="/MainPhp/Authentication/Login.php" class="modal-btn btn-login">Login / Sign Up</a>
                <button type="button" class="modal-btn btn-close" onclick="closeLoginModal()">Cancel</button>
            </div>
        </div>
    </div> -->

    <!-- FLOATING TOAST NOTIFICATION -->
    <div class="toast" id="toast-banner">Added to Cart!</div>

    <!-- 3. UPDATED: JavaScript includes AJAX submission and Toast triggers -->
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

// Handles submitting the item to Cart.php silently in the background
function addToCart(productId) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    // Ensure this path matches the exact URL route to your Cart.php file
    // Adjust path if Cart.php is in a subfolder (e.g., '/User/Cart.php' or 'Cart.php')
    fetch('Cart.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(async response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data.status === 'success') {
            showToast("Added to Cart!");
        } else {
            console.error('Database/PHP Error:', data.message);
            showToast("Failed to add item");
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showToast("Error adding item");
    });
}

// Triggers the pop-up notification
function showToast(message) {
    const toast = document.getElementById('toast-banner');
    if (toast) {
        toast.innerText = message;
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
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