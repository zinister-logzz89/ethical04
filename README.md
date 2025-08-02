# Secure Contact Form

A professional, secure contact form implementation using HTML, PHP, and PHPMailer.

## Features

- ✅ Secure input validation and sanitization
- ✅ Modern, responsive HTML form design
- ✅ AJAX form submission with user feedback
- ✅ PHPMailer integration for reliable email delivery
- ✅ Configurable SMTP settings
- ✅ Optional logging functionality
- ✅ Protection against common vulnerabilities (XSS, CSRF)
- ✅ Professional email templates

## Files Structure

```
├── contact.html          # Main contact form (frontend)
├── contact.php          # Form handler (backend)
├── config.example.php   # Configuration template
├── composer.json        # Dependencies
└── README.md           # This file
```

## Quick Setup

### 1. Install Dependencies

Using Composer (recommended):
```bash
composer install
```

Or download PHPMailer manually and update the require paths in `contact.php`.

### 2. Configure Email Settings

1. Copy the configuration template:
   ```bash
   cp config.example.php config.php
   ```

2. Edit `config.php` with your email settings:
   ```php
   return [
       'smtp_host' => 'mail.your-domain.com',
       'smtp_port' => 587,
       'smtp_username' => 'your-email@domain.com',
       'smtp_password' => 'your-password',
       'smtp_secure' => 'tls',
       'from_email' => 'noreply@your-domain.com',
       'from_name' => 'Your Website',
       'to_email' => 'admin@your-domain.com',
       'to_name' => 'Website Administrator'
   ];
   ```

### 3. Deploy Files

Upload all files to your web server with PHP support.

### 4. Test the Form

Open `contact.html` in your browser and test the contact form.

## Configuration Options

### SMTP Settings

| Setting | Description | Example |
|---------|-------------|---------|
| `smtp_host` | SMTP server hostname | `mail.your-domain.com` |
| `smtp_port` | SMTP port | `587` (TLS), `465` (SSL) |
| `smtp_username` | SMTP username | `your-email@domain.com` |
| `smtp_password` | SMTP password | `your-secure-password` |
| `smtp_secure` | Encryption type | `tls`, `ssl`, or `false` |

### Email Settings

| Setting | Description |
|---------|-------------|
| `from_email` | Sender email address |
| `from_name` | Sender display name |
| `to_email` | Recipient email address |
| `to_name` | Recipient display name |

### Optional Settings

| Setting | Description | Default |
|---------|-------------|---------|
| `enable_logging` | Enable form submission logging | `true` |
| `log_file` | Log file name | `contact_log.txt` |

## Common Email Provider Settings

### Gmail
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```
**Note:** Use an App Password instead of your regular password.

### Outlook/Hotmail
```php
'smtp_host' => 'smtp.live.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```

### Yahoo
```php
'smtp_host' => 'smtp.mail.yahoo.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
```

## Security Features

- **Input Validation**: All form inputs are validated and sanitized
- **Email Validation**: Proper email format validation
- **XSS Protection**: All output is properly escaped
- **Method Validation**: Only POST requests are accepted
- **Rate Limiting**: Consider implementing rate limiting for production use

## Troubleshooting

### Email Not Sending

1. **Check SMTP settings**: Verify all SMTP configuration is correct
2. **Check server logs**: Look for PHP error logs
3. **Test SMTP connection**: Use a tool like `telnet` to test SMTP connectivity
4. **Firewall**: Ensure SMTP ports (587, 465) are not blocked
5. **Authentication**: Some providers require App Passwords or OAuth

### Common Issues

- **Gmail**: Use App Passwords, enable 2FA
- **Shared Hosting**: Contact provider for SMTP settings
- **SSL/TLS Errors**: Try different `smtp_secure` settings

### Error Logs

Check your server's PHP error logs for detailed error messages:
- **Linux**: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
- **cPanel**: Error Logs section
- **Local Development**: Check PHP error settings

## Customization

### Styling
Modify the CSS in `contact.html` to match your website's design.

### Validation
Add custom validation rules in `contact.php` as needed.

### Email Template
Customize the email template in the `$emailBody` variable.

## License

This project is provided as-is for educational and legitimate business purposes.

## Security Notice

- Always use HTTPS in production
- Keep PHPMailer updated
- Implement rate limiting
- Consider adding CAPTCHA for high-traffic sites
- Regularly monitor logs for suspicious activity

## Support

For issues related to specific email providers, consult their documentation:
- [Gmail SMTP Settings](https://support.google.com/mail/answer/7126229)
- [Outlook SMTP Settings](https://support.microsoft.com/en-us/office/pop-imap-and-smtp-settings-8361e398-8af4-4e97-b147-6c6c4ac95353)