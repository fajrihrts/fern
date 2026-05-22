<?php
/**
 * Cleanup Expired OTP Records
 * 
 * This script should be run periodically via cron job
 * Recommended: Daily at 2 AM
 * 
 * Cron job example:
 * 0 2 * * * cd /path/to/fern && php cleanup-otp.php >> logs/cleanup.log 2>&1
 */

require_once __DIR__ . '/autoload.php';

try {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] Starting OTP cleanup...\n";
    
    $count = PasswordReset::cleanupExpiredOTPs();
    
    echo "[$timestamp] ✅ Cleaned up {$count} expired OTP records\n";
    
    // Log to activity log
    if ($count > 0) {
        ActivityLog::log(
            'otp_cleanup',
            "Cleaned up {$count} expired OTP records",
            null
        );
    }
    
    exit(0);
    
} catch (Exception $e) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] ❌ Error: " . $e->getMessage() . "\n";
    error_log("OTP Cleanup Error: " . $e->getMessage());
    exit(1);
}
