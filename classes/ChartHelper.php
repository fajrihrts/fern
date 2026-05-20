<?php
/**
 * Chart Helper
 * Generate data for Chart.js
 */

class ChartHelper {
    
    /**
     * Get registrations by month (last 6 months)
     */
    public static function getRegistrationsByMonth($months = 6) {
        $db = getDbConnection();
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $monthName = date('M Y', strtotime($date));
            
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM registrations 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
            ");
            $stmt->execute([$date]);
            $count = $stmt->fetchColumn();
            
            $data[] = [
                'month' => $monthName,
                'count' => (int) $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Get registrations by status
     */
    public static function getRegistrationsByStatus() {
        $db = getDbConnection();
        
        $statuses = ['pending', 'approved', 'rejected'];
        $data = [];
        
        foreach ($statuses as $status) {
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM registrations 
                WHERE status = ?
            ");
            $stmt->execute([$status]);
            $count = $stmt->fetchColumn();
            
            $data[] = [
                'status' => ucfirst($status),
                'count' => (int) $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Get attendance by status (current month)
     */
    public static function getAttendanceByStatus() {
        $db = getDbConnection();
        
        $statuses = ['hadir', 'izin', 'sakit', 'alpha'];
        $data = [];
        
        foreach ($statuses as $status) {
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM attendance_reports 
                WHERE status = ?
                AND MONTH(date) = MONTH(CURRENT_DATE)
                AND YEAR(date) = YEAR(CURRENT_DATE)
            ");
            $stmt->execute([$status]);
            $count = $stmt->fetchColumn();
            
            $data[] = [
                'status' => ucfirst($status),
                'count' => (int) $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Get internship status distribution
     */
    public static function getInternshipStatus() {
        $db = getDbConnection();
        
        $statuses = ['not_started', 'ongoing', 'completed', 'terminated'];
        $data = [];
        
        foreach ($statuses as $status) {
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM registrations 
                WHERE internship_status = ?
            ");
            $stmt->execute([$status]);
            $count = $stmt->fetchColumn();
            
            $label = [
                'not_started' => 'Belum Mulai',
                'ongoing' => 'Sedang Magang',
                'completed' => 'Selesai',
                'terminated' => 'Berhenti'
            ][$status];
            
            $data[] = [
                'status' => $label,
                'count' => (int) $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Get daily attendance for current month
     */
    public static function getDailyAttendance() {
        $db = getDbConnection();
        $data = [];
        
        $daysInMonth = date('t');
        $currentMonth = date('Y-m');
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonth . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            // Use prepared statement to prevent SQL injection
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM attendance_reports 
                WHERE date = ?
            ");
            $stmt->execute([$date]);
            $count = $stmt->fetchColumn();
            
            $data[] = [
                'date' => $day,
                'count' => (int) $count
            ];
        }
        
        return $data;
    }
    
    /**
     * Get activity logs by action (last 7 days)
     */
    public static function getActivityByAction($days = 7) {
        $db = getDbConnection();
        
        // Use prepared statement to prevent SQL injection
        $stmt = $db->prepare("
            SELECT action, COUNT(*) as count
            FROM activity_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY action
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute([(int)$days]);
        $result = $stmt->fetchAll();
        
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'action' => ucfirst($row['action']),
                'count' => (int) $row['count']
            ];
        }
        
        return $data;
    }
    
    /**
     * Convert data to Chart.js format
     */
    public static function toChartJs($data, $labelKey = 'label', $valueKey = 'count') {
        $labels = [];
        $values = [];
        
        foreach ($data as $item) {
            $labels[] = $item[$labelKey] ?? $item[array_keys($item)[0]];
            $values[] = $item[$valueKey] ?? $item[array_keys($item)[1]];
        }
        
        return [
            'labels' => $labels,
            'data' => $values
        ];
    }
    
    /**
     * Generate colors for charts
     */
    public static function getColors($count = 1) {
        $colors = [
            '#FFEB3B', // Primary (Yellow)
            '#00E5FF', // Accent (Cyan)
            '#4CAF50', // Success (Green)
            '#FF5722', // Danger (Red)
            '#2196F3', // Info (Blue)
            '#FF9800', // Warning (Orange)
            '#9C27B0', // Purple
            '#E91E63', // Pink
            '#00BCD4', // Teal
            '#8BC34A', // Light Green
        ];
        
        if ($count === 1) {
            return $colors[0];
        }
        
        return array_slice($colors, 0, $count);
    }
}
