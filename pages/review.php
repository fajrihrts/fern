<?php
$pageTitle = 'Testimoni Alumni';

// Get all published testimonials
$db = getDbConnection();
$stmt = $db->query("
    SELECT * FROM testimonials 
    WHERE is_published = 1 
    ORDER BY created_at DESC
");
$testimonials = $stmt->fetchAll();

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 style="margin-bottom: 16px;">Testimoni Alumni Magang</h1>
            <p style="color: var(--gray-600); font-size: 1.1rem;">Dengarkan pengalaman mereka yang telah menyelesaikan program magang di BPS PPU</p>
        </div>
        
        <?php if (count($testimonials) > 0): ?>
            <div class="grid grid-cols-3 gap-3">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="nb-card testimonial-card" 
                         data-testimonial='<?= json_encode([
                             'name' => $testimonial['name'],
                             'university' => $testimonial['campus'] ?: $testimonial['university'],
                             'major' => $testimonial['major'] ?? '',
                             'image' => $testimonial['image'] ? upload($testimonial['image']) : '',
                             'initials' => getInitials($testimonial['name']),
                             'content' => nl2br(e($testimonial['text'] ?: $testimonial['content']))
                         ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                         style="cursor: pointer; transition: all 0.2s;">
                        <div style="text-align: center; margin-bottom: 16px;">
                            <?php if ($testimonial['image']): ?>
                                <img src="<?= upload($testimonial['image']) ?>" alt="<?= e($testimonial['name']) ?>" style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #000; object-fit: cover; margin: 0 auto;">
                            <?php else: ?>
                                <div style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 28px; margin: 0 auto;">
                                    <?= getInitials($testimonial['name']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h5 style="text-align: center; margin-bottom: 4px;"><?= e($testimonial['name']) ?></h5>
                        
                        <?php if ($testimonial['campus'] || $testimonial['university']): ?>
                            <p style="text-align: center; color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">
                                <?= e($testimonial['campus'] ?: $testimonial['university']) ?>
                            </p>
                        <?php endif; ?>
                        
                        <p style="color: var(--gray-700); font-size: 14px; line-height: 1.6; margin-bottom: 0;">
                            <?= truncate(e($testimonial['text'] ?: $testimonial['content']), 150) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <script>
            // Attach click handlers to testimonial cards
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.testimonial-card');
                cards.forEach(card => {
                    card.addEventListener('click', function() {
                        const data = JSON.parse(this.getAttribute('data-testimonial'));
                        showTestimonial(data);
                    });
                });
            });
            </script>
        <?php else: ?>
            <!-- Empty State -->
            <div class="nb-card" style="text-align: center; padding: 80px 40px;">
                <div style="font-size: 64px; margin-bottom: 24px; opacity: 0.3;">
                    <i class="bi bi-chat-quote"></i>
                </div>
                <h3 style="margin-bottom: 12px;">Belum Ada Testimoni</h3>
                <p style="color: var(--gray-600);">Testimoni dari alumni magang akan ditampilkan di sini.</p>
            </div>
        <?php endif; ?>
        
        <!-- CTA Section -->
        <div class="nb-card mt-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); text-align: center; padding: 48px;">
            <h3 style="margin-bottom: 16px;">Ingin Berbagi Pengalaman Anda?</h3>
            <p style="margin-bottom: 24px; max-width: 600px; margin-left: auto; margin-right: auto;">
                Jika Anda adalah alumni magang BPS PPU, kami ingin mendengar cerita Anda! Bagikan pengalaman magang Anda untuk menginspirasi calon peserta lainnya.
            </p>
            <?php if (isAuth()): ?>
                <a href="<?= APP_URL ?>/testimoni/create" class="nb-btn" style="background: white;">
                    <i class="bi bi-chat-quote"></i> Kirim Testimoni
                </a>
            <?php else: ?>
                <a href="<?= APP_URL ?>/daftar" class="nb-btn" style="background: white;">
                    <i class="bi bi-rocket-takeoff"></i> Daftar Magang
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
/* Testimonial Card Hover Effect */
.testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.15);
}
</style>

<?php include 'includes/footer.php'; ?>
