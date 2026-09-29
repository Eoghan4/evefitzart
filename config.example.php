<?php
// Copy this file to config.php and fill in your values.
// config.php is gitignored and must be created manually on the server.

define('ENVIRONMENT', 'development'); // 'production' or 'development'
define('DB_HOST', 'localhost');
define('DB_NAME', 'evefitzart');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('UPLOAD_DIR', '/var/www/evefitzart/uploads');
define('UPLOAD_URL_BASE', '/uploads');
define('MAX_FILE_SIZE', 50 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('SESSION_TIMEOUT', 3600);
?>
