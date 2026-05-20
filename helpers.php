<?php
// Helper Functions

/**
 * Get database connection
 * Returns a PDO instance with proper configuration
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            // Use Unix socket if specified
            if (!empty(DB_SOCKET)) {
                $dsn = "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            
            if (defined('APP_DEBUG') && APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Terjadi kesalahan koneksi database. Silakan coba lagi nanti.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Get authenticated user
 * Returns user data if logged in, null otherwise
 */
function auth() {
    if (isset($_SESSION['user_id'])) {
        static $user = null;
        
        if ($user === null) {
            $stmt = safeQuery("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
            $user = $stmt ? $stmt->fetch() : null;
        }
        
        return $user;
    }
    return null;
}

/**
 * Generate UUID v4
 */
function generateUuid(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect helper
 */
function redirect($path) {
    header("Location: " . APP_URL . $path);
    exit;
}

/**
 * Get current URL path
 */
function getCurrentPath() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $basePath = parse_url(APP_URL, PHP_URL_PATH);
    if ($basePath && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    return $path ?: '/';
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Upload file helper
 */
function uploadFile($file, $directory, $allowedTypes, $maxSize = MAX_FILE_SIZE) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload gagal'];
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan'];
    }
    
    // Validate file size
    if ($file['size'] > $maxSize) {
        $maxMB = $maxSize / 1048576;
        return ['success' => false, 'error' => "Ukuran file maksimal {$maxMB}MB"];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateUuid() . '.' . $extension;
    $uploadPath = BASE_PATH . '/uploads/' . $directory . '/' . $filename;
    
    // Create directory if not exists
    $dir = BASE_PATH . '/uploads/' . $directory;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'path' => $directory . '/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Gagal menyimpan file'];
}

/**
 * Delete file helper
 */
function deleteFile($path) {
    $fullPath = BASE_PATH . '/uploads/' . $path;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

/**
 * Format date to Indonesian
 */
function formatDateIndo($date) {
    if (!$date) return '-';
    
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
 * Format datetime to Indonesian
 */
function formatDateTimeIndo($datetime) {
    if (!$datetime) return '-';
    
    $date = formatDateIndo($datetime);
    $time = date('H:i', strtotime($datetime));
    
    return "$date $time";
}

/**
 * Get status badge HTML
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="nb-badge nb-badge-warning">Menunggu Verifikasi</span>',
        'approved' => '<span class="nb-badge nb-badge-success">Diterima</span>',
        'rejected' => '<span class="nb-badge nb-badge-danger">Ditolak</span>',
    ];
    return $badges[$status] ?? $status;
}

/**
 * Get internship status badge HTML
 */
function getInternshipStatusBadge($status) {
    $badges = [
        'not_started' => '<span class="nb-badge" style="background: #9CA3AF;">Belum Mulai</span>',
        'ongoing' => '<span class="nb-badge nb-badge-info">Sedang Magang</span>',
        'completed' => '<span class="nb-badge nb-badge-success">Selesai</span>',
        'terminated' => '<span class="nb-badge nb-badge-danger">Berhenti</span>',
    ];
    return $badges[$status] ?? $status;
}

/**
 * Get attendance status badge HTML
 */
function getAttendanceStatusBadge($status) {
    $badges = [
        'hadir' => '<span class="nb-badge nb-badge-success">Hadir</span>',
        'izin' => '<span class="nb-badge nb-badge-warning">Izin</span>',
        'sakit' => '<span class="nb-badge nb-badge-info">Sakit</span>',
        'alpha' => '<span class="nb-badge nb-badge-danger">Alpha</span>',
    ];
    return $badges[$status] ?? $status;
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get user initials for avatar
 */
function getInitials($name) {
    $words = explode(' ', $name);
    if (count($words) >= 2) {
        return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Check if user has permission
 * Alias for Permission::can()
 */
function can($permission) {
    return Permission::can($permission);
}

/**
 * Check if user has any of the permissions
 */
function canAny(array $permissions) {
    return Permission::canAny($permissions);
}

/**
 * Check if user has all permissions
 */
function canAll(array $permissions) {
    return Permission::canAll($permissions);
}

/**
 * Authorize permission or fail
 * 
 * @param string $permission Permission to check
 * @param string $message Error message
 * @throws Exception
 */
function authorize($permission, $message = 'Anda tidak memiliki akses untuk melakukan aksi ini') {
    if (!Permission::can($permission)) {
        http_response_code(403);
        setFlash('danger', $message);
        
        // Redirect based on role
        $user = auth();
        if ($user) {
            redirect(Role::getDefaultPath($user['role']));
        } else {
            redirect('/login');
        }
        exit;
    }
}

/**
 * Authorize policy or fail
 * 
 * @param string $policy Policy class name
 * @param string $action Action to check (view, create, update, delete)
 * @param mixed $resource Resource to check (optional for create)
 * @param string $message Error message
 */
function authorizePolicy($policy, $action, $resource = null, $message = 'Anda tidak memiliki akses untuk melakukan aksi ini') {
    $user = auth();
    if (!$user) {
        redirect('/login');
        exit;
    }
    
    $allowed = false;
    
    if ($action === 'create') {
        $allowed = $policy::create($user);
    } elseif ($resource) {
        $allowed = $policy::$action($user, $resource);
    }
    
    if (!$allowed) {
        http_response_code(403);
        setFlash('danger', $message);
        redirect(Role::getDefaultPath($user['role']));
        exit;
    }
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if date is today
 */
function isToday($date) {
    return date('Y-m-d', strtotime($date)) === date('Y-m-d');
}

/**
 * Get asset URL
 */
function asset($path) {
    return APP_URL . '/assets/' . $path;
}

/**
 * Get upload URL
 */
function upload($path) {
    return APP_URL . '/uploads/' . $path;
}

/**
 * Sanitize HTML content (for rich text editor output)
 * Allows safe HTML tags while removing dangerous ones
 */
function sanitizeHtml($html) {
    // Allow these tags
    $allowedTags = '<p><br><strong><b><em><i><u><s><h2><h3><ol><ul><li><a><blockquote><pre><code><sub><sup><div><span>';
    return strip_tags($html, $allowedTags);
}

/**
 * Display HTML content safely
 * Handles both plain text (old posts) and HTML (new posts from Quill)
 */
function displayHtml($content) {
    // Check if content contains HTML tags
    if ($content !== strip_tags($content)) {
        // Content has HTML tags, sanitize and display
        return sanitizeHtml($content);
    } else {
        // Plain text content, convert line breaks to <p> tags
        return '<p>' . nl2br(e($content)) . '</p>';
    }
}

/**
 * Check if database connection is available
 * Returns true if connected, false otherwise
 */
function isDatabaseConnected() {
    try {
        $conn = getDbConnection();
        // Try a simple query to verify connection
        $conn->query('SELECT 1');
        return true;
    } catch (Exception $e) {
        error_log("Database connection check failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute database query with error handling
 * Returns result on success, false on failure
 */
function safeQuery($sql, $params = []) {
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage() . " | SQL: " . $sql);
        
        // Check if it's a connection error
        if (in_array($e->getCode(), ['HY000', '2002', '2006'])) {
            handleDatabaseError($e);
        }
        
        return false;
    }
}

/**
 * Execute database query and fetch all results
 */
function fetchAll($sql, $params = []) {
    $stmt = safeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Execute database query and fetch single row
 */
function fetchOne($sql, $params = []) {
    $stmt = safeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

/**
 * Execute database insert/update/delete with error handling
 * Returns true on success, false on failure
 */
function executeQuery($sql, $params = []) {
    $stmt = safeQuery($sql, $params);
    return $stmt !== false;
}

/**
 * Get last insert ID safely
 */
function getLastInsertId() {
    try {
        $conn = getDbConnection();
        return $conn->lastInsertId();
    } catch (Exception $e) {
        error_log("Error getting last insert ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Test database connection and show status
 * Useful for debugging
 */
function testDatabaseConnection() {
    echo "<h3>Testing Database Connection...</h3>";
    
    try {
        $conn = getDbConnection();
        echo "<p style='color: green;'>✓ Database connection successful!</p>";
        
        // Test query
        $stmt = $conn->query("SELECT DATABASE() as db_name, VERSION() as version");
        $result = $stmt->fetch();
        
        echo "<p><strong>Database:</strong> " . $result['db_name'] . "</p>";
        echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
        
        // Test tables
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p><strong>Tables found:</strong> " . count($tables) . "</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        return true;
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database connection failed!</p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        return false;
    }
}
