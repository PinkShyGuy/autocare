<?php
// Authentication functions

function checkAuth() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_username'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function isLoggedIn() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function login($username, $password, $conn) {
    try {
        $stmt = $conn->prepare("SELECT id, username, password, full_name, email FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && $password === $admin['password']) {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['email'];
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

function logout() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    session_destroy();
    header('Location: login.php');
    exit;
}

function registerAdmin($username, $password, $full_name, $email, $conn) {
    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Username atau email sudah digunakan'];
        }
        
        // Insert new admin
        $stmt = $conn->prepare("INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$username, $password, $full_name, $email]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Admin berhasil didaftarkan'];
        } else {
            return ['success' => false, 'message' => 'Gagal mendaftarkan admin'];
        }
        
    } catch(PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function getAdminInfo($admin_id, $conn) {
    try {
        $stmt = $conn->prepare("SELECT username, full_name, email, created_at FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}
?>