<?php
$pageTitle = 'Masuk';

// Check if already logged in (but allow form processing first)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    requireGuest();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Rate limiting by IP
    $ip = RateLimiter::getClientIp();
    $rateLimitKey = 'login:' . $ip;
    
    // Check rate limit (5 attempts per minute)
    if (RateLimiter::tooManyAttempts($rateLimitKey, 5, 1)) {
        $seconds = RateLimiter::availableIn($rateLimitKey, 1);
        $errors['general'] = "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.";
        
        Logger::warning('Login rate limit exceeded', [
            'ip' => $ip,
            'email' => $email
        ]);
    } else {
        // Validate CSRF
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Token keamanan tidak valid';
        }
        
        // Validate input
        if (empty($email)) {
            $errors['email'] = 'Alamat email wajib diisi';
        } elseif (!isValidEmail($email)) {
            $errors['email'] = 'Format email tidak valid';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password wajib diisi';
        }
        
        // Attempt login
        if (empty($errors)) {
            // Hit rate limiter
            RateLimiter::hit($rateLimitKey, 1);
            
            $result = login($email, $password, $remember);
            
            if ($result['success']) {
                // Clear rate limit on successful login
                RateLimiter::clear($rateLimitKey);
                
                $user = $result['user'];
                
                // Redirect based on role
                if (in_array($user['role'], ['admin', 'super_admin'])) {
                    redirect('/admin');
                } else {
                    redirect('/dashboard');
                }
            } else {
                $errors['general'] = $result['error'];
                
                // Log failed attempt
                Logger::warning('Failed login attempt', [
                    'email' => $email,
                    'ip' => $ip,
                    'retries_left' => RateLimiter::retriesLeft($rateLimitKey, 5, 1)
                ]);
            }
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
<body style="background: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
    
    <div style="width: 100%; max-width: 1200px; margin: 40px auto; padding: 0 24px;">
        <div style="display: grid; grid-template-columns: 40% 60%; background: white; border: 5px solid #000; border-radius: 24px; box-shadow: 12px 12px 0 #000; overflow: hidden; min-height: 600px;">
            
            <!-- Left Panel - Branding -->
            <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 48px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
                <!-- Animated blobs -->
                <div style="position: absolute; width: 200px; height: 200px; background: radial-gradient(circle, rgba(255,235,59,0.2) 0%, transparent 70%); border-radius: 50%; top: -50px; right: -50px; animation: float 6s ease-in-out infinite;"></div>
                <div style="position: absolute; width: 150px; height: 150px; background: radial-gradient(circle, rgba(0,229,255,0.2) 0%, transparent 70%); border-radius: 50%; bottom: -30px; left: -30px; animation: float 8s ease-in-out infinite reverse;"></div>
                
                <div style="position: relative; z-index: 1;">
                    <div style="margin-bottom: 32px;">
                        <img src="<?= asset('img/brand.png') ?>" alt="BPS PPU" style="height: 60px; margin-bottom: 16px;" onerror="this.style.display='none'">
                        <h2 style="color: white; margin-bottom: 12px;">Portal e-Registrasi</h2>
                        <p style="color: #cbd5e1; font-size: 14px;">Magang BPS Kabupaten Penajam Paser Utara</p>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span class="nb-badge" style="background: var(--primary);">
                            <i class="bi bi-shield-check"></i> Aman
                        </span>
                        <span class="nb-badge" style="background: var(--accent);">
                            <i class="bi bi-lightning"></i> Cepat
                        </span>
                        <span class="nb-badge" style="background: var(--success);">
                            <i class="bi bi-check-circle"></i> Mudah
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Form -->
            <div style="padding: 48px; display: flex; flex-direction: column; justify-content: center; background: #f9fafb;">
                <div style="max-width: 400px; margin: 0 auto; width: 100%;">
                    <h3 style="margin-bottom: 8px;">Selamat Datang Kembali!</h3>
                    <p style="color: var(--gray-600); margin-bottom: 32px;">Masuk ke akun Anda untuk melanjutkan</p>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="nb-alert nb-alert-danger">
                            <i class="bi bi-exclamation-circle"></i>
                            <span><?= e($errors['general']) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        
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
                                <input :type="show ? 'text' : 'password'" name="password" class="nb-input" placeholder="Masukkan password" required style="padding-right: 48px;">
                                <button type="button" @click="show = !show" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 18px;">
                                    <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                                </button>
                            </div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="nb-error"><?= e($errors['password']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nb-form-group" style="display: flex; justify-content: space-between; align-items: center;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="remember" style="width: 18px; height: 18px; cursor: pointer;">
                                <span>Ingat saya</span>
                            </label>
                            <a href="<?= APP_URL ?>/forgot-password" style="color: var(--primary); font-weight: 600; text-decoration: none; font-size: 14px;">
                                Lupa Password?
                            </a>
                        </div>
                        
                        <button type="submit" class="nb-btn nb-btn-primary" style="width: 100%; justify-content: center; margin-bottom: 16px;">
                            <i class="bi bi-box-arrow-in-right"></i> Masuk
                        </button>
                        
                        <div style="text-align: center;">
                            <p style="color: var(--gray-600); font-size: 14px;">
                                Belum punya akun? 
                                <a href="<?= APP_URL ?>/register" style="color: var(--black); font-weight: 700; text-decoration: none;">
                                    Daftar Sekarang
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        
        <!-- Back to home -->
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
