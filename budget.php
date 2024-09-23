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
    // Handle POST request for inserting/updating budget
    if (isset($input['email']) && isset($input['api_key']) && isset($input['category_id']) && isset($input['budget_amount']) && isset($input['type'])) {
        $email = $input['email'];
        $api_key = $input['api_key'];
        $category_id = $input['category_id'];
        $budget_amount = $input['budget_amount'];
        $category_type = $input['type'];  // 'income' or 'expense'
        
        // Validate category type
        if (!in_array($category_type, ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid category type']);
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
                
                // Check if a budget already exists for the user and category
                $query = "SELECT * FROM budgets WHERE user_id = ? AND category_id = ?";
                $stmt = $con->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("ii", $user_id, $category_id);
                    $stmt->execute();
                    $budget_result = $stmt->get_result();
                    
                    if ($budget_result->num_rows > 0) {
                        // Update existing budget
                        $query = "UPDATE budgets SET budget_amount = ? WHERE user_id = ? AND category_id = ?";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("dii", $budget_amount, $user_id, $category_id);
                        $stmt->execute();
                        
                        $response = ['status' => 'success', 'message' => 'Budget updated successfully'];
                    } else {
                        // Insert new budget
                        $query = "INSERT INTO budgets (user_id, category_id, budget_amount, budget_period) VALUES (?, ?, ?, 'monthly')";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("iid", $user_id, $category_id, $budget_amount);
                        $stmt->execute();
                        
                        $response = ['status' => 'success', 'message' => 'Budget inserted successfully'];
                    }
                } else {
                    // Error in preparing statement
                    $response = ['status' => 'error', 'message' => 'Query preparation failed'];
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
        // Missing parameters
        $response = ['status' => 'error', 'message' => 'Required parameters missing'];
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request for retrieving budgets
    if (isset($_GET['email']) && isset($_GET['api_key']) && isset($_GET['type'])) {
        $email = $_GET['email'];
        $api_key = $_GET['api_key'];
        $category_type = $_GET['type'];  // 'income' or 'expense'
        
        // Validate category type
        if (!in_array($category_type, ['income', 'expense'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid category type']);
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
                
                // Prepare SQL query to fetch categories based on type
                $query = "SELECT c.category_id, c.category_name
                          FROM categories c
                          WHERE c.category_type = ?";
                $stmt = $con->prepare($query);
                
                if ($stmt) {
                    $stmt->bind_param("s", $category_type);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    $categories = [];
                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $category_id = $row['category_id'];
                            $category_name = $row['category_name'];
                            
                            // Check if a budget exists for the user and category
                            $query = "SELECT budget_amount FROM budgets WHERE user_id = ? AND category_id = ?";
                            $stmt = $con->prepare($query);
                            
                            if ($stmt) {
                                $stmt->bind_param("ii", $user_id, $category_id);
                                $stmt->execute();
                                $budget_result = $stmt->get_result();
                                
                                if ($budget_result->num_rows > 0) {
                                    $budget = $budget_result->fetch_assoc();
                                    $budget_amount = $budget['budget_amount'];
                                } else {
                                    // No budget found, set amount to 0
                                    $budget_amount = 0.00;
                                }
                                
                                $categories[] = [
                                    'category_id' => $category_id,
                                    'category_name' => $category_name,
                                    'budget_amount' => $budget_amount
                                ];
                            } else {
                                // Error in preparing statement
                                $response = ['status' => 'error', 'message' => 'Query preparation failed'];
                                echo json_encode($response);
                                exit();
                            }
                        }
                        
                        // Success response with categories and budgets
                        $response = ['status' => 'success', 'categories' => $categories];
                    } else {
                        // No categories found
                        $response = ['status' => 'error', 'message' => 'No categories found'];
                    }
                } else {
                    // Error in preparing statement
                    $response = ['status' => 'error', 'message' => 'Query preparation failed'];
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
