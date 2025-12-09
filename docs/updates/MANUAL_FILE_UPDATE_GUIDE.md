# Manual File Update Guide for Hostinger

This guide will help you manually update your RWAMP Laravel project files on Hostinger using File Manager or FTP.

## Prerequisites

1. **Access to Hostinger hPanel**
2. **File Manager access** OR **FTP client** (FileZilla, WinSCP, etc.)
3. **List of files to update** (provided below)

## Method 1: Using Hostinger File Manager (Easiest)

### Step 1: Access File Manager

1. Login to your **Hostinger hPanel**
2. Go to **Files** → **File Manager**
3. Navigate to your project directory:
   - Usually: `public_html/`
   - Or: `public_html/rwamp-laravel/` (if in subdirectory)

### Step 2: Download Files from GitHub

1. Go to your GitHub repository: `https://github.com/markprop/RWAMP`
2. Click on the file you want to update
3. Click **Raw** button (top right)
4. Right-click → **Save As** to download the file
5. Repeat for each file you need to update

### Step 3: Upload Files to Hostinger

1. In Hostinger File Manager, navigate to the correct directory
2. Click **Upload** button
3. Select the downloaded file(s)
4. Wait for upload to complete
5. Verify the file was uploaded correctly

### Step 4: Set Correct Permissions

