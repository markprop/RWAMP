# User Sync & ULID Backfill Migration Guide

## Overview

This migration syncs missing users from the source database (`u945985759_rwamp_db`) to the live database (`u945985759_live`) and ensures all users have ULIDs to resolve routing errors.

## What This Migration Does

1. **Ensures ULID Column Exists**: Adds `ulid` column if missing
2. **Handles Duplicates**: Updates existing users with missing data
3. **Inserts Missing Users**: Adds 44 missing users (46 total minus 2 duplicates)
4. **Backfills ULIDs**: Generates ULIDs for ALL users with NULL values

## Missing Users Summary

### Total Missing: 46 users
### Duplicates Handled: 2 users
### To Be Inserted: 44 users

### Duplicate Cases Handled:

1. **ID 56** (`tahmeedahmad798@gmail.com`):
   - Already exists as ID 49 in live database
   - **Action**: Skip insertion

2. **ID 57** (`muhammadfazil.ryk@gmail.com`):
   - Already exists as ID 51 in live database
   - **Action**: Update ID 51 with missing KYC data and phone number

3. **ID 95** (`cha957676@gmail.com`):
   - Already exists as ID 82 in live database
   - **Action**: Skip insertion

## Users to Be Inserted (44 users)

| ID | Name | Email | Role | Token Balance | KYC Status |
|----|------|-------|------|---------------|------------|
| 52 | Qamber Ali | purchase.depac@gmail.com | investor | 0.00 | not_started |
| 54 | Ashfaq Ahmad | ashfaqsial92@gmail.com | investor | 1000.00 | pending |
| 55 | Mubeenasif | maharbadshah78600001@gmail.com | investor | 0.00 | not_started |
| 58 | Manoj gir | manojgri555@gmail.com | investor | 0.00 | not_started |
| 59 | Sabir | sabirzhob@gmail.com | investor | 0.00 | not_started |
| 60 | Muhammad Ahsan Raza | goodmen1991000@gmail.com | investor | 0.00 | not_started |
| 61 | Abubaker jutt | jutt302417@gmail.com | investor | 0.00 | not_started |
| 62 | SHAH RUKH | shahrukh1122b@gmail.com | investor | 0.00 | not_started |
| 63 | Khalid Hussain | khalid.hussain388@gmail.com | investor | 0.00 | not_started |
| 64 | Muhammad Imran shahbaz | imran03139234110@gmail.com | investor | 0.00 | not_started |
| 65 | Muhmmad Mudassar | mmudassar2008@gmail.com | investor | 0.00 | not_started |
| 66 | Rana Muhammad Faraz Hussain | faraz5660@gmail.com | investor | 0.00 | not_started |
| 68 | Hafiz Muhammad Huzaifa | huzaifamarketers@gmail.com | investor | 0.00 | not_started |
| 69 | Kashif Nawaz | kn235500@gmail.com | investor | 0.00 | not_started |
| 70 | Muhammad Sayyam | sayyamsaeed88@gmail.com | investor | 0.00 | not_started |
| 71 | Areeb ahmed | aareeb850@gmail.com | investor | 0.00 | not_started |
| 72 | ahmedmawaz | ahmedmawazsial@gmail.com | investor | 0.00 | not_started |
| 73 | Muhammad Bilal | hadiqam2@gmail.com | investor | 0.00 | not_started |
| 74 | Muhammad Waseem | makki12374@yahoo.com | reseller | 0.00 | not_started |
| 75 | Hafiz Muhammad Muneer Ahmed Yousafi | fazimuneer99@gmail.com | investor | 0.00 | not_started |
| 76 | Palak Naz | advocatepalaknazmemon@gmail.com | investor | 3350.00 | not_started |
| 77 | Mirhazar | mirhazarbugti57@gmail.com | investor | 0.00 | not_started |
| 78 | M Shahbaz | hafizshahbazlqp@gmail.com | investor | 0.00 | not_started |
| 79 | afnan ahmed | afnanzulfiqar12@gmail.com | investor | 0.00 | not_started |
| 80 | Muhammad Usman Siddique | usmansiddiqu84@gmail.com | investor | 0.00 | not_started |
| 81 | Altafkhan | iltaf4581@gmail.com | investor | 0.00 | not_started |
| 83 | Muhammad Waqas | waqasjavaid1991@gmail.com | investor | 0.00 | pending |
| 84 | Abdul Hanan | malikhanni7@gmail.com | investor | 0.00 | not_started |
| 85 | Tanveershahid | tanveershahid800@gmail.com | investor | 0.00 | not_started |
| 86 | Muhammad Mudasir | mudasirrandhawa652@gmail.com | investor | 0.00 | not_started |
| 87 | Shahid nazir | akansbj@gmail.com | investor | 0.00 | not_started |
| 88 | Muhammad Younas | mhryounuskhan@gmail.com | investor | 0.00 | not_started |
| 89 | Malik Muhammad Qasim | malikqasimbrother@gmail.com | investor | 0.00 | not_started |
| 90 | Muhammad Farhan | farhanqasim332@gmail.com | investor | 0.00 | not_started |
| 91 | Muhammad Ali siddiqui | siddali432@gmail.com | investor | 0.00 | not_started |
| 92 | Muhammad Ali siddiqui | sid252093@gmail.com | investor | 0.00 | not_started |
| 93 | Adnan Shahzad | daniawaramundadaniawaramunda@gmail.com | investor | 0.00 | not_started |
| 94 | Abdul Basit | abdulbassit41@gmail.com | investor | 0.00 | not_started |
| 96 | Muhammed faizan | faizankhanfaizankhan111987@gmail.com | investor | 0.00 | not_started |
| 97 | Bashir Ahmed | ahmedbashiryahoo56@gmail.com | investor | 0.00 | not_started |

