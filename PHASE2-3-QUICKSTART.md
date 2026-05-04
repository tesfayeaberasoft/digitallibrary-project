# Phase 2 & 3 Quick Start Guide

## 🚀 Get Started in 5 Minutes

### Step 1: Apply Database Migration (REQUIRED)

**For PowerShell (Windows):**
```powershell
Get-Content database/schema-phase2-3.sql | C:\xampp\mysql\bin\mysql.exe -u root digital_library
```

**For Command Prompt:**
```cmd
type database\schema-phase2-3.sql | C:\xampp\mysql\bin\mysql.exe -u root digital_library
```

**For MySQL Workbench:**
1. Open MySQL Workbench
2. Connect to your database
3. File → Open SQL Script → Select `database/schema-phase2-3.sql`
4. Click Execute (lightning bolt icon)

This creates all Phase 2 & 3 tables.

### Step 2: Start Backend Server

```bash
cd backend
php -S localhost:8000 -t public
```

Keep this terminal open.

### Step 3: Start Frontend Server

Open a new terminal:

```bash
cd frontend
npm start
```

The app will open at `http://localhost:3000`

### Step 4: Login

Use the default admin credentials:
- **Email**: admin@library.com
- **Password**: admin123

## ✨ Try New Features

### 1. Notifications (Immediate)

Look at the top-right corner of the navbar:
- Click the 🔔 bell icon
- You'll see a dropdown with notifications
- Try marking notifications as read
- Delete notifications

### 2. Reservations

1. Go to **Books** page
2. Find a book that's currently "Issued" (not available)
3. Click on the book to view details
4. Click **"Reserve Book"** button (if implemented)
5. Go to **Reservations** page from navbar
6. See your reservation with queue position
7. Try canceling a reservation

### 3. Fines

1. Click **Fines** in the navbar
2. View any unpaid fines
3. Click **"Pay Now"** on a fine
4. Select payment method
5. Confirm payment
6. See the fine marked as "Paid"

### 4. Test Backend APIs

```bash
cd backend
php test-phase2-3-api.php
```

This will test all 40+ new endpoints and show results in color.

## 📋 What to Test

### For Regular Users (Members)

✅ **Notifications**
- View notifications in bell dropdown
- Mark notifications as read
- Delete notifications
- Check unread count updates

✅ **Reservations**
- Reserve an issued book
- View reservation queue position
- Cancel a reservation
- Check reservation expiry date

✅ **Fines**
- View unpaid fines
- See total unpaid amount
- Pay a fine with different payment methods
- View payment history

✅ **Reviews** (API Ready)
- Rate a book (1-5 stars)
- Write a review
- Edit your review
- Delete your review
- Mark other reviews as helpful

✅ **Reading Lists** (API Ready)
- Create a reading list
- Add books to your list
- Make a list public
- View public lists from others

### For Admins/Librarians

✅ **Fine Management**
- View all fines in the system
- Waive fines for users
- View fine statistics
- Generate revenue reports

✅ **Reservation Management**
- View all reservations for a book
- See queue positions
- Monitor expired reservations

✅ **Reports & Analytics**
- Dashboard statistics
- Most popular books
- Most active users
- Overdue report
- Revenue report
- Category statistics
- Monthly activity trends

## 🔧 API Testing Examples

### Using cURL

