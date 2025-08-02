<?php
// Contact Form Handler
require_once 'vendor/autoload.php'; // If using Composer
// OR include PHPMailer files manually:
// require_once 'PHPMailer/src/PHPMailer.php';
// require_once 'PHPMailer/src/SMTP.php';
// require_once 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set content type for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Input validation and sanitization
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get and validate form data
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : '';
$message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

// Validation
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters long';
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = 'Please provide a valid email address';
}

if (empty($subject) || strlen($subject) < 3) {
    $errors[] = 'Subject must be at least 3 characters long';
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters long';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Load configuration
if (file_exists('config.php')) {
    $config = require 'config.php';
} else {
    // Fallback configuration - PLEASE CREATE config.php FROM config.example.php
    $config = [
        'smtp_host' => 'your-smtp-server.com',
        'smtp_port' => 587,
        'smtp_username' => 'your-email@domain.com',
        'smtp_password' => 'your-password',
        'smtp_secure' => 'tls',
        'from_email' => 'your-email@domain.com',
        'from_name' => 'Contact Form',
        'to_email' => 'recipient@domain.com',
        'to_name' => 'Website Owner',
        'enable_logging' => true,
        'log_file' => 'contact_log.txt'
    ];
    
    // Show warning if config.php doesn't exist
    error_log("Warning: config.php not found. Please copy config.example.php to config.php and update with your settings.");
}

try {
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
    
    // Set encryption based on config
    if ($config['smtp_secure'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } elseif ($config['smtp_secure'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }
    
    $mail->Port = $config['smtp_port'];
    
    // Recipients
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['to_email'], $config['to_name']);
    $mail->addReplyTo($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Contact Form: ' . $subject;
    
    // Create email body
    $emailBody = "
    <html>
    <head>
        <title>Contact Form Submission</title>
    </head>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
        <p><strong>Message:</strong></p>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
        <hr>
        <p><em>Sent from your website contact form</em></p>
    </body>
    </html>
    ";
    
    $mail->Body = $emailBody;
    $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\nMessage: $message";
    
    // Send email
    $mail->send();
    
    // Log the message (if enabled)
    if (isset($config['enable_logging']) && $config['enable_logging']) {
        $logFile = isset($config['log_file']) ? $config['log_file'] : 'contact_log.txt';
        $logEntry = date('Y-m-d H:i:s') . " - Contact form submission from: $name ($email) - Subject: $subject\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
    
} catch (Exception $e) {
    error_log("Contact form error: " . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Sorry, there was an error sending your message. Please try again later.']);
}
?>