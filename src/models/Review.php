<?php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Order.php';
require_once __DIR__ . '/Product.php';

class Review extends BaseModel {
    protected $table = 'reviews';

    private $badWordsRegex;

    public function __construct() {
        parent::__construct();
        $this->initializeBadWordsRegex();
    }

    private function initializeBadWordsRegex() {
        // English bad words
        $englishBadWords = [
            'fuck', 'shit', 'damn', 'bitch', 'asshole', 'bastard', 'cunt', 'dick', 'pussy', 'ass', 'hell', 'damn', 'crap'
        ];
        
        // Tagalog bad words
        $tagalogBadWords = [
            'puta', 'gago', 'tangina', 'ulol', 'bobo', 'hayop', 'tanga', 'leche', 'yawa', 'siraulo', 'tarantado', 'burat'
        ];
        
        $allBadWords = array_merge($englishBadWords, $tagalogBadWords);
        $pattern = '/\b(' . implode('|', array_map('preg_quote', $allBadWords)) . ')\b/i';
        $this->badWordsRegex = $pattern;
    }

    public function validateComment($comment) {
        return true;
    }

    public function censorBadWords($text) {
        return preg_replace_callback($this->badWordsRegex, function($matches) {
            $word = $matches[0];
            $length = strlen($word);
            if ($length <= 2) {
                return str_repeat('*', $length);
            } elseif ($length === 3) {
                return $word[0] . '*' . $word[2];
            } else {
                return $word[0] . str_repeat('*', $length - 2) . $word[$length - 1];
            }
        }, $text);
    }

    public function create($orderItemId, $productId, $userId, $rating, $comment) {
        // Verify the order item exists, belongs to the user, is for the product, and hasn't been reviewed
        $sqlCheck = "SELECT oi.id 
                     FROM order_items oi
                     JOIN orders o ON oi.order_id = o.id
                     WHERE oi.id = ? 
                       AND oi.product_id = ? 
                       AND o.customer_id = ? 
                       AND o.order_status = 'completed'
                       AND oi.has_reviewed = FALSE";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        if ($stmtCheck === false) {
            ErrorHandler::log("Review create check prepare failed: " . $this->conn->error, 'ERROR');
            return false;
        }
        $stmtCheck->bind_param('iii', $orderItemId, $productId, $userId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        if ($resultCheck->num_rows === 0) {
            ErrorHandler::log("Review create blocked: order item {$orderItemId} not eligible for review by user {$userId} for product {$productId}", 'WARNING');
            $stmtCheck->close();
            return false;
        }
        $stmtCheck->close();

        // No longer validating bad words - allow all reviews but censor on display

        $sql = "INSERT INTO {$this->table} (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("Review create prepare failed: " . $this->conn->error, 'ERROR');
            return false;
        }
        $stmt->bind_param('iiis', $productId, $userId, $rating, $comment);
        $success = $stmt->execute();
        if (!$success) {
            ErrorHandler::log("Review create execute failed: " . $stmt->error, 'ERROR');
            $stmt->close();
            return false;
        }
        $newReviewId = $this->conn->insert_id;
        $stmt->close();

        // Mark the order item as reviewed
        $updateSql = "UPDATE order_items SET has_reviewed = TRUE WHERE id = ?";
        $updateStmt = $this->conn->prepare($updateSql);
        if ($updateStmt === false) {
            ErrorHandler::log("Review create update has_reviewed prepare failed: " . $this->conn->error, 'ERROR');
            // Don't return false here, as the review was created successfully
        } else {
            $updateStmt->bind_param('i', $orderItemId);
            $updateSuccess = $updateStmt->execute();
            if (!$updateSuccess) {
                ErrorHandler::log("Review create update has_reviewed execute failed: " . $updateStmt->error, 'ERROR');
            }
            $updateStmt->close();
        }