## Special Cases

### Users with KYC Data:
- **ID 54** (Ashfaq Ahmad): Has pending KYC with all documents
- **ID 83** (Muhammad Waqas): Has pending KYC with all documents

### Users with Token Balances:
- **ID 54**: 1,000.00 RWAMP
- **ID 76**: 3,350.00 RWAMP

### Users with Reseller Relationships:
- **ID 54**: Linked to reseller ID 11
- **ID 58**: Linked to reseller ID 11
- **ID 60**: Linked to reseller ID 11
- **ID 69**: Linked to reseller ID 11
- **ID 76**: Linked to reseller ID 11

### Reseller User:
- **ID 74** (Muhammad Waseem): Has referral code `RSL74`

## How to Run the Migration

### Step 1: Backup Your Database
```bash
# Create a backup before running migration
mysqldump -u username -p u945985759_live > backup_before_sync_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Run the Migration
```bash
php artisan migrate
```

### Step 3: Verify Results
```sql
-- Check total users count
SELECT COUNT(*) as total_users FROM users;

-- Check users without ULIDs (should be 0)
SELECT COUNT(*) as users_without_ulid FROM users WHERE ulid IS NULL;

-- Check specific inserted users
SELECT id, name, email, ulid FROM users WHERE id IN (52, 54, 55, 58, 59, 60, 61, 62, 63, 64, 65, 66, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 96, 97);

-- Verify ULID uniqueness
SELECT ulid, COUNT(*) as count FROM users GROUP BY ulid HAVING count > 1;
```

### Step 4: Test ULID Routes
After migration, test that ULID-based routes work:
- Visit: `/a/u/{ulid}` for any user
- Verify admin user management features work
- Check that pagination and filters work correctly

## Expected Results

After running the migration:
- ✅ All 44 missing users will be inserted
- ✅ All users (existing + new) will have ULIDs
- ✅ ULID-based routing will work for all users
- ✅ No duplicate users will be created
- ✅ Existing users with missing data will be updated

## Rollback

If you need to rollback:
```bash
php artisan migrate:rollback --step=1
```

This will remove the inserted users (IDs 52-97, excluding duplicates) but will **NOT** remove ULIDs from existing users, as they're needed for the application.

## Troubleshooting

### Error: Duplicate entry for email
- **Cause**: User already exists in database
- **Solution**: Migration automatically skips duplicates by email

### Error: Duplicate ULID
- **Cause**: ULID collision (extremely rare)
- **Solution**: Migration automatically regenerates ULID if collision occurs

### Error: Foreign key constraint (reseller_id)
- **Cause**: Referenced reseller doesn't exist
- **Solution**: Check that reseller IDs (11, 8, 10) exist in database

## Post-Migration Checklist

- [ ] Verify all users have ULIDs: `SELECT COUNT(*) FROM users WHERE ulid IS NULL;` (should be 0)
- [ ] Test ULID routes: `/a/u/{ulid}` for sample users
- [ ] Verify admin user management page loads correctly
- [ ] Check that pagination works
- [ ] Verify filters work correctly
- [ ] Test edit user modal with ULID-based routes
- [ ] Check transaction history for newly inserted users
- [ ] Verify KYC data for users 54 and 83

## Notes

- The migration uses `DB::table()` instead of Eloquent to avoid model events
- ULIDs are generated using Laravel's `Str::ulid()` helper
- All ULIDs are checked for uniqueness before insertion/update
- The migration is idempotent - safe to run multiple times
- Duplicate detection is based on email address
