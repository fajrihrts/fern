<?php
$user = auth();
$db = getDbConnection();

$path = getCurrentPath();

// Export Registrations CSV
if ($path === '/admin/export/registrations/csv') {
    $statusFilter = $_GET['status'] ?? '';
    $internshipFilter = $_GET['internship'] ?? '';
    
    $query = "SELECT r.*, u.email FROM registrations r JOIN users u ON r.user_id = u.id WHERE 1=1";
    $params = [];
    
    if ($statusFilter) {
        $query .= " AND r.status = ?";
        $params[] = $statusFilter;
    }
    if ($internshipFilter) {
        $query .= " AND r.internship_status = ?";
        $params[] = $internshipFilter;
    }
    
    $query .= " ORDER BY r.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Log activity
    ActivityLog::log(
        ActivityLog::ACTION_EXPORT,
        "Admin mengekspor data pendaftaran ke CSV",
        null,
        ['total_records' => count($data), 'filters' => ['status' => $statusFilter, 'internship' => $internshipFilter]]
    );
    
    // Set headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="pendaftaran_' . date('Y-m-d_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, [
        'Tanggal Daftar', 'Nama', 'Email', 'Telepon', 'Universitas', 'Program Studi',
        'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Status Magang', 'Catatan Admin'
    ], ',', '"', '\\');
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, [
            formatDateIndo($row['created_at']),
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['university'],
            $row['major'],
            formatDateIndo($row['start_date']),
            formatDateIndo($row['end_date']),
            $row['status'],
            $row['internship_status'],
            $row['admin_notes'] ?? ''
        ], ',', '"', '\\');
    }
    
    fclose($output);
    exit;
}

// Export Registrations Excel (XLSX)
if ($path === '/admin/export/registrations/excel') {
    $statusFilter = $_GET['status'] ?? '';
    $internshipFilter = $_GET['internship'] ?? '';
    
    $query = "SELECT r.*, u.email FROM registrations r JOIN users u ON r.user_id = u.id WHERE 1=1";
    $params = [];
    
    if ($statusFilter) {
        $query .= " AND r.status = ?";
        $params[] = $statusFilter;
    }
    if ($internshipFilter) {
        $query .= " AND r.internship_status = ?";
        $params[] = $internshipFilter;
    }
    
    $query .= " ORDER BY r.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Log activity
    ActivityLog::log(
        ActivityLog::ACTION_EXPORT,
        "Admin mengekspor data pendaftaran ke Excel",
        null,
        ['total_records' => count($data), 'filters' => ['status' => $statusFilter, 'internship' => $internshipFilter]]
    );
    
    // Create Excel file using simple XML
    $filename = 'pendaftaran_' . date('Y-m-d_His') . '.xlsx';
    
    // Set headers
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create simple XLSX using SpreadsheetML
    require_once __DIR__ . '/../classes/ExcelExporter.php';
    $exporter = new ExcelExporter();
    
    $exporter->addRow([
        'Tanggal Daftar', 'Nama', 'Email', 'Telepon', 'Universitas', 'Program Studi',
        'Tanggal Mulai', 'Tanggal Selesai', 'Status', 'Status Magang', 'Catatan Admin'
    ], true);
    
    foreach ($data as $row) {
        $exporter->addRow([
            formatDateIndo($row['created_at']),
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['university'],
            $row['major'],
            formatDateIndo($row['start_date']),
            formatDateIndo($row['end_date']),
            $row['status'],
            $row['internship_status'],
            $row['admin_notes'] ?? ''
        ]);
    }
    
    $exporter->output();
    exit;
}

// Export Attendance CSV
if ($path === '/admin/export/attendance/csv') {
    $startDate = $_GET['start_date'] ?? '';
    $endDate = $_GET['end_date'] ?? '';
    $regId = $_GET['registration_id'] ?? '';
    
    $query = "SELECT a.*, u.name, u.email FROM attendance_reports a JOIN users u ON a.user_id = u.id WHERE 1=1";
    $params = [];
    
    if ($startDate) {
        $query .= " AND a.date >= ?";
        $params[] = $startDate;
    }
    if ($endDate) {
        $query .= " AND a.date <= ?";
        $params[] = $endDate;
    }
    if ($regId) {
        $query .= " AND a.registration_id = ?";
        $params[] = $regId;
    }
    
    $query .= " ORDER BY a.date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
    
    // Log activity
    ActivityLog::log(
        ActivityLog::ACTION_EXPORT,
        "Admin mengekspor data kehadiran ke CSV",
        null,
        ['total_records' => count($data), 'filters' => ['start_date' => $startDate, 'end_date' => $endDate]]
    );
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="kehadiran_' . date('Y-m-d_His') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Tanggal', 'Nama', 'Email', 'Status', 'Jam Masuk', 'Jam Keluar',
        'Aktivitas', 'Pembelajaran', 'Kendala', 'Konfirmasi', 'Catatan'
    ], ',', '"', '\\');
    
    foreach ($data as $row) {
        fputcsv($output, [
            formatDateIndo($row['date']),
            $row['name'],
            $row['email'],
            $row['status'],
            $row['check_in'] ?? '',
            $row['check_out'] ?? '',
            $row['activities'] ?? '',
            $row['learning'] ?? '',
            $row['obstacles'] ?? '',
            $row['is_confirmed'] ? 'Ya' : 'Tidak',
            $row['notes'] ?? ''
        ], ',', '"', '\\');
    }
    
    fclose($output);
    exit;
}

// Invalid export type
http_response_code(404);
echo "Export type not found";
