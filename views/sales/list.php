<?php
// views/sales/list.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

$database = new Database();
$db = $database->getConnection();

$date_from = isset($_GET['date_from']) ? sanitize($_GET['date_from']) : date('Y-m-d');
$date_to = isset($_GET['date_to']) ? sanitize($_GET['date_to']) : date('Y-m-d');
$payment_method = isset($_GET['payment_method']) ? sanitize($_GET['payment_method']) : '';

// Build query
$query = "SELECT s.*, u.full_name as cashier_name 
          FROM sales s 
          LEFT JOIN users u ON s.cashier_id = u.id 
          WHERE DATE(s.sale_date) BETWEEN :date_from AND :date_to";

if (!empty($payment_method)) {
    $query .= " AND s.payment_method = :payment_method";
}

$query .= " ORDER BY s.sale_date DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':date_from', $date_from);
$stmt->bindParam(':date_to', $date_to);

if (!empty($payment_method)) {
    $stmt->bindParam(':payment_method', $payment_method);
}

$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_sales = 0;
$total_tax = 0;
foreach ($sales as $sale) {
    $total_sales += $sale['total_amount'];
    $total_tax += $sale['tax_amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Records - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>📋 Sales Records</h1>
            </div>
            
            <!-- Filter Section -->
            <div class="table-container" style="margin-bottom: 20px;">
                <div class="table-header">
                    <form method="GET" style="display: flex; gap: 10px; width: 100%; flex-wrap: wrap;">
                        <div class="form-group" style="margin: 0;">
                            <label>From Date</label>
                            <input type="date" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>To Date</label>
                            <input type="date" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label>Payment Method</label>
                            <select name="payment_method">
                                <option value="">All Methods</option>
                                <option value="cash" <?php echo ($payment_method == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                <option value="card" <?php echo ($payment_method == 'card') ? 'selected' : ''; ?>>Card</option>
                                <option value="upi" <?php echo ($payment_method == 'upi') ? 'selected' : ''; ?>>UPI</option>
                            </select>
                        </div>
                        <div style="align-self: flex-end;">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="list.php" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Summary Cards -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Sales</span>
                        <span class="stat-icon">📊</span>
                    </div>
                    <div class="stat-value"><?php echo count($sales); ?></div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-header">
                        <span class="stat-label">Total Revenue</span>
                        <span class="stat-icon">💰</span>
                    </div>
                    <div class="stat-value" style="font-size: 24px;"><?php echo formatCurrency($total_sales); ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-label">Total Tax Collected</span>
                        <span class="stat-icon">🧾</span>
                    </div>
                    <div class="stat-value" style="font-size: 24px;"><?php echo formatCurrency($total_tax); ?></div>
                </div>
            </div>
            
            <!-- Sales Table -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Sales List</h3>
                    <button onclick="window.print()" class="btn btn-success">Print Report</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date & Time</th>
                            <th>Customer Phone</th>
                            <th>Subtotal</th>
                            <th>Discount</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Cashier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sales) > 0): ?>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><strong><?php echo $sale['invoice_number']; ?></strong></td>
                                <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                <td><?php echo $sale['customer_phone'] ?? 'Walk-in'; ?></td>
                                <td><?php echo formatCurrency($sale['subtotal']); ?></td>
                                <td><?php echo formatCurrency($sale['discount']); ?></td>
                                <td><?php echo formatCurrency($sale['tax_amount']); ?></td>
                                <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                                <td><span class="badge badge-success"><?php echo ucfirst($sale['payment_method']); ?></span></td>
                                <td><?php echo $sale['cashier_name']; ?></td>
                                <td>
                                    <a href="../billing/invoice.php?invoice=<?php echo $sale['invoice_number']; ?>" 
                                       class="btn btn-sm btn-primary" target="_blank">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center;">No sales records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>