<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'system_deploy');
define('DB_USER', 'root');
define('DB_PASS', '');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (set false in production)
ini_set('display_errors', true);
error_reporting(E_ALL);
?>
