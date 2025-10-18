<?php
// views/medicines/add.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

if (!hasRole('admin') && !hasRole('pharmacist')) {
    header('Location: ../../views/dashboard/index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Get categories
$query = "SELECT * FROM categories ORDER BY category_name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $medicine_name = sanitize($_POST['medicine_name']);
    $generic_name = sanitize($_POST['generic_name']);
    $category_id = sanitize($_POST['category_id']);
    $manufacturer = sanitize($_POST['manufacturer']);
    $batch_number = sanitize($_POST['batch_number']);
    $expiry_date = sanitize($_POST['expiry_date']);
    $unit_price = floatval($_POST['unit_price']);
    $mrp = floatval($_POST['mrp']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $reorder_level = intval($_POST['reorder_level']);
    $rack_location = sanitize($_POST['rack_location']);
    $description = sanitize($_POST['description']);
    
    if (empty($medicine_name) || empty($category_id) || empty($batch_number)) {
        $error = 'Please fill all required fields';
    } else {
        try {
            $query = "INSERT INTO medicines (medicine_name, generic_name, category_id, manufacturer, batch_number, expiry_date, unit_price, mrp, stock_quantity, reorder_level, rack_location, description) 
                      VALUES (:medicine_name, :generic_name, :category_id, :manufacturer, :batch_number, :expiry_date, :unit_price, :mrp, :stock_quantity, :reorder_level, :rack_location, :description)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':medicine_name', $medicine_name);
            $stmt->bindParam(':generic_name', $generic_name);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':manufacturer', $manufacturer);
            $stmt->bindParam(':batch_number', $batch_number);
            $stmt->bindParam(':expiry_date', $expiry_date);
            $stmt->bindParam(':unit_price', $unit_price);
            $stmt->bindParam(':mrp', $mrp);
            $stmt->bindParam(':stock_quantity', $stock_quantity);
            $stmt->bindParam(':reorder_level', $reorder_level);
            $stmt->bindParam(':rack_location', $rack_location);
            $stmt->bindParam(':description', $description);
            
            if ($stmt->execute()) {
                $message = 'Medicine added successfully!';
                header('Location: list.php');
                exit();
            }
        } catch (Exception $e) {
            $error = 'Error adding medicine: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Add New Medicine</h1>
                <div class="header-actions">
                    <a href="list.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Medicine Details</h3>
                </div>
                <form method="POST" style="padding: 20px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Medicine Name *</label>
                            <input type="text" name="medicine_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Generic Name</label>
                            <input type="text" name="generic_name">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category *</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Manufacturer</label>
                            <input type="text" name="manufacturer">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Batch Number *</label>
                            <input type="text" name="batch_number" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="date" name="expiry_date">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Unit Price (₹)</label>
                            <input type="number" name="unit_price" step="0.01" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label>MRP (₹) *</label>
                            <input type="number" name="mrp" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Stock Quantity *</label>
                            <input type="number" name="stock_quantity" value="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Reorder Level</label>
                            <input type="number" name="reorder_level" value="10">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Rack Location</label>
                            <input type="text" name="rack_location" placeholder="e.g., A1, B2">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn btn-success">Add Medicine</button>
                        <a href="list.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>