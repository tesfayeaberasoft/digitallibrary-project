<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Review {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO reviews (book_id, user_id, rating, review_text, is_approved)
            VALUES (:book_id, :user_id, :rating, :review_text, :is_approved)
        ");
        
        $stmt->execute([
            'book_id' => $data['book_id'],
            'user_id' => $data['user_id'],
            'rating' => $data['rating'],
            'review_text' => $data['review_text'] ?? null,
            'is_approved' => $data['is_approved'] ?? true
        ]);
        
        $reviewId = $db->lastInsertId();
        
        // Update book average rating
        self::updateBookRating($data['book_id']);
        
        return $reviewId;
    }

    public static function update($id, $data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE reviews
            SET rating = ?, review_text = ?
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $data['rating'],
            $data['review_text'] ?? null,
            $id
        ]);
        
        if ($result) {
            // Get book_id and update rating
            $review = self::findById($id);
            self::updateBookRating($review['book_id']);
        }
        
        return $result;
    }

    public static function findById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getBookReviews($bookId, $approved = true) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT r.*, u.first_name, u.last_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.book_id = ?
        ";
        
        $params = [$bookId];
        
        if ($approved) {
            $sql .= " AND r.is_approved = TRUE";
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getUserReviews($userId) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT r.*, b.title, b.author, b.cover_image
            FROM reviews r
            JOIN books b ON r.book_id = b.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        
        // Get book_id before deleting
        $review = self::findById($id);
        $bookId = $review['book_id'];
        
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            self::updateBookRating($bookId);
        }
        
        return $result;
    }

    public static function updateBookRating($bookId) {
        $db = Database::getInstance()->getConnection();
        
        // Calculate average rating
        $stmt = $db->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
            FROM reviews
            WHERE book_id = ? AND is_approved = TRUE
        ");
        $stmt->execute([$bookId]);
        $result = $stmt->fetch();
        
        // Update book table
        $updateStmt = $db->prepare("
            UPDATE books
            SET average_rating = ?, total_reviews = ?
            WHERE id = ?
        ");
        
        return $updateStmt->execute([
            round($result['avg_rating'], 2),
            $result['total_reviews'],
            $bookId
        ]);
    }

    public static function hasUserReviewed($userId, $bookId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE user_id = ? AND book_id = ?
        ");
        $stmt->execute([$userId, $bookId]);
        return $stmt->fetchColumn() > 0;
    }

    public static function incrementHelpful($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE reviews SET helpful_count = helpful_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
