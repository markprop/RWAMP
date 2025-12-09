# Deployment Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=118AB2&center=true&vCenter=true&width=600&lines=Deployment+Guides+%26+Checklists" alt="Deployment Header" />
</p>

This directory contains all deployment-related documentation and guides for the RWAMP platform. Follow these guides to deploy the application to production environments.

## 📄 Documents

### General Deployment
- **DEPLOYMENT_GUIDE.md** - Complete deployment guide for all platforms
- **DEPLOYMENT_CHECKLIST.md** - Pre-deployment checklist and verification
- **DEPLOYMENT_SUMMARY.md** - Deployment summary and best practices

### Hostinger Specific
- **HOSTINGER_DEPLOYMENT_GUIDE.md** - Complete Hostinger shared hosting deployment guide
- **HOSTINGER_DEPLOYMENT_FIXES.md** - Hostinger-specific fixes and troubleshooting
- **HOSTINGER_UPDATE_GUIDE.md** - Updating RWAMP on Hostinger servers

### Post-Deployment
- **POST_DEPLOYMENT_VERIFICATION.md** - Verification steps after deployment
- **SAFE_UPDATE_GUIDE.md** - Safe update procedures and rollback strategies

### Scripts
- **deploy-to-hostinger.sh** - Automated deployment script for Hostinger

## 🚀 Quick Start

### For Hostinger Shared Hosting (Recommended)
1. Review **DEPLOYMENT_CHECKLIST.md** before starting
2. Follow **HOSTINGER_DEPLOYMENT_GUIDE.md** step-by-step
3. Verify with **POST_DEPLOYMENT_VERIFICATION.md** after deployment

### For VPS/Dedicated Servers
1. Review **DEPLOYMENT_CHECKLIST.md**
2. Follow **DEPLOYMENT_GUIDE.md** for general deployment
3. Verify with **POST_DEPLOYMENT_VERIFICATION.md**

## 📋 Deployment Steps Overview

### 1. Preparation
- ✅ Review deployment checklist
- ✅ Backup current system (if updating)
- ✅ Prepare environment variables
- ✅ Build production assets: `npm run build`
- ✅ Optimize Laravel: `php artisan optimize`

### 2. Deployment
- ✅ Upload files to server
- ✅ Configure database connection
- ✅ Set environment variables
- ✅ Run migrations: `php artisan migrate`
- ✅ Create storage symlink: `php artisan storage:link`
- ✅ Set proper file permissions

### 3. Configuration
- ✅ Configure web server (Apache/Nginx)
- ✅ Setup SSL certificate
- ✅ Configure mail settings
- ✅ Setup cron jobs for scheduled tasks
- ✅ Configure queue workers (if using queues)

### 4. Verification
- ✅ Run post-deployment verification
- ✅ Test all critical features
- ✅ Monitor error logs
- ✅ Verify database connections
- ✅ Test payment flows

## 🔧 Platform-Specific Guides

### Hostinger Shared Hosting
**Primary Guide**: [`HOSTINGER_DEPLOYMENT_GUIDE.md`](HOSTINGER_DEPLOYMENT_GUIDE.md)

**Key Steps**:
1. Upload files to `public_html`
2. Configure `.env` file
3. Run migrations via SSH or phpMyAdmin
4. Set file permissions (755 for directories, 644 for files)
5. Configure cron jobs in cPanel

**Automated Script**: Use [`deploy-to-hostinger.sh`](deploy-to-hostinger.sh) for automated deployment

### VPS/Dedicated Server
**Primary Guide**: [`DEPLOYMENT_GUIDE.md`](DEPLOYMENT_GUIDE.md)

**Key Steps**:
1. Install PHP 8.2+, Composer, Node.js
2. Configure web server (Apache/Nginx)
3. Setup SSL certificate (Let's Encrypt recommended)
4. Configure database (MySQL/MariaDB)
5. Deploy using Laravel Forge, Envoyer, or manual deployment
6. Setup supervisor for queue workers
7. Configure cron jobs

## 📊 Production Checklist

Use **DEPLOYMENT_CHECKLIST.md** for a complete checklist. Key items:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Setup SSL certificate
- [ ] Configure mail settings (SMTP)
- [ ] Run `php artisan optimize`
- [ ] Run `npm run build`
- [ ] Setup queue workers
- [ ] Configure cron jobs
- [ ] Setup backups
- [ ] Enable monitoring
- [ ] Test all features
- [ ] Verify security headers

## 🔄 Update Procedures

### Safe Updates
Follow **SAFE_UPDATE_GUIDE.md** for safe update procedures:

1. **Backup**: Always backup database and files
2. **Test**: Test updates in staging first
3. **Deploy**: Follow deployment guide
4. **Verify**: Run post-deployment verification
5. **Monitor**: Monitor error logs after update

### Hostinger Updates
Follow **HOSTINGER_UPDATE_GUIDE.md** for Hostinger-specific update procedures.

## 🐛 Troubleshooting

### Common Issues
- **500 Errors**: Check `.env` configuration and file permissions
- **Database Connection**: Verify database credentials in `.env`
- **Asset Loading**: Ensure `npm run build` was executed
- **Storage Issues**: Verify `storage:link` symlink exists
- **Queue Not Working**: Check supervisor configuration

### Hostinger-Specific Issues
See **HOSTINGER_DEPLOYMENT_FIXES.md** for Hostinger-specific problems and solutions.

## 📚 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Environment Setup**: [`../environment/ENV-FIX-INSTRUCTIONS.md`](../environment/ENV-FIX-INSTRUCTIONS.md)
- **Database Setup**: [`../database/DATABASE_SETUP_GUIDE.md`](../database/DATABASE_SETUP_GUIDE.md)
- **Fixes**: [`../fixes/`](../fixes/) - Troubleshooting guides

## 🔗 Support

- **Website**: [rwamp.io](https://rwamp.io)
- **Email**: info@rwamp.net
- **Phone**: +92 370 1346038

---

## 🔙 Navigation

<p align="center">
  <a href="../../README.md">
    <img src="https://img.shields.io/badge/⬅️%20Back%20to%20Main-FF6B6B?style=for-the-badge&logo=arrow-left&logoColor=white" alt="Back to Main" />
  </a>
  <a href="../README.md">
    <img src="https://img.shields.io/badge/📚%20Documentation%20Index-06D6A0?style=for-the-badge&logo=book&logoColor=white" alt="Documentation Index" />
  </a>
</p>

---

**Last Updated:** January 27, 2025
