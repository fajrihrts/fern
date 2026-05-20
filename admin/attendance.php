<?php
// Start output buffering to catch any unwanted output
ob_start();

$pageTitle = 'Kelola Kehadiran';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// AJAX: Get attendance list for a specific date
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_date_attendance' && isset($_GET['date'])) {
    ob_end_clean(); // Clear and stop output buffering
    $date = $_GET['date'];
    
    $stmt = $db->prepare("
        SELECT a.*, u.name as user_name, u.email
        FROM attendance_reports a
        JOIN users u ON a.user_id = u.id
        WHERE DATE(a.date) = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$date]);
    $reports = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'date' => $date,
        'reports' => $reports
    ]);
    exit;
}

// AJAX: Get attendance detail for a specific report
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_attendance_detail' && isset($_GET['id'])) {
    ob_end_clean(); // Clear and stop output buffering
    
    try {
        $id = $_GET['id'];
        
        // Log the request
        error_log("Fetching attendance detail for ID: " . $id);
        
        $stmt = $db->prepare("
            SELECT a.*, u.name as user_name, u.email
            FROM attendance_reports a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $report = $stmt->fetch();
        
        // Log the result
        error_log("Report found: " . ($report ? 'yes' : 'no'));
        
        header('Content-Type: application/json');
        if ($report) {
            echo json_encode([
                'success' => true,
                'report' => $report
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Report not found'
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        error_log("Error in get_attendance_detail: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Get current month and year from query params or use current
$currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Get attendance data for the current month
$firstDay = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-01";
$lastDay = date('Y-m-t', strtotime($firstDay));

$attendanceByDate = [];
$stmt = $db->prepare("
    SELECT 
        DATE(a.date) as attendance_date,
        COUNT(DISTINCT a.user_id) as total_attendees,
        SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir_count,
        SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin_count,
        SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as sakit_count,
        SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha_count
    FROM attendance_reports a
    WHERE a.date >= ? AND a.date <= ?
    GROUP BY DATE(a.date)
");
$stmt->execute([$firstDay, $lastDay]);
$attendanceData = $stmt->fetchAll();

foreach ($attendanceData as $data) {
    $attendanceByDate[$data['attendance_date']] = $data;
}


// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Token keamanan tidak valid');
        redirect('/admin/attendance');
    }
    
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // Bulk delete
    if ($action === 'bulk_delete' && !empty($_POST['ids'])) {
        $result = BulkAction::delete('attendance_reports', $_POST['ids'], 'Bulk delete attendance reports');
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/attendance');
    }
    
    // Single delete
    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("SELECT photo_proof FROM attendance_reports WHERE id = ?");
        $stmt->execute([$id]);
        $report = $stmt->fetch();
        
        if ($report && $report['photo_proof']) {
            deleteFile($report['photo_proof']);
        }
        
        $stmt = $db->prepare("DELETE FROM attendance_reports WHERE id = ?");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_DELETE, 'Admin menghapus laporan kehadiran', null, ['id' => $id]);
            setFlash('success', 'Laporan berhasil dihapus');
        } else {
            setFlash('danger', 'Gagal menghapus laporan');
        }
        redirect('/admin/attendance');
    }
}


include __DIR__ . '/../includes/admin-header.php';

// Calendar helper functions
function getMonthName($month) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[$month];
}

function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

function getFirstDayOfMonth($month, $year) {
    return (int)date('N', strtotime("$year-$month-01"));
}

$daysInMonth = getDaysInMonth($currentMonth, $currentYear);
$firstDayOfWeek = getFirstDayOfMonth($currentMonth, $currentYear);
$monthName = getMonthName($currentMonth);

// Calculate previous and next month
$prevMonth = $currentMonth - 1;
$prevYear = $currentYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $currentMonth + 1;
$nextYear = $currentYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Kelola Kehadiran</h2>
            <p style="margin: 0; color: var(--gray-600); font-size: 14px;">Kalender kehadiran peserta magang</p>
        </div>
        <a href="<?= APP_URL ?>/admin/export/attendance/csv" class="nb-btn nb-btn-primary">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <!-- Two Column Layout: Stats + Calendar -->
    <div class="attendance-layout">
        <!-- Left Column: Stats + Date Search -->
        <div class="attendance-left">
            <!-- Quick Stats 2x2 Grid -->
            <div class="attendance-stats-grid">
                <div class="nb-card">
                    <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--success);">
                        <?php
                        $stmt = $db->query("SELECT COUNT(*) FROM attendance_reports WHERE status = 'hadir'");
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Hadir</div>
                </div>
                <div class="nb-card">
                    <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--warning);">
                        <?php
                        $stmt = $db->query("SELECT COUNT(*) FROM attendance_reports WHERE status = 'izin'");
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Izin</div>
                </div>
                <div class="nb-card">
                    <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--info);">
                        <?php
                        $stmt = $db->query("SELECT COUNT(*) FROM attendance_reports WHERE status = 'sakit'");
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Sakit</div>
                </div>
                <div class="nb-card">
                    <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--danger);">
                        <?php
                        $stmt = $db->query("SELECT COUNT(*) FROM attendance_reports WHERE status = 'alpha'");
                        echo $stmt->fetchColumn();
                        ?>
                    </div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Alpha</div>
                </div>
            </div>

            <!-- Date Search -->
            <div class="attendance-date-search">
                <form id="dateSearchForm" onsubmit="searchByDate(event)" style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                    <input 
                        type="text" 
                        id="dateSearchInput" 
                        placeholder="dd/mm/yyyy" 
                        value="<?= date('d/m/Y') ?>"
                        style="
                            padding: 10px 16px;
                            border: 3px solid #000;
                            border-radius: 8px;
                            font-size: 14px;
                            font-weight: 600;
                            width: 140px;
                            text-align: center;
                            background: white;
                            font-family: inherit;
                        "
                    >
                    <button 
                        type="submit" 
                        class="nb-btn nb-btn-outline"
                        style="padding: 10px 20px; font-size: 14px;"
                    >cari</button>
                </form>
            </div>
        </div>

        <!-- Right Column: Calendar -->
        <div class="attendance-right">
            <div class="nb-card">
                <!-- Calendar Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 3px solid var(--gray-200);">
                    <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="nb-btn nb-btn-outline">
                        <i class="bi bi-chevron-left"></i> Sebelumnya
                    </a>
                    
                    <h3 style="margin: 0; font-size: 24px; font-weight: 900;">
                        <?= $monthName ?> <?= $currentYear ?>
                    </h3>
                    
                    <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="nb-btn nb-btn-outline">
                        Selanjutnya <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-grid">
                    <!-- Day Headers -->
                    <?php
                    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    foreach ($dayNames as $dayName):
                    ?>
                        <div class="calendar-header-day">
                            <?= $dayName ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Empty cells before first day -->
                    <?php for ($i = 1; $i < $firstDayOfWeek; $i++): ?>
                        <div class="calendar-day-empty"></div>
                    <?php endfor; ?>

                    <!-- Calendar days -->
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): 
                        $dateStr = sprintf("%04d-%02d-%02d", $currentYear, $currentMonth, $day);
                        $hasAttendance = isset($attendanceByDate[$dateStr]);
                        $attendanceInfo = $hasAttendance ? $attendanceByDate[$dateStr] : null;
                        $isToday = $dateStr === date('Y-m-d');
                    ?>
                        <div 
                            class="calendar-day <?= $hasAttendance ? 'has-attendance' : '' ?> <?= $isToday ? 'is-today' : '' ?>"
                            data-date="<?= $dateStr ?>"
                            onclick="<?= $hasAttendance ? "showDateAttendance('$dateStr')" : '' ?>"
                        >
                            <div class="calendar-day-number">
                                <?= $day ?>
                            </div>
                            
                            <?php if ($hasAttendance): ?>
                                <div class="calendar-day-badge">
                                    <i class="bi bi-people-fill"></i> <?= $attendanceInfo['total_attendees'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Legend -->
                <div style="margin-top: 24px; padding-top: 16px; border-top: 3px solid var(--gray-200); display: flex; gap: 24px; flex-wrap: wrap; font-size: 13px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 24px; height: 24px; border: 3px solid var(--primary); border-radius: 6px; background: var(--primary-light);"></div>
                        <span style="font-weight: 600;">Hari Ini</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 24px; height: 24px; border: 3px solid var(--success); border-radius: 6px; background: var(--success-light);"></div>
                        <span style="font-weight: 600;">Ada Kehadiran</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 24px; height: 24px; border: 3px solid var(--gray-200); border-radius: 6px; background: white;"></div>
                        <span style="font-weight: 600;">Tidak Ada Kehadiran</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Attendance Modal -->
<div id="dateAttendanceModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    padding: 20px;
    overflow: auto;
" onclick="closeDateModal()">
    <div style="
        background: white;
        border: 3px solid #000;
        border-radius: 12px;
        max-width: 800px;
        margin: 40px auto;
        box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.2);
        animation: modalSlideIn 0.2s ease-out;
    " onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div style="padding: 24px; border-bottom: 3px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modalDateTitle" style="margin: 0; font-size: 20px; font-weight: 900;">Kehadiran Tanggal</h3>
            <button onclick="closeDateModal()" style="
                width: 36px;
                height: 36px;
                border: 2px solid #000;
                border-radius: 50%;
                background: white;
                cursor: pointer;
                font-size: 20px;
                font-weight: bold;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            " onmouseover="this.style.background='var(--danger)'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='black';">
                ×
            </button>
        </div>

        <!-- Modal Body -->
        <div id="modalAttendanceList" style="padding: 24px;">
            <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                <i class="bi bi-hourglass-split" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Detail Modal -->
