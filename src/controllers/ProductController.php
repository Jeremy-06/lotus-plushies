<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Review.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';

class ProductController {
    
    private $productModel;
    private $categoryModel;
    private $reviewModel;
    private $orderModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->reviewModel = new Review();
        $this->orderModel = new Order();
    }
    
    public function index() {
        $categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        
        $itemsPerPage = 9;
        $currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        if ($search) {
            $products = $this->productModel->searchPaginated($search, $itemsPerPage, $offset);
            $totalProducts = $this->productModel->countSearch($search);
        } elseif ($categoryId) {
            $products = $this->productModel->getByCategoryPaginated($categoryId, $itemsPerPage, $offset);
            $totalProducts = $this->productModel->countByCategory($categoryId);
        } else {
            $products = $this->productModel->getActiveProductsPaginated($itemsPerPage, $offset);
            $totalProducts = $this->productModel->countActiveProducts();
        }

        if (!empty($products)) {
            foreach ($products as $idx => $prod) {
                $stats = $this->reviewModel->getAverageRating($prod['id']);
                $products[$idx]['average_rating'] = isset($stats['average_rating']) ? floatval($stats['average_rating']) : 0.0;
                $products[$idx]['review_count'] = isset($stats['review_count']) ? intval($stats['review_count']) : 0;
            }
        }
        
        $totalPages = ceil($totalProducts / $itemsPerPage);
        
        $categories = $this->categoryModel->getActive();
        
        include __DIR__ . '/../views/products.php';
    }
    
                public function show() {
    
                    if (!isset($_GET['id'])) {
    
                        header('Location: index.php');
    
                        exit();
    
                    }
    
                    
    
                    $productId = intval($_GET['id']);
    
                    $product = $this->productModel->findByIdWithDetails($productId);
    
    
                    if (!$product) {
    
                        Session::setFlash('message', 'Product not found');
    
                        header('Location: index.php?page=products');
    
                        exit();
    
                    }
    
    
                    $inventory = $this->productModel->getInventory($productId);
    
                    $productImages = $this->productModel->getProductImages($productId);
    
                    
    
                    $userId = Session::getUserId();
    
                    $orderItemId = isset($_GET['order_item_id']) ? intval($_GET['order_item_id']) : null;
    
                    $showReviewForm = isset($_GET['review']) && $_GET['review'] === 'true';
    
                    
                    $reviews = [];
    
                    if ($userId) {
    
                        $reviews = $this->reviewModel->findByProductIdWithUserReviewFirst($productId, $userId);
    
                    } else {
    
                        $reviews = $this->reviewModel->findByProductId($productId);
    
                    }
    
    
                    $canReviewOrderItem = false;
    
                    if ($userId && $orderItemId && $showReviewForm) {
    
                        $specificOrderItem = $this->orderModel->getOrderItemById($orderItemId);
    
            
    
                        if (isset($_GET['debug_controller']) && $_GET['debug_controller'] == '1') {
    
                            echo "<pre>ProductController::show Debug:\n";
    
                            echo "  userId: " . ($userId ?? 'null') . "\n";
    
                            echo "  orderItemId: " . ($orderItemId ?? 'null') . "\n";
    
                            echo "  showReviewForm: " . ($showReviewForm ? 'true' : 'false') . "\n";
    
                            echo "  specificOrderItem: " . print_r($specificOrderItem, true) . "\n";
    
                            if ($specificOrderItem) {
    
                                echo "  specificOrderItem['product_id']: " . ($specificOrderItem['product_id'] ?? 'null') . "\n";
    
                                echo "  product['id']: " . ($product['id'] ?? 'null') . "\n";
    
                                echo "  specificOrderItem['customer_id']: " . ($specificOrderItem['customer_id'] ?? 'null') . "\n";
    
                                echo "  specificOrderItem['order_status']: " . ($specificOrderItem['order_status'] ?? 'null') . "\n";
    
                                echo "  specificOrderItem['has_reviewed']: " . ($specificOrderItem['has_reviewed'] ?? 'null') . "\n";
    
                            }
    
                            echo "</pre>";
    
                            exit();
    
                        }
    
                        
    
                        if ($specificOrderItem && 
    
                            $specificOrderItem['product_id'] == $productId && 
    
                            $specificOrderItem['customer_id'] == $userId && 
    
                            $specificOrderItem['order_status'] === 'completed' && 
    
                            !$specificOrderItem['has_reviewed']) {
    
                            $canReviewOrderItem = true;
    
                        }
    
                    }
    
              
                    $hasReviewedProduct = false;
                    $hasPurchasedProduct = false;
    
                    if ($userId) {
                  
                        foreach ($reviews as $review) {
                            if ($review['user_id'] == $userId) {
                                $hasReviewedProduct = true;
                                break;
                            }
                        }
    
       
                        $hasPurchasedProduct = $this->orderModel->hasUserPurchasedProduct($userId, $productId);
                    }
    
                    $totalSold = $this->productModel->getTotalSold($productId);
    
                    include __DIR__ . '/../views/product_detail.php';                }

    public function addReview() {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'], $_POST['order_item_id'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request.']);
                exit();
            }
            header('Location: index.php');
            exit();
        }

        if (!CSRF::validateToken($_POST['csrf_token'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
                exit();
            }
            Session::setFlash('error', 'Invalid CSRF token.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $productId = intval($_POST['product_id']);
        $orderItemId = intval($_POST['order_item_id']);
        $userId = Session::getUserId();
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);

        if (!$userId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You must be logged in to leave a review.']);
                exit();
            }
            Session::setFlash('error', 'You must be logged in to leave a review.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        if ($rating < 1 || $rating > 5) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5.']);
                exit();
            }
            Session::setFlash('error', 'Please select a rating between 1 and 5.');
            header('Location: index.php?page=product&id=' . $productId . '&review=true&order_item_id=' . $orderItemId);
            exit();
        }

        if ($this->reviewModel->hasUserReviewedOrderItem($orderItemId, $userId)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'You have already reviewed this specific purchase.']);
                exit();
            }
            Session::setFlash('error', 'You have already reviewed this specific purchase.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        $newReviewId = $this->reviewModel->create($orderItemId, $productId, $userId, $rating, $comment);

        if ($newReviewId) {
            $stats = $this->reviewModel->getAverageRating($productId);
            $this->productModel->updateRating($productId, $stats['average_rating'], $stats['review_count']);
            
            if ($isAjax) {
                $review = $this->reviewModel->findById($newReviewId);
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'review' => $review]);
                exit();
            }
            
            Session::setFlash('success', 'Thank you for your review!');
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'There was an error submitting your review.']);
                exit();
            }
            Session::setFlash('error', 'There was an error submitting your review. Please try again.');
        }

        header('Location: index.php?page=product&id=' . $productId);
        exit();
    }

    public function editReview() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['review_id'], $_POST['product_id'])) {
            header('Location: index.php');
            exit();
        }

        if (!CSRF::validateToken($_POST['csrf_token'])) {
            Session::setFlash('error', 'Invalid CSRF token.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $reviewId = intval($_POST['review_id']);
        $productId = intval($_POST['product_id']);
        $userId = Session::get('user_id');
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);

        if (!$userId) {
            Session::setFlash('error', 'You must be logged in to edit a review.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        if ($rating < 1 || $rating > 5) {
            Session::setFlash('error', 'Please select a rating between 1 and 5.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        if ($this->reviewModel->update($reviewId, $userId, $rating, $comment)) {
            // Update product's average rating and review count
            $stats = $this->reviewModel->getAverageRating($productId);
            $this->productModel->updateRating($productId, $stats['average_rating'], $stats['review_count']);
            
            Session::setFlash('success', 'Your review has been updated!');
        } else {
            Session::setFlash('error', 'There was an error updating your review. Please try again.');
        }

        header('Location: index.php?page=product&id=' . $productId);
        exit();
    }

    public function adminReplyReview() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['review_id'], $_POST['product_id'])) {
            header('Location: index.php');
            exit();
        }

        if (!CSRF::validateToken($_POST['csrf_token'])) {
            Session::setFlash('error', 'Invalid CSRF token.');
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        $reviewId = intval($_POST['review_id']);
        $productId = intval($_POST['product_id']);
        $adminReply = trim($_POST['admin_reply']);

        // Check if user is admin
        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Access denied. Admin privileges required.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        if (empty($adminReply)) {
            Session::setFlash('error', 'Reply cannot be empty.');
            header('Location: index.php?page=product&id=' . $productId);
            exit();
        }

        if ($this->reviewModel->addAdminReply($reviewId, $adminReply)) {
            Session::setFlash('success', 'Admin reply added successfully.');
        } else {
            Session::setFlash('error', 'Failed to add admin reply.');
        }

        header('Location: index.php?page=product&id=' . $productId);
        exit();
    }
}