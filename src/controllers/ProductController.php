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
        
        if ($search) {
            $products = $this->productModel->search($search);
        } elseif ($categoryId) {
            $products = $this->productModel->getByCategory($categoryId);
        } else {
            $products = $this->productModel->getActiveProducts();
        }
        
        $categories = $this->categoryModel->getActive();
        
        include __DIR__ . '/../views/products.php';
    }
    
    public function show() {
        if (!isset($_GET['id'])) {
            header('Location: index.php');
            exit();
        }
        
        $productId = intval($_GET['id']);
        $product = $this->productModel->findById($productId);
        
        if (!$product) {
            Session::setFlash('message', 'Product not found');
            header('Location: products.php');
            exit();
        }
        
        $inventory = $this->productModel->getInventory($productId);
        
        include __DIR__ . '/../views/product_detail.php';
    }
}