        return $newReviewId;
    }

    public function findById($reviewId) {
        $sql = "SELECT r.*, u.first_name, u.last_name FROM {$this->table} r JOIN users u ON r.user_id = u.id WHERE r.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $reviewId);
        $stmt->execute();
        $result = $stmt->get_result();
        $review = $result->fetch_assoc();
        if ($review) {
            $review['comment'] = $this->censorBadWords($review['comment']);
        }
        return $review;
    }
    
    public function hasUserReviewedOrderItem($orderItemId, $userId) {
        $sql = "SELECT COUNT(*) 
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.id = ? 
                  AND o.customer_id = ? 
                  AND oi.has_reviewed = TRUE";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("hasUserReviewedOrderItem prepare failed: " . $this->conn->error, 'ERROR');
            return false;
        }
        $stmt->bind_param('ii', $orderItemId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $stmt->close();
        return $row[0] > 0;
    }

    public function findByProductId($productId) {
        $sql = "SELECT r.*, u.first_name, u.last_name FROM {$this->table} r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($reviews as &$review) {
            $review['comment'] = $this->censorBadWords($review['comment']);
        }
        return $reviews;
    }

    public function getAverageRating($productId) {
        $sql = "SELECT AVG(rating) as average_rating, COUNT(id) as review_count FROM {$this->table} WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($reviewId, $userId, $rating, $comment) {
        $sql = "UPDATE {$this->table} SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("Review update prepare failed: " . $this->conn->error, 'ERROR');
            return false;
        }
        $stmt->bind_param('isii', $rating, $comment, $reviewId, $userId);
        $success = $stmt->execute();
        if (!$success) {
            ErrorHandler::log("Review update execute failed: " . $stmt->error, 'ERROR');
            $stmt->close();
            return false;
        }
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        if ($affectedRows === 0) {
            ErrorHandler::log("Review update failed: no rows affected. Review ID {$reviewId} may not exist or not belong to user {$userId}", 'WARNING');
            return false;
        }
        return true;
    }

    public function findByProductIdWithUserReviewFirst($productId, $userId) {
        $userReview = [];
        $otherReviews = [];

        // Fetch the logged-in user's review first
        $sqlUserReview = "SELECT r.*, u.first_name, u.last_name 
                          FROM {$this->table} r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.product_id = ? AND r.user_id = ?";
        $stmtUserReview = $this->conn->prepare($sqlUserReview);
        if ($stmtUserReview === false) {
            ErrorHandler::log("findByProductIdWithUserReviewFirst user review prepare failed: " . $this->conn->error, 'ERROR');
            return [];
        }
        $stmtUserReview->bind_param('ii', $productId, $userId);
        $stmtUserReview->execute();
        $resultUserReview = $stmtUserReview->get_result();
        if ($resultUserReview === false) {
            ErrorHandler::log("findByProductIdWithUserReviewFirst user review get_result failed: " . $stmtUserReview->error, 'ERROR');
            $stmtUserReview->close();
            return [];
        }
        $userReview = $resultUserReview->fetch_assoc();
        $stmtUserReview->close();

        // Fetch other reviews, excluding the logged-in user's review
        $sqlOtherReviews = "SELECT r.*, u.first_name, u.last_name 
                            FROM {$this->table} r 
                            JOIN users u ON r.user_id = u.id 
                            WHERE r.product_id = ? AND r.user_id != ? 
                            ORDER BY r.created_at DESC";
        $stmtOtherReviews = $this->conn->prepare($sqlOtherReviews);
        if ($stmtOtherReviews === false) {
            ErrorHandler::log("findByProductIdWithUserReviewFirst other reviews prepare failed: " . $this->conn->error, 'ERROR');
            return [];
        }
        $stmtOtherReviews->bind_param('ii', $productId, $userId);
        $stmtOtherReviews->execute();
        $resultOtherReviews = $stmtOtherReviews->get_result();
        if ($resultOtherReviews === false) {
            ErrorHandler::log("findByProductIdWithUserReviewFirst other reviews get_result failed: " . $stmtOtherReviews->error, 'ERROR');
            $stmtOtherReviews->close();
            return [];
        }
        $otherReviews = $resultOtherReviews->fetch_all(MYSQLI_ASSOC);
        $stmtOtherReviews->close();

        // Combine them, with the user's review first if it exists
        $allReviews = [];
        if ($userReview) {
            $userReview['comment'] = $this->censorBadWords($userReview['comment']);
            $allReviews[] = $userReview;
        }
        foreach ($otherReviews as &$review) {
            $review['comment'] = $this->censorBadWords($review['comment']);
        }
        $allReviews = array_merge($allReviews, $otherReviews);

        return $allReviews;
    }

    public function addAdminReply($reviewId, $adminReply) {
        $sql = "UPDATE {$this->table} SET admin_reply = ?, admin_reply_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("addAdminReply prepare failed: " . $this->conn->error, 'ERROR');
            return false;
        }
        $stmt->bind_param('si', $adminReply, $reviewId);
        $success = $stmt->execute();
        if (!$success) {
            ErrorHandler::log("addAdminReply execute failed: " . $stmt->error, 'ERROR');
        }
        $stmt->close();
        return $success;
    }

    public function getReviewsForAdmin($limit = 10, $offset = 0, $search = null, $productId = null) {
        $sql = "SELECT r.*, p.product_name, u.first_name, u.last_name, u.email 
                FROM {$this->table} r
                JOIN products p ON r.product_id = p.id
                JOIN users u ON r.user_id = u.id";
        
        $conditions = [];
        $params = [];
        $types = '';

        if ($search) {
            $conditions[] = "(p.product_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR r.comment LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'sssss';
        }

        if ($productId) {
            $conditions[] = "r.product_id = ?";
            $params[] = $productId;
            $types .= 'i';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limit;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("getReviewsForAdmin prepare failed: " . $this->conn->error, 'ERROR');
            return [];
        }
        
        // Dynamically bind parameters
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $reviews;
    }

    public function countReviewsForAdmin($search = null, $productId = null) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} r
                JOIN products p ON r.product_id = p.id
                JOIN users u ON r.user_id = u.id";
        
        $conditions = [];
        $params = [];
        $types = '';

        if ($search) {
            $conditions[] = "(p.product_name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR r.comment LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'sssss';
        }

        if ($productId) {
            $conditions[] = "r.product_id = ?";
            $params[] = $productId;
            $types .= 'i';
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            ErrorHandler::log("countReviewsForAdmin prepare failed: " . $this->conn->error, 'ERROR');
            return 0;
        }
        
        // Dynamically bind parameters
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['total'];
    }
}
