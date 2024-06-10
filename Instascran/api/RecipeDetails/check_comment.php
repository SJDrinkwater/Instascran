<?php
session_start();
require_once '../users/users.php';
require_once '../config/connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['comment_id'])) {
    $commentId = $_GET['comment_id'];

    // Decrypt the token from the session
    $users = new Users($pdo);
    $encryptedToken = $_SESSION['token']; 
    $userId = $users->decryptSessionToken($encryptedToken);
   
    if ($userId) { 
        // Check if the comment belongs to the logged-in user
        $sql = "SELECT user_id FROM Comments WHERE comment_id = :comment_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':comment_id' => $commentId]);
        $commentOwner = $stmt->fetchColumn();

        if ($commentOwner == $userId) {
            // Comment belongs to the user, allow deletion (return true)
            echo json_encode(true); 
        } else {
            // Comment doesn't belong to the user (return false)
            echo json_encode(false);
        }
    } else {
        // Invalid or expired token (return false)
        echo json_encode(false); 
    }
} else {
    // Invalid request (return false)
    echo json_encode(false);
}
