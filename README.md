# PHP SMTP Mailer

A secure and flexible PHP email sending script using SMTP functionality. This script provides a clean interface for sending emails through various SMTP providers like Gmail, Outlook, Yahoo, or custom SMTP servers.

## Features

- ✅ Support for multiple SMTP providers (Gmail, Outlook, Yahoo, custom)
- ✅ Secure authentication with environment variables
- ✅ HTML email support
- ✅ Input validation and sanitization
- ✅ Error handling and logging
- ✅ PHPMailer integration (optional)
- ✅ Contact form and newsletter examples
- ✅ Modern responsive HTML interface

## Files Included

- `smtp_mailer.php` - Main SMTP mailer class and handler functions
- `contact_form.html` - Example HTML form with AJAX functionality
- `config.env.example` - Environment configuration template
- `README.md` - This documentation file

## Quick Start

### 1. Setup Configuration

Copy the example configuration file:
```bash
cp config.env.example .env
```

Edit `.env` with your SMTP credentials:
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
CONTACT_EMAIL=admin@yoursite.com
```

### 2. Gmail Setup (Most Common)

1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account settings
   - Security → 2-Step Verification → App passwords
   - Generate a password for "Mail"
   - Use this password in your `.env` file

### 3. Basic Usage

```php
<?php
require_once 'smtp_mailer.php';

// Create mailer instance
$mailer = new SMTPMailer(
    'smtp.gmail.com',    // SMTP host
    587,                 // SMTP port
    'your@email.com',    // SMTP username
    'your-app-password'  // SMTP password
);

// Send email
$success = $mailer->sendEmail(
    'recipient@example.com',        // To
    'Test Subject',                 // Subject
    '<h1>Hello World!</h1>',        // Message (HTML)
    'Your Name',                    // From name (optional)
    'reply@example.com'             // Reply-to (optional)
);

if ($success) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
?>
```

## SMTP Provider Settings

### Gmail
```php
$mailer = new SMTPMailer('smtp.gmail.com', 587, $username, $password);
```

### Outlook/Hotmail
```php
$mailer = new SMTPMailer('smtp-mail.outlook.com', 587, $username, $password);
```

### Yahoo
```php
$mailer = new SMTPMailer('smtp.mail.yahoo.com', 587, $username, $password);
```

### Custom SMTP
```php
$mailer = new SMTPMailer('mail.yourdomain.com', 587, $username, $password);
```

## Environment Variables

For production use, set environment variables instead of hardcoding credentials:

```php
// Load from environment
$mailer = new SMTPMailer(
    $_ENV['SMTP_HOST'],
    $_ENV['SMTP_PORT'],
    $_ENV['SMTP_USERNAME'],
    $_ENV['SMTP_PASSWORD']
);
```

## Contact Form Integration

The included `contact_form.html` demonstrates:
- Form validation
- AJAX submission
- Success/error handling
- Newsletter subscription

### Form Handler

```php
// Handle contact form submission
if ($_POST['action'] === 'contact') {
    $result = handleContactForm();
    echo json_encode($result);
}
```

## Advanced Features

### PHPMailer Integration

For enhanced functionality, install PHPMailer via Composer:

```bash
composer require phpmailer/phpmailer
```

The script automatically detects PHPMailer and uses it when available.

### Error Logging

Errors are automatically logged to PHP error log:
```php
error_log("Email sending failed: " . $e->getMessage());
```

### Input Sanitization

All input is automatically sanitized:
```php
$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
```

## Security Best Practices

1. **Use Environment Variables**: Never hardcode SMTP credentials
2. **Validate Input**: Always validate and sanitize user input
3. **Use App Passwords**: For Gmail, use app-specific passwords
4. **Enable HTTPS**: Always use HTTPS for forms handling sensitive data
5. **Rate Limiting**: Implement rate limiting to prevent spam
6. **Error Handling**: Don't expose sensitive error information to users

## Troubleshooting

### Common Issues

**Authentication Failed**
- Verify SMTP credentials
- For Gmail, ensure 2FA is enabled and use App Password
- Check if "Less secure app access" is enabled (not recommended)

**Connection Timeout**
- Verify SMTP host and port
- Check firewall settings
- Ensure TLS/SSL is properly configured

**Mail Not Received**
- Check spam/junk folders
- Verify recipient email address
- Check email server logs

### Debug Mode

Enable PHP error reporting for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Requirements

- PHP 7.0 or higher
- SMTP server access
- Optional: PHPMailer (via Composer)

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues and questions:
1. Check the troubleshooting section
2. Verify your SMTP settings
3. Check PHP error logs
4. Test with a simple email first

## Example Use Cases

- Contact forms
- Newsletter subscriptions
- User registration emails
- Password reset emails
- Order confirmations
- System notifications