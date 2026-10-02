<?php
require '../db.php';

$stmt = $conn->query("SELECT * FROM images ORDER BY category, title ASC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$mediums = [];
foreach ($images as $img) {
    $cat = $img['category'] ?? '';
    if ($cat) $categories[$cat][] = $img;
    if ($img['medium'] && !in_array($img['medium'], $mediums)) $mediums[] = $img['medium'];
}
ksort($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Eve Fitzsimons</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            background: black;
            color: #e7e1e1;
            font-family: 'Georgia', serif;
            min-height: 100vh;
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

        .nav-link:hover, .nav-link.active { color: white; }

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
            transition: all 0.3s;
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
            font-family: 'Georgia', serif;
            font-size: 2.5rem;
            color: #e7e1e1;
            text-decoration: none;
            transition: color 0.2s;
        }

        .mobile-link:hover { color: white; }

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

        /* ── Page header ── */
        .page-header {
            padding: 7rem 2rem 2rem;
            text-align: center;
        }

        .page-title {
            font-family: 'Georgia', serif;
            font-size: clamp(2.5rem, 6vw, 4rem);
            color: #e7e1e1;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .title-heart {
            width: 40px; height: 40px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        /* ── Filter nav ── */
        .filter-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            padding: 1.5rem 2rem 2rem;
        }

        .filter-row {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .filter-label {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            width: 100%;
            text-align: center;
        }

        .filter-btn {
            padding: 0.5rem 1.25rem;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.25);
            color: rgba(255,255,255,0.6);
            font-family: 'Georgia', serif;
            font-size: 0.85rem;
            letter-spacing: 0.06em;
            cursor: pointer;
            border-radius: 50px;
            transition: all 0.25s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            border-color: white;
            color: white;
            background: rgba(255,255,255,0.08);
        }

        /* ── Gallery ── */
        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.5rem 6rem;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .gallery-grid { grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 3px;
            aspect-ratio: 1/1;
            cursor: pointer;
            background: #111;
        }

        .gallery-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .gallery-item:hover img { transform: scale(1.05); }

        .gallery-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.65);
            display: flex;
            align-items: flex-end;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gallery-item:hover .gallery-overlay { opacity: 1; }

        .overlay-title {
            font-family: 'Georgia', serif;
            font-size: 1.2rem;
            color: white;
        }

        .empty-gallery {
            text-align: center;
            padding: 8rem 2rem;
            color: rgba(255,255,255,0.3);
            font-family: 'Georgia', serif;
            font-size: 1.8rem;
        }

        /* ── Lightbox ── */
        .lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.96);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .lightbox.active { display: flex; }

        .lightbox-inner {
            position: relative;
            max-width: min(90vw, 1000px);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .lightbox-image {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 2px;
            display: block;
        }

        .lightbox-title {
            font-family: 'Georgia', serif;
            font-size: 1.4rem;
            color: #e7e1e1;
            text-align: center;
        }

        .lightbox-medium {
            font-family: 'Georgia', serif;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.45);
            text-align: center;
            margin-top: 0.25rem;
            font-style: italic;
        }

        .lightbox-close {
            position: fixed;
            top: 1.25rem; right: 1.25rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            width: 42px; height: 42px;
            border-radius: 50%;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            line-height: 1;
        }

        .lightbox-close:hover { background: rgba(255,255,255,0.18); }

        /* ── Footer ── */
        footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 2.5rem 2rem;
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
            Eve Fitzsimons
            <img src="../pictures/one heart.png" class="logo-heart" alt="">
        </a>
        <div class="nav-links">
            <a href="../gallery/" class="nav-link active">Gallery</a>
            <a href="../about/" class="nav-link">About</a>
            <a href="../contact/" class="nav-link">Contact</a>
        </div>
        <button class="hamburger" aria-label="Menu" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-close" onclick="toggleMenu()">&times;</button>
        <a href="../gallery/" class="mobile-link">Gallery</a>
        <a href="../about/" class="mobile-link">About</a>
        <a href="../contact/" class="mobile-link">Contact</a>
    </div>

    <div class="page-header">
        <h1 class="page-title">
            Gallery
            <img src="../pictures/one heart.png" class="title-heart" alt="">
        </h1>
    </div>

    <?php if (empty($images)): ?>
        <div class="empty-gallery">No images yet</div>
    <?php else: ?>

    <div class="filter-group">
        <div class="filter-row">
            <span class="filter-label">Category</span>
            <button class="filter-btn cat-btn active" data-cat="all">All</button>
            <?php foreach (array_keys($categories) as $cat): ?>
                <button class="filter-btn cat-btn" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars(ucfirst($cat)) ?></button>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($mediums)): ?>
        <div class="filter-row">
            <span class="filter-label">Medium</span>
            <button class="filter-btn med-btn active" data-med="all">All</button>
            <?php foreach ($mediums as $med): ?>
                <button class="filter-btn med-btn" data-med="<?= htmlspecialchars($med) ?>"><?= htmlspecialchars(ucfirst($med)) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="gallery-container">
        <div class="gallery-grid">
            <?php foreach ($categories as $cat => $imgs): ?>
                <?php foreach ($imgs as $img): ?>
                <div class="gallery-item"
                     data-cat="<?= htmlspecialchars($cat) ?>"
                     data-med="<?= htmlspecialchars($img['medium'] ?? '') ?>"
                     onclick="openLightbox('<?= htmlspecialchars($img['url']) ?>', '<?= htmlspecialchars(addslashes($img['title'])) ?>', '<?= htmlspecialchars(addslashes($img['medium'] ?? '')) ?>')">
                    <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>" loading="lazy">
                    <div class="gallery-overlay">
                        <span class="overlay-title"><?= htmlspecialchars($img['title']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
        <p class="empty-gallery" id="noResults" style="display:none">No results</p>
    </div>

    <?php endif; ?>

    <div class="lightbox" id="lightbox">
        <div class="lightbox-inner">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <img class="lightbox-image" id="lightboxImage" src="" alt="">
            <p class="lightbox-title" id="lightboxTitle"></p>
            <p class="lightbox-medium" id="lightboxMedium"></p>
        </div>
    </div>

    <footer>&copy; 2026 Eve Fitzsimons</footer>

    <script>
        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('open');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('open') ? 'hidden' : '';
        }

        let activeCat = 'all';
        let activeMed = 'all';

        function applyFilters() {
            const items = document.querySelectorAll('.gallery-item');
            let visible = 0;
            items.forEach(item => {
                const catMatch = activeCat === 'all' || item.dataset.cat === activeCat;
                const medMatch = activeMed === 'all' || item.dataset.med === activeMed;
                const show = catMatch && medMatch;
                item.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
        }

        document.querySelectorAll('.cat-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeCat = this.dataset.cat;
                applyFilters();
            });
        });

        document.querySelectorAll('.med-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.med-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeMed = this.dataset.med;
                applyFilters();
            });
        });

        function openLightbox(url, title, medium) {
            document.getElementById('lightboxImage').src = url;
            document.getElementById('lightboxTitle').textContent = title;
            const mediumEl = document.getElementById('lightboxMedium');
            mediumEl.textContent = medium || '';
            mediumEl.style.display = medium ? 'block' : 'none';
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
