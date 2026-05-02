<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Book {
    public static function create($data) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO books (isbn, title, author, category, publisher, publication_year, 
                             edition, pages, language, description, cover_image, 
                             total_copies, available_copies, location, status)
            VALUES (:isbn, :title, :author, :category, :publisher, :publication_year,
                    :edition, :pages, :language, :description, :cover_image,
                    :total_copies, :available_copies, :location, :status)
        ");
        
        $stmt->execute([
            'isbn' => $data['isbn'],
            'title' => $data['title'],
            'author' => $data['author'],
            'category' => $data['category'] ?? null,
            'publisher' => $data['publisher'] ?? null,
            'publication_year' => $data['publication_year'] ?? null,
            'edition' => $data['edition'] ?? null,
            'pages' => $data['pages'] ?? null,
            'language' => $data['language'] ?? 'English',
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'total_copies' => $data['total_copies'] ?? 1,
            'available_copies' => $data['available_copies'] ?? $data['total_copies'] ?? 1,
            'location' => $data['location'] ?? null,
            'status' => 'active'
        ]);
        
        return $db->lastInsertId();
    }

    public static function findById($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByIsbn($isbn) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM books WHERE isbn = ?");
        $stmt->execute([$isbn]);
        return $stmt->fetch();
    }

    public static function update($id, $data) {
        $db = Database::getInstance()->getConnection();
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "{$key} = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        
        $sql = "UPDATE books SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }

    public static function delete($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function all($filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM books WHERE 1=1";
        $params = [];
        
        if (isset($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['available'])) {
            $sql .= " AND available_copies > 0";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        if (isset($filters['offset'])) {
            $sql .= " OFFSET ?";
            $params[] = (int)$filters['offset'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function search($query, $filters = []) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM books WHERE (
            title LIKE ? OR 
            author LIKE ? OR 
            isbn LIKE ? OR 
            description LIKE ?
        )";
        
        $searchTerm = "%{$query}%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        
        if (isset($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }
        
        if (isset($filters['available'])) {
            $sql .= " AND available_copies > 0";
        }
        
        $sql .= " AND status = 'active' ORDER BY title ASC";
        
        if (isset($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateAvailability($id, $increment = false) {
        $db = Database::getInstance()->getConnection();
        
        if ($increment) {
            $stmt = $db->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
        } else {
            $stmt = $db->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
        }
        
        return $stmt->execute([$id]);
    }

    public static function getCategories() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
