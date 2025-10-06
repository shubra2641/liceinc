<?php

namespace App\Helpers;

use Illuminate\Http\Request;

/**
 * Server Helper Class
 * Provides secure access to server information without direct $_SERVER usage
 */
class ServerHelper
{
    /**
     * Get request time float safely
     * @return float
     */
    public static function getRequestTimeFloat(): float
    {
        // Use Laravel's request time if available
        if (defined('LARAVEL_START')) {
            return LARAVEL_START;
        }
        
        // Fallback to microtime for timing calculations
        return microtime(true);
    }

    /**
     * Get server software safely
     * @return string
     */
    public static function getServerSoftware(): string
    {
        $request = request();
        
        // Try to get from request headers first
        $serverSoftware = $request->header('Server', 'Unknown');
        
        // If not available, use a safe default
        if ($serverSoftware === 'Unknown') {
            $serverSoftware = 'Web Server';
        }
        
        return $serverSoftware;
    }

    /**
     * Get execution time safely
     * @return float
     */
    public static function getExecutionTime(): float
    {
        $startTime = self::getRequestTimeFloat();
        return microtime(true) - $startTime;
    }

    /**
     * Check if server is Apache
     * @return bool
     */
    public static function isApache(): bool
    {
        $software = self::getServerSoftware();
        return stripos($software, 'apache') !== false;
    }

    /**
     * Check if server is Nginx
     * @return bool
     */
    public static function isNginx(): bool
    {
        $software = self::getServerSoftware();
        return stripos($software, 'nginx') !== false;
    }

    /**
     * Get current domain safely
     * @return string
     */
    public static function getCurrentDomain(): string
    {
        $request = request();
        
        // Use Laravel's request methods
        $host = $request->getHost();
        $scheme = $request->getScheme();
        
        return $scheme . '://' . $host;
    }

    /**
     * Get server information safely
     * @return array
     */
    public static function getServerInfo(): array
    {
        return [
            'software' => self::getServerSoftware(),
            'execution_time' => self::getExecutionTime(),
            'is_apache' => self::isApache(),
            'is_nginx' => self::isNginx(),
            'domain' => self::getCurrentDomain(),
        ];
    }
}
