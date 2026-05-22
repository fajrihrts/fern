<?php
/**
 * Auto Deploy Script for cPanel
 * 
 * This script handles automatic deployment from Git repository
 * Triggered by GitHub/GitLab webhooks
 * 
 * Setup:
 * 1. Upload this file to your cPanel public_html or project root
 * 2. Set DEPLOY_SECRET in your environment or below
 * 3. Add webhook URL to your Git repository: https://yourdomain.com/deploy.php
 * 4. Set webhook secret in Git repository settings
 */

// ============================================
// CONFIGURATION
// ============================================

// Secret token for webhook authentication (CHANGE THIS!)
define('DEPLOY_SECRET', getenv('DEPLOY_SECRET') ?: 'your-secret-token-here-change-this');

// Git repository path (absolute path to your project)
define('REPO_PATH', __DIR__);

// Git branch to deploy
define('DEPLOY_BRANCH', 'main');

// Log file path
define('LOG_FILE', __DIR__ . '/logs/deploy.log');

// Enable/disable deployment
define('DEPLOY_ENABLED', true);

// Commands to run after deployment
define('POST_DEPLOY_COMMANDS', [
    // 'composer install --no-dev --optimize-autoloader',
    // 'php artisan migrate --force',
    // 'php artisan cache:clear',
]);

// ============================================
// FUNCTIONS
// ============================================

/**
 * Log message to file and output
 */
function logMessage($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$type}] {$message}\n";
    
    // Ensure log directory exists
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Write to log file
    file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    
    // Output to response
    echo $logEntry;
}

/**
 * Verify webhook signature
 */
function verifySignature($payload, $signature, $secret) {
    // GitHub signature format: sha256=hash
    if (strpos($signature, 'sha256=') === 0) {
        $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($hash, $signature);
    }
    
    // GitLab signature format: plain hash
    if (strpos($signature, 'sha256=') === false && strlen($signature) === 64) {
        $hash = hash_hmac('sha256', $payload, $secret);
        return hash_equals($hash, $signature);
    }
    
    return false;
}

/**
 * Execute shell command safely
 */
function executeCommand($command) {
    logMessage("Executing: {$command}");
    
    $output = [];
    $returnCode = 0;
    
    exec($command . ' 2>&1', $output, $returnCode);
    
    $outputStr = implode("\n", $output);
    
    if ($returnCode === 0) {
        logMessage("Success: {$outputStr}");
        return ['success' => true, 'output' => $outputStr];
    } else {
        logMessage("Failed: {$outputStr}", 'ERROR');
        return ['success' => false, 'output' => $outputStr];
    }
}

/**
 * Perform git pull
 */
function gitPull() {
    // Change to repository directory
    chdir(REPO_PATH);
    
    // Fetch latest changes
    $result = executeCommand('git fetch origin ' . DEPLOY_BRANCH);
    if (!$result['success']) {
        return $result;
    }
    
    // Check if there are changes
    $result = executeCommand('git diff HEAD origin/' . DEPLOY_BRANCH . ' --name-only');
    if (empty(trim($result['output']))) {
        logMessage("No changes to deploy");
        return ['success' => true, 'output' => 'No changes'];
    }
    
    logMessage("Changes detected:\n" . $result['output']);
    
    // Stash any local changes
    executeCommand('git stash');
    
    // Pull changes
    $result = executeCommand('git pull origin ' . DEPLOY_BRANCH);
    if (!$result['success']) {
        return $result;
    }
    
    // Pop stash if exists
    executeCommand('git stash pop');
    
    return $result;
}

/**
 * Run post-deployment commands
 */
function runPostDeployCommands() {
    foreach (POST_DEPLOY_COMMANDS as $command) {
        $result = executeCommand($command);
        if (!$result['success']) {
            logMessage("Post-deploy command failed: {$command}", 'ERROR');
            return false;
        }
    }
    return true;
}

