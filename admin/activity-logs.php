<?php
$pageTitle = 'Log Aktivitas';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'action' => $_GET['action'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

// Get current page
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Get paginated data (7 items per page)
$result = ActivityLog::paginate($page, 7, $filters);
$logs = $result['data'];
$paginator = $result['paginator'];

// Get unique actions for filter
$actions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

// Check if AJAX request
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if (!$isAjax) {
    include __DIR__ . '/../includes/admin-header.php';
}
?>

<?php if (!$isAjax): ?>
<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Log Aktivitas Sistem</h2>
            <p style="margin: 0; color: var(--gray-600); font-size: 14px;">Pantau semua aktivitas pengguna dan sistem</p>
        </div>
        <button onclick="exportLogs()" class="nb-btn nb-btn-primary">
            <i class="bi bi-download"></i> Export
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-4 gap-3 mb-4">
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM activity_logs");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Total Log</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--success);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM activity_logs WHERE action IN ('login', 'create', 'approve')");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Success</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--warning);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM activity_logs WHERE action = 'update'");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Update</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--danger);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM activity_logs WHERE action IN ('delete', 'reject')");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Delete/Reject</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="nb-card mb-4">
        <form method="GET" action="">
            <div style="display: grid; grid-template-columns: 1fr auto auto auto auto auto; gap: 12px; align-items: end;">
                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-search"></i> Cari Log
                    </label>
                    <div class="nb-search-box" style="margin: 0;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="nb-input" placeholder="Cari aktivitas, user..." value="<?= e($filters['search']) ?>" style="padding: 10px 12px; font-size: 13px;">
                    </div>
                </div>
                
                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-lightning"></i> Aksi
                    </label>
                    <select name="action" class="nb-input" style="padding: 10px 12px; font-size: 13px; min-width: 130px;">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?= e($action) ?>" <?= $filters['action'] === $action ? 'selected' : '' ?>>
                                <?= ucfirst(e($action)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-calendar"></i> Dari Tanggal
                    </label>
                    <input type="date" name="date_from" class="nb-input" value="<?= e($filters['date_from']) ?>" style="padding: 10px 12px; font-size: 13px;">
                </div>

                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-calendar-check"></i> Sampai Tanggal
                    </label>
                    <input type="date" name="date_to" class="nb-input" value="<?= e($filters['date_to']) ?>" style="padding: 10px 12px; font-size: 13px;">
                </div>
                
                <button type="submit" class="nb-btn nb-btn-primary" style="font-size: 13px; padding: 10px 16px;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                
                <?php if (!empty(array_filter($filters))): ?>
                    <a href="<?= APP_URL ?>/admin/activity-logs" class="nb-btn nb-btn-outline" style="font-size: 13px; padding: 10px 16px;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pagination Info -->
    <div style="margin-bottom: 12px; font-size: 13px; color: var(--gray-600); font-weight: 600;" id="pagination-info">
        <?= $paginator->getInfo() ?>
    </div>
<?php endif; ?>

    <!-- Activity Logs Table -->
    <div class="nb-card" id="logs-container">
        <?php if (count($logs) > 0): ?>
            <div class="nb-table-responsive">
                <table class="nb-table">
                    <thead>
                        <tr style="background: var(--gray-50);">
                            <th style="font-size: 12px; font-weight: 700;">Waktu</th>
                            <th style="font-size: 12px; font-weight: 700;">User</th>
                            <th style="font-size: 12px; font-weight: 700; text-align: center;">Aksi</th>
                            <th style="font-size: 12px; font-weight: 700;">Deskripsi</th>
                            <th style="font-size: 12px; font-weight: 700;">IP Address</th>
                            <th style="font-size: 12px; font-weight: 700; text-align: center;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr style="border-bottom: 1px solid var(--gray-200); transition: background 0.2s;" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='transparent'">
                                <td>
                                    <div style="font-weight: 700; font-size: 13px;"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                    <div style="font-size: 11px; color: var(--gray-600);">
                                        <i class="bi bi-clock"></i> <?= date('H:i:s', strtotime($log['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <div style="font-weight: 700; font-size: 13px;"><?= e($log['user_name']) ?></div>
                                        <div style="font-size: 11px; color: var(--gray-600);">
                                            <i class="bi bi-envelope"></i> <?= e($log['user_email']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400); font-style: italic;">
                                            <i class="bi bi-robot"></i> System
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    $actionColors = [
                                        'login' => 'success',
                                        'logout' => 'info',
                                        'register' => 'primary',
                                        'create' => 'success',
                                        'update' => 'warning',
                                        'delete' => 'danger',
                                        'approve' => 'success',
                                        'reject' => 'danger',
                                    ];
                                    $actionIcons = [
                                        'login' => 'box-arrow-in-right',
                                        'logout' => 'box-arrow-right',
                                        'register' => 'person-plus',
                                        'create' => 'plus-circle',
                                        'update' => 'pencil-square',
                                        'delete' => 'trash',
                                        'approve' => 'check-circle',
                                        'reject' => 'x-circle',
                                    ];
                                    $color = $actionColors[$log['action']] ?? 'secondary';
                                    $icon = $actionIcons[$log['action']] ?? 'circle';
                                    ?>
                                    <span class="nb-badge nb-badge-<?= $color ?>" style="font-size: 11px;">
                                        <i class="bi bi-<?= $icon ?>"></i> <?= ucfirst(e($log['action'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="max-width: 350px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; color: var(--gray-700);">
                                        <?= e($log['description']) ?>
                                    </div>
                                </td>
                                <td>
                                    <code style="font-size: 11px; background: var(--gray-100); padding: 4px 8px; border-radius: 4px; border: 1px solid var(--gray-300);">
                                        <?= e($log['ip_address'] ?? '-') ?>
                                    </code>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($log['metadata']): ?>
                                        <button onclick="showMetadata('<?= e($log['id']) ?>')" class="nb-btn nb-btn-sm nb-btn-outline" style="font-size: 11px; padding: 6px 10px;">
                                            <i class="bi bi-info-circle"></i> Info
                                        </button>
                                        <div id="metadata-<?= e($log['id']) ?>" style="display: none; margin-top: 8px;">
                                            <pre style="font-size: 10px; max-width: 300px; overflow: auto; background: var(--gray-50); padding: 8px; border-radius: 6px; border: 2px solid var(--gray-300); text-align: left;"><?= e(json_encode(json_decode($log['metadata']), JSON_PRETTY_PRINT)) ?></pre>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: var(--gray-400); font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 48px; color: var(--gray-500);">
                <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p style="margin: 0; font-weight: 600;">Tidak ada log aktivitas ditemukan</p>
                <p style="margin: 8px 0 0 0; font-size: 13px;">Coba ubah filter atau cari dengan kriteria lain</p>
            </div>
        <?php endif; ?>
    </div>

<?php if ($isAjax): ?>
    <!-- Pagination Info for AJAX -->
    <div style="margin-bottom: 12px; font-size: 13px; color: var(--gray-600); font-weight: 600;" id="pagination-info">
        <?= $paginator->getInfo() ?>
    </div>
<?php endif; ?>

    <!-- Pagination -->
    <div id="pagination-container">
        <?= $paginator->render(APP_URL . '/admin/activity-logs', $filters, true) ?>
    </div>

<?php if (!$isAjax): ?>
</div>
<?php endif; ?>

<?php if (!$isAjax): ?>
<script>
let currentPage = <?= $page ?>;
const filters = <?= json_encode($filters) ?>;

// Load logs via AJAX
function loadLogs(page) {
    const container = document.getElementById('logs-container');
    const paginationInfo = document.getElementById('pagination-info');
    const paginationContainer = document.getElementById('pagination-container');
    
    // Add loading state
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    
    // Build query string
    const params = new URLSearchParams();
    params.set('page', page);
    params.set('ajax', '1');
    
    // Add filters
    if (filters.search) params.set('search', filters.search);
    if (filters.action) params.set('action', filters.action);
    if (filters.date_from) params.set('date_from', filters.date_from);
    if (filters.date_to) params.set('date_to', filters.date_to);
    
    // Fetch data
    fetch('<?= APP_URL ?>/admin/activity-logs?' + params.toString())
        .then(response => response.text())
        .then(html => {
            // Parse HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update logs container
            const newLogsContainer = doc.getElementById('logs-container');
            if (newLogsContainer) {
                container.innerHTML = newLogsContainer.innerHTML;
            }
            
            // Update pagination info
            const newPaginationInfo = doc.getElementById('pagination-info');
            if (newPaginationInfo) {
                paginationInfo.innerHTML = newPaginationInfo.innerHTML;
            }
            
            // Update pagination
            const newPaginationContainer = doc.getElementById('pagination-container');
            if (newPaginationContainer) {
                paginationContainer.innerHTML = newPaginationContainer.innerHTML;
            }
            
            // Smooth fade in
            container.style.transition = 'opacity 0.3s';
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            
            // Update current page
            currentPage = page;
            
            // Scroll to top of table smoothly
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(error => {
            console.error('Error loading logs:', error);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            alert('Gagal memuat data. Silakan coba lagi.');
        });
}

function showMetadata(id) {
    const metadata = document.getElementById('metadata-' + id);
    if (metadata.style.display === 'none') {
        metadata.style.display = 'block';
    } else {
        metadata.style.display = 'none';
    }
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '<?= APP_URL ?>/admin/export/activity-logs?' + params.toString();
}
</script>
<?php endif; ?>

<?php 
if (!$isAjax) {
    include __DIR__ . '/../includes/footer.php';
}
?>
