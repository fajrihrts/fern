# 🌿 Fern - Portal Magang BPS PPU

Sistem manajemen magang untuk BPS Kabupaten Penajam Paser Utara. Dibangun dengan **Pure PHP** (tanpa framework) untuk kemudahan deployment di shared hosting.

---

## 🚀 Quick Start

### 1. Setup Database
```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE fern CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p fern < sql/schema.sql
mysql -u root -p fern < sql/activity_logs.sql
```

### 2. Konfigurasi
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fern');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_URL', 'http://localhost:8000');
define('DEBUG_MODE', true); // Set false di production
```

### 3. Jalankan Server
```bash
php -S localhost:8000
```

### 4. Akses Aplikasi
- **Website:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin

### 5. Login Admin
```
Email: admin@fern.test
Password: password
```

⚠️ **PENTING:** Ganti password default setelah login pertama!

---

## 📋 Fitur Utama

### Untuk Peserta
- ✅ Registrasi akun
- ✅ Lengkapi data pendaftaran
- ✅ Upload dokumen (proposal, transkrip, sertifikat)
- ✅ Laporan kehadiran harian dengan foto
- ✅ Lihat status pendaftaran
- ✅ Buat testimoni
- ✅ Edit profil

### Untuk Admin
- ✅ Dashboard dengan statistik & charts
- ✅ Kelola pendaftaran (approve/reject/bulk actions)
- ✅ Kelola kehadiran (view/delete/export)
- ✅ Kelola berita/posts (CRUD + image upload)
- ✅ Kelola testimoni (approve/reject)
- ✅ Kelola admin users (khusus super admin)
- ✅ Activity logs (audit trail)
- ✅ Export data ke CSV
- ✅ Search & filter di semua halaman
- ✅ Pagination

### Keamanan
- ✅ CSRF protection
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection
- ✅ Password hashing (bcrypt cost 12)
- ✅ Rate limiting (brute force protection)
- ✅ File upload validation
- ✅ Activity logging
- ✅ Session security

### Performance
- ✅ File-based caching
- ✅ Optimized database queries
- ✅ Pagination
- ✅ Gzip compression
- ✅ Browser caching

### Database Error Handling
- ✅ Automatic connection error detection
- ✅ User-friendly error pages
- ✅ Detailed error logging
- ✅ Safe query helper functions
- ✅ Connection timeout handling
- ✅ Database health check tools

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Pure PHP 8.5+ (no framework) |
| **Database** | MySQL 8.0 |
| **Frontend** | AlpineJS, Bootstrap Icons |
| **Design** | Neubrutalism |
| **Server** | Apache/Nginx + PHP-FPM |

---

## 📁 Struktur Folder

```
fern/
├── admin/              # Admin panel pages
│   ├── index.php      # Dashboard
│   ├── registrations.php
│   ├── attendance.php
│   ├── posts.php
│   ├── testimonials.php
│   ├── users.php
│   └── activity-logs.php
│
├── pages/              # Public & user pages
│   ├── home.php       # Landing page
│   ├── blog.php       # News listing
│   ├── login.php      # Login
│   ├── register.php   # Register
│   ├── dashboard.php  # User dashboard
│   └── ...
│
├── classes/            # Helper classes
│   ├── ActivityLog.php
│   ├── BulkAction.php
│   ├── Cache.php
│   ├── ChartHelper.php
│   ├── Logger.php
│   ├── Paginator.php
│   ├── RateLimiter.php
│   └── Validator.php
│
├── includes/           # Shared templates
│   ├── header.php
│   ├── admin-header.php
│   └── footer.php
│
├── assets/             # Static files
│   ├── css/style.css
│   ├── js/app.js
│   └── img/
│
├── uploads/            # User uploads
├── cache/              # Cache files
├── logs/               # Application logs
├── sql/                # Database schemas
│
├── config.php          # Configuration
├── helpers.php         # Helper functions
├── auth.php            # Authentication
├── autoload.php        # Class autoloader
├── index.php           # Router
└── cleanup.php         # Maintenance script
```

---

## 🗄️ Database

### Tables
1. **users** - User accounts
2. **registrations** - Internship applications
3. **attendance_reports** - Daily attendance
4. **posts** - News/blog articles
5. **testimonials** - User testimonials
6. **activity_logs** - Audit trail
7. **holidays** - Holiday calendar
8. **sessions** - User sessions

### Default Admin Account
```sql
INSERT INTO users (id, name, email, password, role) VALUES
('00000000-0000-0000-0000-000000000001', 'Super Admin', 'admin@fern.test', 
'$2y$12$...', 'super_admin');
```

---

## 🚀 Deployment ke Shared Hosting

### 1. Upload Files
Upload semua file via FTP ke folder `public_html` atau `www`.

### 2. Set Permissions
```bash
chmod 755 uploads/ cache/ logs/
chmod 644 config.php
```

### 3. Buat Database
Buat database via cPanel/phpMyAdmin, lalu import:
- `sql/schema.sql`
- `sql/activity_logs.sql`

### 4. Edit Config
Edit `config.php` dengan kredensial database hosting Anda.

### 5. Setup .htaccess
File `.htaccess` sudah disediakan dengan konfigurasi:
- URL rewriting
- Security headers
- Gzip compression
- Browser caching

### 6. Setup Cron Job (Optional)
Jalankan cleanup script setiap hari:
```
0 2 * * * /usr/bin/php /path/to/fern/cleanup.php
```

### 7. Test
- Akses website Anda
- Login sebagai admin
- Ganti password default
- Test semua fitur

---

## 🔧 Konfigurasi

### Environment Variables (config.php)
```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'fern');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_SOCKET', ''); // Optional: Unix socket path

