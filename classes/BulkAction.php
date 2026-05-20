<?php
/**
 * Bulk Action Helper
 * Handle bulk operations on multiple records
 */

class BulkAction {
    
    /**
     * Bulk delete records
     */
    public static function delete($table, $ids, $logDescription = null) {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Tidak ada data yang dipilih'];
        }
        
        try {
            $db = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $stmt = $db->prepare("DELETE FROM $table WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            
            $deleted = $stmt->rowCount();
            
            // Log activity
            if ($logDescription) {
                ActivityLog::log(
                    ActivityLog::ACTION_DELETE,
                    $logDescription . " ($deleted items)",
                    null,
                    ['ids' => $ids, 'count' => $deleted]
                );
            }
            
            return [
                'success' => true,
                'message' => "$deleted data berhasil dihapus",
                'count' => $deleted
            ];
            
        } catch (Exception $e) {
            Logger::error('Bulk delete failed', [
                'table' => $table,
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Gagal menghapus data'];
        }
    }
    
    /**
     * Bulk update field
     */
    public static function update($table, $ids, $field, $value, $logDescription = null) {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Tidak ada data yang dipilih'];
        }
        
        try {
            $db = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            $params = array_merge([$value], $ids);
            $stmt = $db->prepare("UPDATE $table SET $field = ? WHERE id IN ($placeholders)");
            $stmt->execute($params);
            
            $updated = $stmt->rowCount();
            
            // Log activity
            if ($logDescription) {
                ActivityLog::log(
                    ActivityLog::ACTION_UPDATE,
                    $logDescription . " ($updated items)",
                    null,
                    ['ids' => $ids, 'field' => $field, 'value' => $value, 'count' => $updated]
                );
            }
            
            return [
                'success' => true,
                'message' => "$updated data berhasil diupdate",
                'count' => $updated
            ];
            
        } catch (Exception $e) {
            Logger::error('Bulk update failed', [
                'table' => $table,
                'ids' => $ids,
                'field' => $field,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Gagal mengupdate data'];
        }
    }
    
    /**
     * Bulk approve registrations
     */
    public static function approveRegistrations($ids) {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Tidak ada pendaftaran yang dipilih'];
        }
        
        try {
            $db = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Get registration names for logging
            $stmt = $db->prepare("SELECT name FROM registrations WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Update status
            $params = array_merge($ids);
            $stmt = $db->prepare("
                UPDATE registrations 
                SET status = 'approved', updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($params);
            
            $updated = $stmt->rowCount();
            
            // Log activity
            ActivityLog::log(
                ActivityLog::ACTION_APPROVE,
                "Bulk approve $updated pendaftaran: " . implode(', ', $names),
                null,
                ['ids' => $ids, 'count' => $updated]
            );
            
            // Clear cache
            Cache::forget('admin_stats');
            Cache::forget('admin_chart_status');
            
            return [
                'success' => true,
                'message' => "$updated pendaftaran berhasil disetujui",
                'count' => $updated
            ];
            
        } catch (Exception $e) {
            Logger::error('Bulk approve failed', [
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Gagal menyetujui pendaftaran'];
        }
    }
    
    /**
     * Bulk reject registrations
     */
    public static function rejectRegistrations($ids) {
        if (empty($ids)) {
            return ['success' => false, 'message' => 'Tidak ada pendaftaran yang dipilih'];
        }
        
        try {
            $db = getDbConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // Get registration names for logging
            $stmt = $db->prepare("SELECT name FROM registrations WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Update status
            $params = array_merge($ids);
            $stmt = $db->prepare("
                UPDATE registrations 
                SET status = 'rejected', updated_at = NOW() 
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($params);
            
            $updated = $stmt->rowCount();
            
            // Log activity
            ActivityLog::log(
                ActivityLog::ACTION_REJECT,
                "Bulk reject $updated pendaftaran: " . implode(', ', $names),
                null,
                ['ids' => $ids, 'count' => $updated]
            );
            
            // Clear cache
            Cache::forget('admin_stats');
            Cache::forget('admin_chart_status');
            
            return [
                'success' => true,
                'message' => "$updated pendaftaran berhasil ditolak",
                'count' => $updated
            ];
            
        } catch (Exception $e) {
            Logger::error('Bulk reject failed', [
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Gagal menolak pendaftaran'];
        }
    }
    
    /**
     * Export to CSV
     */
    public static function exportToCsv($data, $filename, $headers = []) {
        if (empty($data)) {
            return false;
        }
        
        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write headers
        if (empty($headers)) {
            $headers = array_keys($data[0]);
        }
        fputcsv($output, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
