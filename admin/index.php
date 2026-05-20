<?php
$pageTitle = 'Dashboard Admin';

// Ensure all classes are loaded
if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

// Get current user and path
$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Get statistics
$stats = [
    'total_registrations' => $db->query("SELECT COUNT(*) as count FROM registrations")->fetch()['count'],
    'pending' => $db->query("SELECT COUNT(*) as count FROM registrations WHERE status = 'pending'")->fetch()['count'],
    'ongoing' => $db->query("SELECT COUNT(*) as count FROM registrations WHERE internship_status = 'ongoing'")->fetch()['count'],
    'completed' => $db->query("SELECT COUNT(*) as count FROM registrations WHERE internship_status = 'completed'")->fetch()['count'],
    'attendance_month' => $db->query("SELECT COUNT(*) as count FROM attendance_reports WHERE MONTH(date) = MONTH(CURRENT_DATE) AND YEAR(date) = YEAR(CURRENT_DATE)")->fetch()['count'],
    'posts' => $db->query("SELECT COUNT(*) as count FROM posts")->fetch()['count'],
    'testimonials' => $db->query("SELECT COUNT(*) as count FROM testimonials")->fetch()['count'],
];

// Get chart data (last 6 months registrations)
$chartData = Cache::remember('admin_chart_registrations', 3600, function() {
    return ChartHelper::getRegistrationsByMonth(6);
});

$chartDataJson = json_encode($chartData);

// Get status distribution
$statusData = Cache::remember('admin_chart_status', 3600, function() {
    return ChartHelper::getRegistrationsByStatus();
});

$statusDataJson = json_encode($statusData);

// Recent registrations
$recentRegs = $db->query("SELECT r.*, u.email FROM registrations r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();

// Recent attendance
$recentAttendance = $db->query("SELECT a.*, u.name FROM attendance_reports a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Dashboard Admin</h2>

    <!-- Stats Grid -->
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="nb-card">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                <div style="width: 48px; height: 48px; background: var(--primary); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <span class="nb-badge nb-badge-warning"><?= $stats['pending'] ?> Pending</span>
            </div>
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;"><?= $stats['total_registrations'] ?></div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Total Pendaftaran</div>
        </div>

        <div class="nb-card">
            <div style="width: 48px; height: 48px; background: var(--info); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                <i class="bi bi-person-workspace"></i>
            </div>
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;"><?= $stats['ongoing'] ?></div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Sedang Magang</div>
        </div>

        <div class="nb-card">
            <div style="width: 48px; height: 48px; background: var(--success); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;"><?= $stats['attendance_month'] ?></div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Laporan Bulan Ini</div>
        </div>

        <div class="nb-card">
            <div style="width: 48px; height: 48px; background: var(--accent); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                <i class="bi bi-check-circle"></i>
            </div>
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;"><?= $stats['completed'] ?></div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Selesai Magang</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="nb-card mb-4">
        <h4 class="mb-3">Aksi Cepat</h4>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-primary">
                <i class="bi bi-file-earmark-text"></i> Kelola Pendaftaran
            </a>
            <a href="<?= APP_URL ?>/admin/attendance" class="nb-btn nb-btn-accent">
                <i class="bi bi-calendar-check"></i> Kelola Kehadiran
            </a>
            <a href="<?= APP_URL ?>/admin/posts" class="nb-btn nb-btn-success">
                <i class="bi bi-newspaper"></i> Kelola Berita
            </a>
            <a href="<?= APP_URL ?>/admin/testimonials" class="nb-btn nb-btn-warning">
                <i class="bi bi-chat-quote"></i> Kelola Testimoni
            </a>
            <?php if (($user['role'] ?? '') === 'super_admin'): ?>
                <a href="<?= APP_URL ?>/admin/users" class="nb-btn nb-btn-outline">
                    <i class="bi bi-people"></i> Kelola Admin
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <!-- Chart: Registrations by Month -->
        <div class="nb-card">
            <h4 class="mb-3">Pendaftaran 6 Bulan Terakhir</h4>
            <canvas id="registrationsChart" style="max-height: 300px;"></canvas>
        </div>
        
        <!-- Chart: Status Distribution -->
        <div class="nb-card">
            <h4 class="mb-3">Distribusi Status Pendaftaran</h4>
            <canvas id="statusChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
    
    <div class="grid grid-cols-2 gap-3 mt-3">
        <!-- Recent Registrations -->
        <div class="nb-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4>Pendaftaran Terbaru</h4>
                <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-outline nb-btn-sm">Lihat Semua</a>
            </div>
            <?php if (count($recentRegs) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($recentRegs as $reg): ?>
                        <div style="padding: 12px; border: 2px solid var(--gray-200); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                                <div style="font-weight: 700;"><?= e($reg['name']) ?></div>
                                <?= getStatusBadge($reg['status']) ?>
                            </div>
                            <div style="font-size: 12px; color: var(--gray-600);">
                                <?= e($reg['university']) ?> • <?= formatDateIndo($reg['created_at']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--gray-500); text-align: center; padding: 20px;">Belum ada pendaftaran</p>
            <?php endif; ?>
        </div>

        <!-- Recent Attendance -->
        <div class="nb-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4>Laporan Kehadiran Terbaru</h4>
                <a href="<?= APP_URL ?>/admin/attendance" class="nb-btn nb-btn-outline nb-btn-sm">Lihat Semua</a>
            </div>
            <?php if (count($recentAttendance) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($recentAttendance as $att): ?>
                        <div style="padding: 12px; border: 2px solid var(--gray-200); border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 4px;">
                                <div style="font-weight: 700;"><?= e($att['name']) ?></div>
                                <?= getAttendanceStatusBadge($att['status']) ?>
                            </div>
                            <div style="font-size: 12px; color: var(--gray-600);">
                                <?= formatDateIndo($att['date']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--gray-500); text-align: center; padding: 20px;">Belum ada laporan</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Registrations Chart
const regData = <?= $chartDataJson ?>;
const regCtx = document.getElementById('registrationsChart').getContext('2d');
new Chart(regCtx, {
    type: 'line',
    data: {
        labels: regData.map(d => d.month),
        datasets: [{
            label: 'Pendaftaran',
            data: regData.map(d => d.count),
            borderColor: '#FFEB3B',
            backgroundColor: 'rgba(255, 235, 59, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Status Chart
const statusData = <?= $statusDataJson ?>;
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusData.map(d => d.status),
        datasets: [{
            data: statusData.map(d => d.count),
            backgroundColor: ['#FF9800', '#4CAF50', '#FF5722'],
            borderColor: '#000',
            borderWidth: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
