# Digital Library Management System - Setup Guide

## Prerequisites

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer
- Node.js 16+ and npm
- Git

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/tesfayeaberasoft/digitallibrary-project.git
cd digitallibrary-project
```

### 2. Database Setup

```bash
# Login to MySQL
mysql -u root -p

# Create database and import schema
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql
```

### 3. Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit .env file with your database credentials
# Update DB_HOST, DB_NAME, DB_USER, DB_PASS
# Update JWT_SECRET with a secure random string

# Start PHP development server
php -S localhost:8000 -t public
```

The backend API will be available at `http://localhost:8000`

### 4. Frontend Setup

Open a new terminal:

```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start development server
npm start
```

The frontend will be available at `http://localhost:3000`

## Default Login Credentials

After running the seed data, you can login with:

**Admin Account:**
- Email: admin@library.com
- Password: admin123

**Librarian Account:**
- Email: librarian@library.com
- Password: admin123

**Student Account:**
- Email: student@library.com
- Password: admin123

**Staff Account:**
- Email: staff@library.com
- Password: admin123

## Testing the Application

1. Open `http://localhost:3000` in your browser
2. Login with one of the default accounts
3. Explore the features:
   - Browse books
   - Search for books
   - View transactions (as student/staff)
   - Manage books (as admin/librarian)
   - Issue/return books (as admin/librarian)
   - View dashboard statistics

## Troubleshooting

### Backend Issues

**Database Connection Error:**
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists

**Composer Dependencies:**
```bash
cd backend
composer update
```

### Frontend Issues

**Port Already in Use:**
```bash
# Change port in package.json or use:
PORT=3001 npm start
```

**API Connection Error:**
- Verify backend is running on port 8000
- Check REACT_APP_API_URL in frontend/.env

## Production Deployment

### Backend

1. Set `APP_DEBUG=false` in `.env`
2. Use a production web server (Apache/Nginx)
3. Configure virtual host to point to `backend/public`
4. Enable HTTPS
5. Set secure JWT_SECRET

### Frontend

```bash
cd frontend
npm run build
```

Deploy the `build` folder to your web server.

## Next Steps (Phase 2)

After Phase 1 is working, you can implement:
- Reservation System
- Fine Management
- Notifications
- Reports & Analytics
- And more advanced features from the requirements document

## Support

For issues or questions, please create an issue on GitHub.
