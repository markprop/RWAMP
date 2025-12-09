# Database Migration Guide for Hostinger Update

This guide lists all new migrations and which ones you need to run based on your requirements.

## Migration Status Check

First, check which migrations have already been run on your production server:

**Via SSH:**
```bash
php artisan migrate:status
```

This will show you which migrations are pending (not run yet).

## New Migrations List

### Chat System Migrations (Currently Disabled - Optional)

These migrations are for the chat system which is currently **disabled**. You can skip these if you don't plan to enable chat, or run them now to prepare for future enablement.

#### 1. Add Chat Fields to Users Table
**File:** `2025_12_01_000001_add_chat_fields_to_users_table.php`
**What it does:**
- Adds `avatar` column (string, nullable)
- Adds `status` column (string, default 'online')
- Adds `receipt_screenshot` column (string, nullable)

**Status:** ⚠️ **OPTIONAL** - Only needed if you want to enable chat system later

#### 2. Create Chats Table
**File:** `2025_12_01_000002_create_chats_table.php`
**What it does:**
- Creates `chats` table for storing chat conversations

**Status:** ⚠️ **OPTIONAL** - Only needed for chat system

#### 3. Create Chat Participants Table
**File:** `2025_12_01_000003_create_chat_participants_table.php`
**What it does:**
- Creates `chat_participants` table for group chat participants

**Status:** ⚠️ **OPTIONAL** - Only needed for chat system

#### 4. Create Chat Messages Table
**File:** `2025_12_01_000004_create_chat_messages_table.php`
**What it does:**
- Creates `chat_messages` table for storing messages

**Status:** ⚠️ **OPTIONAL** - Only needed for chat system

#### 5. Create Chat Message Reads Table
**File:** `2025_12_01_000005_create_chat_message_reads_table.php`
**What it does:**
- Creates `chat_message_reads` table for read receipts

**Status:** ⚠️ **OPTIONAL** - Only needed for chat system

#### 6. Add Reaction to Chat Messages
**File:** `2025_12_02_000001_add_reaction_to_chat_messages.php`
**What it does:**
- Adds `reaction` column to `chat_messages` table

**Status:** ⚠️ **OPTIONAL** - Only needed for chat system

### Other Migrations (Check if Already Run)

#### 7. Update Reseller Applications Table
**File:** `2025_11_18_063030_update_reseller_applications_table_for_signup.php`
**What it does:**
- Adds `password` field to reseller_applications
- Adds `experience` field
- Changes `investment_capacity` from enum to string

**Status:** ✅ **CHECK** - May already be run, verify with `migrate:status`

## Recommended Migration Strategy

### Option 1: Current Features Only (Recommended)

**Skip chat migrations** since chat system is disabled. Only run if you see pending migrations for other features:

```bash
# Check status first
php artisan migrate:status

# Run only pending migrations (excluding chat if you want)
# Or run all pending migrations
php artisan migrate --force
```

**Migrations to SKIP (if chat is disabled):**
- `2025_12_01_000001_add_chat_fields_to_users_table.php`
- `2025_12_01_000002_create_chats_table.php`
- `2025_12_01_000003_create_chat_participants_table.php`
- `2025_12_01_000004_create_chat_messages_table.php`
- `2025_12_01_000005_create_chat_message_reads_table.php`
- `2025_12_02_000001_add_reaction_to_chat_messages.php`

### Option 2: Prepare for Future Chat System

**Run all migrations** including chat migrations to prepare for future chat enablement:

```bash
# Run all pending migrations
php artisan migrate --force
```

This will create all tables, and when you enable chat later, everything will be ready.

## Migration Commands

### Check Migration Status
```bash
php artisan migrate:status
```

### Run All Pending Migrations
```bash
php artisan migrate --force
```

### Run Specific Migration
```bash
# This is not directly possible, but you can:
# 1. Check which migrations are pending
php artisan migrate:status

# 2. Run all pending (they run in order)
php artisan migrate --force
```

### Rollback Last Migration
```bash
php artisan migrate:rollback --step=1
```

### Rollback All Migrations (DANGEROUS - Don't use in production)
```bash
php artisan migrate:reset
```

## What Each Migration Does (Detailed)

