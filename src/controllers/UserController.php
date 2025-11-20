<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/CSRF.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/FileUpload.php';

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
        
        // Validate user still exists in database
        Session::validateUserExists();
        
        $userId = Session::getUserId();
        $user = $this->userModel->findById($userId);
        $recentOrders = $this->orderModel->getCustomerOrders($userId);
        
        // Check if user has pending or incomplete orders
        $hasPendingOrders = $this->orderModel->hasPendingOrders($userId);
        
        // If user is admin, get admin count for deletion protection info
        $adminCount = 0;
        if ($user['role'] === 'admin') {
            $adminCount = $this->userModel->countAdmins();
        }
        
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
        
        // Validate user still exists in database
        Session::validateUserExists();
        
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postalCode = trim($_POST['postal_code'] ?? '');
        $country = trim($_POST['country'] ?? 'Philippines');
        
        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            // Get current user to get old profile picture path
            $user = $this->userModel->findById($userId);
            $oldImagePath = $user['profile_picture'] ? 'profiles/' . $user['profile_picture'] : null;
            
            $uploadResult = FileUpload::uploadUserProfile($_FILES['profile_picture'], $userId, $oldImagePath);
            
            if ($uploadResult['success']) {
                $filename = $uploadResult['filename'];
                $this->userModel->updateProfilePicture($userId, $filename);
            } else {
                Session::setFlash('message', $uploadResult['error']);
            }
        }
        
        // Update profile information
        if ($this->userModel->updateProfile($userId, $firstName, $lastName, $phone, $address, $city, $postalCode, $country)) {
            // Update session with new first name
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            
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
        
        // Validate user still exists in database
        Session::validateUserExists();
        
        $user = $this->userModel->findById($userId);
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            Session::setFlash('message', 'Current password is incorrect');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Validate new password
        if (strlen($newPassword) < 6) {
            Session::setFlash('message', 'New password must be at least 6 characters long');
            header('Location: index.php?page=profile');
            exit();
        }
        
        if ($newPassword !== $confirmPassword) {
            Session::setFlash('message', 'New passwords do not match');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Update password
        if ($this->userModel->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT))) {
            Session::setFlash('success', 'Password changed successfully');
        } else {
            Session::setFlash('message', 'Failed to change password');
        }
        
        header('Location: index.php?page=profile');
        exit();
    }
    
    public function deleteAccount() {
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
        $user = $this->userModel->findById($userId);
        $emailConfirm = $_POST['email_confirm'] ?? '';
        
        // Check if user is the last admin
        if ($user['role'] === 'admin') {
            $adminCount = $this->userModel->countAdmins();
            if ($adminCount <= 1) {
                Session::setFlash('message', 'Cannot delete your account. You are the only administrator in the system. At least one admin must remain to manage the system.');
                header('Location: index.php?page=profile');
                exit();
            }
        }
        
        // Check if user has pending or incomplete orders
        if ($this->orderModel->hasPendingOrders($userId)) {
            Session::setFlash('message', 'Cannot delete your account. You have active orders (pending, processing, or shipped). Please complete or cancel all orders before deleting your account.');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Verify email confirmation matches
        if ($emailConfirm !== $user['email']) {
            Session::setFlash('message', 'Email confirmation does not match. Account not deleted.');
            header('Location: index.php?page=profile');
            exit();
        }
        
        // Delete the account
        if ($this->userModel->delete($userId)) {
            // Clear session and redirect to home
            Session::destroy();
            Session::setFlash('success', 'Your account has been successfully deleted.');
            header('Location: index.php');
            exit();
        } else {
            Session::setFlash('message', 'Failed to delete account. Please try again.');
            header('Location: index.php?page=profile');
            exit();
        }
    }
}