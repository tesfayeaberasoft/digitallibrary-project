<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Fine;

class FineController
{
    private $fineModel;

    public function __construct()
    {
        $this->fineModel = new Fine();
    }

    /**
     * Get all fines for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user;
        
        $fines = $this->fineModel->getUserFines($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => $fines
        ]);
    }

    /**
     * Get all fines (Admin/Librarian only)
     */
    public function all(Request $request)
    {
        $user = $request->user;
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $status = $request->query('status');
        $userId = $request->query('user_id');
        
        $fines = $this->fineModel->getAllFines($status, $userId);
        
        return Response::json([
            'success' => true,
            'data' => $fines
        ]);
    }

    /**
     * Get total unpaid fines for user
     */
    public function total(Request $request)
    {
        $user = $request->user;
        
        $total = $this->fineModel->getTotalUnpaid($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => [
                'total_unpaid' => $total
            ]
        ]);
    }

    /**
     * Pay a fine
     */
    public function pay(Request $request, $id)
    {
        $user = $request->user;
        $data = $request->body();

        // Validate payment data
        if (!isset($data['payment_method'])) {
            return Response::json([
                'success' => false,
                'message' => 'Payment method is required'
            ], 400);
        }

        // Verify fine belongs to user
        $fine = $this->fineModel->getById($id);
        
        if (!$fine) {
            return Response::json([
                'success' => false,
                'message' => 'Fine not found'
            ], 404);
        }

        if ($fine['user_id'] != $user['id']) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        if ($fine['status'] !== 'unpaid') {
            return Response::json([
                'success' => false,
                'message' => 'Fine is already ' . $fine['status']
            ], 400);
        }

        // Process payment
        $result = $this->fineModel->payFine(
            $id,
            $data['payment_method'],
            $data['payment_reference'] ?? null
        );

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Fine paid successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to process payment'
        ], 500);
    }

    /**
     * Waive a fine (Admin/Librarian only)
     */
    public function waive(Request $request, $id)
    {
        $user = $request->user;
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $fine = $this->fineModel->getById($id);
        
        if (!$fine) {
            return Response::json([
                'success' => false,
                'message' => 'Fine not found'
            ], 404);
        }

        if ($fine['status'] !== 'unpaid') {
            return Response::json([
                'success' => false,
                'message' => 'Fine is already ' . $fine['status']
            ], 400);
        }

        $result = $this->fineModel->waiveFine($id);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Fine waived successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to waive fine'
        ], 500);
    }

    /**
     * Get fine statistics (Admin/Librarian only)
     */
    public function statistics(Request $request)
    {
        $user = $request->user;
        
        if (!in_array($user['role'], ['admin', 'librarian'])) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $stats = $this->fineModel->getStatistics();
        
        return Response::json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