### Chat Fields Migration
```php
// Adds to users table:
- avatar (string, nullable) - Profile picture URL
- status (string, default 'online') - User online status
- receipt_screenshot (string, nullable) - Receipt from chat
```

### Chats Table
```php
// Creates chats table with:
- id
- user_id (creator)
- type (private/group)
- name (for groups)
- participants (JSON)
- timestamps
```

### Chat Messages Table
```php
// Creates chat_messages table with:
- id
- chat_id
- sender_id
- content
- media_type
- media_path
- location_data
- is_deleted
- deleted_by
- deleted_at
- timestamps
```

## Current Features - No Migrations Needed

The following features **DO NOT require new migrations** (they use existing tables):

✅ **Tawk.to Integration** - No migrations needed
✅ **Portfolio Value Calculations** - Uses existing `crypto_payments` and `transactions` tables
✅ **Buy From Reseller** - Uses existing `buy_from_reseller_requests` table
✅ **Request Submission History** - Uses existing `buy_from_reseller_requests` table
✅ **Profile Value Display** - Uses existing `users` and `transactions` tables

## Step-by-Step Migration Process

### Step 1: Backup Database
**IMPORTANT:** Always backup your database before running migrations!

**Via Hostinger hPanel:**
1. Go to **Databases** → **phpMyAdmin**
2. Select your database
3. Click **Export** tab
4. Choose **Quick** export method
5. Click **Go** to download backup

### Step 2: Check Current Status
```bash
php artisan migrate:status
```

This shows:
- ✅ = Already run
- ⏳ = Pending (not run yet)

### Step 3: Run Migrations

**If you want to skip chat migrations:**
1. Temporarily rename chat migration files (add `.skip` extension)
2. Run: `php artisan migrate --force`
3. Rename files back (for future use)

**If you want to run all migrations:**
```bash
php artisan migrate --force
```

### Step 4: Verify Migrations
```bash
# Check status again
php artisan migrate:status

# All should show as "Ran"
```

### Step 5: Test Application
1. Visit your website
2. Test login
3. Check dashboard
4. Verify all features work

## Migration File Names Reference

For manual file upload, here are the exact migration file names:

### Chat System Migrations (Optional)
1. `2025_12_01_000001_add_chat_fields_to_users_table.php`
2. `2025_12_01_000002_create_chats_table.php`
3. `2025_12_01_000003_create_chat_participants_table.php`
4. `2025_12_01_000004_create_chat_messages_table.php`
5. `2025_12_01_000005_create_chat_message_reads_table.php`
6. `2025_12_02_000001_add_reaction_to_chat_messages.php`

### Other Migrations
7. `2025_11_18_063030_update_reseller_applications_table_for_signup.php`

## Troubleshooting

### Issue: Migration fails with "Table already exists"

**Solution:**
```bash
# Check if table exists
php artisan tinker
# Then: Schema::hasTable('chats')
# Exit: exit

# If table exists, migration was already run
# Mark it as run manually:
php artisan migrate:status
```

### Issue: Migration fails with "Column already exists"

**Solution:**
The migration was partially run. Check which columns exist and skip the migration or modify it.

### Issue: Can't run migrations via SSH

**Solution:**
1. Contact Hostinger support to run migrations
2. OR use Hostinger Terminal (if available in hPanel)
3. OR manually create tables via phpMyAdmin (not recommended)

## Recommendation

**For Current Deployment:**

Since chat system is **disabled**, you can **skip chat migrations** for now. Only run:

1. Check if `2025_11_18_063030_update_reseller_applications_table_for_signup.php` is pending
2. If pending, run it
3. Skip all chat migrations (2025_12_01_* and 2025_12_02_*)

**Command:**
```bash
# Check what's pending
php artisan migrate:status

# If only chat migrations are pending, you can skip them
# If other migrations are pending, run:
php artisan migrate --force
```

## Summary

- **Chat Migrations:** 6 files (OPTIONAL - skip if chat disabled)
- **Other Migrations:** 1 file (CHECK if already run)
- **Current Features:** No new migrations needed
- **Action:** Check `migrate:status` first, then decide

---

**Last Updated:** 2025-11-20
**Version:** 1.0
