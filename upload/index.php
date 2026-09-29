<?php
require '../db.php';

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
}

session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: ../login/');
    exit;
}

function validateImageFile($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        $error = $errorMessages[$file['error']] ?? 'Unknown upload error';
        return ['valid' => false, 'error' => $error];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file extension'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'File is not a valid image'];
    }

    return ['valid' => true, 'error' => null];
}

function saveImageLocally($file, $uploadDir, $uploadUrlBase) {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid('', true) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return ['success' => false, 'url' => null, 'error' => 'Could not create uploads directory'];
        }
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'url' => null, 'error' => 'Failed to save image to server'];
    }

    return ['success' => true, 'url' => $uploadUrlBase . '/' . $filename, 'error' => null];
}

$message = '';
$messageType = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid security token. Please try again.";
    } else {
        $title    = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $medium   = trim($_POST['medium'] ?? '');

        if (empty($title)) {
            $message = "Please provide an image title.";
        } elseif (empty($category)) {
            $message = "Please provide a category.";
        } elseif (strlen($title) > 255) {
            $message = "Title is too long (max 255 characters).";
        } elseif (strlen($category) > 100) {
            $message = "Category is too long (max 100 characters).";
        } elseif (strlen($medium) > 100) {
            $message = "Medium is too long (max 100 characters).";
        } elseif (!isset($_FILES['image'])) {
            $message = "Please select an image to upload.";
        } else {
            $validation = validateImageFile($_FILES['image']);

            if (!$validation['valid']) {
                $message = $validation['error'];
            } else {
                $uploadResult = saveImageLocally($_FILES['image'], UPLOAD_DIR, UPLOAD_URL_BASE);

                if ($uploadResult['success']) {
                    try {
                        $stmt = $conn->prepare("INSERT INTO images (title, category, medium, url) VALUES (?, ?, ?, ?)");
                        $success = $stmt->execute([$title, $category, $medium, $uploadResult['url']]);

                        if ($success) {
                            $message = "Upload successful! Image added to gallery.";
                            $messageType = 'success';
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        } else {
                            $message = "Database error: Failed to save image information.";
                        }
                    } catch (PDOException $e) {
                        $message = "Database error: " . $e->getMessage();
                        error_log("Database error in upload: " . $e->getMessage());
                    }
                } else {
                    $message = $uploadResult['error'];
                }
            }
        }
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Eve Fitz Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: black;
            color: #e7e1e1;
            font-family: 'Georgia', serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a { text-decoration: none; color: inherit; }

        @keyframes heartbeat {
            0%   { transform: scale(1); }
            20%  { transform: scale(1.2); }
            40%  { transform: scale(0.9); }
            60%  { transform: scale(1); }
            100% { transform: scale(1); }
        }

        /* ── Nav ── */
        .site-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .nav-logo {
            font-family: 'Loved by the King', cursive;
            font-size: 1.6rem;
            color: #e7e1e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-heart {
            width: 28px; height: 28px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .nav-links { display: flex; gap: 2rem; }

        .nav-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-family: 'Georgia', serif;
            font-size: 0.95rem;
            letter-spacing: 0.05em;
            transition: color 0.2s;
        }

        .nav-link:hover { color: white; }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }

        .hamburger span {
            display: block;
            width: 24px; height: 2px;
            background: white;
            border-radius: 2px;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            inset: 0;
            background: black;
            z-index: 600;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3rem;
        }

        .mobile-menu.open { display: flex; }

        .mobile-link {
            font-family: 'Loved by the King', cursive;
            font-size: 2.5rem;
            color: #e7e1e1;
            text-decoration: none;
        }

        .mobile-close {
            position: absolute;
            top: 1.5rem; right: 1.5rem;
            background: none;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }

        @media (max-width: 640px) {
            .nav-links { display: none; }
            .hamburger { display: flex; }
            .site-nav { padding: 1rem 1.25rem; }
        }

        /* ── Upload form ── */
        .upload-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 6rem 1.5rem 5rem;
        }

        .upload-heading {
            font-family: 'Loved by the King', cursive;
            font-size: clamp(2rem, 5vw, 3rem);
            color: #e7e1e1;
            margin-bottom: 0.4rem;
        }

        .user-info {
            color: rgba(255,255,255,0.35);
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            margin-bottom: 2.5rem;
        }

        .upload-card {
            width: 100%;
            max-width: 540px;
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 8px;
            padding: 2.5rem;
            background: rgba(255,255,255,0.02);
        }

        .success-msg {
            background: rgba(40,167,69,0.12);
            border: 1px solid rgba(40,167,69,0.35);
            color: #80e898;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
        }

        .error-msg {
            background: rgba(200,50,50,0.12);
            border: 1px solid rgba(200,50,50,0.35);
            color: #ff9999;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
        }

        .form-group { margin-bottom: 1.5rem; }

        label {
            display: block;
            font-size: 0.8rem;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 6px;
            color: white;
            font-family: 'Georgia', serif;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: rgba(255,255,255,0.45);
        }

        input[type="file"] { cursor: pointer; padding: 0.65rem 0.8rem; }

        small {
            display: block;
            color: rgba(255,255,255,0.3);
            font-size: 0.8rem;
            margin-top: 0.4rem;
            letter-spacing: 0.03em;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
            flex-wrap: wrap;
        }

        button[type="submit"] {
            flex: 1;
            min-width: 120px;
            padding: 0.9rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.3rem;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.25s, border-color 0.25s;
        }

        button[type="submit"]:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.5);
        }

        button[type="submit"]:disabled { opacity: 0.45; cursor: not-allowed; }

        .logout-btn {
            padding: 0.9rem 1.5rem;
            background: transparent;
            border: 1px solid rgba(200,50,50,0.3);
            color: rgba(255,150,150,0.7);
            font-family: 'Loved by the King', cursive;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.25s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .logout-btn:hover {
            border-color: rgba(200,50,50,0.7);
            color: #ff9999;
        }

        footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 2rem;
            text-align: center;
            color: rgba(255,255,255,0.3);
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    <nav class="site-nav">
        <a href="../" class="nav-logo">
            Eve Fitz Art
            <img src="../pictures/one heart.png" class="logo-heart" alt="">
        </a>
        <div class="nav-links">
            <a href="../" class="nav-link">Home</a>
            <a href="../gallery/" class="nav-link">Gallery</a>
            <a href="../admin/" class="nav-link">Dashboard</a>
        </div>
        <button class="hamburger" aria-label="Menu" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" onclick="toggleMenu()">&times;</button>
        <a href="../" class="mobile-link">Home</a>
        <a href="../gallery/" class="mobile-link">Gallery</a>
        <a href="../admin/" class="mobile-link">Dashboard</a>
    </div>

    <div class="upload-wrap">
        <h1 class="upload-heading">Upload</h1>
        <p class="user-info"><?= htmlspecialchars($_SESSION['email']) ?></p>

        <div class="upload-card">
            <?php if ($message): ?>
                <div class="<?= $messageType === 'success' ? 'success-msg' : 'error-msg' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_FILE_SIZE ?>">

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required placeholder="Image title" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" required placeholder="e.g. portrait, landscape" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="medium">Medium</label>
                    <input type="text" id="medium" name="medium" placeholder="e.g. oil on canvas, watercolour, digital" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    <small>JPG, PNG, GIF, WEBP &middot; Max <?= MAX_FILE_SIZE / 1024 / 1024 ?>MB</small>
                </div>

                <div class="form-buttons">
                    <button type="submit" id="submitBtn">Upload</button>
                    <a href="../logout/" class="logout-btn">Logout</a>
                </div>
            </form>
        </div>
    </div>

    <footer>&copy; 2026 Eve Fitz Art</footer>

    <script>
        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('open');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('open') ? 'hidden' : '';
        }

        const MAX_FILE_SIZE = <?= MAX_FILE_SIZE ?>;
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const title    = document.getElementById('title').value.trim();
            const category = document.getElementById('category').value.trim();
            const image    = document.getElementById('image').files[0];

            if (!title || !category || !image) {
                e.preventDefault();
                alert('Please fill in all fields and select an image.');
                return;
            }

            if (!ALLOWED_TYPES.includes(image.type)) {
                e.preventDefault();
                alert('Invalid file type. Please select a JPG, PNG, GIF, or WEBP image.');
                return;
            }

            if (image.size > MAX_FILE_SIZE) {
                e.preventDefault();
                alert('File too large. Max size: ' + (MAX_FILE_SIZE / 1024 / 1024) + 'MB');
                return;
            }

            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Uploading...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
