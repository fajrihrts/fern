<?php
$pageTitle = 'Kelola Pendaftaran';

if (!class_exists('Cache')) {
    require_once __DIR__ . '/../autoload.php';
}

$user = auth();
$path = getCurrentPath();

$db = getDbConnection();

// Check if viewing or editing specific registration
$viewId = $_GET['id'] ?? null;
$mode = 'list'; // list, view, edit

if ($viewId && isset($_GET['action'])) {
    if ($_GET['action'] === 'view') {
        $mode = 'view';
    } elseif ($_GET['action'] === 'edit') {
        $mode = 'edit';
    }
}

// Get registration data if viewing or editing
$registration = null;
if ($viewId && in_array($mode, ['view', 'edit'])) {
    $stmt = $db->prepare("
        SELECT r.*, u.email, u.name as user_name
        FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.id = ?
    ");
    $stmt->execute([$viewId]);
    $registration = $stmt->fetch();
    
    if (!$registration) {
        setFlash('danger', 'Pendaftaran tidak ditemukan');
        redirect('/admin/registrations');
    }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Token keamanan tidak valid');
        redirect('/admin/registrations');
    }
    
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    
    // Bulk actions
    if ($action === 'bulk_approve' && !empty($_POST['ids'])) {
        $result = BulkAction::approveRegistrations($_POST['ids']);
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/registrations');
    }
    
    if ($action === 'bulk_reject' && !empty($_POST['ids'])) {
        $result = BulkAction::rejectRegistrations($_POST['ids']);
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/registrations');
    }
    
    if ($action === 'bulk_delete' && !empty($_POST['ids'])) {
        $result = BulkAction::delete('registrations', $_POST['ids'], 'Bulk delete pendaftaran');
        setFlash($result['success'] ? 'success' : 'danger', $result['message']);
        redirect('/admin/registrations');
    }
    
    // Single actions
    if ($action === 'update' && $id) {
        $status = $_POST['status'] ?? '';
        $internshipStatus = $_POST['internship_status'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $actualStartDate = $_POST['actual_start_date'] ?? null;
        $actualEndDate = $_POST['actual_end_date'] ?? null;
        $terminationReason = trim($_POST['termination_reason'] ?? '');
        
        $stmt = $db->prepare("
            UPDATE registrations 
            SET status = ?, internship_status = ?, admin_notes = ?, 
                actual_start_date = ?, actual_end_date = ?, termination_reason = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$status, $internshipStatus, $adminNotes, $actualStartDate, $actualEndDate, $terminationReason, $id])) {
            ActivityLog::log(
                ActivityLog::ACTION_UPDATE,
                "Admin mengupdate pendaftaran",
                null,
                ['registration_id' => $id, 'status' => $status]
            );
            setFlash('success', 'Pendaftaran berhasil diperbarui');
        } else {
            setFlash('danger', 'Gagal memperbarui pendaftaran');
        }
        redirect('/admin/registrations');
    }
}

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'internship' => $_GET['internship'] ?? '',
    'search' => $_GET['search'] ?? '',
    'university' => $_GET['university'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
];

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Build WHERE clause
$where = [];
$params = [];

