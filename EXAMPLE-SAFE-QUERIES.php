<?php
/**
 * Example: Using Safe Database Query Functions
 * 
 * This file demonstrates how to use the safe query helper functions
 * that include automatic error handling for database operations.
 * 
 * These functions will automatically handle connection errors and
 * show user-friendly error pages when database issues occur.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

// ============================================================================
// EXAMPLE 1: Fetch All Records
// ============================================================================

/**
 * Old way (manual error handling):
 */
function getUsersOldWay() {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE role = ?");
        $stmt->execute(['user']);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return [];
    }
}

/**
 * New way (automatic error handling):
 */
function getUsersNewWay() {
    return fetchAll("SELECT * FROM users WHERE role = ?", ['user']);
}

// ============================================================================
// EXAMPLE 2: Fetch Single Record
// ============================================================================

/**
 * Old way:
 */
function getUserByIdOldWay($userId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return null;
    }
}

/**
 * New way:
 */
function getUserByIdNewWay($userId) {
    return fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
}

// ============================================================================
// EXAMPLE 3: Insert Data
// ============================================================================

/**
 * Old way:
 */
function createUserOldWay($name, $email, $password) {
    try {
        $db = getDbConnection();
        $userId = generateUuid();
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("
            INSERT INTO users (id, name, email, password, role, created_at, updated_at)
            VALUES (?, ?, ?, ?, 'user', NOW(), NOW())
        ");
        
        $stmt->execute([$userId, $name, $email, $hashedPassword]);
        return $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return false;
    }
}

/**
 * New way:
 */
function createUserNewWay($name, $email, $password) {
    $userId = generateUuid();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $success = executeQuery("
        INSERT INTO users (id, name, email, password, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'user', NOW(), NOW())
    ", [$userId, $name, $email, $hashedPassword]);
    
    return $success ? $userId : false;
}

// ============================================================================
// EXAMPLE 4: Update Data
// ============================================================================

/**
 * Old way:
 */
function updateUserOldWay($userId, $name, $email) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, email = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$name, $email, $userId]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return false;
    }
}

/**
 * New way:
 */
function updateUserNewWay($userId, $name, $email) {
    return executeQuery("
        UPDATE users 
        SET name = ?, email = ?, updated_at = NOW()
        WHERE id = ?
    ", [$name, $email, $userId]);
}

// ============================================================================
// EXAMPLE 5: Delete Data
// ============================================================================

/**
 * Old way:
 */
function deleteUserOldWay($userId) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Error: " . $e->getMessage());
        return false;
    }
}

/**
 * New way:
 */
function deleteUserNewWay($userId) {
    return executeQuery("DELETE FROM users WHERE id = ?", [$userId]);
}

// ============================================================================
// EXAMPLE 6: Complex Query with Joins
// ============================================================================

/**
 * Fetch registrations with user information
 */
function getRegistrationsWithUsers() {
    return fetchAll("
        SELECT 
            r.*,
            u.name as user_name,
            u.email as user_email
        FROM registrations r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.status = ?
        ORDER BY r.created_at DESC
    ", ['pending']);
}

// ============================================================================
// EXAMPLE 7: Count Records
// ============================================================================

/**
 * Count total users by role
 */
function countUsersByRole($role) {
    $result = fetchOne("SELECT COUNT(*) as total FROM users WHERE role = ?", [$role]);
    return $result ? (int)$result['total'] : 0;
}

// ============================================================================
// EXAMPLE 8: Check if Record Exists
// ============================================================================

/**
 * Check if email already exists
 */
function emailExists($email) {
    $result = fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    return $result !== null;
}

// ============================================================================
// EXAMPLE 9: Transaction (Advanced)
// ============================================================================

/**
 * For transactions, you still need to use getDbConnection() directly
 * But the connection will still have error handling
 */
function createRegistrationWithTransaction($userId, $data) {
    try {
        $db = getDbConnection();
        $db->beginTransaction();
        
        // Insert registration
        $registrationId = generateUuid();
        executeQuery("
            INSERT INTO registrations (id, user_id, institution, start_date, end_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ", [$registrationId, $userId, $data['institution'], $data['start_date'], $data['end_date']]);
        
        // Log activity
        executeQuery("
            INSERT INTO activity_logs (id, user_id, action, description, created_at)
            VALUES (?, ?, 'registration_created', 'New registration submitted', NOW())
        ", [generateUuid(), $userId]);
        
        $db->commit();
        return $registrationId;
        
    } catch (Exception $e) {
        if (isset($db)) {
            $db->rollBack();
        }
        error_log("Transaction Error: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// EXAMPLE 10: Check Database Connection Before Operations
// ============================================================================

/**
 * Check if database is available before performing operations
 */
function performDatabaseOperation() {
    // Check connection first
    if (!isDatabaseConnected()) {
        return ['success' => false, 'error' => 'Database tidak tersedia'];
    }
    
    // Perform operations
    $users = fetchAll("SELECT * FROM users LIMIT 10");
    
    return ['success' => true, 'data' => $users];
}

// ============================================================================
// USAGE IN REAL APPLICATION
// ============================================================================

/**
 * Example: User Management Page
 */
function userManagementPage() {
    // Fetch all users (automatic error handling)
    $users = fetchAll("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    
    // If database error occurs, the error page will be shown automatically
    // No need for try-catch blocks!
    
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . e($user['name']) . "</td>";
        echo "<td>" . e($user['email']) . "</td>";
        echo "<td>" . e($user['role']) . "</td>";
        echo "</tr>";
    }
}

/**
 * Example: Create User Form Handler
 */
function handleCreateUser() {
    // Validate input
    if (empty($_POST['email'])) {
        return ['success' => false, 'error' => 'Email required'];
    }
    
    // Check if email exists
    if (emailExists($_POST['email'])) {
        return ['success' => false, 'error' => 'Email already exists'];
    }
    
    // Create user (automatic error handling)
    $userId = createUserNewWay(
        $_POST['name'],
        $_POST['email'],
        $_POST['password']
    );
    
    if ($userId) {
        return ['success' => true, 'user_id' => $userId];
    } else {
        return ['success' => false, 'error' => 'Failed to create user'];
    }
}

// ============================================================================
// BENEFITS OF USING SAFE QUERY FUNCTIONS
// ============================================================================

/**
 * 1. Automatic Error Handling
 *    - No need for try-catch blocks in every query
 *    - Consistent error handling across the application
 * 
 * 2. User-Friendly Error Pages
 *    - Users see helpful error messages
 *    - Suggestions for fixing common issues
 * 
 * 3. Automatic Error Logging
 *    - All errors are logged automatically
 *    - Easier debugging and monitoring
 * 
 * 4. Cleaner Code
 *    - Less boilerplate code
 *    - More readable and maintainable
 * 
 * 5. Connection Error Detection
 *    - Automatically detects connection issues
 *    - Shows appropriate error page
 * 
 * 6. Consistent API
 *    - Same function signature across all queries
 *    - Easy to remember and use
 */

// ============================================================================
// MIGRATION GUIDE
// ============================================================================

/**
 * To migrate existing code:
 * 
 * 1. Replace direct PDO queries with safe functions:
 *    - $stmt->fetchAll() → fetchAll($sql, $params)
 *    - $stmt->fetch() → fetchOne($sql, $params)
 *    - $stmt->execute() → executeQuery($sql, $params)
 * 
 * 2. Remove try-catch blocks (unless you need custom error handling)
 * 
 * 3. Remove manual error logging (it's automatic now)
 * 
 * 4. Test thoroughly to ensure error handling works as expected
 */

?>
