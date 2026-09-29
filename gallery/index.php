<?php
require '../db.php';

$stmt = $conn->query("SELECT * FROM images ORDER BY category, title ASC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
foreach ($images as $img) {
    $categories[$img['category']][] = $img;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Eve Fitz Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            background-color: black;
            color: white;
            font-family: 'Loved by the King', cursive;
            min-height: 100vh;
        }

        .nav-buttons {
            position: fixed;
            top: 20px; right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        a { text-decoration: none; }

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
            0% { transform: scale(1); }
            20% { transform: scale(1.2); }
            40% { transform: scale(0.9); }
            60% { transform: scale(1); }
            100% { transform: scale(1); }
        }

        h1 {
            text-align: center;
            font-size: 3em;
            padding: 80px 20px 20px;
            color: #e7e1e1;
        }

        h1::after {
            content: '';
            display: inline-block;
            vertical-align: middle;
            width: 50px; height: 50px;
            background-image: url('../pictures/one heart.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-left: 10px;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .filter-nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1.5rem 2rem;
        }

        .filter-btn {
            padding: 8px 20px;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: rgba(255,255,255,0.7);
            font-family: 'Loved by the King', cursive;
            font-size: 1em;
            cursor: pointer;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: white;
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .gallery-container {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .category-section { margin-bottom: 4rem; }

        .category-title {
            text-align: center;
            font-size: 2em;
            color: #e7e1e1;
            margin-bottom: 2rem;
            text-transform: capitalize;
        }

        .gallery-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            width: 300px; height: 300px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover { transform: scale(1.05); box-shadow: 0 4px 20px rgba(255,255,255,0.1); }

        .gallery-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay { opacity: 1; }

        .overlay-title {
            color: white;
            font-size: 1.3em;
            text-align: center;
            font-family: 'Loved by the King', cursive;
        }

        .lightbox {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .lightbox.active { display: flex; }

        .lightbox-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            text-align: center;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-title {
            font-family: 'Loved by the King', cursive;
            color: #e7e1e1;
            font-size: 1.5em;
            margin-top: 1rem;
        }

        .lightbox-close {
            position: fixed;
            top: 1rem; right: 1rem;
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            width: 40px; height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .lightbox-close:hover { background: rgba(255,255,255,0.2); }

        .empty-gallery {
            text-align: center;
            padding: 6rem 2rem;
            color: rgba(255,255,255,0.5);
            font-size: 1.5em;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: rgba(255,255,255,0.4);
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="nav-buttons">
        <a href="../"><button class="nav-button">Home</button></a>
        <a href="../about/"><button class="nav-button">About</button></a>
        <a href="../contact/"><button class="nav-button">Contact</button></a>
    </div>

    <h1>Gallery</h1>

    <?php if (empty($images)): ?>
        <div class="empty-gallery">No images uploaded yet.</div>
    <?php else: ?>

    <div class="filter-nav">
        <button class="filter-btn active" data-filter="all">All</button>
        <?php foreach (array_keys($categories) as $cat): ?>
            <button class="filter-btn" data-filter="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars(ucfirst($cat)) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="gallery-container">
        <?php foreach ($categories as $cat => $imgs): ?>
        <div class="category-section" data-category="<?= htmlspecialchars($cat) ?>">
            <h2 class="category-title"><?= htmlspecialchars(ucfirst($cat)) ?></h2>
            <div class="gallery-grid">
                <?php foreach ($imgs as $img): ?>
                <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($img['url']) ?>', '<?= htmlspecialchars(addslashes($img['title'])) ?>')">
                    <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" loading="lazy">
                    <div class="gallery-overlay">
                        <h3 class="overlay-title"><?= htmlspecialchars($img['title']) ?></h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <img class="lightbox-image" id="lightboxImage" src="" alt="">
            <p class="lightbox-title" id="lightboxTitle"></p>
        </div>
    </div>

    <footer>&copy; 2026 Eve Fitz Art</footer>

    <script>
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                document.querySelectorAll('.category-section').forEach(sec => {
                    sec.style.display = (filter === 'all' || sec.dataset.category === filter) ? 'block' : 'none';
                });
            });
        });

        function openLightbox(url, title) {
            document.getElementById('lightboxImage').src = url;
            document.getElementById('lightboxTitle').textContent = title;
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>
