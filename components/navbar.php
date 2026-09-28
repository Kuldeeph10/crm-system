<?php
if (!isset($_SESSION['user_id'])) {
    return;
}
?>
<nav class="navbar">
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <a class="navbar-brand" href="#">
        <i class="fas fa-crm"></i> CRM System
    </a>
    <div class="navbar-right">
        <div class="user-dropdown">
            <button class="dropdown-toggle" onclick="toggleDropdown()">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
            </button>
            <div class="dropdown-menu" id="dropdownMenu">
                <a class="text-danger" href="<?php echo BASE_URL; ?>logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleDropdown() {
    const menu = document.getElementById('dropdownMenu');
    menu.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.querySelector('.user-dropdown');
    if (!dropdown.contains(event.target)) {
        document.getElementById('dropdownMenu').classList.remove('show');
    }
});

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}
</script>