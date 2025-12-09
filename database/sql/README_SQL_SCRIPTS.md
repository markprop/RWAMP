# SQL Scripts for Database Synchronization

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=24&duration=2000&pause=500&color=06D6A0&center=true&vCenter=true&width=600&lines=Database+SQL+Scripts" alt="SQL Scripts Header" />
</p>

This directory contains SQL scripts for database synchronization and data migration. These scripts are designed to work with auto-generated user IDs and use email addresses for dynamic lookups.

## 📄 Scripts

- **insert_missing_users.sql** - Inserts missing users with auto-generated IDs
- **insert_transactions.sql** - Inserts transaction records using email lookups
- **insert_sessions.sql** - Inserts user session records using email lookups

## 🔄 Execution Order

### Step 1: Insert Missing Users
```sql
-- Run: insert_missing_users.sql
-- Inserts all missing users
-- IDs will be auto-generated (AUTO_INCREMENT)
-- ULIDs will be NULL initially (backfilled later)
```

### Step 2: Insert Transactions (Optional)
```sql
-- Run: insert_transactions.sql (if you have transaction data)
-- Uses email lookups to find user IDs
-- Requires all users to exist first
```

### Step 3: Insert Sessions (Optional)
```sql
-- Run: insert_sessions.sql (if you have session data)
-- Uses email lookups for user sessions
-- Only inserts sessions with user_id references
```

### Step 4: Backfill ULIDs
```bash
# Run Artisan command to generate ULIDs
php artisan users:sync-missing
```

## ⚠️ Important Notes

### Email-Based Lookups
All scripts use email addresses to find user IDs because:
- ✅ User IDs are auto-generated (AUTO_INCREMENT)
- ✅ We cannot predict what IDs will be assigned
- ✅ Email addresses are unique identifiers
- ✅ Ensures compatibility across different database states

### Reseller ID References
Some users have `reseller_id` set to hardcoded values (e.g., `11`). These need to be updated after insertion:

```sql
-- Example: Update reseller_id for users that reference reseller by email
UPDATE users u1
JOIN users u2 ON u2.email = 'reseller@example.com'
SET u1.reseller_id = u2.id
WHERE u1.reseller_id = 11; -- Old hardcoded ID
```

### Admin User ID
The admin user (typically ID 5) is assumed to exist. If your admin has a different ID, update the transactions script accordingly:

```sql
-- Check admin user ID
SELECT id, email FROM users WHERE role = 'admin' LIMIT 1;

-- Update hardcoded admin ID in transactions script if needed
```

## 🔧 Troubleshooting

### If Transactions Fail
1. ✅ Verify all users exist:
   ```sql
   SELECT COUNT(*) FROM users WHERE email IN ('user1@example.com', 'user2@example.com');
   ```

2. ✅ Check admin user ID:
   ```sql
   SELECT id FROM users WHERE role = 'admin' LIMIT 1;
   ```

3. ✅ Update hardcoded admin ID (5) in transactions script if needed

### If Sessions Fail
1. ✅ Most sessions have `user_id = NULL` (anonymous)
2. ✅ Only sessions with user_id references are inserted
3. ✅ Verify user emails match exactly (case-sensitive)

### Common Issues
- **Email Mismatch**: Ensure emails in scripts match database exactly
- **Missing Users**: Run `insert_missing_users.sql` first
- **ULID Missing**: Run `php artisan users:sync-missing` after insertion

## 📋 Script Details

### insert_missing_users.sql
- **Purpose**: Inserts 40 missing users with auto-generated IDs
- **Dependencies**: None (run first)
- **Output**: Users inserted with NULL ULIDs (backfill required)

### insert_transactions.sql
- **Purpose**: Inserts transaction records using email lookups
- **Dependencies**: All users must exist
- **Output**: Transaction records linked to users by email

### insert_sessions.sql
- **Purpose**: Inserts user session records using email lookups
- **Dependencies**: All users must exist
- **Output**: Session records linked to users by email

## 🔗 Related Documentation

- **Database Setup**: [`../../docs/database/DATABASE_SETUP_GUIDE.md`](../../docs/database/DATABASE_SETUP_GUIDE.md)
- **User Sync**: [`../../docs/database/USER_SYNC_MIGRATION_GUIDE.md`](../../docs/database/USER_SYNC_MIGRATION_GUIDE.md)
- **Database Analysis**: [`../../docs/database/DATABASE_ANALYSIS.md`](../../docs/database/DATABASE_ANALYSIS.md)

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
  <a href="../../docs/README.md">
    <img src="https://img.shields.io/badge/📚%20Documentation%20Index-06D6A0?style=for-the-badge&logo=book&logoColor=white" alt="Documentation Index" />
  </a>
  <a href="../../docs/database/README.md">
    <img src="https://img.shields.io/badge/🗄️%20Database%20Docs-118AB2?style=for-the-badge&logo=database&logoColor=white" alt="Database Docs" />
  </a>
</p>

---

**Last Updated:** January 27, 2025
