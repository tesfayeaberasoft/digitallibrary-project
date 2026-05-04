<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ReadingList
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new reading list
     */
    public function create($userId, $name, $description = null, $isPublic = false)
    {
        $stmt = $this->db->prepare("
            INSERT INTO reading_lists (user_id, name, description, is_public)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([$userId, $name, $description, $isPublic ? 1 : 0]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all reading lists for a user
     */
    public function getUserLists($userId)
    {
        $stmt = $this->db->prepare("
            SELECT rl.*, 
                   COUNT(rli.id) as book_count
            FROM reading_lists rl
            LEFT JOIN reading_list_items rli ON rl.id = rli.reading_list_id
            WHERE rl.user_id = ?
            GROUP BY rl.id
            ORDER BY rl.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific reading list with books
     */
    public function getListWithBooks($listId, $userId = null)
    {
        // Get list details
        $stmt = $this->db->prepare("
            SELECT * FROM reading_lists 
            WHERE id = ? AND (user_id = ? OR is_public = 1)
        ");
        
        $stmt->execute([$listId, $userId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$list) {
            return null;
        }

        // Get books in the list
        $stmt = $this->db->prepare("
            SELECT b.*, rli.added_date, rli.notes, rli.id as list_item_id
            FROM reading_list_items rli
            JOIN books b ON rli.book_id = b.id
            WHERE rli.reading_list_id = ?
            ORDER BY rli.added_date DESC
        ");
        
        $stmt->execute([$listId]);
        $list['books'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $list;
    }

    /**
     * Add a book to a reading list
     */
    public function addBook($listId, $bookId, $userId, $notes = null)
    {
        // Verify list ownership
        $stmt = $this->db->prepare("SELECT user_id FROM reading_lists WHERE id = ?");
        $stmt->execute([$listId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$list || $list['user_id'] != $userId) {
            return false;
        }

        // Check if book already in list
        $stmt = $this->db->prepare("
            SELECT id FROM reading_list_items 
            WHERE reading_list_id = ? AND book_id = ?
        ");
        $stmt->execute([$listId, $bookId]);
        
        if ($stmt->fetch()) {
            return false; // Already in list
        }

        // Add book to list
        $stmt = $this->db->prepare("
            INSERT INTO reading_list_items (reading_list_id, book_id, notes)
            VALUES (?, ?, ?)
        ");
        
        return $stmt->execute([$listId, $bookId, $notes]);
    }

    /**
     * Remove a book from a reading list
     */
    public function removeBook($listId, $bookId, $userId)
    {
        // Verify list ownership
        $stmt = $this->db->prepare("SELECT user_id FROM reading_lists WHERE id = ?");
        $stmt->execute([$listId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$list || $list['user_id'] != $userId) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM reading_list_items 
            WHERE reading_list_id = ? AND book_id = ?
        ");
        
        return $stmt->execute([$listId, $bookId]);
    }

    /**
     * Update a reading list
     */
    public function update($listId, $userId, $data)
    {
        // Verify ownership
        $stmt = $this->db->prepare("SELECT user_id FROM reading_lists WHERE id = ?");
        $stmt->execute([$listId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$list || $list['user_id'] != $userId) {
            return false;
        }

        $fields = [];
        $values = [];
        
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
        }
        
        if (isset($data['is_public'])) {
            $fields[] = "is_public = ?";
            $values[] = $data['is_public'] ? 1 : 0;
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $listId;
        
        $stmt = $this->db->prepare("
            UPDATE reading_lists 
            SET " . implode(', ', $fields) . "
            WHERE id = ?
        ");
        
        return $stmt->execute($values);
    }

    /**
     * Delete a reading list
     */
    public function delete($listId, $userId)
    {
        // Verify ownership
        $stmt = $this->db->prepare("SELECT user_id FROM reading_lists WHERE id = ?");
        $stmt->execute([$listId]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$list || $list['user_id'] != $userId) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM reading_lists WHERE id = ?");
        return $stmt->execute([$listId]);
    }

    /**
     * Get public reading lists
     */
    public function getPublicLists($limit = 20, $offset = 0)
    {
        $stmt = $this->db->prepare("
            SELECT rl.*, 
                   u.name as creator_name,
                   COUNT(rli.id) as book_count
            FROM reading_lists rl
            JOIN users u ON rl.user_id = u.id
            LEFT JOIN reading_list_items rli ON rl.id = rli.reading_list_id
            WHERE rl.is_public = 1
            GROUP BY rl.id
            ORDER BY rl.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
