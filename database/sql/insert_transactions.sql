-- SQL script to insert transactions
-- Run this AFTER inserting all users
-- Uses email addresses to find user IDs dynamically (since IDs are auto-generated)
-- 
-- IMPORTANT: This is a TEMPLATE file. You need to:
-- 1. Replace email addresses with actual emails from your database
-- 2. Update admin user lookup (currently uses role = 'admin')
-- 3. Verify all referenced users exist before running
--
-- To find user emails, run:
-- SELECT id, name, email FROM users ORDER BY id;
--
-- Note: Admin user is found by role='admin'. If you have multiple admins, update accordingly.

-- Transaction 1: Admin transfer to user 9 (khalid.hussain388@gmail.com)
-- NOTE: Update email addresses based on your actual user data
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    admin.id, -- Admin user ID (found by role)
    u.id,
    'admin_transfer_credit',
    1000.00,
    1.00,
    1000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763468192-9',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-18 12:16:32',
    '2025-11-18 12:19:28'
FROM `users` u
CROSS JOIN (SELECT id FROM users WHERE role = 'admin' LIMIT 1) admin
WHERE u.email = 'khalid.hussain388@gmail.com' -- Update with actual email
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763468192-9' AND user_id = u.id);

-- Transaction 2: Admin debit (for transaction 1)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    admin.id, -- Admin user ID
    admin.id,
    u.id,
    'admin_transfer_debit',
    -1000.00,
    1.00,
    1000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763468192-9',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-18 12:16:32',
    '2025-11-18 12:19:28'
FROM `users` u
CROSS JOIN (SELECT id FROM users WHERE role = 'admin' LIMIT 1) admin
WHERE u.email = 'khalid.hussain388@gmail.com' -- Update with actual email
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763468192-9' AND user_id = admin.id);

-- Transaction 3: Admin transfer to user 8
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    2000.00,
    2.00,
    4000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763637488-8',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-20 11:18:08',
    '2025-11-20 11:18:50'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763637488-8' AND user_id = u.id);

-- Transaction 4: Admin debit (for transaction 3)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -2000.00,
    2.00,
    4000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763637488-8',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-20 11:18:08',
    '2025-11-20 11:18:50'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763637488-8' AND user_id = 5);

-- Transaction 5: Reseller sell (user 8 to user 12)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -100.00,
    2.00,
    200.00,
    'reseller',
    'completed',
    'BUY-REQ-1',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-20 11:22:51',
    '2025-11-20 11:22:51'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-REQ-1' AND type = 'reseller_sell');

-- Transaction 6: Buy from reseller (user 12 from user 8)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    100.00,
    2.00,
    200.00,
    'reseller',
    'completed',
    'BUY-REQ-1',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-20 11:22:51',
    '2025-11-20 11:22:51'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-REQ-1' AND type = 'buy_from_reseller');

-- Transaction 7: Admin transfer to user 11
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763712819-11',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-21 08:13:39',
    '2025-11-21 08:14:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 11 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763712819-11' AND user_id = u.id);

-- Transaction 8: Admin debit (for transaction 7)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763712819-11',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-21 08:13:39',
    '2025-11-21 08:14:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 11 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763712819-11' AND user_id = 5);

-- Transaction 9: Admin transfer to user 10
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763713774-10',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-21 08:29:34',
    '2025-11-21 10:55:42'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763713774-10' AND user_id = u.id);

-- Transaction 10: Admin debit (for transaction 9)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1763713774-10',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-21 08:29:34',
    '2025-11-21 10:55:42'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1763713774-10' AND user_id = 5);

-- Transaction 11: Admin transfer to user 14
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    800.00,
    1.50,
    1200.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764069172-14',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-25 11:12:52',
    '2025-11-25 11:18:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 14 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764069172-14' AND user_id = u.id);

-- Transaction 12: Admin debit (for transaction 11)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -800.00,
    1.50,
    1200.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764069172-14',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-25 11:12:52',
    '2025-11-25 11:18:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 14 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764069172-14' AND user_id = 5);

-- Transaction 13: Reseller sell (user 8 to user 12)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -400.00,
    2.00,
    800.00,
    'reseller',
    'completed',
    'SELL-12-1764069584',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-25 11:19:44',
    '2025-11-25 11:19:44'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'SELL-12-1764069584' AND type = 'reseller_sell');

