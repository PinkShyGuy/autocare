<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Daftar Servis';
$db = new Database();
$conn = $db->getConnection();

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['service_id'])) {
    try {
        $service_id = (int)$_POST['service_id'];
        $new_status = sanitize($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE services SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $service_id]);
        
        $_SESSION['success'] = 'Status servis berhasil diperbarui!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

// Handle delete service
if (isset($_POST['delete_service']) && isset($_POST['service_id'])) {
    try {
        $service_id = (int)$_POST['service_id'];
        
        // Get service info for confirmation message
        $stmt = $conn->prepare("
            SELECT s.service_type, c.name as customer_name, v.license_plate
            FROM services s 
            JOIN customers c ON s.customer_id = c.id 
            JOIN vehicles v ON s.vehicle_id = v.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$service_id]);
        $service_info = $stmt->fetch();
        
        if ($service_info) {
            // Delete the service
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$service_id]);
            
            $_SESSION['success'] = "Data servis {$service_info['service_type']} untuk {$service_info['customer_name']} ({$service_info['license_plate']}) berhasil dihapus!";
        } else {
            $_SESSION['error'] = 'Data servis tidak ditemukan!';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

        // Get all services with customer and vehicle data
try {
    $stmt = $conn->prepare("
        SELECT s.*, c.name as customer_name, c.email, c.phone,
               v.license_plate, v.brand, v.model, v.year
        FROM services s 
        JOIN customers c ON s.customer_id = c.id 
        JOIN vehicles v ON s.vehicle_id = v.id 
        ORDER BY s.entry_date DESC, s.created_at DESC
    ");
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<h1 class="page-title">Daftar Servis</h1>

<div class="card">
    <div class="search-filter-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari customer, kendaraan, atau jenis servis..." class="form-input">
        </div>
        <div class="filter-select">
            <select id="statusFilter" class="form-select">
                <option value="">Semua Status</option>
                <option value="Dalam Antrian">Dalam Antrian</option>
                <option value="Dikerjakan">Dikerjakan</option>
                <option value="Selesai">Selesai</option>
            </select>
        </div>
        <div>
            <a href="tambah_service.php" class="btn btn-primary">Tambah Servis</a>
        </div>
    </div>
    
    <?php if (empty($services)): ?>
        <p class="no-results">Belum ada data servis</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table" id="servicesTable">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Kendaraan</th>
                        <th>Jenis Servis</th>
                        <th>Tanggal Masuk</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($service['customer_name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($service['email']); ?></small><br>
                                    <small><?php echo htmlspecialchars($service['phone']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($service['license_plate']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($service['brand'] . ' ' . $service['model'] . ' (' . $service['year'] . ')'); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($service['service_type']); ?></strong>
                                    <?php if ($service['complaint']): ?>
                                        <br><small style="color: #666;"><?php echo htmlspecialchars($service['complaint']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo formatDate($service['entry_date']); ?></td>
                            <td><?php echo getStatusBadge($service['status']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <select name="status" class="form-select btn-small" onchange="this.form.submit()">
                                            <option value="Dalam Antrian" <?php echo $service['status'] == 'Dalam Antrian' ? 'selected' : ''; ?>>Dalam Antrian</option>
                                            <option value="Dikerjakan" <?php echo $service['status'] == 'Dikerjakan' ? 'selected' : ''; ?>>Dikerjakan</option>
                                            <option value="Selesai" <?php echo $service['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                    
                                    <!-- Simple delete form for testing -->
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus servis <?php echo htmlspecialchars($service['service_type']); ?> untuk <?php echo htmlspecialchars($service['customer_name']); ?> (<?php echo htmlspecialchars($service['license_plate']); ?>)?')">
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <input type="hidden" name="delete_service" value="1">
                                        <button type="submit" class="btn btn-danger btn-small">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
            Total: <?php echo count($services); ?> servis
        </div>
    <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3 class="modal-title">⚠️ Konfirmasi Hapus</h3>
        <p class="modal-text" id="deleteModalText">Apakah Anda yakin ingin menghapus data servis ini?</p>
        <div class="modal-buttons">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="service_id" id="deleteServiceId">
                <input type="hidden" name="delete_service" value="1">
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('List service page loaded');
    
    // Check if elements exist
    const searchInput = document.getElementById('searchInput');
    const servicesTable = document.getElementById('servicesTable');
    const statusFilter = document.getElementById('statusFilter');
    
    console.log('Search input found:', !!searchInput);
    console.log('Services table found:', !!servicesTable);
    console.log('Status filter found:', !!statusFilter);
    
    if (searchInput && servicesTable) {
        console.log('Initializing live search manually...');
        
        const tbody = servicesTable.querySelector('tbody');
        if (!tbody) {
            console.error('Table tbody not found!');
            return;
        }
        
        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
        console.log('Found data rows:', rows.length);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            console.log('Searching for:', searchTerm);
            
            let visibleCount = 0;
            
            rows.forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                let found = false;
                
                // Search in all cells
                for (let i = 0; i < cells.length - 1; i++) { // Exclude last column (Actions)
                    if (cells[i].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                if (found || searchTerm === '') {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            console.log('Visible rows after search:', visibleCount);
            
            // Handle no results
            let noResultsRow = tbody.querySelector('.no-results-row');
            if (noResultsRow) {
                noResultsRow.remove();
            }
            
            if (visibleCount === 0 && searchTerm !== '') {
                const colCount = rows.length > 0 ? rows[0].querySelectorAll('td').length : 6;
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `<td colspan="${colCount}" style="text-align: center; padding: 2rem; color: #666; font-style: italic;">Tidak ada data yang ditemukan untuk "${searchTerm}"</td>`;
                tbody.appendChild(noResultsRow);
            }
        });
    }
    
    // Status filter
    if (statusFilter && servicesTable) {
        console.log('Initializing status filter manually...');
        
        const tbody = servicesTable.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(.no-results-row)'));
        
        statusFilter.addEventListener('change', function() {
            const filterValue = this.value.trim();
            console.log('Filtering by status:', filterValue);
            
            let visibleCount = 0;
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells[4]) { // Status column (index 4)
                    const statusCell = cells[4];
                    const badgeElement = statusCell.querySelector('.badge');
                    const statusText = badgeElement ? badgeElement.textContent.trim() : statusCell.textContent.trim();
                    
                    if (filterValue === '' || statusText === filterValue) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
            
            console.log('Visible rows after filter:', visibleCount);
            
            // Handle no results for filter
            let noResultsRow = tbody.querySelector('.no-results-row');
            if (noResultsRow) {
                noResultsRow.remove();
            }
            
            if (visibleCount === 0 && filterValue !== '') {
                const colCount = rows.length > 0 ? rows[0].querySelectorAll('td').length : 6;
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `<td colspan="${colCount}" style="text-align: center; padding: 2rem; color: #666; font-style: italic;">Tidak ada data dengan status "${filterValue}"</td>`;
                tbody.appendChild(noResultsRow);
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>