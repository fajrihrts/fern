<?php
/**
 * Router for PHP Built-in Server
 * Usage: php -S localhost:8000 router.php
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve the file as-is
}

// Route everything else to index.php
require_once __DIR__ . '/index.php';
