<?php
// views/billing/invoice.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();

$invoice_number = isset($_GET['invoice']) ? sanitize($_GET['invoice']) : '';

if (empty($invoice_number)) {
    header('Location: pos.php');
    exit();
}

// Get sale details
$query = "SELECT s.*, u.full_name as cashier_name 
          FROM sales s 
          LEFT JOIN users u ON s.cashier_id = u.id 
          WHERE s.invoice_number = :invoice_number";
$stmt = $db->prepare($query);
$stmt->bindParam(':invoice_number', $invoice_number);
$stmt->execute();
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sale) {
    header('Location: pos.php');
    exit();
}

// Get sale items
$query = "SELECT si.*, m.medicine_name, m.generic_name 
          FROM sale_items si 
          LEFT JOIN medicines m ON si.medicine_id = m.id 
          WHERE si.sale_id = :sale_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':sale_id', $sale['id']);
$stmt->execute();
$sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $invoice_number; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .invoice-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            text-align: center;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-header h1 {
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .invoice-info div {
            padding: 15px;
            background: #f8fafc;
            border-radius: 5px;
        }
        
        .invoice-table {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .invoice-summary {
            margin-left: auto;
            width: 300px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .summary-row.total {
            border-top: 2px solid var(--dark-color);
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 15px;
        }
        
        .invoice-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            color: var(--secondary-color);
        }
        
        .action-buttons {
            text-align: center;
            margin-bottom: 20px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="no-print action-buttons">
        <a href="pos.php" class="btn btn-primary">New Sale</a>
        <button onclick="window.print()" class="btn btn-success">Print Invoice</button>
        <a href="../dashboard/index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>🏥 <?php echo SITE_NAME; ?></h1>
            <p>Complete Pharmacy Solution</p>
            <p><strong>GST No:</strong> 29XXXXX1234X1Z5</p>
        </div>
        
        <div class="invoice-info">
            <div>
                <strong>Invoice Number:</strong><br>
                <?php echo $sale['invoice_number']; ?><br><br>
                <strong>Date:</strong><br>
                <?php echo formatDateTime($sale['sale_date']); ?>
            </div>
            
            <div>
                <strong>Customer Phone:</strong><br>
                <?php echo $sale['customer_phone'] ?? 'Walk-in Customer'; ?><br><br>
                <strong>Payment Method:</strong><br>
                <?php echo strtoupper($sale['payment_method']); ?>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine Name</th>
                    <th>Batch</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sr = 1;
                foreach ($sale_items as $item): 
                ?>
                <tr>
                    <td><?php echo $sr++; ?></td>
                    <td>
                        <strong><?php echo $item['medicine_name']; ?></strong><br>
                        <small><?php echo $item['generic_name']; ?></small>
                    </td>
                    <td><?php echo $item['batch_number']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo formatCurrency($item['unit_price']); ?></td>
                    <td><?php echo formatCurrency($item['total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="invoice-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span><?php echo formatCurrency($sale['subtotal']); ?></span>
            </div>
            
            <?php if ($sale['discount'] > 0): ?>
            <div class="summary-row">
                <span>Discount:</span>
                <span>- <?php echo formatCurrency($sale['discount']); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="summary-row">
                <span>Tax (<?php echo TAX_RATE; ?>%):</span>
                <span><?php echo formatCurrency($sale['tax_amount']); ?></span>
            </div>
            
            <div class="summary-row total">
                <span>Total Amount:</span>
                <span><?php echo formatCurrency($sale['total_amount']); ?></span>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p><strong>Cashier:</strong> <?php echo $sale['cashier_name']; ?></p>
            <p>Thank you for your purchase!</p>
            <p style="margin-top: 20px; font-size: 12px;">
                This is a computer-generated invoice and does not require a signature.
            </p>
        </div>
    </div>
</body>
</html>