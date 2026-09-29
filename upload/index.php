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

        if (empty($title)) {
            $message = "Please provide an image title.";
        } elseif (empty($category)) {
            $message = "Please provide a category.";
        } elseif (strlen($title) > 255) {
            $message = "Title is too long (max 255 characters).";
        } elseif (strlen($category) > 100) {
            $message = "Category is too long (max 100 characters).";
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
                        $stmt = $conn->prepare("INSERT INTO images (title, category, url) VALUES (?, ?, ?)");
                        $success = $stmt->execute([$title, $category, $uploadResult['url']]);

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
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: black;
            color: white;
            font-family: 'Loved by the King', cursive;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 5rem;
        }

        a { text-decoration: none; }

        .nav-buttons {
            position: fixed;
            top: 20px; right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        .nav-button {
            background-color: transparent;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
            cursor: pointer;
            width: 120px; height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Loved by the King', cursive;
        }

        .nav-button::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 40px; height: 40px;
            background-image: url('../pictures/other heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: background-image 0.3s ease;
        }

        .nav-button:hover::before {
            background-image: url('../pictures/one heart.png');
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        h1 {
            font-size: 2.5em;
            color: #e7e1e1;
            margin-bottom: 0.5rem;
        }

        .user-info {
            color: rgba(255,255,255,0.5);
            font-size: 0.9em;
            margin-bottom: 2rem;
        }

        .upload-container {
            width: 100%;
            max-width: 560px;
            padding: 2.5rem;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
            margin: 0 1rem 3rem;
        }

        .success-message {
            background: rgba(40,167,69,0.15);
            border: 1px solid rgba(40,167,69,0.4);
            color: #80e898;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1em;
        }

        .error-message {
            background: rgba(200,50,50,0.15);
            border: 1px solid rgba(200,50,50,0.4);
            color: #ff9999;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1em;
        }

        .form-group { margin-bottom: 1.5rem; }

        label {
            display: block;
            font-size: 1.1em;
            margin-bottom: 0.4rem;
            color: rgba(255,255,255,0.8);
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: rgba(255,255,255,0.7);
        }

        input[type="file"] { cursor: pointer; padding: 0.6rem; }

        small {
            display: block;
            color: rgba(255,255,255,0.4);
            margin-top: 0.4rem;
            font-size: 0.85em;
            font-family: 'Inter', sans-serif;
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        button[type="submit"] {
            flex: 1;
            padding: 0.85rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.2em;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s;
        }

        button[type="submit"]:hover { background: rgba(255,255,255,0.2); }
        button[type="submit"]:disabled { opacity: 0.5; cursor: not-allowed; }

        .logout-btn {
            padding: 0.85rem 1.5rem;
            background: transparent;
            border: 1px solid rgba(200,50,50,0.4);
            color: rgba(255,150,150,0.8);
            font-family: 'Loved by the King', cursive;
            font-size: 1.1em;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .logout-btn:hover {
            border-color: rgba(200,50,50,0.8);
            color: #ff9999;
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../"><button class="nav-button">Home</button></a>
        <a href="../gallery/"><button class="nav-button">Gallery</button></a>
        <a href="../admin/"><button class="nav-button">Dashboard</button></a>
    </div>

    <h1>Upload</h1>
    <p class="user-info">Logged in as: <?= htmlspecialchars($_SESSION['email']) ?></p>

    <div class="upload-container">
        <?php if ($message): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>">
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
                <input type="text" id="category" name="category" required placeholder="e.g., portrait, landscape" maxlength="100">
            </div>

            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" required>
                <small>JPG, PNG, GIF, WEBP · Max <?= MAX_FILE_SIZE / 1024 / 1024 ?>MB</small>
            </div>

            <div class="form-buttons">
                <button type="submit" id="submitBtn">Upload</button>
                <a href="../logout/" class="logout-btn">Logout</a>
            </div>
        </form>
    </div>

    <script>
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
