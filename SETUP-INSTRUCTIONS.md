# 🚀 Setup Instructions untuk Fajri

Instruksi khusus untuk setup auto-deploy project Fern ke cPanel.

---

## ✅ Status Setup

- ✅ Git repository initialized
- ✅ Remote repository: https://github.com/fajrihrts/fern.git
- ✅ Branch: main
- ✅ Git config: Fajri Harits
- ✅ Deployment files ready

---

## 🔑 Deploy Secret Token

**PENTING:** Simpan token ini dengan aman!

```
d21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157
```

Token ini akan digunakan untuk:
1. `config.php` → `DEPLOY_SECRET`
2. GitHub Webhook → Secret

---

## 📝 Langkah 1: Update config.php

Edit file `config.php` dan update bagian deployment:

```php
// Deployment Secret (PENTING: Ganti dengan token di atas!)
define('DEPLOY_SECRET', 'd21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157');
define('DEPLOY_ENABLED', true);
define('DEPLOY_BRANCH', 'main');
```

**Cara edit:**
```bash
nano config.php
# atau
open -a TextEdit config.php
```

Cari bagian `DEPLOY_SECRET` dan ganti dengan token di atas.

---

## 📝 Langkah 2: Commit dan Push ke GitHub

```bash
# Add all files
git add .

# Commit
git commit -m "Initial commit - Setup auto deployment"

# Push ke GitHub
git push -u origin main
```

**Note:** Jika diminta username/password, gunakan:
- Username: `fajrihrts`
- Password: GitHub Personal Access Token (bukan password biasa)

**Cara buat Personal Access Token:**
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token
3. Pilih scope: `repo` (full control)
4. Copy token dan simpan (tidak bisa dilihat lagi!)

---

## 📝 Langkah 3: Setup di cPanel

### A. Setup Git Version Control

1. **Login ke cPanel** Anda
2. **Cari "Git Version Control"** di search bar
3. **Klik "Create"**
4. **Isi form:**

```
Clone URL: https://github.com/fajrihrts/fern.git
Repository Path: /home/[username]/repositories/fern
Repository Name: fern
```

5. **Klik "Create"**
6. **Klik "Manage"** pada repository yang baru dibuat
7. **Setup Deployment:**
   - Klik "Pull or Deploy" tab
   - Deployment Path: `/home/[username]/public_html`
   - Klik "Update from Remote" untuk test

### B. Setup Database

1. **Cari "MySQL Databases"** di cPanel
2. **Buat database baru:**
   - Database Name: `[username]_fern`
   - Klik "Create Database"

3. **Buat user database:**
   - Username: `[username]_fern`
   - Password: (generate strong password)
   - Klik "Create User"

4. **Tambahkan user ke database:**
   - Pilih user dan database yang baru dibuat
   - Centang "ALL PRIVILEGES"
   - Klik "Make Changes"

5. **Import database schema:**
   - Buka phpMyAdmin
   - Pilih database `[username]_fern`
   - Klik tab "Import"
   - Upload dan import file:
     - `sql/schema.sql`
     - `sql/activity_logs.sql`
     - `sql/dummy_data.sql` (opsional)

### C. Setup Config File di Server

1. **Buka File Manager** di cPanel
2. **Navigate ke:** `/home/[username]/public_html`
3. **Klik kanan `config.example.php`** → Copy
4. **Rename copy menjadi:** `config.php`
5. **Edit `config.php`** dengan kredensial database:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', '[username]_fern');
define('DB_USER', '[username]_fern');
define('DB_PASS', '[password yang dibuat tadi]');

// Application
define('APP_URL', 'https://yourdomain.com'); // Ganti dengan domain Anda
define('APP_DEBUG', false); // PENTING: false di production!

// Deployment
define('DEPLOY_SECRET', 'd21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157');
define('DEPLOY_ENABLED', true);
define('DEPLOY_BRANCH', 'main');
```

6. **Save**

---

## 📝 Langkah 4: Setup GitHub Webhook

1. **Buka repository** di GitHub: https://github.com/fajrihrts/fern
2. **Klik "Settings"** (tab di atas)
3. **Klik "Webhooks"** (menu kiri)
4. **Klik "Add webhook"**
5. **Isi form:**

```
Payload URL: https://yourdomain.com/deploy.php
Content type: application/json
Secret: d21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157
```

6. **Which events would you like to trigger this webhook?**
   - Pilih: **Just the push event**

7. **Active:** ✅ Centang
8. **Klik "Add webhook"**

---

## 🧪 Langkah 5: Test Deployment

### Test 1: Cek Deploy Script

Buka di browser:
```
https://yourdomain.com/deploy.php
```

Seharusnya muncul:
```
Deploy script is active
Repository: /home/[username]/public_html
Branch: main
Last deployment: Never
```

### Test 2: Test Auto Deploy

```bash
# Buat perubahan kecil
echo "# Test Auto Deploy" >> README.md

