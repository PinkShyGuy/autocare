<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Daftar Kendaraan';
$db = new Database();
$conn = $db->getConnection();

// Get all vehicles with their owner and service information
try {
    $stmt = $conn->prepare("
        SELECT v.*, c.name as owner_name, c.email, c.phone,
               COUNT(s.id) as service_count,
               MAX(s.entry_date) as last_service_date,
               (SELECT COUNT(*) FROM services WHERE vehicle_id = v.id AND status IN ('Dalam Antrian', 'Dikerjakan')) as active_services
        FROM vehicles v 
        JOIN customers c ON v.customer_id = c.id
        LEFT JOIN services s ON v.id = s.vehicle_id
        GROUP BY v.id
        ORDER BY v.license_plate ASC
    ");
    $stmt->execute();
    $vehicles = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<h1 class="page-title">Daftar Kendaraan</h1>

<div class="card">
    <div class="search-filter-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari nomor polisi atau nama pemilik..." class="form-input">
        </div>
        <div>
            <a href="tambah_service.php" class="btn btn-primary">Tambah Kendaraan Baru</a>
        </div>
    </div>
    
    <?php if (empty($vehicles)): ?>
        <p class="no-results">Belum ada data kendaraan</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table" id="vehiclesTable">
                <thead>
                    <tr>
                        <th>Nomor Polisi</th>
                        <th>Pemilik</th>
                        <th>Kendaraan</th>
                        <th>Tahun</th>
                        <th>Total Servis</th>
                        <th>Servis Aktif</th>
                        <th>Servis Terakhir</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td>
                                <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($vehicle['license_plate']); ?></strong>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($vehicle['owner_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($vehicle['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($vehicle['phone']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($vehicle['brand']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($vehicle['model']); ?></small>
                                </div>
                            </td>
                            <td><?php echo $vehicle['year']; ?></td>
                            <td>
                                <span class="badge badge-secondary"><?php echo $vehicle['service_count']; ?> kali</span>
                            </td>
                            <td>
                                <?php if ($vehicle['active_services'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $vehicle['active_services']; ?> aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($vehicle['last_service_date']): ?>
                                    <?php echo formatDate($vehicle['last_service_date']); ?>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">Belum pernah</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($vehicle['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
            Total: <?php echo count($vehicles); ?> kendaraan
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom search for vehicles - search in license plate and owner name columns
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('vehiclesTable');
    
    if (searchInput && table) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const licensePlate = cells[0] ? cells[0].textContent.toLowerCase() : '';
                const ownerName = cells[1] ? cells[1].textContent.toLowerCase() : '';
                const model = cells[2] ? cells[2].textContent.toLowerCase() : '';
                
                const found = licensePlate.includes(searchTerm) || ownerName.includes(searchTerm) || model.includes(searchTerm);
                row.style.display = found ? '' : 'none';
            });
            
            // Show/hide no results message
            const visibleRows = rows.filter(row => row.style.display !== 'none');
            
            // Remove existing no-results row
            const existingNoResults = tbody.querySelector('.no-results-row');
            if (existingNoResults) {
                existingNoResults.remove();
            }
            
            if (visibleRows.length === 0 && searchTerm !== '') {
                const noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = '<td colspan="8" class="no-results">Tidak ada kendaraan yang ditemukan</td>';
                tbody.appendChild(noResultsRow);
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>