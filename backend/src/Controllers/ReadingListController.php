<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\ReadingList;

class ReadingListController
{
    private $readingListModel;

    public function __construct()
    {
        $this->readingListModel = new ReadingList();
    }

    /**
     * Get all reading lists for the authenticated user
     */
    public function index()
    {
        $user = $GLOBALS['auth_user'];
        
        $lists = $this->readingListModel->getUserLists($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => $lists
        ]);
    }

    /**
     * Get a specific reading list with books
     */
    public function show($id)
    {
        $user = $GLOBALS['auth_user'];
        
        $list = $this->readingListModel->getListWithBooks($id, $user['id']);
        
        if (!$list) {
            return Response::json([
                'success' => false,
                'message' => 'Reading list not found or access denied'
            ], 404);
        }

        return Response::json([
            'success' => true,
            'data' => $list
        ]);
    }

    /**
     * Create a new reading list
     */
    public function create()
    {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();

        // Validate input
        if (!isset($data['name']) || empty(trim($data['name']))) {
            return Response::json([
                'success' => false,
                'message' => 'List name is required'
            ], 400);
        }

        $listId = $this->readingListModel->create(
            $user['id'],
            $data['name'],
            $data['description'] ?? null,
            $data['is_public'] ?? false
        );

        if ($listId) {
            return Response::json([
                'success' => true,
                'message' => 'Reading list created successfully',
                'data' => [
                    'list_id' => $listId
                ]
            ], 201);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to create reading list'
        ], 500);
    }

    /**
     * Update a reading list
     */
    public function update($id)
    {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();

        $result = $this->readingListModel->update($id, $user['id'], $data);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Reading list updated successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to update reading list or access denied'
        ], 403);
    }

    /**
     * Delete a reading list
     */
    public function delete($id)
    {
        $user = $GLOBALS['auth_user'];

        $result = $this->readingListModel->delete($id, $user['id']);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Reading list deleted successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to delete reading list or access denied'
        ], 403);
    }

    /**
     * Add a book to a reading list
     */
    public function addBook($id)
    {
        $user = $GLOBALS['auth_user'];
        $data = Request::all();

        if (!isset($data['book_id'])) {
            return Response::json([
                'success' => false,
                'message' => 'Book ID is required'
            ], 400);
        }

        $result = $this->readingListModel->addBook(
            $id,
            $data['book_id'],
            $user['id'],
            $data['notes'] ?? null
        );

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Book added to reading list'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to add book (already in list or access denied)'
        ], 400);
    }

    /**
     * Remove a book from a reading list
     */
    public function removeBook($id, $bookId)
    {
        $user = $GLOBALS['auth_user'];

        $result = $this->readingListModel->removeBook($id, $bookId, $user['id']);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Book removed from reading list'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to remove book or access denied'
        ], 403);
    }

    /**
     * Get public reading lists
     */
    public function publicLists()
    {
        $limit = $_GET['limit'] ?? 20);
        $offset = $_GET['offset'] ?? 0);
        
        $lists = $this->readingListModel->getPublicLists($limit, $offset);
        
        return Response::json([
            'success' => true,
            'data' => $lists
        ]);
    }
}
