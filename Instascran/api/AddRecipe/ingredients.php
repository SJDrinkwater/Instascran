<?php
require_once '../config/connect.php';

// Ensure a GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only GET requests are allowed"]);
    exit();
}

// Handle filtering by name 
if (isset($_GET['name'])) {
    $name = "%" . $_GET['name'] . "%"; 
    $sql = "SELECT product_id, product_name, product_measurement FROM Products WHERE product_name LIKE :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name);
} else {
    // Default query for all ingredients
    $sql = "SELECT product_id, product_name, product_measurement FROM Products";
    $stmt = $pdo->query($sql);
}

$stmt->execute();
$ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode( $ingredients);
?>
