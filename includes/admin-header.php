<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <link rel="icon" type="image/png" href="<?= asset('img/favicon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
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
<body style="background: var(--gray-50); padding-top: 56px;">
    
<?php
// Ensure variables are set
$user = $user ?? auth();
$path = $path ?? getCurrentPath();
?>

    <!-- Page Loading Overlay -->
    <div id="pageLoadingOverlay">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Admin Navbar -->
    <nav style="background: var(--gray-900); border-bottom: 3px solid var(--black); box-shadow: 0 3px 0 var(--black); position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="container" style="padding: 8px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="<?= APP_URL ?>/admin" style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--white); font-weight: 900; font-size: 16px;">
                    <img src="<?= asset('img/brand.png') ?>" alt="BPS PPU" style="height: 32px;" onerror="this.style.display='none'">
                    <span>Admin Panel</span>
                </a>
                
                <div style="display: flex; gap: 2px;">
                    <a href="<?= APP_URL ?>/admin" class="nb-nav-link <?= strpos($path ?? '', '/admin') === 0 && ($path ?? '') === '/admin' ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="<?= APP_URL ?>/admin/registrations" class="nb-nav-link <?= strpos($path ?? '', '/admin/registrations') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-file-earmark-text"></i> Pendaftaran
                    </a>
                    <a href="<?= APP_URL ?>/admin/attendance" class="nb-nav-link <?= strpos($path ?? '', '/admin/attendance') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-calendar-check"></i> Kehadiran
                    </a>
                    <a href="<?= APP_URL ?>/admin/posts" class="nb-nav-link <?= strpos($path ?? '', '/admin/posts') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-newspaper"></i> Berita
                    </a>
                    <a href="<?= APP_URL ?>/admin/testimonials" class="nb-nav-link <?= strpos($path ?? '', '/admin/testimonials') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-chat-quote"></i> Testimoni
                    </a>
                    <a href="<?= APP_URL ?>/admin/activity-logs" class="nb-nav-link <?= strpos($path ?? '', '/admin/activity-logs') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                        <i class="bi bi-clock-history"></i> Log
                    </a>
                    <?php if (($user['role'] ?? '') === 'super_admin'): ?>
                        <a href="<?= APP_URL ?>/admin/users" class="nb-nav-link <?= strpos($path ?? '', '/admin/users') === 0 ? 'active' : '' ?>" style="padding: 6px 12px; font-size: 13px;">
                            <i class="bi bi-people"></i> Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div x-data="{ open: false }" style="position: relative;">
                <button @click="open = !open" style="display: flex; align-items: center; gap: 8px; background: none; border: none; cursor: pointer; padding: 4px 10px; border-radius: 8px; transition: all 0.2s; color: var(--white);" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">
                    <?php if (!empty($user['profile_photo'] ?? '')): ?>
                        <img src="<?= upload($user['profile_photo']) ?>" alt="<?= e($user['name'] ?? 'User') ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--primary); object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--primary); background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--black); font-size: 12px;">
                            <?= getInitials($user['name'] ?? 'Admin') ?>
                        </div>
                    <?php endif; ?>
                    <div style="text-align: left;">
                        <div style="font-weight: 700; font-size: 13px;"><?= e($user['name'] ?? 'Admin') ?></div>
                        <div style="font-size: 11px; color: var(--gray-400);"><?= ($user['role'] ?? '') === 'super_admin' ? 'Super Admin' : 'Admin' ?></div>
                    </div>
                    <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                </button>
                
                <div x-show="open" @click.away="open = false" x-transition style="position: absolute; top: 100%; right: 0; margin-top: 8px; background: rgba(31, 41, 55, 0.95); backdrop-filter: blur(10px); border: 2px solid rgba(0, 0, 0, 0.5); border-radius: 10px; box-shadow: 4px 4px 20px rgba(0, 0, 0, 0.3); min-width: 180px; z-index: 1000;">
                    <a href="<?= APP_URL ?>/" class="nb-nav-link" style="border-bottom: 1px solid rgba(75, 85, 99, 0.5); color: var(--white); padding: 8px 14px; font-size: 13px;">
                        <i class="bi bi-house"></i> Lihat Website
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/logout" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <button type="submit" class="nb-nav-link" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; color: var(--danger); padding: 8px 14px; font-size: 13px;">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
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

    <!-- Custom Confirmation Modal -->
    <script src="<?= asset('js/confirmation-modal.js') ?>"></script>

    <!-- Page Transition Script -->
    <script>
        // Smooth page transitions
        document.addEventListener('DOMContentLoaded', function() {
            // Get all navigation links
            const navLinks = document.querySelectorAll('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript:"])');
            const loadingOverlay = document.getElementById('pageLoadingOverlay');
            
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Skip if it's a form submit button or has special handling
                    if (this.closest('form') || this.hasAttribute('onclick')) {
                        return;
                    }
                    
                    // Skip if it's a download/export link
                    const href = this.getAttribute('href') || '';
                    if (href.includes('/export/') || href.includes('download') || href.includes('.csv') || href.includes('.pdf') || href.includes('.xlsx')) {
                        return;
                    }
                    
                    // Skip if link has download attribute
                    if (this.hasAttribute('download')) {
                        return;
                    }
                    
                    // Skip if it's the same page
                    const currentPath = window.location.pathname;
                    const linkPath = new URL(this.href, window.location.origin).pathname;
                    if (currentPath === linkPath) {
                        return;
                    }
                    
                    // Show loading overlay
                    if (loadingOverlay) {
                        e.preventDefault();
                        loadingOverlay.classList.add('active');
                        
                        // Navigate after a short delay
                        setTimeout(() => {
                            window.location.href = this.href;
                        }, 150);
                    }
                });
            });
            
            // Hide loading overlay when page is fully loaded
            window.addEventListener('pageshow', function() {
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('active');
                }
            });
            
            // Handle browser back/forward buttons
            window.addEventListener('popstate', function() {
                if (loadingOverlay) {
                    loadingOverlay.classList.add('active');
                }
            });
        });
    </script>
