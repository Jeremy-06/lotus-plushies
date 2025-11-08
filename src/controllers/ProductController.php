<?php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../helpers/Session.php';

class ProductController {
    
    private $productModel;
    private $categoryModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }
    
    public function index() {
        $categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        
        // Pagination settings
        $itemsPerPage = 9; // 3x3 grid
        $currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        // Get products and total count based on filters
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
        
        // Calculate total pages
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
        
        include __DIR__ . '/../views/product_detail.php';
    }
}