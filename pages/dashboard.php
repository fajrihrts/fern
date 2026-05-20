<?php
$pageTitle = 'Dashboard Peserta';
$user = auth();

// Get user's registration
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = ?");
$stmt->execute([$user['id']]);
$registration = $stmt->fetch();

// Check if user can submit testimonial
$canSubmitTestimonial = false;
if ($registration && $registration['internship_status'] === 'completed') {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM testimonials WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $testimonialCount = $stmt->fetch()['count'];
    $canSubmitTestimonial = $testimonialCount == 0;
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
<body style="background: var(--gray-50); padding-top: 85px;">
    
    <!-- Topbar -->
    <div style="background: white; border-bottom: 5px solid #000; box-shadow: 0 5px 0 #000; position: fixed; top: 0; left: 0; right: 0; z-index: 1000;">
        <div class="container" style="padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <a href="<?= APP_URL ?>/" style="text-decoration: none; color: inherit;">
                    <img src="<?= asset('img/brand.png') ?>" alt="BPS PPU" style="height: 40px;" onerror="this.style.display='none'">
                </a>
                <h4 style="margin: 0;">Dashboard Peserta</h4>
            </div>
            
            <div x-data="{ open: false }" style="position: relative;">
                <button @click="open = !open" style="display: flex; align-items: center; gap: 12px; background: none; border: none; cursor: pointer; padding: 8px 16px; border-radius: 12px; transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='transparent'">
                    <?php if ($user['profile_photo']): ?>
                        <img src="<?= upload($user['profile_photo']) ?>" alt="<?= e($user['name']) ?>" style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #000; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                            <?= getInitials($user['name']) ?>
                        </div>
                    <?php endif; ?>
                    <div style="text-align: left;">
                        <div style="font-weight: 700; font-size: 14px;"><?= e($user['name']) ?></div>
                        <div style="font-size: 12px; color: var(--gray-500);">Peserta Magang</div>
                    </div>
                    <i class="bi bi-chevron-down"></i>
                </button>
                
                <div x-show="open" @click.away="open = false" x-transition style="position: absolute; top: 100%; right: 0; margin-top: 8px; background: white; border: 3px solid #000; border-radius: 12px; box-shadow: 4px 4px 0 #000; min-width: 200px; z-index: 1000;">
                    <a href="<?= APP_URL ?>/profile/edit" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: inherit; font-weight: 600; border-bottom: 2px solid var(--gray-200); transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='white'">
                        <i class="bi bi-person-circle"></i> Edit Profil
                    </a>
                    <a href="<?= APP_URL ?>/" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; text-decoration: none; color: inherit; font-weight: 600; border-bottom: 2px solid var(--gray-200); transition: all 0.2s;" onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background='white'">
                        <i class="bi bi-house"></i> Beranda
                    </a>
                    <form method="POST" action="<?= APP_URL ?>/logout" style="margin: 0;">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <button type="submit" style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-weight: 600; color: var(--danger); transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='white'">
                            <i class="bi bi-box-arrow-right"></i> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if ($flash = getFlash()): ?>
        <div class="container" style="margin-top: 20px;">
            <div class="nb-alert nb-alert-<?= $flash['type'] ?>">
                <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                <span><?= e($flash['message']) ?></span>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="container" style="padding: 40px 24px;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            
            <!-- Main Column -->
            <div>
                <?php if (!$registration): ?>
                    <!-- No Registration Yet -->
                    <div class="nb-card" style="text-align: center; padding: 48px;">
                        <div style="width: 80px; height: 80px; background: var(--primary); border: 4px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 40px;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3 style="margin-bottom: 12px;">Lengkapi Formulir Pendaftaran</h3>
                        <p style="color: var(--gray-600); margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto;">
                            Anda belum melengkapi formulir pendaftaran magang. Silakan isi data diri dan unggah dokumen yang diperlukan.
                        </p>
                        <a href="<?= APP_URL ?>/pendaftaran/lengkapi" class="nb-btn nb-btn-primary nb-btn-lg">
                            <i class="bi bi-pencil-square"></i> Lengkapi Formulir
                        </a>
                    </div>
                    
                    <!-- Steps -->
                    <div class="nb-card mt-3">
                        <h4 style="margin-bottom: 20px;">Langkah Pendaftaran</h4>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <div style="display: flex; align-items: start; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: var(--success); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">
                                    <i class="bi bi-check"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">Buat Akun</div>
                                    <div style="color: var(--gray-600); font-size: 14px;">Akun Anda sudah berhasil dibuat</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: start; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: var(--warning); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">2</div>
                                <div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">Isi Formulir</div>
                                    <div style="color: var(--gray-600); font-size: 14px;">Lengkapi data diri dan unggah dokumen</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: start; gap: 12px;">
                                <div style="width: 32px; height: 32px; background: var(--gray-300); border: 2px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">3</div>
                                <div>
                                    <div style="font-weight: 700; margin-bottom: 4px; color: var(--gray-500);">Verifikasi</div>
                                    <div style="color: var(--gray-500); font-size: 14px;">Tunggu persetujuan dari admin</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Registration Status -->
                    <div class="nb-card" style="background: <?= $registration['status'] === 'approved' ? '#f0fdf4' : ($registration['status'] === 'rejected' ? '#fef2f2' : '#fffbeb') ?>;">
                        <div style="display: flex; align-items: start; gap: 16px;">
                            <div style="width: 60px; height: 60px; background: <?= $registration['status'] === 'approved' ? 'var(--success)' : ($registration['status'] === 'rejected' ? 'var(--danger)' : 'var(--warning)') ?>; border: 3px solid #000; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">
                                <i class="bi bi-<?= $registration['status'] === 'approved' ? 'check-circle' : ($registration['status'] === 'rejected' ? 'x-circle' : 'clock-history') ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="margin-bottom: 8px;">
                                    <?php if ($registration['status'] === 'pending'): ?>
                                        Berkas Sedang Ditinjau
                                    <?php elseif ($registration['status'] === 'approved'): ?>
                                        Selamat! Anda Diterima 🎉
                                    <?php else: ?>
                                        Pendaftaran Tidak Diterima
                                    <?php endif; ?>
                                </h4>
                                <p style="color: var(--gray-700); margin-bottom: 16px;">
                                    <?php if ($registration['status'] === 'pending'): ?>
                                        Formulir pendaftaran Anda sedang ditinjau oleh admin. Silakan cek dashboard secara berkala untuk melihat status verifikasi.
                                    <?php elseif ($registration['status'] === 'approved'): ?>
                                        Pendaftaran magang Anda telah disetujui. Anda dapat mulai melaporkan kehadiran harian melalui menu Laporan Kehadiran.
                                    <?php else: ?>
                                        Mohon maaf, pendaftaran Anda tidak dapat kami terima saat ini. Silakan hubungi admin untuk informasi lebih lanjut.
                                    <?php endif; ?>
                                </p>
                                
                                <?php if ($registration['admin_notes']): ?>
                                    <div style="background: white; border: 2px solid #000; border-radius: 8px; padding: 12px; margin-bottom: 16px;">
                                        <div style="font-weight: 700; font-size: 12px; margin-bottom: 4px;">Catatan Admin:</div>
                                        <div style="font-size: 14px;"><?= nl2br(e($registration['admin_notes'])) ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="display: flex; gap: 12px;">
                                    <?php if ($registration['status'] === 'pending'): ?>
                                        <a href="<?= APP_URL ?>/registration/edit" class="nb-btn nb-btn-outline nb-btn-sm">
                                            <i class="bi bi-pencil"></i> Edit Pendaftaran
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($registration['status'] === 'approved'): ?>
                                        <a href="<?= APP_URL ?>/laporan" class="nb-btn nb-btn-primary nb-btn-sm">
                                            <i class="bi bi-calendar-check"></i> Laporan Kehadiran
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Registration Details -->
                    <div class="nb-card mt-3">
                        <h4 style="margin-bottom: 20px;">Detail Pendaftaran</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Nama Lengkap</div>
                                <div style="font-weight: 700;"><?= e($registration['name']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Email</div>
                                <div style="font-weight: 700;"><?= e($registration['email']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">No. WhatsApp</div>
                                <div style="font-weight: 700;"><?= e($registration['phone']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Universitas</div>
                                <div style="font-weight: 700;"><?= e($registration['university']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Program Studi</div>
                                <div style="font-weight: 700;"><?= e($registration['major']) ?></div>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 4px;">Periode Magang</div>
                                <div style="font-weight: 700;"><?= formatDateIndo($registration['start_date']) ?> - <?= formatDateIndo($registration['end_date']) ?></div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--gray-200);">
                            <div style="font-size: 12px; color: var(--gray-500); font-weight: 600; margin-bottom: 8px;">Dokumen</div>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/proposal" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank">
                                    <i class="bi bi-file-pdf"></i> Proposal
                                </a>
                                <?php if ($registration['transcript_file']): ?>
                                    <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/transcript" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank">
                                        <i class="bi bi-file-pdf"></i> Transkrip
                                    </a>
                                <?php endif; ?>
                                <?php if ($registration['recommendation_letter_file']): ?>
                                    <a href="<?= APP_URL ?>/dokumen/<?= $registration['id'] ?>/recommendation_letter" class="nb-btn nb-btn-outline nb-btn-sm" target="_blank">
                                        <i class="bi bi-file-pdf"></i> Surat Rekomendasi
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Testimonial CTA -->
                    <?php if ($canSubmitTestimonial): ?>
                        <div class="nb-card mt-3" style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border: 4px solid #000;">
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div style="font-size: 48px;">⭐</div>
                                <div style="flex: 1;">
                                    <h4 style="margin-bottom: 8px;">Bagikan Pengalaman Anda!</h4>
                                    <p style="margin-bottom: 16px;">Magang Anda telah selesai. Yuk, bagikan pengalaman dan kesan Anda selama magang di BPS PPU.</p>
                                    <a href="<?= APP_URL ?>/testimoni/create" class="nb-btn" style="background: white;">
                                        <i class="bi bi-chat-quote"></i> Kirim Testimoni
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div>
                <!-- Profile Card -->
                <div class="nb-card">
                    <div style="text-align: center;">
                        <?php if ($user['profile_photo']): ?>
                            <img src="<?= upload($user['profile_photo']) ?>" alt="<?= e($user['name']) ?>" style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid #000; object-fit: cover; margin-bottom: 16px;">
                        <?php else: ?>
                            <div style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid #000; background: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 32px; margin: 0 auto 16px;">
                                <?= getInitials($user['name']) ?>
                            </div>
                        <?php endif; ?>
                        <h4 style="margin-bottom: 4px;"><?= e($user['name']) ?></h4>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;"><?= e($user['email']) ?></p>
                        <a href="<?= APP_URL ?>/profile/edit" class="nb-btn nb-btn-outline nb-btn-sm" style="width: 100%; justify-content: center;">
                            <i class="bi bi-pencil"></i> Edit Profil
                        </a>
                    </div>
                </div>
                
                <!-- Status Magang -->
                <?php if ($registration): ?>
                    <div class="nb-card mt-3">
                        <h5 style="margin-bottom: 12px;">Status Magang</h5>
                        <div style="margin-bottom: 12px;">
                            <?= getStatusBadge($registration['status']) ?>
                        </div>
                        <div>
                            <?= getInternshipStatusBadge($registration['internship_status']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Tips -->
                <div class="nb-card mt-3">
                    <h5 style="margin-bottom: 16px;"><i class="bi bi-lightbulb"></i> Tips Magang</h5>
                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 14px;">
                        <div style="display: flex; gap: 8px;">
                            <i class="bi bi-check-circle" style="color: var(--success); flex-shrink: 0;"></i>
                            <span>Lapor kehadiran setiap hari</span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <i class="bi bi-check-circle" style="color: var(--success); flex-shrink: 0;"></i>
                            <span>Catat aktivitas dengan detail</span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <i class="bi bi-check-circle" style="color: var(--success); flex-shrink: 0;"></i>
                            <span>Aktif bertanya dan belajar</span>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <i class="bi bi-check-circle" style="color: var(--success); flex-shrink: 0;"></i>
                            <span>Jaga komunikasi dengan mentor</span>
                        </div>
                    </div>
                </div>
                
                <!-- Help -->
                <div class="nb-card mt-3" style="background: var(--gray-900); color: white;">
                    <h5 style="margin-bottom: 12px; color: var(--primary);"><i class="bi bi-question-circle"></i> Butuh Bantuan?</h5>
                    <p style="font-size: 14px; margin-bottom: 16px; color: var(--gray-300);">Hubungi kami jika ada pertanyaan</p>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-envelope" style="color: var(--primary);"></i>
                            <span style="color: var(--gray-300);"><?= CONTACT_EMAIL ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-telephone" style="color: var(--primary);"></i>
                            <span style="color: var(--gray-300);"><?= CONTACT_PHONE ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
