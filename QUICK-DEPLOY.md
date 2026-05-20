# ⚡ Quick Deploy Reference

Panduan cepat untuk deployment ke cPanel.

---

## 🚀 Setup Awal (Sekali Saja)

### 1. Persiapan Local

```bash
# Run setup script
bash setup-deploy.sh

# Edit config.php dengan kredensial database
nano config.php

# Commit dan push
git add .
git commit -m "Setup deployment"
git push origin main
```

### 2. Setup di cPanel

**A. Git Version Control:**
```
Clone URL: https://github.com/username/fern.git
Repository Path: /home/username/repositories/fern
Deploy Path: /home/username/public_html
```

**B. Database:**
```
1. Buat database: username_fern
2. Buat user: username_fern
3. Import: sql/schema.sql, sql/activity_logs.sql
```

**C. Config File:**
```
1. Copy config.example.php → config.php
2. Edit dengan kredensial database
3. Set APP_DEBUG = false
4. Generate dan set DEPLOY_SECRET
```

### 3. Setup Webhook

**GitHub:**
```
Settings → Webhooks → Add webhook
URL: https://yourdomain.com/deploy.php
Secret: [DEPLOY_SECRET dari config.php]
Events: Push events
```

**GitLab:**
```
Settings → Webhooks
URL: https://yourdomain.com/deploy.php
Secret token: [DEPLOY_SECRET dari config.php]
Trigger: Push events
```

---

## 🔄 Workflow Harian

```bash
# 1. Edit code
nano file.php

# 2. Test local
php -S localhost:8000

# 3. Commit
git add .
git commit -m "Update feature X"

# 4. Push (auto deploy!)
git push origin main

# 5. Verify
# Cek webhook: GitHub → Settings → Webhooks → Recent Deliveries
# Cek log: https://yourdomain.com/logs/deploy.log
```

---

## 🔍 Monitoring

### Cek Status Deployment

```bash
# Via browser
https://yourdomain.com/deploy.php

# Via SSH
tail -f /home/username/public_html/logs/deploy.log
```

### Cek Webhook

- **GitHub:** Settings → Webhooks → Recent Deliveries
- **GitLab:** Settings → Webhooks → Recent events
- Status: **200 OK** = Success ✅

### Cek Application Logs

```bash
# Via SSH
tail -f /home/username/public_html/logs/app-*.log

# Via browser
https://yourdomain.com/logs/
```

---

## 🐛 Troubleshooting

### Webhook Gagal (500/403)

```bash
# 1. Cek secret token
# Pastikan DEPLOY_SECRET sama di config.php dan webhook

# 2. Cek permissions
chmod 755 deploy.php

# 3. Cek log
tail -f logs/deploy.log

# 4. Test manual
curl https://yourdomain.com/deploy.php
```

### Git Pull Gagal

```bash
# Via SSH
cd /home/username/public_html

# Cek status
git status

# Jika ada conflict
git stash
git pull origin main
git stash pop

# Force pull (hati-hati!)
git fetch --all
git reset --hard origin/main
```

### Changes Tidak Muncul

```bash
# 1. Clear browser cache (Ctrl+F5)

# 2. Clear PHP opcache
# Via cPanel: PHP Selector → Options → Reset OPcache

# 3. Clear application cache
rm -f cache/*.cache

# 4. Verify file updated
ls -la /home/username/public_html
```

### Database Error

```bash
# 1. Test connection
https://yourdomain.com/test-db-connection.php

# 2. Cek config.php
nano config.php

# 3. Cek database privileges
# Via phpMyAdmin: User accounts → Edit privileges
```

---

## 🔧 Useful Commands

### Via SSH

```bash
# Login
ssh username@yourdomain.com

# Navigate to project
cd /home/username/public_html

# Check git status
git status
git log --oneline -5

# Manual pull
git pull origin main

# Check permissions
ls -la

# View logs
tail -f logs/deploy.log
tail -f logs/app-*.log

# Clear cache
rm -f cache/*.cache

# Check disk space
df -h

# Check PHP version
php -v
```

### Via cPanel

```bash
# File Manager
# Navigate to: /home/username/public_html

# Terminal
# Available in cPanel → Terminal

# Git Version Control
# Manage → Update from Remote

# PHP Selector
# Select PHP version & extensions

# Cron Jobs
# Setup scheduled tasks
```

---

## 📊 Deployment Checklist

### Pre-Deployment

- [ ] Code tested locally
- [ ] Database migrations ready (if any)
- [ ] Config updated (if needed)
- [ ] Dependencies updated (if any)
- [ ] Commit message clear

### Post-Deployment

- [ ] Webhook status: 200 OK
- [ ] Deploy log: no errors
- [ ] Website accessible
- [ ] Login works
- [ ] Key features tested
- [ ] No PHP errors

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG = false` in production
- [ ] Strong `DEPLOY_SECRET` set
- [ ] Default admin password changed
- [ ] `.htaccess` protection active
- [ ] SSL certificate installed
- [ ] Sensitive files not committed
- [ ] Database credentials secure
- [ ] File permissions correct (755/644)

---

## 📞 Quick Links

- **Website:** https://yourdomain.com
- **Admin:** https://yourdomain.com/admin
- **Deploy Status:** https://yourdomain.com/deploy.php
- **DB Test:** https://yourdomain.com/test-db-connection.php
- **cPanel:** https://yourdomain.com:2083
- **phpMyAdmin:** cPanel → phpMyAdmin

---

## 📖 Documentation

- `README.md` - Project overview
- `DEPLOYMENT-GUIDE.md` - Detailed deployment guide
- `DEPLOY-SETUP.md` - Setup instructions
- `DATABASE-ERROR-HANDLING.md` - Database troubleshooting

---

## 🆘 Emergency Procedures

### Rollback Deployment

```bash
# Via SSH
cd /home/username/public_html

# List backups
ls -la backups/

# Restore from backup
tar -xzf backups/backup-YYYY-MM-DD-HHMMSS.tar.gz

# Or rollback git
git log --oneline -10
git reset --hard COMMIT_HASH
```

### Disable Auto Deploy

```bash
# Edit config.php
define('DEPLOY_ENABLED', false);

# Or delete webhook
# GitHub: Settings → Webhooks → Delete
```

### Enable Maintenance Mode

```bash
# Edit config.php
define('MAINTENANCE_MODE', true);
```

---

## 💡 Tips

1. **Always test locally first** before pushing
2. **Use meaningful commit messages** for easy tracking
3. **Check webhook status** after each push
4. **Monitor logs regularly** for errors
5. **Keep backups** of important data
6. **Update dependencies** regularly
7. **Review security** periodically

---

## 🎯 Common Tasks

### Update Dependencies

```bash
# If using Composer
composer update
git add composer.lock
git commit -m "Update dependencies"
git push
```

### Database Migration

```bash
# 1. Create migration SQL file
# 2. Test locally
# 3. Upload to server
# 4. Run via phpMyAdmin or SSH:
mysql -u username_fern -p username_fern < migration.sql
```

### Clear All Caches

```bash
# Application cache
rm -f cache/*.cache

# PHP opcache (via PHP script)
<?php opcache_reset(); ?>

# Browser cache
Ctrl+F5 or Cmd+Shift+R
```

---

**Need help? Check DEPLOYMENT-GUIDE.md for detailed instructions.**

