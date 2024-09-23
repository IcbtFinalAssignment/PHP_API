<?php
$response = array();
include 'db/db_connect.php'; 
include 'security_headers.php';
include 'env_loader.php'; 

// Load the .env file
loadEnv(__DIR__ . '/.env');

// Get the access key from environment variables
$accessKey = getenv('ACCESS_KEY'); 

// Get the input request parameters
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

// Check for Access Key in request header
if (!isset($_SERVER['HTTP_ACCESS_KEY']) || $_SERVER['HTTP_ACCESS_KEY'] !== $accessKey) {
    $response["status"] = 403;
    $response["message"] = "Forbidden: Invalid access";
    echo json_encode($response);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request for adding transactions (income/expense)
    if (isset($input['email']) && isset($input['api_key']) && isset($input['category_id']) && isset($input['amount']) && isset($input['transaction_type']) && isset($input['account_id'])) {
        $email = $input['email'];
        $api_key = $input['api_key'];
        $category_id = $input['category_id'];
        $amount = $input['amount'];
        $category_type = $input['transaction_type'];  // 'income' or 'expense'
        $account_id = $input['account_id'];  // Account ID to update balance
        $description = isset($input['description']) ? $input['description'] : null;  // Optional description field
        
        // Validate category type
        if (!in_array($category_type, ['income', 'expense'])) {
            $response = ['status' => 'error', 'message' => 'Invalid category type'];
            echo json_encode($response);
            exit();
        }
        
        // Verify if the category_id exists and matches the transaction_type
        $query = "SELECT category_type FROM categories WHERE category_id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $category = $result->fetch_assoc();
            $db_category_type = $category['category_type'];
            
            // Check if the provided transaction_type matches the category_type
            if ($category_type !== $db_category_type) {
                $response = ['status' => 'error', 'message' => 'Transaction type does not match category type'];
                echo json_encode($response);
                exit();
            }
            
            // Fetch user_id based on email and api_key
            $query = "SELECT user_id FROM member WHERE email = ? AND api_key = ?";
            $stmt = $con->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("ss", $email, $api_key);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $user_id = $user['user_id'];
                    
                    // Fetch current account balance
                    $query = "SELECT balance FROM accounts WHERE account_id = ?";
                    $stmt = $con->prepare($query);
                    $stmt->bind_param("i", $account_id);
                    $stmt->execute();
                    $balance_result = $stmt->get_result();
                    
                    if ($balance_result->num_rows > 0) {
                        $account = $balance_result->fetch_assoc();
                        $current_balance = $account['balance'];
                        
                        // Check if balance is sufficient for expense transactions
                        if ($category_type === 'expense' && $current_balance < $amount) {
                            $response = ['status' => 'error', 'message' => 'Insufficient balance'];
                            echo json_encode($response);
                            exit();
                        }
                        
                        // Insert transaction into the transactions table with description
                        $query = "INSERT INTO transactions (user_id, category_id, amount, transaction_type, account_id, description) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("iidsss", $user_id, $category_id, $amount, $category_type, $account_id, $description);
                        $stmt->execute();

                        // Update account balance
                        if ($category_type === 'income') {
                            $query = "UPDATE accounts SET balance = balance + ? WHERE account_id = ?";
                        } else {
                            $query = "UPDATE accounts SET balance = balance - ? WHERE account_id = ?";
                        }

                        $stmt = $con->prepare($query);
                        $stmt->bind_param("di", $amount, $account_id);
                        $stmt->execute();
                        
                        $response = ['status' => 'success', 'message' => 'Transaction updated successfully'];
                    } else {
                        // Account not found
                        $response = ['status' => 'error', 'message' => 'Account not found'];
                    }
                } else {
                    // User not found
                    $response = ['status' => 'error', 'message' => 'Invalid email or API key'];
                }
            } else {
                // Error in preparing statement
                $response = ['status' => 'error', 'message' => 'Query preparation failed'];
            }
        } else {
            // Category not found
            $response = ['status' => 'error', 'message' => 'Invalid category ID'];
        }
    } else {
        // Missing parameters
        $response = ['status' => 'error', 'message' => 'Required parameters missing'];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request for retrieving accounts and categories
    if (isset($_GET['email']) && isset($_GET['api_key'])) {
        $email = $_GET['email'];
        $api_key = $_GET['api_key'];
        $category_type = isset($_GET['category_type']) ? $_GET['category_type'] : null;  // Optional category type parameter
        
        // Validate category type
        if ($category_type && !in_array($category_type, ['income', 'expense'])) {
            $response = ['status' => 'error', 'message' => 'Invalid category type'];
            echo json_encode($response);
            exit();
        }
        
        // Fetch user_id based on email and api_key
        $query = "SELECT user_id FROM member WHERE email = ? AND api_key = ?";
        $stmt = $con->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ss", $email, $api_key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_id = $user['user_id'];
                
                // Fetch user accounts with account_type
                $query = "SELECT account_id, account_name, balance, account_type FROM accounts WHERE user_id = ?";
                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $account_result = $stmt->get_result();

                $accounts = [];
                while ($row = $account_result->fetch_assoc()) {
                    $accounts[] = $row;
                }
                
                // Fetch categories with optional type filter
                $query = "SELECT category_id, category_name, category_type FROM categories";
                if ($category_type) {
                    $query .= " WHERE category_type = ?";
                }
                
                $stmt = $con->prepare($query);
                if ($category_type) {
                    $stmt->bind_param("s", $category_type);
                }
                $stmt->execute();
                $category_result = $stmt->get_result();

                $categories = [];
                while ($row = $category_result->fetch_assoc()) {
                    $categories[] = $row;
                }

                // Return accounts and categories
                $response = [
                    'status' => 'success',
                    'accounts' => $accounts,
                    'categories' => $categories
                ];
            } else {
                // User not found
                $response = ['status' => 'error', 'message' => 'Invalid email or API key'];
            }
        } else {
            // Error in preparing statement
            $response = ['status' => 'error', 'message' => 'Query preparation failed'];
        }
    } else {
        // Missing parameters
        $response = ['status' => 'error', 'message' => 'Required parameters missing'];
    }
} else {
    // Invalid request method
    $response = ['status' => 'error', 'message' => 'Invalid request method'];
}

// Return JSON response
echo json_encode($response);
?>
