<?php
// Check authentication
require_once '../includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - AutoCare' : 'AutoCare Admin'; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>AutoCare Admin</h2>
            </div>
            <div class="hamburger" id="hamburgerMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <div class="nav-menu" id="navMenu">
                <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="tambah_service.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tambah_service.php' ? 'active' : ''; ?>">Tambah Servis</a>
                <a href="list_service.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'list_service.php' ? 'active' : ''; ?>">Daftar Servis</a>
                <a href="list_customer.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'list_customer.php' ? 'active' : ''; ?>">Daftar Customer</a>
                <a href="list_kendaraan.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'list_kendaraan.php' ? 'active' : ''; ?>">Daftar Kendaraan</a>
            </div>
            <div class="nav-user">
                <div class="user-info">
                    <a href="profile.php" class="user-name">👤 <?php echo htmlspecialchars($_SESSION['admin_name']); ?></a>
                    <a href="../auth/logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    
    <main class="main-content">
        <div class="container"><?php
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
}
?>