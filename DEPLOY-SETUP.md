# Setup Auto Deploy ke cPanel

Panduan lengkap untuk setup auto deployment menggunakan Git Version Control di cPanel + Webhook.

## 📋 Prerequisites

- cPanel dengan Git Version Control enabled
- Repository GitHub/GitLab/Bitbucket
- SSH access ke cPanel (opsional, tapi direkomendasikan)

---

## 🚀 Langkah 1: Setup Git di cPanel

### A. Via cPanel Git Version Control (Cara Mudah)

1. **Login ke cPanel**
2. **Cari "Git Version Control"** di search bar
3. **Klik "Create"**
4. **Isi form:**
   - **Clone URL**: URL repository Anda (contoh: `https://github.com/username/repo.git`)
   - **Repository Path**: `/home/username/public_html` atau path project Anda
   - **Repository Name**: Nama bebas (contoh: `fern`)
   - **Branch**: `main` atau `master`
5. **Klik "Create"**

### B. Via SSH (Cara Manual)

```bash
# Login via SSH
ssh username@yourdomain.com

# Masuk ke directory public_html
cd public_html

# Clone repository
git clone https://github.com/username/repo.git .

# Set branch
git checkout main

# Set git config
git config user.name "Your Name"
git config user.email "your@email.com"
```

---

## 🔐 Langkah 2: Setup Deploy Script

### A. Upload deploy.php

1. **Upload file `deploy.php`** ke root project Anda (sama dengan index.php)
2. **Edit konfigurasi** di `deploy.php`:

```php
// Ganti secret token (PENTING!)
define('DEPLOY_SECRET', 'your-super-secret-token-here-12345');

// Path ke repository (biasanya sudah benar)
define('REPO_PATH', __DIR__);

// Branch yang akan di-deploy
define('DEPLOY_BRANCH', 'main');
```

### B. Set Permissions

Via cPanel File Manager atau SSH:

```bash
chmod 755 deploy.php
chmod 755 logs
chmod 755 backups
```

### C. Test Deploy Script

Buka di browser: `https://yourdomain.com/deploy.php`

Seharusnya muncul:
```
Deploy script is active
Repository: /home/username/public_html
Branch: main
Last deployment: Never
```

---

## 🪝 Langkah 3: Setup Webhook di GitHub/GitLab

### A. GitHub

1. **Buka repository** di GitHub
2. **Settings** → **Webhooks** → **Add webhook**
3. **Isi form:**
   - **Payload URL**: `https://yourdomain.com/deploy.php`
   - **Content type**: `application/json`
   - **Secret**: Token yang sama dengan `DEPLOY_SECRET` di deploy.php
   - **Which events**: Pilih "Just the push event"
   - **Active**: ✅ Centang
4. **Klik "Add webhook"**

### B. GitLab

1. **Buka repository** di GitLab
2. **Settings** → **Webhooks**
3. **Isi form:**
   - **URL**: `https://yourdomain.com/deploy.php`
   - **Secret token**: Token yang sama dengan `DEPLOY_SECRET`
   - **Trigger**: Centang "Push events"
   - **Branch**: `main`
   - **SSL verification**: Enable
4. **Klik "Add webhook"**

### C. Bitbucket

1. **Buka repository** di Bitbucket
2. **Repository settings** → **Webhooks** → **Add webhook**
3. **Isi form:**
   - **Title**: Auto Deploy
   - **URL**: `https://yourdomain.com/deploy.php`
   - **Triggers**: Pilih "Repository push"
4. **Klik "Save"**

---

## 🧪 Langkah 4: Test Deployment

### A. Test Manual

1. **Buat perubahan kecil** di repository (edit README.md)
2. **Commit dan push**:
   ```bash
   git add .
   git commit -m "Test auto deploy"
   git push origin main
   ```
3. **Cek webhook** di GitHub/GitLab:
   - Seharusnya ada delivery baru dengan status 200 OK
