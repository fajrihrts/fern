# 🚀 Deployment Guide

Panduan deployment project Fern ke cPanel dengan auto-deploy dari GitHub.

---

## 📋 Prerequisites

- cPanel dengan Git Version Control
- Repository GitHub: https://github.com/fajrihrts/fern.git
- Database MySQL
- PHP 8.0+

---

## 🔧 Setup (One Time)

### 1. Setup Git di cPanel

**cPanel → Git Version Control → Create**

```
Clone URL: https://github.com/fajrihrts/fern.git
Repository Path: /home/[username]/repositories/fern
Deploy Path: /home/[username]/public_html
Branch: main
```

### 2. Setup Database

**cPanel → MySQL Databases**

```bash
# Create database
Database: [username]_fern

# Create user
User: [username]_fern
Password: [strong password]

# Grant privileges: ALL PRIVILEGES

# Import via phpMyAdmin
- sql/schema.sql
- sql/activity_logs.sql
```

### 3. Setup Config

**File Manager → /home/[username]/public_html**

```bash
# Copy config template
cp config.example.php config.php

# Edit config.php
nano config.php
```

**Update these values:**

```php
// Database
define('DB_HOST', 'localhost');
define('DB_NAME', '[username]_fern');
define('DB_USER', '[username]_fern');
define('DB_PASS', '[your-password]');

// Application
define('APP_URL', 'https://yourdomain.com');
define('APP_DEBUG', false);

// Deployment (generate with: openssl rand -hex 32)
define('DEPLOY_SECRET', 'your-secret-token-here');
define('DEPLOY_ENABLED', true);
define('DEPLOY_BRANCH', 'main');
```

### 4. Setup Webhook

**GitHub → Settings → Webhooks → Add webhook**

```
Payload URL: https://yourdomain.com/deploy.php
Content type: application/json
Secret: [same as DEPLOY_SECRET in config.php]
Events: Just the push event
Active: ✅
```

### 5. Set Permissions

**Via cPanel File Manager or SSH:**

```bash
chmod 755 logs cache uploads backups
chmod 644 .htaccess config.php
chmod 755 deploy.php
```

---

## 🧪 Testing

### Test Deploy Script

```bash
# Via browser
https://yourdomain.com/deploy.php

# Expected output:
# Deploy script is active
# Repository: /home/[username]/public_html
# Branch: main
```

### Test Auto Deploy

```bash
# Make a change
echo "# Test" >> README.md

# Commit and push
git add README.md
git commit -m "Test auto deploy"
git push origin main

# Check webhook
# GitHub → Settings → Webhooks → Recent Deliveries
# Status should be: 200 OK ✅
```

### Test Website

```
https://yourdomain.com
https://yourdomain.com/admin
```

---

## 🔄 Daily Workflow

```bash
# 1. Edit code
nano file.php

# 2. Commit and push
git add .
git commit -m "Update feature"
git push origin main

# 3. Done! Website auto-updates ✅
```

---

## 📊 Monitoring

### Deployment Log

```bash
# Via browser
https://yourdomain.com/logs/deploy.log

# Via SSH
tail -f /home/[username]/public_html/logs/deploy.log
```

### Webhook Status

```
GitHub → Settings → Webhooks → Recent Deliveries
```

---

## 🐛 Troubleshooting

### Webhook Failed (403)

**Cause:** Invalid secret token

**Fix:**
- Check `DEPLOY_SECRET` in config.php
- Check Secret in GitHub webhook
- Must be exactly the same

### Webhook Failed (500)

**Cause:** Server error

**Fix:**
```bash
# Check deployment log
tail -f logs/deploy.log

# Check permissions
chmod 755 deploy.php
```

### Git Pull Failed

**Fix via SSH:**
```bash
cd /home/[username]/public_html
git status
git stash
git pull origin main
```

### Changes Not Showing

**Fix:**
- Clear browser cache (Ctrl+F5)
- Clear PHP opcache (cPanel → PHP Selector → Reset OPcache)
- Verify file updated on server

### Database Connection Error

**Fix:**
```bash
# Test connection
https://yourdomain.com/test-db-connection.php

# Check config.php credentials
# Check database user privileges
```

---

## 🔒 Security Checklist

- [ ] `APP_DEBUG = false` in production
- [ ] Strong `DEPLOY_SECRET` set
- [ ] Default admin password changed
- [ ] SSL certificate installed
- [ ] File protection active (.htaccess)
- [ ] Sensitive files not accessible:
  - https://yourdomain.com/config.php → 403
  - https://yourdomain.com/logs/ → 403
  - https://yourdomain.com/cache/ → 403

---

## 🔧 Advanced

### Rollback Deployment

```bash
# Via SSH
cd /home/[username]/public_html

# List backups
ls -la backups/

# Restore from backup
tar -xzf backups/backup-YYYY-MM-DD-HHMMSS.tar.gz
```

### Disable Auto Deploy

```php
// Edit config.php
define('DEPLOY_ENABLED', false);

// Or delete webhook in GitHub
```

### Maintenance Mode

```php
// Edit config.php
define('MAINTENANCE_MODE', true);
```

---

## 📝 Files Overview

### Core Files
- `.gitignore` - Protect sensitive files
- `.htaccess` - Security & performance
- `.cpanel.yml` - cPanel deployment config
- `config.example.php` - Config template
- `deploy.php` - Webhook handler

### Scripts
- `setup-deploy.sh` - Setup helper
- `test-webhook.sh` - Webhook tester

### Documentation
- `README.md` - Project overview
- `DEPLOY-SETUP.md` - Detailed setup guide
- `DEPLOYMENT.md` - This file

---

## 🆘 Support

### Logs
- `logs/deploy.log` - Deployment logs
- `logs/app-*.log` - Application logs
- cPanel Error Log

### Testing
```bash
# Test webhook
bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET

# Test database
https://yourdomain.com/test-db-connection.php
```

### Documentation
- See `DEPLOY-SETUP.md` for detailed instructions
- See `README.md` for project overview

---

## ✅ Quick Checklist

**Setup:**
- [ ] Git repository cloned in cPanel
- [ ] Database created and imported
- [ ] config.php configured
- [ ] Webhook created in GitHub
- [ ] Permissions set correctly

**Testing:**
- [ ] Deploy script accessible
- [ ] Test push successful
- [ ] Webhook status: 200 OK
- [ ] Website accessible
- [ ] Login works

**Security:**
- [ ] APP_DEBUG = false
- [ ] Admin password changed
- [ ] Sensitive files protected
- [ ] SSL enabled

---

**Repository:** https://github.com/fajrihrts/fern.git  
**Last Updated:** 2026-05-20

