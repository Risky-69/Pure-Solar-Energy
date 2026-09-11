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
        /* Header Layout Styling */
        header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
            width: 100%;
        }

        #view-title {
            white-space: nowrap;
            margin: 0;
            font-size: 1.5rem;
            color: #fff;
        }

        .search-container {
            flex: 1;
            display: flex;
        }

        .search-container input {
            width: 100%;
            padding: 10px 16px;
            background: #111a2e;
            border: 1px solid #1e293b;
            border-radius: 8px;
            color: #fff;
            outline: none;
            font-size: 0.95rem;
            transition: border-color 0.2s ease-in-out;
        }

        .search-container input:focus {
            border-color: #00f2fe;
        }

        /* Log In Button Styling */
        .login-btn-header {
            background: #ffb703;
            color: #000;
            padding: 9px 22px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.2s, transform 0.2s;
            white-space: nowrap;
            display: inline-block;
        }
        .login-btn-header:hover {
            background: #ffa200;
            transform: translateY(-1px);
        }

        /* Profile "Me ▾" Dropdown Styling */
        .user-menu-wrapper {
            position: relative;
            display: inline-block;
            z-index: 1001;
        }

        .me-btn {
            background: #111a2e;
            border: 1px solid #1e293b;
            color: #00f2fe;
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .me-btn:hover {
            border-color: #00f2fe;
            background: #16223b;
        }

        .user-dropdown-menu {
            position: absolute;
            top: 130%;
            right: 0;
            width: 200px;
            background: #0d1726;
            border: 1px solid #1e293b;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 242, 254, 0.1);
            display: none;
            flex-direction: column;
            z-index: 1003;
            overflow: hidden;
            padding: 6px 0;
        }

        .user-dropdown-menu.active {
            display: flex !important;
        }

        .dropdown-header {
            padding: 10px 16px;
            font-size: 0.8rem;
            color: #94a3b8;
            border-bottom: 1px solid #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .dropdown-item {
            padding: 10px 16px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .dropdown-item:hover {
            background: #16223b;
            color: #00f2fe;
        }

        .dropdown-divider {
            height: 1px;
            background: #1e293b;
            margin: 4px 0;
        }

        .logout-link {
            color: #ef4444;
        }

        .logout-link:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        /* Product Cards & Out of Stock */
        .product-card.out-of-stock {
            opacity: 0.45;
            filter: grayscale(80%);
            position: relative;
        }

        .btn-disabled {
            background: #1e293b !important;
            color: #64748b !important;
            border: 1px solid #334155 !important;
            cursor: not-allowed !important;
            pointer-events: none;
        }

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

        /* Site Footer Styling */
        footer.site-footer {
            background: #040912;
            border-top: 1px solid #162235;
            padding: 40px 30px 20px 30px;
            margin-top: 60px;
            color: #94a3b8;
            font-size: 0.85rem;
            width: 100%;
            box-sizing: border-box;
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
            
            <!-- Conditional Header Auth Option -->

            <div class="user-menu-wrapper">
                <button type="button" id="meBtn" class="auth-link auth-signup-btn">
                    Me ▾
                </button>

                <div id="userDropdownMenu" class="user-dropdown-menu">
                    <div class="dropdown-header">My Account</div>
                    <a href="/User/MyAccount.php" class="dropdown-item">👤 View Profile</a>
                    <a href="Orders.php" class="dropdown-item">📦 View Orders</a>
                    <a href="Cart.php" class="dropdown-item">🛒 Shopping Cart</a>
                    <div class="dropdown-divider"></div>
                    <a href="Logout.php" class="dropdown-item logout-link">🚪 Log Out</a>
                </div>
            </div>

        </header>
        
        <!-- PRODUCT GRID -->
        <div class="product-grid" id="productGrid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $item): ?>
                <?php $isOutOfStock = intval($item['quantity']) <= 0; ?>
                
                <div class="product-card <?= $isOutOfStock ? 'out-of-stock' : '' ?>" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="badge-type" style="color: #00f2fe; font-size: 0.75rem; text-transform: uppercase; font-weight: bold;">
                                <?= htmlspecialchars($item['product_type']) ?>
                            </span>
                            <?php if ($isOutOfStock): ?>
                                <span style="color: #ef4444; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; background: rgba(239, 68, 68, 0.15); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(239, 68, 68, 0.3);">
                                    Out of Stock
                                </span>
                            <?php endif; ?>
                        </div>

                        <h3 style="margin: 10px 0; font-size: 1.1rem; color: #fff;"><?= htmlspecialchars($item['product_name']) ?></h3>
                        <p style="color: #94a3b8; font-size: 0.85rem; line-height: 1.4; margin-bottom: 15px;"><?= htmlspecialchars($item['product_info'] ?? '') ?></p>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                            <span style="font-size: 1.2rem; font-weight: bold; color: <?= $isOutOfStock ? '#64748b' : '#00f2fe' ?>;">
                                $<?= number_format($item['price'], 2) ?>
                            </span>
                            <span style="font-size: 0.8rem; color: <?= $isOutOfStock ? '#ef4444' : '#64748b' ?>;">
                                Stock: <?= htmlspecialchars($item['quantity']) ?>
                            </span>
                        </div>
                        
                        <?php if ($isOutOfStock): ?>
                            <button type="button" 
                                    class="btn-cart btn-disabled" 
                                    disabled
                                    style="width: 100%; margin-top: 15px; padding: 10px; background: #1e293b; border: 1px solid #334155; border-radius: 6px; color: #64748b; font-weight: bold; cursor: not-allowed; pointer-events: none;">
                                Out of Stock
                            </button>
                        <?php else: ?>
                            <button type="button" 
                                    class="btn-cart"
                                    onclick="addToCart(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['product_name'])) ?>')"
                                    style="width: 100%; margin-top: 15px; padding: 10px; background: linear-gradient(90deg, #00f2fe 0%, #4facfe 100%); border: none; border-radius: 6px; color: #000; font-weight: bold; cursor: pointer;">
                                Add to Cart
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #94a3b8;">No products found in the database.</p>
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
// Toggle Profile Menu Dropdown
const meBtn = document.getElementById('meBtn');
const userDropdownMenu = document.getElementById('userDropdownMenu');

if (meBtn && userDropdownMenu) {
    meBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdownMenu.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!userDropdownMenu.contains(e.target) && e.target !== meBtn) {
            userDropdownMenu.classList.remove('active');
        }
    });
}

// Live Search Filter
const searchInput = document.getElementById('catalog-search');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const cards = document.querySelectorAll('.product-card');
        
        cards.forEach(card => {
            const title = card.querySelector('h3') ? card.querySelector('h3').innerText.toLowerCase() : '';
            const type = card.querySelector('.badge-type') ? card.querySelector('.badge-type').innerText.toLowerCase() : '';
            
            if (title.includes(query) || type.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
}

// Add To Cart Ajax Handler
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