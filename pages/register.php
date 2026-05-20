<?php
$pageTitle = 'Daftar Akun';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirmation = $_POST['password_confirmation'] ?? '';
    
    // Validate CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid';
    }
    
    // Validate name
    if (empty($name)) {
        $errors['name'] = 'Nama lengkap wajib diisi';
    } elseif (strlen($name) > 255) {
        $errors['name'] = 'Nama terlalu panjang (maksimal 255 karakter)';
    }
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email wajib diisi';
    } elseif (!isValidEmail($email)) {
        $errors['email'] = 'Format email tidak valid';
    }
    
    // Validate password
    if (empty($password)) {
        $errors['password'] = 'Password wajib diisi';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password minimal 8 karakter';
    } elseif ($password !== $password_confirmation) {
        $errors['password_confirmation'] = 'Konfirmasi password tidak cocok';
    }
    
    // Attempt registration
    if (empty($errors)) {
        $result = register($name, $email, $password);
        
        if ($result['success']) {
            setFlash('success', 'Akun berhasil dibuat! Silakan lengkapi formulir pendaftaran.');
            redirect('/dashboard');
        } else {
            $errors['general'] = $result['error'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body style="background: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 0;">
    
    <div style="width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 24px;">
        <div style="display: grid; grid-template-columns: 40% 60%; background: white; border: 5px solid #000; border-radius: 24px; box-shadow: 12px 12px 0 #000; overflow: hidden; min-height: 600px;">
            
            <!-- Left Panel -->
            <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 48px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
                <div style="position: absolute; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,235,59,0.2) 0%, transparent 70%); border-radius: 50%; top: -50px; right: -50px; animation: float 6s ease-in-out infinite;"></div>
                <div style="position: absolute; width: 150px; height: 150px; background: radial-gradient(circle, rgba(0,229,255,0.2) 0%, transparent 70%); border-radius: 50%; bottom: -30px; left: -30px; animation: float 8s ease-in-out infinite reverse;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div style="margin-bottom: 32px;">
                        <img src="<?= asset('img/brand.png') ?>" alt="BPS PPU" style="height: 60px; margin-bottom: 16px;" onerror="this.style.display='none'">
                        <h2 style="color: white; margin-bottom: 12px;">Daftar Akun Baru</h2>
                        <p style="color: #cbd5e1; font-size: 14px;">Ikuti 4 langkah mudah untuk memulai magang</p>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div style="display: flex; align-items: start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">1</div>
                            <div>
                                <div style="color: white; font-weight: 700; margin-bottom: 4px;">Buat Akun</div>
                                <div style="color: #cbd5e1; font-size: 13px;">Daftar dengan email aktif</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: var(--accent); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">2</div>
                            <div>
                                <div style="color: white; font-weight: 700; margin-bottom: 4px;">Lengkapi Data</div>
                                <div style="color: #cbd5e1; font-size: 13px;">Isi formulir pendaftaran</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: var(--success); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">3</div>
                            <div>
                                <div style="color: white; font-weight: 700; margin-bottom: 4px;">Verifikasi</div>
                                <div style="color: #cbd5e1; font-size: 13px;">Tunggu persetujuan admin</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: start; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: var(--info); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">4</div>
                            <div>
                                <div style="color: white; font-weight: 700; margin-bottom: 4px;">Mulai Magang</div>
                                <div style="color: #cbd5e1; font-size: 13px;">Lapor kehadiran harian</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Form -->
            <div style="padding: 48px; display: flex; flex-direction: column; justify-content: center; background: #f9fafb;">
                <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                    <h3 style="margin-bottom: 8px;">Buat Akun Peserta</h3>
                    <p style="color: var(--gray-600); margin-bottom: 32px;">Isi data diri Anda dengan lengkap</p>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="nb-alert nb-alert-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            <span><?= e($errors['general']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" x-data="{ password: '', strength: 0, strengthText: 'Lemah', strengthColor: '#FF5252' }">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
                        <div class="nb-form-group">
                            <label class="nb-label">
                                <i class="bi bi-person"></i> Nama Lengkap
                            </label>
                            <input type="text" name="name" class="nb-input" placeholder="Nama lengkap sesuai KTP" value="<?= e($_POST['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="nb-error"><?= e($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nb-form-group">
                            <label class="nb-label">
                                <i class="bi bi-envelope"></i> Alamat Email
                            </label>
                            <input type="email" name="email" class="nb-input" placeholder="nama@email.com" value="<?= e($_POST['email'] ?? '') ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="nb-error"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nb-form-group" x-data="{ show: false }">
                            <label class="nb-label">
                                <i class="bi bi-lock"></i> Password
                            </label>
                            <div style="position: relative;">
                                <input 
                                    :type="show ? 'text' : 'password'" 
                                    name="password" 
                                    class="nb-input" 
                                    placeholder="Minimal 8 karakter" 
                                    required 
                                    style="padding-right: 48px;"
                                    x-model="password"
                                    @input="
                                        let str = 0;
                                        if (password.length >= 8) str++;
                                        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) str++;
                                        if (password.match(/\d/)) str++;
                                        if (password.match(/[^a-zA-Z\d]/)) str++;
                                        
                                        if (str <= 1) { strength = 25; strengthText = 'Lemah'; strengthColor = '#FF5252'; }
                                        else if (str === 2) { strength = 50; strengthText = 'Cukup'; strengthColor = '#FFB300'; }
                                        else if (str === 3) { strength = 75; strengthText = 'Kuat'; strengthColor = '#FFEB3B'; }
                                        else { strength = 100; strengthText = 'Sangat Kuat'; strengthColor = '#00FF88'; }
                                    "
                                >
                                <button type="button" @click="show = !show" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 18px;">
                                    <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                            <div x-show="password.length > 0" style="margin-top: 8px;">
                                <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                    <div :style="`width: ${strength}%; background: ${strengthColor}; height: 100%; transition: all 0.3s;`"></div>
                                </div>
                                <div style="font-size: 12px; font-weight: 600; margin-top: 4px;" :style="`color: ${strengthColor}`" x-text="strengthText"></div>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="nb-error"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nb-form-group" x-data="{ show: false }">
                            <label class="nb-label">
                                <i class="bi bi-lock-fill"></i> Konfirmasi Password
                            </label>
                            <div style="position: relative;">
                                <input :type="show ? 'text' : 'password'" name="password_confirmation" class="nb-input" placeholder="Ketik ulang password" required style="padding-right: 48px;">
                                <button type="button" @click="show = !show" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 18px;">
                                    <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password_confirmation'])): ?>
                                <div class="nb-error"><?= e($errors['password_confirmation']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="nb-btn nb-btn-primary" style="width: 100%; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-rocket-takeoff"></i> Daftar Sekarang
                        </button>
                        
                        <div style="text-align: center;">
                            <p style="color: var(--gray-600); font-size: 14px;">
                                Sudah punya akun? 
                                <a href="<?= APP_URL ?>/login" style="color: var(--black); font-weight: 700; text-decoration: none;">
                                    Masuk di sini
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        
        <div style="text-align: center; margin-top: 24px;">
            <a href="<?= APP_URL ?>/" class="nb-btn nb-btn-outline">
                <i class="bi bi-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
    
    <style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @media (max-width: 768px) {
        body > div > div {
            grid-template-columns: 1fr !important;
        }
        body > div > div > div:first-child {
            display: none;
        }
    }
    </style>
    
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
