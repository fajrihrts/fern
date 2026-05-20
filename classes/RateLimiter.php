<?php
/**
 * Rate Limiter Class
 * Protect against brute force attacks
 * File-based (no Redis needed)
 */

class RateLimiter {
    private static $storageDir;
    
    public static function init() {
        self::$storageDir = BASE_PATH . '/cache/rate_limits';
        
        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0755, true);
        }
    }
    
    /**
     * Get storage file path for key
     */
    private static function getFilePath($key) {
        return self::$storageDir . '/' . md5($key) . '.limit';
    }
    
    /**
     * Check if action is allowed
     * 
     * @param string $key Unique identifier (e.g., 'login:192.168.1.1')
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     * @return bool True if allowed, false if rate limited
     */
    public static function attempt($key, $maxAttempts = 5, $decayMinutes = 1) {
        if (!self::$storageDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        $now = time();
        $decaySeconds = $decayMinutes * 60;
        
        // Get current attempts
        $data = self::getData($file);
        
        // Clean old attempts
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $decaySeconds) {
            return ($now - $timestamp) < $decaySeconds;
        });
        
        // Check if rate limited
        if (count($data['attempts']) >= $maxAttempts) {
            $oldestAttempt = min($data['attempts']);
            $data['locked_until'] = $oldestAttempt + $decaySeconds;
            self::saveData($file, $data);
            
            Logger::warning('Rate limit exceeded', [
                'key' => $key,
                'attempts' => count($data['attempts']),
                'max_attempts' => $maxAttempts
            ]);
            
            return false;
        }
        
        // Add new attempt
        $data['attempts'][] = $now;
        self::saveData($file, $data);
        
        return true;
    }
    
    /**
     * Check if key is rate limited
     */
    public static function tooManyAttempts($key, $maxAttempts = 5, $decayMinutes = 1) {
        if (!self::$storageDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        $now = time();
        $decaySeconds = $decayMinutes * 60;
        
        $data = self::getData($file);
        
        // Clean old attempts
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $decaySeconds) {
            return ($now - $timestamp) < $decaySeconds;
        });
        
        return count($data['attempts']) >= $maxAttempts;
    }
    
    /**
     * Get remaining attempts
     */
    public static function retriesLeft($key, $maxAttempts = 5, $decayMinutes = 1) {
        if (!self::$storageDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        $now = time();
        $decaySeconds = $decayMinutes * 60;
        
        $data = self::getData($file);
        
        // Clean old attempts
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $decaySeconds) {
            return ($now - $timestamp) < $decaySeconds;
        });
        
        return max(0, $maxAttempts - count($data['attempts']));
    }
    
    /**
     * Get seconds until available again
     */
    public static function availableIn($key, $decayMinutes = 1) {
        if (!self::$storageDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        $now = time();
        $decaySeconds = $decayMinutes * 60;
        
        $data = self::getData($file);
        
        if (empty($data['attempts'])) {
            return 0;
        }
        
        $oldestAttempt = min($data['attempts']);
        $availableAt = $oldestAttempt + $decaySeconds;
        
        return max(0, $availableAt - $now);
    }
    
    /**
     * Clear rate limit for key
     */
    public static function clear($key) {
        $file = self::getFilePath($key);
        
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    /**
     * Hit the rate limiter (increment counter)
     */
    public static function hit($key, $decayMinutes = 1) {
        if (!self::$storageDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        $now = time();
        $decaySeconds = $decayMinutes * 60;
        
        $data = self::getData($file);
        
        // Clean old attempts
        $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $decaySeconds) {
            return ($now - $timestamp) < $decaySeconds;
        });
        
        // Add new hit
        $data['attempts'][] = $now;
        self::saveData($file, $data);
        
        return count($data['attempts']);
    }
    
    /**
     * Get data from file
     */
    private static function getData($file) {
        if (!file_exists($file)) {
            return ['attempts' => [], 'locked_until' => null];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?: ['attempts' => [], 'locked_until' => null];
    }
    
    /**
     * Save data to file
     */
    private static function saveData($file, $data) {
        file_put_contents($file, json_encode($data));
    }
    
    /**
     * Clean old rate limit files
     */
    public static function cleanOld() {
        if (!self::$storageDir) {
            self::init();
        }
        
        $files = glob(self::$storageDir . '/*.limit');
        $now = time();
        $cleaned = 0;
        
        foreach ($files as $file) {
            // Delete files older than 1 hour
            if (($now - filemtime($file)) > 3600) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Helper: Get client IP
     */
    public static function getClientIp() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Check for proxy headers
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        return $ip;
    }
}

// Initialize
RateLimiter::init();
