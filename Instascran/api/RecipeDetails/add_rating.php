<?php
session_start(); 
require_once '../users/users.php'; 
require_once '../config/connect.php';


if (isset($_SESSION['token'])) {
    $users = new Users($pdo);
    $encryptedToken = $_SESSION['token']; 
    $userId = $users->decryptSessionToken($encryptedToken);

    if (!$userId) {
        header("Location: ../../login.html");
        exit();
    }
    // Check if the rating form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
        // Check if recipe_id and rating are set and not empty
        if (isset($_POST['recipe_id'], $_POST['rating']) && !empty($_POST['recipe_id']) && !empty($_POST['rating'])) {
            $recipe_id = $_POST['recipe_id'];
            $rating = $_POST['rating'];

            $existingRating = $pdo->prepare("SELECT * FROM Ratings WHERE recipe_id = ? AND user_id = ?");
            $existingRating->execute([$recipe_id, $userId]);

            // If the user has already rated the recipe, update the rating
            if ($existingRating->rowCount() > 0) {
                $update_sql = "UPDATE Ratings SET rating = ? WHERE recipe_id = ? AND user_id = ?";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute([$rating, $recipe_id, $userId]);
                echo "Rating updated successfully!";
            } else {
                // If the user has not rated the recipe, insert the rating
                $insert_sql = "INSERT INTO Ratings (recipe_id, user_id, rating) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute([$recipe_id, $userId, $rating]);
                echo "Rating submitted successfully!";
            }
        } else {
            // If recipe ID or rating is missing, prompt the user to provide them
            echo "Please provide both recipe ID and rating.";
        }
    } else {
        // If the form is not submitted, prompt the user to submit the form
        echo "Rating form not submitted.";
    }
} else {
    // If the session token is not valid or not set, prompt the user to log in
    echo "User not logged in. Please log in to submit ratings.";
}
?>
