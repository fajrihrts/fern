<?php
/**
 * Simple File-based Cache
 * Works on any shared hosting without Redis/Memcached
 */

class Cache {
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hour
    
    public static function init() {
        self::$cacheDir = BASE_PATH . '/cache';
        
        // Ensure cache directory exists
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cache file path
     */
    private static function getFilePath($key) {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Get cached value
     */
    public static function get($key, $default = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $file = self::getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // Check if expired
        if ($data['expires_at'] < time()) {
            unlink($file);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Set cache value
     */
    public static function set($key, $value, $ttl = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $ttl = $ttl ?? self::$defaultTTL;
        $file = self::getFilePath($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];
        
        return file_put_contents($file, serialize($data)) !== false;
    }
    
    /**
     * Check if cache exists and not expired
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Delete cache
     */
    public static function forget($key) {
        $file = self::getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     */
    public static function flush() {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Remember - Get from cache or execute callback and cache result
     */
    public static function remember($key, $ttl, $callback) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Clean expired cache
     */
    public static function cleanExpired() {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if ($data['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}

// Initialize cache
Cache::init();
