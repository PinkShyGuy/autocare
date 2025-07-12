<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Daftar Customer';
$db = new Database();
$conn = $db->getConnection();

// Get all customers with their vehicle and service counts
try {
    $stmt = $conn->prepare("
        SELECT c.*,
               COUNT(DISTINCT v.id) as vehicle_count,
               COUNT(DISTINCT s.id) as service_count,
               MAX(s.entry_date) as last_service_date
        FROM customers c 
        LEFT JOIN vehicles v ON c.id = v.customer_id
        LEFT JOIN services s ON c.id = s.customer_id
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $stmt->execute();
    $customers = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

include '../includes/header.php';
?>

<h1 class="page-title">Daftar Customer</h1>

<div class="card">
    <div class="search-filter-container">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Cari nama atau email customer..." class="form-input">
        </div>
        <div>
            <a href="tambah_service.php" class="btn btn-primary">Tambah Customer Baru</a>
        </div>
    </div>
    
    <?php if (empty($customers)): ?>
        <p class="no-results">Belum ada data customer</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table" id="customersTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Nomor HP</th>
                        <th>Jumlah Kendaraan</th>
                        <th>Total Servis</th>
                        <th>Servis Terakhir</th>
                        <th>Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $customer['vehicle_count']; ?> unit</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?php echo $customer['service_count']; ?> kali</span>
                            </td>
                            <td>
                                <?php if ($customer['last_service_date']): ?>
                                    <?php echo formatDate($customer['last_service_date']); ?>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">Belum pernah</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($customer['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
            Total: <?php echo count($customers); ?> customer
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom search for customers - search in name and email columns
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('customersTable');
    
    if (searchInput && table) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const name = cells[0] ? cells[0].textContent.toLowerCase() : '';
                const email = cells[1] ? cells[1].textContent.toLowerCase() : '';
                const phone = cells[2] ? cells[2].textContent.toLowerCase() : '';

                const found = name.includes(searchTerm) || email.includes(searchTerm) || phone.includes(searchTerm);
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
                noResultsRow.innerHTML = '<td colspan="7" class="no-results">Tidak ada customer yang ditemukan</td>';
                tbody.appendChild(noResultsRow);
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>