**Get Notifications:**
```bash
curl -X GET http://localhost:8000/api/notifications \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Create Reservation:**
```bash
curl -X POST http://localhost:8000/api/reservations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"book_id": 1}'
```

**Pay Fine:**
```bash
curl -X POST http://localhost:8000/api/fines/1/pay \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"payment_method": "cash"}'
```

**Create Review:**
```bash
curl -X POST http://localhost:8000/api/books/1/reviews \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"rating": 5, "review_text": "Great book!"}'
```

### Using the Test Script

The easiest way to test all endpoints:

```bash
cd backend
php test-phase2-3-api.php
```

This will:
- Login automatically
- Test all 40+ endpoints
- Show color-coded results
- Display success/failure for each test

## 📊 Expected Results

### After Database Migration

You should see these new tables:
- reservations
- fines
- notifications
- reviews
- reading_lists
- reading_list_items
- libraries
- book_transfers
- qr_codes
- user_preferences
- digital_content

### After Starting Servers

**Backend** (http://localhost:8000):
- API endpoints responding
- No PHP errors in terminal

**Frontend** (http://localhost:3000):
- App loads successfully
- Navbar shows bell icon
- New menu items: Reservations, Fines

### After Testing

**Notifications:**
- Bell icon shows unread count
- Dropdown displays notifications
- Can mark as read/delete

**Reservations:**
- Page shows user's reservations
- Queue positions displayed
- Can cancel reservations

**Fines:**
- Page shows unpaid fines
- Total unpaid amount displayed
- Payment modal works

## 🐛 Troubleshooting

### Database Migration Fails

**Error**: Table already exists
**Solution**: Tables were already created. You're good to go!

**Error**: Access denied
**Solution**: Check your MySQL credentials

### Notifications Not Showing

**Check**:
1. Database migration completed?
2. Backend server running?
3. Check browser console for errors
4. Verify token is valid (try logging out and back in)

### API Endpoints Return 404

**Check**:
1. Backend server running on port 8000?
2. Routes added to `backend/public/index.php`?
3. Check URL path is correct

### Frontend Pages Not Loading

**Check**:
1. Routes added to `frontend/src/App.js`?
2. Components imported correctly?
3. Check browser console for errors
4. Try `npm install` again

## 📝 Test Checklist

Use this checklist to verify everything works:

### Backend
- [ ] Database migration successful
- [ ] Backend server starts without errors
- [ ] Test script runs successfully
- [ ] All API endpoints return 200/201
- [ ] Authentication works

### Frontend
- [ ] Frontend compiles without errors
- [ ] App loads at localhost:3000
- [ ] Login works
- [ ] Navbar shows bell icon
- [ ] Reservations page loads
- [ ] Fines page loads

### Features
- [ ] Notifications dropdown works
- [ ] Can mark notifications as read
- [ ] Can view reservations
- [ ] Can cancel reservations
- [ ] Can view fines
- [ ] Can pay fines
- [ ] Payment modal works

### Admin Features (if admin/librarian)
- [ ] Can view all fines
- [ ] Can waive fines
- [ ] Can access reports
- [ ] Dashboard shows statistics

## 🎯 Next Steps After Testing

1. **Enhance BookDetails Page**
   - Add reviews section
   - Add reserve button
   - Show average rating

2. **Create Reading Lists Page**
   - Display user's lists
   - Create/edit/delete lists
   - Add/remove books

3. **Enhance Dashboard**
   - Add Phase 2 & 3 statistics
   - Show pending reservations
   - Display unpaid fines

4. **Integration**
   - Show fines on Profile
   - Add reservation status to books
   - Display reading list count

## 📚 Documentation

- **Full Setup Guide**: `PHASE2-3-README.md`
- **Implementation Details**: `IMPLEMENTATION-SUMMARY.md`
- **Progress Tracking**: `PHASE2-3-PROGRESS.md`
- **Project Overview**: `PROJECT_SUMMARY.md`

## 💬 Need Help?

1. Check the documentation files listed above
2. Review the test script output for errors
3. Check browser console for frontend errors
4. Check terminal for backend errors
5. Verify database migration completed

## ✅ Success Indicators

You'll know everything is working when:

✅ Database has all new tables
✅ Backend server runs without errors
✅ Frontend compiles successfully
✅ Test script shows all tests passing
✅ Bell icon appears in navbar
✅ Notifications dropdown works
✅ Reservations page displays
✅ Fines page displays
✅ Can interact with all features

## 🎉 You're All Set!

If you've completed all steps and tests pass, you're ready to use all Phase 2 & 3 features!

**Happy Testing! 🚀**