<div id="attendanceDetailModal" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 10000;
    padding: 20px;
    overflow: auto;
" onclick="closeDetailModal()">
    <div style="
        background: white;
        border: 3px solid #000;
        border-radius: 12px;
        max-width: 600px;
        margin: 40px auto;
        box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.2);
        animation: modalSlideIn 0.2s ease-out;
    " onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div style="padding: 24px; border-bottom: 3px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 20px; font-weight: 900;">Detail Kehadiran</h3>
            <button onclick="closeDetailModal()" style="
                width: 36px;
                height: 36px;
                border: 2px solid #000;
                border-radius: 50%;
                background: white;
                cursor: pointer;
                font-size: 20px;
                font-weight: bold;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            " onmouseover="this.style.background='var(--danger)'; this.style.color='white';" onmouseout="this.style.background='white'; this.style.color='black';">
                ×
            </button>
        </div>

        <!-- Modal Body -->
        <div id="modalDetailContent" style="padding: 24px;">
            <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                <i class="bi bi-hourglass-split" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-light: #FEF3C7;
    --success-light: #D1FAE5;
}

/* Two-column attendance layout */
.attendance-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 20px;
    align-items: start;
}

.attendance-left {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.attendance-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.attendance-date-search {
    display: flex;
    justify-content: center;
    padding-top: 8px;
}

.attendance-right {
    min-width: 0;
}

/* Calendar Grid - Optimized for no scroll */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 6px;
}

