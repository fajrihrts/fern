<?php
$pageTitle = 'Edit Profil';
$user = auth();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Token keamanan tidak valid';
    }
    
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $errors['name'] = 'Nama lengkap wajib diisi';
    } elseif (strlen($name) > 255) {
        $errors['name'] = 'Nama terlalu panjang';
    }
    
    // Handle profile photo upload
    $photoPath = $user['profile_photo'];
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $result = uploadFile($_FILES['profile_photo'], 'profile_photos', ALLOWED_IMAGE_TYPES, MAX_PROFILE_PHOTO_SIZE);
        if ($result['success']) {
            // Delete old photo
            if ($user['profile_photo']) {
                deleteFile($user['profile_photo']);
            }
            $photoPath = $result['path'];
        } else {
            $errors['profile_photo'] = $result['error'];
        }
    }
    
    if (empty($errors)) {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE users SET name = ?, profile_photo = ?, updated_at = NOW() WHERE id = ?");
        
        if ($stmt->execute([$name, $photoPath, $user['id']])) {
            $_SESSION['user_name'] = $name;
            setFlash('success', 'Profil berhasil diperbarui!');
            redirect('/dashboard');
        } else {
            $errors['general'] = 'Gagal memperbarui profil';
        }
    }
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container-sm">
        <div class="mb-3">
            <a href="<?= APP_URL ?>/dashboard" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="nb-card">
            <h2 class="mb-3">Edit Profil</h2>
            <p style="color: var(--gray-600); margin-bottom: 32px;">
                Perbarui informasi profil Anda
            </p>

            <?php if (isset($errors['general'])): ?>
                <div class="nb-alert nb-alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <!-- Current Photo -->
                <div class="nb-form-group">
                    <label class="nb-label">Foto Profil Saat Ini</label>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <?php if ($user['profile_photo']): ?>
                            <img src="<?= upload($user['profile_photo']) ?>" alt="<?= e($user['name']) ?>" style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid #000; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 36px;">
                                <?= getInitials($user['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upload New Photo -->
                <div class="nb-form-group">
                    <label class="nb-label">Ganti Foto Profil <span style="color: var(--gray-500); font-weight: 400;">- Opsional</span></label>
                    <input type="file" name="profile_photo" class="nb-input" accept="image/jpeg,image/png,image/jpg">
                    <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> Format: JPG, PNG. Maksimal 2MB
                    </div>
                    <?php if (isset($errors['profile_photo'])): ?>
                        <div class="nb-error"><?= e($errors['profile_photo']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Name -->
                <div class="nb-form-group">
                    <label class="nb-label">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                    <input type="text" name="name" class="nb-input" value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="nb-error"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email (disabled) -->
                <div class="nb-form-group">
                    <label class="nb-label">Email</label>
                    <input type="email" class="nb-input" value="<?= e($user['email']) ?>" disabled>
                    <div style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">
                        <i class="bi bi-info-circle"></i> Email tidak dapat diubah
                    </div>
                </div>

                <!-- Submit -->
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <a href="<?= APP_URL ?>/dashboard" class="nb-btn nb-btn-outline">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="nb-btn nb-btn-primary">
                        <i class="bi bi-check-circle"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
