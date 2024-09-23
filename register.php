<?php
$response = array();
include 'db/db_connect.php';
include 'functions.php';
include 'security_headers.php';
include 'env_loader.php'; 

// Load the .env file
loadEnv(__DIR__ . '/.env');

// Get the access key from environment variables
$accessKey = getenv('ACCESS_KEY'); 

// Function to validate email domain
function isValidEmailDomain($email) {
    $domain = substr(strrchr($email, "@"), 1);
    return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
}

// Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// Check for Access Key in request header
if (!isset($_SERVER['HTTP_ACCESS_KEY']) || $_SERVER['HTTP_ACCESS_KEY'] !== $accessKey) {
    $response["status"] = 403;
    $response["message"] = "Forbidden: Invalid";
    echo json_encode($response);
    exit();
}

// Check for Mandatory parameters
if (isset($input['email']) && isset($input['password']) && isset($input['full_name'])) {
    $email = $input['email'];
    $password = $input['password'];
    $fullName = $input['full_name'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["status"] = 3;
        $response["message"] = "Invalid email address format";
        echo json_encode($response);
        exit();
    }

    // Validate email domain
    if (!isValidEmailDomain($email)) {
        $response["status"] = 4;
        $response["message"] = "Email domain does not have valid MX or A records";
        echo json_encode($response);
        exit();
    }

    // Check if user already exists
    if (!userExists($email)) {
        $salt = getSalt();
        $passwordHash = password_hash(concatPasswordWithSalt($password, $salt), PASSWORD_DEFAULT);
        
        // Generate a unique API Key
        $apiKey = bin2hex(random_bytes(16)); // 32-character API Key
        
        $base64Encoded = base64_encode($apiKey);
        $base64EncodedTwice = base64_encode($base64Encoded);
        $base64EncodedThrice = base64_encode($base64EncodedTwice);

        // Insert the user into the database
        $insertUserQuery = "INSERT INTO member(email, full_name, password_hash, salt, api_key) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $con->prepare($insertUserQuery)) {
            $stmt->bind_param("sssss", $email, $fullName, $passwordHash, $salt, $apiKey);
            $stmt->execute();
            $userId = $stmt->insert_id; // Get the newly inserted user_id
            $stmt->close();

            // Insert bank and cash accounts with 0 balance
            $insertAccountsQuery = "INSERT INTO accounts (user_id, account_name, balance, account_type) VALUES (?, 'Main Cash Account', 0.00, 'cash'), (?, 'Main Bank Account', 0.00, 'bank')";
            if ($stmt = $con->prepare($insertAccountsQuery)) {
                $stmt->bind_param("ii", $userId, $userId);
                $stmt->execute();
                $stmt->close();
            }

            $response["status"] = 0;
            $response["message"] = "User created and accounts initialized";
            $response["api_key"] = $base64EncodedThrice;
        }
    } else {
        $response["status"] = 1;
        $response["message"] = "User exists";
    }
} else {
    $response["status"] = 2;
    $response["message"] = "Missing mandatory parameters";
}

// Display the JSON response
echo json_encode($response);
?>
