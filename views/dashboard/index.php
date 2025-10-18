<?php
// views/dashboard/index.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [
    'today_sales' => 0,
    'total_medicines' => 0,
    'low_stock' => 0,
    'expired' => 0
];

// Today's sales
$query = "SELECT SUM(total_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['today_sales'] = $result['total'] ?? 0;

// Total medicines
$query = "SELECT COUNT(*) as total FROM medicines";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['total_medicines'] = $result['total'];

// Low stock
$query = "SELECT COUNT(*) as total FROM medicines WHERE stock_quantity <= reorder_level";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['low_stock'] = $result['total'];

// Expired medicines
$query = "SELECT COUNT(*) as total FROM medicines WHERE expiry_date < CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['expired'] = $result['total'];

// Recent sales
$query = "SELECT s.*, u.full_name as cashier_name 
          FROM sales s 
          LEFT JOIN users u ON s.cashier_id = u.id 
          ORDER BY s.sale_date DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Low stock medicines
$query = "SELECT * FROM medicines WHERE stock_quantity <= reorder_level ORDER BY stock_quantity ASC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$low_stock_medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <strong><?php echo $_SESSION['full_name']; ?></strong>
                            <br>
                            <small><?php echo ucfirst($_SESSION['role']); ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Today's Sales</span>
                        <span class="stat-icon">💰</span>
                    </div>
                    <div class="stat-value"><?php echo formatCurrency($stats['today_sales']); ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-header">
                        <span class="stat-label">Total Medicines</span>
                        <span class="stat-icon">💊</span>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_medicines']; ?></div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-header">
                        <span class="stat-label">Low Stock Items</span>
                        <span class="stat-icon">⚠️</span>
                    </div>
                    <div class="stat-value"><?php echo $stats['low_stock']; ?></div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-header">
                        <span class="stat-label">Expired Medicines</span>
                        <span class="stat-icon">❌</span>
                    </div>
                    <div class="stat-value"><?php echo $stats['expired']; ?></div>
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Recent Sales</h3>
                    <a href="../billing/pos.php" class="btn btn-primary">New Sale</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer Phone</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Cashier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_sales) > 0): ?>
                            <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['invoice_number']; ?></td>
                                    <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                    <td><?php echo $sale['customer_phone'] ?? 'Walk-in'; ?></td>
                                    <td><?php echo formatCurrency($sale['total_amount']); ?></td>
                                    <td><span class="badge badge-success"><?php echo ucfirst($sale['payment_method']); ?></span></td>
                                    <td><?php echo $sale['cashier_name']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No sales found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <br>

            <!-- Low Stock Alert -->
            <?php if (count($low_stock_medicines) > 0): ?>
            <div class="table-container">
                <div class="table-header">
                    <h3>Low Stock Alert</h3>
                    <a href="../medicines/list.php" class="btn btn-secondary">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Generic Name</th>
                            <th>Batch Number</th>
                            <th>Stock</th>
                            <th>Reorder Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_medicines as $medicine): ?>
                            <tr>
                                <td><?php echo $medicine['medicine_name']; ?></td>
                                <td><?php echo $medicine['generic_name']; ?></td>
                                <td><?php echo $medicine['batch_number']; ?></td>
                                <td><strong><?php echo $medicine['stock_quantity']; ?></strong></td>
                                <td><?php echo $medicine['reorder_level']; ?></td>
                                <td>
                                    <?php if ($medicine['stock_quantity'] == 0): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Low Stock</span>
                                    <?php endif; ?>
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