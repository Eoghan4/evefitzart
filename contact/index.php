<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Eve Fitzsimons</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: black;
            color: #e7e1e1;
            font-family: 'Loved by the King', cursive;
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
            font-family: 'Loved by the King', cursive;
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

        /* ── Content ── */
        .contact-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8rem 2rem 6rem;
            gap: 1.5rem;
        }

        .page-title {
            font-family: 'Loved by the King', cursive;
            font-size: clamp(2.5rem, 7vw, 4.5rem);
            color: #e7e1e1;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .title-heart {
            width: 42px; height: 42px;
            object-fit: contain;
            animation: heartbeat 2s ease-in-out infinite;
        }

        .contact-text {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            line-height: 1.8;
            color: rgba(255,255,255,0.72);
            max-width: 520px;
        }

        .instagram-link {
            color: #e05580;
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s;
        }

        .instagram-link:hover { color: #ff7aa8; }

        /* ── Footer ── */
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
            Eve Fitzsimons
            <img src="../pictures/one heart.png" class="logo-heart" alt="">
        </a>
        <div class="nav-links">
            <a href="../gallery/" class="nav-link">Gallery</a>
            <a href="../about/" class="nav-link">About</a>
            <a href="../contact/" class="nav-link active">Contact</a>
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

    <div class="contact-wrap">
        <h1 class="page-title">
            Contact
            <img src="../pictures/one heart.png" class="title-heart" alt="">
        </h1>
        <p class="contact-text">
            For enquiries or to get in touch, reach me on Instagram —
            <a href="https://www.instagram.com/eve.fitzart/" class="instagram-link">@eve.fitzart</a>
        </p>
    </div>

    <footer>&copy; 2026 Eve Fitzsimons</footer>

    <script>
        function toggleMenu() {
            document.getElementById('mobileMenu').classList.toggle('open');
            document.body.style.overflow = document.getElementById('mobileMenu').classList.contains('open') ? 'hidden' : '';
        }
    </script>
</body>
</html>
