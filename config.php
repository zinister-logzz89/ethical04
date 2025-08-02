<?php

// SMTP Configuration
// Replace these values with your actual SMTP settings

// SMTP Server Settings
define('SMTP_HOST', 'smtp.gmail.com');          // Your SMTP server (e.g., smtp.gmail.com, smtp.outlook.com)
define('SMTP_PORT', 587);                       // SMTP port (587 for TLS, 465 for SSL, 25 for non-encrypted)
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your SMTP username (usually your email)
define('SMTP_PASSWORD', 'your-app-password');    // Your SMTP password (use app password for Gmail)

// Email Settings
define('LOG_EMAIL', 'logs@yourdomain.com');      // Email address where logs will be sent
define('FROM_EMAIL', 'noreply@yourdomain.com');  // From email address
define('FROM_NAME', 'Security Logger');          // From name

// Security Settings
define('ENABLE_LOGGING', true);                  // Set to false to disable logging
define('LOG_TO_FILE', true);                     // Also log to file as backup
define('MAX_ATTEMPTS_PER_IP', 10);              // Maximum attempts per IP before blocking (not implemented)

// Optional: Advanced Settings
define('USE_SSL', false);                        // Set to true if using SSL (port 465)
define('SMTP_DEBUG', 0);                         // SMTP debug level (0 = off, 1 = client, 2 = server)

?>