// Application
define('APP_NAME', 'Portal e-Registrasi Magang BPS PPU');
define('APP_URL', 'http://localhost:8000');
define('APP_DEBUG', true); // Set false di production

// Security
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', ...]);
```

---

## 📝 Dokumentasi Tambahan

- **CARA-MENJALANKAN.md** - Panduan instalasi detail
- **QUICK-START.md** - Panduan cepat
- **EXAMPLES.md** - Contoh penggunaan
- **DATABASE-ERROR-HANDLING.md** - Dokumentasi error handling database
- **EXAMPLE-SAFE-QUERIES.php** - Contoh penggunaan safe query functions
- **CACHE-FIX-DOCUMENTATION.md** - Dokumentasi cache fix & prevention
- **PHASE-2-DOCS.md** - Dokumentasi Phase 2
- **PHASE-3-DOCS.md** - Dokumentasi Phase 3
- **UPGRADE-LOG.md** - Riwayat upgrade

---

## 🐛 Troubleshooting

### Error: Class not found
```bash
# Pastikan autoload.php di-load
require_once __DIR__ . '/autoload.php';
```

### Error: Database connection failed
```bash
# Cek kredensial di config.php
# Cek MySQL service running
# Cek socket path (untuk XAMPP Mac)

# Test koneksi database
http://localhost:8000/test-db-connection.php

# Lihat dokumentasi lengkap
DATABASE-ERROR-HANDLING.md
```

### Error: Permission denied (uploads)
```bash
chmod 755 uploads/ cache/ logs/
```

### Error: 404 Not Found
```bash
# Pastikan .htaccess ada dan mod_rewrite enabled
# Atau gunakan PHP built-in server
php -S localhost:8000
```

### Database Error Handling
Sistem dilengkapi dengan error handling otomatis untuk masalah database:
- **Connection refused**: MySQL tidak berjalan
- **Socket not found**: Path socket salah di config.php
- **Access denied**: Username/password salah
- **Unknown database**: Database belum dibuat
- **Timeout**: Server database sibuk

Lihat **DATABASE-ERROR-HANDLING.md** untuk dokumentasi lengkap.

---

## 📊 Status Proyek

**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**Completion:** 100%

| Category | Status |
|----------|--------|
| Public Pages | ✅ 8/8 Complete |
| User Pages | ✅ 8/8 Complete |
| Admin Pages | ✅ 8/8 Complete |
| Security | ✅ Complete |
| Performance | ✅ Complete |
| Documentation | ✅ Complete |

---

## 🤝 Support

Untuk pertanyaan atau masalah:
1. Cek dokumentasi di folder `/fern/*.md`
2. Cek error logs di `/logs/`
3. Cek activity logs di admin panel
4. Hubungi administrator sistem

---

## 📄 License

Proprietary - BPS Kabupaten Penajam Paser Utara

---

**Developed with ❤️ for BPS PPU**  
**Last Updated:** 2026-05-12
