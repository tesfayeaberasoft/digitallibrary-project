<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Transaction;
use App\Models\Book;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class TransactionController {
    public function issue() {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();
        
        // Check permissions
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to issue books');
        }
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'user_id' => 'required|numeric',
            'book_id' => 'required|numeric',
            'due_date' => 'required'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        // Check if book exists and is available
        $book = Book::findById($data['book_id']);
        
        if (!$book) {
            Response::notFound('Book not found');
        }
        
        if ($book['available_copies'] <= 0) {
            Response::error('Book is not available', 400);
        }
        
        // Check if user already has this book issued
        $existingTransaction = Transaction::findActiveByUserAndBook($data['user_id'], $data['book_id']);
        
        if ($existingTransaction) {
            Response::error('User already has this book issued', 400);
        }
        
        try {
            // Create transaction
            $transactionData = [
                'user_id' => $data['user_id'],
                'book_id' => $data['book_id'],
                'issue_date' => date('Y-m-d'),
                'due_date' => $data['due_date'],
                'issued_by' => $user['id'],
                'notes' => $data['notes'] ?? null
            ];
            
            $transactionId = Transaction::create($transactionData);
            
            // Update book availability
            Book::updateAvailability($data['book_id'], false);
            
            $transaction = Transaction::findById($transactionId);
            
            // Log activity
            ActivityLog::log($user['id'], 'ISSUE_BOOK', 'transaction', $transactionId, 
                "Issued book '{$book['title']}' to user ID {$data['user_id']}");
            
            Response::success($transaction, 'Book issued successfully', 201);
        } catch (\Exception $e) {
            Response::serverError('Failed to issue book: ' . $e->getMessage());
        }
    }

    public function return() {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();
        
        // Check permissions
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to process returns');
        }
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'transaction_id' => 'required|numeric'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        $transaction = Transaction::findById($data['transaction_id']);
        
        if (!$transaction) {
            Response::notFound('Transaction not found');
        }
        
        if ($transaction['status'] !== 'issued' && $transaction['status'] !== 'overdue') {
            Response::error('Book has already been returned', 400);
        }
        
        try {
            // Update transaction
            Transaction::returnBook($data['transaction_id'], $user['id']);
            
            // Update book availability
            Book::updateAvailability($transaction['book_id'], true);
            
            $updatedTransaction = Transaction::findById($data['transaction_id']);
            
            // Log activity
            ActivityLog::log($user['id'], 'RETURN_BOOK', 'transaction', $data['transaction_id'], 
                "Returned book '{$transaction['book_title']}'");
            
            Response::success($updatedTransaction, 'Book returned successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to return book');
        }
    }

    public function userTransactions($userId) {
        $user = $GLOBALS['auth_user'];
        
        // Users can only view their own transactions unless they're admin/librarian
        if ($user['id'] != $userId && !in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You can only view your own transactions');
        }
        
        $filters = [
            'status' => Request::get('status'),
            'limit' => Request::get('limit', 50)
        ];
        
        $transactions = Transaction::getUserTransactions($userId, $filters);
        
        Response::success([
            'transactions' => $transactions,
            'total' => count($transactions)
        ]);
    }

    public function bookTransactions($bookId) {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can view book transaction history
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to view book transactions');
        }
        
        $filters = [
            'status' => Request::get('status'),
            'limit' => Request::get('limit', 50)
        ];
        
        $transactions = Transaction::getBookTransactions($bookId, $filters);
        
        Response::success([
            'transactions' => $transactions,
            'total' => count($transactions)
        ]);
    }

    public function index() {
        $user = $GLOBALS['auth_user'];
        
        // Only admin/librarian can view all transactions
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to view all transactions');
        }
        
        $filters = [
            'status' => Request::get('status'),
            'user_id' => Request::get('user_id'),
            'book_id' => Request::get('book_id'),
            'limit' => Request::get('limit', 50),
            'offset' => Request::get('offset', 0)
        ];
        
        $transactions = Transaction::all($filters);
        
        Response::success([
            'transactions' => $transactions,
            'total' => count($transactions)
        ]);
    }
}
