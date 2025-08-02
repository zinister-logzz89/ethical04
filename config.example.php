<?php
/*
 * Email Configuration Example
 * 
 * Copy this file to 'config.php' and update with your actual email settings
 * Make sure to keep 'config.php' secure and never commit it to version control
 */

return [
    // SMTP Server Configuration
    'smtp_host' => 'mail.your-domain.com',        // Your SMTP server hostname
    'smtp_port' => 587,                           // SMTP port (587 for TLS, 465 for SSL, 25 for non-encrypted)
    'smtp_username' => 'your-email@domain.com',   // Your SMTP username (usually your email)
    'smtp_password' => 'your-secure-password',    // Your SMTP password
    'smtp_secure' => 'tls',                       // Security: 'tls', 'ssl', or false for none
    
    // Email Settings
    'from_email' => 'noreply@your-domain.com',    // Email address to send from
    'from_name' => 'Your Website Contact Form',   // Name to display as sender
    'to_email' => 'admin@your-domain.com',        // Email address to receive messages
    'to_name' => 'Website Administrator',         // Name of the recipient
    
    // Optional Settings
    'enable_logging' => true,                     // Enable/disable logging to file
    'log_file' => 'contact_log.txt',             // Log file name
    'enable_auto_reply' => false,                // Send auto-reply to form submitter
    'auto_reply_subject' => 'Thank you for contacting us',
    'auto_reply_message' => 'We have received your message and will get back to you soon.'
];

/*
 * Common SMTP Settings for Popular Email Providers:
 * 
 * Gmail:
 * - Host: smtp.gmail.com
 * - Port: 587
 * - Secure: tls
 * - Note: You may need to use an "App Password" instead of your regular password
 * 
 * Outlook/Hotmail:
 * - Host: smtp.live.com
 * - Port: 587
 * - Secure: tls
 * 
 * Yahoo:
 * - Host: smtp.mail.yahoo.com
 * - Port: 587 or 465
 * - Secure: tls (for 587) or ssl (for 465)
 * 
 * Custom/Business Email:
 * - Contact your hosting provider for SMTP settings
 * - Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted)
 */
?>