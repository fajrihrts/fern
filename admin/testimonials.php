<?php
$pageTitle = 'Kelola Testimoni';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Token keamanan tidak valid');
        redirect('/admin/testimonials');
    }
    
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // Publish
    if ($action === 'publish' && $id) {
        $stmt = $db->prepare("UPDATE testimonials SET is_published = 1, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_UPDATE, 'Admin mempublikasikan testimoni', null, ['id' => $id]);
            setFlash('success', 'Testimoni dipublikasikan');
        }
        redirect('/admin/testimonials');
    }
    
    // Unpublish
    if ($action === 'unpublish' && $id) {
        $stmt = $db->prepare("UPDATE testimonials SET is_published = 0, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_UPDATE, 'Admin menyembunyikan testimoni', null, ['id' => $id]);
            setFlash('success', 'Testimoni disembunyikan');
        }
        redirect('/admin/testimonials');
    }
    
    // Delete
    if ($action === 'delete' && $id) {
        $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
        if ($stmt->execute([$id])) {
            ActivityLog::log(ActivityLog::ACTION_DELETE, 'Admin menghapus testimoni', null, ['id' => $id]);
            setFlash('success', 'Testimoni dihapus');
        }
        redirect('/admin/testimonials');
    }
    
    // Bulk delete
    if ($action === 'bulk_delete' && !empty($_POST['ids'])) {
        $result = BulkAction::delete('testimonials', $_POST['ids'], 'Bulk delete testimonials');
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/testimonials');
    }
}

// Get filters
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Build WHERE clause
$where = [];
$params = [];

