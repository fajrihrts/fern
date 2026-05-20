<?php
/**
 * Activity Log System
 * Track user actions in the system
 */

class ActivityLog {
    
    /**
     * Log an activity
     */
    public static function log($action, $description, $userId = null, $metadata = []) {
        try {
            $db = getDbConnection();
            
            // Get user ID from session if not provided
            if ($userId === null && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }
            
            // Get IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Get user agent
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Prepare metadata
            $metadataJson = !empty($metadata) ? json_encode($metadata) : null;
            
            // Insert log
            $stmt = $db->prepare("
                INSERT INTO activity_logs (
                    id, user_id, action, description, metadata, 
                    ip_address, user_agent, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                generateUuid(),
                $userId,
                $action,
                $description,
                $metadataJson,
                $ipAddress,
                $userAgent
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Logger::error('Failed to log activity', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get recent activities
     */
    public static function getRecent($limit = 50, $userId = null) {
        $db = getDbConnection();
        
        $sql = "
            SELECT a.*, u.name as user_name, u.email as user_email
            FROM activity_logs a
            LEFT JOIN users u ON a.user_id = u.id
        ";
        
        if ($userId) {
            $sql .= " WHERE a.user_id = ?";
        }
        
        $sql .= " ORDER BY a.created_at DESC LIMIT ?";
        
        $stmt = $db->prepare($sql);
        
        if ($userId) {
            $stmt->execute([$userId, $limit]);
        } else {
            $stmt->execute([$limit]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get activities with pagination
     */
    public static function paginate($page = 1, $perPage = 20, $filters = []) {
        $db = getDbConnection();
        
        // Build WHERE clause
        $where = [];
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $where[] = "a.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "a.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(a.description LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(a.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(a.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Count total
        $countSql = "SELECT COUNT(*) FROM activity_logs a LEFT JOIN users u ON a.user_id = u.id $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Create paginator
        $paginator = new Paginator($total, $perPage, $page);
        
        // Get data
        $sql = "
            SELECT a.*, u.name as user_name, u.email as user_email
            FROM activity_logs a
            LEFT JOIN users u ON a.user_id = u.id
            $whereClause
            ORDER BY a.created_at DESC
            {$paginator->getLimit()}
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        return [
            'data' => $data,
            'paginator' => $paginator
        ];
    }
    
    /**
     * Get activity statistics
     */
    public static function getStats($days = 7) {
        $db = getDbConnection();
        
        $sql = "
            SELECT 
                action,
                COUNT(*) as count,
                DATE(created_at) as date
            FROM activity_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY action, DATE(created_at)
            ORDER BY date DESC, count DESC
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$days]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Clean old logs
     */
    public static function cleanOld($days = 90) {
        try {
            $db = getDbConnection();
            
            $stmt = $db->prepare("
                DELETE FROM activity_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            
            $stmt->execute([$days]);
            
            $deleted = $stmt->rowCount();
            
            Logger::info("Cleaned old activity logs", ['deleted' => $deleted]);
            
            return $deleted;
            
        } catch (Exception $e) {
            Logger::error('Failed to clean old activity logs', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Predefined action types
     */
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_REGISTER = 'register';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_UPLOAD = 'upload';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_EXPORT = 'export';
}
