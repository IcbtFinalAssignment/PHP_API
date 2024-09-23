<?php
$response = array();
include 'db/db_connect.php';
include 'functions.php';
include 'security_headers.php';
include 'env_loader.php'; // Load environment variables

// Load the .env file
loadEnv(__DIR__ . '/.env');

// Get the access key from environment variables
$accessKey = getenv('ACCESS_KEY'); 

// Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); // Convert JSON into array

// Check for Access Key in request header
if (!isset($_SERVER['HTTP_ACCESS_KEY']) || $_SERVER['HTTP_ACCESS_KEY'] !== $accessKey) {
    $response["status"] = 403;
    $response["message"] = "Forbidden: Invalid access key";
    echo json_encode($response);
    exit();
}

// Check for Mandatory parameters
if (isset($input['email']) && isset($input['password'])) {
    $email = $input['email'];
    $password = $input['password'];
    $query = "SELECT full_name, password_hash, salt, api_key FROM member WHERE email = ?"; 

    if ($stmt = $con->prepare($query)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($fullName, $passwordHashDB, $salt, $apiKeyDB);
        if ($stmt->fetch()) {
            // Validate the password
            if (password_verify(concatPasswordWithSalt($password, $salt), $passwordHashDB)) {
                // Triple-encode the API Key in Base64
                $base64Encoded = base64_encode($apiKeyDB);
                $base64EncodedTwice = base64_encode($base64Encoded);
                $base64EncodedThrice = base64_encode($base64EncodedTwice);

                $response["status"] = 0;
                $response["message"] = "Login successful";
                $response["full_name"] = $fullName;
                $response["api_key"] = $base64EncodedThrice;
            } else {
                $response["status"] = 1;
                $response["message"] = "Invalid email and password combination"; 
            }
        } else {
            $response["status"] = 1;
            $response["message"] = "Invalid email and password combination"; 
        }

        $stmt->close();
    }
} else {
    $response["status"] = 2;
    $response["message"] = "Missing mandatory parameters";
}

// Display the JSON response
echo json_encode($response);
?>