if ($filters['search']) {
    $where[] = "(t.name LIKE ? OR t.content LIKE ? OR t.text LIKE ?)";
    $search = '%' . $filters['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

if ($filters['status']) {
    if ($filters['status'] === 'published') {
        $where[] = "t.is_published = 1";
    } elseif ($filters['status'] === 'draft') {
        $where[] = "t.is_published = 0";
    }
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM testimonials t LEFT JOIN users u ON t.user_id = u.id $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Create paginator
$paginator = new Paginator($total, 20, $page);

// Get data
$query = "
    SELECT t.*, u.name as user_name, u.email
    FROM testimonials t
    LEFT JOIN users u ON t.user_id = u.id
    $whereClause
    ORDER BY t.created_at DESC
    {$paginator->getLimit()}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$testimonials = $stmt->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Kelola Testimoni</h2>
            <p style="margin: 0; color: var(--gray-600); font-size: 14px;">Kelola testimoni dari peserta magang</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM testimonials");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Total Testimoni</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--success);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM testimonials WHERE is_published = 1");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Published</div>
        </div>
        <div class="nb-card">
            <div style="font-size: 2rem; font-weight: 900; margin-bottom: 4px; color: var(--warning);">
                <?php
                $stmt = $db->query("SELECT COUNT(*) FROM testimonials WHERE is_published = 0");
                echo $stmt->fetchColumn();
                ?>
            </div>
            <div style="font-weight: 700; font-size: 14px; color: var(--gray-600);">Draft</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="nb-card mb-4">
        <form method="GET" action="">
            <div style="display: grid; grid-template-columns: 1fr auto auto auto; gap: 12px; align-items: end;">
                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-search"></i> Cari Testimoni
                    </label>
                    <div class="nb-search-box" style="margin: 0;">
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" class="nb-input" placeholder="Cari nama atau pesan..." value="<?= e($filters['search']) ?>" style="padding: 10px 12px; font-size: 13px;">
                    </div>
                </div>
                
                <div>
                    <label class="nb-label" style="font-size: 12px; margin-bottom: 6px;">
                        <i class="bi bi-funnel"></i> Status
                    </label>
                    <select name="status" class="nb-input" style="padding: 10px 12px; font-size: 13px; min-width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>>
                            ✓ Published
                        </option>
                        <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>>
                            ⏸ Draft
                        </option>
                    </select>
                </div>
                
                <button type="submit" class="nb-btn nb-btn-primary" style="font-size: 13px; padding: 10px 16px;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                
                <?php if (!empty(array_filter($filters))): ?>
                    <a href="<?= APP_URL ?>/admin/testimonials" class="nb-btn nb-btn-outline" style="font-size: 13px; padding: 10px 16px;">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Pagination Info -->
    <div style="margin-bottom: 12px; font-size: 13px; color: var(--gray-600); font-weight: 600;">
        <?= $paginator->getInfo() ?>
    </div>

    <form method="POST" id="bulkForm">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        
        <?php if (count($testimonials) > 0): ?>
            <div class="nb-card mb-3" style="padding: 12px; background: var(--gray-50); border: 2px solid var(--gray-200);">
                <div style="display: flex; gap: 12px; align-items: center;">
                    <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
                    <label for="selectAll" style="font-weight: 600; cursor: pointer; margin: 0; font-size: 13px;">Pilih Semua</label>
                    <span style="color: var(--gray-300);">|</span>
                    <button type="submit" name="action" value="bulk_delete" class="nb-btn nb-btn-sm nb-btn-danger" onclick="return confirm('Hapus semua testimoni terpilih?')" style="font-size: 12px; padding: 8px 12px;">
                        <i class="bi bi-trash"></i> Hapus Terpilih
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="nb-card">
            <?php if (count($testimonials) > 0): ?>
                <div class="nb-table-responsive">
                    <table class="nb-table">
                        <thead>
                            <tr style="background: var(--gray-50);">
                                <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllHeader" style="width: 18px; height: 18px;"></th>
                                <th style="font-size: 12px; font-weight: 700;">Nama & Info</th>
                                <th style="font-size: 12px; font-weight: 700;">Pesan</th>
                                <th style="font-size: 12px; font-weight: 700; text-align: center;">Status</th>
                                <th style="font-size: 12px; font-weight: 700;">Tanggal</th>
                                <th style="font-size: 12px; font-weight: 700; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $t): ?>
                                <tr style="border-bottom: 1px solid var(--gray-200); transition: background 0.2s;" onmouseover="this.style.background='var(--gray-50)'" onmouseout="this.style.background='transparent'">
                                    <td style="text-align: center;"><input type="checkbox" name="ids[]" value="<?= $t['id'] ?>" class="row-checkbox" style="width: 18px; height: 18px;"></td>
                                    <td>
                                        <div style="font-weight: 700; font-size: 14px; margin-bottom: 4px;"><?= e($t['name']) ?></div>
                                        <?php if ($t['user_name']): ?>
                                            <div style="font-size: 11px; color: var(--gray-600);">
                                                <i class="bi bi-person-circle"></i> <?= e($t['user_name']) ?>
                                            </div>
                                            <div style="font-size: 11px; color: var(--gray-600);">
                                                <i class="bi bi-envelope"></i> <?= e($t['email']) ?>
                                            </div>
                                        <?php elseif ($t['university']): ?>
                                            <div style="font-size: 11px; color: var(--gray-600);">
                                                <i class="bi bi-building"></i> <?= e($t['university']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; color: var(--gray-700);">
                                            "<?= truncate(e($t['content'] ?: $t['text']), 80) ?>"
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($t['is_published']): ?>
                                            <span class="nb-badge nb-badge-success" style="font-size: 11px;">
                                                <i class="bi bi-check-circle"></i> Published
                                            </span>
                                        <?php else: ?>
                                            <span class="nb-badge nb-badge-warning" style="font-size: 11px;">
                                                <i class="bi bi-pause-circle"></i> Draft
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size: 12px; color: var(--gray-600);"><?= formatDateIndo($t['created_at']) ?></td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <?php if (!$t['is_published']): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                    <button type="submit" class="nb-btn nb-btn-sm nb-btn-success" title="Publikasikan" style="font-size: 12px; padding: 6px 10px;">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                    <input type="hidden" name="action" value="unpublish">
                                                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                                    <button type="submit" class="nb-btn nb-btn-sm nb-btn-warning" title="Sembunyikan" style="font-size: 12px; padding: 6px 10px;">
                                                        <i class="bi bi-eye-slash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <button type="button" class="nb-btn nb-btn-sm nb-btn-danger delete-btn" data-form-id="delete-form-<?= $t['id'] ?>" title="Hapus" style="font-size: 12px; padding: 6px 10px;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="delete-form-<?= $t['id'] ?>" method="POST" style="display: none;">
                                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 48px; color: var(--gray-500);">
                    <i class="bi bi-inbox" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p style="margin: 0; font-weight: 600;">Tidak ada testimoni ditemukan</p>
                    <p style="margin: 8px 0 0 0; font-size: 13px;">Coba ubah filter atau cari dengan kriteria lain</p>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <?= $paginator->render(APP_URL . '/admin/testimonials', $filters) ?>
</div>

<script>
// Checkbox handlers
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('selectAllHeader')?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
});

// Delete button handlers with custom confirmation modal
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const formId = this.getAttribute('data-form-id');
            const form = document.getElementById(formId);
            
            if (form) {
                showConfirmation(
                    'Hapus Testimoni?',
                    'Apakah Anda yakin ingin menghapus testimoni ini? Tindakan ini tidak dapat dibatalkan.',
                    (confirmed) => {
                        if (confirmed) {
                            form.submit();
                        }
                    }
                );
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
