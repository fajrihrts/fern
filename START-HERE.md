# 🚀 START HERE - Auto Deploy Setup

Panduan cepat untuk memulai auto-deployment ke cPanel.

---

## ⚡ Quick Start (5 Menit)

### 1️⃣ Setup Local (2 menit)

```bash
# Jalankan setup script
bash setup-deploy.sh

# Edit config.php dengan kredensial database Anda
nano config.php

# Commit dan push
git add .
git commit -m "Setup deployment"
git push origin main
```

### 2️⃣ Setup cPanel (2 menit)

1. **Git Version Control** → Create
   - Clone URL: `https://github.com/fajrihrts/fern.git`
   - Deploy Path: `/home/username/public_html`

2. **MySQL Databases** → Create
   - Database: `username_fern`
   - Import: `sql/schema.sql`

3. **File Manager** → Edit
   - Copy `config.example.php` → `config.php`
   - Edit dengan kredensial database

### 3️⃣ Setup Webhook (1 menit)

**GitHub:** Settings → Webhooks → Add webhook
```
URL: https://yourdomain.com/deploy.php
Secret: [dari config.php DEPLOY_SECRET]
Events: Push events
```

**GitLab:** Settings → Webhooks
```
URL: https://yourdomain.com/deploy.php
Secret token: [dari config.php DEPLOY_SECRET]
Trigger: Push events
```

---

## ✅ Test Deployment

```bash
# Test 1: Cek deploy script
curl https://yourdomain.com/deploy.php

# Test 2: Push perubahan
echo "# Test" >> README.md
git add README.md
git commit -m "Test auto deploy"
git push origin main

# Test 3: Cek webhook
# GitHub: Settings → Webhooks → Recent Deliveries
# Status harus: 200 OK ✅

# Test 4: Cek log
tail -f logs/deploy.log
```

---

## 📚 Dokumentasi Lengkap

| File | Untuk Apa |
|------|-----------|
| **DEPLOYMENT-GUIDE.md** | 📖 Panduan lengkap step-by-step |
| **QUICK-DEPLOY.md** | ⚡ Quick reference untuk daily use |
| **DEPLOYMENT-CHECKLIST.md** | ✅ Checklist lengkap deployment |
| **DEPLOYMENT-FILES.md** | 📦 Penjelasan semua file deployment |
| **DEPLOY-SETUP.md** | 🔧 Setup instructions detail |

---

## 🎯 Workflow Setelah Setup

```bash
# 1. Edit code
nano file.php

# 2. Commit
git add .
git commit -m "Update feature X"

# 3. Push (auto deploy!)
git push origin main

# 4. Done! ✅
# Website otomatis terupdate dalam beberapa detik
```

---

## 🔍 Monitoring

```bash
# Cek status deployment
https://yourdomain.com/deploy.php

# Cek deployment log
https://yourdomain.com/logs/deploy.log

# Cek webhook history
# GitHub: Settings → Webhooks → Recent Deliveries
```

---

## 🆘 Troubleshooting Cepat

### Webhook gagal (403/500)
```bash
# Cek secret token sama di config.php dan webhook
# Cek logs/deploy.log untuk error details
tail -f logs/deploy.log
```

### Changes tidak muncul
```bash
# Clear browser cache: Ctrl+F5
# Clear PHP opcache via cPanel
# Verify file updated: ls -la
```

### Database error
```bash
# Test connection
https://yourdomain.com/test-db-connection.php

# Cek config.php credentials
nano config.php
```

---

## 📞 Need Help?

1. **Cek dokumentasi:** DEPLOYMENT-GUIDE.md
2. **Cek logs:** logs/deploy.log
3. **Cek webhook:** GitHub/GitLab webhook deliveries
4. **Test manual:** bash test-webhook.sh

---

## 🎉 That's It!

Setelah setup selesai, setiap kali Anda push ke repository, website akan otomatis terupdate!

**Happy Deploying! 🚀**

