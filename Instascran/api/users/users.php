<?php
require_once '../config/connect.php'; 
require_once '../config/not_the_encryption_key.php';
class Users
{
    private $pdo; 

    public function __construct($pdo)
    {
        $this->pdo = $pdo; 
    }

    public function login($username, $password)
    {
        $conn = $this->pdo;

        // Sanitize input
        $username = $conn->quote($username); 

        // Query to retrieve the hashed password and user_id for the given username
        $query = "SELECT user_id, password FROM Users WHERE user_name = $username";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($result) {
            $hashedPasswordFromDB = $result['password'];
            $user_id = $result['user_id'];

            // Verify the hashed password against the input password
            if (password_verify($password, $hashedPasswordFromDB)) {
                return ["success" => true, "user_id" => $user_id];
            } else {
                return ["success" => false];
            }
        } else {
            return ["success" => false];
        }
    }

    public function isUsernameTaken($username)
    {
        try {
            $conn = $this->pdo;

            $query = "SELECT COUNT(*) FROM Users WHERE user_name = :username";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->execute();

            $count = $stmt->fetchColumn();

            return $count > 0;
        } catch (PDOException $e) {
            echo "Error checking username: " . $e->getMessage();
            return false;
        }
    }

    public function createUser($username, $password, $user_icon)
    {
        try {
            $conn = $this->pdo;
            
            if ($this->isUsernameTaken($username)) {
                header("Location: ../../register.html?username_exists=true");
                exit();
            }
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO Users (user_name, password, user_icon) VALUES (:username, :password, :user_icon)";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':username', $username);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':user_icon', $user_icon);

            return $stmt->execute();
        } catch (PDOException $e) {
            echo "Error creating user: " . $e->getMessage();
            return false;
        }
    }

    public function destroySession()
    {
        session_start(); 
        unset($_SESSION['token']);
        session_destroy();
        header("Location: ../login.html");
        exit;
    }
    
    public function decryptSessionToken($token) 
    {
        // Decode the token from base64
        $decoded_token = base64_decode($token);

        // Extract IV and encrypted payload
        $iv_length = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded_token, 0, $iv_length);
        $encrypted_payload = substr($decoded_token, $iv_length);

        // Decrypt the payload using AES-256-CBC encryption
        $secret_key = SECRET_KEY;
        $decrypted_payload = openssl_decrypt($encrypted_payload, 'aes-256-cbc', $secret_key, 0, $iv);

        // Decode JSON payload
        $payload = json_decode($decrypted_payload, true);

        if ($payload && isset($payload['user_id']) && isset($payload['exp']) && $payload['exp'] >= time()) {
            return $payload['user_id'];
        } else {
            return null;
        }
    }
   public function generateSessionToken($user_id)
    {
        $payload = json_encode(array(
            'user_id' => $user_id,
            'exp' => time() + (60 * 60) // Token expires in 1 hour
        ));
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $secret_key = SECRET_KEY;
        $encrypted_payload = openssl_encrypt($payload, 'aes-256-cbc', $secret_key, 0, $iv);

        $token = base64_encode($iv . $encrypted_payload);

        return $token;
    }
 
}
?>
