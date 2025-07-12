<?php
session_start();
require_once '../includes/config.php';

$page_title = 'Tambah Servis';
$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Sanitize input data
        $customer_name = sanitize($_POST['customer_name']);
        $customer_email = sanitize($_POST['customer_email']);
        $customer_phone = sanitize($_POST['customer_phone']);
        $license_plate = strtoupper(sanitize($_POST['license_plate']));
        $brand = sanitize($_POST['brand']);
        $model = sanitize($_POST['model']);
        $year = (int)$_POST['year'];
        $service_type = sanitize($_POST['service_type']);
        $complaint = sanitize($_POST['complaint']);
        $entry_date = $_POST['entry_date'];
        $status = sanitize($_POST['status']);
        
        // Check if customer exists by email
        $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$customer_email]);
        $existingCustomer = $stmt->fetch();
        
        if ($existingCustomer) {
            $customer_id = $existingCustomer['id'];
            
            // Update customer data if exists
            $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$customer_name, $customer_phone, $customer_id]);
        } else {
            // Insert new customer
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
            $stmt->execute([$customer_name, $customer_email, $customer_phone]);
            $customer_id = $conn->lastInsertId();
        }
        
        // Check if vehicle exists
        $stmt = $conn->prepare("SELECT id FROM vehicles WHERE license_plate = ?");
        $stmt->execute([$license_plate]);
        $existingVehicle = $stmt->fetch();
        
        if ($existingVehicle) {
            $vehicle_id = $existingVehicle['id'];
            
            // Update vehicle data if exists
            $stmt = $conn->prepare("UPDATE vehicles SET customer_id = ?, brand = ?, model = ?, year = ? WHERE id = ?");
            $stmt->execute([$customer_id, $brand, $model, $year, $vehicle_id]);
        } else {
            // Insert new vehicle
            $stmt = $conn->prepare("INSERT INTO vehicles (customer_id, license_plate, brand, model, year) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$customer_id, $license_plate, $brand, $model, $year]);
            $vehicle_id = $conn->lastInsertId();
        }
        
        // Insert service record
        $stmt = $conn->prepare("INSERT INTO services (customer_id, vehicle_id, service_type, complaint, entry_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $vehicle_id, $service_type, $complaint, $entry_date, $status]);
        
        $conn->commit();
        $_SESSION['success'] = 'Data servis berhasil ditambahkan!';
        header('Location: list_service.php');
        exit;
        
    } catch(PDOException $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
}

include '../includes/header.php';
?>

<h1 class="page-title">Tambah Servis Baru</h1>

<div class="card">
    <form id="serviceForm" method="POST" action="">
        <h3 class="card-title">Data Customer</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="customer_name" class="form-label">Nama Customer *</label>
                <input type="text" id="customer_name" name="customer_name" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="customer_email" class="form-label">Email Customer *</label>
                <input type="email" id="customer_email" name="customer_email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="customer_phone" class="form-label">Nomor HP *</label>
                <input type="tel" id="customer_phone" name="customer_phone" class="form-input" required>
            </div>
        </div>
        
        <h3 class="card-title">Data Kendaraan</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="license_plate" class="form-label">Nomor Polisi *</label>
                <input type="text" id="license_plate" name="license_plate" class="form-input" placeholder="B1234CD" required>
            </div>
            <div class="form-group">
                <label for="brand" class="form-label">Merek *</label>
                <input type="text" id="brand" name="brand" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="model" class="form-label">Model *</label>
                <input type="text" id="model" name="model" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="year" class="form-label">Tahun *</label>
                <input type="number" id="year" name="year" class="form-input" min="1990" max="<?php echo date('Y'); ?>" required>
            </div>
        </div>
        
        <h3 class="card-title">Data Servis</h3>
        <div class="form-row">
            <div class="form-group">
                <label for="service_type" class="form-label">Jenis Servis *</label>
                <select id="service_type" name="service_type" class="form-select" required>
                    <option value="">Pilih Jenis Servis</option>
                    <option value="Service Rutin">Service Rutin</option>
                    <option value="Ganti Oli">Ganti Oli</option>
                    <option value="Perbaikan Mesin">Perbaikan Mesin</option>
                    <option value="Perbaikan AC">Perbaikan AC</option>
                    <option value="Service Rem">Service Rem</option>
                    <option value="Tune Up">Tune Up</option>
                    <option value="Spooring Balancing">Spooring Balancing</option>
                    <option value="Ganti Ban">Ganti Ban</option>
                    <option value="Perbaikan Body">Perbaikan Body</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="entry_date" class="form-label">Tanggal Masuk *</label>
                <input type="date" id="entry_date" name="entry_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="Dalam Antrian" selected>Dalam Antrian</option>
                    <option value="Dikerjakan">Dikerjakan</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="complaint" class="form-label">Keluhan</label>
            <textarea id="complaint" name="complaint" class="form-textarea" placeholder="Deskripsikan keluhan atau catatan tambahan..."></textarea>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">Simpan Data Servis</button>
            <a href="list_service.php" class="btn btn-secondary" style="margin-left: 1rem;">Batal</a>
        </div>
    </form>
</div>

<script>
// Auto-suggestions for common brands
const commonBrands = ['Toyota', 'Honda', 'Suzuki', 'Daihatsu', 'Mitsubishi', 'Nissan', 'Mazda', 'Isuzu', 'Hyundai', 'KIA'];

document.addEventListener('DOMContentLoaded', function() {
    initAutoSuggestion('brand', commonBrands);
    
    // License plate formatting
    const licensePlateInput = document.getElementById('license_plate');
    licensePlateInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
    
    // Year validation
    const yearInput = document.getElementById('year');
    yearInput.addEventListener('input', function() {
        const year = parseInt(this.value);
        const currentYear = new Date().getFullYear();
        if (year > currentYear) {
            this.value = currentYear;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>