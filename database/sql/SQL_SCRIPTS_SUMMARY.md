# SQL Scripts Summary

## Files Created

1. **`insert_missing_users.sql`** ✅ Complete
   - Inserts 40 missing users
   - Uses auto-generated IDs
   - ULIDs set to NULL (backfill later)

2. **`insert_sessions.sql`** ✅ Complete
   - Inserts user sessions with user_id references
   - Uses email lookups to find user IDs
   - Only inserts sessions that have user_id (skips anonymous sessions)

3. **`insert_transactions.sql`** ⚠️ Template
   - Template for inserting transactions
   - Uses email lookups for user IDs
   - **Needs actual transaction data** - currently has example transactions
   - You need to replace email addresses with actual emails from your database

4. **`UPDATE_RESELLER_IDS.sql`** ✅ Complete
   - Updates hardcoded `reseller_id` values
   - Converts old ID references to new user IDs found by email

5. **`README_SQL_SCRIPTS.md`** ✅ Complete
   - Documentation for all scripts
   - Execution order and troubleshooting

## Execution Steps

### Step 1: Insert Missing Users
```sql
-- Run in phpMyAdmin or mysql command line
SOURCE database/sql/insert_missing_users.sql;
```

### Step 2: Update Reseller References
```sql
-- First, find your reseller emails
SELECT id, name, email, role FROM users WHERE role = 'reseller';

-- Then update the UPDATE_RESELLER_IDS.sql file with actual reseller email
-- Then run:
SOURCE database/sql/UPDATE_RESELLER_IDS.sql;
```

### Step 3: Insert Sessions (if needed)
```sql
SOURCE database/sql/insert_sessions.sql;
```

### Step 4: Insert Transactions (if you have transaction data)
```sql
-- First, update insert_transactions.sql with actual email addresses
-- Then run:
SOURCE database/sql/insert_transactions.sql;
```

### Step 5: Backfill ULIDs
```bash
php artisan users:sync-missing
```

## Important Notes

### Email-Based Lookups
All scripts use email addresses because:
- User IDs are auto-generated (AUTO_INCREMENT)
- We cannot predict what IDs will be assigned
- Email addresses are unique and stable identifiers

### Reseller ID Issue
Some users in `insert_missing_users.sql` have hardcoded `reseller_id = 11`. This needs to be updated using `UPDATE_RESELLER_IDS.sql` after all users are inserted.

### Admin User
The transactions script assumes admin user exists and finds it by `role = 'admin'`. If you have multiple admins, the script will use the first one found.

## Verification Queries

```sql
-- Check all users have ULIDs
SELECT COUNT(*) as missing_ulids 
FROM users 
WHERE ulid IS NULL;
-- Should return 0 after backfill

-- Check total users
SELECT COUNT(*) as total_users FROM users;

-- Check reseller references
SELECT 
    u1.id,
    u1.name,
    u1.reseller_id,
    u2.name as reseller_name
FROM users u1
LEFT JOIN users u2 ON u1.reseller_id = u2.id
WHERE u1.reseller_id IS NOT NULL;
```

## Troubleshooting

### "User not found" errors
- Verify user exists: `SELECT * FROM users WHERE email = 'user@example.com';`
- Check email spelling (case-sensitive)
- Ensure user was inserted in Step 1

### "Admin user not found" errors
- Check admin exists: `SELECT * FROM users WHERE role = 'admin';`
- Update transactions script if admin has different role or criteria

### Reseller ID still showing old values
- Run `UPDATE_RESELLER_IDS.sql` again
- Verify reseller email is correct in the script
- Check that reseller user actually exists
