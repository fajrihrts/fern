# 🚀 Panduan Auto Deploy ke cPanel

Panduan lengkap setup auto-deployment dari Git repository ke cPanel hosting.

---

## 📋 Persiapan

### Yang Anda Butuhkan:
- ✅ Akun cPanel dengan Git Version Control
- ✅ Repository Git (GitHub/GitLab/Bitbucket)
- ✅ Domain/subdomain yang sudah aktif
- ✅ Database MySQL di cPanel

---

## 🎯 Langkah 1: Setup Repository Git

### A. Push Project ke Git Repository

```bash
# Masuk ke folder project
cd /Users/fajriharits/Desktop/bps/fern/fern

# Initialize git (jika belum)
git init

# Add remote repository
git remote add origin https://github.com/username/fern.git

# Add all files
git add .

# Commit
git commit -m "Initial commit - ready for deployment"

# Push ke repository
git push -u origin main
```

### B. Pastikan File Penting Tidak Ter-commit

File yang **TIDAK BOLEH** di-commit:
- ❌ `config.php` (berisi kredensial database)
- ❌ `logs/*.log` (file log)
- ❌ `cache/*.cache` (file cache)
- ❌ `uploads/*` (file upload user)
- ❌ `backups/*` (file backup)

File `.gitignore` sudah di-setup untuk mengabaikan file-file ini.

---

## 🎯 Langkah 2: Setup Git di cPanel

### A. Login ke cPanel

1. Buka cPanel Anda: `https://yourdomain.com:2083`
2. Login dengan username dan password cPanel

### B. Setup Git Version Control

1. **Cari "Git Version Control"** di search bar cPanel
2. **Klik "Create"**
3. **Isi form:**

```
Clone URL: https://github.com/username/fern.git
Repository Path: /home/username/repositories/fern
Repository Name: fern
```

4. **Klik "Create"**

### C. Setup Deployment Path

1. Setelah repository dibuat, klik **"Manage"**
2. Di bagian **"Pull or Deploy"**, klik **"Update from Remote"**
3. Klik **"Generate"** untuk membuat deployment script
4. Edit **Deployment Path** menjadi: `/home/username/public_html`

---

## 🎯 Langkah 3: Setup Database

### A. Buat Database di cPanel

1. **Cari "MySQL Databases"** di cPanel
2. **Buat database baru:**
   - Database Name: `username_fern`
   - Klik "Create Database"

3. **Buat user database:**
   - Username: `username_fern`
   - Password: (generate strong password)
   - Klik "Create User"

4. **Tambahkan user ke database:**
   - Pilih user dan database yang baru dibuat
   - Centang "ALL PRIVILEGES"
   - Klik "Make Changes"

### B. Import Database Schema

1. **Buka phpMyAdmin** di cPanel
2. **Pilih database** `username_fern`
3. **Klik tab "Import"**
4. **Upload file:**
   - `sql/schema.sql`
   - `sql/activity_logs.sql`
   - `sql/dummy_data.sql` (opsional, untuk data testing)
5. **Klik "Go"**

---

## 🎯 Langkah 4: Setup Config File

### A. Copy Config Example

Via cPanel File Manager:

1. Buka **File Manager**
2. Navigate ke `/home/username/public_html`
3. Klik kanan `config.example.php`
4. Pilih **"Copy"**
5. Rename menjadi `config.php`

### B. Edit Config File

Edit `config.php` dengan kredensial yang benar:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'username_fern');
define('DB_USER', 'username_fern');
define('DB_PASS', 'your-database-password');

// Application
define('APP_URL', 'https://yourdomain.com');
define('APP_DEBUG', false); // PENTING: false di production!

