<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Dashboard';
$db = new Database();
$conn = $db->getConnection();

// Get statistics
try {
    // Total customers
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM customers");
    $stmt->execute();
    $totalCustomers = $stmt->fetch()['total'];
    
    // Total vehicles
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vehicles");
    $stmt->execute();
    $totalVehicles = $stmt->fetch()['total'];
    
    // Active services (Dalam Antrian + Dikerjakan)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM services WHERE status IN ('Dalam Antrian', 'Dikerjakan')");
    $stmt->execute();
    $activeServices = $stmt->fetch()['total'];
    
    // Recent services (last 5)
    $stmt = $conn->prepare("
        SELECT s.*, c.name as customer_name, v.license_plate, v.brand, v.model 
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        JOIN vehicles v ON s.vehicle_id = v.id 
        ORDER BY s.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentServices = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<h1 class="page-title">Dashboard Admin</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo $totalCustomers; ?></div>
        <div class="stat-label">Total Customer</div>
    </div>
    
    <div class="stat-card warning">
        <div class="stat-number"><?php echo $totalVehicles; ?></div>
        <div class="stat-label">Total Kendaraan</div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-number"><?php echo $activeServices; ?></div>
        <div class="stat-label">Servis Aktif</div>
    </div>
</div>

<div class="card">
    <h2 class="card-title">Servis Terbaru</h2>
    
    <?php if (empty($recentServices)): ?>
        <p class="no-results">Belum ada data servis</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Kendaraan</th>
                        <th>Jenis Servis</th>
                        <th>Tanggal Masuk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentServices as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($service['license_plate'] . ' - ' . $service['brand'] . ' ' . $service['model']); ?></td>
                            <td><?php echo htmlspecialchars($service['service_type']); ?></td>
                            <td><?php echo formatDate($service['entry_date']); ?></td>
                            <td><?php echo getStatusBadge($service['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 1rem;">
            <a href="list_service.php" class="btn btn-primary">Lihat Semua Servis</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>