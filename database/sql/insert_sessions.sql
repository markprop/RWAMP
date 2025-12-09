-- SQL script to insert sessions
-- Run this AFTER inserting all users
-- Uses subqueries to find user IDs by email (since IDs are auto-generated)
-- Note: Only inserts sessions that have user_id references

-- Session for user 97 (Bashir Ahmed)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
SELECT 
    '6t6LBvpcgFFMMbs8BTLINjwf8BFnSQHGx4UULQ3H',
    u.id,
    '119.155.204.62',
    'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36',
    'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZDZvS0d6VTJRMmV0MDFCRzIzSWpRU0tERTIxU1BDUmRaUEVxdWRCUCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vZGV2LnJ3YW1wLm5ldC9wcm9maWxlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxODoidmVyaWZpY2F0aW9uX2VtYWlsIjtzOjI4OiJhaG1lZGJhc2hpcnlhaG9vNTZAZ21haWwuY29tIjtzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo5Nzt9',
    1765176827
FROM `users` u WHERE u.email = 'ahmedbashiryahoo56@gmail.com'
AND NOT EXISTS (SELECT 1 FROM sessions WHERE id = '6t6LBvpcgFFMMbs8BTLINjwf8BFnSQHGx4UULQ3H');

-- Session for user 96 (Muhammed faizan)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
SELECT 
    'CK7B73PAUWxqk8UmXe3D1vy3o7v0eB7SQIgT5V8E',
    u.id,
    '139.5.116.12',
    'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36',
    'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTmVleDRJTld6dUhsY05Ud0I0UkdBQU4zajk5enplUk1ES3Y4WGRMVCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vZGV2LnJ3YW1wLm5ldC9wcm9maWxlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czoxODoidmVyaWZpY2F0aW9uX2VtYWlsIjtzOjM2OiJmYWl6YW5raGFuZmFpemFua2hhbjExMTk4N0BnbWFpbC5jb20iO3M6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjk2O30=',
    1765128705
FROM `users` u WHERE u.email = 'faizankhanfaizankhan111987@gmail.com'
AND NOT EXISTS (SELECT 1 FROM sessions WHERE id = 'CK7B73PAUWxqk8UmXe3D1vy3o7v0eB7SQIgT5V8E');

-- Session for user 69 (Kashif Nawaz)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
SELECT 
    'n7fedOR75RqjWEx9oRkIVDa3S2g5PbUpJN48Q1zT',
    u.id,
    '37.111.154.112',
    'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36',
    'YTo0OntzOjY6Il90b2tlbiI7czo0MDoibFZjZjJXaDJQNVR6UUFXZkdsbDlHOW9SNWdsNzZnN0V0SnNoRFVuUSI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Njk7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHBzOi8vZGV2LnJ3YW1wLm5ldCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',
    1765118400
FROM `users` u WHERE u.email = 'kn235500@gmail.com'
AND NOT EXISTS (SELECT 1 FROM sessions WHERE id = 'n7fedOR75RqjWEx9oRkIVDa3S2g5PbUpJN48Q1zT');

-- Session for user 76 (Palak Naz)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`)
SELECT 
    'ZvMGoFKBQNWrP08O37A3D5Aft8gRu0qsYeGrv5eT',
    u.id,
    '137.59.220.42',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36',
    'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiejZGaFRnRU03Ymh3RmZjOEZxUEFGSTlubVhCOFVIMDllMjE4bUtNQSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MDoiaHR0cHM6Ly9kZXYucndhbXAubmV0L2Rhc2hib2FyZC9pbnZlc3RvciI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU0OiJodHRwczovL2Rldi5yd2FtcC5uZXQvZGFzaGJvYXJkL2ludmVzdG9yP29wZW49cHVyY2hhc2UiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo3Njt9',
    1765190822
FROM `users` u WHERE u.email = 'advocatepalaknazmemon@gmail.com'
AND NOT EXISTS (SELECT 1 FROM sessions WHERE id = 'ZvMGoFKBQNWrP08O37A3D5Aft8gRu0qsYeGrv5eT');

-- Note: Other sessions in the dump have NULL user_id, so they don't need user references
-- These are anonymous sessions and can be inserted as-is if needed
