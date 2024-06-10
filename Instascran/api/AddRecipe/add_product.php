<?php
session_start(); 

require_once '../config/connect.php'; 

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $product_name = $_POST['product_name'];
    $product_measurement = $_POST['product_measurement'];

    // Validate product name and measurement
    if (empty($product_name) || empty($product_measurement)) {
        echo "Product name and measurement are required.";
        exit(); 
    }

    // Check if the product name already exists
    $check_sql = "SELECT * FROM Products WHERE product_name=?";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([$product_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        echo "Product name already exists. Please choose a different name.";
        exit();
    }

    // Restrict product measurement options
    $allowed_measurements = array("ml", "grams", "whole");
    if (!in_array($product_measurement, $allowed_measurements)) {
        echo "Invalid product measurement. Allowed options are ml, grams, or whole.";
        exit();
    }

    // Insert into Products table, excluding product_id (auto-increment)
    $insert_sql = "INSERT INTO Products (product_name, product_measurement) VALUES (?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $pdo->errorInfo()[2];
        exit();
    }

    if ($stmt->execute([$product_name, $product_measurement])) {
        header("Location: ../../Recipes/add_recipe.html");
        exit();
    } else {
        echo "Error: Unable to add product.";
        error_log("Error adding product: " . $stmt->errorInfo()[2]);
        exit();
    }
} else {
}
?>
<?php
session_start();

require_once '../config/connect.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_name = $_POST['product_name'];
    $product_measurement = $_POST['product_measurement'];

    // Validate product name and measurement
    if (empty($product_name) || empty($product_measurement)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Product name and measurement are required."]);
        header("Location: ../../Recipes/add_recipe.html");
        exit();
    }
    
    // Check if the product name already exists
    $check_sql = "SELECT * FROM Products WHERE product_name=?";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([$product_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Product name already exists. Please choose a different name."]);
        exit();
    }

    // Restrict product measurement options
    $allowed_measurements = ["ml", "grams", "whole"];
    if (!in_array($product_measurement, $allowed_measurements)) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Invalid product measurement. Allowed options are ml, grams, or whole."]);
        exit();
    }

    // Insert into Products table, excluding product_id (auto-increment)
    $insert_sql = "INSERT INTO Products (product_name, product_measurement) VALUES (?, ?)";
    $stmt = $pdo->prepare($insert_sql);
    if (!$stmt) {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Error preparing statement."]);
        exit();
    }

    if ($stmt->execute([$product_name, $product_measurement])) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Product added successfully."]);
        exit();
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["message" => "Error: Unable to add product."]);
        error_log("Error adding product: " . $stmt->errorInfo()[2]);
        exit();
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Invalid request method."]);
    exit();
}

