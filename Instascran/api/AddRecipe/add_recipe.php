<?php
session_start();

require_once '../users/users.php';
require_once '../config/connect.php'; 

// Decrypt the token from the session
$users = new Users($pdo);
$encryptedToken = $_SESSION['token']; 
$userId = $users->decryptSessionToken($encryptedToken);
if (!$userId) {
    header("Location: ../../login.html");
    exit();
}
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form inputs
    $required_fields = ['recipe_name', 'recipe_kcal', 'meal_type', 'instructions', 'ingredients', 'quantities'];
    $missing_inputs = array_filter($required_fields, function($field) {
        return empty($_POST[$field]);
    });

    if (!empty($missing_inputs)) {
        echo "Failed to add recipe. Please ensure all required fields are filled: " . implode(', ', $missing_inputs);
        exit(); 
    }

    // Retrieve form inputs
    $recipe_name = $_POST['recipe_name'];
    $recipe_kcal = $_POST['recipe_kcal'];
    $meal_type = $_POST['meal_type'];
    $instructions = $_POST['instructions'];
    $ingredients = $_POST['ingredients'];
    $quantities = $_POST['quantities'];
    $recipe_img = isset($_POST['recipe_img']) ? $_POST['recipe_img'] : 'assets/images/no_img.jpg';

    // Insert into Recipes table
    $sql = "INSERT INTO Recipes (recipe_name, recipe_kcal, recipe_img, meal_type, instructions, created_at, user_id) 
            VALUES (?, ?, ?, ?, ?, NOW(), ?)";
    
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $pdo->errorInfo()[2];
        exit();
    }

    $stmt->execute([$recipe_name, $recipe_kcal, $recipe_img, $meal_type, $instructions, $userId]);
    if ($stmt->rowCount() === 0) {
        echo "Error adding recipe.";
        exit();
    }

    // Get the recipe_id of the newly inserted recipe
    $recipe_id = $pdo->lastInsertId();

    // Insert into RecipeProducts table
    $sql = "INSERT INTO RecipeProducts (recipe_id, product_id, n_ingredients) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        echo "Error preparing statement: " . $pdo->errorInfo()[2];
        exit();
    }

    foreach ($ingredients as $key => $ingredient) {
        $ingredient_id = intval($ingredient); 
        $quantity = intval($quantities[$key]);
        $stmt->execute([$recipe_id, $ingredient_id, $quantity]);
        if ($stmt->rowCount() === 0) {
            echo "Error adding ingredient for recipe.";
            exit();
        }
    }

    echo "Recipe added successfully!";
    header("Location: ../../Recipes/my_recipes.html");
}
?>
