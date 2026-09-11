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
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['username']); 

$toastMessage = "";
$toastType = "";

// ==========================================
// ADD TO CART HANDLER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!$isLoggedIn) {
        header("Location: /MainPhp/Authentication/Login.php");
        exit();
    }

    $productId = intval($_POST['product_id']);
    $qty = 1;

    // Verify product in database
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();

    if ($product && $product['quantity'] > 0) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $currentInCart = $_SESSION['cart'][$productId] ?? 0;
        
        if (($currentInCart + $qty) <= $product['quantity']) {
            $_SESSION['cart'][$productId] = $currentInCart + $qty;
            $toastMessage = htmlspecialchars($product['product_name']) . " added to cart!";
            $toastType = "success";
        } else {
            $toastMessage = "Cannot add item. Reached stock limit (" . $product['quantity'] . " available).";
            $toastType = "error";
        }
    } else {
        $toastMessage = "Sorry, this product is currently out of stock.";
        $toastType = "error";
    }
}

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

// Calculate total items in cart
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solar Solutions Dashboard</title>
    <link rel="stylesheet" href="./Products/style.css">
    <style>
        /* Header Right Layout */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .login-btn-header {
            background: #ffb703;
            color: #000;
            padding: 8px 22px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
            display: inline-block;
        }
        .login-btn-header:hover {
            background: #ffa200;
            transform: translateY(-1px);
        }

        /* Modal Overlay & Box */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.75);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: #0d1726;
            border: 1px solid #1b2a40;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            color: #fff;
        }
        .modal-icon { font-size: 2.5rem; margin-bottom: 10px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 20px; justify-content: center; }
        .modal-btn {
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
        }
        .btn-login { background: #00f2fe; color: #000; }
        .btn-close { background: #1e293b; color: #94a3b8; }

        /* Floating Toast Notification */
        .toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #0d1726;
            color: #00f2fe;
            text-align: center;
            border: 1px solid #00f2fe;
            border-radius: 8px;
            padding: 14px 20px;
            position: fixed;
            z-index: 1001;
            right: 30px;
            bottom: 30px;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            opacity: 0;
            transition: opacity 0.3s, bottom 0.3s;
        }
        .toast.error { color: #ef4444; border-color: #ef4444; }
        .toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 40px;
        }

        .cart-badge {
            background: #00f2fe;
            color: #000;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 6px;
        }

        /* Out of stock disabled button styling */
        .btn-disabled {
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            background: #09101d !important;
            border: 1px solid #162235 !important;
            border-radius: 6px;
            color: #475569 !important;
            font-weight: bold;
            cursor: not-allowed !important;
            pointer-events: none;
            box-shadow: none !important;
        }

        /* Site Footer Styling */
        footer.site-footer {
            background: #040912;
            border-top: 1px solid #162235;
            padding: 40px 30px 20px 30px;
            margin-top: 60px;
            color: #94a3b8;
            font-size: 0.85rem;
            width: 100%;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-col h4 {
            color: #fff;
            font-size: 0.95rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .footer-col.highlight h4 {
            color: #00f2fe;
        }
        .footer-col p {
            line-height: 1.5;
            font-size: 0.8rem;
            color: #64748b;
        }
        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-col ul li {
            margin-bottom: 8px;
        }
        .footer-col ul li a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
            font-size: 0.8rem;
        }
        .footer-col ul li a:hover {
            color: #00f2fe;
        }

        .rewards-card {
            background: #0d1726;
            border: 1px solid #1b2a40;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
            margin: 15px 0 10px 0;
            font-weight: bold;
            color: #ffb703;
            letter-spacing: 1px;
            font-size: 0.85rem;
        }

        .app-store-btns {
            display: flex;
            gap: 8px;
        }
        .app-store-btn {
            background: #0d1726;
            border: 1px solid #1b2a40;
            border-radius: 4px;
            padding: 6px 10px;
            color: #00f2fe;
            text-decoration: none;
            font-size: 0.7rem;
            display: inline-block;
            line-height: 1.2;
            font-weight: 600;
        }

        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 20px;
        }
        .payment-badge {
            background: #0d1726;
            border: 1px solid #1b2a40;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            color: #cbd5e1;
        }

        .social-links {
            display: flex;
            gap: 8px;
        }
        .social-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #0d1726;
            border: 1px solid #1b2a40;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00f2fe;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .footer-bottom {
            border-top: 1px solid #162235;
            margin-top: 30px;
            padding-top: 18px;
            text-align: center;
            color: #64748b;
            font-size: 0.78rem;
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
            
            <?php if ($isLoggedIn): ?>
                <a href="Cart.php" class="nav-btn">My Cart <span class="cart-badge"><?= $cartCount ?></span></a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="../../MAIN.php" class="return-main-btn">Return to Main</a>
        </div>
    </aside>

    <!-- MAIN CATALOG INTERFACE -->
    <main>
        <!-- TOP HEADER BAR WITH SEARCH & LOGIN BUTTON -->
        <header>
            <h2 id="view-title">
                <?= isset($_GET['type']) && $_GET['type'] !== 'All' ? htmlspecialchars($_GET['type']) . 's' : 'All Products' ?> 
            </h2>

            <div class="header-actions">
                <div class="search-container">
                    <input type="text" id="catalog-search" placeholder="Search products..." oninput="searchCatalog()">
                </div>

                <?php if ($isLoggedIn): ?>
                    <span style="color: #00f2fe; font-size: 0.85rem; font-weight: bold;">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'Account') ?>
                    </span>
                    <a href="/MainPhp/Authentication/Logout.php" class="login-btn-header" style="background: #1e293b; color: #fff;">Logout</a>
                <?php else: ?>
                    <a href="/MainPhp/Authentication/Login.php" class="login-btn-header">Log In</a>
                <?php endif; ?>
            </div>
        </header>
        
        <!-- PRODUCT CATALOG GRID -->
        <div class="product-grid" id="productGrid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <div class="product-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <span class="badge-type" style="color: #00f2fe; font-size: 0.75rem; text-transform: uppercase; font-weight: bold;"><?= htmlspecialchars($item['product_type']) ?></span>
                        <h3 style="margin: 10px 0; font-size: 1.1rem; color: #fff;"><?= htmlspecialchars($item['product_name']) ?></h3>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #00f2fe;">$<?= number_format($item['price'], 2) ?></span>
                            
                            <?php if ($item['quantity'] <= 0): ?>
                                <span style="font-size: 0.8rem; color: #ef4444; font-weight: bold;">Out of Stock</span>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: #64748b;">Stock: <?= htmlspecialchars($item['quantity']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($item['quantity'] <= 0): ?>
                            <!-- OUT OF STOCK BUTTON -->
                            <button type="button" class="btn-disabled" disabled>
                                Out of Stock
                            </button>
                        <?php elseif ($isLoggedIn): ?>
                            <!-- LOGGED IN ADD TO CART -->
                            <form method="POST" style="margin-top: 15px;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" 
                                        class="btn-cart"
                                        style="width: 100%; padding: 10px; background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); border: none; border-radius: 6px; color: #000; font-weight: bold; cursor: pointer;">
                                        Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- GUEST LOGIN TRIGGER MODAL -->
                            <button type="button" 
                                    class="btn-cart"
                                    onclick="showLoginModal()"
                                    style="width: 100%; margin-top: 15px; padding: 10px; background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); border: none; border-radius: 6px; color: #000; font-weight: bold; cursor: pointer;">
                                    Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #94a3b8;" id="noProductsMsg">No products found in the database.</p>
        <?php endif; ?>
        </div>

        <!-- FOOTER SECTION -->
        <footer class="site-footer">
            <div class="footer-grid">
                <!-- Column 1: Rewards & Apps -->
                <div class="footer-col highlight">
                    <h4>Earn Rewards, Save More.</h4>
                    <p>Join Pure Solar Rewards and enjoy exclusive deals, points, and member perks.</p>
                    <div class="rewards-card">
                        PURE SOLAR<br><span style="font-size:0.7rem; color:#94a3b8;">REWARDS</span>
                    </div>
                    <div class="app-store-btns">
                        <a href="#" class="app-store-btn">GET IT ON<br><span style="color:#fff;">Google Play</span></a>
                        <a href="#" class="app-store-btn">Download on the<br><span style="color:#fff;">App Store</span></a>
                    </div>
                </div>

                <!-- Column 2: Get to Know Us -->
                <div class="footer-col">
                    <h4>Get to Know Us</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Find Our Store</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>

                <!-- Column 3: Support -->
                <div class="footer-col">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Terms and Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">FAQ & Help</a></li>
                    </ul>
                </div>

                <!-- Column 4: Payment Methods & Socials -->
                <div class="footer-col">
                    <h4>We Accept:</h4>
                    <div class="payment-methods">
                        <span class="payment-badge">MasterCard</span>
                        <span class="payment-badge">VISA</span>
                        <span class="payment-badge">BancNet</span>
                        <span class="payment-badge">GCash</span>
                        <span class="payment-badge">Maya</span>
                        <span class="payment-badge">Billease</span>
                    </div>

                    <h4 style="margin-top: 15px;">Stay Connected with Us:</h4>
                    <div class="social-links">
                        <a href="#" class="social-icon">f</a>
                        <a href="#" class="social-icon">♪</a>
                        <a href="#" class="social-icon">📷</a>
                        <a href="#" class="social-icon">in</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                Copyright © 2026 Pure Power Systems. All Rights Reserved
            </div>
        </footer>
    </main>

    <!-- AUTHENTICATION REQUIRED MODAL -->
    <div id="loginModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-icon">&#128274;</div>
            <h2>Authentication Required</h2>
            <p style="margin-top: 10px; color: #94a3b8; font-size: 0.9rem;">Please log in to your account to add items to your shopping cart.</p>
            <div class="modal-actions">
                <a href="/MainPhp/Authentication/Login.php" class="modal-btn btn-login">Login / Sign Up</a>
                <button type="button" class="modal-btn btn-close" onclick="closeLoginModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- FLOATING TOAST NOTIFICATION -->
    <div class="toast <?= $toastType ?>" id="toast-banner"><?= $toastMessage ?></div>

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

        function searchCatalog() {
            const query = document.getElementById('catalog-search').value.toLowerCase();
            const cards = document.querySelectorAll('.product-card');
            
            cards.forEach(card => {
                const title = card.querySelector('h3').innerText.toLowerCase();
                const type = card.querySelector('.badge-type').innerText.toLowerCase();
                
                if (title.includes(query) || type.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('loginModal');
            if (event.target === modal) {
                closeLoginModal();
            }
        });

        <?php if (!empty($toastMessage)): ?>
            window.addEventListener('DOMContentLoaded', () => {
                const toast = document.getElementById('toast-banner');
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            });
        <?php endif; ?>
    </script>
</body>
</html>