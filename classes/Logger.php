<?php
/**
 * Simple Logger Class
 * File-based logging for shared hosting
 */

class Logger {
    private static $logFile;
    
    public static function init() {
        self::$logFile = BASE_PATH . '/logs/app-' . date('Y-m-d') . '.log';
        
        // Ensure logs directory exists
        $logDir = BASE_PATH . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log debug message (only in development)
     */
    public static function debug($message, $context = []) {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    /**
     * Write log entry
     */
    private static function log($level, $message, $context = []) {
        if (!self::$logFile) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Log exception
     */
    public static function exception($exception) {
        self::error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    /**
     * Clean old logs (keep last 30 days)
     */
    public static function cleanOldLogs($days = 30) {
        $logDir = BASE_PATH . '/logs';
        $files = glob($logDir . '/app-*.log');
        $cutoff = time() - ($days * 86400);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

// Initialize logger
Logger::init();
