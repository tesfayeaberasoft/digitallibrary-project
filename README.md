# Digital Library Management System

A comprehensive library management system with separate PHP backend and React frontend.

## Project Structure

```
digitallibrary-project/
├── backend/          # PHP REST API
├── frontend/         # React Application
└── database/         # MySQL Schema & Seeds
```

## Phase 1: Core Features (Basic Features A)

### Implemented Features
- ✅ User Management (Registration, Login, Profile)
- ✅ Book Management (CRUD Operations)
- ✅ Search & Browse (Title, Author, ISBN, Category)
- ✅ Issue & Return Books
- ✅ Availability Tracking

## Technology Stack

### Backend
- PHP 8.0+
- MySQL 8.0+
- JWT Authentication
- RESTful API Architecture

### Frontend
- React 18+
- React Router
- Axios for API calls
- Modern CSS/Tailwind

## Setup Instructions

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
# Configure database credentials in .env
php -S localhost:8000 -t public
```

### Frontend Setup
```bash
cd frontend
npm install
npm start
```

### Database Setup
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql
```

## API Documentation

Base URL: `http://localhost:8000/api`

### Authentication Endpoints
- POST `/auth/register` - User registration
- POST `/auth/login` - User login
- POST `/auth/logout` - User logout
- POST `/auth/reset-password` - Password reset
- GET `/auth/profile` - Get user profile
- PUT `/auth/profile` - Update user profile

### Book Endpoints
- GET `/books` - List all books
- GET `/books/:id` - Get book details
- POST `/books` - Add new book (Admin/Librarian)
- PUT `/books/:id` - Update book (Admin/Librarian)
- DELETE `/books/:id` - Delete book (Admin)
- GET `/books/search` - Search books

### Issue/Return Endpoints
- POST `/transactions/issue` - Issue a book
- POST `/transactions/return` - Return a book
- GET `/transactions/user/:userId` - Get user's transactions
- GET `/transactions/book/:bookId` - Get book's transaction history

## User Roles
- **Admin**: Full system control
- **Librarian**: Manage books and users
- **Student**: Borrow and search books
- **Staff**: Borrow and search books

## License
MIT
