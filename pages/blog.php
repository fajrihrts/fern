<?php
$pageTitle = 'Info & Pengumuman';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Get total posts
$db = getDbConnection();
$stmt = $db->query("SELECT COUNT(*) as total FROM posts WHERE is_published = 1");
$total = $stmt->fetch()['total'];
$totalPages = ceil($total / $perPage);

// Get posts
$stmt = $db->prepare("
    SELECT * FROM posts 
    WHERE is_published = 1 
    ORDER BY published_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$posts = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 style="margin-bottom: 16px;">Info & Pengumuman</h1>
            <p style="color: var(--gray-600); font-size: 1.1rem;">Berita dan informasi terbaru seputar program magang BPS PPU</p>
        </div>
        
        <?php if (count($posts) > 0): ?>
            <div class="grid grid-cols-3 gap-3">
                <?php foreach ($posts as $post): ?>
                    <a href="<?= APP_URL ?>/post/<?= $post['id'] ?>" class="nb-card nb-animate-pop" style="text-decoration: none; color: inherit; display: block;">
                        <?php if ($post['image']): ?>
                            <img src="<?= upload($post['image']) ?>" alt="<?= e($post['title']) ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 12px; margin-bottom: 16px; border: 3px solid #000;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 12px; margin-bottom: 16px; border: 3px solid #000; display: flex; align-items: center; justify-content: center; font-size: 48px;">
                                <i class="bi bi-newspaper"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 8px;">
                            <i class="bi bi-calendar"></i> <?= formatDateIndo($post['published_at']) ?>
                        </div>
                        
                        <h4 style="margin-bottom: 12px;"><?= e($post['title']) ?></h4>
                        
                        <p style="color: var(--gray-600); margin-bottom: 16px;">
                            <?= truncate(strip_tags($post['content']), 120) ?>
                        </p>
                        
                        <div style="font-weight: 700; color: var(--black);">
                            Baca Selengkapnya <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="display: flex; justify-content: center; gap: 8px; margin-top: 48px;">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="nb-btn nb-btn-outline">
                            <i class="bi bi-chevron-left"></i> Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="nb-btn <?= $i === $page ? 'nb-btn-primary' : 'nb-btn-outline' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="nb-btn nb-btn-outline">
                            Selanjutnya <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="nb-card" style="text-align: center; padding: 80px 40px;">
                <div style="font-size: 64px; margin-bottom: 24px; opacity: 0.3;">
                    <i class="bi bi-newspaper"></i>
                </div>
                <h3 style="margin-bottom: 12px;">Belum Ada Berita</h3>
                <p style="color: var(--gray-600);">Berita dan pengumuman akan ditampilkan di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
