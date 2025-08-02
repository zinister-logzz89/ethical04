<?php

declare(strict_types=1);

/**
 * Installation Script for Modern PHP Contact Form
 * Run this script once to set up your contact form system
 */

echo "🚀 Modern PHP Contact Form - Installation Script\n";
echo "==============================================\n\n";

// Check PHP version
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    echo "❌ Error: PHP 8.0 or higher is required. Current version: " . PHP_VERSION . "\n";
    exit(1);
}
echo "✅ PHP version: " . PHP_VERSION . " (OK)\n";

// Check if composer is available
if (!file_exists('composer.json')) {
    echo "❌ Error: composer.json not found. Please run this script from the project root.\n";
    exit(1);
}
echo "✅ Composer configuration found\n";

// Install dependencies if vendor directory doesn't exist
if (!is_dir('vendor')) {
    echo "📦 Installing dependencies...\n";
    exec('composer install --no-dev --optimize-autoloader', $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Error: Failed to install dependencies. Please run 'composer install' manually.\n";
        exit(1);
    }
    echo "✅ Dependencies installed\n";
} else {
    echo "✅ Dependencies already installed\n";
}

// Create .env file if it doesn't exist
if (!file_exists('.env')) {
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ Created .env file from .env.example\n";
        echo "⚠️  Please edit .env file with your actual configuration!\n";
    } else {
        echo "❌ Error: .env.example file not found\n";
        exit(1);
    }
} else {
    echo "✅ .env file already exists\n";
}

// Create logs directory
if (!is_dir('logs')) {
    mkdir('logs', 0755, true);
    echo "✅ Created logs directory\n";
} else {
    echo "✅ Logs directory already exists\n";
}

// Set proper permissions
if (function_exists('chmod')) {
    chmod('logs', 0755);
    if (file_exists('.env')) {
        chmod('.env', 0600); // Only owner can read/write
    }
    echo "✅ Set proper file permissions\n";
}

// Generate a random CSRF secret key
$envContent = file_get_contents('.env');
if (strpos($envContent, 'your-random-secret-key-here') !== false) {
    $randomKey = bin2hex(random_bytes(32));
    $envContent = str_replace('your-random-secret-key-here', $randomKey, $envContent);
    file_put_contents('.env', $envContent);
    echo "✅ Generated random CSRF secret key\n";
}

echo "\n🎉 Installation completed successfully!\n\n";
echo "Next steps:\n";
echo "1. Edit the .env file with your actual SMTP and email configuration\n";
echo "2. Configure your web server to point to the 'public/' directory\n";
echo "3. Ensure mod_rewrite is enabled (for Apache)\n";
echo "4. Test the contact form by visiting public/index.html\n\n";

echo "Configuration required in .env:\n";
echo "- SMTP_HOST: Your SMTP server hostname\n";
echo "- SMTP_USERNAME: Your SMTP username\n";
echo "- SMTP_PASSWORD: Your SMTP password\n";
echo "- RECEIVER_EMAIL: Email address to receive form submissions\n";
echo "- SENDER_EMAIL: Email address used as sender\n\n";

echo "For security in production:\n";
echo "- Enable HTTPS\n";
echo "- Remove this install.php file\n";
echo "- Regularly update dependencies\n";
echo "- Monitor logs for suspicious activity\n\n";

echo "Enjoy your modernized, secure contact form! 🎯\n";