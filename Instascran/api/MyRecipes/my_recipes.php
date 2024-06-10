<?php
require_once '../users/users.php'; 
require_once '../config/connect.php'; 
require_once '../config/time_elapsed_string.php';
session_start();
function getRecipesWithDetails($pdo, $userId) {
    $sql = "SELECT r.recipe_id, r.recipe_name, r.recipe_img, r.created_at, r.recipe_kcal, r.meal_type, u.user_name, u.user_icon 
            FROM Recipes r 
            JOIN Users u ON r.user_id = u.user_id 
            WHERE r.user_id = :user_id
            ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
  
    $recipes = array();
    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $recipe_id = $row['recipe_id'];
            $comments_sql = "SELECT c.comment_id, c.comment, c.created_at, u.user_name 
                             FROM Comments c 
                             JOIN Users u ON c.user_id = u.user_id 
                             WHERE c.recipe_id = :recipe_id 
                             ORDER BY c.created_at DESC 
                             LIMIT 3";
            $comments_stmt = $pdo->prepare($comments_sql);
            $comments_stmt->execute(['recipe_id' => $recipe_id]);
            $comments = array();
            if ($comments_stmt->rowCount() > 0) {
                while ($comment_row = $comments_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $comment = array(
                        "comment_id" => $comment_row['comment_id'],
                        "comment" => $comment_row['comment'],
                        "created_at" => time_elapsed_string($comment_row['created_at']),
                        "user_name" => $comment_row['user_name']
                    );
                    array_push($comments, $comment);
                }
            }

            // Fetch average rating for the recipe
            $avg_rating_sql = "SELECT AVG(rating) AS avg_rating FROM Ratings WHERE recipe_id = :recipe_id";
            $avg_rating_stmt = $pdo->prepare($avg_rating_sql);
            $avg_rating_stmt->execute(['recipe_id' => $recipe_id]);
            $avg_rating = 0; // Default value if no rating is found
            if ($avg_rating_row = $avg_rating_stmt->fetch(PDO::FETCH_ASSOC)) {
                $avg_rating = round($avg_rating_row['avg_rating']); 
            }

            // Construct recipe array with comments and ratings
            $recipe = array(
                "recipe_id" => $row['recipe_id'],
                "recipe_name" => $row['recipe_name'],
                "recipe_img" => $row['recipe_img'],
                "created_at" => time_elapsed_string($row['created_at']),
                "recipe_kcal" => $row['recipe_kcal'],
                "meal_type" => $row['meal_type'],
                "user_name" => $row['user_name'],
                "user_icon" => $row['user_icon'],
                "comments" => $comments,
                "average_rating" => $avg_rating
            );
            array_push($recipes, $recipe);
        }
    }
    return $recipes;
}

// Handle the API routing
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    if (isset($_SESSION['token'])) {

        // Decrypt session token to get user ID
        $users = new Users($pdo);
        $encryptedToken = $_SESSION['token']; 
        $userId = $users->decryptSessionToken($encryptedToken);

        if (!$userId) {
            header("Location: ../../login.html");
            exit();
        }
        // Check if user_id is valid
        if ($userId && is_int($userId)) {  
            // Use the user ID to fetch recipes with details
            $recipes_with_details = getRecipesWithDetails($pdo, $userId);

            $response = [
                'status' => 'success',
                'user_id' => $userId,
                'recipes' => $recipes_with_details
            ];
            echo json_encode($response);
        } else {
            // Return an error response if user_id is invalid
            $response = [
                'status' => 'error',
                'message' => 'Invalid user session.'
            ];
            echo json_encode($response);
        }
    } else {
        // Return an error response if session token is not set
        $response = [
            'status' => 'error',
            'message' => 'User not authenticated.'
        ];
        echo json_encode($response);
    }
} elseif ($method === 'POST') {
    // Decode JSON request body
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['recipe_id'])) {
        // Sanitize and validate the recipe_id
        $recipe_id = intval($data['recipe_id']);
        if ($recipe_id <= 0) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid recipe ID"]);
            exit();
        }

        try {
            // Start a transaction
            $pdo->beginTransaction();

            // Perform the deletion query for Recipes table
            $sqlRecipes = "DELETE FROM Recipes WHERE recipe_id = :recipe_id";
            $stmt = $pdo->prepare($sqlRecipes);
            $stmt->execute(['recipe_id' => $recipe_id]);

            // Perform the deletion query for RecipeProducts table
            $sqlRecipeProducts = "DELETE FROM RecipeProducts WHERE recipe_id = :recipe_id";
            $stmt = $pdo->prepare($sqlRecipeProducts);
            $stmt->execute(['recipe_id' => $recipe_id]);

            // Commit the transaction
            $pdo->commit();

            echo json_encode(["message" => "Recipe and associated records deleted successfully"]);
        } catch (PDOException $e) {
            // Rollback the transaction if an error occurs
            $pdo->rollback();
            http_response_code(500); // Internal server error
            echo json_encode(["error" => "Error deleting recipe and associated records: " . $e->getMessage()]);
        }
    } else {
        // recipe_id not provided in the request body
        http_response_code(400); // Bad request
        echo json_encode(["error" => "Recipe ID not provided"]);
    }
} else {
    // Invalid method
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method not allowed"]);
}

?>
