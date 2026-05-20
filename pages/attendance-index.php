<?php
$pageTitle = 'Laporan Kehadiran';
$user = auth();

// Check if user has approved registration
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = ? AND status = 'approved'");
$stmt->execute([$user['id']]);
$registration = $stmt->fetch();

if (!$registration) {
    redirect('/dashboard');
}

// Get filter
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

// Get attendance reports for the month
$stmt = $db->prepare("
    SELECT * FROM attendance_reports 
    WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
    ORDER BY date ASC
");
$stmt->execute([$user['id'], $month, $year]);
$reports = $stmt->fetchAll();

// Create attendance map by date
$attendanceMap = [];
foreach ($reports as $report) {
    $day = (int)date('j', strtotime($report['date']));
    $attendanceMap[$day] = $report;
}

// Calculate statistics
$stats = [
    'hadir' => 0,
    'izin' => 0,
    'sakit' => 0,
    'alpha' => 0
];

foreach ($reports as $report) {
    $stats[$report['status']]++;
}

// Check if can create today's report
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT id FROM attendance_reports WHERE user_id = ? AND date = ?");
$stmt->execute([$user['id'], $today]);
$canCreateToday = !$stmt->fetch() && $registration['internship_status'] === 'ongoing';

// Calendar calculations
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay); // 0 (Sunday) to 6 (Saturday)
$monthName = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$month];

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div style="margin-bottom: 32px;">
            <h2 style="margin-bottom: 8px;">Kalender Laporan Harian</h2>
            <p style="color: var(--gray-600); margin: 0;">Kelola laporan kehadiran harian Anda</p>
        </div>

        <!-- Calendar Card -->
        <div class="nb-card">
            <!-- Calendar Header with Month Navigation -->
            <div style="display: flex; justify-content: center; align-items: center; gap: 16px; margin-bottom: 24px;">
                <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" style="width: 32px; height: 32px; border: 2px solid var(--gray-300); border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--black); transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <h4 style="margin: 0; min-width: 120px; text-align: center; font-size: 16px;"><?= $monthName ?> <?= $year ?></h4>
                <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" style="width: 32px; height: 32px; border: 2px solid var(--gray-300); border-radius: 6px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: var(--black); transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>

            <!-- Calendar Grid -->
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                    <!-- Day Headers -->
                    <thead>
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Sen</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Sel</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Rab</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Kam</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Jum</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Sab</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; font-size: 13px; color: var(--gray-600);">Min</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentDay = 1;
                        $todayDay = (int)date('j');
                        $todayMonth = (int)date('n');
                        $todayYear = (int)date('Y');
                        
                        // Calculate weeks needed
                        $weeksNeeded = ceil(($daysInMonth + $dayOfWeek) / 7);
                        
                        for ($week = 0; $week < $weeksNeeded; $week++):
                        ?>
                            <tr style="border-bottom: 1px solid var(--gray-100);">
                                <?php for ($dayIndex = 0; $dayIndex < 7; $dayIndex++): ?>
                                    <?php
                                    // Adjust for Monday start (0 = Monday, 6 = Sunday)
                                    $adjustedDayOfWeek = ($dayOfWeek == 0) ? 6 : $dayOfWeek - 1;
                                    
                                    if (($week == 0 && $dayIndex < $adjustedDayOfWeek) || $currentDay > $daysInMonth):
                                        // Empty cell
                                        echo '<td style="padding: 14px 8px; background: var(--gray-50);"></td>';
                                    else:
                                        $isToday = ($currentDay == $todayDay && $month == $todayMonth && $year == $todayYear);
                                        $hasAttendance = isset($attendanceMap[$currentDay]);
                                        $attendance = $hasAttendance ? $attendanceMap[$currentDay] : null;
                                        
                                        // Determine status color
                                        $statusColor = '#e5e7eb'; // default gray
                                        if ($hasAttendance) {
                                            switch ($attendance['status']) {
                                                case 'hadir':
                                                    $statusColor = '#00FF88'; // green
                                                    break;
                                                case 'izin':
                                                    $statusColor = '#FFB300'; // yellow
                                                    break;
                                                case 'sakit':
                                                    $statusColor = '#64B5F6'; // blue
                                                    break;
                                                case 'alpha':
                                                    $statusColor = '#FF5252'; // red
                                                    break;
                                            }
                                        }
                                        
                                        $cellStyle = 'padding: 14px 8px; text-align: center; vertical-align: middle; position: relative; cursor: pointer; transition: background 0.2s;';
                                        if ($isToday) {
                                            $cellStyle .= ' background: #e0f2fe;';
                                        }
                                    ?>
                                        <td style="<?= $cellStyle ?>" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='<?= $isToday ? '#e0f2fe' : 'transparent' ?>'">
                                            <div style="position: relative; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                                <!-- Day Number -->
                                                <div style="font-weight: 600; font-size: 14px; <?= $isToday ? 'color: #0284c7;' : 'color: var(--gray-700);' ?>">
                                                    <?= $currentDay ?>
                                                </div>
                                                
                                                <!-- Status Indicator -->
                                                <?php if ($hasAttendance): ?>
                                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: <?= $statusColor ?>;"></div>
                                                <?php else: ?>
                                                    <div style="width: 8px; height: 8px; border-radius: 50%; border: 1px solid var(--gray-300);"></div>
                                                <?php endif; ?>
                                                
                                                <!-- Today Badge -->
                                                <?php if ($isToday): ?>
                                                    <div style="position: absolute; top: -6px; right: -6px; width: 18px; height: 18px; background: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: white;">
                                                        <?= $currentDay ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Click to view/create -->
                                                <?php if ($hasAttendance): ?>
                                                    <a href="<?= APP_URL ?>/laporan/<?= sprintf('%04d-%02d-%02d', $year, $month, $currentDay) ?>" style="position: absolute; inset: -14px -8px; z-index: 1;"></a>
                                                <?php elseif ($isToday && $canCreateToday): ?>
                                                    <a href="<?= APP_URL ?>/laporan/create" style="position: absolute; inset: -14px -8px; z-index: 1;"></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php
                                        $currentDay++;
                                    endif;
                                    ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="nb-card mt-4">
            <h5 style="margin-bottom: 16px;">Keterangan:</h5>
            <div style="display: flex; flex-wrap: wrap; gap: 24px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: #00FF88; border: 2px solid #000;"></div>
                    <span style="font-weight: 600; font-size: 14px;">Hadir</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: #FFB300; border: 2px solid #000;"></div>
                    <span style="font-weight: 600; font-size: 14px;">Izin</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: #64B5F6; border: 2px solid #000;"></div>
                    <span style="font-weight: 600; font-size: 14px;">Sakit</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: #FF5252; border: 2px solid #000;"></div>
                    <span style="font-weight: 600; font-size: 14px;">Alpha</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--gray-300);"></div>
                    <span style="font-weight: 600; font-size: 14px;">Belum Lapor</span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-4 gap-3 mt-4">
            <div class="nb-card" style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 900; color: var(--success); margin-bottom: 8px;">
                    <?= $stats['hadir'] ?>
                </div>
                <div style="font-weight: 700; font-size: 14px;">Hadir</div>
            </div>
            <div class="nb-card" style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 900; color: var(--warning); margin-bottom: 8px;">
                    <?= $stats['izin'] ?>
                </div>
                <div style="font-weight: 700; font-size: 14px;">Izin</div>
            </div>
            <div class="nb-card" style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 900; color: var(--info); margin-bottom: 8px;">
                    <?= $stats['sakit'] ?>
                </div>
                <div style="font-weight: 700; font-size: 14px;">Sakit</div>
            </div>
            <div class="nb-card" style="text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 900; color: var(--danger); margin-bottom: 8px;">
                    <?= $stats['alpha'] ?>
                </div>
                <div style="font-weight: 700; font-size: 14px;">Alpha</div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