/* Calendar Header Days */
.calendar-header-day {
    text-align: center;
    font-weight: 700;
    font-size: 12px;
    color: var(--gray-600);
    padding: 8px 0;
    border-bottom: 2px solid var(--gray-200);
}

/* Empty Calendar Cells */
.calendar-day-empty {
    min-height: 60px;
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: 8px;
}

/* Calendar Day Cell - Optimized height */
.calendar-day {
    min-height: 60px;
    max-height: 80px;
    border: 3px solid var(--gray-200);
    border-radius: 10px;
    padding: 8px;
    cursor: default;
    background: white;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.calendar-day.has-attendance {
    border-color: var(--success);
    background: var(--success-light);
    cursor: pointer;
}

.calendar-day.is-today {
    border-color: var(--primary);
    background: var(--primary-light);
}

.calendar-day.has-attendance:hover {
    transform: translateY(-2px);
    box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.15);
}

.calendar-day-number {
    font-weight: 900;
    font-size: 15px;
    color: var(--gray-800);
}

.calendar-day.is-today .calendar-day-number {
    color: var(--primary);
}

.calendar-day-badge {
    font-size: 10px;
    font-weight: 700;
    color: var(--success);
    text-align: center;
    margin-top: 4px;
}

/* Responsive: stack on smaller screens */
@media (max-width: 1200px) {
    .attendance-layout {
        grid-template-columns: 280px 1fr;
        gap: 16px;
    }
    
    .calendar-day {
        min-height: 50px;
        max-height: 70px;
        padding: 6px;
    }
    
    .calendar-day-number {
        font-size: 14px;
    }
    
    .calendar-day-badge {
        font-size: 9px;
    }
}

@media (max-width: 960px) {
    .attendance-layout {
        grid-template-columns: 1fr;
    }
    
    .attendance-stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .calendar-day {
        min-height: 70px;
        max-height: 90px;
    }
}

