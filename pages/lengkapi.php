<?php
$pageTitle = 'Lengkapi Pendaftaran';
$user = auth();

// Check if already has registration
$db = getDbConnection();
$stmt = $db->prepare("SELECT id FROM registrations WHERE user_id = ?");
$stmt->execute([$user['id']]);
if ($stmt->fetch()) {
    redirect('/dashboard');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Token keamanan tidak valid';
    }
    
    // Get and validate input
    $phone = trim($_POST['phone'] ?? '');
    $university = trim($_POST['university'] ?? '');
    $major = trim($_POST['major'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // Validate required fields
    if (empty($phone)) $errors['phone'] = 'Nomor WhatsApp wajib diisi';
    if (empty($university)) $errors['university'] = 'Asal universitas wajib diisi';
    if (empty($major)) $errors['major'] = 'Program studi wajib diisi';
    if (empty($start_date)) $errors['start_date'] = 'Tanggal mulai wajib diisi';
    if (empty($end_date)) $errors['end_date'] = 'Tanggal selesai wajib diisi';
    
    // Validate dates
    if (!empty($start_date) && strtotime($start_date) < strtotime('today')) {
        $errors['start_date'] = 'Tanggal mulai tidak boleh di masa lalu';
    }
    if (!empty($start_date) && !empty($end_date) && strtotime($end_date) <= strtotime($start_date)) {
        $errors['end_date'] = 'Tanggal selesai harus setelah tanggal mulai';
    }
    
    // Validate proposal file (required)
    if (!isset($_FILES['proposal_file']) || $_FILES['proposal_file']['error'] !== UPLOAD_ERR_OK) {
        $errors['proposal_file'] = 'Proposal wajib diunggah';
    }
    
    // Process file uploads if no errors
    if (empty($errors)) {
        $proposal = uploadFile($_FILES['proposal_file'], 'proposals', [ALLOWED_PDF_TYPE]);
        if (!$proposal['success']) {
            $errors['proposal_file'] = $proposal['error'];
        }
        
        $transcript = null;
        if (isset($_FILES['transcript_file']) && $_FILES['transcript_file']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['transcript_file'], 'transcripts', [ALLOWED_PDF_TYPE]);
            $transcript = $result['success'] ? $result['path'] : null;
        }
        
        $recommendation = null;
        if (isset($_FILES['recommendation_letter_file']) && $_FILES['recommendation_letter_file']['error'] === UPLOAD_ERR_OK) {
            $result = uploadFile($_FILES['recommendation_letter_file'], 'recommendation_letters', [ALLOWED_PDF_TYPE]);
            $recommendation = $result['success'] ? $result['path'] : null;
        }
        
        // Handle multiple certificates (max 5)
        $certificates = [];
        if (isset($_FILES['certificate_files'])) {
            $allowedTypes = array_merge([ALLOWED_PDF_TYPE], ALLOWED_IMAGE_TYPES);
            for ($i = 0; $i < min(count($_FILES['certificate_files']['name']), 5); $i++) {
                if ($_FILES['certificate_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['certificate_files']['name'][$i],
                        'type' => $_FILES['certificate_files']['type'][$i],
                        'tmp_name' => $_FILES['certificate_files']['tmp_name'][$i],
                        'error' => $_FILES['certificate_files']['error'][$i],
                        'size' => $_FILES['certificate_files']['size'][$i]
                    ];
                    $result = uploadFile($file, 'certificates', $allowedTypes);
                    if ($result['success']) {
                        $certificates[] = $result['path'];
                    }
                }
            }
        }
        
        // Insert registration
        if ($proposal['success']) {
            $stmt = $db->prepare("
                INSERT INTO registrations (
                    id, user_id, name, email, phone, university, major, 
                    start_date, end_date, proposal_file, transcript_file, 
                    recommendation_letter_file, certificate_files, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $registrationId = generateUuid();
            $certificatesJson = !empty($certificates) ? json_encode($certificates) : null;
            
            if ($stmt->execute([
                $registrationId, $user['id'], $user['name'], $user['email'], 
                $phone, $university, $major, $start_date, $end_date, 
                $proposal['path'], $transcript, $recommendation, $certificatesJson
            ])) {
                setFlash('success', 'Pendaftaran berhasil dikirim! Silakan tunggu verifikasi dari admin.');
                redirect('/dashboard');
            } else {
                $errors['general'] = 'Gagal menyimpan pendaftaran';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container-sm">
        <!-- Progress Bar -->
        <div class="nb-card mb-4">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div style="text-align: center; flex: 1;">
                    <div style="width: 40px; height: 40px; background: var(--success); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800;">
                        <i class="bi bi-check"></i>
                    </div>
                    <div style="font-weight: 700; font-size: 12px;">Buat Akun</div>
                </div>
                <div style="flex: 1; height: 3px; background: var(--success); margin: 0 8px;"></div>
                <div style="text-align: center; flex: 1;">
                    <div style="width: 40px; height: 40px; background: var(--warning); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800;">2</div>
                    <div style="font-weight: 700; font-size: 12px;">Isi Formulir</div>
                </div>
                <div style="flex: 1; height: 3px; background: var(--gray-300); margin: 0 8px;"></div>
                <div style="text-align: center; flex: 1;">
                    <div style="width: 40px; height: 40px; background: var(--gray-300); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800;">3</div>
                    <div style="font-weight: 700; font-size: 12px; color: var(--gray-500);">Verifikasi</div>
                </div>
            </div>
        </div>

        <div class="nb-card">
            <h2 class="mb-3">Lengkapi Data Pendaftaran</h2>
            <p style="color: var(--gray-600); margin-bottom: 32px;">
                Isi formulir di bawah ini dengan lengkap dan benar. Pastikan semua dokumen yang diunggah dalam format yang sesuai.
            </p>

            <?php if (isset($errors['general'])): ?>
                <div class="nb-alert nb-alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Data Diri -->
                <h4 class="mb-3" style="padding-top: 20px; border-top: 3px solid var(--gray-200);">
                    <i class="bi bi-person"></i> Data Diri
                </h4>

                <div class="grid grid-cols-2 gap-3">
                    <div class="nb-form-group">
                        <label class="nb-label">Nama Lengkap</label>
                        <input type="text" class="nb-input" value="<?= e($user['name']) ?>" disabled>
                    </div>
                    <div class="nb-form-group">
                        <label class="nb-label">Email</label>
                        <input type="email" class="nb-input" value="<?= e($user['email']) ?>" disabled>
                    </div>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Nomor WhatsApp <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="phone" class="nb-input" placeholder="08xxxxxxxxxx" value="<?= e($_POST['phone'] ?? '') ?>" required>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="nb-error"><?= e($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Data Akademik -->
                <h4 class="mb-3" style="padding-top: 20px; border-top: 3px solid var(--gray-200);">
                    <i class="bi bi-mortarboard"></i> Data Akademik
                </h4>

                <div class="nb-form-group">
                    <label class="nb-label">Asal Universitas <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="university" class="nb-input" placeholder="Contoh: Universitas Indonesia" value="<?= e($_POST['university'] ?? '') ?>" required>
                    <?php if (isset($errors['university'])): ?>
                        <div class="nb-error"><?= e($errors['university']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Program Studi <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="major" class="nb-input" placeholder="Contoh: Statistika" value="<?= e($_POST['major'] ?? '') ?>" required>
                    <?php if (isset($errors['major'])): ?>
                        <div class="nb-error"><?= e($errors['major']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Periode Magang -->
                <h4 class="mb-3" style="padding-top: 20px; border-top: 3px solid var(--gray-200);">
                    <i class="bi bi-calendar-range"></i> Periode Magang
                </h4>

                <div class="grid grid-cols-2 gap-3">
                    <div class="nb-form-group">
                        <label class="nb-label">Tanggal Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="start_date" class="nb-input" value="<?= e($_POST['start_date'] ?? '') ?>" required>
                        <?php if (isset($errors['start_date'])): ?>
                            <div class="nb-error"><?= e($errors['start_date']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="nb-form-group">
                        <label class="nb-label">Tanggal Selesai <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="end_date" class="nb-input" value="<?= e($_POST['end_date'] ?? '') ?>" required>
                        <?php if (isset($errors['end_date'])): ?>
                            <div class="nb-error"><?= e($errors['end_date']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <h4 class="mb-3" style="padding-top: 20px; border-top: 3px solid var(--gray-200);">
                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Dokumen
                </h4>

                <div class="nb-form-group">
                    <label class="nb-label">Proposal Magang (PDF, max 5MB) <span style="color: var(--danger);">*</span></label>
                    <input type="file" name="proposal_file" class="nb-input" accept=".pdf" required>
                    <?php if (isset($errors['proposal_file'])): ?>
                        <div class="nb-error"><?= e($errors['proposal_file']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Transkrip Nilai (PDF, max 5MB) <span style="color: var(--gray-500); font-weight: 400;">- Opsional</span></label>
                    <input type="file" name="transcript_file" class="nb-input" accept=".pdf">
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Surat Rekomendasi (PDF, max 5MB) <span style="color: var(--gray-500); font-weight: 400;">- Opsional</span></label>
                    <input type="file" name="recommendation_letter_file" class="nb-input" accept=".pdf">
                </div>

                <div class="nb-form-group">
                    <label class="nb-label">Sertifikat (PDF/JPG/PNG, max 5 file) <span style="color: var(--gray-500); font-weight: 400;">- Opsional</span></label>
                    <input type="file" name="certificate_files[]" class="nb-input" accept=".pdf,.jpg,.jpeg,.png" multiple>
                    <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> Anda dapat memilih hingga 5 file sekaligus
                    </div>
                </div>

                <!-- Warning -->
                <div class="nb-alert nb-alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Perhatian:</strong> Pastikan semua data yang Anda masukkan sudah benar. Setelah mengirim formulir, Anda hanya dapat mengedit jika status masih "Pending".
                    </div>
                </div>

                <!-- Submit -->
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="<?= APP_URL ?>/dashboard" class="nb-btn nb-btn-outline">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-send"></i> Kirim Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
