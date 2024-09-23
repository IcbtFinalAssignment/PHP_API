<?php
$response = array();
include 'db/db_connect.php'; 
include 'security_headers.php';
include 'env_loader.php'; 
include 'functions.php';
require 'mailer/src/PHPMailer.php';
require 'mailer/src/SMTP.php';
require 'mailer/src/Exception.php';

// Set the default timezone to Colombo
date_default_timezone_set('Asia/Colombo');

// Load the .env file
loadEnv(__DIR__ . '/.env');

// Get the access key from environment variables
$accessKey = getenv('ACCESS_KEY'); 

// Check for Access Key in request header
if (!isset($_SERVER['HTTP_ACCESS_KEY']) || $_SERVER['HTTP_ACCESS_KEY'] !== $accessKey) {
    $response["status"] = 403;
    $response["message"] = "Forbidden: Invalid access";
    echo json_encode($response);
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Read the POST input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE); 

// Ensure that the 'method' is specified in the POST request
if (!isset($input['method'])) {
    $response["status"] = 400;
    $response["message"] = "Bad Request: Missing method parameter";
    echo json_encode($response);
    exit();
}

$method = $input['method'];

if ($method === 'forgot_password') {
    if (isset($input['email'])) {
        $email = $input['email'];

        // Check if the user exists
        $query = "SELECT * FROM member WHERE email=?";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User exists, generate OTP and send it via email
                $otp = mt_rand(100000, 999999); // Generate 6-digit OTP
                $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); 

                $updateQuery = "UPDATE member SET otp=?, otp_expiry=? WHERE email=?";
                if ($stmt2 = $con->prepare($updateQuery)) {
                    $stmt2->bind_param("sss", $otp, $otpExpiry, $email);
                    $stmt2->execute();
                    $stmt2->close();

                    // Send OTP via email
                    $mail = new PHPMailer\PHPMailer\PHPMailer();
                    $mail->isSMTP();
                    $mail->Host = 'smtp-relay.brevo.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'sasitechsolutions@gmail.com';
                    $mail->Password = 'P56BSEMnJkbspH9A'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('info@bodima.top', 'Fintrack App');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = "OTP for password reset";
                    $mail->Body = "Your OTP is: " . $otp;

                    if ($mail->send()) {
                        $response["status"] = 0;
                        $response["message"] = "OTP sent to email.";
                    } else {
                        $response["status"] = 500;
                        $response["message"] = "OTP email could not be sent.";
                    }
                }
            } else {
                $response["status"] = 404;
                $response["message"] = "User not found.";
            }
        }
    } else {
        $response["status"] = 400;
        $response["message"] = "Missing email parameter.";
    }
} elseif ($method === 'otp_validate') {
    if (isset($input['email']) && isset($input['otp'])) {
        $email = $input['email'];
        $otp = $input['otp'];

        // Check OTP validity
        $query = "SELECT otp, otp_expiry, api_key FROM member WHERE email=?";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if ($row['otp'] === $otp && strtotime($row['otp_expiry']) > time()) {
                    // OTP is valid, return API key (Base64 encoded 3 times)
                    $apiKey = base64_encode(base64_encode(base64_encode($row['api_key'])));
                    $response["status"] = 0;
                    $response["message"] = "OTP validated.";
                    $response["api_key"] = $apiKey;
                } else {
                    $response["status"] = 400;
                    $response["message"] = "Invalid or expired OTP.";
                }
            } else {
                $response["status"] = 404;
                $response["message"] = "User not found.";
            }
        }
    } else {
        $response["status"] = 400;
        $response["message"] = "Missing email or otp parameter.";
    }
} elseif ($method === 'change_password') {
    if (isset($input['email']) && isset($input['api_key']) && isset($input['password'])) {
        $email = $input['email'];
        $apiKey = $input['api_key']; // Incoming encoded API key
        $password = $input['password'];

        // Validate API key and email
        $query = "SELECT api_key, salt FROM member WHERE email=?";
        if ($stmt = $con->prepare($query)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                // Compare the incoming API key to the original API key (not encoded)
                if ($apiKey === $row['api_key']) {
                    // API key is valid, update password
                    $salt = $row['salt'];
                    $newPasswordHash = password_hash(concatPasswordWithSalt($password, $salt), PASSWORD_DEFAULT);

                    $updateQuery = "UPDATE member SET password_hash=? WHERE email=?";
                    if ($stmt2 = $con->prepare($updateQuery)) {
                        $stmt2->bind_param("ss", $newPasswordHash, $email);
                        $stmt2->execute();
                        $stmt2->close();

                        $response["status"] = 0;
                        $response["message"] = "Password changed successfully.";
                    }
                } else {
                    $response["status"] = 403;
                    $response["message"] = "Invalid API key.";
                }
            } else {
                $response["status"] = 404;
                $response["message"] = "User not found.";
            }
        }
    } else {
        $response["status"] = 400;
        $response["message"] = "Missing email, api_key, or password parameter.";
    }
} else {
    $response["status"] = 400;
    $response["message"] = "Invalid method.";
}

echo json_encode($response);
?>
