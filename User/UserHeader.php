<header class="navbar">
    <div class="logo-container">
        <span class="logo-accent"></span><span class="logo-main">PURE SOLAR</span>
        <div class="tagline">Pure Power Pure Savings</div>
    </div>
    <nav class="nav-links">
        <a href="MAIN.php">Home</a>
        <a href="UserOptions/View Products/Products.php" class="prod-link product-btn">Products</a>
        <a href="MAIN.php#contacts">Contacts</a>

        <a href="Cart.php" class="cart-btn" aria-label="Shopping Cart">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="cart-count">0</span>
        </a>

        <div class="user-menu-wrapper" style="position: relative; display: inline-block;">
            <button class="auth-link auth-signup-btn" onclick="toggleUserDropdown(event)" style="border: none; cursor: pointer;">
                Me ▾
            </button>

            <div id="userDropdown" class="user-dropdown-menu">
                <div class="dropdown-header">My Account</div>
                <a href="MyAccount.php" class="dropdown-item">👤 View Profile</a>
                <a href="Cart.php" class="dropdown-item">🛒 My Orders / Cart</a>
                <div class="dropdown-divider"></div>
                <a href="../MAIN.php" class="dropdown-item logout-link">🚪 Log Out</a>
            </div>
        </div>
    </nav>
</header>

<!-- DROPDOWN STYLES -->
<style>
.user-dropdown-menu {
    position: absolute;
    top: 125%;
    right: 0;
    width: 200px;
    background: #0d1726;
    border: 1px solid #1e293b;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5), 0 0 15px rgba(0, 242, 254, 0.1);
    display: none;
    flex-direction: column;
    z-index: 1000;
    overflow: hidden;
    padding: 6px 0;
}

.user-dropdown-menu.active {
    display: flex;
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
</style>

<!-- DROPDOWN SCRIPT -->
<script>
function toggleUserDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('userDropdown');
    menu.classList.toggle('active');
}

window.addEventListener('click', function(event) {
    const menu = document.getElementById('userDropdown');
    if (menu && menu.classList.contains('active')) {
        menu.classList.remove('active');
    }
});
</script>