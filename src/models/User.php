<?php

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    
    protected $table = 'users';
    
    public function create($email, $password, $role = 'customer') {
        try {
            $email = strtolower($email);
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $sql = "INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt === false) {
                ErrorHandler::log("User create prepare failed: " . mysqli_error($this->conn), 'ERROR');
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, 'sss', $email, $passwordHash, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                return mysqli_insert_id($this->conn);
            }
            
            ErrorHandler::log("User create failed: " . mysqli_error($this->conn), 'ERROR', ['email' => $email]);
            return false;
        } catch (Exception $e) {
            ErrorHandler::log("User create exception: " . $e->getMessage(), 'ERROR', ['email' => $email]);
            return false;
        }
    }
    
    public function findByEmail($email) {
        try {
            // Case-insensitive email match
            $sql = "SELECT * FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1";
            $stmt = mysqli_prepare($this->conn, $sql);
            
            if ($stmt === false) {
                ErrorHandler::log("findByEmail prepare failed: " . mysqli_error($this->conn), 'ERROR');
                return null;
            }
            
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            return mysqli_fetch_assoc($result);
        } catch (Exception $e) {
            ErrorHandler::log("findByEmail exception: " . $e->getMessage(), 'ERROR', ['email' => $email]);
            return null;
        }
    }
    
    public function verifyPassword($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                return $user;
            }
            return false;
        } catch (Exception $e) {
            ErrorHandler::log("verifyPassword exception: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    public function emailExists($email) {
        $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt) > 0;
    }
    
    public function getAllCustomers() {
        $sql = "SELECT id, email, role, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getAllUsers() {
        $sql = "SELECT id, email, role, created_at FROM users ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function searchAndSortUsers($search = '', $sortBy = 'created_at', $sortOrder = 'DESC', $roleFilter = '') {
        try {
            // Validate sort column
            $allowedColumns = ['id', 'email', 'role', 'created_at', 'first_name', 'last_name'];
            if (!in_array($sortBy, $allowedColumns)) {
                $sortBy = 'created_at';
            }
            
            // Validate sort order
            $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
            
            $sql = "SELECT id, email, role, first_name, last_name, phone, city, created_at FROM users WHERE 1=1";
            $params = [];
            $types = '';
            
            // Add role filter
            if (!empty($roleFilter) && in_array($roleFilter, ['admin', 'customer'])) {
                $sql .= " AND role = ?";
                $params[] = $roleFilter;
                $types .= 's';
            }
            
            // Add search filter
            if (!empty($search)) {
                $sql .= " AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'ssss';
            }
            
            $sql .= " ORDER BY $sortBy $sortOrder";
            
            if (!empty($params)) {
                $stmt = mysqli_prepare($this->conn, $sql);
                
                if ($stmt === false) {
                    ErrorHandler::log("searchAndSortUsers prepare failed: " . mysqli_error($this->conn), 'ERROR');
                    return [];
                }
                
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            } else {
                $result = mysqli_query($this->conn, $sql);
                
                if ($result === false) {
                    ErrorHandler::log("searchAndSortUsers query failed: " . mysqli_error($this->conn), 'ERROR');
                    return [];
                }
                
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
        } catch (Exception $e) {
            ErrorHandler::log("searchAndSortUsers exception: " . $e->getMessage(), 'ERROR');
            return [];
        }
    }

    public function updateRole(int $userId, string $role): bool {
        $role = $role === 'admin' ? 'admin' : 'customer';
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $role, $userId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function updateProfile($userId, $firstName, $lastName, $phone, $address, $city, $postalCode, $country) {
        $sql = "UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                phone = ?, 
                address = ?, 
                city = ?, 
                postal_code = ?, 
                country = ? 
                WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'sssssssi', $firstName, $lastName, $phone, $address, $city, $postalCode, $country, $userId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function updateProfilePicture($userId, $profilePicture) {
        $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $profilePicture, $userId);
        return mysqli_stmt_execute($stmt);
    }
    
    public function updatePassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $passwordHash, $userId);
        return mysqli_stmt_execute($stmt);
    }
}