/**
 * Create backup before deployment
 */
function createBackup() {
    $backupDir = REPO_PATH . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . '/backup-' . date('Y-m-d-His') . '.tar.gz';
    
    // Exclude certain directories from backup
    $excludes = '--exclude=backups --exclude=logs --exclude=cache --exclude=.git --exclude=node_modules';
    
    $command = "tar -czf {$backupFile} {$excludes} -C " . REPO_PATH . " .";
    $result = executeCommand($command);
    
    if ($result['success']) {
        logMessage("Backup created: {$backupFile}");
        
        // Keep only last 5 backups
        $backups = glob($backupDir . '/backup-*.tar.gz');
        if (count($backups) > 5) {
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $toDelete = array_slice($backups, 0, count($backups) - 5);
            foreach ($toDelete as $file) {
                unlink($file);
                logMessage("Deleted old backup: {$file}");
            }
        }
    }
    
    return $result['success'];
}

// ============================================
// MAIN EXECUTION
// ============================================

// Set content type
header('Content-Type: text/plain');

// Check if deployment is enabled
if (!DEPLOY_ENABLED) {
    http_response_code(503);
    logMessage("Deployment is disabled", 'WARNING');
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle GET request (for testing)
if ($method === 'GET') {
    logMessage("Deploy script is active and ready");
    echo "Deploy script is active\n";
    echo "Repository: " . REPO_PATH . "\n";
    echo "Branch: " . DEPLOY_BRANCH . "\n";
    echo "Last deployment: " . (file_exists(LOG_FILE) ? date('Y-m-d H:i:s', filemtime(LOG_FILE)) : 'Never') . "\n";
    exit;
}

// Handle POST request (webhook)
if ($method !== 'POST') {
    http_response_code(405);
    logMessage("Method not allowed: {$method}", 'ERROR');
    exit;
}

// Get payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Verify signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? $_SERVER['HTTP_X_GITLAB_TOKEN'] ?? '';

if (empty($signature)) {
    http_response_code(401);
    logMessage("No signature provided", 'ERROR');
    exit;
}

if (!verifySignature($payload, $signature, DEPLOY_SECRET)) {
    http_response_code(403);
    logMessage("Invalid signature", 'ERROR');
    exit;
}

logMessage("=== DEPLOYMENT STARTED ===");
logMessage("Triggered by: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));

// Check if push is to the correct branch
$branch = '';
if (isset($data['ref'])) {
    // GitHub/GitLab format
    $branch = str_replace('refs/heads/', '', $data['ref']);
} elseif (isset($data['push']['changes'][0]['new']['name'])) {
    // Bitbucket format
    $branch = $data['push']['changes'][0]['new']['name'];
}

if ($branch !== DEPLOY_BRANCH) {
    logMessage("Push to branch '{$branch}' ignored (deploying only '{" . DEPLOY_BRANCH . "}')", 'WARNING');
    exit;
}

logMessage("Push to branch '{$branch}' detected");

// Create backup
logMessage("Creating backup...");
if (!createBackup()) {
    logMessage("Backup failed, aborting deployment", 'ERROR');
    http_response_code(500);
    exit;
}

// Perform git pull
logMessage("Pulling changes from repository...");
$result = gitPull();

if (!$result['success']) {
    http_response_code(500);
    logMessage("=== DEPLOYMENT FAILED ===", 'ERROR');
    exit;
}

// Run post-deployment commands
if (!empty(POST_DEPLOY_COMMANDS)) {
    logMessage("Running post-deployment commands...");
    if (!runPostDeployCommands()) {
        http_response_code(500);
        logMessage("=== DEPLOYMENT FAILED (post-deploy commands) ===", 'ERROR');
        exit;
    }
}

// Clear PHP opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    logMessage("PHP opcache cleared");
}

logMessage("=== DEPLOYMENT COMPLETED SUCCESSFULLY ===");
http_response_code(200);
