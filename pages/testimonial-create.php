<?php
$pageTitle = 'Kirim Testimoni';
$user = auth();

// Check if user has completed internship
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = ? AND internship_status = 'completed'");
$stmt->execute([$user['id']]);
$registration = $stmt->fetch();

if (!$registration) {
    setFlash('danger', 'Anda hanya dapat mengirim testimoni setelah menyelesaikan magang.');
    redirect('/dashboard');
}

// Check if already submitted testimonial
$stmt = $db->prepare("SELECT id FROM testimonials WHERE user_id = ?");
$stmt->execute([$user['id']]);
if ($stmt->fetch()) {
    setFlash('warning', 'Anda sudah mengirim testimoni.');
    redirect('/dashboard');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Token keamanan tidak valid';
    }
    
    $content = trim($_POST['content'] ?? '');
    
    if (strlen($content) < 50) {
        $errors['content'] = 'Testimoni minimal 50 karakter';
    } elseif (strlen($content) > 1000) {
        $errors['content'] = 'Testimoni maksimal 1000 karakter';
    }
    
    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO testimonials (
                id, user_id, name, university, major, content, is_published, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
        ");
        
        if ($stmt->execute([
            generateUuid(), $user['id'], $user['name'], 
            $registration['university'], $registration['major'], 
            $content
        ])) {
            setFlash('success', 'Testimoni berhasil dikirim! Menunggu persetujuan admin.');
            redirect('/dashboard');
        } else {
            $errors['general'] = 'Gagal menyimpan testimoni';
        }
    }
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container-sm">
        <div class="mb-3">
            <a href="<?= APP_URL ?>/dashboard" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="nb-card">
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="font-size: 64px; margin-bottom: 16px;">💬</div>
                <h2 style="margin-bottom: 8px;">Bagikan Pengalaman Anda</h2>
                <p style="color: var(--gray-600);">
                    Ceritakan pengalaman magang Anda di BPS PPU untuk menginspirasi calon peserta lainnya
                </p>
            </div>

            <?php if (isset($errors['general'])): ?>
                <div class="nb-alert nb-alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- User Info (Read-only) -->
                <div class="nb-card-sm" style="background: var(--gray-50); margin-bottom: 24px;">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Nama</div>
                            <div style="font-weight: 700;"><?= e($user['name']) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Universitas</div>
                            <div style="font-weight: 700;"><?= e($registration['university']) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Program Studi</div>
                            <div style="font-weight: 700;"><?= e($registration['major']) ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Periode Magang</div>
                            <div style="font-weight: 700;">
                                <?= formatDateIndo($registration['actual_start_date'] ?: $registration['start_date']) ?> - 
                                <?= formatDateIndo($registration['actual_end_date'] ?: $registration['end_date']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="nb-form-group">
                    <label class="nb-label">
                        Testimoni <span style="color: var(--danger);">*</span>
                        <span id="content-count" style="float: right; color: var(--gray-500); font-weight: 400;">0/1000</span>
                    </label>
                    <textarea 
                        name="content" 
                        id="content" 
                        class="nb-textarea" 
                        rows="8" 
                        placeholder="Ceritakan pengalaman Anda selama magang di BPS PPU. Apa yang Anda pelajari? Bagaimana suasana kerjanya? Apa yang paling berkesan? (minimal 50 karakter)" 
                        required
                    ><?= e($_POST['content'] ?? '') ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                        <div class="nb-error"><?= e($errors['content']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="nb-alert nb-alert-info">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Catatan:</strong> Testimoni Anda akan ditinjau oleh admin sebelum dipublikasikan. Pastikan testimoni yang Anda tulis sopan dan sesuai dengan pengalaman Anda.
                    </div>
                </div>

                <!-- Submit -->
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="<?= APP_URL ?>/dashboard" class="nb-btn nb-btn-outline">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-send"></i> Kirim Testimoni
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
// Character counter
const textarea = document.getElementById('content');
const counter = document.getElementById('content-count');

if (textarea && counter) {
    const updateCounter = () => {
        const length = textarea.value.length;
        counter.textContent = length + '/1000';
        counter.style.color = length < 50 ? 'var(--danger)' : length >= 1000 ? 'var(--warning)' : 'var(--success)';
    };
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}
</script>

<?php include 'includes/footer.php'; ?>
