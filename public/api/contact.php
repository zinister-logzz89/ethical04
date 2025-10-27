<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\ContactFormHandler;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// CORS headers for modern web applications
header('Access-Control-Allow-Origin: ' . ($_ENV['ALLOWED_ORIGINS'] ?? '*'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration from environment variables
$config = [
    'smtp_host' => $_ENV['SMTP_HOST'],
    'smtp_port' => $_ENV['SMTP_PORT'],
    'smtp_username' => $_ENV['SMTP_USERNAME'],
    'smtp_password' => $_ENV['SMTP_PASSWORD'],
    'smtp_encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    'receiver_email' => $_ENV['RECEIVER_EMAIL'],
    'sender_email' => $_ENV['SENDER_EMAIL'],
    'sender_name' => $_ENV['SENDER_NAME'] ?? 'Contact Form',
    'rate_limit_requests' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 5),
    'rate_limit_window' => (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 3600),
    'csrf_secret_key' => $_ENV['CSRF_SECRET_KEY']
];

// Validate required configuration
$requiredConfig = ['smtp_host', 'smtp_username', 'smtp_password', 'receiver_email', 'sender_email'];
foreach ($requiredConfig as $key) {
    if (empty($config[$key])) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Server configuration error']);
        exit;
    }
}

try {
    $handler = new ContactFormHandler($config);
    $result = $handler->handleRequest();
    echo json_encode($result);
} catch (Exception $e) {
    error_log('Contact form error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}