if ($filters['status']) {
    $where[] = "r.status = ?";
    $params[] = $filters['status'];
}
if ($filters['internship']) {
    $where[] = "r.internship_status = ?";
    $params[] = $filters['internship'];
}
if ($filters['search']) {
    $where[] = "(r.name LIKE ? OR u.email LIKE ? OR r.university LIKE ?)";
    $search = '%' . $filters['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}
if ($filters['university']) {
    $where[] = "r.university LIKE ?";
    $params[] = '%' . $filters['university'] . '%';
}
if ($filters['date_from']) {
    $where[] = "DATE(r.created_at) >= ?";
    $params[] = $filters['date_from'];
}
if ($filters['date_to']) {
    $where[] = "DATE(r.created_at) <= ?";
    $params[] = $filters['date_to'];
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countSql = "SELECT COUNT(*) FROM registrations r JOIN users u ON r.user_id = u.id $whereClause";
$stmt = $db->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Create paginator
$paginator = new Paginator($total, 5, $page);

// Get data
$query = "
    SELECT r.*, u.email 
    FROM registrations r 
    JOIN users u ON r.user_id = u.id 
    $whereClause
    ORDER BY r.created_at DESC
    {$paginator->getLimit()}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($mode === 'view' && $registration): ?>
    <!-- View Detail Registration -->
    <div class="container-md" style="padding: 16px 15px; max-height: calc(100vh - 72px); overflow: hidden;">
        <div style="margin-bottom: 10px;">
            <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
        
        <div class="nb-card nb-card-compact" style="padding: 16px; max-height: calc(100vh - 130px); overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--gray-200);">
                <h3 style="margin: 0; font-size: 18px;">Detail Pendaftaran</h3>
                <a href="<?= APP_URL ?>/admin/registrations?id=<?= $registration['id'] ?>&action=edit" class="nb-btn nb-btn-warning nb-btn-sm">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
            
            <!-- 3 Column Layout -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                
                <!-- Column 1: Personal & Academic Info -->
                <div>
                    <!-- Personal Info -->
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Informasi Pribadi</h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Nama Lengkap</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['name']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Email</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900); word-break: break-all;"><?= e($registration['email']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">No. WhatsApp</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['phone']) ?></div>
                            </div>
                            <?php if (isset($registration['gender']) && $registration['gender']): ?>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Jenis Kelamin</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['gender']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Academic Info -->
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Informasi Akademik</h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Universitas</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['university']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Program Studi</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['major']) ?></div>
                            </div>
                            <?php if (isset($registration['student_id']) && $registration['student_id']): ?>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">NIM</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['student_id']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($registration['semester']) && $registration['semester']): ?>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Semester</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= e($registration['semester']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Column 2: Internship Period & Status -->
                <div>
                    <!-- Internship Period -->
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Periode Magang</h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Tanggal Mulai (Rencana)</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= formatDateIndo($registration['start_date']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Tanggal Selesai (Rencana)</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= formatDateIndo($registration['end_date']) ?></div>
                            </div>
                            <?php if ($registration['actual_start_date']): ?>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Tanggal Mulai (Aktual)</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= formatDateIndo($registration['actual_start_date']) ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if ($registration['actual_end_date']): ?>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 1px; text-transform: uppercase; letter-spacing: 0.3px;">Tanggal Selesai (Aktual)</div>
                                <div style="font-weight: 600; font-size: 13px; color: var(--gray-900);"><?= formatDateIndo($registration['actual_end_date']) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Status</h5>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.3px;">Status Pendaftaran</div>
                                <?= getStatusBadge($registration['status']) ?>
                            </div>
                            <div>
                                <div style="font-size: 9px; color: var(--gray-500); font-weight: 600; margin-bottom: 3px; text-transform: uppercase; letter-spacing: 0.3px;">Status Magang</div>
                                <?= getInternshipStatusBadge($registration['internship_status']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Column 3: Notes & Documents -->
                <div>
                    <?php if ($registration['admin_notes']): ?>
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Catatan Admin</h5>
                        <div style="background: var(--gray-50); padding: 8px; border-radius: 6px; border: 1px solid var(--gray-200); font-size: 12px; line-height: 1.4; color: var(--gray-700);">
                            <?= nl2br(e($registration['admin_notes'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($registration['termination_reason']): ?>
                    <div style="margin-bottom: 12px;">
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Alasan Berhenti</h5>
                        <div style="background: #fef2f2; padding: 8px; border-radius: 6px; border: 1px solid #fca5a5; font-size: 12px; line-height: 1.4; color: #991b1b;">
                            <?= nl2br(e($registration['termination_reason'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Documents -->
                    <div>
                        <h5 style="margin-bottom: 8px; padding-bottom: 3px; border-bottom: 1px solid var(--gray-200); font-size: 13px; font-weight: 700; color: var(--gray-700);">Dokumen</h5>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <?php if ($registration['proposal_file']): ?>
                            <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/proposal" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank" style="font-size: 11px; padding: 4px 8px;">
                                <i class="bi bi-file-pdf"></i> Proposal
                            </a>
                            <?php endif; ?>
                            <?php if ($registration['transcript_file']): ?>
                            <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/transcript" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank" style="font-size: 11px; padding: 4px 8px;">
                                <i class="bi bi-file-pdf"></i> Transkrip
                            </a>
                            <?php endif; ?>
                            <?php if ($registration['recommendation_letter_file']): ?>
                            <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/recommendation_letter" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank" style="font-size: 11px; padding: 4px 8px;">
                                <i class="bi bi-file-pdf"></i> Surat Rekomendasi
                            </a>
                            <?php endif; ?>
                            <?php if ($registration['certificate_files']): ?>
                                <?php 
                                $certificates = json_decode($registration['certificate_files'], true);
                                if (is_array($certificates) && count($certificates) > 0):
                                    foreach ($certificates as $index => $cert):
                                ?>
                                <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/certificate/<?= $index ?>" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank" style="font-size: 11px; padding: 4px 8px;">
                                    <i class="bi bi-file-pdf"></i> Sertifikat <?= $index + 1 ?>
                                </a>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

<?php elseif ($mode === 'edit' && $registration): ?>
    <!-- Edit Registration -->
    <div class="container-md" style="padding: 24px 15px;">
        <div style="margin-bottom: 16px;">
            <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
        
        <div class="nb-card nb-card-compact">
            <h3 style="margin: 0 0 20px 0; font-size: 18px;">Edit Pendaftaran: <?= e($registration['name']) ?></h3>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $registration['id'] ?>">
                
                <div class="nb-form-group">
                    <label class="nb-label">Status Pendaftaran *</label>
                    <select name="status" class="nb-input" required>
                        <option value="pending" <?= $registration['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $registration['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $registration['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="nb-form-group">
                    <label class="nb-label">Status Magang *</label>
                    <select name="internship_status" class="nb-input" required>
                        <option value="not_started" <?= $registration['internship_status'] === 'not_started' ? 'selected' : '' ?>>Belum Mulai</option>
                        <option value="ongoing" <?= $registration['internship_status'] === 'ongoing' ? 'selected' : '' ?>>Sedang Magang</option>
                        <option value="completed" <?= $registration['internship_status'] === 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="terminated" <?= $registration['internship_status'] === 'terminated' ? 'selected' : '' ?>>Berhenti</option>
                    </select>
                </div>
                
                <div class="nb-form-group">
                    <label class="nb-label">Tanggal Mulai Aktual</label>
                    <input type="date" name="actual_start_date" class="nb-input" value="<?= $registration['actual_start_date'] ?>">
                    <small style="color: var(--gray-600);">Kosongkan jika belum mulai</small>
                </div>
                
                <div class="nb-form-group">
                    <label class="nb-label">Tanggal Selesai Aktual</label>
                    <input type="date" name="actual_end_date" class="nb-input" value="<?= $registration['actual_end_date'] ?>">
                    <small style="color: var(--gray-600);">Kosongkan jika belum selesai</small>
                </div>
                
                <div class="nb-form-group">
                    <label class="nb-label">Catatan Admin</label>
                    <textarea name="admin_notes" class="nb-textarea" rows="4" placeholder="Catatan untuk peserta..."><?= e($registration['admin_notes']) ?></textarea>
                </div>
                
                <div class="nb-form-group">
                    <label class="nb-label">Alasan Berhenti</label>
                    <textarea name="termination_reason" class="nb-textarea" rows="3" placeholder="Isi jika status magang = Berhenti"><?= e($registration['termination_reason']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 20px;">
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                    <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-outline">
                        <i class="bi bi-x"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- List View -->
<div class="container py-5">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2>Kelola Pendaftaran</h2>
        <div style="display: flex; gap: 12px;">
            <a href="<?= APP_URL ?>/admin/export/registrations/csv" class="nb-btn nb-btn-outline">
                <i class="bi bi-filetype-csv"></i> Export CSV
            </a>
            <a href="<?= APP_URL ?>/admin/export/registrations/excel" class="nb-btn nb-btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filter Bar - Redesigned Simple -->
    <div class="nb-card mb-4" style="padding: 20px;">
        <form method="GET" action="">
            <!-- Row 1: Search + Quick Filters -->
            <div style="display: grid; grid-template-columns: 1fr auto auto auto; gap: 12px; margin-bottom: 12px;">
                <!-- Search -->
                <div class="nb-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="nb-input" placeholder="Cari nama, email, universitas..." value="<?= e($filters['search']) ?>" style="padding: 10px 10px 10px 40px;">
                </div>
                
                <!-- Status Filter -->
                <select name="status" class="nb-input" style="min-width: 160px; padding: 10px 14px;">
                    <option value="">Semua Status</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                    <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>✅ Approved</option>
                    <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>❌ Rejected</option>
                </select>
                
                <!-- Internship Status Filter -->
                <select name="internship" class="nb-input" style="min-width: 180px; padding: 10px 14px;">
                    <option value="">Semua Status Magang</option>
                    <option value="not_started" <?= $filters['internship'] === 'not_started' ? 'selected' : '' ?>>🔜 Belum Mulai</option>
                    <option value="ongoing" <?= $filters['internship'] === 'ongoing' ? 'selected' : '' ?>>🔄 Sedang Magang</option>
                    <option value="completed" <?= $filters['internship'] === 'completed' ? 'selected' : '' ?>>✅ Selesai</option>
                    <option value="terminated" <?= $filters['internship'] === 'terminated' ? 'selected' : '' ?>>⛔ Berhenti</option>
                </select>
                
                <!-- Filter Button -->
                <button type="submit" class="nb-btn nb-btn-primary" style="padding: 10px 24px;">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            
            <!-- Row 2: Advanced Filters (Collapsible) -->
            <div id="advancedFilters" style="display: <?= (!empty($filters['university']) || !empty($filters['date_from']) || !empty($filters['date_to'])) ? 'block' : 'none' ?>; padding-top: 12px; border-top: 1px solid var(--gray-200);">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 12px;">
                    <input type="text" name="university" class="nb-input" placeholder="🎓 Universitas..." value="<?= e($filters['university']) ?>" style="padding: 10px 14px;">
                    <input type="date" name="date_from" class="nb-input" value="<?= e($filters['date_from']) ?>" placeholder="Dari tanggal" style="padding: 10px 14px;">
                    <input type="date" name="date_to" class="nb-input" value="<?= e($filters['date_to']) ?>" placeholder="Sampai tanggal" style="padding: 10px 14px;">
                    
                    <?php if (!empty(array_filter($filters))): ?>
                        <a href="<?= APP_URL ?>/admin/registrations" class="nb-btn nb-btn-outline" style="padding: 10px 20px;">
                            <i class="bi bi-x-circle"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Toggle Advanced Filters -->
            <div style="margin-top: 12px;">
                <button type="button" onclick="toggleAdvancedFilters()" class="nb-btn nb-btn-sm nb-btn-outline" style="font-size: 12px; padding: 6px 12px;">
                    <i class="bi bi-chevron-down" id="toggleIcon"></i> <span id="toggleText">Filter Lanjutan</span>
                </button>
            </div>
        </form>
    </div>

    <script>
    function toggleAdvancedFilters() {
        const filters = document.getElementById('advancedFilters');
        const icon = document.getElementById('toggleIcon');
        const text = document.getElementById('toggleText');
        
        if (filters.style.display === 'none') {
            filters.style.display = 'block';
            icon.className = 'bi bi-chevron-up';
            text.textContent = 'Sembunyikan Filter';
        } else {
            filters.style.display = 'none';
            icon.className = 'bi bi-chevron-down';
            text.textContent = 'Filter Lanjutan';
        }
    }
    </script>

    <!-- Pagination Info -->
    <div class="nb-pagination-info">
        <?= $paginator->getInfo() ?>
    </div>

    <div id="registrations-list-container">
        <!-- Bulk Actions Form -->
        <form method="POST" id="bulkForm">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            
            <!-- Bulk Action Bar - Simplified -->
            <?php if (count($registrations) > 0): ?>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);">
                    <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: white; font-weight: 600; font-size: 14px; margin: 0;">
                            <input type="checkbox" id="selectAll" style="width: 20px; height: 20px; cursor: pointer; accent-color: var(--primary);">
                            Pilih Semua
                        </label>
                        <div style="width: 2px; height: 24px; background: rgba(255,255,255,0.3);"></div>
                        <button type="submit" name="action" value="bulk_approve" class="nb-btn nb-btn-success nb-btn-sm" onclick="return confirm('Setujui semua data terpilih?')" style="font-size: 13px; padding: 8px 16px;">
                            <i class="bi bi-check-circle"></i> Setujui
                        </button>
                        <button type="submit" name="action" value="bulk_reject" class="nb-btn nb-btn-danger nb-btn-sm" onclick="return confirm('Tolak semua data terpilih?')" style="font-size: 13px; padding: 8px 16px;">
                            <i class="bi bi-x-circle"></i> Tolak
                        </button>
                        <button type="submit" name="action" value="bulk_delete" class="nb-btn nb-btn-outline nb-btn-sm" onclick="return confirm('Hapus semua data terpilih? Tindakan ini tidak dapat dibatalkan!')" style="font-size: 13px; padding: 8px 16px; background: white; color: #667eea;">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Registrations Table -->
            <div style="background: var(--white); border: 1px solid var(--gray-300); border-radius: 8px; overflow: hidden;">
                <?php if (count($registrations) > 0): ?>
                    <div class="nb-table-responsive">
                        <table class="nb-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;"><input type="checkbox" id="selectAllHeader" style="width: 18px; height: 18px; accent-color: var(--primary);"></th>
                                    <th>Nama</th>
                                    <th>Universitas</th>
                                    <th>Jurusan</th>
                                    <th>Periode</th>
                                    <th>Status</th>
                                    <th>Status Magang</th>
                                    <th style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="<?= $reg['id'] ?>" class="row-checkbox" style="width: 18px; height: 18px; accent-color: var(--primary);"></td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--gray-900);"><?= e($reg['name']) ?></div>
                                            <div style="font-size: 12px; color: var(--gray-500);"><?= e($reg['email']) ?></div>
                                        </td>
                                        <td style="color: var(--gray-700);"><?= e($reg['university']) ?></td>
                                        <td style="color: var(--gray-700);"><?= e($reg['major']) ?></td>
                                        <td>
                                            <div style="font-size: 13px; color: var(--gray-800);"><?= formatDateIndo($reg['start_date']) ?></div>
                                            <div style="font-size: 12px; color: var(--gray-500);">s/d <?= formatDateIndo($reg['end_date']) ?></div>
                                        </td>
                                        <td><?= getStatusBadge($reg['status']) ?></td>
                                        <td><?= getInternshipStatusBadge($reg['internship_status']) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 6px;">
                                                <button type="button" onclick="viewDetail('<?= $reg['id'] ?>')" class="nb-btn nb-btn-sm nb-btn-info" title="Lihat Detail" style="padding: 6px 10px;">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button type="button" onclick="editRegistration('<?= $reg['id'] ?>')" class="nb-btn nb-btn-sm nb-btn-warning" title="Edit" style="padding: 6px 10px;">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                        <i class="bi bi-inbox" style="font-size: 56px; margin-bottom: 16px; display: block; color: var(--gray-400);"></i>
                        <p style="font-size: 16px; font-weight: 600; color: var(--gray-600); margin-bottom: 4px;">Tidak ada pendaftaran ditemukan</p>
                        <p style="font-size: 14px; color: var(--gray-500);">Coba ubah filter atau reset pencarian</p>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <!-- Pagination -->
        <div id="pagination-container">
            <?= $paginator->render(APP_URL . '/admin/registrations', $filters) ?>
        </div>
    </div>
</div>

<script>
// Initialize checkbox handlers
function initCheckboxHandlers() {
    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('selectAllHeader')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
}

// Initialize on page load
initCheckboxHandlers();

function viewDetail(id) {
    window.location.href = '<?= APP_URL ?>/admin/registrations?id=' + id + '&action=view';
}

function editRegistration(id) {
    window.location.href = '<?= APP_URL ?>/admin/registrations?id=' + id + '&action=edit';
}

// AJAX Pagination for Registrations List
function loadRegistrationsPage(page) {
    const container = document.getElementById('registrations-list-container');
    
    // Add loading state
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';
    
    // Get current filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', page);
    urlParams.set('ajax', '1');
    
    // Fetch registrations for the requested page
    fetch('<?= APP_URL ?>/admin/registrations?' + urlParams.toString())
        .then(response => response.text())
        .then(html => {
            // Parse the HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('registrations-list-container');
            
            if (newContent) {
                // Smooth fade out
                container.style.transition = 'opacity 0.2s';
                container.style.opacity = '0';
                
                setTimeout(() => {
                    // Replace content
                    container.innerHTML = newContent.innerHTML;
                    
                    // Re-initialize checkbox handlers
                    initCheckboxHandlers();
                    
                    // Re-attach pagination click handlers
                    attachPaginationHandlers();
                    
                    // Smooth fade in
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    
                    // Scroll to top of list smoothly
                    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        })
        .catch(error => {
            console.error('Error loading registrations:', error);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            alert('Gagal memuat data. Silakan coba lagi.');
        });
}

// Attach click handlers to pagination links
function attachPaginationHandlers() {
    const paginationLinks = document.querySelectorAll('.nb-pagination a');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Extract page number from URL
            const url = new URL(this.href);
            const page = url.searchParams.get('page') || 1;
            
            // Load page via AJAX
            loadRegistrationsPage(page);
        });
    });
}

// Initialize pagination handlers on page load
attachPaginationHandlers();
</script>

<?php endif; // End mode check ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