// Deployment Secret (generate random token)
define('DEPLOY_SECRET', 'generate-random-token-here');
```

### C. Generate Deploy Secret

Untuk generate random token, gunakan salah satu cara:

**Via Terminal:**
```bash
openssl rand -hex 32
```

**Via PHP:**
```php
<?php echo bin2hex(random_bytes(32)); ?>
```

**Via Online Generator:**
- https://randomkeygen.com/

Copy token yang dihasilkan dan paste ke `DEPLOY_SECRET` di `config.php`.

---

## 🎯 Langkah 5: Setup Webhook untuk Auto Deploy

### A. Setup di GitHub

1. **Buka repository** di GitHub
2. **Settings** → **Webhooks** → **Add webhook**
3. **Isi form:**

```
Payload URL: https://yourdomain.com/deploy.php
Content type: application/json
Secret: [paste DEPLOY_SECRET dari config.php]
```

4. **Which events would you like to trigger this webhook?**
   - Pilih: **Just the push event**

5. **Active:** ✅ Centang
6. **Klik "Add webhook"**

### B. Setup di GitLab

1. **Buka repository** di GitLab
2. **Settings** → **Webhooks**
3. **Isi form:**

```
URL: https://yourdomain.com/deploy.php
Secret token: [paste DEPLOY_SECRET dari config.php]
Trigger: ✅ Push events
Branch: main
SSL verification: ✅ Enable SSL verification
```

4. **Klik "Add webhook"**

### C. Setup di Bitbucket

1. **Buka repository** di Bitbucket
2. **Repository settings** → **Webhooks** → **Add webhook**
3. **Isi form:**

```
Title: Auto Deploy to cPanel
URL: https://yourdomain.com/deploy.php
Triggers: ✅ Repository push
```

4. **Klik "Save"**

---

## 🎯 Langkah 6: Set Permissions

Via cPanel File Manager atau SSH:

```bash
# Set permissions untuk folders
chmod 755 logs
chmod 755 cache
chmod 755 uploads
chmod 755 backups

# Set permissions untuk files
chmod 644 .htaccess
chmod 644 config.php
chmod 755 deploy.php
```

---

## 🎯 Langkah 7: Test Deployment

### A. Test Manual Deploy Script

1. Buka browser: `https://yourdomain.com/deploy.php`
2. Seharusnya muncul:

```
Deploy script is active
Repository: /home/username/public_html
Branch: main
Last deployment: Never
```

### B. Test Auto Deploy

1. **Edit file di repository** (contoh: edit README.md)
2. **Commit dan push:**

```bash
git add README.md
git commit -m "Test auto deploy"
git push origin main
```

3. **Cek webhook di Git repository:**
   - GitHub: Settings → Webhooks → Recent Deliveries
   - GitLab: Settings → Webhooks → Recent events
   - Seharusnya ada delivery baru dengan status **200 OK**

4. **Cek deployment log:**
   - Buka: `https://yourdomain.com/logs/deploy.log`
   - Atau via File Manager: `/home/username/public_html/logs/deploy.log`

### C. Verify Changes

1. Buka website Anda
2. Pastikan perubahan sudah muncul
3. Test login dan fitur-fitur utama

---

## 🎯 Langkah 8: Security Hardening

### A. Protect Sensitive Files

File `.htaccess` sudah include protection, tapi pastikan:

```apache
# Protect config file
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

# Protect deploy script (optional - allow only from Git IPs)
<Files "deploy.php">
    Order Deny,Allow
    Deny from all
    
    # GitHub webhook IPs
    Allow from 140.82.112.0/20
    Allow from 143.55.64.0/20
    
    # GitLab webhook IPs  
    Allow from 34.74.90.64/28
    Allow from 34.74.226.0/24
</Files>
```

### B. Protect Directories

Pastikan ada `.htaccess` di folder:
- ✅ `/logs/.htaccess` → `Deny from all`
- ✅ `/cache/.htaccess` → `Deny from all`
- ✅ `/backups/.htaccess` → `Deny from all`

### C. Change Default Admin Password

1. Login sebagai admin: `https://yourdomain.com/admin`
2. Email: `admin@fern.test`
3. Password: `password`
4. **SEGERA GANTI PASSWORD!**

---

## 🎯 Langkah 9: Setup Cron Jobs (Opsional)

### A. Cleanup Script

1. **Buka "Cron Jobs"** di cPanel
2. **Add New Cron Job:**

```
Minute: 0
Hour: 2
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/username/public_html/cleanup.php
```

Ini akan menjalankan cleanup script setiap hari jam 2 pagi.

### B. Backup Script (Opsional)

Jika Anda ingin backup otomatis tambahan:

```bash
# Backup database setiap hari
0 3 * * * mysqldump -u username_fern -p'password' username_fern | gzip > /home/username/backups/db-$(date +\%Y\%m\%d).sql.gz
```

---

## 📊 Monitoring & Maintenance

### A. Cek Deployment Logs

```bash
# Via SSH
tail -f /home/username/public_html/logs/deploy.log

# Via browser
https://yourdomain.com/logs/deploy.log
```

### B. Cek Application Logs

