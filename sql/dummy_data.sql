-- ============================================
-- FERN - Dummy Data
-- Data contoh untuk testing dan demo
-- ============================================

-- 1. USERS (Peserta)
INSERT INTO users (id, name, email, password, role, profile_photo, created_at, updated_at) VALUES
('11111111-1111-1111-1111-111111111111', 'Budi Santoso', 'budi@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-01-15 08:30:00', '2026-01-15 08:30:00'),
('22222222-2222-2222-2222-222222222222', 'Siti Nurhaliza', 'siti@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-01-20 09:15:00', '2026-01-20 09:15:00'),
('33333333-3333-3333-3333-333333333333', 'Ahmad Fauzi', 'ahmad@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
('44444444-4444-4444-4444-444444444444', 'Dewi Lestari', 'dewi@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-02-10 11:20:00', '2026-02-10 11:20:00'),
('55555555-5555-5555-5555-555555555555', 'Rizki Pratama', 'rizki@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-03-05 13:45:00', '2026-03-05 13:45:00'),
('66666666-6666-6666-6666-666666666666', 'Putri Ayu', 'putri@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-03-15 14:30:00', '2026-03-15 14:30:00'),
('77777777-7777-7777-7777-777777777777', 'Andi Wijaya', 'andi@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-04-01 08:00:00', '2026-04-01 08:00:00'),
('88888888-8888-8888-8888-888888888888', 'Maya Sari', 'maya@example.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYIxIYJuT3u', 'peserta', NULL, '2026-04-10 09:30:00', '2026-04-10 09:30:00');

-- 2. REGISTRATIONS (Pendaftaran)
INSERT INTO registrations (id, user_id, name, email, phone, university, major, start_date, end_date, proposal_file, transcript_file, recommendation_letter_file, certificate_files, status, internship_status, admin_notes, actual_start_date, actual_end_date, created_at, updated_at) VALUES
('a1111111-1111-1111-1111-111111111111', '11111111-1111-1111-1111-111111111111', 'Budi Santoso', 'budi@example.com', '081234567890', 'Universitas Mulawarman', 'Statistika', '2026-02-01', '2026-04-30', 'proposals/proposal_budi.pdf', 'transcripts/transkrip_budi.pdf', 'recommendations/rekomendasi_budi.pdf', '["certificates/sertifikat_budi_1.pdf"]', 'approved', 'ongoing', 'Peserta aktif dan rajin', '2026-02-01', NULL, '2026-01-15 08:30:00', '2026-01-16 10:00:00'),
('a2222222-2222-2222-2222-222222222222', '22222222-2222-2222-2222-222222222222', 'Siti Nurhaliza', 'siti@example.com', '081234567891', 'Universitas Balikpapan', 'Sistem Informasi', '2026-02-15', '2026-05-15', 'proposals/proposal_siti.pdf', 'transcripts/transkrip_siti.pdf', 'recommendations/rekomendasi_siti.pdf', '["certificates/sertifikat_siti_1.pdf"]', 'approved', 'ongoing', NULL, '2026-02-15', NULL, '2026-01-20 09:15:00', '2026-01-22 14:30:00'),
('a3333333-3333-3333-3333-333333333333', '33333333-3333-3333-3333-333333333333', 'Ahmad Fauzi', 'ahmad@example.com', '081234567892', 'Institut Teknologi Kalimantan', 'Teknik Informatika', '2026-03-01', '2026-05-31', 'proposals/proposal_ahmad.pdf', 'transcripts/transkrip_ahmad.pdf', 'recommendations/rekomendasi_ahmad.pdf', '["certificates/sertifikat_ahmad_1.pdf","certificates/sertifikat_ahmad_2.pdf"]', 'approved', 'ongoing', 'Sangat kompeten di bidang data', '2026-03-01', NULL, '2026-02-01 10:00:00', '2026-02-03 11:00:00'),
('a4444444-4444-4444-4444-444444444444', '44444444-4444-4444-4444-444444444444', 'Dewi Lestari', 'dewi@example.com', '081234567893', 'Universitas Mulawarman', 'Ekonomi', '2026-03-15', '2026-06-15', 'proposals/proposal_dewi.pdf', 'transcripts/transkrip_dewi.pdf', NULL, NULL, 'approved', 'not_started', NULL, NULL, NULL, '2026-02-10 11:20:00', '2026-02-12 09:00:00'),
('a5555555-5555-5555-5555-555555555555', '55555555-5555-5555-5555-555555555555', 'Rizki Pratama', 'rizki@example.com', '081234567894', 'Politeknik Negeri Balikpapan', 'Teknik Komputer', '2026-04-01', '2026-06-30', 'proposals/proposal_rizki.pdf', 'transcripts/transkrip_rizki.pdf', 'recommendations/rekomendasi_rizki.pdf', NULL, 'pending', 'not_started', NULL, NULL, NULL, '2026-03-05 13:45:00', '2026-03-05 13:45:00'),
('a6666666-6666-6666-6666-666666666666', '66666666-6666-6666-6666-666666666666', 'Putri Ayu', 'putri@example.com', '081234567895', 'Universitas Balikpapan', 'Akuntansi', '2026-04-15', '2026-07-15', 'proposals/proposal_putri.pdf', 'transcripts/transkrip_putri.pdf', NULL, NULL, 'pending', 'not_started', NULL, NULL, NULL, '2026-03-15 14:30:00', '2026-03-15 14:30:00'),
('a7777777-7777-7777-7777-777777777777', '77777777-7777-7777-7777-777777777777', 'Andi Wijaya', 'andi@example.com', '081234567896', 'Universitas Mulawarman', 'Statistika', '2026-01-01', '2026-03-31', 'proposals/proposal_andi.pdf', 'transcripts/transkrip_andi.pdf', 'recommendations/rekomendasi_andi.pdf', '["certificates/sertifikat_andi_1.pdf"]', 'approved', 'completed', 'Luar biasa! Sangat berprestasi', '2026-01-01', '2026-03-31', '2026-04-01 08:00:00', '2026-04-02 10:00:00'),
('a8888888-8888-8888-8888-888888888888', '88888888-8888-8888-8888-888888888888', 'Maya Sari', 'maya@example.com', '081234567897', 'Institut Teknologi Kalimantan', 'Sistem Informasi', '2026-05-01', '2026-07-31', 'proposals/proposal_maya.pdf', 'transcripts/transkrip_maya.pdf', NULL, NULL, 'rejected', 'not_started', 'Dokumen tidak lengkap', NULL, NULL, '2026-04-10 09:30:00', '2026-04-12 15:00:00');

-- 3. ATTENDANCE REPORTS (Laporan Kehadiran)
INSERT INTO attendance_reports (id, user_id, registration_id, date, status, activities, photo_proof, created_at, updated_at) VALUES
('b1111111-1111-1111-1111-111111111111', '11111111-1111-1111-1111-111111111111', 'a1111111-1111-1111-1111-111111111111', '2026-05-01', 'hadir', 'Melakukan input data sensus penduduk ke dalam sistem database BPS', 'attendance_photos/budi_20260501.jpg', '2026-05-01 16:00:00', '2026-05-01 16:00:00'),
('b1111111-1111-1111-1111-111111111112', '11111111-1111-1111-1111-111111111111', 'a1111111-1111-1111-1111-111111111111', '2026-05-02', 'hadir', 'Membantu verifikasi data hasil survei ekonomi daerah', 'attendance_photos/budi_20260502.jpg', '2026-05-02 16:30:00', '2026-05-02 16:30:00'),
('b1111111-1111-1111-1111-111111111113', '11111111-1111-1111-1111-111111111111', 'a1111111-1111-1111-1111-111111111111', '2026-05-05', 'hadir', 'Membuat visualisasi data statistik menggunakan software R', 'attendance_photos/budi_20260505.jpg', '2026-05-05 15:45:00', '2026-05-05 15:45:00'),
('b2222222-2222-2222-2222-222222222221', '22222222-2222-2222-2222-222222222222', 'a2222222-2222-2222-2222-222222222222', '2026-05-01', 'hadir', 'Mengembangkan modul sistem informasi untuk pencatatan data', 'attendance_photos/siti_20260501.jpg', '2026-05-01 16:15:00', '2026-05-01 16:15:00'),
('b2222222-2222-2222-2222-222222222222', '22222222-2222-2222-2222-222222222222', 'a2222222-2222-2222-2222-222222222222', '2026-05-02', 'sakit', 'Tidak masuk karena sakit demam', NULL, '2026-05-02 08:00:00', '2026-05-02 08:00:00'),
('b2222222-2222-2222-2222-222222222223', '22222222-2222-2222-2222-222222222222', 'a2222222-2222-2222-2222-222222222222', '2026-05-05', 'hadir', 'Testing dan debugging sistem informasi yang sedang dikembangkan', 'attendance_photos/siti_20260505.jpg', '2026-05-05 16:00:00', '2026-05-05 16:00:00'),
('b3333333-3333-3333-3333-333333333331', '33333333-3333-3333-3333-333333333333', 'a3333333-3333-3333-3333-333333333333', '2026-05-01', 'hadir', 'Melakukan data cleaning dan preprocessing untuk analisis data mining', 'attendance_photos/ahmad_20260501.jpg', '2026-05-01 16:45:00', '2026-05-01 16:45:00'),
('b3333333-3333-3333-3333-333333333332', '33333333-3333-3333-3333-333333333333', 'a3333333-3333-3333-3333-333333333333', '2026-05-02', 'hadir', 'Implementasi algoritma clustering untuk segmentasi data', 'attendance_photos/ahmad_20260502.jpg', '2026-05-02 17:00:00', '2026-05-02 17:00:00'),
('b3333333-3333-3333-3333-333333333333', '33333333-3333-3333-3333-333333333333', 'a3333333-3333-3333-3333-333333333333', '2026-05-05', 'izin', 'Izin mengikuti seminar nasional statistika', NULL, '2026-05-05 07:30:00', '2026-05-05 07:30:00'),
('b7777777-7777-7777-7777-777777777771', '77777777-7777-7777-7777-777777777777', 'a7777777-7777-7777-7777-777777777777', '2026-03-28', 'hadir', 'Presentasi hasil penelitian machine learning untuk prediksi data', 'attendance_photos/andi_20260328.jpg', '2026-03-28 16:00:00', '2026-03-28 16:00:00'),
('b7777777-7777-7777-7777-777777777772', '77777777-7777-7777-7777-777777777777', 'a7777777-7777-7777-7777-777777777777', '2026-03-29', 'hadir', 'Dokumentasi dan penyusunan laporan akhir magang', 'attendance_photos/andi_20260329.jpg', '2026-03-29 16:30:00', '2026-03-29 16:30:00'),
('b7777777-7777-7777-7777-777777777773', '77777777-7777-7777-7777-777777777777', 'a7777777-7777-7777-7777-777777777777', '2026-03-31', 'hadir', 'Serah terima hasil kerja dan perpisahan dengan tim BPS', 'attendance_photos/andi_20260331.jpg', '2026-03-31 15:00:00', '2026-03-31 15:00:00');

-- 4. POSTS (Berita/Artikel)
INSERT INTO posts (id, title, content, image, is_published, published_at, created_at, updated_at) VALUES
('c1111111-1111-1111-1111-111111111111', 'Pembukaan Pendaftaran Magang Periode Mei - Juli 2026', 'BPS Kabupaten Penajam Paser Utara membuka kesempatan bagi mahasiswa untuk mengikuti program magang pada periode Mei hingga Juli 2026. Program ini bertujuan untuk memberikan pengalaman praktis dalam bidang statistik dan pengolahan data.\n\nPersyaratan:\n- Mahasiswa aktif minimal semester 5\n- IPK minimal 3.00\n- Memiliki minat di bidang statistik dan data\n\nPendaftaran dibuka mulai 1 April hingga 30 April 2026. Informasi lengkap dapat dilihat di menu Pendaftaran.', 'posts/magang_2026.jpg', 1, '2026-03-25 10:00:00', '2026-03-25 10:00:00', '2026-03-25 10:00:00'),
('c2222222-2222-2222-2222-222222222222', 'Workshop Data Visualization untuk Peserta Magang', 'BPS PPU mengadakan workshop khusus untuk peserta magang tentang teknik visualisasi data menggunakan berbagai tools seperti R, Python, dan Tableau. Workshop ini diadakan pada tanggal 15 Mei 2026.\n\nMateri yang akan dibahas:\n- Prinsip dasar visualisasi data\n- Pembuatan grafik interaktif\n- Dashboard untuk reporting\n- Best practices dalam presentasi data\n\nSemua peserta magang wajib mengikuti workshop ini.', 'posts/workshop_dataviz.jpg', 1, '2026-05-01 09:00:00', '2026-05-01 09:00:00', '2026-05-01 09:00:00'),
('c3333333-3333-3333-3333-333333333333', 'Kunjungan Lapangan ke Desa untuk Survei Sosial Ekonomi', 'Peserta magang berkesempatan mengikuti kegiatan survei sosial ekonomi ke beberapa desa di Kabupaten Penajam Paser Utara. Kegiatan ini memberikan pengalaman langsung dalam pengumpulan data di lapangan.\n\nKegiatan meliputi:\n- Wawancara dengan responden\n- Pengisian kuesioner\n- Verifikasi data\n- Koordinasi dengan petugas lapangan\n\nPengalaman ini sangat berharga untuk memahami proses pengumpulan data statistik secara menyeluruh.', 'posts/survei_lapangan.jpg', 1, '2026-04-20 14:30:00', '2026-04-20 14:30:00', '2026-04-20 14:30:00'),
('c4444444-4444-4444-4444-444444444444', 'Tips Sukses Menjalani Magang di BPS', 'Berikut beberapa tips untuk memaksimalkan pengalaman magang Anda di BPS:\n\n1. Selalu tepat waktu dan disiplin\n2. Aktif bertanya dan belajar\n3. Dokumentasikan setiap kegiatan\n4. Jalin networking dengan pegawai dan sesama peserta\n5. Manfaatkan fasilitas dan resources yang tersedia\n6. Kerjakan tugas dengan serius dan profesional\n7. Jaga komunikasi yang baik dengan pembimbing\n\nSemoga tips ini membantu Anda mendapatkan pengalaman magang yang bermanfaat!', NULL, 1, '2026-04-01 11:00:00', '2026-04-01 11:00:00', '2026-04-01 11:00:00'),
('c5555555-5555-5555-5555-555555555555', 'Pengumuman Libur Nasional dan Cuti Bersama', 'Diberitahukan kepada seluruh peserta magang bahwa pada tanggal 17 Mei 2026 (Hari Raya Waisak) dan 1 Juni 2026 (Hari Lahir Pancasila) kantor BPS PPU libur.\n\nPeserta magang tidak perlu membuat laporan kehadiran pada tanggal tersebut. Kegiatan magang akan dilanjutkan kembali pada hari kerja berikutnya.\n\nTerima kasih atas perhatiannya.', NULL, 1, '2026-05-10 08:00:00', '2026-05-10 08:00:00', '2026-05-10 08:00:00');

-- 5. TESTIMONIALS (Testimoni)
INSERT INTO testimonials (id, user_id, name, university, major, content, rating, is_published, created_at, updated_at) VALUES
('d1111111-1111-1111-1111-111111111111', '77777777-7777-7777-7777-777777777777', 'Andi Wijaya', 'Universitas Mulawarman', 'Statistika', 'Pengalaman magang di BPS PPU sangat luar biasa! Saya belajar banyak tentang pengolahan data statistik dan machine learning. Tim pembimbing sangat supportif dan profesional. Terima kasih BPS PPU!', 5, 1, '2026-04-01 10:00:00', '2026-04-01 14:00:00'),
('d2222222-2222-2222-2222-222222222222', '11111111-1111-1111-1111-111111111111', 'Budi Santoso', 'Universitas Mulawarman', 'Statistika', 'Magang di BPS memberikan pengalaman praktis yang sangat berharga. Saya bisa mengaplikasikan teori yang dipelajari di kampus ke dalam pekerjaan nyata. Lingkungan kerja yang kondusif dan fasilitas yang memadai.', 5, 1, '2026-05-05 16:00:00', '2026-05-05 17:00:00'),
('d3333333-3333-3333-3333-333333333333', '22222222-2222-2222-2222-222222222222', 'Siti Nurhaliza', 'Universitas Balikpapan', 'Sistem Informasi', 'Program magang yang terstruktur dengan baik. Saya mendapat bimbingan intensif dalam pengembangan sistem informasi. Sangat recommended untuk mahasiswa yang ingin belajar tentang data dan teknologi informasi.', 4, 1, '2026-05-06 09:00:00', '2026-05-06 10:00:00'),
('d4444444-4444-4444-4444-444444444444', '33333333-3333-3333-3333-333333333333', 'Ahmad Fauzi', 'Institut Teknologi Kalimantan', 'Teknik Informatika', 'Kesempatan yang sangat baik untuk belajar data mining dan analisis data. Proyek yang dikerjakan sangat menantang dan meningkatkan skill saya. Terima kasih kepada seluruh tim BPS PPU!', 5, 1, '2026-05-07 15:30:00', '2026-05-07 16:00:00');

-- 6. HOLIDAYS (Hari Libur)
INSERT INTO holidays (id, date, name, description, created_at, updated_at) VALUES
(1, '2026-01-01', 'Tahun Baru 2026', 'Tahun Baru Masehi', NOW(), NOW()),
(2, '2026-02-12', 'Tahun Baru Imlek', 'Tahun Baru Imlek 2577 Kongzili', NOW(), NOW()),
(3, '2026-03-11', 'Isra Miraj', 'Isra Miraj Nabi Muhammad SAW', NOW(), NOW()),
(4, '2026-03-14', 'Hari Suci Nyepi', 'Tahun Baru Saka 1948', NOW(), NOW()),
(5, '2026-04-03', 'Wafat Isa Almasih', 'Wafat Yesus Kristus', NOW(), NOW()),
(6, '2026-04-05', 'Idul Fitri', 'Hari Raya Idul Fitri 1447 H', NOW(), NOW()),
(7, '2026-04-06', 'Idul Fitri', 'Hari Raya Idul Fitri 1447 H (Hari Kedua)', NOW(), NOW()),
(8, '2026-05-01', 'Hari Buruh', 'Hari Buruh Internasional', NOW(), NOW()),
(9, '2026-05-14', 'Kenaikan Isa Almasih', 'Kenaikan Yesus Kristus', NOW(), NOW()),
(10, '2026-05-26', 'Hari Raya Waisak', 'Hari Raya Waisak 2570', NOW(), NOW()),
(11, '2026-06-01', 'Hari Lahir Pancasila', 'Hari Lahir Pancasila', NOW(), NOW()),
(12, '2026-06-11', 'Idul Adha', 'Hari Raya Idul Adha 1447 H', NOW(), NOW()),
(13, '2026-07-01', 'Tahun Baru Islam', 'Tahun Baru Islam 1448 H', NOW(), NOW()),
(14, '2026-08-17', 'Hari Kemerdekaan RI', 'HUT Kemerdekaan RI ke-81', NOW(), NOW()),
(15, '2026-09-10', 'Maulid Nabi Muhammad', 'Maulid Nabi Muhammad SAW', NOW(), NOW()),
(16, '2026-12-25', 'Hari Raya Natal', 'Hari Raya Natal', NOW(), NOW());

-- ============================================
-- SELESAI
-- ============================================
-- Total data:
-- - 8 Users (peserta)
-- - 8 Registrations (berbagai status)
-- - 12 Attendance Reports
-- - 5 Posts
-- - 4 Testimonials
-- - 16 Holidays
-- ============================================
