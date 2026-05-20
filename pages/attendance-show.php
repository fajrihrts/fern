<?php
$date = $_GET['date'] ?? '';
$user = auth();

if (empty($date)) {
    redirect('/laporan');
}

// Get report
$db = getDbConnection();
$stmt = $db->prepare("SELECT * FROM attendance_reports WHERE user_id = ? AND date = ?");
$stmt->execute([$user['id'], $date]);
$report = $stmt->fetch();

if (!$report) {
    setFlash('danger', 'Laporan tidak ditemukan.');
    redirect('/laporan');
}

$pageTitle = 'Detail Laporan - ' . formatDateIndo($date);

include 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="mb-3">
            <a href="<?= APP_URL ?>/laporan" class="nb-btn nb-btn-outline nb-btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="nb-card">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid var(--gray-200);">
                <div>
                    <h2 style="margin-bottom: 8px;">Detail Laporan Kehadiran</h2>
                    <p style="color: var(--gray-600); margin: 0;">
                        <i class="bi bi-calendar"></i> <?= formatDateIndo($report['date']) ?>
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <?= getAttendanceStatusBadge($report['status']) ?>
                </div>
            </div>

            <!-- Time Info: Jam Submit Absen -->
            <div style="background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <div style="text-align: center;">
                    <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 8px; font-weight: 600;">
                        <i class="bi bi-clock"></i> Waktu Submit Absen
                    </div>
                    <div style="font-size: 48px; font-weight: 900; color: var(--success); line-height: 1;">
                        <?= $report['check_in'] ? date('H:i', strtotime($report['check_in'])) : '-' ?>
                    </div>
                    <div style="font-size: 12px; color: var(--gray-500); margin-top: 8px;">
                        Tercatat otomatis saat laporan dikirim
                    </div>
                </div>
            </div>

            <!-- 2 Column Layout: Info Left, Photo Right -->
            <div style="display: grid; grid-template-columns: 1fr 500px; gap: 32px; align-items: start;">
                <!-- Left Column: Information -->
                <div>
                    <!-- Activities -->
                    <div style="margin-bottom: 20px;">
                        <h5 style="margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-list-check"></i> Uraian Aktivitas
                        </h5>
                        <div style="background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: 8px; padding: 12px;">
                            <p style="line-height: 1.6; margin: 0; white-space: pre-wrap; font-size: 14px;"><?= e($report['activities']) ?></p>
                        </div>
                    </div>

                    <!-- Learning -->
                    <div style="margin-bottom: 20px;">
                        <h5 style="margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-lightbulb"></i> Pembelajaran
                        </h5>
                        <div style="background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: 8px; padding: 12px;">
                            <p style="line-height: 1.6; margin: 0; white-space: pre-wrap; font-size: 14px;"><?= e($report['learning']) ?></p>
                        </div>
                    </div>

                    <!-- Obstacles -->
                    <div style="margin-bottom: 20px;">
                        <h5 style="margin-bottom: 8px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-exclamation-triangle"></i> Kendala
                        </h5>
                        <div style="background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: 8px; padding: 12px;">
                            <p style="line-height: 1.6; margin: 0; white-space: pre-wrap; font-size: 14px;"><?= e($report['obstacles']) ?></p>
                        </div>
                    </div>

                    <!-- Confirmation Status -->
                    <div style="background: <?= $report['is_confirmed'] ? '#f0fdf4' : '#fef2f2' ?>; border: 2px solid <?= $report['is_confirmed'] ? 'var(--success)' : 'var(--danger)' ?>; border-radius: 8px; padding: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <?php if ($report['is_confirmed']): ?>
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 20px;"></i>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Data Telah Dikonfirmasi</div>
                                    <div style="font-size: 12px; color: var(--gray-600);">Laporan ini telah dikonfirmasi kebenarannya</div>
                                </div>
                            <?php else: ?>
                                <i class="bi bi-x-circle" style="color: var(--danger); font-size: 20px;"></i>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;">Belum Dikonfirmasi</div>
                                    <div style="font-size: 12px; color: var(--gray-600);">Laporan ini belum dikonfirmasi</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <?php if ($report['notes']): ?>
                        <div style="margin-top: 20px; background: #dbeafe; border: 2px solid #3b82f6; border-radius: 8px; padding: 12px;">
                            <div style="display: flex; gap: 10px;">
                                <i class="bi bi-info-circle" style="color: #3b82f6; font-size: 18px; flex-shrink: 0;"></i>
                                <div style="font-size: 13px;">
                                    <strong>Catatan Admin:</strong><br>
                                    <?= nl2br(e($report['notes'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Photo -->
                <div style="position: sticky; top: 70px;">
                    <?php if ($report['photo_proof']): ?>
                        <div>
                            <h5 style="margin-bottom: 12px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-image"></i> Foto Bukti
                            </h5>
                            <img src="<?= upload($report['photo_proof']) ?>" alt="Foto Bukti" style="width: 100%; height: auto; border: 3px solid #000; border-radius: 12px; box-shadow: 4px 4px 0 #000;">
                        </div>
                    <?php else: ?>
                        <div style="background: var(--gray-100); border: 3px dashed var(--gray-300); border-radius: 12px; padding: 40px; text-align: center;">
                            <i class="bi bi-image" style="font-size: 48px; color: var(--gray-400); margin-bottom: 12px; display: block;"></i>
                            <p style="color: var(--gray-500); margin: 0; font-size: 14px;">Tidak ada foto bukti</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata -->
            <div style="margin-top: 24px; padding-top: 20px; border-top: 2px solid var(--gray-200); font-size: 11px; color: var(--gray-500);">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div>
                        <i class="bi bi-clock-history"></i> Dibuat: <?= formatDateTimeIndo($report['created_at']) ?>
                    </div>
                    <?php if ($report['updated_at'] !== $report['created_at']): ?>
                        <div>
                            <i class="bi bi-pencil"></i> Diperbarui: <?= formatDateTimeIndo($report['updated_at']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 992px) {
    section .container > .nb-card > div[style*="grid-template-columns: 1fr 500px"] {
        display: block !important;
    }
    
    section .container > .nb-card > div[style*="grid-template-columns: 1fr 500px"] > div:last-child {
        margin-top: 24px;
        position: static !important;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