@media (max-width: 640px) {
    .attendance-stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .calendar-grid {
        gap: 4px;
    }
    
    .calendar-day {
        min-height: 50px;
        max-height: 65px;
        padding: 4px;
    }
    
    .calendar-header-day {
        font-size: 11px;
        padding: 6px 0;
    }
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes highlightPulse {
    0%, 100% {
        outline-color: var(--primary);
        box-shadow: 0 0 0 0 rgba(255, 209, 0, 0);
    }
    50% {
        outline-color: var(--warning);
        box-shadow: 0 0 12px 4px rgba(255, 209, 0, 0.4);
    }
}
</style>

<script>
// Search attendance by manually entered date (dd/mm/yyyy)
function searchByDate(event) {
    event.preventDefault();
    const input = document.getElementById('dateSearchInput').value.trim();
    
    // Parse dd/mm/yyyy format
    const parts = input.split('/');
    if (parts.length !== 3) {
        alert('Format tanggal tidak valid. Gunakan format dd/mm/yyyy');
        return;
    }
    
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const year = parseInt(parts[2], 10);
    
    if (isNaN(day) || isNaN(month) || isNaN(year) || month < 1 || month > 12 || day < 1 || day > 31) {
        alert('Tanggal tidak valid. Gunakan format dd/mm/yyyy');
        return;
    }
    
    // Navigate to the month/year of the searched date
    window.location.href = '?month=' + month + '&year=' + year + '&highlight=' + day;
}

// Highlight searched date on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const highlightDay = urlParams.get('highlight');
    if (highlightDay) {
        const year = urlParams.get('year');
        const month = urlParams.get('month');
        if (year && month) {
            const dateStr = year + '-' + month.padStart(2, '0') + '-' + highlightDay.padStart(2, '0');
            const dayEl = document.querySelector('.calendar-day[data-date="' + dateStr + '"]');
            if (dayEl) {
                dayEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                dayEl.style.outline = '4px solid var(--primary)';
                dayEl.style.outlineOffset = '2px';
                dayEl.style.animation = 'highlightPulse 1.5s ease-in-out 3';
                
                // If it has attendance, auto-open modal
                if (dayEl.classList.contains('has-attendance')) {
                    setTimeout(function() {
                        showDateAttendance(dateStr);
                    }, 600);
                }
            }
        }
    }
});

// Show attendance list for a specific date
function showDateAttendance(date) {
    const modal = document.getElementById('dateAttendanceModal');
    const titleEl = document.getElementById('modalDateTitle');
    const listEl = document.getElementById('modalAttendanceList');
    
    // Format date for display
    const dateObj = new Date(date + 'T00:00:00');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formattedDate = dateObj.toLocaleDateString('id-ID', options);
    
    titleEl.textContent = 'Kehadiran ' + formattedDate;
    
    // Show loading
    listEl.innerHTML = `
        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
            <i class="bi bi-hourglass-split" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Memuat data...</p>
        </div>
    `;
    
    modal.style.display = 'block';
    
    // Fetch attendance data
    fetch('<?= APP_URL ?>/admin/attendance?ajax=get_date_attendance&date=' + date)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.reports.length > 0) {
                let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
                
                data.reports.forEach(report => {
                    const statusColors = {
                        'hadir': 'var(--success)',
                        'izin': 'var(--warning)',
                        'sakit': 'var(--info)',
                        'alpha': 'var(--danger)'
                    };
                    const statusColor = statusColors[report.status] || 'var(--gray-500)';
                    
                    html += `
                        <div style="
                            padding: 16px;
                            border: 2px solid var(--gray-200);
                            border-radius: 8px;
                            cursor: pointer;
                            transition: all 0.2s;
                            background: white;
                        " 
                        onclick="showAttendanceDetail('${report.id}')"
                        onmouseover="this.style.borderColor='var(--primary)'; this.style.transform='translateX(4px)';"
                        onmouseout="this.style.borderColor='var(--gray-200)'; this.style.transform='translateX(0)';">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">${report.user_name}</div>
                                    <div style="font-size: 12px; color: var(--gray-600);">${report.email}</div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <span style="
                                        padding: 6px 12px;
                                        border: 2px solid ${statusColor};
                                        border-radius: 6px;
                                        background: ${statusColor}20;
                                        color: ${statusColor};
                                        font-weight: 700;
                                        font-size: 12px;
                                        text-transform: uppercase;
                                    ">${report.status}</span>
                                    <i class="bi bi-chevron-right" style="color: var(--gray-400);"></i>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                listEl.innerHTML = html;
            } else {
                listEl.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: var(--gray-500);">
                        <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p>Tidak ada data kehadiran</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            listEl.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--danger);">
                    <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Gagal memuat data</p>
                </div>
            `;
        });
}

