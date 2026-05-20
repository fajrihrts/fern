<?php
$pageTitle = 'Beranda';

// Prevent all types of cache
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Clear PHP opcache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Get latest 3 posts - force fresh data
$db = getDbConnection();
$stmt = $db->query("
    SELECT * FROM posts 
    WHERE is_published = 1 
    ORDER BY published_at DESC 
    LIMIT 3
");
$latestPosts = $stmt->fetchAll();

// Debug: log what we got
error_log('Home page - Latest posts count: ' . count($latestPosts));
foreach ($latestPosts as $post) {
    error_log('Home page - Post: ' . $post['title']);
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); height: 100vh; display: flex; align-items: center; position: relative; overflow: hidden; margin-top: -52px; padding-top: 52px;">
    <!-- Animated gradient blobs -->
    <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(255,235,59,0.2) 0%, transparent 70%); border-radius: 50%; top: -100px; right: -100px; animation: float 6s ease-in-out infinite;"></div>
    <div style="position: absolute; width: 300px; height: 300px; background: radial-gradient(circle, rgba(0,229,255,0.2) 0%, transparent 70%); border-radius: 50%; bottom: -50px; left: -50px; animation: float 8s ease-in-out infinite reverse;"></div>
    
    <div class="container" style="width: 100%;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
            <div style="color: white; z-index: 1;">
                <h1 style="font-size: 3.5rem; margin-bottom: 24px; line-height: 1.1;">
                    Bangun Karier Data & Statistik dari <span style="color: var(--primary);">Pengalaman Nyata</span> di BPS
                </h1>
                <p style="font-size: 1.25rem; margin-bottom: 32px; color: #cbd5e1; font-weight: 500; line-height: 1.6;">
                    Bergabunglah dengan program magang di Badan Pusat Statistik Kabupaten Penajam Paser Utara dan dapatkan pengalaman langsung dalam pengolahan data statistik.
                </p>
                
                <?php if (isAuth()): ?>
                    <?php 
                    $user = auth();
                    $dashboardUrl = ($user['role'] === 'admin' || $user['role'] === 'super_admin') 
                        ? APP_URL . '/admin' 
                        : APP_URL . '/dashboard';
                    ?>
                    <a href="<?= $dashboardUrl ?>" class="nb-btn nb-btn-primary nb-btn-lg">
                        <i class="bi bi-speedometer2"></i> Lihat Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/daftar" class="nb-btn nb-btn-primary nb-btn-lg">
                        <i class="bi bi-rocket-takeoff"></i> Daftar Sekarang
                    </a>
                <?php endif; ?>
            </div>
            
            <div style="z-index: 1; display: none;" class="hero-image-desktop">
                <img src="<?= asset('img/avatar.png') ?>" alt="Magang BPS" style="width: 100%; max-width: 500px; margin: 0 auto; display: block;" onerror="this.parentElement.style.display='none'">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Mengapa Magang di BPS PPU?</h2>
        
        <div class="grid grid-cols-3 gap-3">
            <!-- Feature 1 -->
            <div class="nb-card nb-animate-pop">
                <div style="width: 60px; height: 60px; background: var(--primary); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="bi bi-briefcase" style="font-size: 28px;"></i>
                </div>
                <h4 style="margin-bottom: 12px;">Pengalaman Nyata</h4>
                <p style="color: var(--gray-600);">Terlibat langsung dalam proyek pengolahan data statistik dan survei lapangan yang berdampak pada kebijakan daerah.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="nb-card nb-animate-pop">
                <div style="width: 60px; height: 60px; background: var(--accent); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="bi bi-people" style="font-size: 28px;"></i>
                </div>
                <h4 style="margin-bottom: 12px;">Mentoring Terarah</h4>
                <p style="color: var(--gray-600);">Dibimbing langsung oleh statistisi profesional dengan pengalaman puluhan tahun di bidang data dan statistik.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="nb-card nb-animate-pop">
                <div style="width: 60px; height: 60px; background: var(--success); border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="bi bi-award" style="font-size: 28px;"></i>
                </div>
                <h4 style="margin-bottom: 12px;">Sertifikat Resmi</h4>
                <p style="color: var(--gray-600);">Dapatkan sertifikat resmi dari BPS yang diakui secara nasional sebagai bukti pengalaman magang Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest News Section -->
<?php if (count($latestPosts) > 0): ?>
<section class="py-5" style="background: var(--gray-100);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
            <h2>Info & Pengumuman Terbaru</h2>
            <a href="<?= APP_URL ?>/blog" class="nb-btn nb-btn-outline">
                Lihat Semua <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-3 gap-3">
            <?php foreach ($latestPosts as $post): ?>
                <a href="<?= APP_URL ?>/post/<?= $post['id'] ?>" class="nb-card nb-animate-pop" style="text-decoration: none; color: inherit; display: block; transition: all 0.2s;">
                    <?php if ($post['image']): ?>
                        <img src="<?= upload($post['image']) ?>?v=<?= time() ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; border: 3px solid #000;">
                    <?php endif; ?>
                    
                    <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 8px;">
                        <i class="bi bi-calendar"></i> <?= formatDateIndo($post['published_at']) ?>
                    </div>
                    
                    <h4 style="margin-bottom: 12px;"><?= e($post['title']) ?></h4>
                    
                    <p style="color: var(--gray-600); margin-bottom: 16px;">
                        <?= truncate(strip_tags($post['content']), 120) ?>
                    </p>
                    
                    <div style="font-weight: 700; color: var(--primary);">
                        Baca Selengkapnya <i class="bi bi-arrow-right"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@media (min-width: 769px) {
    .hero-image-desktop { display: block !important; }
}

@media (max-width: 768px) {
    /* Hero section adjustments */
    section[style*="linear-gradient"] {
        height: auto !important;
        min-height: 100vh !important;
        padding-top: 80px !important;
        padding-bottom: 60px !important;
    }
    
    section[style*="linear-gradient"] .container {
        padding-left: 20px !important;
        padding-right: 20px !important;
    }
    
    section[style*="linear-gradient"] > div > div {
        grid-template-columns: 1fr !important;
    }
    
    section[style*="linear-gradient"] h1 {
        font-size: 2rem !important;
        line-height: 1.2 !important;
        margin-bottom: 20px !important;
    }
    
    section[style*="linear-gradient"] p {
        font-size: 1rem !important;
        line-height: 1.6 !important;
        margin-bottom: 28px !important;
    }
    
    /* Features section - adjust grid */
    section.py-5 .grid-cols-3 {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
}

@media (max-width: 480px) {
    section[style*="linear-gradient"] {
        padding-top: 70px !important;
        padding-bottom: 50px !important;
        min-height: auto !important;
    }
    
    section[style*="linear-gradient"] .container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }
    
    section[style*="linear-gradient"] h1 {
        font-size: 1.65rem !important;
        line-height: 1.25 !important;
        margin-bottom: 16px !important;
    }
    
    section[style*="linear-gradient"] h1 span {
        display: inline !important;
    }
    
    section[style*="linear-gradient"] p {
        font-size: 0.9rem !important;
        line-height: 1.5 !important;
        margin-bottom: 24px !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
