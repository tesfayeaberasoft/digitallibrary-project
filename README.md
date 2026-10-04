# Digital Library Management System

A full-stack library management system built with a PHP REST API backend and a React frontend, targeting Ethiopian university/institutional use.

---

## Tech Stack

| Layer     | Technology                                      |
|-----------|-------------------------------------------------|
| Backend   | PHP 8.0+, custom MVC REST API, PDO + MySQL      |
| Auth      | JWT (`firebase/php-jwt`), bcrypt passwords      |
| Frontend  | React 18, React Router v6, Axios, Context API   |
| Database  | MySQL 8.0+ (utf8mb4)                            |
| Server    | Apache (`.htaccess` → `public/index.php`)        |

---

## Project Structure

```
digitallibrary-project/
│
├── docs/                          # All project documentation
│   ├── SETUP.md
│   ├── QUICK_START.md
│   ├── START_HERE.md
│   ├── PROJECT_SUMMARY.md
│   ├── IMPLEMENTATION-SUMMARY.md
│   ├── PHASE2-3-README.md
│   ├── PHASE2-3-PROGRESS.md
│   └── PHASE2-3-QUICKSTART.md
│
├── scripts/                       # Launcher scripts
│   ├── start-backend.bat
│   └── start-frontend.bat
│
├── backend/                       # PHP REST API
│   ├── public/
│   │   ├── index.php              # Single entry point — all routes defined here
│   │   └── .htaccess              # Apache rewrite rule
│   ├── src/
│   │   ├── Controllers/           # 11 controllers (Auth, Book, Transaction, ...)
│   │   ├── Core/                  # Router, Database, Request, Response
│   │   ├── Middleware/            # AuthMiddleware, CorsMiddleware
│   │   ├── Models/                # 9 models (User, Book, Fine, Review, ...)
│   │   └── Services/              # JWTService, ValidationService
│   ├── scripts/                   # Dev/utility PHP scripts (not for production)
│   │   ├── test-api.php
│   │   ├── test-connection.php
│   │   ├── test-phase2-3-api.php
│   │   ├── generate-password.php
│   │   └── update-passwords.php
│   ├── uploads/                   # Book cover image storage
│   ├── .env.example               # Environment variable template
│   ├── composer.json
│   └── composer.lock
│
├── database/
│   ├── migrations/                # SQL schema files (run in order)
│   │   ├── 001_core_schema.sql    # Phase 1: users, books, transactions
│   │   └── 002_phase2_3_schema.sql# Phase 2+3: reservations, fines, reviews, ...
│   ├── seeds/
│   │   └── seeds.sql              # Sample data (4 users, 10 books)
│   └── scripts/
│       └── update-passwords.sql   # One-off password migration script
│
└── frontend/                      # React Application
    ├── public/
    │   └── index.html
    └── src/
        ├── App.js                 # Root component — routes + ProtectedRoute
        ├── index.js               # React entry point
        ├── styles/                # Global stylesheets
        │   ├── App.css
        │   └── index.css
        ├── contexts/
        │   └── AuthContext.js     # Auth state + login/logout/updateUser
        ├── components/            # Shared UI components
        │   ├── Navbar.js / .css
        │   └── NotificationBell.js / .css
        ├── pages/
        │   ├── auth/              # Public / authentication pages
        │   │   ├── Home.js / .css
        │   │   ├── Login.js / .css
        │   │   ├── Register.js / .css
        │   │   └── Auth.css       # Shared auth styles
        │   ├── admin/             # Admin & librarian pages
        │   │   ├── Dashboard.js / .css
        │   │   └── Users.js
        │   └── library/           # Core library pages (all authenticated users)
        │       ├── Books.js / .css
        │       ├── BookDetails.js
        │       ├── Transactions.js
        │       ├── Reservations.js / .css
        │       ├── Fines.js / .css
        │       └── Profile.js
        ├── services/
        │   └── api.js             # Axios instance + all API service objects
        ├── hooks/                 # Custom React hooks
        │   ├── useAuth.js         # Re-exports useAuth from AuthContext
        │   └── useLocalStorage.js
        ├── utils/                 # Pure helper functions
        │   ├── formatters.js      # formatDate, formatCurrency, timeAgo, capitalize
        │   └── validators.js      # isValidEmail, isStrongPassword, isRequired
        └── constants/             # App-wide constants (mirrors backend enums)
            ├── roles.js           # ROLES, STAFF_ROLES
            ├── status.js          # TRANSACTION_STATUS, FINE_STATUS, etc.
            └── api.js             # API_BASE_URL, FINE_PER_DAY_ETB, poll intervals
```

---

## Features

| Feature               | Backend | Frontend |
|-----------------------|---------|----------|
| Authentication (JWT)  | ✅      | ✅       |
| Book Management       | ✅      | ✅       |
| Issue & Return        | ✅      | ✅       |
| Reservations + Queue  | ✅      | ✅       |
| Fines (5 ETB/day)     | ✅      | ✅       |
| Notifications         | ✅      | ✅       |
| Reviews & Ratings     | ✅      | ⬜       |
| Reading Lists         | ✅      | ⬜       |
| Reports               | ✅      | ⬜       |
| Dashboard Stats       | ✅      | ✅       |
| User Management       | ✅      | ✅       |

---

## Quick Start

See [`docs/QUICK_START.md`](docs/QUICK_START.md) for full setup instructions.

### TL;DR

```bash
# 1. Import the database
mysql -u root -p < database/migrations/001_core_schema.sql
mysql -u root -p < database/migrations/002_phase2_3_schema.sql
mysql -u root -p < database/seeds/seeds.sql

# 2. Start backend (Apache/PHP server pointing to backend/public/)
#    Or double-click: scripts/start-backend.bat

# 3. Start frontend
cd frontend
npm install
npm start
#    Or double-click: scripts/start-frontend.bat
```

### Default Accounts

| Role      | Email                    | Password   |
|-----------|--------------------------|------------|
| Admin     | admin@library.com        | admin123   |
| Librarian | librarian@library.com    | admin123   |
| Student   | student@library.com      | admin123   |
| Staff     | staff@library.com        | admin123   |

---

## Environment Setup

Copy `backend/.env.example` to `backend/.env` and fill in your database credentials and JWT secret.
