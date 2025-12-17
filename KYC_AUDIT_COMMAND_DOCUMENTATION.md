# KYC Image Audit and Repair Command Documentation

## Overview

The `kyc:audit-images` command audits and repairs KYC (Know Your Customer) image data to ensure database paths accurately reflect the actual file locations on the server's filesystem.

## Command Signature

```bash
php artisan kyc:audit-images [options]
```

## Options

- `--dry-run` : Run in audit mode without making any database changes (recommended for first run)
- `--base-path=` : Specify a custom base path for KYC images (default: auto-detects `public/kyc` or `storage/app/kyc`)
- `--fix-missing` : Automatically fix NULL paths if corresponding files exist in the directory

## Usage Examples

### 1. Audit Mode (Recommended First Step)

Run in dry-run mode to see what issues exist without making changes:

```bash
php artisan kyc:audit-images --dry-run
```

This will:
- Scan all users with `kyc_status` of 'pending' or 'approved'
- Check if files exist at the paths stored in the database
- Log all issues found
- Display a summary report
- **Make NO changes to the database**

### 2. Repair Mode with Auto-Fix

Run with auto-fix enabled to automatically repair NULL paths:

```bash
php artisan kyc:audit-images --fix-missing
```

This will:
- Perform all audit checks
- Automatically update NULL paths if files are found in the user's directory
- Log unresolved errors (files that don't exist)
- Update database records

### 3. Custom Base Path

If your KYC files are stored in a non-standard location:

```bash
php artisan kyc:audit-images --base-path=/var/www/html/public/kyc
```

### 4. Full Repair (No Dry-Run)

Run the full repair process:

```bash
php artisan kyc:audit-images --fix-missing
```

**Warning**: This will modify the database. Always run with `--dry-run` first!

## What the Command Does

### Target Users

The command processes only users where:
- `kyc_status` is either 'pending' or 'approved'
- `kyc_submitted_at` is not NULL

### Path Verification Logic

For each user, the command checks three image paths:
1. `kyc_id_front_path` - Front of ID document
2. `kyc_id_back_path` - Back of ID document (may be NULL for passports)
3. `kyc_selfie_path` - Selfie with ID document

#### Case 1: Path is NULL or Empty
- **If `--fix-missing` is enabled**: Searches the user's directory for a matching file
  - Looks for files with the type name in the filename (e.g., "front", "back", "selfie")
  - If found, updates the database with the correct relative path
- **If `--fix-missing` is disabled**: Only logs the issue

#### Case 2: Path Points to Existing File
- Verifies the file exists at the specified path
- Logs as "correct" - no action taken

#### Case 3: Path Points to Non-Existent File
- Logs as an error requiring manual intervention
- Does NOT automatically guess a new filename (safety measure)
- Suggests potential matches if found in the directory
- Increments "unresolved errors" counter

#### Case 4: User Directory Missing
- Logs as a critical error
- If all three paths are NULL and directory is missing, may change status to 'not_started'

#### Case 5: All Files Missing
- If all three paths are NULL or point to non-existent files:
  - Changes `kyc_status` to 'not_started' (allows user to resubmit)
  - Logs the status change

## Output and Reporting

### Progress Indicator

The command displays a progress bar showing:
- Total users processed
- Current progress

### Summary Report

At the end, displays a table with:

| Metric | Description |
|--------|-------------|
| Total Users Processed | Number of users checked |
| Users with Correct Paths | Users where all paths are valid |
| Paths Fixed | Number of NULL paths that were updated |
| Missing Directories | Users whose KYC directory doesn't exist |
| Unresolved Path Errors | Files that don't exist (require manual intervention) |
| Status Changes | Users whose status was changed to 'not_started' |
| Errors Encountered | Number of exceptions during processing |

### Logging

All operations are logged to `storage/logs/laravel.log` with detailed information:

- User ID and name
- Original KYC status
- Original image paths
- Verification results
- Actions taken
- Errors encountered

## Safety Features

### 1. Dry-Run Mode
- Default recommendation: Always run with `--dry-run` first
- Shows what would be changed without making changes
- Allows you to review before committing

### 2. No Automatic Guessing
- If a path points to a non-existent file, the command does NOT automatically guess a new filename
- This prevents incorrect path assignments
- Manual review required for these cases

### 3. Transaction Safety
- Each user update is independent
- If one user fails, others continue processing
- Errors are logged but don't stop the entire process

### 4. Path Validation
- Validates paths before updating
- Ensures paths are within the expected directory structure
- Prevents path traversal issues

## Business Logic Decisions

### Status Changes

The command may change `kyc_status` to 'not_started' in these cases:

1. **All paths NULL + directory missing**: User has no KYC data, allow resubmission
2. **All files missing**: All three paths point to non-existent files, allow resubmission

**Note**: This logic can be modified in the `processUser()` method if your business requirements differ.

### File Matching Logic

When `--fix-missing` is enabled and a path is NULL:

1. **First Priority**: Files with type name in filename (e.g., "front_123.jpg", "selfie_456.png")
2. **Second Priority**: Files sorted by modification time:
   - Front: First file (oldest)
   - Back: Second file (if 3+ files exist)
   - Selfie: Last file (newest)

## Troubleshooting

### "KYC base directory not found"

**Solution**: Specify the correct base path:
```bash
php artisan kyc:audit-images --base-path=/path/to/kyc
```

### High Number of Unresolved Errors

**Cause**: Files may have been moved or deleted outside the system.

**Solution**: 
1. Check the log file for details
2. Manually verify which files exist
3. Consider restoring from backup if needed

### Permission Denied Errors

**Cause**: PHP doesn't have read/write permissions to the KYC directory.

**Solution**: 
```bash
chmod -R 755 /path/to/kyc
chown -R www-data:www-data /path/to/kyc  # Adjust user/group as needed
```

## Best Practices

1. **Always run dry-run first**: `php artisan kyc:audit-images --dry-run`
2. **Review the summary**: Check the report before running without `--dry-run`
3. **Backup database**: Create a database backup before running repairs
4. **Monitor logs**: Check `storage/logs/laravel.log` for detailed information
5. **Run during low traffic**: Process large datasets during off-peak hours
6. **Schedule regular audits**: Run monthly to catch issues early

## Integration with Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Run KYC audit weekly in dry-run mode
    $schedule->command('kyc:audit-images --dry-run')
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

## Example Output

```
=== RUNNING IN DRY-RUN MODE (NO CHANGES WILL BE MADE) ===

Using public directory: /var/www/html/public/kyc

Found 150 users to process.

 150/150 [████████████████████████] 100%

=== AUDIT SUMMARY REPORT ===

+---------------------------+-------+
| Metric                    | Count |
+---------------------------+-------+
| Total Users Processed     | 150   |
| Users with Correct Paths  | 120   |
| Paths Fixed               | 15    |
| Missing Directories       | 3     |
| Unresolved Path Errors    | 12    |
| Status Changes            | 5     |
| Errors Encountered        | 0     |
+---------------------------+-------+

This was a dry-run. No changes were made to the database.
Run without --dry-run to apply fixes.

Base path used: public/kyc
Log file: /var/www/html/storage/logs/laravel.log
```

## Support

For issues or questions:
1. Check the log file: `storage/logs/laravel.log`
2. Review the summary report
3. Run with `--dry-run` to diagnose issues
4. Check file permissions and directory structure
