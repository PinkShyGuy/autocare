<?php
// Database Configuration
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'autocare_db');
// define('DB_USER', 'root');
// define('DB_PASS', '');

// class Database {
//     private $host = DB_HOST;
//     private $db_name = DB_NAME;
//     private $username = DB_USER;
//     private $password = DB_PASS;
//     private $conn = null;

//     public function getConnection() {
//         if ($this->conn === null) {
//             try {
//                 $this->conn = new PDO(
//                     "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
//                     $this->username,
//                     $this->password
//                 );
//                 $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                 $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//                 $this->conn->exec("SET NAMES utf8");
//             } catch(PDOException $e) {
//                 die("Connection failed: " . $e->getMessage());
//             }
//         }
//     }
// }

class Database {
    // 1. Deklarasikan properti tanpa nilai awal
    private $host;
    private $user;
    private $pass;
    private $db;
    private $port;
    private $conn = null;

    // 2. Buat constructor untuk mengisi nilai properti
    public function __construct() {
        $this->host = getenv('MYSQLHOST');
        $this->user = getenv('MYSQLUSER');
        $this->pass = getenv('MYSQLPASSWORD');
        $this->db   = getenv('MYSQL_DATABASE');
        $this->port = getenv('MYSQLPORT');
    }

    // 3. Method untuk membuat dan mengembalikan koneksi
    public function getConnection() {
        // Hanya buat koneksi baru jika belum ada
        if ($this->conn === null) {
            try {
                // Gunakan properti yang sudah diisi oleh constructor
                $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db;
                $this->conn = new PDO($dsn, $this->user, $this->pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Hentikan aplikasi jika koneksi gagal
                die("Koneksi Database Gagal: " . $e->getMessage());
            }
        }
        return $this->conn;
    }
}

// Functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function getStatusBadge($status) {
    $badges = [
        'Dalam Antrian' => 'badge-warning',
        'Dikerjakan' => 'badge-info',
        'Selesai' => 'badge-success'
    ];
    
    $class = isset($badges[$status]) ? $badges[$status] : 'badge-secondary';
    return "<span class='badge {$class}'>{$status}</span>";
}
?>