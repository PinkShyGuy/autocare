<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

$page_title = 'Profile Admin';
$db = new Database();
$conn = $db->getConnection();

// Get current admin info
$admin_info = getAdminInfo($_SESSION['admin_id'], $conn);

if (!$admin_info) {
    $_SESSION['error'] = 'Gagal mengambil informasi admin';
    header('Location: dashboard.php');
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Semua field password harus diisi';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Konfirmasi password baru tidak cocok';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'Password baru minimal 6 karakter';
    } else {
        try {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $current_admin = $stmt->fetch();
            
            if ($current_admin && $current_password === $current_admin['password']) {
                // Update password
                $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $_SESSION['admin_id']]);
                
                $_SESSION['success'] = 'Password berhasil diubah';
            } else {
                $_SESSION['error'] = 'Password saat ini salah';
            }
        } catch(PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    }
    
    header('Location: profile.php');
    exit;
}

include '../includes/header.php';
?>

<h1 class="page-title">Profile Admin</h1>

<div class="form-row">
    <div class="card">
        <h2 class="card-title">Informasi Admin</h2>
        
        <div class="profile-info">
            <div class="info-item">
                <strong>Nama Lengkap:</strong>
                <span><?php echo htmlspecialchars($admin_info['full_name']); ?></span>
            </div>
            
            <div class="info-item">
                <strong>Username:</strong>
                <span><?php echo htmlspecialchars($admin_info['username']); ?></span>
            </div>
            
            <div class="info-item">
                <strong>Email:</strong>
                <span><?php echo htmlspecialchars($admin_info['email']); ?></span>
            </div>
            
            <div class="info-item">
                <strong>Terdaftar Sejak:</strong>
                <span><?php echo formatDate($admin_info['created_at']); ?></span>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2 class="card-title">Ubah Password</h2>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" id="new_password" name="new_password" class="form-input" required>
                <small style="color: #666; font-size: 0.875rem;">Minimal 6 karakter</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>
            
            <button type="submit" name="change_password" class="btn btn-primary">Ubah Password</button>
        </form>
    </div>
</div>

<style>
.profile-info {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background-color: #f8f9fa;
    border-radius: 5px;
    border-left: 3px solid #667eea;
}

.info-item strong {
    color: #2c3e50;
    font-weight: 600;
    min-width: 150px;
}

.info-item span {
    color: #495057;
    text-align: right;
}

@media (max-width: 768px) {
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .info-item strong {
        min-width: auto;
    }
    
    .info-item span {
        text-align: left;
    }
}
</style>

<?php include '../includes/footer.php'; ?>