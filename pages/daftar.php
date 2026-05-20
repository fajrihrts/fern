<?php
$pageTitle = 'Daftar Magang';

// Redirect if already logged in
if (isAuth()) {
    $role = $_SESSION['user_role'];
    if (in_array($role, ['admin', 'super_admin'])) {
        redirect('/admin');
    } else {
        redirect('/dashboard');
    }
}

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container-sm">
        <div class="text-center mb-4">
            <h1 style="margin-bottom: 12px; font-size: 2.5rem;">Daftar Program Magang</h1>
            <p style="color: var(--gray-600); font-size: 1rem;">Pilih salah satu opsi di bawah untuk memulai</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 800px; margin: 0 auto 32px;">
            
            <!-- Buat Akun Baru -->
            <div class="nb-card nb-animate-pop" style="text-align: center; padding: 32px 24px;">
                <div style="width: 64px; height: 64px; background: var(--primary); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 32px;">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h3 style="margin-bottom: 8px; font-size: 1.5rem;">Buat Akun Baru</h3>
                <p style="color: var(--gray-600); margin-bottom: 20px; font-size: 14px;">
                    Belum punya akun? Daftar sekarang dan mulai proses pendaftaran magang Anda.
                </p>
                <a href="<?= APP_URL ?>/register" class="nb-btn nb-btn-primary" style="width: 100%; justify-content: center; font-size: 14px; padding: 12px;">
                    <i class="bi bi-rocket-takeoff"></i> Daftar Sekarang
                </a>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--gray-200);">
                    <div style="font-size: 13px; color: var(--gray-600); text-align: left;">
                        <div style="font-weight: 700; margin-bottom: 10px; font-size: 12px; text-transform: uppercase; color: var(--gray-500);">Yang Anda dapatkan:</div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Akun peserta magang</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Dashboard pribadi</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Tracking status pendaftaran</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sudah Punya Akun -->
            <div class="nb-card nb-animate-pop" style="text-align: center; padding: 32px 24px;">
                <div style="width: 64px; height: 64px; background: var(--accent); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 32px;">
                    <i class="bi bi-box-arrow-in-right"></i>
                </div>
                <h3 style="margin-bottom: 8px; font-size: 1.5rem;">Sudah Punya Akun</h3>
                <p style="color: var(--gray-600); margin-bottom: 20px; font-size: 14px;">
                    Masuk ke akun Anda untuk melanjutkan proses pendaftaran atau melihat status magang.
                </p>
                <a href="<?= APP_URL ?>/login" class="nb-btn nb-btn-accent" style="width: 100%; justify-content: center; font-size: 14px; padding: 12px;">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--gray-200);">
                    <div style="font-size: 13px; color: var(--gray-600); text-align: left;">
                        <div style="font-weight: 700; margin-bottom: 10px; font-size: 12px; text-transform: uppercase; color: var(--gray-500);">Akses ke:</div>
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Dashboard peserta</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Lengkapi formulir</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 14px;"></i>
                                <span>Laporan kehadiran</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Info Section - More Compact -->
        <div class="nb-card" style="max-width: 800px; margin: 0 auto; padding: 24px;">
            <h4 style="margin-bottom: 16px; text-align: center; font-size: 1.1rem;">
                <i class="bi bi-info-circle"></i> Alur Pendaftaran
            </h4>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                <div style="text-align: center;">
                    <div style="width: 40px; height: 40px; background: var(--primary); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 900; font-size: 18px;">1</div>
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Buat Akun</div>
                    <div style="font-size: 11px; color: var(--gray-600); line-height: 1.4;">Daftar dengan email aktif</div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 40px; height: 40px; background: var(--accent); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 900; font-size: 18px;">2</div>
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Lengkapi Data</div>
                    <div style="font-size: 11px; color: var(--gray-600); line-height: 1.4;">Isi formulir & upload dokumen</div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 40px; height: 40px; background: var(--success); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 900; font-size: 18px;">3</div>
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Verifikasi</div>
                    <div style="font-size: 11px; color: var(--gray-600); line-height: 1.4;">Tunggu persetujuan admin</div>
                </div>
                <div style="text-align: center;">
                    <div style="width: 40px; height: 40px; background: var(--info); border: 3px solid #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: 900; font-size: 18px;">4</div>
                    <div style="font-weight: 700; font-size: 13px; margin-bottom: 4px;">Mulai Magang</div>
                    <div style="font-size: 11px; color: var(--gray-600); line-height: 1.4;">Lapor kehadiran harian</div>
                </div>
            </div>
        </div>
        
        <!-- Help Section - More Compact -->
        <div class="text-center mt-4">
            <p style="color: var(--gray-600); margin-bottom: 12px; font-size: 14px;">
                <i class="bi bi-question-circle"></i> Butuh bantuan? Hubungi kami
            </p>
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; font-size: 13px;">
                <a href="mailto:<?= CONTACT_EMAIL ?>" style="display: flex; align-items: center; gap: 6px; color: var(--black); text-decoration: none; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--black)'">
                    <i class="bi bi-envelope-fill"></i> <?= CONTACT_EMAIL ?>
                </a>
                <a href="tel:<?= str_replace(['(', ')', ' '], '', CONTACT_PHONE) ?>" style="display: flex; align-items: center; gap: 6px; color: var(--black); text-decoration: none; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--black)'">
                    <i class="bi bi-telephone-fill"></i> <?= CONTACT_PHONE ?>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 768px) {
    section > div > div:nth-child(2) {
        grid-template-columns: 1fr !important;
    }
    section > div > div:nth-child(3) > div:last-child {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
