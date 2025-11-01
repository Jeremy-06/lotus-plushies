<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
require_once __DIR__ . '/../helpers/Validation.php';

class UserController {
    
    private $userModel;
    private $orderModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->orderModel = new Order();
    }
    
    public function profile() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login to view profile');
            header('Location: index.php?page=login');
            exit();
        }
        
        $userId = Session::getUserId();
        $user = $this->userModel->findById($userId);
        $recentOrders = $this->orderModel->getCustomerOrders($userId);
        
        include __DIR__ . '/../views/profile.php';
    }
    
    public function updateProfile() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login first');
            header('Location: index.php?page=login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=profile');
            exit();
        }
        
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: index.php?page=profile');
            exit();
        }
        
        $userId = Session::getUserId();
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postalCode = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Philippines');
        
        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validate file type
            if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) {
                Session::setFlash('message', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
                header('Location: index.php?page=profile');
                exit();
            }
            
            // Validate file size
            if ($_FILES['profile_picture']['size'] > $maxSize) {
                Session::setFlash('message', 'File too large. Maximum size is 5MB.');
                header('Location: index.php?page=profile');
                exit();
            }
            
            // Get current user to delete old profile picture
            $user = $this->userModel->findById($userId);
            if (!empty($user['profile_picture']) && file_exists($uploadDir . $user['profile_picture'])) {
                unlink($uploadDir . $user['profile_picture']);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $filename)) {
                $this->userModel->updateProfilePicture($userId, $filename);
            } else {
                Session::setFlash('message', 'Failed to upload profile picture');
                header('Location: index.php?page=profile');
                exit();
            }
        }
        
        if ($this->userModel->updateProfile($userId, $firstName, $lastName, $phone, $address, $city, $postalCode, $country)) {
            // Update session with new first name
            Session::set('first_name', $firstName);
            Session::setFlash('success', 'Profile updated successfully');
        } else {
            Session::setFlash('message', 'Failed to update profile');
        }
        
        header('Location: index.php?page=profile');
        exit();
    }
    
    public function changePassword() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('message', 'Please login first');
            header('Location: index.php?page=login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=profile');
            exit();
        }
        
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: index.php?page=profile');
            exit();
        }
        
        $userId = Session::getUserId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Get user to verify current password
        $user = $this->userModel->findById($userId);
        
        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::setFlash('message', 'Current password is incorrect');
            header('Location: index.php?page=profile');
            exit();
        }
        
        if ($newPassword !== $confirmPassword) {
            Session::setFlash('message', 'New passwords do not match');
            header('Location: index.php?page=profile');
            exit();
        }
        
        if (strlen($newPassword) < 6) {
            Session::setFlash('message', 'Password must be at least 6 characters');
            header('Location: index.php?page=profile');
            exit();
        }
        
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            Session::setFlash('success', 'Password changed successfully');
        } else {
            Session::setFlash('message', 'Failed to change password');
        }
        
        header('Location: index.php?page=profile');
        exit();
    }
}