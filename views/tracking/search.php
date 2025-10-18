<?php
// views/tracking/search.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

$database = new Database();
$db = $database->getConnection();

$phone_number = '';
$customer_data = null;
$purchase_history = [];

if (isset($_GET['phone'])) {
    $phone_number = sanitize($_GET['phone']);
    
    // Get customer tracking data
    $query = "SELECT * FROM customer_tracking WHERE phone_number = :phone";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':phone', $phone_number);
    $stmt->execute();
    $customer_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get purchase history
    $query = "SELECT s.*, u.full_name as cashier_name 
              FROM sales s 
              LEFT JOIN users u ON s.cashier_id = u.id 
              WHERE s.customer_phone = :phone 
              ORDER BY s.sale_date DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':phone', $phone_number);
    $stmt->execute();
    $purchase_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Tracking - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>🔍 Customer Tracking</h1>
            </div>
            
            <!-- Search Section -->
            <div class="table-container" style="margin-bottom: 20px;">
                <div class="table-header">
                    <h3>Search by Phone Number</h3>
                </div>
                <form method="GET" style="padding: 20px;">
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="phone" placeholder="Enter 10 digit phone number" 
                               value="<?php echo $phone_number; ?>" 
                               pattern="[0-9]{10}" 
                               maxlength="10" 
                               style="flex: 1;" required>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
            
            <?php if ($phone_number && !$customer_data && count($purchase_history) == 0): ?>
                <div class="alert alert-warning">No purchase history found for this phone number.</div>
            <?php endif; ?>
            
            <?php if ($customer_data): ?>
            <!-- Customer Summary -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Purchases</span>
                        <span class="stat-icon">🛒</span>
                    </div>
                    <div class="stat-value"><?php echo $customer_data['total_purchases']; ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-header">
                        <span class="stat-label">Total Spent</span>
                        <span class="stat-icon">💰</span>
                    </div>
                    <div class="stat-value"><?php echo formatCurrency($customer_data['total_amount_spent']); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">First Purchase</span>
                        <span class="stat-icon">📅</span>
                    </div>
                    <div class="stat-value" style="font-size: 16px;">
                        <?php echo formatDate($customer_data['first_purchase_date']); ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Last Purchase</span>
                        <span class="stat-icon">🕒</span>
                    </div>
                    <div class="stat-value" style="font-size: 16px;">
                        <?php echo formatDate($customer_data['last_purchase_date']); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (count($purchase_history) > 0): ?>
            <!-- Purchase History -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Purchase History</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Cashier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchase_history as $sale): ?>
                        <?php
                        // Get item count
                        $query = "SELECT COUNT(*) as item_count FROM sale_items WHERE sale_id = :sale_id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':sale_id', $sale['id']);
                        $stmt->execute();
                        $item_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <tr>
                            <td><?php echo $sale['invoice_number']; ?></td>
                            <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                            <td><?php echo $item_data['item_count']; ?> items</td>
                            <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                            <td><span class="badge badge-success"><?php echo ucfirst($sale['payment_method']); ?></span></td>
                            <td><?php echo $sale['cashier_name']; ?></td>
                            <td>
                                <a href="../billing/invoice.php?invoice=<?php echo $sale['invoice_number']; ?>" 
                                   class="btn btn-sm btn-primary" target="_blank">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>