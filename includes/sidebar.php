<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 Pharmacy System</h2>
        <p><?php echo SITE_NAME; ?></p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo SITE_URL; ?>views/dashboard/index.php" class="<?php echo ($current_page == 'index.php' && strpos($_SERVER['PHP_SELF'], 'dashboard') !== false) ? 'active' : ''; ?>">
                <i>📊</i> Dashboard
            </a>
        </li>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/billing/pos.php" class="<?php echo ($current_page == 'pos.php') ? 'active' : ''; ?>">
                <i>💳</i> Billing / POS
            </a>
        </li>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/sales/list.php" class="<?php echo ($current_page == 'list.php' && strpos($_SERVER['PHP_SELF'], 'sales') !== false) ? 'active' : ''; ?>">
                <i>📋</i> Sales Records
            </a>
        </li>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/medicines/list.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'medicines') !== false) ? 'active' : ''; ?>">
                <i>💊</i> Medicines
            </a>
        </li>
        
        <?php if (hasRole('admin') || hasRole('pharmacist')): ?>
        <li>
            <a href="<?php echo SITE_URL; ?>views/purchases/list.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'purchases') !== false) ? 'active' : ''; ?>">
                <i>📦</i> Purchases
            </a>
        </li>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/suppliers/list.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'suppliers') !== false) ? 'active' : ''; ?>">
                <i>🏢</i> Suppliers
            </a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/tracking/search.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'tracking') !== false) ? 'active' : ''; ?>">
                <i>🔍</i> Customer Tracking
            </a>
        </li>
        
        <li>
            <a href="<?php echo SITE_URL; ?>views/reports/index.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'reports') !== false) ? 'active' : ''; ?>">
                <i>📈</i> Reports
            </a>
        </li>
        
        <?php if (hasRole('admin')): ?>
        <li>
            <a href="<?php echo SITE_URL; ?>views/users/list.php" class="<?php echo (strpos($_SERVER['PHP_SELF'], 'users') !== false) ? 'active' : ''; ?>">
                <i>👥</i> Users
            </a>
        </li>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-footer">
        <a href="<?php echo SITE_URL; ?>views/auth/logout.php" class="btn btn-danger btn-block">
            <i>🚪</i> Logout
        </a>
    </div>
</aside>