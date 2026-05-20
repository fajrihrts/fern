<?php
/**
 * Simple Autoloader for Fern
 * No Composer required - works on any shared hosting
 */

// Load core files first
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

// Load all classes
$classFiles = [
    'Role',
    'Permission',
    'Policy',
    'Logger',
    'Cache',
    'Validator',
    'Paginator',
    'ActivityLog',
    'RateLimiter',
    'ChartHelper',
    'BulkAction'
];

foreach ($classFiles as $class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

// SPL Autoloader for future classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    
    return false;
});