-- Transaction 14: Buy from reseller (user 12 from user 8)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    400.00,
    2.00,
    800.00,
    'reseller',
    'completed',
    'BUY-RSL-8-1764069584',
    NULL,
    NULL,
    NULL,
    'pending',
    NULL,
    NULL,
    '2025-11-25 11:19:44',
    '2025-11-25 11:19:44'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 8 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-RSL-8-1764069584' AND type = 'buy_from_reseller');

-- Transaction 15: Reseller sell (user 11 to user 12)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -800.00,
    1.50,
    1200.00,
    'reseller',
    'completed',
    'SELL-12-1764141416',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-26 07:16:56',
    '2025-11-26 07:16:56'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 11 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'SELL-12-1764141416' AND type = 'reseller_sell');

-- Transaction 16: Buy from reseller (user 12 from user 11)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    800.00,
    1.50,
    1200.00,
    'reseller',
    'completed',
    'BUY-RSL-11-1764141416',
    NULL,
    NULL,
    NULL,
    'pending',
    NULL,
    NULL,
    '2025-11-26 07:16:56',
    '2025-11-26 07:16:56'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 11 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 12 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-RSL-11-1764141416' AND type = 'buy_from_reseller');

-- Transaction 17: Reseller sell (user 10 to user 18)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -1335.00,
    1.50,
    2002.50,
    'reseller',
    'completed',
    'SELL-18-1764162427',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-11-26 13:07:07',
    '2025-11-26 13:07:07'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 18 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'SELL-18-1764162427' AND type = 'reseller_sell');

-- Transaction 18: Buy from reseller (user 18 from user 10)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    1335.00,
    1.50,
    2002.50,
    'reseller',
    'completed',
    'BUY-RSL-10-1764162427',
    NULL,
    NULL,
    NULL,
    'pending',
    NULL,
    NULL,
    '2025-11-26 13:07:07',
    '2025-11-26 13:07:07'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 18 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-RSL-10-1764162427' AND type = 'buy_from_reseller');

-- Transaction 19: Reseller sell (user 10 to user 23)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -600.00,
    2.00,
    1200.00,
    'reseller',
    'completed',
    'SELL-23-1764594218',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 13:03:38',
    '2025-12-01 13:03:38'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 23 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'SELL-23-1764594218' AND type = 'reseller_sell');

-- Transaction 20: Buy from reseller (user 23 from user 10)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    600.00,
    2.00,
    1200.00,
    'reseller',
    'completed',
    'BUY-RSL-10-1764594218',
    NULL,
    NULL,
    NULL,
    'pending',
    NULL,
    NULL,
    '2025-12-01 13:03:38',
    '2025-12-01 13:03:38'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 23 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-RSL-10-1764594218' AND type = 'buy_from_reseller');

-- Transaction 23: Admin transfer to user 25
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    600.00,
    1.67,
    1000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764598511-25',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 14:15:11',
    '2025-12-01 16:47:58'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 25 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764598511-25' AND user_id = u.id);

-- Transaction 24: Admin debit (for transaction 23)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -600.00,
    1.67,
    1000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764598511-25',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 14:15:11',
    '2025-12-01 16:47:58'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 25 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764598511-25' AND user_id = 5);

-- Transaction 25: Admin transfer to user 31
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    1000.00,
    0.50,
    500.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764608516-31',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 17:01:56',
    '2025-12-01 17:09:35'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 31 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764608516-31' AND user_id = u.id);

-- Transaction 26: Admin debit (for transaction 25)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -1000.00,
    0.50,
    500.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764608516-31',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 17:01:56',
    '2025-12-01 17:09:35'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 31 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764608516-31' AND user_id = 5);

-- Transaction 27: Reseller sell (user 10 to user 32)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    reseller.id,
    reseller.id,
    buyer.id,
    'reseller_sell',
    -10000.00,
    1.00,
    10000.00,
    'reseller',
    'completed',
    'SELL-32-1764609078',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 17:11:18',
    '2025-12-01 17:11:18'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 32 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'SELL-32-1764609078' AND type = 'reseller_sell');

-- Transaction 28: Buy from reseller (user 32 from user 10)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    buyer.id,
    reseller.id,
    buyer.id,
    'buy_from_reseller',
    10000.00,
    1.00,
    10000.00,
    'reseller',
    'completed',
    'BUY-RSL-10-1764609078',
    NULL,
    NULL,
    NULL,
    'pending',
    NULL,
    NULL,
    '2025-12-01 17:11:18',
    '2025-12-01 17:11:18'
