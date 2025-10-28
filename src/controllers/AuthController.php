<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Session.php';
require_once __DIR__ . '/../helpers/Validation.php';
require_once __DIR__ . '/../helpers/CSRF.php';

class AuthController {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function showLogin() {
        if (Session::isLoggedIn()) {
            header('Location: index.php');
            exit();
        }
        include __DIR__ . '/../views/login.php';
    }
    
    public function login() {
        if (!isset($_POST['submit'])) {
            header('Location: login.php');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: login.php');
            exit();
        }
        
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        
        // Validation
        $validator = new Validation();
        $validator->required('email', $email)
                  ->email('email', $email)
                  ->required('password', $password);
        
        if ($validator->hasErrors()) {
            foreach ($validator->getErrors() as $field => $error) {
                Session::setFlash($field . 'Error', $error);
            }
            Session::set('email', $email);
            header('Location: login.php');
            exit();
        }
        
        // Verify credentials
        $user = $this->userModel->verifyPassword($email, $password);
        
        if ($user) {
            Session::set('user_id', $user['id']);
            Session::set('email', $user['email']);
            Session::set('role', $user['role']);
            Session::setFlash('success', 'Login successful');
            
            if ($user['role'] === 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: index.php');
            }
            exit();
        } else {
            Session::setFlash('message', 'Invalid email or password');
            Session::set('email', $email);
            header('Location: login.php');
            exit();
        }
    }
    
    public function showRegister() {
        if (Session::isLoggedIn()) {
            header('Location: index.php');
            exit();
        }
        include __DIR__ . '/../views/register.php';
    }
    
    public function register() {
        if (!isset($_POST['submit'])) {
            header('Location: register.php');
            exit();
        }
        
        // Validate CSRF token
        if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
            Session::setFlash('message', 'Invalid request');
            header('Location: register.php');
            exit();
        }
        
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);
        
        // Validation
        $validator = new Validation();
        $validator->required('email', $email)
                  ->email('email', $email)
                  ->required('password', $password)
                  ->minLength('password', $password, 6)
                  ->required('confirm_password', $confirmPassword)
                  ->match('confirm_password', $confirmPassword, $password, 'Passwords do not match');
        
        if ($validator->hasErrors()) {
            foreach ($validator->getErrors() as $field => $error) {
                Session::setFlash($field . 'Error', $error);
            }
            Session::set('email', $email);
            header('Location: register.php');
            exit();
        }
        
        // Check if email already exists
        if ($this->userModel->emailExists($email)) {
            Session::setFlash('emailError', 'Email already registered');
            Session::set('email', $email);
            header('Location: register.php');
            exit();
        }
        
        // Create user
        $userId = $this->userModel->create($email, $password);
        
        if ($userId) {
            Session::set('user_id', $userId);
            Session::set('email', $email);
            Session::set('role', 'customer');
            Session::setFlash('success', 'Registration successful');
            header('Location: index.php');
            exit();
        } else {
            Session::setFlash('message', 'Registration failed. Please try again');
            header('Location: register.php');
            exit();
        }
    }
    
    public function logout() {
        Session::destroy();
        session_start();
        Session::setFlash('success', 'Logged out successfully');
        header('Location: index.php');
        exit();
    }
}