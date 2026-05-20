    <!-- Footer -->
    <footer class="nb-footer">
        <div class="nb-footer-content">
            <!-- About Section -->
            <div class="nb-footer-section">
                <h4>Tentang BPS PPU</h4>
                <p>Badan Pusat Statistik Kabupaten Penajam Paser Utara menyediakan data dan informasi statistik yang akurat untuk pembangunan daerah.</p>
                
                <div class="nb-footer-social">
                    <a href="<?= FACEBOOK_URL ?>" target="_blank" title="Facebook" style="background: #1877F2; color: white; border-color: #1877F2;">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="<?= INSTAGRAM_URL ?>" target="_blank" title="Instagram" style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%); color: white; border-color: #e1306c;">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="<?= TWITTER_URL ?>" target="_blank" title="Twitter/X" style="background: #000; color: white; border-color: #000;">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                    <a href="<?= YOUTUBE_URL ?>" target="_blank" title="YouTube" style="background: #FF0000; color: white; border-color: #FF0000;">
                        <i class="bi bi-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Navigation Section -->
            <div class="nb-footer-section">
                <h4>Navigasi</h4>
                <a href="<?= APP_URL ?>/">Beranda</a>
                <a href="<?= APP_URL ?>/blog">Info & Pengumuman</a>
                <a href="<?= APP_URL ?>/tentang">Tentang Program</a>
                <a href="<?= APP_URL ?>/review">Testimoni</a>
                <a href="<?= APP_URL ?>/daftar">Daftar Magang</a>
            </div>
            
            <!-- Contact Section -->
            <div class="nb-footer-section">
                <h4>Kontak</h4>
                <p>
                    <i class="bi bi-geo-alt"></i> 
                    <a href="https://maps.app.goo.gl/7swXWjYorPk62sCFA" target="_blank" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                        <?= CONTACT_ADDRESS ?>
                    </a>
                </p>
                <p><i class="bi bi-envelope"></i> <?= CONTACT_EMAIL ?></p>
                <p><i class="bi bi-telephone"></i> <?= CONTACT_PHONE ?></p>
                <a href="<?= WEBSITE_URL ?>" target="_blank"><i class="bi bi-globe"></i> Website Resmi</a>
            </div>
        </div>
        
        <div class="nb-footer-bottom">
            <div class="container">
                &copy; <?= date('Y') ?> BPS Kabupaten Penajam Paser Utara. All rights reserved.
            </div>
        </div>
    </footer>
    
    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
    
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
</body>
</html>
