<?php
// views/billing/search_medicine.php
require_once '../../config/config.php';
require_once '../../config/database.php';
requireLogin();

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$term = isset($_GET['term']) ? sanitize($_GET['term']) : '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit();
}

$query = "SELECT id, medicine_name, generic_name, batch_number, mrp, stock_quantity, expiry_date 
          FROM medicines 
          WHERE (medicine_name LIKE :term OR generic_name LIKE :term) 
          AND stock_quantity > 0 
          AND expiry_date > CURDATE()
          ORDER BY medicine_name 
          LIMIT 20";

$stmt = $db->prepare($query);
$search_term = "%{$term}%";
$stmt->bindParam(':term', $search_term);
$stmt->execute();

$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($medicines);
?>