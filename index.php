<?php
require './db.php';

$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

$heroTitle = htmlspecialchars($content['hero_title'] ?? 'Eve Fitzsimons');
$heroSubtitle = htmlspecialchars($content['hero_subtitle'] ?? 'Art Portfolio');
$heroDescription = htmlspecialchars($content['hero_description'] ?? '');

$defaults = [
    ['src' => './pictures/base.png', 'alt' => 'Art 1', 'label' => 'Art'],
    ['src' => './pictures/beatas1.png', 'alt' => 'Art 2', 'label' => 'Art'],
    ['src' => './pictures/base.png', 'alt' => 'Art 3', 'label' => 'Art'],
];
$featured = [];
for ($i = 1; $i <= 3; $i++) {
    $imgId = $content['featured_image_' . $i] ?? '';
    if ($imgId) {
        $stmt = $conn->prepare("SELECT url, title, category FROM images WHERE id = ?");
        $stmt->execute([$imgId]);
        $row = $stmt->fetch();
        if ($row) {
            $featured[] = [
                'src'   => htmlspecialchars($row['url']),
                'alt'   => htmlspecialchars($row['title']),
                'label' => htmlspecialchars(ucfirst($row['category'])),
            ];
            continue;
        }
    }
    $featured[] = $defaults[$i - 1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eve Fitz Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="pictures/other heart.png" type="image/png">
    <style>
        body {
            margin: 0; padding: 0;
            background-color: black;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            font-family: 'Loved by the King', cursive;
        }

        #heart-container {
            position: relative;
            cursor: pointer;
            z-index: 2;
            transition: opacity 1s ease-in-out;
        }

        #heart {
            width: 60px;
            height: auto;
            animation: heartbeat 2s ease-in-out infinite;
        }

        @keyframes heartbeat {
            0% { transform: scale(1); }
            20% { transform: scale(1.2); }
            40% { transform: scale(0.9); }
            60% { transform: scale(1); }
            100% { transform: scale(1); }
        }

        #content {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            background-color: black;
            z-index: 1;
            overflow-y: auto;
        }

        #content.visible { opacity: 1; }

        #base-image {
            width: 100vw;
            height: 100vh;
            object-fit: contain;
            background: black;
            display: block;
        }

        .text-content {
            position: absolute;
            top: 20px; left: 20px;
            color: white;
            text-align: left;
            font-family: 'Loved by the King', cursive;
        }

        h1 {
            color: #e7e1e1;
            font-size: 3em;
            margin: 0;
            font-family: 'Loved by the King', cursive;
            display: flex;
            align-items: center;
        }

        h1::after {
            content: '';
            display: inline-block;
            width: 60px; height: 60px;
            background-image: url('pictures/one heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-left: 10px;
            animation: heartbeat 2s ease-in-out infinite;
        }

        h3 {
            color: rgb(216, 211, 211);
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        p {
            color: white;
            font-size: 1.2em;
            line-height: 1.6;
        }

        a { text-decoration: none; }

        .nav-buttons {
            position: absolute;
            top: 20px; right: 20px;
            display: flex;
            gap: 10px;
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
            background-image: url('pictures/other heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            transition: background-image 0.3s ease;
        }

        .nav-button:hover::before {
            background-image: url('pictures/one heart.png');
            animation: heartbeat 2s ease-in-out infinite;
        }

        .featured-section {
            background: black;
            padding: 4rem 2rem;
            text-align: center;
        }

        .featured-section h2 {
            font-family: 'Loved by the King', cursive;
            color: #e7e1e1;
            font-size: 2.5em;
            margin-bottom: 2rem;
        }

        .work-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .work-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            width: 300px;
            height: 375px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .work-item:hover { transform: scale(1.05); }

        .work-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
        }

        .work-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .work-item:hover .work-overlay { opacity: 1; }

        .work-overlay h3 {
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.5em;
            margin: 0;
        }

        .view-gallery-btn {
            display: inline-block;
            padding: 12px 30px;
            border: 2px solid white;
            color: white;
            font-family: 'Loved by the King', cursive;
            font-size: 1.2em;
            cursor: pointer;
            background: transparent;
            transition: background 0.3s ease, color 0.3s ease;
        }

        .view-gallery-btn:hover { background: white; color: black; }

        footer {
            background: black;
            color: rgba(255,255,255,0.4);
            text-align: center;
            padding: 2rem;
            font-family: 'Loved by the King', cursive;
            font-size: 0.9em;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div id="heart-container">
        <img src="pictures/one heart.png" alt="Heart" id="heart">
    </div>

    <div id="content">
        <img src="pictures/base.png" alt="Eve Fitz Art" id="base-image">
        <div class="text-content">
            <h1><?= $heroTitle ?></h1>
            <?php if ($heroSubtitle): ?><h3><?= $heroSubtitle ?></h3><?php endif; ?>
            <?php if ($heroDescription): ?><p><?= $heroDescription ?></p><?php endif; ?>
        </div>
        <div class="nav-buttons">
            <a href="gallery/"><button class="nav-button">Gallery</button></a>
            <a href="about/"><button class="nav-button">About</button></a>
            <a href="contact/"><button class="nav-button">Contact</button></a>
        </div>

        <div class="featured-section">
            <h2>Featured Work</h2>
            <div class="work-grid">
                <?php foreach ($featured as $f): ?>
                <div class="work-item">
                    <img src="<?= $f['src'] ?>" alt="<?= $f['alt'] ?>">
                    <div class="work-overlay"><h3><?= $f['label'] ?></h3></div>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="gallery/"><button class="view-gallery-btn">View Full Gallery</button></a>
        </div>

        <footer>&copy; 2026 Eve Fitz Art</footer>
    </div>

    <script>
        document.getElementById('heart-container').addEventListener('click', function() {
            this.style.opacity = '0';
            setTimeout(() => {
                this.style.display = 'none';
                document.body.style.overflow = 'auto';
                document.getElementById('content').classList.add('visible');
            }, 1000);
        });
    </script>
</body>
</html>
