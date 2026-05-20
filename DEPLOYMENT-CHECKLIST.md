# ✅ Deployment Checklist

Checklist lengkap untuk memastikan deployment berjalan lancar.

---

## 📋 Pre-Deployment Checklist

### Local Setup

- [ ] Git repository sudah di-initialize
- [ ] Remote repository sudah di-set (GitHub/GitLab/Bitbucket)
- [ ] `.gitignore` sudah di-setup dengan benar
- [ ] `config.example.php` sudah dibuat
- [ ] `config.php` TIDAK ter-commit ke repository
- [ ] File sensitif TIDAK ter-commit (logs, cache, uploads)
- [ ] `deploy.php` sudah ada dan ter-commit
- [ ] `.cpanel.yml` sudah ada dan ter-commit
- [ ] Dokumentasi lengkap (README.md, DEPLOYMENT-GUIDE.md)
- [ ] Database schema files ada di folder `sql/`
- [ ] Code sudah di-test di local
- [ ] Tidak ada error di local testing

### Security

- [ ] Strong `DEPLOY_SECRET` sudah di-generate
- [ ] Default passwords sudah diganti
- [ ] `.htaccess` protection sudah di-setup
- [ ] Sensitive files protected (config.php, .env)
- [ ] Protected directories punya `.htaccess` (logs, cache, backups)
- [ ] File upload validation sudah implemented
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS protection sudah implemented
- [ ] CSRF protection sudah implemented

---

## 🚀 Deployment Setup Checklist

### cPanel Setup

- [ ] Login ke cPanel berhasil
- [ ] Git Version Control tersedia di cPanel
- [ ] SSH access tersedia (opsional tapi recommended)
- [ ] PHP version sesuai requirement (8.0+)
- [ ] MySQL/MariaDB tersedia
- [ ] Disk space cukup (minimal 500MB)
- [ ] SSL certificate sudah installed

### Git Repository Setup

- [ ] Repository dibuat di cPanel Git Version Control
- [ ] Clone URL sudah benar
- [ ] Repository path sudah benar
- [ ] Deploy path sudah di-set ke public_html
- [ ] Branch sudah di-set (main/master)
- [ ] Git pull manual berhasil

### Database Setup

- [ ] Database sudah dibuat di cPanel
- [ ] Database user sudah dibuat
- [ ] User sudah di-assign ke database
- [ ] User punya ALL PRIVILEGES
- [ ] `schema.sql` sudah di-import
- [ ] `activity_logs.sql` sudah di-import
- [ ] Database connection test berhasil
- [ ] Tables sudah terbuat dengan benar

### Config File Setup

