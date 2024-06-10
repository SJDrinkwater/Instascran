<?php
session_start();
require_once 'users.php';
require_once '../config/connect.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST["username"];
    $password = $_POST["password"];
    $user_icon = $_POST["user_icon"]; 

    // Create a new Users object
    $user= new Users($pdo);

    // Attempt to create the user
    echo "Attempting to create user...<br>";
    $accountCreateAttempt = $user->createUser($username, $password, $user_icon);

    if ($accountCreateAttempt) {
        // Redirect to login page if account creation is successful
        header("Location: ../../login.html");
        exit(); 
    } else {
        // Redirect back to register page if there was an error creating the account
        header("Location: ../../register.html");
        exit(); 
    }
}
?>
