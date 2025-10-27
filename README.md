# Modern PHP Contact Form

A secure, modern PHP contact form with best practices for security, maintainability, and user experience.

## Features

- **Modern PHP 8.0+** with strict typing and PSR-4 autoloading
- **Security First**: CSRF protection, rate limiting, input validation
- **Dependency Management**: Composer for package management
- **Environment Configuration**: Secure configuration with .env files
- **Responsive Design**: Modern HTML5/CSS3 interface
- **AJAX Form Submission**: Seamless user experience
- **Error Logging**: Comprehensive logging system
- **Email Integration**: PHPMailer with SMTP support

## Security Improvements

✅ **CSRF Protection**: Prevents cross-site request forgery attacks  
✅ **Rate Limiting**: Prevents spam and brute force attacks  
✅ **Input Validation**: Proper sanitization of all user inputs  
✅ **Environment Variables**: Sensitive data stored securely  
✅ **Error Handling**: Graceful error handling without exposing internals  
✅ **HTTP Security Headers**: Protection against common web vulnerabilities  
✅ **File Access Control**: Restricted access to sensitive files  

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd modernized-contact-form
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your actual configuration
   ```

4. **Set up web server**
   - Point document root to `public/` directory
   - Ensure mod_rewrite is enabled for Apache
   - Set appropriate file permissions

## Configuration

Edit the `.env` file with your settings:

### SMTP Configuration
```env
SMTP_HOST=your-smtp-server.com
SMTP_PORT=587
SMTP_USERNAME=your-email@domain.com
SMTP_PASSWORD=your-password
SMTP_ENCRYPTION=tls
```

### Email Settings
```env
RECEIVER_EMAIL=admin@yourdomain.com
SENDER_EMAIL=noreply@yourdomain.com
SENDER_NAME="Contact Form"
```

### Security Settings
```env
CSRF_SECRET_KEY=your-random-secret-key-here
RATE_LIMIT_REQUESTS=5
RATE_LIMIT_WINDOW=3600
```

## Usage

1. Navigate to `public/index.html` in your web browser
2. Fill out the contact form
3. Submit securely via AJAX
4. Check your configured email for the message

## File Structure

```
├── public/                 # Web-accessible files
│   ├── index.html         # Contact form interface
│   └── api/               # API endpoints
│       ├── contact.php    # Form submission handler
│       └── csrf-token.php # CSRF token provider
├── src/                   # PHP classes
│   ├── ContactFormHandler.php
│   ├── SecurityManager.php
│   └── Logger.php
├── logs/                  # Application logs
├── vendor/                # Composer dependencies
├── .env.example          # Environment configuration template
├── .htaccess             # Apache configuration
├── composer.json         # Dependency management
└── README.md            # This file
```

## API Endpoints

### POST /api/contact.php
Submit contact form data

**Parameters:**
- `email` (required): User's email address
- `password` (required): User's password
- `message` (optional): Additional message
- `csrf_token` (required): CSRF protection token

**Response:**
```json
{
  "success": true|false,
  "message": "Status message"
}
```

### GET /api/csrf-token.php
Get CSRF token for form submission

**Response:**
```json
{
  "token": "csrf_token_string"
}
```

## Development

### Requirements
- PHP 8.0 or higher
- Composer
- Web server (Apache/Nginx)
- SMTP server access

### Testing
```bash
composer test
```

### Code Style
This project follows PSR-12 coding standards.

## Security Considerations

- **Never commit `.env` files** to version control
- **Use strong passwords** for SMTP authentication
- **Enable HTTPS** in production
- **Keep dependencies updated** regularly
- **Monitor logs** for suspicious activity
- **Set appropriate file permissions** (644 for files, 755 for directories)

## License

This project is for educational purposes. Use responsibly and ensure compliance with applicable laws and regulations.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## Support

For issues and questions, please open an issue on the repository.