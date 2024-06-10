<?php
require_once '../users/users.php'; 
require_once '../config/connect.php'; 
require_once '../config/time_elapsed_string.php';
session_start(); 


// Function to get recipe details
function getRecipeDetails($recipe_id) {
    global $pdo;

    $sql_recipe = "SELECT r.recipe_id, r.recipe_name, r.recipe_img, r.instructions, r.created_at, r.user_id, 
                          u.user_name, u.user_icon, AVG(ra.rating) AS avg_rating
                   FROM Recipes r
                   INNER JOIN Users u ON r.user_id = u.user_id
                   LEFT JOIN Ratings ra ON r.recipe_id = ra.recipe_id
                   WHERE r.recipe_id = ?";
                   
    $result_recipe = $pdo->prepare($sql_recipe);
    $result_recipe->execute([$recipe_id]);

    if ($result_recipe->rowCount() > 0) {
        $recipeDetails = $result_recipe->fetch();

        $sql_ingredients = "SELECT p.product_name, p.product_measurement, rp.n_ingredients
                            FROM RecipeProducts rp
                            INNER JOIN Products p ON rp.product_id = p.product_id
                            WHERE rp.recipe_id = ?";
        $result_ingredients = $pdo->prepare($sql_ingredients);
        $result_ingredients->execute([$recipe_id]);
        $ingredients_result = $result_ingredients->fetchAll();

        $recipeDetails['ingredients'] = $ingredients_result;

        // Fetch comments for the recipe
        $comments_sql = "SELECT c.comment_id, c.comment, c.created_at, u.user_name 
                         FROM Comments c 
                         JOIN Users u ON c.user_id = u.user_id 
                         WHERE c.recipe_id = ? 
                         ORDER BY c.created_at DESC 
                         LIMIT 5";
        $comments_stmt = $pdo->prepare($comments_sql); 
        $comments_stmt->execute([$recipe_id]); 
        $comments = array();
        if ($comments_stmt->rowCount() > 0) { // Check if there are rows returned
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

        $recipeDetails['comments'] = $comments;

        // Retrieve and store average rating
        $avg_rating_sql = "SELECT AVG(rating) AS avg_rating FROM Ratings WHERE recipe_id = ?";
        $avg_rating_stmt = $pdo->prepare($avg_rating_sql); 
        $avg_rating_stmt->execute([$recipe_id]); 
        $avg_rating_row = $avg_rating_stmt->fetch(PDO::FETCH_ASSOC);
        $avg_rating = isset($avg_rating_row['avg_rating']) ? round($avg_rating_row['avg_rating']) : 0; // Check if average rating exists

        $recipe = array(
            "recipe_id" => $recipe_id,
            "recipe_name" => $recipeDetails['recipe_name'],
            "recipe_img" => $recipeDetails['recipe_img'],
            "created_at" => time_elapsed_string($recipeDetails['created_at']),
            "instructions" => $recipeDetails['instructions'],
            "user_id" => $recipeDetails['user_id'],
            "user_name" => $recipeDetails['user_name'],
            "user_icon" => $recipeDetails['user_icon'],
            "ingredients" => $ingredients_result,
            "comments" => $comments,
            "average_rating" => $avg_rating
        );

        header('Content-Type: application/json');
        echo json_encode($recipe);
    } else {
        echo json_encode("No recipe found with the given ID.");
    }
}

// Handle retrieving the recipe ID from the URL parameter
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['recipe_id'])) {
        $recipe_id = $_GET['recipe_id'];
        getRecipeDetails($recipe_id);
    } else {
        echo json_encode("No recipe ID provided.");
    }
}

// Handle POST requests (for example, adding comments)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a request to add a comment
    if (isset($_POST['submit_comment'])) {
        // Check if required fields are set and not empty
        if (isset($_POST['recipe_id'], $_POST['comment']) && !empty($_POST['recipe_id']) && !empty($_POST['comment'])) {
            $recipe_id = $_POST['recipe_id'];
            $comment = $_POST['comment'];

            // Check if user is logged in
            $users = new Users($pdo);
            $encryptedToken = $_SESSION['token']; 
            $userId = $users->decryptSessionToken($encryptedToken);
   
            if (!$userId) {
                header("Location: ../../login.html");
                exit();
            }
            if ($userId) {
                // Prepare and execute the SQL statement to insert the comment
                $sql = "INSERT INTO Comments (recipe_id, user_id, comment) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$recipe_id, $userId, $comment]);

                // Check if the comment was inserted successfully
                if ($stmt->rowCount() > 0) {
                    echo json_encode("Comment submitted successfully!");
                } else {
                    echo json_encode("Failed to submit comment.");
                }
            } else {
                echo json_encode("User not logged in. Please log in to submit comments.");
            }
        } else {
            echo json_encode("Please provide recipe ID and comment.");
        }
    }
}
?>
