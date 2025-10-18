<?php
// config/config.php
session_start();

// Site Configuration
define('SITE_NAME', 'Pharmacy Management System');
define('SITE_URL', 'http://localhost/pharmacy-management/');
define('CURRENCY', '₹');

// Tax Configuration
define('TAX_RATE', 12); // 12% GST

// Date/Time Configuration
date_default_timezone_set('Asia/Kolkata');

// File Upload Configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Pagination
define('RECORDS_PER_PAGE', 20);

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . 'views/auth/login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!hasRole('admin')) {
        header('Location: ' . SITE_URL . 'views/dashboard/index.php');
        exit();
    }
}

// Generate invoice number
function generateInvoiceNumber() {
    return 'INV' . date('Ymd') . rand(1000, 9999);
}

// Generate purchase order number
function generatePONumber() {
    return 'PO' . date('Ymd') . rand(1000, 9999);
}

// Format currency
function formatCurrency($amount) {
    return CURRENCY . ' ' . number_format($amount, 2);
}

// Format date
function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

// Format datetime
function formatDateTime($datetime) {
    return date('d-m-Y h:i A', strtotime($datetime));
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        header('Location: ' . SITE_URL . 'views/auth/login.php?timeout=1');
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
}
?>