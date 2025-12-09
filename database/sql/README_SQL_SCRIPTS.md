# SQL Scripts for Database Synchronization

## Overview
These SQL scripts are designed to work with auto-generated user IDs. They use email addresses to find user IDs dynamically, ensuring compatibility regardless of what IDs are assigned.

## Execution Order

1. **First**: Run `insert_missing_users.sql`
   - Inserts all missing users
   - IDs will be auto-generated
   - ULIDs will be NULL initially

2. **Second**: Run `insert_transactions.sql` (if you have transaction data)
   - Uses email lookups to find user IDs
   - Requires all users to exist first

3. **Third**: Run `insert_sessions.sql` (if you have session data)
   - Uses email lookups for user sessions
   - Only inserts sessions with user_id references

4. **Finally**: Backfill ULIDs
   ```bash
   php artisan users:sync-missing
   ```

## Important Notes

### Email-Based Lookups
All scripts use email addresses to find user IDs because:
- User IDs are auto-generated (AUTO_INCREMENT)
- We cannot predict what IDs will be assigned
- Email addresses are unique identifiers

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
The admin user (typically ID 5) is assumed to exist. If your admin has a different ID, update the transactions script accordingly.

## Troubleshooting

### If transactions fail:
- Verify all users exist: `SELECT COUNT(*) FROM users WHERE email IN (...);`
- Check admin user ID: `SELECT id FROM users WHERE role = 'admin' LIMIT 1;`
- Update hardcoded admin ID (5) in transactions script if needed

### If sessions fail:
- Most sessions have `user_id = NULL` (anonymous)
- Only sessions with user_id references are inserted
- Verify user emails match exactly (case-sensitive)

## File Descriptions

- `insert_missing_users.sql`: Inserts 40 missing users with auto-generated IDs
- `insert_transactions.sql`: Inserts transaction records using email lookups
- `insert_sessions.sql`: Inserts user session records using email lookups
- `README_SQL_SCRIPTS.md`: This file
