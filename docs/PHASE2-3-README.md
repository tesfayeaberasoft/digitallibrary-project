# Phase 2 & 3 Implementation Guide

## Overview

This document provides a comprehensive guide for the Phase 2 (Intermediate Features) and Phase 3 (Advanced Features) implementation of the Digital Library Management System.

## ✅ Completed Features

### Backend Implementation (100%)

#### 1. Database Schema
- ✅ Reservations system with queue management
- ✅ Fines tracking and payment system
- ✅ Notifications system
- ✅ Reviews and ratings
- ✅ Reading lists
- ✅ Multi-library support
- ✅ Book transfers
- ✅ QR codes
- ✅ User preferences

#### 2. Models
- ✅ `Reservation.php` - Queue management, expiry handling
- ✅ `Fine.php` - Fine calculation, payment processing
- ✅ `Notification.php` - Multi-type notifications
- ✅ `Review.php` - Ratings and reviews with helpful count
- ✅ `ReadingList.php` - Personal and public reading lists

#### 3. Controllers
- ✅ `ReservationController.php` - Create, view, cancel reservations
- ✅ `FineController.php` - View, pay, waive fines
- ✅ `NotificationController.php` - List, mark read, delete notifications
- ✅ `ReviewController.php` - CRUD operations for reviews
- ✅ `ReadingListController.php` - Manage reading lists
- ✅ `ReportController.php` - Analytics and statistics

#### 4. API Routes
All Phase 2 & 3 routes have been added to `backend/public/index.php`:

**Reservations:**
- `POST /api/reservations` - Create reservation
- `GET /api/reservations` - Get user reservations
- `DELETE /api/reservations/{id}` - Cancel reservation
- `GET /api/books/{id}/reservations` - Get book reservations (Admin)

**Fines:**
- `GET /api/fines` - Get user fines
- `GET /api/fines/total` - Get total unpaid fines
- `POST /api/fines/{id}/pay` - Pay fine
- `POST /api/fines/{id}/waive` - Waive fine (Admin)
- `GET /api/fines/all` - Get all fines (Admin)
- `GET /api/fines/statistics` - Get fine statistics (Admin)

**Notifications:**
- `GET /api/notifications` - Get user notifications
- `GET /api/notifications/unread` - Get unread notifications
- `GET /api/notifications/unread-count` - Get unread count
- `PUT /api/notifications/{id}/read` - Mark as read
- `PUT /api/notifications/read-all` - Mark all as read
- `DELETE /api/notifications/{id}` - Delete notification
- `DELETE /api/notifications/read-all` - Delete all read

**Reviews:**
- `POST /api/books/{id}/reviews` - Create review
- `GET /api/books/{id}/reviews` - Get book reviews
- `GET /api/books/{id}/reviews/statistics` - Get review statistics
- `GET /api/reviews/user` - Get user reviews
- `PUT /api/reviews/{id}` - Update review
- `DELETE /api/reviews/{id}` - Delete review
- `POST /api/reviews/{id}/helpful` - Mark review as helpful

**Reading Lists:**
- `GET /api/reading-lists` - Get user reading lists
- `GET /api/reading-lists/public` - Get public reading lists
- `GET /api/reading-lists/{id}` - Get reading list with books
- `POST /api/reading-lists` - Create reading list
- `PUT /api/reading-lists/{id}` - Update reading list
- `DELETE /api/reading-lists/{id}` - Delete reading list
- `POST /api/reading-lists/{id}/books` - Add book to list
- `DELETE /api/reading-lists/{id}/books/{bookId}` - Remove book from list

**Reports (Admin/Librarian):**
- `GET /api/reports/dashboard` - Dashboard statistics
- `GET /api/reports/popular-books` - Most borrowed books
- `GET /api/reports/active-users` - Most active users
- `GET /api/reports/overdue` - Overdue report
- `GET /api/reports/revenue` - Fine revenue report
- `GET /api/reports/category-stats` - Category statistics
- `GET /api/reports/monthly-activity` - Monthly activity report

### Frontend Implementation (40%)

#### Completed Components
- ✅ `NotificationBell.js` - Real-time notification dropdown in navbar
- ✅ `Reservations.js` - Reservation management page
- ✅ `Fines.js` - Fine viewing and payment page

#### Updated Components
- ✅ `Navbar.js` - Added NotificationBell and new menu links
- ✅ `App.js` - Added routes for new pages

## 🚧 Remaining Work

### Frontend Pages (To Be Implemented)
1. **Reading Lists Page** - Create, manage, and share reading lists
2. **Enhanced Book Details** - Add reviews section, reservation button
3. **Reports Dashboard** - Admin analytics and statistics
4. **Notifications Page** - Full notification history view

### Integration Tasks
1. Add "Reserve Book" button to BookDetails page
2. Display user's fines on Profile page
3. Show reviews on BookDetails page
4. Add reading list functionality to Books page
5. Enhance Dashboard with Phase 2 & 3 statistics

## 📋 Setup Instructions

### 1. Database Migration

Run the Phase 2 & 3 schema migration:

```bash
mysql -u root -p digital_library < database/schema-phase2-3.sql
```

This will create all new tables:
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

### 2. Backend Setup

No additional backend setup required. All controllers and models are in place.

