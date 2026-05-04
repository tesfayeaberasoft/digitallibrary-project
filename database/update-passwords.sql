-- Update passwords for all test users to: admin123
USE digital_library;

UPDATE users SET password_hash = '$2y$10$gRawgcWXGDo0r1ri8s12nOGmMaO6HTbEevKo1PHcTlR3kAd7eEQ5a' 
WHERE email IN ('admin@library.com', 'librarian@library.com', 'student@library.com', 'staff@library.com');

SELECT 'Passwords updated successfully!' as message;
SELECT email, role, status FROM users;
