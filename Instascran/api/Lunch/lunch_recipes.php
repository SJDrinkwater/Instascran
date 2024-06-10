<?php
require_once '../config/time_elapsed_string.php';

// Function to get recipes with comments and ratings
function getRecipesWithDetails() {
    require_once '../config/connect.php'; 

    $sql = "SELECT r.recipe_id, r.recipe_name, r.recipe_img, r.created_at, r.recipe_kcal, r.meal_type, u.user_name, u.user_icon 
            FROM Recipes r 
            JOIN Users u ON r.user_id = u.user_id 
            WHERE r.meal_type = 'lunch' 
            ORDER BY r.created_at DESC";

    $result = $pdo->query($sql);

    $recipes = array();
    if ($result->rowCount() > 0) { 
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) { 
            $recipe_id = $row['recipe_id'];
            // Fetch comments for the recipe
            $comments_sql = "SELECT c.comment_id, c.comment, c.created_at, u.user_name 
                             FROM Comments c 
                             JOIN Users u ON c.user_id = u.user_id 
                             WHERE c.recipe_id = ? 
                             ORDER BY c.created_at DESC 
                             LIMIT 3";
            $comments_stmt = $pdo->prepare($comments_sql); 
            $comments_stmt->execute([$recipe_id]); 
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
            $avg_rating_sql = "SELECT AVG(rating) AS avg_rating FROM Ratings WHERE recipe_id = ?";
            $avg_rating_stmt = $pdo->prepare($avg_rating_sql); 
            $avg_rating_stmt->execute([$recipe_id]); 
            $avg_rating_row = $avg_rating_stmt->fetch(PDO::FETCH_ASSOC);
            $avg_rating = isset($avg_rating_row['avg_rating']) ? round($avg_rating_row['avg_rating']) : 0; // Check if average rating exists

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


// Get recipes with comments and ratings and return as JSON
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $recipes_with_details = getRecipesWithDetails();
    header('Content-Type: application/json');
    echo json_encode($recipes_with_details);
} else {
    http_response_code(405); // Method Not Allowed
}
?>
