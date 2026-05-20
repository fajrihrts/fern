<?php
$pageTitle = 'Buat Laporan Kehadiran';
$user = auth();

// Check registration
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = ? AND status = 'approved' AND internship_status = 'ongoing'");
$stmt->execute([$user['id']]);
$registration = $stmt->fetch();

if (!$registration) {
    setFlash('danger', 'Anda tidak memiliki akses untuk membuat laporan kehadiran.');
    redirect('/dashboard');
}

// Check if already reported today
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT id FROM attendance_reports WHERE user_id = ? AND date = ?");
$stmt->execute([$user['id'], $today]);
if ($stmt->fetch()) {
    setFlash('warning', 'Anda sudah membuat laporan untuk hari ini.');
    redirect('/laporan');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Token keamanan tidak valid';
    }
    
    $status = $_POST['status'] ?? '';
    // Auto-set check_in to current time when submitting
    $checkIn = date('H:i:s');
    $checkOut = null; // Will be set when user does check-out
    $activities = trim($_POST['activities'] ?? '');
    $learning = trim($_POST['learning'] ?? '');
    $obstacles = trim($_POST['obstacles'] ?? '');
    $isConfirmed = isset($_POST['is_confirmed']) ? 1 : 0;
    
    // Validate
    if (!in_array($status, ['hadir', 'izin', 'sakit', 'alpha'])) {
        $errors['status'] = 'Status kehadiran tidak valid';
    }
    
    if (strlen($activities) < 100) {
        $errors['activities'] = 'Uraian aktivitas minimal 100 karakter';
    } elseif (strlen($activities) > 1000) {
        $errors['activities'] = 'Uraian aktivitas maksimal 1000 karakter';
    }
    
    if (strlen($learning) < 100) {
        $errors['learning'] = 'Pembelajaran minimal 100 karakter';
    } elseif (strlen($learning) > 1000) {
        $errors['learning'] = 'Pembelajaran maksimal 1000 karakter';
    }
    
    if (strlen($obstacles) < 100) {
        $errors['obstacles'] = 'Kendala minimal 100 karakter';
    } elseif (strlen($obstacles) > 1000) {
        $errors['obstacles'] = 'Kendala maksimal 1000 karakter';
    }
    
    if (!$isConfirmed) {
        $errors['is_confirmed'] = 'Anda harus mencentang konfirmasi';
    }
    
    // Handle photo upload
    $photoPath = null;
    if (isset($_FILES['photo_proof']) && $_FILES['photo_proof']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['photo_proof'], 'attendance_photos', ALLOWED_IMAGE_TYPES);
        if ($result['success']) {
            $photoPath = $result['path'];
        } else {
            $errors['photo_proof'] = $result['error'];
        }
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO attendance_reports (
                id, user_id, registration_id, date, status, check_in, check_out,
                activities, learning, obstacles, photo_proof, is_confirmed, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        if ($stmt->execute([
            generateUuid(), $user['id'], $registration['id'], $today, $status,
            $checkIn, $checkOut, $activities, $learning, $obstacles, $photoPath, $isConfirmed
        ])) {
            setFlash('success', 'Laporan kehadiran berhasil disimpan!');
            redirect('/laporan');
        } else {
            $errors['general'] = 'Gagal menyimpan laporan';
        }
    }
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container-sm">
        <div class="mb-3">
            <a href="<?= APP_URL ?>/laporan" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="nb-card">
            <h2 class="mb-3">Laporan Kehadiran Hari Ini</h2>
            <p style="color: var(--gray-600); margin-bottom: 32px;">
                <i class="bi bi-calendar"></i> <?= formatDateIndo($today) ?>
            </p>

            <?php if (isset($errors['general'])): ?>
                <div class="nb-alert nb-alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="nb-form-group">
                    <label class="nb-label">Status Kehadiran <span style="color: var(--danger);">*</span></label>
                    <select name="status" class="nb-select" required>
                        <option value="">Pilih Status</option>
                        <option value="hadir" <?= ($_POST['status'] ?? '') === 'hadir' ? 'selected' : '' ?>>Hadir</option>
                        <option value="izin" <?= ($_POST['status'] ?? '') === 'izin' ? 'selected' : '' ?>>Izin</option>
                        <option value="sakit" <?= ($_POST['status'] ?? '') === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="alpha" <?= ($_POST['status'] ?? '') === 'alpha' ? 'selected' : '' ?>>Alpha</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="nb-error"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Info: Waktu absen otomatis -->
                <div class="nb-alert nb-alert-info" style="margin-bottom: 24px;">
                    <i class="bi bi-info-circle"></i>
                    <span>
                        <strong>Waktu Absen Otomatis:</strong> Jam masuk akan tercatat saat Anda mengirim laporan ini. 
                        Untuk absen keluar, klik tombol "Absen Keluar" di halaman laporan Anda nanti.
                    </span>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">
                        Uraian Aktivitas <span style="color: var(--danger);">*</span>
                        <span id="activities-count" style="float: right; color: var(--gray-500); font-weight: 400;">0/1000</span>
                    </label>
                    <textarea name="activities" id="activities" class="nb-textarea" rows="5" placeholder="Jelaskan aktivitas yang Anda lakukan hari ini (minimal 100 karakter)" required><?= e($_POST['activities'] ?? '') ?></textarea>
                    <?php if (isset($errors['activities'])): ?>
                        <div class="nb-error"><?= e($errors['activities']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">
                        Pembelajaran <span style="color: var(--danger);">*</span>
                        <span id="learning-count" style="float: right; color: var(--gray-500); font-weight: 400;">0/1000</span>
                    </label>
                    <textarea name="learning" id="learning" class="nb-textarea" rows="5" placeholder="Apa yang Anda pelajari hari ini? (minimal 100 karakter)" required><?= e($_POST['learning'] ?? '') ?></textarea>
                    <?php if (isset($errors['learning'])): ?>
                        <div class="nb-error"><?= e($errors['learning']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">
                        Kendala <span style="color: var(--danger);">*</span>
                        <span id="obstacles-count" style="float: right; color: var(--gray-500); font-weight: 400;">0/1000</span>
                    </label>
                    <textarea name="obstacles" id="obstacles" class="nb-textarea" rows="5" placeholder="Kendala atau hambatan yang dihadapi (minimal 100 karakter)" required><?= e($_POST['obstacles'] ?? '') ?></textarea>
                    <?php if (isset($errors['obstacles'])): ?>
                        <div class="nb-error"><?= e($errors['obstacles']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Foto Bukti <span style="color: var(--gray-500); font-weight: 400;">- Opsional</span></label>
                    <input type="file" name="photo_proof" class="nb-input" accept="image/*">
                    <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> Format: JPG, PNG. Maksimal 5MB
                    </div>
                    <?php if (isset($errors['photo_proof'])): ?>
                        <div class="nb-error"><?= e($errors['photo_proof']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label style="display: flex; align-items: start; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="is_confirmed" style="width: 20px; height: 20px; cursor: pointer; margin-top: 2px;" <?= isset($_POST['is_confirmed']) ? 'checked' : '' ?> required>
                        <span style="font-weight: 600;">
                            Saya menyatakan bahwa data yang saya isi adalah benar dan dapat dipertanggungjawabkan.
                        </span>
                    </label>
                    <?php if (isset($errors['is_confirmed'])): ?>
                        <div class="nb-error"><?= e($errors['is_confirmed']) ?></div>
                    <?php endif; ?>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="<?= APP_URL ?>/laporan" class="nb-btn nb-btn-outline">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-send"></i> Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Character counters
['activities', 'learning', 'obstacles'].forEach(id => {
    const textarea = document.getElementById(id);
    const counter = document.getElementById(id + '-count');
    
    if (textarea && counter) {
        const updateCounter = () => {
            const length = textarea.value.length;
            counter.textContent = length + '/1000';
            counter.style.color = length < 100 ? 'var(--danger)' : length >= 1000 ? 'var(--warning)' : 'var(--success)';
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
