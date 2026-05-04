<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Review;

class ReviewController
{
    private $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new Review();
    }

    /**
     * Get all reviews for a book
     */
    public function bookReviews(Request $request, $bookId)
    {
        $limit = $request->query('limit', 20);
        $offset = $request->query('offset', 0);
        
        $reviews = $this->reviewModel->getBookReviews($bookId, $limit, $offset);
        
        return Response::json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Get all reviews by a user
     */
    public function userReviews(Request $request)
    {
        $user = $request->user;
        
        $reviews = $this->reviewModel->getUserReviews($user['id']);
        
        return Response::json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Create a review
     */
    public function create(Request $request, $bookId)
    {
        $user = $request->user;
        $data = $request->body();

        // Validate input
        if (!isset($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return Response::json([
                'success' => false,
                'message' => 'Rating must be between 1 and 5'
            ], 400);
        }

        // Check if user already reviewed this book
        $existingReview = $this->reviewModel->getUserBookReview($user['id'], $bookId);
        
        if ($existingReview) {
            return Response::json([
                'success' => false,
                'message' => 'You have already reviewed this book'
            ], 400);
        }

        $reviewId = $this->reviewModel->create(
            $bookId,
            $user['id'],
            $data['rating'],
            $data['review_text'] ?? null
        );

        if ($reviewId) {
            return Response::json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => [
                    'review_id' => $reviewId
                ]
            ], 201);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to create review'
        ], 500);
    }

    /**
     * Update a review
     */
    public function update(Request $request, $id)
    {
        $user = $request->user;
        $data = $request->body();

        // Verify review belongs to user
        $review = $this->reviewModel->getById($id);
        
        if (!$review) {
            return Response::json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        if ($review['user_id'] != $user['id']) {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Validate rating if provided
        if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
            return Response::json([
                'success' => false,
                'message' => 'Rating must be between 1 and 5'
            ], 400);
        }

        $result = $this->reviewModel->update($id, $data);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Review updated successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to update review'
        ], 500);
    }

    /**
     * Delete a review
     */
    public function delete(Request $request, $id)
    {
        $user = $request->user;

        // Verify review belongs to user or user is admin
        $review = $this->reviewModel->getById($id);
        
        if (!$review) {
            return Response::json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        if ($review['user_id'] != $user['id'] && $user['role'] !== 'admin') {
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $result = $this->reviewModel->delete($id);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to delete review'
        ], 500);
    }

    /**
     * Mark a review as helpful
     */
    public function markHelpful(Request $request, $id)
    {
        $review = $this->reviewModel->getById($id);
        
        if (!$review) {
            return Response::json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $result = $this->reviewModel->incrementHelpful($id);

        if ($result) {
            return Response::json([
                'success' => true,
                'message' => 'Review marked as helpful'
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Failed to update review'
        ], 500);
    }

    /**
     * Get review statistics for a book
     */
    public function bookStatistics(Request $request, $bookId)
    {
        $stats = $this->reviewModel->getBookStatistics($bookId);
        
        return Response::json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
