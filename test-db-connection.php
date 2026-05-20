<?php
/**
 * Database Connection Test Script
 * 
 * This script tests the database connection and displays detailed information
 * about the connection status, configuration, and available tables.
 * 
 * Usage: Access this file directly in your browser
 * Example: http://localhost:8000/test-db-connection.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// Security: Only allow in debug mode
if (!APP_DEBUG) {
    die("This test script is only available in debug mode. Set APP_DEBUG to true in config.php");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f7fafc;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 40px;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #718096;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f7fafc;
            border-radius: 6px;
            border-left: 4px solid #4299e1;
        }
        .section h3 {
            color: #2d3748;
            margin-bottom: 15px;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            font-weight: 600;
            color: #4a5568;
        }
        .config-value {
            color: #2d3748;
            font-family: 'Courier New', monospace;
        }
        .success {
            color: #38a169;
            font-weight: 600;
        }
        .error {
            color: #e53e3e;
            font-weight: 600;
        }
        .warning {
            color: #d69e2e;
            font-weight: 600;
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
        }
        li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #38a169;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background: #4299e1;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn:hover {
            background: #3182ce;
        }
        pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Test</h1>
        <p class="subtitle">Testing database connectivity and configuration</p>

        <!-- Configuration Section -->
        <div class="section">
            <h3>📋 Database Configuration</h3>
            <div class="config-item">
                <span class="config-label">Host:</span>
                <span class="config-value"><?= e(DB_HOST) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Database Name:</span>
                <span class="config-value"><?= e(DB_NAME) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Username:</span>
                <span class="config-value"><?= e(DB_USER) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Socket Path:</span>
                <span class="config-value"><?= e(DB_SOCKET) ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Socket Exists:</span>
                <span class="config-value <?= file_exists(DB_SOCKET) ? 'success' : 'error' ?>">
                    <?= file_exists(DB_SOCKET) ? '✓ Yes' : '✗ No' ?>
                </span>
            </div>
        </div>

        <!-- Connection Test Section -->
        <div class="section">
            <h3>🔌 Connection Test</h3>
            <?php
            $startTime = microtime(true);
            $connected = testDatabaseConnection();
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            ?>
            <p style="margin-top: 15px;">
                <strong>Connection Time:</strong> <?= $duration ?> ms
            </p>
        </div>

        <!-- Troubleshooting Section -->
        <?php if (!$connected): ?>
        <div class="section" style="border-left-color: #e53e3e;">
            <h3>🔧 Troubleshooting Steps</h3>
            <ul>
                <li>Make sure MySQL/XAMPP is running</li>
                <li>Verify the socket path in config.php matches your XAMPP installation</li>
                <li>Check if the database '<?= e(DB_NAME) ?>' exists</li>
                <li>Verify database user credentials</li>
                <li>Run the import-db.sh script to create the database</li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="javascript:location.reload()" class="btn">🔄 Test Again</a>
            <a href="<?= APP_URL ?>" class="btn" style="background: #48bb78;">← Back to Home</a>
        </div>
    </div>
</body>
</html>