- [ ] `config.php` sudah dibuat dari `config.example.php`
- [ ] `DB_HOST` sudah benar (biasanya localhost)
- [ ] `DB_NAME` sudah benar
- [ ] `DB_USER` sudah benar
- [ ] `DB_PASS` sudah benar
- [ ] `APP_URL` sudah benar (https://yourdomain.com)
- [ ] `APP_DEBUG` set ke `false`
- [ ] `DEPLOY_SECRET` sudah di-set dengan token yang kuat
- [ ] `DEPLOY_ENABLED` set ke `true`
- [ ] `DEPLOY_BRANCH` sudah benar (main/master)
- [ ] Timezone sudah benar

### File Permissions

- [ ] `logs/` → 755
- [ ] `cache/` → 755
- [ ] `uploads/` → 755
- [ ] `backups/` → 755
- [ ] `.htaccess` → 644
- [ ] `config.php` → 644
- [ ] `deploy.php` → 755
- [ ] Protected directories punya `.htaccess`

### Webhook Setup

- [ ] Webhook URL sudah benar (https://yourdomain.com/deploy.php)
- [ ] Secret token sama dengan `DEPLOY_SECRET` di config.php
- [ ] Content type: application/json (GitHub)
- [ ] Trigger: Push events
- [ ] Branch filter: main (atau branch yang sesuai)
- [ ] SSL verification enabled
- [ ] Webhook active/enabled
- [ ] Test delivery berhasil (status 200)

---

## 🧪 Testing Checklist

### Manual Testing

- [ ] Website accessible (https://yourdomain.com)
- [ ] Homepage load dengan benar
- [ ] Static assets load (CSS, JS, images)
- [ ] No 404 errors
- [ ] No PHP errors
- [ ] Database connection works

### Deploy Script Testing

- [ ] `deploy.php` accessible via browser
- [ ] Deploy script menampilkan status
- [ ] Manual webhook test berhasil
- [ ] Git pull works
- [ ] Backup creation works
- [ ] Deployment log created

### Webhook Testing

- [ ] Push test commit ke repository
- [ ] Webhook triggered automatically
- [ ] Webhook delivery status: 200 OK
- [ ] Deployment log shows success
- [ ] Changes reflected on website
- [ ] No errors in deployment log

### Functionality Testing

- [ ] Login page works
- [ ] Registration works
- [ ] Admin login works
- [ ] Dashboard loads
- [ ] Database queries work
- [ ] File upload works
- [ ] Session management works
- [ ] CSRF protection works
- [ ] Rate limiting works

### Security Testing

- [ ] Cannot access `config.php` via browser
- [ ] Cannot access `logs/` via browser
- [ ] Cannot access `cache/` via browser
- [ ] Cannot access `backups/` via browser
- [ ] Cannot execute PHP in `uploads/`
- [ ] HTTPS redirect works (if enabled)
- [ ] Security headers present
- [ ] XSS protection works
- [ ] SQL injection prevention works

---

## 📊 Post-Deployment Checklist

### Immediate Actions

- [ ] Change default admin password
- [ ] Test all critical features
- [ ] Check error logs for issues
- [ ] Verify database data
- [ ] Test user registration flow
- [ ] Test file upload functionality
- [ ] Verify email sending (if enabled)
- [ ] Check mobile responsiveness

### Monitoring Setup

- [ ] Setup uptime monitoring (UptimeRobot, Pingdom)
- [ ] Setup error tracking (optional)
- [ ] Setup log monitoring
- [ ] Setup backup monitoring
- [ ] Document monitoring credentials

### Backup Setup

- [ ] Verify auto-backup works (before each deploy)
- [ ] Setup database backup cron job
- [ ] Setup file backup schedule
- [ ] Test backup restoration
- [ ] Document backup locations
- [ ] Setup off-site backup (optional)

### Performance Optimization

- [ ] Enable gzip compression
- [ ] Enable browser caching
- [ ] Enable PHP opcache
- [ ] Optimize images
- [ ] Minify CSS/JS (optional)
- [ ] Setup CDN (optional)

### Documentation

- [ ] Document deployment process
- [ ] Document server credentials (securely)
- [ ] Document database credentials (securely)
- [ ] Document webhook setup
- [ ] Document backup procedures
- [ ] Document rollback procedures
- [ ] Create runbook for common issues

---

## 🔄 Ongoing Maintenance Checklist

### Daily

- [ ] Check deployment logs
- [ ] Check application logs
- [ ] Monitor uptime
- [ ] Check for errors

### Weekly

- [ ] Review webhook deliveries
- [ ] Check disk space usage
- [ ] Review security logs
- [ ] Test backup restoration
- [ ] Update dependencies (if needed)

### Monthly

- [ ] Security audit
- [ ] Performance review
- [ ] Database optimization
- [ ] Clean old logs
- [ ] Clean old backups
- [ ] Review and update documentation

### Quarterly

- [ ] Full security audit
- [ ] Disaster recovery test
- [ ] Review and update dependencies
- [ ] Performance optimization review
- [ ] User feedback review

---

## 🆘 Emergency Procedures Checklist

### If Deployment Fails

- [ ] Check webhook delivery status
- [ ] Check deployment log for errors
- [ ] Check PHP error log
- [ ] Test manual git pull
- [ ] Check file permissions
- [ ] Verify config.php settings
- [ ] Rollback to previous version if needed

### If Website Down

- [ ] Check server status
- [ ] Check database connection
- [ ] Check error logs
- [ ] Enable maintenance mode
- [ ] Restore from backup if needed
- [ ] Contact hosting support if needed

### If Database Issues

- [ ] Check database connection
- [ ] Check database credentials
- [ ] Check database server status
- [ ] Restore from backup if needed
- [ ] Check for corrupted tables
- [ ] Run database repair if needed

---

## 📝 Sign-off

### Deployment Team

- [ ] Developer sign-off: _________________ Date: _______
- [ ] QA sign-off: _________________ Date: _______
- [ ] Admin sign-off: _________________ Date: _______

### Deployment Details

```
Deployment Date: _________________
Deployment Time: _________________
Deployed By: _________________
Git Commit Hash: _________________
Deployment Status: [ ] Success [ ] Failed
Issues Found: _________________
Resolution: _________________
```

---

## 📞 Contact Information

### Support Contacts

```
Developer: _________________
Email: _________________
Phone: _________________

System Admin: _________________
Email: _________________
Phone: _________________

Hosting Support: _________________
Email: _________________
Phone: _________________
```

---

## 📖 References

- **Deployment Guide:** DEPLOYMENT-GUIDE.md
- **Quick Reference:** QUICK-DEPLOY.md
- **Setup Instructions:** DEPLOY-SETUP.md
- **Project README:** README.md
- **Database Docs:** DATABASE-ERROR-HANDLING.md

---

**Last Updated:** 2026-05-20