### 3. Frontend Setup

The new pages are already integrated. Just ensure the frontend is running:

```bash
cd frontend
npm install
npm start
```

### 4. Testing

#### API Testing

Use the provided test script:

```bash
cd backend
php test-phase2-3-api.php
```

This will test all Phase 2 & 3 endpoints.

#### Manual Testing

1. **Reservations:**
   - Navigate to Books page
   - Try to reserve a book that's currently issued
   - Check Reservations page for your reservation
   - Cancel a reservation

2. **Fines:**
   - Navigate to Fines page
   - View any unpaid fines
   - Try paying a fine with different payment methods

3. **Notifications:**
   - Click the bell icon in navbar
   - View unread notifications
   - Mark notifications as read
   - Delete notifications

4. **Reviews:**
   - Go to a book details page
   - Write a review with rating
   - Edit your review
   - Mark other reviews as helpful

5. **Reading Lists:**
   - Create a new reading list
   - Add books to your list
   - Make a list public
   - View public lists from other users

## 🔧 Configuration

### Fine Calculation

Fines are automatically calculated in the `Fine` model:
- Rate: $0.50 per day
- Maximum: $25.00 per book

To change these values, edit `backend/src/Models/Fine.php`:

```php
private $finePerDay = 0.50;
private $maxFine = 25.00;
```

### Reservation Expiry

Reservations expire after 7 days by default. To change this, edit `backend/src/Models/Reservation.php`:

```php
private $expiryDays = 7;
```

### Notification Polling

The NotificationBell component polls for new notifications every 30 seconds. To change this, edit `frontend/src/components/NotificationBell.js`:

```javascript
const interval = setInterval(fetchUnreadCount, 30000); // 30 seconds
```

## 📊 Database Indexes

The schema includes optimized indexes for performance:

- Reservations: `user_id`, `book_id`, `status`, `queue_position`
- Fines: `user_id`, `status`, `transaction_id`
- Notifications: `user_id`, `is_read`, `type`, `created_at`
- Reviews: `book_id`, `user_id`, `rating`
- Reading Lists: `user_id`

## 🔐 Permissions

### User Roles

**Member:**
- Create/cancel own reservations
- View/pay own fines
- View own notifications
- Create/edit/delete own reviews
- Create/manage own reading lists

**Librarian:**
- All member permissions
- View all reservations
- View all fines
- Waive fines
- View reports and analytics

**Admin:**
- All librarian permissions
- Delete any review
- Access all reports
- Manage system settings

## 🐛 Troubleshooting

### Issue: Notifications not appearing

**Solution:**
1. Check if notifications table exists
2. Verify API endpoint: `GET /api/notifications/unread-count`
3. Check browser console for errors
4. Ensure user is authenticated

### Issue: Fines not calculating

**Solution:**
1. Verify transactions have `due_date` set
2. Check if Fine model is being called on return
3. Ensure database has fines table
4. Check `calculateFine()` method in Fine model

### Issue: Reservations not working

**Solution:**
1. Verify book status is 'issued' (can't reserve available books)
2. Check reservation expiry dates
3. Ensure queue_position is being calculated
4. Verify foreign key constraints

## 📈 Performance Optimization

### Database Queries

All models use prepared statements to prevent SQL injection and improve performance.

### Caching Recommendations

Consider implementing caching for:
- Popular books list
- Category statistics
- User notification counts
- Public reading lists

### Frontend Optimization

- Notification polling can be replaced with WebSockets for real-time updates
- Implement pagination for large lists
- Add loading skeletons for better UX

## 🚀 Future Enhancements

### Phase 4 (Potential)
- Digital content viewer (PDF, EPUB)
- Recommendation engine based on reading history
- Barcode/QR code scanning
- Email/SMS notifications
- Mobile app
- Advanced search with filters
- Book recommendations
- Social features (follow users, share lists)

## 📝 API Documentation

Full API documentation is available in the test script: `backend/test-phase2-3-api.php`

Each endpoint includes:
- HTTP method
- URL path
- Required parameters
- Expected response
- Authentication requirements

## 🤝 Contributing

When adding new features:

1. Create model in `backend/src/Models/`
2. Create controller in `backend/src/Controllers/`
3. Add routes in `backend/public/index.php`
4. Create frontend page in `frontend/src/pages/`
5. Add route in `frontend/src/App.js`
6. Update navbar if needed
7. Test all endpoints
8. Update documentation

## 📞 Support

For issues or questions:
- Check `PHASE2-3-PROGRESS.md` for implementation status
- Review `PROJECT_SUMMARY.md` for overall project structure
- Test endpoints using `backend/test-phase2-3-api.php`
- Check browser console and PHP error logs

## ✨ Summary

Phase 2 & 3 implementation is **70% complete**:
- ✅ Backend: 100% (Database, Models, Controllers, Routes)
- 🚧 Frontend: 40% (NotificationBell, Reservations, Fines pages)
- ⏳ Integration: 20% (Needs book details enhancement, dashboard updates)

**Next Priority:**
1. Enhance BookDetails page with reviews and reservation button
2. Create Reading Lists page
3. Add Phase 2 & 3 statistics to Dashboard
4. Implement full notifications page
5. Test all features end-to-end
