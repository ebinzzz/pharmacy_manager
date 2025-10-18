<?php
// views/billing/pos.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_sale'])) {
    $customer_phone = !empty($_POST['customer_phone']) ? sanitize($_POST['customer_phone']) : null;
    $payment_method = sanitize($_POST['payment_method']);
    $discount = floatval($_POST['discount'] ?? 0);
    $cart_items = json_decode($_POST['cart_data'], true);
    
    if (empty($cart_items)) {
        $error = 'Cart is empty!';
    } else {
        try {
            $db->beginTransaction();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($cart_items as $item) {
                $subtotal += $item['quantity'] * $item['price'];
            }
            
            $discount_amount = ($subtotal * $discount) / 100;
            $taxable_amount = $subtotal - $discount_amount;
            $tax_amount = ($taxable_amount * TAX_RATE) / 100;
            $total_amount = $taxable_amount + $tax_amount;
            
            // Generate invoice number
            $invoice_number = generateInvoiceNumber();
            
            // Insert sale
            $query = "INSERT INTO sales (invoice_number, customer_phone, subtotal, discount, tax_amount, total_amount, payment_method, cashier_id) 
                      VALUES (:invoice_number, :customer_phone, :subtotal, :discount, :tax_amount, :total_amount, :payment_method, :cashier_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':invoice_number', $invoice_number);
            $stmt->bindParam(':customer_phone', $customer_phone);
            $stmt->bindParam(':subtotal', $subtotal);
            $stmt->bindParam(':discount', $discount_amount);
            $stmt->bindParam(':tax_amount', $tax_amount);
            $stmt->bindParam(':total_amount', $total_amount);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->bindParam(':cashier_id', $_SESSION['user_id']);
            $stmt->execute();
            
            $sale_id = $db->lastInsertId();
            
            // Insert sale items and update stock
            foreach ($cart_items as $item) {
                $item_total = $item['quantity'] * $item['price'];
                
                // Insert sale item
                $query = "INSERT INTO sale_items (sale_id, medicine_id, batch_number, quantity, unit_price, subtotal, total) 
                          VALUES (:sale_id, :medicine_id, :batch_number, :quantity, :unit_price, :subtotal, :total)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':sale_id', $sale_id);
                $stmt->bindParam(':medicine_id', $item['id']);
                $stmt->bindParam(':batch_number', $item['batch_number']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':unit_price', $item['price']);
                $stmt->bindParam(':subtotal', $item_total);
                $stmt->bindParam(':total', $item_total);
                $stmt->execute();
                
                // Update stock
                $query = "UPDATE medicines SET stock_quantity = stock_quantity - :quantity WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':id', $item['id']);
                $stmt->execute();
            }
            
            // Update customer tracking if phone provided
            if ($customer_phone) {
                $query = "INSERT INTO customer_tracking (phone_number, first_purchase_date, last_purchase_date, total_purchases, total_amount_spent) 
                          VALUES (:phone, NOW(), NOW(), 1, :amount) 
                          ON DUPLICATE KEY UPDATE 
                          last_purchase_date = NOW(), 
                          total_purchases = total_purchases + 1, 
                          total_amount_spent = total_amount_spent + :amount";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':phone', $customer_phone);
                $stmt->bindParam(':amount', $total_amount);
                $stmt->execute();
            }
            
            $db->commit();
            
            // Redirect to invoice page
            header('Location: invoice.php?invoice=' . $invoice_number);
            exit();
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error processing sale: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Point of Sale - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            height: calc(100vh - 150px);
        }
        
        .medicine-search {
            background: white;
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
        }
        
        .cart-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .medicine-item {
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .medicine-item:hover {
            border-color: var(--primary-color);
            background: #f8fafc;
        }
        
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .qty-controls {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: var(--primary-color);
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .cart-summary {
            background: #f8fafc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-row.total {
            border-top: 2px solid var(--border-color);
            padding-top: 10px;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>💳 Point of Sale (POS)</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="pos-container">
                <!-- Medicine Search Section -->
                <div class="medicine-search">
                    <h3>Search Medicine</h3>
                    <div class="form-group">
                        <input type="text" id="searchInput" placeholder="Search by medicine name or generic name..." onkeyup="searchMedicine()">
                    </div>
                    
                    <div id="medicineResults"></div>
                </div>
                
                <!-- Cart Section -->
                <div class="cart-section">
                    <h3>Cart</h3>
                    
                    <div class="cart-items" id="cartItems">
                        <p style="text-align: center; color: #94a3b8;">Cart is empty</p>
                    </div>
                    
                    <div class="cart-summary" id="cartSummary" style="display: none;">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">₹ 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount (<span id="discountPercent">0</span>%):</span>
                            <span id="discountAmount">₹ 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (<?php echo TAX_RATE; ?>%):</span>
                            <span id="taxAmount">₹ 0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="totalAmount">₹ 0.00</span>
                        </div>
                    </div>
                    
                    <form method="POST" id="checkoutForm">
                        <div class="form-group">
                            <label>Customer Phone (Optional)</label>
                            <input type="text" name="customer_phone" placeholder="10 digit mobile number" maxlength="10" pattern="[0-9]{10}">
                        </div>
                        
                        <div class="form-group">
                            <label>Discount (%)</label>
                            <input type="number" name="discount" id="discountInput" value="0" min="0" max="100" step="0.01" onchange="updateCart()">
                        </div>
                        
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="upi">UPI</option>
                            </select>
                        </div>
                        
                        <input type="hidden" name="cart_data" id="cartData">
                        <button type="submit" name="process_sale" class="btn btn-success btn-block" id="checkoutBtn" disabled>
                            Process Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let cart = [];
        
        // Search medicine
        function searchMedicine() {
            const searchTerm = document.getElementById('searchInput').value;
            
            if (searchTerm.length < 2) {
                document.getElementById('medicineResults').innerHTML = '';
                return;
            }
            
            fetch('search_medicine.php?term=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(data => {
                    displayMedicines(data);
                });
        }
        
        function displayMedicines(medicines) {
            const container = document.getElementById('medicineResults');
            
            if (medicines.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #94a3b8;">No medicines found</p>';
                return;
            }
            
            let html = '';
            medicines.forEach(medicine => {
                html += `
                    <div class="medicine-item" onclick='addToCart(${JSON.stringify(medicine)})'>
                        <strong>${medicine.medicine_name}</strong><br>
                        <small>${medicine.generic_name || ''}</small><br>
                        <small>Batch: ${medicine.batch_number} | Stock: ${medicine.stock_quantity}</small><br>
                        <strong>MRP: ₹${medicine.mrp}</strong>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }
        
        function addToCart(medicine) {
            if (medicine.stock_quantity <= 0) {
                alert('Out of stock!');
                return;
            }
            
            // Check if already in cart
            const existing = cart.find(item => item.id === medicine.id);
            
            if (existing) {
                if (existing.quantity < medicine.stock_quantity) {
                    existing.quantity++;
                } else {
                    alert('Cannot add more than available stock!');
                    return;
                }
            } else {
                cart.push({
                    id: medicine.id,
                    name: medicine.medicine_name,
                    batch_number: medicine.batch_number,
                    price: parseFloat(medicine.mrp),
                    quantity: 1,
                    max_stock: medicine.stock_quantity
                });
            }
            
            updateCart();
        }
        
        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }
        
        function updateQuantity(index, change) {
            const item = cart[index];
            const newQty = item.quantity + change;
            
            if (newQty <= 0) {
                removeFromCart(index);
            } else if (newQty <= item.max_stock) {
                item.quantity = newQty;
                updateCart();
            } else {
                alert('Cannot exceed available stock!');
            }
        }
        
        function updateCart() {
            const container = document.getElementById('cartItems');
            const summaryDiv = document.getElementById('cartSummary');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            if (cart.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #94a3b8;">Cart is empty</p>';
                summaryDiv.style.display = 'none';
                checkoutBtn.disabled = true;
                return;
            }
            
            let html = '';
            let subtotal = 0;
            
            cart.forEach((item, index) => {
                const itemTotal = item.quantity * item.price;
                subtotal += itemTotal;
                
                html += `
                    <div class="cart-item">
                        <div>
                            <strong>${item.name}</strong><br>
                            <small>${item.batch_number}</small><br>
                            <small>₹${item.price} x ${item.quantity} = ₹${itemTotal.toFixed(2)}</small>
                        </div>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn" onclick="updateQuantity(${index}, -1)">-</button>
                            <span style="padding: 0 10px;">${item.quantity}</span>
                            <button type="button" class="qty-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeFromCart(${index})" style="margin-left: 10px;">✕</button>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Calculate totals
            const discount = parseFloat(document.getElementById('discountInput').value || 0);
            const discountAmount = (subtotal * discount) / 100;
            const taxableAmount = subtotal - discountAmount;
            const taxAmount = (taxableAmount * <?php echo TAX_RATE; ?>) / 100;
            const total = taxableAmount + taxAmount;
            
            document.getElementById('subtotal').textContent = '₹ ' + subtotal.toFixed(2);
            document.getElementById('discountPercent').textContent = discount.toFixed(2);
            document.getElementById('discountAmount').textContent = '₹ ' + discountAmount.toFixed(2);
            document.getElementById('taxAmount').textContent = '₹ ' + taxAmount.toFixed(2);
            document.getElementById('totalAmount').textContent = '₹ ' + total.toFixed(2);
            
            document.getElementById('cartData').value = JSON.stringify(cart);
            
            summaryDiv.style.display = 'block';
            checkoutBtn.disabled = false;
        }
    </script>
</body>
</html>