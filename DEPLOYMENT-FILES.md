# 📦 Deployment Files Overview

Dokumentasi lengkap semua file yang terkait dengan deployment.

---

## 📁 File Structure

```
fern/
├── .gitignore                    # Git ignore rules
├── .htaccess                     # Apache configuration
├── .cpanel.yml                   # cPanel deployment config
├── config.example.php            # Config template
├── deploy.php                    # Auto-deploy webhook handler
├── setup-deploy.sh               # Setup script
├── test-webhook.sh               # Webhook testing script
│
├── backups/                      # Auto-backup directory
│   └── .gitignore               # Ignore backup files
│
├── uploads/                      # User uploads
│   ├── .htaccess                # Upload protection
│   └── README.md                # Upload docs
│
└── docs/
    ├── DEPLOYMENT-GUIDE.md       # Detailed deployment guide
    ├── DEPLOYMENT-CHECKLIST.md   # Deployment checklist
    ├── QUICK-DEPLOY.md           # Quick reference
    └── DEPLOY-SETUP.md           # Setup instructions
```

---

## 📄 File Descriptions

### Core Deployment Files

#### `.gitignore`
**Purpose:** Mencegah file sensitif ter-commit ke repository

**Isi:**
- Config files (config.php, .env)
- Logs (logs/*.log)
- Cache (cache/*.cache)
- Uploads (uploads/*)
- Backups (backups/*)
- System files (.DS_Store, Thumbs.db)

**Status:** ✅ Harus di-commit

---

#### `.htaccess`
**Purpose:** Apache web server configuration

**Fitur:**
- URL rewriting
- Security headers
- File protection
- Gzip compression
- Browser caching
- Deploy script protection (optional)

**Status:** ✅ Harus di-commit

---

#### `.cpanel.yml`
**Purpose:** cPanel deployment automation

**Fungsi:**
- Copy files ke deployment path
- Create necessary directories
- Set file permissions
- Create .htaccess for protected dirs
- Clear cache
- Log deployment

**Status:** ✅ Harus di-commit

---

#### `config.example.php`
**Purpose:** Template untuk config.php

**Berisi:**
- Database configuration
- Application settings
- Security settings
- File upload settings
- Cache settings
- Deployment settings

**Status:** ✅ Harus di-commit

**Note:** Copy ke `config.php` dan edit dengan kredensial sebenarnya

---

#### `deploy.php`
**Purpose:** Webhook handler untuk auto-deployment

**Fitur:**
- Webhook signature verification
- Automatic git pull
- Pre-deployment backup
- Post-deployment commands
- Deployment logging
- Error handling

**Endpoints:**
- GET: Status check
- POST: Webhook handler

**Status:** ✅ Harus di-commit

**Security:** 
- Requires valid signature
- Can be IP-restricted via .htaccess

---

### Setup & Testing Scripts

#### `setup-deploy.sh`
**Purpose:** Setup script untuk persiapan deployment

**Fungsi:**
- Check git initialization
- Create config.php from example
- Create necessary directories
- Create .htaccess for protected dirs
- Set file permissions
- Setup git remote
- Generate deploy secret

**Usage:**
```bash
bash setup-deploy.sh
```

**Status:** ✅ Harus di-commit

---

#### `test-webhook.sh`
**Purpose:** Test webhook deployment secara manual

**Fungsi:**
- Send test webhook request
- Verify signature
- Check response status
- Provide troubleshooting tips

**Usage:**
```bash
bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET
```

**Status:** ✅ Harus di-commit

---

### Documentation Files

#### `DEPLOYMENT-GUIDE.md`
**Purpose:** Panduan deployment lengkap dan detail

**Isi:**
- Setup repository Git
- Setup Git di cPanel
- Setup database
- Setup config file
- Setup webhook
- Set permissions
- Testing procedures
- Security hardening
- Monitoring
- Troubleshooting

**Audience:** Developer, DevOps

**Status:** ✅ Harus di-commit

---

#### `QUICK-DEPLOY.md`
**Purpose:** Quick reference untuk deployment

**Isi:**
- Setup awal (ringkas)
- Workflow harian
- Monitoring commands
- Troubleshooting quick fixes
- Useful commands
- Emergency procedures

**Audience:** Developer (daily use)

**Status:** ✅ Harus di-commit

---

#### `DEPLOYMENT-CHECKLIST.md`
**Purpose:** Checklist lengkap untuk deployment

**Isi:**
- Pre-deployment checklist
- Deployment setup checklist
- Testing checklist
- Post-deployment checklist
- Ongoing maintenance checklist
- Emergency procedures checklist

**Audience:** DevOps, QA

**Status:** ✅ Harus di-commit

---

#### `DEPLOY-SETUP.md`
**Purpose:** Setup instructions untuk auto-deploy

**Isi:**
- Setup Git di cPanel
- Setup deploy script
- Setup webhook
- Test deployment
- Advanced configuration
- Monitoring

**Audience:** DevOps

**Status:** ✅ Harus di-commit (sudah ada sebelumnya)

---

### Protected Directories

#### `backups/.gitignore`
**Purpose:** Ignore all backup files

**Content:**
```
*
!.gitignore
```

**Status:** ✅ Harus di-commit

---

#### `uploads/.htaccess`
**Purpose:** Protect uploads directory

**Fitur:**
- Deny PHP execution
- Allow only specific file types
- Disable directory listing
- Disable script execution

**Status:** ✅ Harus di-commit

---

#### `uploads/README.md`
**Purpose:** Documentation untuk uploads directory

**Status:** ✅ Harus di-commit

---

## 🔐 Security Considerations

### Files That Should NEVER Be Committed

❌ **config.php** - Contains database credentials
❌ **logs/*.log** - Contains sensitive logs
❌ **cache/*.cache** - Contains cached data
❌ **uploads/** - Contains user uploads
❌ **backups/** - Contains backup files
❌ **.env** - Environment variables

### Files That MUST Be Committed

✅ **.gitignore** - Protects sensitive files
✅ **.htaccess** - Security configuration
✅ **.cpanel.yml** - Deployment automation
✅ **config.example.php** - Config template
✅ **deploy.php** - Deployment handler
✅ **setup-deploy.sh** - Setup script
✅ **test-webhook.sh** - Testing script
✅ **All documentation files**

---

## 🚀 Deployment Workflow

### 1. Initial Setup (One Time)

```bash
# Run setup script
bash setup-deploy.sh

# Edit config.php
nano config.php

# Commit and push
git add .
git commit -m "Setup deployment"
git push origin main
```

### 2. Setup cPanel (One Time)

1. Create Git repository in cPanel
2. Setup deployment path
3. Create database
4. Import database schema
5. Copy and edit config.php
6. Setup webhook

### 3. Daily Workflow

```bash
# Edit code
nano file.php

# Commit
git add .
git commit -m "Update feature"

# Push (auto deploy!)
git push origin main
```

### 4. Monitoring

```bash
# Check deployment status
curl https://yourdomain.com/deploy.php

# Check logs
tail -f logs/deploy.log
tail -f logs/app-*.log

# Check webhook
# GitHub: Settings → Webhooks → Recent Deliveries
```

---

## 📊 File Permissions

### Directories
```bash
chmod 755 logs
chmod 755 cache
chmod 755 uploads
chmod 755 backups
```

### Files
```bash
chmod 644 .htaccess
chmod 644 config.php
chmod 755 deploy.php
chmod 755 setup-deploy.sh
chmod 755 test-webhook.sh
```

### Protected Directories
```bash
# Create .htaccess with "Deny from all"
echo "Deny from all" > logs/.htaccess
echo "Deny from all" > cache/.htaccess
echo "Deny from all" > backups/.htaccess
```

---

## 🧪 Testing

### Test Deploy Script

```bash
# Via browser
https://yourdomain.com/deploy.php

# Expected output:
# Deploy script is active
# Repository: /home/username/public_html
# Branch: main
# Last deployment: Never
```

### Test Webhook

```bash
# Using test script
bash test-webhook.sh https://yourdomain.com/deploy.php YOUR_SECRET

# Expected: Status 200 OK
```

### Test Auto Deploy

```bash
# Make a change
echo "# Test" >> README.md

# Commit and push
git add README.md
git commit -m "Test auto deploy"
git push origin main

# Check webhook delivery
# GitHub: Settings → Webhooks → Recent Deliveries
# Expected: Status 200 OK

# Check deployment log
tail -f logs/deploy.log
```

---

## 🔧 Customization

### Modify Deploy Script

Edit `deploy.php`:

```php
// Change branch
define('DEPLOY_BRANCH', 'production');

// Add post-deploy commands
define('POST_DEPLOY_COMMANDS', [
    'composer install --no-dev',
    'php cleanup.php',
]);

// Change backup retention
// Keep only last 10 backups instead of 5
```

### Modify cPanel Deployment

Edit `.cpanel.yml`:

```yaml
# Change deployment path
- export DEPLOYPATH=/home/username/subdomain/

# Add custom commands
- php $DEPLOYPATH/artisan migrate --force
```

### Modify Webhook Restrictions

Edit `.htaccess`:

```apache
# Uncomment and add your IP
<Files "deploy.php">
    Allow from YOUR.IP.ADDRESS.HERE
</Files>
```

---

## 📝 Maintenance

### Regular Tasks

**Daily:**
- Check deployment logs
- Monitor webhook deliveries

**Weekly:**
- Review application logs
- Check disk space
- Test backup restoration

**Monthly:**
- Clean old logs
- Clean old backups
- Security audit
- Update dependencies

---

## 🆘 Troubleshooting

### Common Issues

**Issue:** Webhook returns 403
**Solution:** Check DEPLOY_SECRET matches in config.php and webhook

**Issue:** Git pull fails
**Solution:** Check git status, resolve conflicts, check permissions

**Issue:** Changes not reflected
**Solution:** Clear browser cache, clear PHP opcache, verify file updated

**Issue:** Database connection error
**Solution:** Check config.php credentials, test connection

---

## 📞 Support

### Documentation

- **DEPLOYMENT-GUIDE.md** - Detailed guide
- **QUICK-DEPLOY.md** - Quick reference
- **DEPLOYMENT-CHECKLIST.md** - Checklist
- **DEPLOY-SETUP.md** - Setup instructions

### Logs

- **logs/deploy.log** - Deployment logs
- **logs/app-*.log** - Application logs
- **cPanel Error Log** - Server errors

### Testing

- **test-webhook.sh** - Test webhook
- **test-db-connection.php** - Test database

---

## 🎯 Quick Commands

```bash
# Setup
bash setup-deploy.sh

# Test webhook
bash test-webhook.sh https://yourdomain.com/deploy.php SECRET

# Check status
curl https://yourdomain.com/deploy.php

# View logs
tail -f logs/deploy.log
tail -f logs/app-*.log

# Manual deploy (via SSH)
cd /home/username/public_html
git pull origin main

# Rollback (via SSH)
cd /home/username/public_html
tar -xzf backups/backup-YYYY-MM-DD-HHMMSS.tar.gz
```

---

**Last Updated:** 2026-05-20

