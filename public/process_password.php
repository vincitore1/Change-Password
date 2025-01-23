<?php
// Koneksi database
$host = 'localhost';
$dbname = 'system_deploy';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Koneksi gagal: " . $e->getMessage();
    exit;
}

// Fungsi untuk ganti password
function gantiPassword($user_id, $password_lama, $password_baru, $konfirmasi_password) {
    global $pdo;
    
    if(empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        return ["status" => false, "pesan" => "Semua field harus diisi"];
    }
    
    if($password_baru !== $konfirmasi_password) {
        return ["status" => false, "pesan" => "Password baru dan konfirmasi password tidak sama"];
    }
    
    if(strlen($password_baru) < 8) {
        return ["status" => false, "pesan" => "Password baru minimal 8 karakter"];
    }

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if(!password_verify($password_lama, $user['password'])) {
            return ["status" => false, "pesan" => "Password lama tidak sesuai"];
        }

        $hash_password_baru = password_hash($password_baru, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash_password_baru, $user_id]);

        return ["status" => true, "pesan" => "Password berhasil diubah"];

    } catch(PDOException $e) {
        return ["status" => false, "pesan" => "Terjadi kesalahan: " . $e->getMessage()];
    }
}

// Handler untuk form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = 1; // Sesuaikan dengan ID user yang sedang login
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    $hasil = gantiPassword($user_id, $password_lama, $password_baru, $konfirmasi_password);
    
    echo json_encode($hasil);
    exit;
}
?>