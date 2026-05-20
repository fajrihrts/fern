<?php
/**
 * Cleanup Script
 * Run this periodically to clean old data
 * 
 * Usage:
 * - Via cron: 0 2 * * * /usr/bin/php /path/to/fern/cleanup.php
 * - Via browser: http://yoursite.com/cleanup.php?key=YOUR_SECRET_KEY
 * - Via CLI: php cleanup.php
 */

require_once __DIR__ . '/autoload.php';

// Security: Require secret key if accessed via browser
if (php_sapi_name() !== 'cli') {
    // Use secret key from config
    $secretKey = defined('CLEANUP_SECRET_KEY') ? CLEANUP_SECRET_KEY : 'fern_cleanup_2026';
    
    if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
        http_response_code(403);
        die('Access denied');
    }
    
    // Set content type
    header('Content-Type: text/plain');
}

echo "=== Fern Cleanup Script ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Clean old activity logs (90 days)
echo "1. Cleaning old activity logs...\n";
$deleted = ActivityLog::cleanOld(90);
echo "   Deleted: $deleted logs\n\n";

// 2. Clean expired cache
echo "2. Cleaning expired cache...\n";
$cleaned = Cache::cleanExpired();
echo "   Cleaned: $cleaned cache files\n\n";

// 3. Clean old log files (30 days)
echo "3. Cleaning old log files...\n";
Logger::cleanOldLogs(30);
echo "   Old log files cleaned\n\n";

// 4. Clean old rate limit files
echo "4. Cleaning old rate limit files...\n";
$cleaned = RateLimiter::cleanOld();
echo "   Cleaned: $cleaned rate limit files\n\n";

// 5. Optimize database tables (optional)
echo "5. Optimizing database tables...\n";
try {
    $db = getDbConnection();
    $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    $tables = ['users', 'registrations', 'attendance_reports', 'posts', 'testimonials', 'activity_logs'];
    
    foreach ($tables as $table) {
        $db->exec("OPTIMIZE TABLE $table");
        echo "   Optimized: $table\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Generate statistics
echo "6. Statistics:\n";
try {
    $db = getDbConnection();
    $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    $stats = [
        'Total Users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'Total Registrations' => $db->query("SELECT COUNT(*) FROM registrations")->fetchColumn(),
        'Total Activity Logs' => $db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn(),
        'Cache Files' => count(glob(BASE_PATH . '/cache/*.cache')),
        'Log Files' => count(glob(BASE_PATH . '/logs/app-*.log')),
    ];
    
    foreach ($stats as $key => $value) {
        echo "   $key: $value\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Cleanup Complete ===\n";
echo "Finished at: " . date('Y-m-d H:i:s') . "\n";

// Log cleanup activity
Logger::info('Cleanup script executed', [
    'deleted_logs' => $deleted ?? 0,
    'cleaned_cache' => $cleaned ?? 0
]);
