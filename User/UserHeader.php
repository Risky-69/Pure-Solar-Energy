<header class="navbar">
    <div class="logo-container">
        <span class="logo-accent"></span><span class="logo-main">PURE SOLAR</span>
        <div class="tagline">Pure Power Pure Savings</div>
    </div>
    
    <nav class="nav-links">
        <!-- <a href="UserIndex.php">Home</a> -->
        <a href="UserOptions/View Products/Products.php?type=All" class="prod-link product-btn">Products</a>
        <a href="../User/UserOptions/Solar Calculator 2/Calculator.php">Solar Calculator</a>
        
        <!-- User Dropdown Menu -->
        <div class="user-menu-wrapper">
            <button type="button" id="meBtn" class="auth-link auth-signup-btn">
                Me ▾
            </button>

            <div id="userDropdownMenu" class="user-dropdown-menu">
                <div class="dropdown-header">My Account</div>
                <a href="../../../User/MyAccount.php" class="dropdown-item">👤 View Profile</a>
                <a href="Orders.php" class="dropdown-item">📦 View Orders</a>
                <a href="Cart.php" class="dropdown-item">🛒 Shopping Cart</a>
                <div class="dropdown-divider"></div>
                <a href="/../MAIN.php" class="dropdown-item logout-link">🚪 Log Out</a>
            </div>
        </div>
    </nav>
</header>
<script>
function toggleUserDropdown(event) {
    event.stopPropagation();
    const dropdown = document.getElementById("userDropdownMenu");
    dropdown.classList.toggle("active");
}

// Close dropdown when clicking outside
window.addEventListener("click", function() {
    const dropdown = document.getElementById("userDropdownMenu");
    if (dropdown && dropdown.classList.contains("active")) {
        dropdown.classList.remove("active");
    }
});
</script>
<!-- DROPDOWN STYLES -->
<style>
.user-menu-wrapper {
    position: relative;
    display: inline-block;
    z-index: 1001; /* Keeps button above hero section/overlays */
}

.auth-signup-btn {
    position: relative;
    z-index: 1002;
    cursor: pointer !important;
    pointer-events: auto !important;
    border: none;
}

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
    z-index: 1003;
    overflow: hidden;
    padding: 6px 0;
    padding-left: 50px;
}

.user-dropdown-menu.active {
    display: flex !important;
}

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

.dropdown a {
    color: pink;
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
document.addEventListener("DOMContentLoaded", function() {
    const meBtn = document.getElementById("meBtn");
    const dropdownMenu = document.getElementById("userDropdownMenu");

    if (meBtn && dropdownMenu) {
        meBtn.addEventListener("click", function(event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle("active");
        });

        // Close dropdown when clicking outside
        document.addEventListener("click", function(event) {
            if (!dropdownMenu.contains(event.target) && !meBtn.contains(event.target)) {
                dropdownMenu.classList.remove("active");
            }
        });
    }
});
</script>