# Updates & Migration Documentation

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=118AB2&center=true&vCenter=true&width=600&lines=Update+Guides+%26+Migrations" alt="Updates Header" />
</p>

This directory contains update guides, migration instructions, and update checklists for the RWAMP platform.

## 📄 Documents

### Migration Guides
- **MIGRATION_GUIDE.md** - Complete migration guide (8.5KB, 311 lines)
- **QUICK_UPDATE_CHECKLIST.md** - Quick update checklist (2.5KB, 91 lines)
- **MANUAL_FILE_UPDATE_GUIDE.md** - Manual file update procedures (12KB, 433 lines)

### Update Information
- **UPDATE_TOKENS_SOLD.md** - Token sales update documentation (1.2KB, 56 lines)

## 🔄 Update Process

### Pre-Update Checklist
1. ✅ Review **QUICK_UPDATE_CHECKLIST.md** before starting
2. ✅ Backup current system (database and files)
3. ✅ Review **MIGRATION_GUIDE.md** for breaking changes
4. ✅ Test updates in staging environment first
5. ✅ Verify all dependencies are compatible

### Update Methods

#### Automated Updates
- ✅ Use deployment scripts (`deploy-to-hostinger.sh`)
- ✅ Follow deployment guides
- ✅ Automated testing and verification
- ✅ Git-based deployment

#### Manual Updates
- ✅ Follow **MANUAL_FILE_UPDATE_GUIDE.md** step-by-step
- ✅ Update files systematically
- ✅ Verify each step before proceeding
- ✅ Test after each major change

### Post-Update Verification
1. ✅ Verify all features work correctly
2. ✅ Check error logs: `storage/logs/laravel.log`
3. ✅ Test critical paths (login, payment, dashboard)
4. ✅ Update documentation if needed
5. ✅ Monitor system performance

## 📋 Update Categories

### Database Updates
**Commands**:
```bash
# Run migrations
php artisan migrate

# Check migration status
php artisan migrate:status

# Rollback if needed
php artisan migrate:rollback
```

**Checklist**:
- [ ] Review migration files for breaking changes
- [ ] Backup database before migration
- [ ] Run migrations in order
- [ ] Verify all tables created correctly
- [ ] Check foreign key constraints
- [ ] Verify ULID columns (if applicable)

### Code Updates
**Commands**:
```bash
# Update dependencies
composer update
npm update

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

**Checklist**:
- [ ] Review `composer.json` and `package.json` changes
- [ ] Check for breaking changes in dependencies
- [ ] Update environment variables if needed
- [ ] Test compatibility with existing code
- [ ] Verify all routes work
- [ ] Test authentication and authorization

### Configuration Updates
**Checklist**:
- [ ] Update `.env` file with new variables
- [ ] Review configuration files in `config/`
- [ ] Test new features and settings
- [ ] Verify third-party API keys
- [ ] Check mail and payment configurations

## 🚨 Important Update Notes

### Breaking Changes
- **ULID Migration**: Existing numeric IDs are automatically migrated to ULID
- **Route Changes**: Some admin routes now use ULID (`/a/u/{ulid}`)
- **Game System**: New tables and fields added
- **Chat System**: Infrastructure ready, routes may need enabling

### Backward Compatibility
- ✅ Numeric ID routes redirect to ULID routes
- ✅ Legacy route names still work
- ✅ Database migrations are non-destructive where possible

## 📊 Recent Updates

### January 27, 2025
- ✅ Trading Game System implemented
- ✅ ULID URL obfuscation added
- ✅ WalletConnect v2 integration
- ✅ Weighted-average pricing
- ✅ Documentation reorganization

### Update History
- See Git commit history for detailed update log
- Check **MIGRATION_GUIDE.md** for migration-specific changes

## 🔧 Troubleshooting Updates

### Migration Failures
1. Check database connection
2. Verify migration file syntax
3. Check for conflicting migrations
4. Review error messages in logs

### Update Errors
1. Check error logs: `storage/logs/laravel.log`
2. Verify file permissions
3. Check environment configuration
4. Review **MANUAL_FILE_UPDATE_GUIDE.md**

### Rollback Procedures
1. Restore database backup
2. Revert code changes via Git
3. Clear all caches
4. Verify system functionality

## 📚 Related Documentation

- **Main README**: [`../../README.md`](../../README.md)
- **Deployment**: [`../deployment/SAFE_UPDATE_GUIDE.md`](../deployment/SAFE_UPDATE_GUIDE.md)
- **Database**: [`../database/DATABASE_SETUP_GUIDE.md`](../database/DATABASE_SETUP_GUIDE.md)
- **Fixes**: [`../fixes/`](../fixes/) - Troubleshooting

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
