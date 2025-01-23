<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Konfigurasi Database
require_once 'config.php';

// Response helper function
function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Validasi token (ganti dengan sistem autentikasi yang sesuai)
function validateToken() {
    $headers = apache_request_headers();
    $token = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    
    if (!$token) {
        sendResponse(false, 'Unauthorized: No token provided');
    }
    
    // Implement your token validation logic here
    // Return user_id if token is valid
    // For example:
    // return verifyJWTToken($token);
    
    return 1; // Dummy user_id for testing
}

// Validasi input password
function validatePasswordInput($password_lama, $password_baru, $konfirmasi_password) {
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        return ['valid' => false, 'message' => 'Semua field harus diisi'];
    }
    
    if ($password_baru !== $konfirmasi_password) {
        return ['valid' => false, 'message' => 'Password baru dan konfirmasi tidak cocok'];
    }
    
    if (strlen($password_baru) < 8) {
        return ['valid' => false, 'message' => 'Password baru minimal 8 karakter'];
    }
    
    // Tambahkan validasi kompleksitas password jika diperlukan
    return ['valid' => true];
}

// Main API logic
try {
    // Hanya menerima method POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(false, 'Method not allowed');
    }
    
    // Ambil JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['password_lama']) || !isset($input['password_baru']) || !isset($input['konfirmasi_password'])) {
        sendResponse(false, 'Invalid input format');
    }
    
    // Validasi token dan dapatkan user_id
    $user_id = validateToken();
    
    // Validasi input password
    $validation = validatePasswordInput(
        $input['password_lama'],
        $input['password_baru'],
        $input['konfirmasi_password']
    );
    
    if (!$validation['valid']) {
        sendResponse(false, $validation['message']);
    }
    
    // Koneksi database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verifikasi password lama
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($input['password_lama'], $user['password'])) {
        sendResponse(false, 'Password lama tidak sesuai');
    }
    
    // Update password baru
    $hash_password_baru = password_hash($input['password_baru'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash_password_baru, $user_id]);
    
    // Log perubahan password
    $stmt = $pdo->prepare("INSERT INTO password_change_logs (user_id, changed_at, ip_address) VALUES (?, NOW(), ?)");
    $stmt->execute([$user_id, $_SERVER['REMOTE_ADDR']]);
    
    sendResponse(true, 'Password berhasil diubah');
    
} catch (PDOException $e) {
    sendResponse(false, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage());
}
?>
