<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\SecurityManager;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_ENV['ALLOWED_ORIGINS'] ?? '*'));
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $config = [
        'csrf_secret_key' => $_ENV['CSRF_SECRET_KEY'] ?? 'default-secret-change-me'
    ];
    
    $security = new SecurityManager($config);
    $token = $security->generateCsrfToken();
    
    echo json_encode(['token' => $token]);
} catch (Exception $e) {
    error_log('CSRF token generation error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}