4. **Cek log deployment**:
   - Buka `https://yourdomain.com/logs/deploy.log`
   - Atau via SSH: `tail -f logs/deploy.log`

### B. Troubleshooting

**Webhook gagal (status 500/403):**
- Cek `DEPLOY_SECRET` sudah sama di deploy.php dan webhook
- Cek permissions file deploy.php (755)
- Cek error log: `tail -f logs/deploy.log`

**Git pull gagal:**
- Pastikan git sudah ter-setup dengan benar
- Cek SSH key atau credentials
- Test manual: `cd /path/to/repo && git pull origin main`

**File tidak terupdate:**
- Cek apakah branch sudah benar
- Cek apakah ada conflict: `git status`
- Cek opcache: tambahkan `opcache_reset()` di deploy.php

---

## ⚙️ Langkah 5: Konfigurasi Lanjutan (Opsional)

### A. Enable Post-Deploy Commands

Edit `deploy.php`, uncomment commands yang dibutuhkan:

```php
define('POST_DEPLOY_COMMANDS', [
    'composer install --no-dev --optimize-autoloader',
    'php cleanup.php',  // Clear cache
    // 'php artisan migrate --force',  // Jika pakai Laravel
]);
```

### B. Setup Backup Otomatis

Backup otomatis sudah enabled by default. File backup disimpan di folder `backups/`.

Untuk restore backup:
```bash
cd /path/to/repo
tar -xzf backups/backup-2024-01-15-120000.tar.gz
```

### C. Protect Deploy Script

Tambahkan di `.htaccess` untuk protect deploy.php:

```apache
<Files "deploy.php">
    # Allow only from GitHub/GitLab IPs
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

### D. Setup Notification (Opsional)

Tambahkan di `deploy.php` untuk kirim notifikasi ke Slack/Discord:

```php
function sendNotification($message) {
    $webhookUrl = 'YOUR_SLACK_WEBHOOK_URL';
    $data = json_encode(['text' => $message]);
    
    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Panggil setelah deployment berhasil
sendNotification("✅ Deployment successful!");
```

---

## 📊 Monitoring

### Cek Status Deployment

```bash
# Via SSH
tail -f logs/deploy.log

# Via browser
https://yourdomain.com/logs/deploy.log
```

### Cek Webhook History

- **GitHub**: Repository → Settings → Webhooks → Recent Deliveries
- **GitLab**: Repository → Settings → Webhooks → Recent events

---

## 🔒 Security Checklist

- ✅ Ganti `DEPLOY_SECRET` dengan token yang kuat
- ✅ Jangan commit `deploy.php` dengan secret ke repository
- ✅ Protect folder `logs/` dan `backups/` dengan `.htaccess`
- ✅ Gunakan HTTPS untuk webhook URL
- ✅ Enable SSL verification di webhook settings
- ✅ Limit IP access ke deploy.php (opsional)

---

## 🎯 Workflow Deployment

```
Developer Push Code
        ↓
GitHub/GitLab Webhook
        ↓
deploy.php Triggered
        ↓
Verify Signature
        ↓
Create Backup
        ↓
Git Pull Changes
        ↓
Run Post-Deploy Commands
        ↓
Clear Cache
        ↓
Deployment Complete ✅
```

---

## 📝 Notes

- Deployment hanya trigger untuk push ke branch `main` (bisa diubah di config)
- Backup otomatis dibuat sebelum setiap deployment
- Hanya 5 backup terakhir yang disimpan (auto cleanup)
- Log deployment tersimpan di `logs/deploy.log`
- Jika deployment gagal, restore dari backup terakhir

---

## 🆘 Support

Jika ada masalah:
1. Cek `logs/deploy.log` untuk error details
2. Test manual git pull: `cd /path/to/repo && git pull`
3. Test webhook di GitHub/GitLab webhook settings
4. Cek PHP error log di cPanel

---

**Happy Deploying! 🚀**
