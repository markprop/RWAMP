-- SQL script to update reseller_id references
-- Run this AFTER inserting all users
-- Updates hardcoded reseller_id values to use actual user IDs found by email

-- Update users that reference reseller by old ID 11
-- Replace 'reseller@example.com' with actual reseller email
UPDATE users u1
JOIN users u2 ON u2.email = 'reseller@example.com' -- UPDATE THIS EMAIL
SET u1.reseller_id = u2.id
WHERE u1.reseller_id = 11
AND u2.role = 'reseller';

-- Verify updates
SELECT 
    u1.id,
    u1.name,
    u1.email,
    u1.reseller_id,
    u2.name as reseller_name,
    u2.email as reseller_email
FROM users u1
LEFT JOIN users u2 ON u1.reseller_id = u2.id
WHERE u1.reseller_id IS NOT NULL
ORDER BY u1.id;
