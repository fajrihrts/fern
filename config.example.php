<?php
/**
 * Configuration File - Example
 * 
 * Copy this file to config.php and update with your settings
 * DO NOT commit config.php to repository!
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Optional: Unix socket path (for XAMPP Mac or specific hosting)
// Leave empty if not needed
define('DB_SOCKET', '');

// ============================================
// APPLICATION CONFIGURATION
// ============================================

define('APP_NAME', 'Portal e-Registrasi Magang BPS PPU');
define('APP_URL', 'https://yourdomain.com'); // Change to your domain
define('APP_ENV', 'production'); // production, staging, development
define('APP_DEBUG', false); // Set to false in production!

// ============================================
// SECURITY CONFIGURATION
// ============================================

// Session configuration
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('SESSION_NAME', 'FERN_SESSION');

// CSRF token expiry
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// Password hashing cost (10-12 recommended)
define('PASSWORD_COST', 12);

// ============================================
// FILE UPLOAD CONFIGURATION
// ============================================

// Maximum file size (in bytes)
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Allowed image types
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
]);

// Allowed document types
define('ALLOWED_DOCUMENT_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]);

// Upload directories
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', APP_URL . '/uploads');

// ============================================
// CACHE CONFIGURATION
// ============================================

define('CACHE_ENABLED', true);
define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_LIFETIME', 3600); // 1 hour

// ============================================
// LOGGING CONFIGURATION
// ============================================

define('LOG_ENABLED', true);
define('LOG_DIR', __DIR__ . '/logs');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// ============================================
// RATE LIMITING
// ============================================

define('RATE_LIMIT_ENABLED', true);
define('RATE_LIMIT_MAX_ATTEMPTS', 5);
define('RATE_LIMIT_WINDOW', 900); // 15 minutes

// ============================================
// EMAIL CONFIGURATION (Optional)
// ============================================

define('MAIL_ENABLED', false);
define('MAIL_FROM', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', APP_NAME);

// SMTP settings (if using SMTP)
define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 587);
define('MAIL_SMTP_USER', 'your-email@gmail.com');
define('MAIL_SMTP_PASS', 'your-app-password');
define('MAIL_SMTP_SECURE', 'tls'); // tls or ssl

// ============================================
// TIMEZONE
// ============================================

date_default_timezone_set('Asia/Makassar');

// ============================================
// ERROR REPORTING
// ============================================

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// PATHS
// ============================================

define('BASE_PATH', __DIR__);
define('ROOT_PATH', __DIR__);
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PAGES_PATH', ROOT_PATH . '/pages');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// ============================================
// DEPLOYMENT CONFIGURATION
// ============================================

// Auto-deploy secret (for deploy.php webhook)
// Generate a strong random token: openssl rand -hex 32
define('DEPLOY_SECRET', 'your-super-secret-deploy-token-here');
define('DEPLOY_ENABLED', true);
define('DEPLOY_BRANCH', 'main');

// ============================================
// MAINTENANCE MODE
// ============================================

define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.');
define('MAINTENANCE_ALLOWED_IPS', [
    '127.0.0.1',
    // Add your IP here to access during maintenance
]);
