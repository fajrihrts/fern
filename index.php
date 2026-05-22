<?php
// FERN - Router Utama

// Configure session before starting
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session BEFORE loading config (to avoid ini_set warnings)
session_start();

// Load autoloader (includes config, helpers, auth)
require_once __DIR__ . '/autoload.php';

// Set custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    Logger::error("PHP Error: $errstr", [
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ]);
    
    if (APP_DEBUG) {
        echo "<b>Error:</b> $errstr in <b>$errfile</b> on line <b>$errline</b><br>";
    }
    
    return true;
});

// Set exception handler
set_exception_handler(function($exception) {
    Logger::exception($exception);
    
    if (APP_DEBUG) {
        echo "<h2>Exception:</h2>";
        echo "<p><b>Message:</b> " . $exception->getMessage() . "</p>";
        echo "<p><b>File:</b> " . $exception->getFile() . ":" . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        echo "Terjadi kesalahan. Silakan coba lagi nanti.";
    }
});

// Check remember me
checkRememberMe();

// Get current path
$path = getCurrentPath();
$method = $_SERVER['REQUEST_METHOD'];

// Route handling
try {
    // Public routes
    if ($path === '/' || $path === '') {
        require 'pages/home.php';
    }
    elseif ($path === '/blog') {
        require 'pages/blog.php';
    }
    elseif (preg_match('/^\/post\/([a-f0-9-]+)$/', $path, $matches)) {
        $_GET['id'] = $matches[1];
        require 'pages/post.php';
    }
    elseif ($path === '/review') {
        require 'pages/review.php';
    }
    elseif ($path === '/tentang') {
        require 'pages/tentang.php';
    }
    elseif ($path === '/daftar') {
        require 'pages/daftar.php';
    }
    
    // Auth routes (guest only)
    elseif ($path === '/register') {
        require 'pages/register.php';
    }
    elseif ($path === '/login') {
        require 'pages/login.php';
    }
    elseif ($path === '/forgot-password') {
        require 'pages/forgot-password.php';
    }
    elseif ($path === '/reset-password') {
        require 'pages/reset-password.php';
    }
    elseif ($path === '/logout' && $method === 'POST') {
        requireAuth();
        logout();
    }
    
    // Peserta routes
    elseif ($path === '/dashboard') {
        requireRole('peserta');
        require 'pages/dashboard.php';
    }
    elseif ($path === '/profile/edit') {
        requireRole('peserta');
        require 'pages/edit-profile.php';
    }
    elseif ($path === '/pendaftaran/lengkapi') {
        requireRole('peserta');
        require 'pages/lengkapi.php';
    }
    elseif ($path === '/registration/edit') {
        requireRole('peserta');
        require 'pages/edit-registration.php';
    }
    elseif ($path === '/laporan') {
        requireRole('peserta');
        require 'pages/attendance-index.php';
    }
    elseif ($path === '/laporan/create') {
        requireRole('peserta');
        require 'pages/attendance-create.php';
    }
    elseif (preg_match('/^\/laporan\/([0-9-]+)$/', $path, $matches)) {
        requireRole('peserta');
        $_GET['date'] = $matches[1];
        require 'pages/attendance-show.php';
    }
    elseif ($path === '/testimoni/create') {
        requireRole('peserta');
        require 'pages/testimonial-create.php';
    }
    
    // Admin routes
    elseif ($path === '/admin' || $path === '/admin/') {
        requireRole('admin');
        $user = auth(); // Get user before loading admin page
        require 'admin/index.php';
    }
    elseif (strpos($path, '/admin/') === 0) {
        requireRole('admin');
        $user = auth(); // Get user before loading admin page
        $adminPath = substr($path, 7); // Remove '/admin/'
        
        if ($adminPath === 'registrations') {
            require 'admin/registrations.php';
        }
        elseif ($adminPath === 'attendance') {
            require 'admin/attendance.php';
        }
        elseif ($adminPath === 'posts') {
            require 'admin/posts.php';
        }
        elseif ($adminPath === 'testimonials') {
            require 'admin/testimonials.php';
        }
        elseif ($adminPath === 'users') {
            requireRole('super_admin');
            require 'admin/users.php';
        }
        elseif ($adminPath === 'activity-logs') {
            require 'admin/activity-logs.php';
        }
        elseif (strpos($adminPath, 'export/') === 0) {
            require 'admin/export.php';
        }
        else {
            http_response_code(404);
            echo "404 - Halaman tidak ditemukan";
        }
    }
    
    // Document download route (requires auth)
    elseif (preg_match('/^\/dokumen\//', $path)) {
        requireAuth();
        require 'pages/download-document.php';
    }
    
    // Health check
    elseif ($path === '/health') {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok', 'timestamp' => time()]);
    }
    
    // 404
    else {
        http_response_code(404);
        echo "404 - Halaman tidak ditemukan";
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    http_response_code(500);
    
    // Check if this is an AJAX request
    if (isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan server'
        ]);
    } else {
        echo "500 - Terjadi kesalahan server";
    }
}
