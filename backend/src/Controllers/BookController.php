<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Book;
use App\Models\ActivityLog;
use App\Services\ValidationService;

class BookController {
    public function index() {
        $filters = [
            'category' => Request::get('category'),
            'status' => Request::get('status', 'active'),
            'available' => Request::get('available'),
            'limit' => Request::get('limit', 20),
            'offset' => Request::get('offset', 0)
        ];
        
        $books = Book::all($filters);
        
        Response::success([
            'books' => $books,
            'total' => count($books)
        ]);
    }

    public function show($id) {
        $book = Book::findById($id);
        
        if (!$book) {
            Response::notFound('Book not found');
        }
        
        Response::success($book);
    }

    public function store() {
        $user = $GLOBALS['auth_user'];
        
        // Check permissions
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to add books');
        }
        
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'isbn' => 'required|unique:books,isbn',
            'title' => 'required|min:2',
            'author' => 'required|min:2',
            'total_copies' => 'numeric'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        try {
            $bookId = Book::create($data);
            $book = Book::findById($bookId);
            
            // Log activity
            ActivityLog::log($user['id'], 'CREATE_BOOK', 'book', $bookId, "Added book: {$book['title']}");
            
            Response::success($book, 'Book added successfully', 201);
        } catch (\Exception $e) {
            Response::serverError('Failed to add book: ' . $e->getMessage());
        }
    }

    public function update($id) {
        $user = $GLOBALS['auth_user'];
        
        // Check permissions
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            Response::forbidden('You do not have permission to update books');
        }
        
        $book = Book::findById($id);
        
        if (!$book) {
            Response::notFound('Book not found');
        }
        
        $data = Request::all();
        
        $validator = new ValidationService();
        $isValid = $validator->validate($data, [
            'title' => 'min:2',
            'author' => 'min:2',
            'total_copies' => 'numeric',
            'available_copies' => 'numeric'
        ]);
        
        if (!$isValid) {
            Response::validationError($validator->getErrors());
        }
        
        try {
            Book::update($id, $data);
            $updatedBook = Book::findById($id);
            
            // Log activity
            ActivityLog::log($user['id'], 'UPDATE_BOOK', 'book', $id, "Updated book: {$updatedBook['title']}");
            
            Response::success($updatedBook, 'Book updated successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to update book');
        }
    }

    public function destroy($id) {
        $user = $GLOBALS['auth_user'];
        
        // Check permissions (only admin can delete)
        if ($user['role'] !== 'admin') {
            Response::forbidden('Only administrators can delete books');
        }
        
        $book = Book::findById($id);
        
        if (!$book) {
            Response::notFound('Book not found');
        }
        
        try {
            Book::delete($id);
            
            // Log activity
            ActivityLog::log($user['id'], 'DELETE_BOOK', 'book', $id, "Deleted book: {$book['title']}");
            
            Response::success([], 'Book deleted successfully');
        } catch (\Exception $e) {
            Response::serverError('Failed to delete book');
        }
    }

    public function search() {
        $query = Request::get('q');
        
        if (!$query) {
            Response::error('Search query is required', 400);
        }
        
        $filters = [
            'category' => Request::get('category'),
            'available' => Request::get('available'),
            'limit' => Request::get('limit', 20)
        ];
        
        $books = Book::search($query, $filters);
        
        Response::success([
            'books' => $books,
            'total' => count($books),
            'query' => $query
        ]);
    }
}
