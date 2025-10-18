<?php
// views/medicines/list.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();
checkSessionTimeout();

$database = new Database();
$db = $database->getConnection();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Get categories
$query = "SELECT * FROM categories ORDER BY category_name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build query
$query = "SELECT m.*, c.category_name 
          FROM medicines m 
          LEFT JOIN categories c ON m.category_id = c.id 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (m.medicine_name LIKE :search OR m.generic_name LIKE :search)";
}

if (!empty($category_filter)) {
    $query .= " AND m.category_id = :category";
}

$query .= " ORDER BY m.medicine_name";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}

if (!empty($category_filter)) {
    $stmt->bindParam(':category', $category_filter);
}

$stmt->execute();
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicines - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>💊 Medicines</h1>
                <div class="header-actions">
                    <?php if (hasRole('admin') || hasRole('pharmacist')): ?>
                    <a href="add.php" class="btn btn-primary">Add New Medicine</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="table-container" style="margin-bottom: 20px;">
                <div class="table-header">
                    <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                        <input type="text" name="search" placeholder="Search medicine..." value="<?php echo $search; ?>" style="flex: 1;">
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['category_name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="list.php" class="btn btn-secondary">Clear</a>
                    </form>
                </div>
            </div>
            
            <!-- Medicines Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Generic Name</th>
                            <th>Category</th>
                            <th>Batch</th>
                            <th>Expiry</th>
                            <th>MRP</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($medicines) > 0): ?>
                            <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo $medicine['id']; ?></td>
                                <td><strong><?php echo $medicine['medicine_name']; ?></strong></td>
                                <td><?php echo $medicine['generic_name']; ?></td>
                                <td><?php echo $medicine['category_name']; ?></td>
                                <td><?php echo $medicine['batch_number']; ?></td>
                                <td><?php echo formatDate($medicine['expiry_date']); ?></td>
                                <td><?php echo formatCurrency($medicine['mrp']); ?></td>
                                <td><strong><?php echo $medicine['stock_quantity']; ?></strong></td>
                                <td>
                                    <?php if ($medicine['stock_quantity'] == 0): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php elseif ($medicine['stock_quantity'] <= $medicine['reorder_level']): ?>
                                        <span class="badge badge-warning">Low Stock</span>
                                    <?php elseif (strtotime($medicine['expiry_date']) < strtotime('+3 months')): ?>
                                        <span class="badge badge-warning">Near Expiry</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view.php?id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <?php if (hasRole('admin') || hasRole('pharmacist')): ?>
                                    <a href="edit.php?id=<?php echo $medicine['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align: center;">No medicines found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>