<?php
$postId = $_GET['id'] ?? '';

if (empty($postId)) {
    redirect('/blog');
}

// Get post
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM posts WHERE id = ? AND is_published = 1");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    redirect('/blog');
}

$pageTitle = $post['title'];

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <!-- Back Button -->
        <div class="mb-3">
            <a href="<?= APP_URL ?>/blog" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali ke Blog
            </a>
        </div>
        
        <!-- 2 Column Layout -->
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; align-items: start;">
            <!-- Main Content (Left) -->
            <div>
                <!-- Post Content -->
                <article class="nb-card">
                    <!-- Post Header -->
                    <div style="margin-bottom: 24px;">
                        <h1 style="margin-bottom: 16px; line-height: 1.3;"><?= e($post['title']) ?></h1>
                        
                        <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 16px; border-bottom: 3px solid var(--gray-200);">
                            <div style="font-size: 14px; color: var(--gray-600); font-weight: 600;">
                                <i class="bi bi-calendar"></i> <?= formatDateIndo($post['published_at']) ?>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <span style="font-size: 12px; color: var(--gray-500); font-weight: 600;">Bagikan:</span>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(APP_URL . '/post/' . $post['id']) ?>" target="_blank" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #1877F2; color: white; border-radius: 8px; border: 2px solid #000; text-decoration: none; font-size: 14px;">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode(APP_URL . '/post/' . $post['id']) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #000; color: white; border-radius: 8px; border: 2px solid #000; text-decoration: none; font-size: 14px;">
                                    <i class="bi bi-twitter-x"></i>
                                </a>
                                <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . APP_URL . '/post/' . $post['id']) ?>" target="_blank" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: #25D366; color: white; border-radius: 8px; border: 2px solid #000; text-decoration: none; font-size: 14px;">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($post['image']): ?>
                        <img src="<?= upload($post['image']) ?>" alt="<?= e($post['title']) ?>" style="width: 100%; max-height: 500px; object-fit: cover; border-radius: 12px; border: 3px solid #000; margin-bottom: 32px;">
                    <?php endif; ?>
                    
                    <!-- Post Body -->
                    <div class="post-content" style="line-height: 1.8; font-size: 16px; color: var(--gray-800);">
                        <?= displayHtml($post['content']) ?>
                    </div>
                </article>
            </div>
            
            <!-- Sidebar (Right) -->
            <div style="position: sticky; top: 70px;">
                <div class="nb-card" style="padding: 20px;">
                    <h4 style="margin-bottom: 20px; font-size: 18px;">Berita Lainnya</h4>
                    
                    <?php
                    $stmt = $db->prepare("
                        SELECT * FROM posts 
                        WHERE is_published = 1 AND id != ? 
                        ORDER BY published_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute([$postId]);
                    $relatedPosts = $stmt->fetchAll();
                    ?>
                    
                    <?php if (count($relatedPosts) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($relatedPosts as $related): ?>
                                <a href="<?= APP_URL ?>/post/<?= $related['id'] ?>" style="text-decoration: none; color: inherit; display: block; padding-bottom: 16px; border-bottom: 2px solid var(--gray-200); transition: all 0.2s;" onmouseover="this.style.transform='translateX(4px)'" onmouseout="this.style.transform='translateX(0)'">
                                    <?php if ($related['image']): ?>
                                        <img src="<?= upload($related['image']) ?>" alt="<?= e($related['title']) ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; border: 2px solid #000;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 120px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 8px; margin-bottom: 12px; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                                            <i class="bi bi-newspaper"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div style="font-size: 11px; color: var(--gray-500); font-weight: 600; margin-bottom: 6px;">
                                        <i class="bi bi-calendar"></i> <?= formatDateIndo($related['published_at']) ?>
                                    </div>
                                    <h6 style="margin-bottom: 0; font-size: 14px; line-height: 1.4; font-weight: 700;">
                                        <?= e($related['title']) ?>
                                    </h6>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        
                        <a href="<?= APP_URL ?>/blog" class="nb-btn nb-btn-outline nb-btn-sm" style="width: 100%; justify-content: center; margin-top: 16px;">
                            Lihat Semua Berita <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <p style="color: var(--gray-500); font-size: 14px; text-align: center;">Belum ada berita lainnya</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