# Commit
git add README.md
git commit -m "Test auto deploy"

# Push
git push origin main
```

### Test 3: Cek Webhook

1. **Buka GitHub:** https://github.com/fajrihrts/fern/settings/hooks
2. **Klik webhook** yang baru dibuat
3. **Scroll ke "Recent Deliveries"**
4. **Seharusnya ada delivery baru dengan:**
   - Status: ✅ 200 OK
   - Response body: Deployment log

### Test 4: Cek Website

1. Buka website Anda
2. Pastikan perubahan sudah muncul
3. Test login dan fitur-fitur utama

---

## 📊 Monitoring

### Cek Deployment Log

Via browser:
```
https://yourdomain.com/logs/deploy.log
```

Via SSH (jika ada akses):
```bash
ssh [username]@yourdomain.com
tail -f /home/[username]/public_html/logs/deploy.log
```

### Cek Webhook History

GitHub → Settings → Webhooks → Recent Deliveries

---

## 🔒 Security Checklist

Setelah deployment, pastikan:

- [ ] `APP_DEBUG` set ke `false` di config.php
- [ ] Default admin password sudah diganti
- [ ] `DEPLOY_SECRET` sudah di-set dengan token yang kuat
- [ ] SSL certificate sudah installed (HTTPS)
- [ ] Test akses ke file sensitif (harus denied):
  - https://yourdomain.com/config.php → 403 Forbidden
  - https://yourdomain.com/logs/ → 403 Forbidden
  - https://yourdomain.com/cache/ → 403 Forbidden
  - https://yourdomain.com/backups/ → 403 Forbidden

---

## 🎯 Workflow Setelah Setup

Setelah semua setup selesai, workflow Anda akan sangat simple:

```bash
# 1. Edit code di local
nano file.php

# 2. Test di local
php -S localhost:8000

# 3. Commit changes
git add .
git commit -m "Update feature X"

# 4. Push (auto deploy!)
git push origin main

# 5. Done! Website otomatis terupdate ✅
```

---

## 🆘 Troubleshooting

### Webhook gagal (Status 403)

**Penyebab:** Secret token tidak cocok

**Solusi:**
1. Cek `DEPLOY_SECRET` di config.php
2. Cek Secret di GitHub webhook settings
3. Pastikan keduanya sama persis

### Webhook gagal (Status 500)

**Penyebab:** Error di deploy.php

**Solusi:**
1. Cek logs/deploy.log untuk error details
2. Cek PHP error log di cPanel
3. Pastikan permissions benar (755 untuk deploy.php)

### Git pull gagal

**Penyebab:** Conflict atau permission issue

**Solusi via SSH:**
```bash
cd /home/[username]/public_html
git status
git stash
git pull origin main
```

### Changes tidak muncul di website

**Solusi:**
1. Clear browser cache (Ctrl+F5 atau Cmd+Shift+R)
2. Clear PHP opcache via cPanel
3. Verify file benar-benar terupdate di server

---

## 📞 Support

### Dokumentasi
- **START-HERE.md** - Quick start
- **DEPLOYMENT-GUIDE.md** - Detailed guide
- **QUICK-DEPLOY.md** - Daily reference
- **DEPLOYMENT-CHECKLIST.md** - Checklist

### Logs
- **logs/deploy.log** - Deployment logs
- **logs/app-*.log** - Application logs

### Testing
```bash
# Test webhook
bash test-webhook.sh https://yourdomain.com/deploy.php d21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157

# Test database
https://yourdomain.com/test-db-connection.php
```

---

## ✅ Checklist Lengkap

- [ ] Update config.php dengan DEPLOY_SECRET
- [ ] Commit dan push ke GitHub
- [ ] Setup Git Version Control di cPanel
- [ ] Setup database di cPanel
- [ ] Import database schema
- [ ] Setup config.php di server
- [ ] Setup GitHub webhook
- [ ] Test deploy script
- [ ] Test auto deploy
- [ ] Verify webhook status
- [ ] Test website functionality
- [ ] Change default admin password
- [ ] Verify security (file protection)

---

## 🎉 Selesai!

Setelah semua langkah di atas selesai, Anda sudah punya:

✅ Auto-deployment dari GitHub ke cPanel  
✅ Automatic backup sebelum setiap deployment  
✅ Deployment logging untuk monitoring  
✅ Secure webhook dengan signature verification  
✅ Rollback capability dari backup  

**Happy Deploying! 🚀**

---

**Repository:** https://github.com/fajrihrts/fern.git  
**Deploy Secret:** `d21ff0d744846550b66416765d8b8a54c47ddebbe207c655b491c34973598157`  
**Date:** 2026-05-20

