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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_comment'])) {
    // Check if comment_id is set and not empty
    if (isset($_POST['comment_id']) && !empty($_POST['comment_id'])) {
        $comment_id = $_POST['comment_id'];

        // Check if user_id is set in the session
        if(isset($userId)) {

            $sql = "DELETE FROM Comments WHERE comment_id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$comment_id, $userId]);

            // Check if the comment was deleted successfully
            if ($stmt->rowCount() > 0) {
                echo "Comment deleted successfully!";
            } else {
                echo "Failed to delete comment. Either the comment does not exist or you do not have permission to delete it.";
            }
        } else {
            echo "User not logged in. Please log in to delete comments.";
        }
    } else {
        echo "Please provide comment ID.";
    }
}
?>
