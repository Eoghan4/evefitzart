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
    <title>Eve Fitzsimons</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="pictures/other heart.png" type="image/png">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: black;
            color: #e7e1e1;
            font-family: 'Georgia', serif;
            overflow: hidden;
        }

        @keyframes heartbeat {
            0%   { transform: scale(1); }
            20%  { transform: scale(1.2); }
            40%  { transform: scale(0.9); }
            60%  { transform: scale(1); }
            100% { transform: scale(1); }
        }

        /* ── Entry screen ── */
        #entry {
            position: fixed;
            inset: 0;
            z-index: 900;
            background: black;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            cursor: pointer;
            transition: opacity 0.9s ease;
        }

        #entry.hidden { opacity: 0; pointer-events: none; }

        #entry-heart {
            width: 60px;
            height: 60px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .entry-hint {
            color: rgba(255,255,255,0.4);
            font-family: 'Georgia', serif;
            font-size: 0.85rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        @media (max-width: 640px) {
            #entry-heart { width: 80px; height: 80px; }
        }

        /* ── Main content ── */
        #content {
            opacity: 0;
            transition: opacity 1.8s ease;
            min-height: 100vh;
        }

        #content.visible { opacity: 1; }

        a { text-decoration: none; color: inherit; }

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
            width: 28px;
            height: 28px;
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
            width: 24px;
            height: 2px;
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

        /* ── Hero ── */
        .hero {
            height: 100vh;
            height: 100svh;
            background-image: url('pictures/base.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            background-color: black;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding-bottom: 4rem;
            position: relative;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 40%;
            background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.75));
            pointer-events: none;
        }

        .hero-text {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 0 1.5rem;
        }

        .hero-text h1 {
            font-family: 'Georgia', serif;
            font-size: clamp(2.8rem, 8vw, 5.5rem);
            color: #e7e1e1;
            line-height: 1.1;
            margin-bottom: 0.5rem;
        }

        .hero-text .subtitle {
            font-family: 'Georgia', serif;
            font-size: clamp(0.95rem, 2vw, 1.15rem);
            color: rgba(255,255,255,0.6);
            letter-spacing: 0.08em;
            margin-bottom: 0.5rem;
        }

        .hero-text .description {
            font-size: 1rem;
            color: rgba(255,255,255,0.5);
        }

        /* ── Featured ── */
        .featured {
            background: black;
            padding: 6rem 2rem 5rem;
        }

        .featured-inner {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-heading {
            font-family: 'Georgia', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            color: #e7e1e1;
            text-align: center;
            margin-bottom: 3rem;
        }

        .work-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 640px) {
            .work-grid { grid-template-columns: 1fr; }
        }

        .work-item {
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            aspect-ratio: 4/5;
            cursor: pointer;
            background: #111;
        }

        .work-item img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .work-item:hover img { transform: scale(1.04); }

        .work-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .work-item:hover .work-overlay { opacity: 1; }

        .work-overlay span {
            font-family: 'Georgia', serif;
            font-size: 1.6rem;
            color: white;
        }

        .view-all-wrap { text-align: center; }

        .view-all-btn {
            display: inline-block;
            padding: 0.85rem 2.5rem;
            border: 1px solid rgba(255,255,255,0.5);
            color: white;
            font-family: 'Georgia', serif;
            font-size: 0.9rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            background: transparent;
            transition: background 0.3s, border-color 0.3s;
            cursor: pointer;
            border-radius: 2px;
            text-decoration: none;
        }

        .view-all-btn:hover {
            background: rgba(255,255,255,0.1);
            border-color: white;
        }

        /* ── Footer ── */
        footer {
            background: black;
            border-top: 1px solid rgba(255,255,255,0.08);
            padding: 2.5rem 2rem;
            text-align: center;
            color: rgba(255,255,255,0.3);
            font-family: 'Georgia', serif;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

    <!-- Entry screen -->
    <div id="entry">
        <img src="pictures/one heart.png" alt="Enter" id="entry-heart">
        <span class="entry-hint">tap to enter</span>
    </div>

    <!-- Main content -->
    <div id="content">

        <nav class="site-nav">
            <a href="./" class="nav-logo">
                Eve Fitzsimons
                <img src="pictures/one heart.png" class="logo-heart" alt="">
            </a>
            <div class="nav-links">
                <a href="gallery/" class="nav-link">Gallery</a>
                <a href="about/" class="nav-link">About</a>
                <a href="contact/" class="nav-link">Contact</a>
            </div>
            <button class="hamburger" aria-label="Menu" onclick="toggleMenu()">
                <span></span><span></span><span></span>
            </button>
        </nav>

        <div class="mobile-menu" id="mobileMenu">
            <button class="mobile-close" onclick="toggleMenu()">&times;</button>
            <a href="gallery/" class="mobile-link">Gallery</a>
            <a href="about/" class="mobile-link">About</a>
            <a href="contact/" class="mobile-link">Contact</a>
        </div>

        <!-- Hero -->
        <section class="hero">
            <div class="hero-text">
                <h1><?= $heroTitle ?></h1>
                <?php if ($heroSubtitle): ?>
                    <p class="subtitle"><?= $heroSubtitle ?></p>
                <?php endif; ?>
                <?php if ($heroDescription): ?>
                    <p class="description"><?= $heroDescription ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Featured Work -->
        <section class="featured">
            <div class="featured-inner">
                <h2 class="section-heading">Featured Work</h2>
                <div class="work-grid">
                    <?php foreach ($featured as $f): ?>
                    <div class="work-item">
                        <img src="<?= $f['src'] ?>" alt="<?= $f['alt'] ?>" loading="lazy">
                        <div class="work-overlay"><span><?= $f['label'] ?></span></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="view-all-wrap">
                    <a href="gallery/" class="view-all-btn">View Full Gallery</a>
                </div>
            </div>
        </section>

        <footer>&copy; 2026 Eve Fitzsimons</footer>

    </div><!-- #content -->

    <script>
        // Entry screen
        document.getElementById('entry').addEventListener('click', function() {
            this.classList.add('hidden');
            setTimeout(() => {
                this.style.display = 'none';
                document.body.style.overflow = 'auto';
                document.getElementById('content').classList.add('visible');
            }, 900);
        });

        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('open');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('open') ? 'hidden' : '';
        }
    </script>
</body>
</html>
