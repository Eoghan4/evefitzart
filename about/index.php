<?php
require '../db.php';

$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

$aboutPhoto = htmlspecialchars($content['about_photo'] ?? '../pictures/beatas1.png');
$aboutBio   = $content['about_bio'] ?? '<p>Eve Fitzsimons is a passionate artist who creates stunning and heartfelt pieces that resonate with her audience. Her work is inspired by the beauty of the world around her and the emotions that connect us all.</p>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Eve Fitz Art</title>
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
        }

        a { text-decoration: none; }

        .nav-buttons {
            display: flex;
            gap: 10px;
            margin: 30px 0 0;
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
            color: #e7e1e1;
            font-size: 3em;
            margin: 2rem 0 1.5rem;
            text-align: center;
        }

        h1::after {
            content: '';
            display: inline-block;
            vertical-align: middle;
            width: 60px; height: 60px;
            background-image: url('../pictures/one heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-left: 10px;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .about-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 3rem;
            max-width: 1000px;
            padding: 0 2rem 4rem;
        }

        .about-photo { flex-shrink: 0; }

        .about-photo img {
            width: 300px;
            height: 375px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(255,255,255,0.1);
        }

        .about-text {
            flex: 1;
            min-width: 280px;
            max-width: 600px;
        }

        .about-text p {
            font-size: 1.3em;
            line-height: 1.7;
            color: rgb(216, 211, 211);
            margin-bottom: 1rem;
        }

        footer {
            background: black;
            color: rgba(255,255,255,0.4);
            text-align: center;
            padding: 2rem;
            width: 100%;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9em;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../"><button class="nav-button">Home</button></a>
        <a href="../gallery/"><button class="nav-button">Gallery</button></a>
        <a href="../contact/"><button class="nav-button">Contact</button></a>
    </div>

    <h1>About Eve</h1>

    <div class="about-content">
        <div class="about-photo">
            <img src="<?= $aboutPhoto ?>" alt="Eve Fitzsimons">
        </div>
        <div class="about-text">
            <?= $aboutBio ?>
        </div>
    </div>

    <footer>&copy; 2026 Eve Fitz Art</footer>
</body>
</html>
