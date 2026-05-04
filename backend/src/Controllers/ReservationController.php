<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Reservation;
use App\Models\Book;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class ReservationController {
    public function create() {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'book_id' => 'required|numeric'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        // Check if book exists
        $book = Book::findById($data['book_id']);
        if (!$book) {
            Response::notFound('Book not found');
        }
        
        // Check if book is available
        if ($book['available_copies'] > 0) {
            Response::error('Book is currently available. Please borrow it directly.', 400);
        }
        
        // Check if user already has a reservation for this book
        if (Reservation::hasActiveReservation($user['id'], $data['book_id'])) {
            Response::error('You already have an active reservation for this book', 400);
        }
        
        try {
            $reservationId = Reservation::create([
                'user_id' => $user['id'],
                'book_id' => $data['book_id']
            ]);
            
            $reservation = Reservation::getUserReservations($user['id']);
            $reservation = array_filter($reservation, fn($r) => $r['id'] == $reservationId)[0] ?? null;
            
            // Log activity
            ActivityLog::log($user['id'], 'CREATE_RESERVATION', 'reservation', $reservationId, 
                "Reserved book: {$book['title']}");
            
            // Send notification
            Notification::sendGeneralNotification(
                $user['id'],
                'Reservation Confirmed',
                "Your reservation for '{$book['title']}' has been confirmed. You are in position {$reservation['queue_position']} in the queue."
            );
            
            Response::success($reservation, 'Book reserved successfully', 201);
        } catch (\Exception $e) {
            Response::serverError('Failed to create reservation: ' . $e->getMessage());
        }
    }

    public function index() {
        $user = $GLOBALS['auth_user'];
        $status = Request::get('status');
        
        $reservations = Reservation::getUserReservations($user['id'], $status);
        
        Response::success([
            'reservations' => $reservations,
            'total' => count($reservations)
        ]);
    }

    public function cancel($id) {
        $user = $GLOBALS['auth_user'];
        
        $reservations = Reservation::getUserReservations($user['id']);
        $reservation = array_filter($reservations, fn($r) => $r['id'] == $id)[0] ?? null;
        
        if (!$reservation) {
            Response::notFound('Reservation not found');
        }
        
        if ($reservation['status'] !== 'pending') {
            Response::error('Only pending reservations can be cancelled', 400);
        }
        
        try {
            Reservation::cancel($id);
            
            ActivityLog::log($user['id'], 'CANCEL_RESERVATION', 'reservation', $id, 
                "Cancelled reservation for: {$reservation['title']}");
            
            Response::success([], 'Reservation cancelled successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to cancel reservation');
        }
    }

    public function getBookReservations($bookId) {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can view book reservations
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to view book reservations');
        }
        
        $reservations = Reservation::getBookReservations($bookId);
        
        Response::success([
            'reservations' => $reservations,
            'total' => count($reservations)
        ]);
    }
}