```bash
# Via SSH
tail -f /home/username/public_html/logs/app-*.log

# Via cPanel File Manager
Navigate ke: /home/username/public_html/logs/
```

### C. Cek Webhook Status

- **GitHub:** Repository → Settings → Webhooks → Recent Deliveries
- **GitLab:** Repository → Settings → Webhooks → Recent events
- **Bitbucket:** Repository Settings → Webhooks → View requests

### D. Cek Backups

Backup otomatis dibuat sebelum setiap deployment di folder `/backups/`.

Untuk restore backup:

```bash
cd /home/username/public_html
tar -xzf backups/backup-2024-01-15-120000.tar.gz
```

---

## 🔧 Troubleshooting

### Problem: Webhook gagal (Status 500)

**Solusi:**
1. Cek `logs/deploy.log` untuk error details
2. Pastikan `DEPLOY_SECRET` sama di `config.php` dan webhook settings
3. Cek permissions: `chmod 755 deploy.php`
4. Test manual: buka `https://yourdomain.com/deploy.php`

### Problem: Git pull gagal

**Solusi:**
1. Login via SSH
2. Test manual git pull:
```bash
cd /home/username/public_html
git pull origin main
```
3. Jika ada conflict, resolve manual:
```bash
git status
git stash
git pull origin main
```

### Problem: Changes tidak muncul di website

**Solusi:**
1. Clear browser cache (Ctrl+F5)
2. Clear PHP opcache via cPanel
3. Cek apakah file benar-benar terupdate:
```bash
ls -la /home/username/public_html
```

### Problem: Database connection error

**Solusi:**
1. Cek kredensial di `config.php`
2. Test koneksi: `https://yourdomain.com/test-db-connection.php`
3. Pastikan database user punya privileges
4. Cek MySQL service di cPanel

### Problem: Permission denied errors

**Solusi:**
```bash
# Set correct permissions
chmod 755 logs cache uploads backups
chmod 644 .htaccess config.php
chmod 755 deploy.php
```

---

## 🎉 Workflow Deployment

Setelah setup selesai, workflow Anda akan seperti ini:

```
1. Edit code di local
2. Commit changes: git commit -m "Update feature X"
3. Push to repository: git push origin main
4. Webhook otomatis trigger deploy.php
5. deploy.php akan:
   - Verify signature
   - Create backup
   - Git pull changes
   - Clear cache
   - Log deployment
6. Website otomatis terupdate! ✅
```

---

## 📝 Checklist Deployment

Sebelum go-live, pastikan:

- ✅ Database sudah di-import
- ✅ `config.php` sudah diisi dengan benar
- ✅ `APP_DEBUG` set ke `false`
- ✅ Default admin password sudah diganti
- ✅ Webhook sudah di-setup dan tested
- ✅ Permissions sudah di-set dengan benar
- ✅ `.htaccess` protection sudah aktif
- ✅ SSL certificate sudah installed
- ✅ Backup otomatis sudah berjalan
- ✅ Cron jobs sudah di-setup
- ✅ Test semua fitur utama

---

## 🆘 Support

Jika ada masalah:

1. **Cek logs:**
   - `/logs/deploy.log` - Deployment logs
   - `/logs/app-*.log` - Application logs
   - cPanel Error Log

2. **Cek webhook:**
   - GitHub/GitLab webhook recent deliveries
   - Response status dan body

3. **Test manual:**
   - `https://yourdomain.com/deploy.php` - Deploy script status
   - `https://yourdomain.com/test-db-connection.php` - Database connection

4. **Dokumentasi:**
   - `README.md` - Overview
   - `DEPLOY-SETUP.md` - Setup details
   - `DATABASE-ERROR-HANDLING.md` - Database troubleshooting

---

## 🎯 Next Steps

Setelah deployment berhasil:

1. **Setup monitoring** (optional):
   - Uptime monitoring (UptimeRobot, Pingdom)
   - Error tracking (Sentry, Rollbar)

2. **Setup backup strategy**:
   - Database backup harian
   - File backup mingguan
   - Off-site backup bulanan

3. **Performance optimization**:
   - Enable CDN (Cloudflare)
   - Optimize images
   - Enable caching

4. **Security hardening**:
   - Setup firewall rules
   - Enable 2FA untuk admin
   - Regular security audits

---

**Happy Deploying! 🚀**

Jika ada pertanyaan, silakan hubungi tim development.

