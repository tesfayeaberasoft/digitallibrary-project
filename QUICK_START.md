# Quick Start Guide - Digital Library Management System

## 🚀 5-Minute Setup

### Prerequisites Check
```bash
php --version    # Should be 8.0+
mysql --version  # Should be 8.0+
node --version   # Should be 16+
composer --version
```

### Step 1: Database (2 minutes)
```bash
# Create and populate database
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql
```

### Step 2: Backend (1 minute)
```bash
cd backend
composer install
cp .env.example .env

# Edit .env - Update these lines:
# DB_USER=root
# DB_PASS=your_password
# JWT_SECRET=your-random-secret-key

# Start server
php -S localhost:8000 -t public
```

### Step 3: Frontend (2 minutes)
```bash
# Open new terminal
cd frontend
npm install
cp .env.example .env
npm start
```

## ✅ Test It Works

1. Open http://localhost:3000
2. Login with: `admin@library.com` / `admin123`
3. You should see the dashboard!

## 🎯 What You Can Do Now

### As Admin (admin@library.com)
- ✅ View dashboard statistics
- ✅ Browse all books
- ✅ Search for books
- ✅ Add new books
- ✅ Edit/delete books
- ✅ View all users
- ✅ Issue books to users
- ✅ Return books
- ✅ View all transactions

### As Student (student@library.com)
- ✅ Browse and search books
- ✅ View book details
- ✅ See your borrowed books
- ✅ Update your profile

## 📝 Common Tasks

### Add a New Book
1. Login as Admin/Librarian
2. Go to "Books" page
3. Click "+ Add Book"
4. Fill in details (ISBN, Title, Author, etc.)
5. Click "Add Book"

### Issue a Book
1. Login as Admin/Librarian
2. Go to "Books" page
3. Click on a book
4. Click "Issue Book"
5. Select user and due date
6. Confirm

### Search for Books
1. Go to "Books" page
2. Type in search box (title, author, or ISBN)
3. Click "Search"

## 🔧 Troubleshooting

### Backend won't start
```bash
# Check if port 8000 is free
netstat -ano | findstr :8000

# Try different port
php -S localhost:8080 -t public
```

### Frontend won't start
```bash
# Clear cache and reinstall
rm -rf node_modules package-lock.json
npm install
npm start
```

### Database connection error
1. Check MySQL is running
2. Verify credentials in `backend/.env`
3. Ensure database exists:
```bash
mysql -u root -p -e "SHOW DATABASES;"
```

### Can't login
1. Verify backend is running (http://localhost:8000)
2. Check browser console for errors
3. Try default credentials:
   - Email: admin@library.com
   - Password: admin123

## 📱 Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@library.com | admin123 |
| Librarian | librarian@library.com | admin123 |
| Student | student@library.com | admin123 |
| Staff | staff@library.com | admin123 |

## 🎨 Sample Data Included

- ✅ 10 books (programming & tech)
- ✅ 4 users (all roles)
- ✅ 3 sample transactions
- ✅ Activity logs

## 🔗 Important URLs

- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000/api
- **API Test**: http://localhost:8000/api/books

## 📚 Next Steps

1. ✅ Explore the dashboard
2. ✅ Try searching for books
3. ✅ Issue a book to a student
4. ✅ Return a book
5. ✅ Add your own books
6. ✅ Create new users
7. 📖 Read PROJECT_SUMMARY.md for full features
8. 📖 Read SETUP.md for detailed setup

## 💡 Pro Tips

- Use Chrome DevTools to inspect API calls
- Check `backend/.env` for configuration
- Sample books are in `database/seeds.sql`
- All passwords are hashed with bcrypt
- JWT tokens expire in 24 hours (configurable)

## 🆘 Need Help?

1. Check SETUP.md for detailed instructions
2. Review PROJECT_SUMMARY.md for architecture
3. Check browser console for frontend errors
4. Check terminal for backend errors
5. Create an issue on GitHub

## ✨ You're Ready!

Your Digital Library Management System is now running. Start exploring and managing your library! 📚

---

**Happy Coding!** 🚀
