<?php
session_start();
require_once 'users.php';
require_once '../config/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = new Users($pdo);
        $loginResult = $user->login($username, $password);

        if ($loginResult["success"]) {
            $token = $user->generateSessionToken($loginResult["user_id"]);
            $_SESSION['token'] = $token;
            header("Location: ../../Recipes/all_recipes.html");
            exit();
        } else {
            header("Location: ../../login.html?error=1");
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500); // Internal Server Error
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    http_response_code(405); // Method Not Allowed
    header("Location: ../../login.html");
    exit();
}
?>