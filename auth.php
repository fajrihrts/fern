<?php
// Authentication Functions

/**
 * Check if user is authenticated
 */
function isAuth() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current authenticated user
 */
function auth() {
    if (!isAuth()) {
        return null;
    }
    
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    // Refresh role in session if changed
    if ($user && isset($_SESSION['user_role']) && $user['role'] !== $_SESSION['user_role']) {
        $oldRole = $_SESSION['user_role'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log role change
        ActivityLog::log(
            'role_refreshed',
            "User role refreshed from {$oldRole} to {$user['role']}",
            $user['id']
        );
    }
    
    return $user;
}

/**
 * Login user
 */
function login($email, $password, $remember = false) {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Email atau password yang Anda masukkan salah'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['name'];
    
    // Log activity
    ActivityLog::log(
        ActivityLog::ACTION_LOGIN,
        "User {$user['name']} login ke sistem",
        $user['id']
    );
    
    // Remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        
        $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
           ->execute([$token, $user['id']]);
    }
    
    return ['success' => true, 'user' => $user];
}

/**
 * Register new user
 */
function register($name, $email, $password) {
    $db = getDbConnection();
    
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email sudah terdaftar'];
    }
    
    // Create user
    $userId = generateUuid();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    $stmt = $db->prepare("
        INSERT INTO users (id, name, email, password, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'peserta', NOW(), NOW())
    ");
    
    if ($stmt->execute([$userId, $name, $email, $hashedPassword])) {
        // Auto login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'peserta';
        $_SESSION['user_name'] = $name;
        
        // Log activity
        ActivityLog::log(
            ActivityLog::ACTION_REGISTER,
            "User baru mendaftar: {$name}",
            $userId
        );
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    return ['success' => false, 'error' => 'Gagal membuat akun'];
}

/**
 * Logout user
 */
function logout() {
    $userId = $_SESSION['user_id'] ?? null;
    $userName = $_SESSION['user_name'] ?? 'Unknown';
    
    // Log activity before clearing session
    if ($userId) {
        ActivityLog::log(
            ActivityLog::ACTION_LOGOUT,
            "User {$userName} logout dari sistem",
            $userId
        );
    }
    
    // Clear remember token
    if (isset($_COOKIE['remember_token'])) {
        $db = getDbConnection();
        $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")
           ->execute([$userId]);
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
    
    // Start new session for flash message
    session_start();
    setFlash('success', 'Anda telah berhasil logout');
    
    redirect('/login');
}

/**
 * Check if user has role
 */
function hasRole($role) {
    if (!isAuth()) {
        return false;
    }
    
    $userRole = $_SESSION['user_role'];
    
    // Use Role class for hierarchy check if available
    if (class_exists('Role')) {
        return Role::hasPermission($userRole, $role);
    }
    
    // Fallback to original logic
    if ($userRole === 'super_admin') {
        return true;
    }
    
    if ($role === 'admin' && in_array($userRole, ['admin', 'super_admin'])) {
        return true;
    }
    
    return $userRole === $role;
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isAuth()) {
        redirect('/login');
    }
}

/**
 * Require guest (not authenticated)
 */
function requireGuest() {
    if (isAuth()) {
        $role = $_SESSION['user_role'];
        
        if (class_exists('Role')) {
            redirect(Role::getDefaultPath($role));
        } else {
            // Fallback if Role class not loaded
            if (in_array($role, ['admin', 'super_admin'])) {
                redirect('/admin');
            } else {
                redirect('/dashboard');
            }
        }
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireAuth();
    
    if (!hasRole($role)) {
        $userRole = $_SESSION['user_role'];
        
        // Log unauthorized access attempt
        ActivityLog::log(
            'unauthorized_access',
            "User with role '{$userRole}' attempted to access '{$role}' area",
            $_SESSION['user_id']
        );
        
        // Redirect to appropriate dashboard
        if (class_exists('Role')) {
            redirect(Role::getDefaultPath($userRole));
        } else {
            // Fallback if Role class not loaded
            if (in_array($userRole, ['admin', 'super_admin'])) {
                redirect('/admin');
            } else {
                redirect('/dashboard');
            }
        }
    }
}

/**
 * Check remember me cookie
 */
function checkRememberMe() {
    if (!isAuth() && isset($_COOKIE['remember_token'])) {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE remember_token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
        }
    }
}
