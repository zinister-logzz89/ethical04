<?php
/**
 * PHP SMTP Mailer Script
 * A secure and flexible email sending class using SMTP
 */

class SMTPMailer {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_secure; // 'tls' or 'ssl'
    
    public function __construct($host, $port, $username, $password, $secure = 'tls') {
        $this->smtp_host = $host;
        $this->smtp_port = $port;
        $this->smtp_username = $username;
        $this->smtp_password = $password;
        $this->smtp_secure = $secure;
    }
    
    /**
     * Send email using SMTP
     */
    public function sendEmail($to, $subject, $message, $from_name = '', $reply_to = '') {
        try {
            // Create headers
            $headers = $this->buildHeaders($from_name, $reply_to);
            
            // Use PHPMailer if available, otherwise use basic mail function
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendWithPHPMailer($to, $subject, $message, $from_name, $reply_to);
            } else {
                return $this->sendWithBasicSMTP($to, $subject, $message, $headers);
            }
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHPMailer (recommended)
     */
    private function sendWithPHPMailer($to, $subject, $message, $from_name, $reply_to) {
        require_once 'vendor/autoload.php'; // Composer autoload
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_secure;
            $mail->Port = $this->smtp_port;
            
            // Recipients
            $mail->setFrom($this->smtp_username, $from_name ?: 'Mailer');
            $mail->addAddress($to);
            
            if ($reply_to) {
                $mail->addReplyTo($reply_to);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Basic SMTP implementation (for demonstration)
     */
    private function sendWithBasicSMTP($to, $subject, $message, $headers) {
        // This is a simplified example - for production use PHPMailer
        $formatted_message = $headers . "\r\n\r\n" . $message;
        
        // Use PHP's mail function with additional parameters
        $additional_parameters = "-f " . $this->smtp_username;
        
        return mail($to, $subject, $message, $headers, $additional_parameters);
    }
    
    /**
     * Build email headers
     */
    private function buildHeaders($from_name, $reply_to) {
        $from_email = $this->smtp_username;
        $from_header = $from_name ? "$from_name <$from_email>" : $from_email;
        
        $headers = "From: $from_header\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        if ($reply_to) {
            $headers .= "Reply-To: $reply_to\r\n";
        }
        
        return $headers;
    }
}

/**
 * Example usage and configuration
 */
class EmailConfig {
    // Gmail SMTP settings
    const GMAIL_HOST = 'smtp.gmail.com';
    const GMAIL_PORT = 587;
    const GMAIL_SECURE = 'tls';
    
    // Outlook SMTP settings
    const OUTLOOK_HOST = 'smtp-mail.outlook.com';
    const OUTLOOK_PORT = 587;
    const OUTLOOK_SECURE = 'tls';
    
    // Yahoo SMTP settings
    const YAHOO_HOST = 'smtp.mail.yahoo.com';
    const YAHOO_PORT = 587;
    const YAHOO_SECURE = 'tls';
}

/**
 * Contact form handler example
 */
function handleContactForm() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Sanitize input
        $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_STRING);
        $message = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_STRING);
        
        // Validate input
        if (!$email || !$name || !$subject || !$message) {
            return ['success' => false, 'error' => 'All fields are required'];
        }
        
        // Configure SMTP (use environment variables in production)
        $smtp_host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $smtp_port = $_ENV['SMTP_PORT'] ?? 587;
        $smtp_username = $_ENV['SMTP_USERNAME'] ?? 'your-email@gmail.com';
        $smtp_password = $_ENV['SMTP_PASSWORD'] ?? 'your-app-password';
        
        // Create mailer instance
        $mailer = new SMTPMailer($smtp_host, $smtp_port, $smtp_username, $smtp_password);
        
        // Prepare email content
        $email_subject = "Contact Form: " . $subject;
        $email_body = "
            <h3>New Contact Form Submission</h3>
            <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        ";
        
        // Send email
        $recipient = $_ENV['CONTACT_EMAIL'] ?? 'admin@yoursite.com';
        $success = $mailer->sendEmail($recipient, $email_subject, $email_body, $name, $email);
        
        if ($success) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return ['success' => false, 'error' => 'Failed to send email'];
        }
    }
}

/**
 * Newsletter subscription example
 */
function sendWelcomeEmail($user_email, $user_name) {
    // Configure SMTP
    $mailer = new SMTPMailer(
        $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
        $_ENV['SMTP_PORT'] ?? 587,
        $_ENV['SMTP_USERNAME'] ?? 'your-email@gmail.com',
        $_ENV['SMTP_PASSWORD'] ?? 'your-app-password'
    );
    
    $subject = "Welcome to our newsletter!";
    $message = "
        <h2>Welcome, " . htmlspecialchars($user_name) . "!</h2>
        <p>Thank you for subscribing to our newsletter.</p>
        <p>You'll receive updates about our latest news and offers.</p>
        <p>Best regards,<br>The Team</p>
    ";
    
    return $mailer->sendEmail($user_email, $subject, $message, 'Newsletter Team');
}

// Example usage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'contact':
            $result = handleContactForm();
            echo json_encode($result);
            break;
            
        case 'newsletter':
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            
            if ($email && $name) {
                $success = sendWelcomeEmail($email, $name);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Welcome email sent!' : 'Failed to send email'
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid input']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>