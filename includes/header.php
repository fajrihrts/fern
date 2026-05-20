<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Manajemen Magang BPS Kabupaten Penajam Paser Utara">
    <meta name="theme-color" content="#FFEB3B">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('img/brand.png') ?>">
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        /* Page Transition Styles */
        body {
            opacity: 0;
            animation: fadeIn 0.3s ease-in forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        
        /* Loading Overlay */
        #pageLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gray-50);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        
        #pageLoadingOverlay.active {
            display: flex;
            opacity: 1;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--gray-300);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Page Loading Overlay -->
    <div id="pageLoadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Testimonial Modal Script -->
    <script src="<?= asset('js/testimonial-modal.js') ?>"></script>
    <!-- Navbar -->
    <nav class="nb-nav">
        <div class="nb-nav-container">
            <a href="<?= APP_URL ?>/" class="nb-nav-brand">
                <img src="<?= asset('img/brand.png') ?>" alt="BPS PPU" onerror="this.style.display='none'">
            </a>
            
            <button class="nb-nav-toggle" aria-label="Toggle menu">
                <i class="bi bi-list"></i>
            </button>
            
            <ul class="nb-nav-menu">
                <li><a href="<?= APP_URL ?>/" class="nb-nav-link <?= $path === '/' ? 'active' : '' ?>">Beranda</a></li>
                <li><a href="<?= APP_URL ?>/blog" class="nb-nav-link <?= strpos($path, '/blog') === 0 || strpos($path, '/post') === 0 ? 'active' : '' ?>">Info & Pengumuman</a></li>
                <li><a href="<?= APP_URL ?>/tentang" class="nb-nav-link <?= $path === '/tentang' ? 'active' : '' ?>">Tentang Program</a></li>
                
                <?php if (isAuth()): ?>
                    <?php $user = auth(); ?>
                    <li x-data="{ open: false }" style="position: relative;">
                        <button @click="open = !open" class="nb-nav-link" style="display: flex; align-items: center; gap: 8px; background: none; border: none; cursor: pointer; color: var(--white);">
                            <?php if ($user['profile_photo']): ?>
                                <img src="<?= upload($user['profile_photo']) ?>" alt="<?= e($user['name']) ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--primary); background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: var(--black);">
                                    <?= getInitials($user['name']) ?>
                                </div>
                            <?php endif; ?>
                            <span><?= e($user['name']) ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-transition style="position: absolute; top: 100%; right: 0; margin-top: 8px; background: rgba(31, 41, 55, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 3px solid rgba(0, 0, 0, 0.5); border-radius: 12px; box-shadow: 4px 4px 20px rgba(0, 0, 0, 0.3); min-width: 200px; z-index: 1000;">
                            <?php if ($user['role'] === 'peserta'): ?>
                                <a href="<?= APP_URL ?>/dashboard" class="nb-nav-link" style="border-bottom: 2px solid rgba(75, 85, 99, 0.5); color: var(--white);">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="<?= APP_URL ?>/profile/edit" class="nb-nav-link" style="border-bottom: 2px solid rgba(75, 85, 99, 0.5); color: var(--white);">
                                    <i class="bi bi-person-circle"></i> Edit Profil
                                </a>
                            <?php else: ?>
                                <a href="<?= APP_URL ?>/admin" class="nb-nav-link" style="border-bottom: 2px solid rgba(75, 85, 99, 0.5); color: var(--white);">
                                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                                </a>
                            <?php endif; ?>
                            <form method="POST" action="<?= APP_URL ?>/logout" style="margin: 0;">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <button type="submit" class="nb-nav-link" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; color: var(--danger);">
                                    <i class="bi bi-box-arrow-right"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?= APP_URL ?>/daftar" class="nb-btn nb-btn-primary nb-btn-sm">Daftar Sekarang</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="nb-alert nb-alert-<?= $flash['type'] ?>">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        </div>
    <?php endif; ?>