FROM `users` reseller, `users` buyer
WHERE reseller.email = (SELECT email FROM users WHERE id = 10 LIMIT 1)
AND buyer.email = (SELECT email FROM users WHERE id = 32 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'BUY-RSL-10-1764609078' AND type = 'buy_from_reseller');

-- Transaction 29: Admin transfer to user 36
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764611304-36',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 17:48:24',
    '2025-12-01 18:08:42'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 36 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764611304-36' AND user_id = u.id);

-- Transaction 30: Admin debit (for transaction 29)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764611304-36',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 17:48:24',
    '2025-12-01 18:08:42'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 36 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764611304-36' AND user_id = 5);

-- Transaction 31: Admin transfer to user 38
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764612600-38',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 18:10:00',
    '2025-12-02 08:23:34'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 38 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764612600-38' AND user_id = u.id);

-- Transaction 32: Admin debit (for transaction 31)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -100000.00,
    0.50,
    50000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764612600-38',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-01 18:10:00',
    '2025-12-02 08:23:34'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 38 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764612600-38' AND user_id = 5);

-- Transaction 33: Admin transfer to user 45
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    250.00,
    2.00,
    500.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764669740-45',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-02 10:02:20',
    '2025-12-02 10:28:09'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 45 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764669740-45' AND user_id = u.id);

-- Transaction 34: Admin debit (for transaction 33)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -250.00,
    2.00,
    500.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764669740-45',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-02 10:02:20',
    '2025-12-02 10:28:09'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 45 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764669740-45' AND user_id = 5);

-- Transaction 35: Admin transfer to user 54 (Ashfaq Ahmad)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    1000.00,
    2.00,
    2000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764679280-54',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-02 12:41:20',
    '2025-12-02 13:27:49'
FROM `users` u WHERE u.email = 'ashfaqsial92@gmail.com'
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764679280-54' AND user_id = u.id);

-- Transaction 36: Admin debit (for transaction 35)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -1000.00,
    2.00,
    2000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764679280-54',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-02 12:41:20',
    '2025-12-02 13:27:49'
FROM `users` u WHERE u.email = 'ashfaqsial92@gmail.com'
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764679280-54' AND user_id = 5);

-- Transaction 37: Admin transfer to user 26
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    1500.00,
    2.00,
    3000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764757023-26',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-03 10:17:03',
    '2025-12-03 10:56:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 26 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764757023-26' AND user_id = u.id);

-- Transaction 38: Admin debit (for transaction 37)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -1500.00,
    2.00,
    3000.00,
    'admin',
    'completed',
    'ADMIN-SELL-1764757023-26',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-03 10:17:03',
    '2025-12-03 10:56:49'
FROM `users` u WHERE u.email = (SELECT email FROM users WHERE id = 26 LIMIT 1)
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764757023-26' AND user_id = 5);

-- Transaction 39: Admin transfer to user 76 (Palak Naz)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    u.id,
    5,
    u.id,
    'admin_transfer_credit',
    3350.00,
    1.49,
    5000.21,
    'admin',
    'completed',
    'ADMIN-SELL-1764761965-76',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-03 11:39:25',
    '2025-12-05 07:58:22'
FROM `users` u WHERE u.email = 'advocatepalaknazmemon@gmail.com'
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764761965-76' AND user_id = u.id);

-- Transaction 40: Admin debit (for transaction 39)
INSERT INTO `transactions` (`user_id`, `sender_id`, `recipient_id`, `type`, `amount`, `price_per_coin`, `total_price`, `sender_type`, `status`, `reference`, `payment_type`, `payment_hash`, `payment_receipt`, `payment_status`, `verified_by`, `verified_at`, `created_at`, `updated_at`)
SELECT 
    5,
    5,
    u.id,
    'admin_transfer_debit',
    -3350.00,
    1.49,
    5000.21,
    'admin',
    'completed',
    'ADMIN-SELL-1764761965-76',
    'cash',
    NULL,
    NULL,
    'verified',
    NULL,
    NULL,
    '2025-12-03 11:39:25',
    '2025-12-05 07:58:22'
FROM `users` u WHERE u.email = 'advocatepalaknazmemon@gmail.com'
AND NOT EXISTS (SELECT 1 FROM transactions WHERE reference = 'ADMIN-SELL-1764761965-76' AND user_id = 5);
