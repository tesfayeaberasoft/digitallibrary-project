-- Digital Library Management System - Sample Data
USE digital_library;

-- Insert Admin User (password: admin123)
INSERT INTO users (email, password_hash, first_name, last_name, role, phone, address, status) VALUES
('admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', '+251911234567', 'Addis Ababa, Ethiopia', 'active'),
('librarian@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Librarian', 'librarian', '+251922345678', 'Addis Ababa, Ethiopia', 'active'),
('student@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Student', 'student', '+251933456789', 'Addis Ababa, Ethiopia', 'active'),
('staff@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mike', 'Staff', 'staff', '+251944567890', 'Addis Ababa, Ethiopia', 'active');

-- Insert Sample Books
INSERT INTO books (isbn, title, author, category, publisher, publication_year, edition, pages, language, description, total_copies, available_copies, location) VALUES
('978-0-13-468599-1', 'Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', 'Programming', 'Prentice Hall', 2008, '1st', 464, 'English', 'A comprehensive guide to writing clean, maintainable code.', 5, 5, 'Shelf A1'),
('978-0-13-235088-4', 'Clean Architecture', 'Robert C. Martin', 'Software Engineering', 'Prentice Hall', 2017, '1st', 432, 'English', 'A guide to software structure and design.', 3, 3, 'Shelf A2'),
('978-0-201-63361-0', 'Design Patterns: Elements of Reusable Object-Oriented Software', 'Gang of Four', 'Software Engineering', 'Addison-Wesley', 1994, '1st', 395, 'English', 'Classic book on design patterns in software development.', 4, 4, 'Shelf A3'),
('978-0-596-52068-7', 'JavaScript: The Good Parts', 'Douglas Crockford', 'Web Development', 'O\'Reilly Media', 2008, '1st', 176, 'English', 'A deep dive into JavaScript best practices.', 6, 6, 'Shelf B1'),
('978-1-491-95027-2', 'Learning React', 'Alex Banks, Eve Porcello', 'Web Development', 'O\'Reilly Media', 2020, '2nd', 310, 'English', 'Modern patterns for developing React applications.', 5, 5, 'Shelf B2'),
('978-0-13-468599-2', 'Introduction to Algorithms', 'Thomas H. Cormen', 'Computer Science', 'MIT Press', 2009, '3rd', 1312, 'English', 'Comprehensive introduction to algorithms and data structures.', 4, 4, 'Shelf C1'),
('978-0-134-68599-3', 'Database System Concepts', 'Abraham Silberschatz', 'Database', 'McGraw-Hill', 2019, '7th', 1376, 'English', 'Fundamental concepts of database management systems.', 3, 3, 'Shelf C2'),
('978-0-321-12742-6', 'The Pragmatic Programmer', 'Andrew Hunt, David Thomas', 'Programming', 'Addison-Wesley', 2019, '2nd', 352, 'English', 'Your journey to mastery in software development.', 5, 5, 'Shelf A4'),
('978-1-449-35573-9', 'Designing Data-Intensive Applications', 'Martin Kleppmann', 'Software Engineering', 'O\'Reilly Media', 2017, '1st', 616, 'English', 'The big ideas behind reliable, scalable systems.', 3, 3, 'Shelf C3'),
('978-0-596-00784-8', 'Head First Design Patterns', 'Eric Freeman, Elisabeth Robson', 'Software Engineering', 'O\'Reilly Media', 2004, '1st', 694, 'English', 'A brain-friendly guide to design patterns.', 4, 4, 'Shelf A5');

-- Insert Sample Transactions
INSERT INTO transactions (user_id, book_id, issue_date, due_date, status, issued_by) VALUES
(3, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'issued', 2),
(3, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 11 DAY), 'issued', 2),
(4, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'returned', 2);

-- Update available copies based on issued books
UPDATE books SET available_copies = available_copies - 1 WHERE id IN (1, 4);

-- Insert Activity Logs
INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description) VALUES
(1, 'LOGIN', 'user', 1, 'Admin logged in'),
(2, 'CREATE_BOOK', 'book', 1, 'Added new book: Clean Code'),
(2, 'ISSUE_BOOK', 'transaction', 1, 'Issued book to student'),
(3, 'LOGIN', 'user', 3, 'Student logged in');
