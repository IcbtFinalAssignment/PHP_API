<?php
$response = array();
include 'db/db_connect.php'; 
include 'security_headers.php';
include 'env_loader.php'; 

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

// Handle GET request for retrieving transaction records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['email']) && isset($_GET['api_key'])) {
        $email = $_GET['email'];
        $api_key = $_GET['api_key'];
        $transaction_type = isset($_GET['transaction_type']) ? $_GET['transaction_type'] : null; // Optional

        // Validate transaction_type if provided
        if ($transaction_type && !in_array($transaction_type, ['income', 'expense'])) {
            $response = ['status' => 'error', 'message' => 'Invalid transaction type'];
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

                // Initialize base query for transactions
                $query = "SELECT t.transaction_id, t.category_id, t.amount, t.transaction_type, t.account_id, t.description, t.transaction_date, c.category_name
                          FROM transactions t
                          JOIN categories c ON t.category_id = c.category_id
                          WHERE t.user_id = ?";

                $params = [$user_id];
                $types = "i";  // "i" for integer

                // Check for transaction_type
                if ($transaction_type) {
                    $query .= " AND t.transaction_type = ?";
                    $params[] = $transaction_type;
                    $types .= "s";
                }

                // Check for month parameter
                if (isset($_GET['month'])) {
                    $month = (int)$_GET['month'];  // Month as a number (1 for January, 2 for February, etc.)
                    
                    if ($month < 1 || $month > 12) {
                        $response = ['status' => 'error', 'message' => 'Invalid month number'];
                        echo json_encode($response);
                        exit();
                    }

                    // Get the month name
                    $monthName = date("F", mktime(0, 0, 0, $month, 10));
                    $query .= " AND MONTH(t.transaction_date) = ? AND YEAR(t.transaction_date) = YEAR(CURDATE())";
                    $params[] = $month;
                    $types .= "i";

                } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = $_GET['start_date'];
                    $end_date = $_GET['end_date'];

                    // Validate date format
                    if (!DateTime::createFromFormat('Y-m-d', $start_date) || !DateTime::createFromFormat('Y-m-d', $end_date)) {
                        $response = ['status' => 'error', 'message' => 'Invalid date format'];
                        echo json_encode($response);
                        exit();
                    }

                    // Check if end_date is a future date
                    $current_date = date('Y-m-d');
                    if ($end_date > $current_date) {
                        $response = ['status' => 'error', 'message' => 'End date cannot be in the future'];
                        echo json_encode($response);
                        exit();
                    }

                    // Add date range to the query
                    $query .= " AND t.transaction_date BETWEEN ? AND ?";
                    $params[] = $start_date;
                    $params[] = $end_date;
                    $types .= "ss";  // "s" for string

                } else {
                    // If neither month nor date range is provided, show all transactions
                    $query .= " AND YEAR(t.transaction_date) = YEAR(CURDATE())";
                }

                // Add ordering to the query
                $query .= " ORDER BY t.transaction_date DESC";

                // Prepare and execute the query
                $stmt = $con->prepare($query);

                if ($stmt) {
                    // Bind parameters
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $transaction_result = $stmt->get_result();

                    $transactions = [];
                    while ($row = $transaction_result->fetch_assoc()) {
                        $transactions[] = $row;
                    }

                    // Fetch account balances
                    $balance_query = "SELECT account_id, balance, account_type, account_name FROM accounts WHERE user_id = ?";
                    $balance_stmt = $con->prepare($balance_query);
                    if ($balance_stmt) {
                        $balance_stmt->bind_param("i", $user_id);
                        $balance_stmt->execute();
                        $balance_result = $balance_stmt->get_result();

                        $balances = [];
                        while ($balance_row = $balance_result->fetch_assoc()) {
                            $balances[] = $balance_row;
                        }
                        $balance_stmt->close();
                    }

                    if (count($transactions) > 0) {
                        // Return transactions and balances data
                        $response = [
                            'status' => 'success',
                            'transactions' => $transactions,
                            'balances' => $balances
                        ];
                    } else {
                        // No transactions found
                        if (isset($_GET['month'])) {
                            $response = ['status' => 'error', 'message' => 'No transactions found for ' . $monthName];
                        } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                            $response = ['status' => 'error', 'message' => 'No transactions found for the provided date range'];
                        } else {
                            $response = ['status' => 'error', 'message' => 'No transactions found for the current year'];
                        }
                    }
                } else {
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