// Show attendance detail
function showAttendanceDetail(id) {
    const modal = document.getElementById('attendanceDetailModal');
    const contentEl = document.getElementById('modalDetailContent');
    
    // Show loading
    contentEl.innerHTML = `
        <div style="text-align: center; padding: 40px; color: var(--gray-500);">
            <i class="bi bi-hourglass-split" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Memuat detail...</p>
        </div>
    `;
    
    modal.style.display = 'block';
    
    // Fetch detail data
    fetch('<?= APP_URL ?>/admin/attendance?ajax=get_attendance_detail&id=' + id)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                
                if (data.success) {
                    const report = data.report;
                    const statusColors = {
                        'hadir': 'var(--success)',
                        'izin': 'var(--warning)',
                        'sakit': 'var(--info)',
                        'alpha': 'var(--danger)'
                    };
                    const statusColor = statusColors[report.status] || 'var(--gray-500)';
                    
                    // Format date
                    const dateObj = new Date(report.date + 'T00:00:00');
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const formattedDate = dateObj.toLocaleDateString('id-ID', options);
                    
                    let html = `
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <!-- User Info -->
                            <div style="padding: 16px; background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: 8px;">
                                <div style="font-weight: 900; font-size: 18px; margin-bottom: 8px;">${report.user_name}</div>
                                <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 4px;">
                                    <i class="bi bi-envelope"></i> ${report.email}
                                </div>
                                ${report.phone ? `
                                    <div style="font-size: 13px; color: var(--gray-600);">
                                        <i class="bi bi-telephone"></i> ${report.phone}
                                    </div>
                                ` : ''}
                            </div>

                            <!-- Attendance Info -->
                            <div>
                                <div style="display: grid; grid-template-columns: 140px 1fr; gap: 12px; font-size: 14px;">
                                    <div style="font-weight: 700; color: var(--gray-600);">Tanggal:</div>
                                    <div style="font-weight: 600;">${formattedDate}</div>

                                    <div style="font-weight: 700; color: var(--gray-600);">Status:</div>
                                    <div>
                                        <span style="
                                            padding: 6px 12px;
                                            border: 2px solid ${statusColor};
                                            border-radius: 6px;
                                            background: ${statusColor}20;
                                            color: ${statusColor};
                                            font-weight: 700;
                                            font-size: 12px;
                                            text-transform: uppercase;
                                            display: inline-block;
                                        ">${report.status}</span>
                                    </div>

                                    <div style="font-weight: 700; color: var(--gray-600);">Waktu Submit:</div>
                                    <div style="font-weight: 600;">${new Date(report.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}</div>

                                    <div style="font-weight: 700; color: var(--gray-600);">Aktivitas:</div>
                                    <div style="font-weight: 600;">${report.activities || '-'}</div>
                                </div>
                            </div>

                            <!-- Photo -->
                            ${report.photo_proof ? `
                                <div>
                                    <div style="font-weight: 700; color: var(--gray-600); margin-bottom: 8px; font-size: 14px;">Foto Bukti:</div>
                                    <img src="<?= APP_URL ?>/uploads/${report.photo_proof}" 
                                         style="width: 100%; border: 3px solid #000; border-radius: 12px; cursor: pointer;"
                                         onclick="window.open(this.src, '_blank')">
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    contentEl.innerHTML = html;
                } else {
                    contentEl.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: var(--danger);">
                            <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                            <p>Data tidak ditemukan</p>
                            <p style="font-size: 12px; margin-top: 8px;">${data.error || ''}</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                contentEl.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: var(--danger);">
                        <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                        <p>Gagal memuat detail</p>
                        <p style="font-size: 12px; margin-top: 8px;">Response tidak valid</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            contentEl.innerHTML = `
                <div style="text-align: center; padding: 40px; color: var(--danger);">
                    <i class="bi bi-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Gagal memuat detail</p>
                    <p style="font-size: 12px; margin-top: 8px;">${error.message}</p>
                </div>
            `;
        });
}

// Close modals
function closeDateModal() {
    document.getElementById('dateAttendanceModal').style.display = 'none';
}

function closeDetailModal() {
    document.getElementById('attendanceDetailModal').style.display = 'none';
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDateModal();
        closeDetailModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