1. Right-click on uploaded file
2. Select **Change Permissions**
3. Set permissions:
   - **Files**: `644` or `644`
   - **Directories**: `755`
   - **storage/** and **bootstrap/cache/**: `775`

## Method 2: Using FTP Client (FileZilla)

### Step 1: Connect to Hostinger via FTP

1. **Download FileZilla** (if not installed): https://filezilla-project.org/
2. **Get FTP credentials** from Hostinger:
   - Go to hPanel → **Files** → **FTP Accounts**
   - Note: Host, Username, Password, Port (usually 21)

3. **Connect in FileZilla**:
   - Host: `ftp.yourdomain.com` or your IP
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: `21`

### Step 2: Download Updated Files from GitHub

1. Go to your GitHub repository
2. Navigate to the file you need
3. Click **Raw** button
4. Save the file locally

### Step 3: Upload Files via FileZilla

1. **Left panel (Local)**: Navigate to where you saved the file
2. **Right panel (Remote)**: Navigate to your project directory on server
3. **Drag and drop** the file from left to right
4. **Confirm overwrite** if prompted

## Files That Need to Be Updated

### Critical Files (Must Update)

#### 1. Security Headers Middleware
**Path:** `app/Http/Middleware/SecurityHeaders.php`
- **Why:** Contains CSP fixes for tawk.to integration
- **Action:** Replace entire file

#### 2. Tawk.to Component
**Path:** `resources/views/components/tawk-to.blade.php`
- **Why:** New tawk.to integration component
- **Action:** Upload new file

#### 3. Services Configuration
**Path:** `config/services.php`
- **Why:** Contains tawk.to configuration
- **Action:** Update file (add tawk.to config section)

#### 4. Layout File
**Path:** `resources/views/layouts/app.blade.php`
- **Why:** Includes tawk.to component
- **Action:** Check if `@include('components.tawk-to')` is present before `</body>`

### Dashboard Files

#### 5. Investor Dashboard
**Path:** `resources/views/dashboard/investor.blade.php`
- **Why:** Portfolio value calculations
- **Action:** Replace entire file

#### 6. Reseller Dashboard
**Path:** `resources/views/dashboard/reseller.blade.php`
- **Why:** Portfolio value calculations
- **Action:** Replace entire file

#### 7. Reseller Sell Page
**Path:** `resources/views/dashboard/reseller-sell.blade.php`
- **Why:** Dynamic balance calculation, wallet lookup fixes
- **Action:** Replace entire file

### Controller Files

#### 8. Crypto Payment Controller
**Path:** `app/Http/Controllers/CryptoPaymentController.php`
- **Why:** Portfolio calculations, buy from reseller updates
- **Action:** Replace entire file

#### 9. Reseller Controller
**Path:** `app/Http/Controllers/ResellerController.php`
- **Why:** Portfolio calculations, balance check removal
- **Action:** Replace entire file

#### 10. Profile Controller
**Path:** `app/Http/Controllers/ProfileController.php`
- **Why:** Official price calculation
- **Action:** Replace entire file

### View Files

#### 11. User Profile
**Path:** `resources/views/auth/profile.blade.php`
- **Why:** Value calculation, transaction history updates
- **Action:** Replace entire file

#### 12. User History
**Path:** `resources/views/dashboard/user-history.blade.php`
- **Why:** Request submission history, price columns
- **Action:** Replace entire file

#### 13. Buy From Reseller Modal
**Path:** `resources/views/components/buy-from-reseller-modal.blade.php`
- **Why:** Reseller highlighting, balance removal
- **Action:** Replace entire file

### Model Files

#### 14. User Model
**Path:** `app/Models/User.php`
- **Why:** New relationships, fields
- **Action:** Replace entire file

### New Files (If Not Exists)

#### 15. Broadcasting Config
**Path:** `config/broadcasting.php`
- **Why:** Pusher configuration (if using chat)
- **Action:** Upload new file

#### 16. Routes Channels
**Path:** `routes/channels.php`
- **Why:** Broadcasting channels (if using chat)
- **Action:** Upload new file (if chat is enabled)

## Step-by-Step Manual Update Process

### Phase 1: Backup Current Files

1. **Create backup folder** on your server:
   ```
   public_html/backup_2025_11_20/
   ```

2. **Copy critical files** to backup:
   - All files in `app/Http/Controllers/`
   - All files in `app/Http/Middleware/`
   - All files in `resources/views/`
   - `config/services.php`
   - `.env` file (download it first!)

### Phase 2: Update Configuration Files

1. **Update `config/services.php`**:
   - Open file in File Manager
   - Add tawk.to configuration section:
   ```php
   'tawk' => [
       'enabled' => env('TAWK_ENABLED', true),
       'property_id' => env('TAWK_PROPERTY_ID'),
       'widget_id' => env('TAWK_WIDGET_ID'),
   ],
   ```

2. **Update `.env` file**:
   - Download `.env` file first (backup!)
   - Add these lines:
   ```env
   # Tawk.to Live Chat Widget Configuration
   TAWK_ENABLED=true
   TAWK_PROPERTY_ID=691ec32b545b891960a7807b
   TAWK_WIDGET_ID=1jag2kp6s
   ```
   - Upload updated `.env` file

### Phase 3: Update Application Files

1. **Update Middleware**:
   - Upload `app/Http/Middleware/SecurityHeaders.php`
   - Verify file permissions: `644`

2. **Update Controllers**:
   - Upload all controller files from the list above
   - Verify file permissions: `644`

3. **Update Models**:
   - Upload `app/Models/User.php`
   - Verify file permissions: `644`

### Phase 4: Update View Files

1. **Update Layout**:
   - Open `resources/views/layouts/app.blade.php`
   - Verify `@include('components.tawk-to')` is before `</body>`
   - If missing, add it

2. **Upload New Component**:
   - Upload `resources/views/components/tawk-to.blade.php`
   - Verify file permissions: `644`

3. **Update Dashboard Views**:
   - Upload all dashboard view files
   - Verify file permissions: `644`

4. **Update Other Views**:
   - Upload profile, history, and modal files
   - Verify file permissions: `644`

### Phase 5: Run Database Migrations

**Via SSH** (if you have access):
```bash
cd public_html
php artisan migrate --force
```

**Via Hostinger Terminal** (if available):
1. Go to hPanel → **Advanced** → **Terminal**
2. Run: `php artisan migrate --force`

**If no SSH/Terminal access:**
- Migrations will run automatically on next page load (if auto-migration is enabled)
- OR contact Hostinger support to run migrations

### Phase 6: Clear Caches

**Via SSH/Terminal:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Via File Manager:**
1. Delete these folders (they will regenerate):
   - `bootstrap/cache/*` (delete contents, not folder)
   - `storage/framework/cache/*` (delete contents)
   - `storage/framework/views/*` (delete contents)

2. **Recreate cache** by visiting your website (it will auto-generate)

### Phase 7: Verify Permissions

Set correct permissions:

**Via File Manager:**
1. Right-click on `storage/` folder → **Change Permissions** → `775`
2. Right-click on `bootstrap/cache/` folder → **Change Permissions** → `775`
3. For all PHP files: `644`
4. For all directories: `755`

**Via SSH:**
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 644 app resources config
```

## Quick Update Checklist

Use this checklist to ensure you update everything:

### Configuration
- [ ] `config/services.php` - Added tawk.to config
- [ ] `.env` - Added tawk.to variables
- [ ] `config/broadcasting.php` - Uploaded (if using chat)

### Middleware
- [ ] `app/Http/Middleware/SecurityHeaders.php` - Updated

### Controllers
- [ ] `app/Http/Controllers/CryptoPaymentController.php` - Updated
- [ ] `app/Http/Controllers/ResellerController.php` - Updated
- [ ] `app/Http/Controllers/ProfileController.php` - Updated

### Models
- [ ] `app/Models/User.php` - Updated

### Views - Components
- [ ] `resources/views/components/tawk-to.blade.php` - Uploaded
- [ ] `resources/views/components/buy-from-reseller-modal.blade.php` - Updated
- [ ] `resources/views/layouts/app.blade.php` - Verified tawk.to include

### Views - Dashboards
- [ ] `resources/views/dashboard/investor.blade.php` - Updated
- [ ] `resources/views/dashboard/reseller.blade.php` - Updated
- [ ] `resources/views/dashboard/reseller-sell.blade.php` - Updated
- [ ] `resources/views/dashboard/user-history.blade.php` - Updated

### Views - Auth
- [ ] `resources/views/auth/profile.blade.php` - Updated

### Post-Update
- [ ] Database migrations run
- [ ] Caches cleared
- [ ] Permissions set correctly
- [ ] `.env` file updated
- [ ] Website tested

## Testing After Update

1. **Clear browser cache** (Ctrl+Shift+Delete)
2. **Visit homepage** - Should load without errors
3. **Login** - Should work correctly
4. **Check dashboard** - Portfolio values should display
5. **Check tawk.to widget** - Should appear when logged in
6. **Test buy from reseller** - Should work correctly
7. **Check browser console** - Should have minimal errors

## Troubleshooting

### Issue: 500 Error After Update

**Solution:**
1. Check `storage/logs/laravel.log` for errors
2. Verify file permissions are correct
3. Clear all caches
4. Check `.env` file has all required variables

### Issue: Tawk.to Widget Not Showing

**Solution:**
1. Verify `.env` has tawk.to variables
2. Check `config/services.php` has tawk.to config
3. Verify `tawk-to.blade.php` component exists
4. Check `app.blade.php` includes the component
5. Clear config cache
6. Verify user is logged in (widget only for authenticated users)

### Issue: Portfolio Values Not Showing

**Solution:**
1. Check if migrations ran successfully
2. Verify controller files are updated
3. Clear view cache
4. Check browser console for JavaScript errors

### Issue: Files Not Uploading

**Solution:**
1. Check file size limits
2. Verify you have write permissions
3. Try uploading one file at a time
4. Use FTP instead of File Manager for large files

## Important Notes

1. **Always backup** before updating
2. **Update `.env` file** with new variables
3. **Clear caches** after updating files
4. **Test thoroughly** after each update
5. **Keep backup** of old files for rollback

## Rollback Plan

If something goes wrong:

1. **Restore from backup**:
   - Copy files from `backup_2025_11_20/` back to original locations
   - Restore `.env` file from backup

2. **Clear caches**:
   ```bash
   php artisan optimize:clear
   ```

3. **Test website** - Should work as before

## File Size Considerations

- **Small files** (< 1MB): Use File Manager
- **Large files** (> 1MB): Use FTP
- **Many files**: Use Git (if SSH access available)

## Alternative: Partial Update

If you only want to update specific features:

### Update Only Tawk.to Integration:
- `config/services.php`
- `.env` (add tawk.to variables)
- `app/Http/Middleware/SecurityHeaders.php`
- `resources/views/components/tawk-to.blade.php`
- `resources/views/layouts/app.blade.php`

### Update Only Portfolio Calculations:
- `app/Http/Controllers/CryptoPaymentController.php`
- `app/Http/Controllers/ResellerController.php`
- `resources/views/dashboard/investor.blade.php`
- `resources/views/dashboard/reseller.blade.php`

---

**Last Updated:** 2025-11-20
**Version:** 1.0

