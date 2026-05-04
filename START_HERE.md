# 🚀 Start Your Digital Library System

## Quick Start (2 Steps)

### Step 1: Start Backend Server
Open a terminal and run:
```bash
cd backend
php -S localhost:8000 -t public
```

**OR** double-click: `start-backend.bat`

You should see:
```
PHP 8.x Development Server (http://localhost:8000) started
```

### Step 2: Start Frontend (New Terminal)
Open a **NEW** terminal and run:
```bash
cd frontend
npm start
```

**OR** double-click: `start-frontend.bat`

Browser will open automatically at: http://localhost:3000

## ✅ Test Login

Use these credentials:
- **Email**: admin@library.com
- **Password**: admin123

## 🐛 Troubleshooting

### "Login Failed" or "Registration Failed"

**Check 1: Is backend running?**
- Open http://localhost:8000/api/books in your browser
- You should see JSON data with books
- If you see "Connection refused", backend is not running

**Check 2: Check browser console**
- Press F12 in browser
- Go to Console tab
- Look for red errors
- Common error: "Network Error" = backend not running

**Check 3: Check backend terminal**
- Look for errors in the terminal running PHP server
- Each request should show up as a log line

### Backend Won't Start

**Error: "Address already in use"**
```bash
# Kill process on port 8000
netstat -ano | findstr :8000
taskkill /PID <PID_NUMBER> /F

# Or use different port
php -S localhost:8080 -t public
# Then update frontend/.env: REACT_APP_API_URL=http://localhost:8080/api
```

**Error: "Database connection failed"**
```bash
# Test database connection
cd backend
php test-connection.php

# If fails, check MySQL is running
# Then verify credentials in backend/.env
```

### Frontend Won't Start

**Error: "Port 3000 already in use"**
```bash
# Use different port
set PORT=3001
npm start
```

**Error: "Cannot find module"**
```bash
cd frontend
npm install
```

## 📝 Default Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@library.com | admin123 |
| Librarian | librarian@library.com | admin123 |
| Student | student@library.com | admin123 |
| Staff | staff@library.com | admin123 |

## 🎯 What to Test

1. ✅ Login with admin account
2. ✅ View dashboard statistics
3. ✅ Browse books
4. ✅ Search for a book
5. ✅ View book details
6. ✅ Check your transactions
7. ✅ Update your profile

## 🆘 Still Having Issues?

1. Make sure both terminals are running (backend AND frontend)
2. Check browser console (F12) for errors
3. Verify backend is accessible: http://localhost:8000/api/books
4. Try clearing browser cache (Ctrl+Shift+Delete)
5. Restart both servers

## ✨ You're All Set!

Once both servers are running:
- Backend API: http://localhost:8000
- Frontend App: http://localhost:3000

Happy coding! 📚
