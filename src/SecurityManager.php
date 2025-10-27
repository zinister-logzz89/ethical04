<?php

declare(strict_types=1);

namespace App;

class SecurityManager
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->startSession();
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function checkRateLimit(): bool
    {
        $ip = $this->getClientIp();
        $key = 'rate_limit_' . md5($ip);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 1,
                'timestamp' => time()
            ];
            return true;
        }

        $window = $this->config['rate_limit_window'] ?? 3600; // 1 hour default
        $maxRequests = $this->config['rate_limit_requests'] ?? 5;

        // Reset if window expired
        if (time() - $_SESSION[$key]['timestamp'] > $window) {
            $_SESSION[$key] = [
                'count' => 1,
                'timestamp' => time()
            ];
            return true;
        }

        // Check if limit exceeded
        if ($_SESSION[$key]['count'] >= $maxRequests) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }

    public function getClientInfo(): array
    {
        $ip = $this->getClientIp();
        $location = $this->getLocationInfo($ip);
        
        return [
            'ip' => $ip,
            'location' => $location,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    private function getLocationInfo(string $ip): string
    {
        try {
            // Use a more reliable and privacy-focused geolocation service
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'Contact Form/1.0'
                ]
            ]);
            
            $data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city", false, $context);
            
            if ($data) {
                $json = json_decode($data, true);
                if ($json && $json['status'] === 'success') {
                    return ($json['city'] ?? 'Unknown') . ', ' . ($json['country'] ?? 'Unknown');
                }
            }
        } catch (Exception $e) {
            // Log error but don't expose it
        }
        
        return 'Location unavailable';
    }
}