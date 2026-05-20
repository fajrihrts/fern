<?php
$pageTitle = 'Tentang Program Magang';

// Get real statistics
$db = getDbConnection();

// Total pendaftar
$stmt = $db->query("SELECT COUNT(*) as count FROM registrations");
$totalPendaftar = $stmt->fetch()['count'];

// Alumni (completed)
$stmt = $db->query("SELECT COUNT(*) as count FROM registrations WHERE internship_status = 'completed'");
$totalAlumni = $stmt->fetch()['count'];

// Universitas (distinct)
$stmt = $db->query("SELECT COUNT(DISTINCT university) as count FROM registrations");
$totalUniversitas = $stmt->fetch()['count'];

// Testimoni published
$stmt = $db->query("SELECT COUNT(*) as count FROM testimonials WHERE is_published = 1");
$totalTestimoniPositif = $stmt->fetch()['count'];

// Get 3 latest testimonials
$stmt = $db->query("
    SELECT * FROM testimonials 
    WHERE is_published = 1 
    ORDER BY created_at DESC 
    LIMIT 3
");
$testimonials = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Header Section -->
<section style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; color: white; margin-top: -52px; padding-top: 52px; position: relative; overflow: hidden; padding-bottom: 60px;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1 style="color: white; margin-bottom: 16px; font-size: 2.5rem;">Tentang Program Magang</h1>
            <p style="font-size: 1.1rem; color: #cbd5e1; line-height: 1.6; margin-bottom: 40px;">
                Pahami persyaratan, alur, dan cara pendaftaran program pemagang PKL di BPS Kabupaten PPU
            </p>
            
            <!-- Statistics -->
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; max-width: 700px; margin: 0 auto;">
                <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 4px;">
                        <?= $totalPendaftar ?>+
                    </div>
                    <div style="font-weight: 600; font-size: 12px; color: #cbd5e1;">Total Pendaftar</div>
                </div>
                <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 4px;">
                        <?= $totalAlumni ?>+
                    </div>
                    <div style="font-weight: 600; font-size: 12px; color: #cbd5e1;">Alumni Magang</div>
                </div>
                <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 4px;">
                        <?= $totalUniversitas ?>+
                    </div>
                    <div style="font-weight: 600; font-size: 12px; color: #cbd5e1;">Asal Universitas</div>
                </div>
                <div style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 4px;">
                        <?= $totalTestimoniPositif ?>+
                    </div>
                    <div style="font-weight: 600; font-size: 12px; color: #cbd5e1;">Testimoni Alumni</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Syarat Pendaftaran & Alur Online -->
<section class="py-5">
    <div class="container">
        <div class="requirements-grid" style="display: grid; grid-template-columns: 1fr 450px; gap: 40px; align-items: start;">
            <!-- Syarat Pendaftaran (Left) -->
            <div class="requirements-section">
                <h2 style="margin-bottom: 12px;">SYARAT PENDAFTARAN</h2>
                <p style="color: var(--gray-600); margin-bottom: 32px; line-height: 1.6;">
                    Berikut adalah syarat-syarat yang harus dipenuhi untuk pendaftaran online di Sistem Informasi Magang BPS Kabupaten PPU
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Syarat 1 -->
                    <div style="display: flex; gap: 16px;">
                        <div style="width: 48px; height: 48px; background: var(--black); border: 3px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-person-check" style="color: var(--primary); font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 style="margin-bottom: 8px;">Mahasiswa Aktif</h5>
                            <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                                Peserta merupakan mahasiswa aktif yang terdaftar di PDDIKTI dan memiliki Nomor Induk Mahasiswa (NIM) aktif.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Syarat 2 -->
                    <div style="display: flex; gap: 16px;">
                        <div style="width: 48px; height: 48px; background: var(--black); border: 3px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-file-earmark-text" style="color: var(--primary); font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 style="margin-bottom: 8px;">Surat Pengantar Institusi</h5>
                            <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                                Memiliki surat pengantar resmi dari kampus atau fakultas yang ditujukan kepada Kepala BPS Kabupaten PPU.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Syarat 3 -->
                    <div style="display: flex; gap: 16px;">
                        <div style="width: 48px; height: 48px; background: var(--black); border: 3px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-file-pdf" style="color: var(--primary); font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 style="margin-bottom: 8px;">Proposal Magang</h5>
                            <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                                Proposal magang yang berisi tujuan, rencana kegiatan, dan timeline yang jelas. Format PDF maksimal 5 MB.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Syarat 4 -->
                    <div style="display: flex; gap: 16px;">
                        <div style="width: 48px; height: 48px; background: var(--black); border: 3px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-shield-check" style="color: var(--primary); font-size: 20px;"></i>
                        </div>
                        <div>
                            <h5 style="margin-bottom: 8px;">Komitmen Kerahasiaan</h5>
                            <p style="color: var(--gray-600); font-size: 14px; line-height: 1.6;">
                                Bersedia menjaga kerahasiaan data dan informasi BPS serta mematuhi peraturan yang berlaku.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Alur Pendaftaran Online (Right) -->
            <div class="nb-card alur-section" style="background: var(--gray-50); position: sticky; top: 70px;">
                <div style="background: var(--primary); color: var(--black); padding: 12px; border-radius: 8px; text-align: center; font-weight: 800; margin-bottom: 20px;">
                    <i class="bi bi-laptop"></i> ALUR PENDAFTARAN ONLINE
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Step 1 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">1</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Buat Akun Peserta</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Daftar akun dengan email aktif dan buat password yang kuat (min 8 karakter).
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">2</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Lengkapi Data Pendaftaran</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Isi formulir pendaftaran dengan data diri lengkap dan pastikan semua informasi benar.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">3</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Verifikasi dan Tes BPS PMI</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Verifikasi email dan lakukan tes BPS PMI sesuai jadwal yang ditentukan.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">4</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Upload Dokumen Pendukung</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Upload proposal, transkrip, surat rekomendasi, dan sertifikat pendukung lainnya.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 5 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">5</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Tunggu Verifikasi</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Tim admin akan memverifikasi berkas Anda dalam 3-5 hari kerja. Cek email secara berkala.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Step 6 -->
                    <div style="display: flex; gap: 12px; align-items: start;">
                        <div style="width: 32px; height: 32px; background: var(--primary); border: 2px solid var(--black); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; flex-shrink: 0;">6</div>
                        <div>
                            <h6 style="margin-bottom: 4px; font-size: 14px;">Pengumuman Hasil</h6>
                            <p style="color: var(--gray-600); font-size: 13px; line-height: 1.5;">
                                Pengumuman hasil seleksi akan dikirim melalui email dan bisa dicek di dashboard peserta.
                            </p>
                        </div>
                    </div>
                </div>
                
                <a href="<?= APP_URL ?>/daftar" class="nb-btn nb-btn-primary" style="width: 100%; justify-content: center; margin-top: 24px;">
                    <i class="bi bi-rocket-takeoff"></i> MAU DAFTAR SEKARANG?
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<?php if (count($testimonials) > 0): ?>
<section class="py-5" style="background: var(--gray-50);">
    <div class="container">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-block; background: var(--primary); color: var(--black); padding: 8px 20px; border-radius: 20px; border: 2px solid var(--black); font-weight: 800; font-size: 12px; margin-bottom: 16px;">
                ALUMNI ANGKATAN
            </div>
            <h2 style="margin-bottom: 12px;">KATA MEREKA</h2>
            <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">
                Pengalaman nyata dari mahasiswa yang pernah magang di BPS Kabupaten PPU
            </p>
        </div>
        
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
                     style="display: flex; flex-direction: column; height: 100%; cursor: pointer; transition: all 0.2s;">
                    <div style="flex: 1; margin-bottom: 20px;">
                        <div style="font-size: 32px; color: var(--primary); margin-bottom: 8px; line-height: 1;">"</div>
                        <p style="color: var(--gray-700); font-size: 14px; line-height: 1.7; font-style: italic;">
                            <?= truncate(e($testimonial['text'] ?: $testimonial['content']), 150) ?>
                        </p>
                        <div style="font-size: 32px; color: var(--primary); text-align: right; line-height: 1; margin-top: 8px;">"</div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 12px; padding-top: 16px; border-top: 2px solid var(--gray-200);">
                        <?php if ($testimonial['image']): ?>
                            <img src="<?= upload($testimonial['image']) ?>" alt="<?= e($testimonial['name']) ?>" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #000; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px;">
                                <?= getInitials($testimonial['name']) ?>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 14px; margin-bottom: 2px;"><?= e($testimonial['name']) ?></div>
                            <?php if ($testimonial['campus'] || $testimonial['university']): ?>
                                <div style="font-size: 12px; color: var(--gray-600);">
                                    <?= e($testimonial['campus'] ?: $testimonial['university']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
        
        <div style="text-align: center; margin-top: 32px;">
            <a href="<?= APP_URL ?>/review" class="nb-btn nb-btn-primary">
                <i class="bi bi-chat-quote"></i> LIHAT SEMUA TESTIMONI
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);">
    <div class="container">
        <div style="max-width: 700px; margin: 0 auto; text-align: center;">
            <h2 style="margin-bottom: 16px;">Siap Memulai Perjalanan Magang Anda?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 32px;">
                Bergabunglah dengan program magang BPS PPU dan dapatkan pengalaman berharga dalam dunia statistik dan data.
            </p>
            <a href="<?= APP_URL ?>/daftar" class="nb-btn nb-btn-lg" style="background: white;">
                <i class="bi bi-rocket-takeoff"></i> Daftar Sekarang
            </a>
        </div>
    </div>
</section>

<style>
/* Testimonial Card Hover Effect */
.testimonial-card:hover {
    transform: translateY(-4px);
    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.15);
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Hero Section - Make it shorter and adjust stats */
    section[style*="min-height: 100vh"] {
        min-height: auto !important;
        padding-top: 80px !important;
        padding-bottom: 40px !important;
    }
    
    section[style*="min-height: 100vh"] h1 {
        font-size: 1.75rem !important;
    }
    
    section[style*="min-height: 100vh"] p {
        font-size: 0.95rem !important;
    }
    
    /* Statistics Grid - 2 columns on mobile */
    .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
        max-width: 100% !important;
    }
    
    .stats-grid > div {
        padding: 16px 12px !important;
    }
    
    .stats-grid > div > div:first-child {
        font-size: 1.5rem !important;
    }
    
    .stats-grid > div > div:last-child {
        font-size: 11px !important;
    }
    
    /* Requirements Grid - Single column on mobile */
    .requirements-grid {
        grid-template-columns: 1fr !important;
        gap: 32px !important;
    }
    
    /* Remove sticky positioning on mobile */
    .alur-section {
        position: static !important;
        top: auto !important;
    }
    
    /* Testimonials Grid - Single column on mobile */
    section .grid-cols-3 {
        grid-template-columns: 1fr !important;
    }
    
    /* Adjust card padding on mobile */
    .nb-card {
        padding: 20px !important;
    }
    
    /* Syarat items - smaller icons and text */
    .requirements-section > div > div {
        gap: 12px !important;
    }
    
    .requirements-section > div > div > div:first-child {
        width: 40px !important;
        height: 40px !important;
    }
    
    .requirements-section > div > div > div:first-child i {
        font-size: 18px !important;
    }
    
    .requirements-section h5 {
        font-size: 1rem !important;
    }
    
    /* Alur steps - smaller on mobile */
    .alur-section > div:last-of-type > div {
        gap: 10px !important;
    }
    
    .alur-section h6 {
        font-size: 13px !important;
    }
    
    .alur-section p {
        font-size: 12px !important;
    }
}

/* Small mobile devices */
@media (max-width: 480px) {
    .stats-grid {
        gap: 10px !important;
    }
    
    .stats-grid > div {
        padding: 12px 8px !important;
        border-radius: 8px !important;
    }
    
    .stats-grid > div > div:first-child {
        font-size: 1.25rem !important;
        margin-bottom: 2px !important;
    }
    
    .stats-grid > div > div:last-child {
        font-size: 10px !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
