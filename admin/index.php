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

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$images = $conn->query("SELECT * FROM images ORDER BY category, title ASC")->fetchAll();

$contentRows = $conn->query("SELECT content_key, content_value FROM site_content")->fetchAll();
$content = [];
foreach ($contentRows as $row) {
    $content[$row['content_key']] = $row['content_value'];
}

function c($content, $key, $default = '') {
    return htmlspecialchars($content[$key] ?? $default);
}

$imageOptions = '<option value="">— Use default picture —</option>';
foreach ($images as $img) {
    $imageOptions .= '<option value="' . $img['id'] . '">' . htmlspecialchars($img['title']) . ' (' . htmlspecialchars($img['category']) . ')</option>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Eve Fitz Art</title>
    <link href="https://fonts.googleapis.com/css2?family=Loved+by+the+King&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../pictures/other heart.png" type="image/png">
    <style>
        :root {
            --bg: #000000;
            --surface: #111111;
            --surface2: #1a1a1a;
            --border: rgba(255,255,255,0.15);
            --text: #e7e1e1;
            --muted: rgba(255,255,255,0.5);
            --danger: #e05555;
            --success: #55c870;
            --transition: all 0.2s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h1 {
            font-family: 'Loved by the King', cursive;
            font-size: 1.6rem;
            color: #e7e1e1;
            font-weight: 400;
        }
        .topbar-right { display: flex; gap: 1rem; align-items: center; font-size: 0.9rem; color: var(--muted); }
        .topbar a { color: var(--muted); text-decoration: none; transition: color 0.2s; }
        .topbar a:hover { color: white; }

        .tabs {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            display: flex;
        }
        .tab-btn {
            padding: 1rem 1.5rem;
            border: none;
            background: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--muted);
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active { color: white; border-bottom-color: rgba(255,255,255,0.6); }

        .tab-content { display: none; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .tab-content.active { display: block; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .card h2 {
            font-family: 'Loved by the King', cursive;
            font-size: 1.8rem;
            font-weight: 400;
            color: #e7e1e1;
            margin-bottom: 1.5rem;
        }

        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--muted); margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: var(--text);
            transition: var(--transition);
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: rgba(255,255,255,0.5); }
        .form-group textarea { min-height: 200px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
        .form-group select option { background: #222; }

        .btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-primary { background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.25); }
        .btn-primary:hover { background: rgba(255,255,255,0.2); }
        .btn-danger { background: rgba(200,50,50,0.2); color: #ff9999; border: 1px solid rgba(200,50,50,0.4); }
        .btn-danger:hover { background: rgba(200,50,50,0.35); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }

        .gallery-table { width: 100%; border-collapse: collapse; }
        .gallery-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .gallery-table td { padding: 0.75rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
        .gallery-table tr:last-child td { border-bottom: none; }
        .gallery-table tr:hover td { background: rgba(255,255,255,0.03); }

        .thumb {
            width: 60px; height: 60px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }

        .inline-input {
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 0.4rem 0.6rem;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            width: 100%;
            background: transparent;
            color: var(--text);
            transition: var(--transition);
        }
        .inline-input:hover { border-color: var(--border); background: var(--surface2); }
        .inline-input:focus { outline: none; border-color: rgba(255,255,255,0.4); background: var(--surface2); }

        .actions-cell { display: flex; gap: 0.5rem; align-items: center; }

        #toast {
            position: fixed;
            bottom: 2rem; right: 2rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: white;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 9999;
            pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateY(0); }
        #toast.success { background: rgba(40,167,69,0.9); }
        #toast.error { background: rgba(200,50,50,0.9); }

        #about-photo-preview {
            width: 120px; height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 0.75rem;
            display: block;
            border: 1px solid var(--border);
        }

        .featured-preview {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        .featured-preview img {
            width: 100%;
            aspect-ratio: 4/5;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .topbar { padding: 1rem; }
            .tab-content { padding: 1rem; }
            .form-row { grid-template-columns: 1fr; }
            .featured-preview { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <h1>Admin Dashboard</h1>
    <div class="topbar-right">
        <span><?= htmlspecialchars($_SESSION['email']) ?></span>
        <a href="../upload/">Upload</a>
        <a href="../gallery/">Gallery</a>
        <a href="../logout/">Logout</a>
    </div>
</div>

<div class="tabs">
    <button class="tab-btn active" data-tab="gallery">Gallery</button>
    <button class="tab-btn" data-tab="home">Home Page</button>
    <button class="tab-btn" data-tab="about">About Page</button>
</div>

<!-- GALLERY TAB -->
<div class="tab-content active" id="tab-gallery">
    <div class="card">
        <h2>Gallery Images</h2>
        <?php if (empty($images)): ?>
            <p style="color: var(--muted);">No images uploaded yet.</p>
        <?php else: ?>
        <table class="gallery-table">
            <thead>
                <tr>
                    <th style="width:80px">Photo</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th style="width:130px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($images as $img): ?>
                <tr data-id="<?= $img['id'] ?>">
                    <td><img src="<?= htmlspecialchars($img['url']) ?>" class="thumb" alt=""></td>
                    <td><input type="text" class="inline-input img-title" value="<?= htmlspecialchars($img['title']) ?>" maxlength="255"></td>
                    <td><input type="text" class="inline-input img-category" value="<?= htmlspecialchars($img['category']) ?>" maxlength="100"></td>
                    <td>
                        <div class="actions-cell">
                            <button class="btn btn-primary btn-sm save-image-btn">Save</button>
                            <button class="btn btn-danger btn-sm delete-image-btn">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- HOME TAB -->
<div class="tab-content" id="tab-home">
    <div class="card">
        <h2>Hero Text</h2>
        <div class="form-group">
            <label>Title</label>
            <input type="text" id="hero_title" value="<?= c($content, 'hero_title', 'Eve Fitzsimons') ?>" maxlength="100">
        </div>
        <div class="form-group">
            <label>Subtitle</label>
            <input type="text" id="hero_subtitle" value="<?= c($content, 'hero_subtitle', 'Art Portfolio') ?>" maxlength="150">
        </div>
        <div class="form-group">
            <label>Description</label>
            <input type="text" id="hero_description" value="<?= c($content, 'hero_description', '') ?>" maxlength="300">
        </div>
        <button class="btn btn-primary" id="save-hero-btn">Save Hero Text</button>
    </div>

    <div class="card">
        <h2>Featured Images</h2>
        <p style="color:var(--muted); margin-bottom:1.5rem; font-size:0.9rem;">Choose which images appear in the Featured Work section on the home page.</p>
        <div class="form-row">
            <div class="form-group">
                <label>Featured Image 1</label>
                <select id="featured_image_1"><?= $imageOptions ?></select>
            </div>
            <div class="form-group">
                <label>Featured Image 2</label>
                <select id="featured_image_2"><?= $imageOptions ?></select>
            </div>
            <div class="form-group">
                <label>Featured Image 3</label>
                <select id="featured_image_3"><?= $imageOptions ?></select>
            </div>
        </div>
        <div class="featured-preview">
            <img id="fp1" src="" alt="">
            <img id="fp2" src="" alt="">
            <img id="fp3" src="" alt="">
        </div>
        <button class="btn btn-primary" id="save-featured-btn" style="margin-top:1.5rem">Save Featured Images</button>
    </div>
</div>

<!-- ABOUT TAB -->
<div class="tab-content" id="tab-about">
    <div class="card">
        <h2>About Photo</h2>
        <div class="form-group">
            <label>Photo URL or path (e.g. /uploads/filename.jpg)</label>
            <input type="text" id="about_photo" value="<?= c($content, 'about_photo', '../pictures/beatas1.png') ?>">
        </div>
        <img id="about-photo-preview" src="<?= c($content, 'about_photo', '../pictures/beatas1.png') ?>" alt="About photo preview">
        <button class="btn btn-primary" id="save-about-photo-btn" style="margin-top:1rem">Save Photo</button>
    </div>

    <div class="card">
        <h2>Bio Text</h2>
        <p style="color:var(--muted); margin-bottom:1rem; font-size:0.9rem;">Use HTML like &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;. Wrap paragraphs in &lt;p&gt;...&lt;/p&gt;</p>
        <div class="form-group">
            <textarea id="about_bio"><?= htmlspecialchars($content['about_bio'] ?? '') ?></textarea>
        </div>
        <button class="btn btn-primary" id="save-about-bio-btn">Save Bio</button>
    </div>
</div>

<div id="toast"></div>

<script>
const CSRF = <?= json_encode($_SESSION['csrf_token']) ?>;

const imageMap = {
    <?php foreach ($images as $img): ?>
    <?= $img['id'] ?>: { url: <?= json_encode($img['url']) ?>, title: <?= json_encode($img['title']) ?> },
    <?php endforeach; ?>
};

const featuredSaved = {
    featured_image_1: <?= json_encode($content['featured_image_1'] ?? '') ?>,
    featured_image_2: <?= json_encode($content['featured_image_2'] ?? '') ?>,
    featured_image_3: <?= json_encode($content['featured_image_3'] ?? '') ?>,
};

['featured_image_1','featured_image_2','featured_image_3'].forEach(key => {
    const sel = document.getElementById(key);
    if (featuredSaved[key]) sel.value = featuredSaved[key];
});
updateFeaturedPreviews();

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});

function toast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show ' + type;
    setTimeout(() => { t.className = ''; }, 3000);
}

async function postAction(data) {
    data.csrf_token = CSRF;
    const body = new URLSearchParams(data);
    const res = await fetch('actions.php', { method: 'POST', body });
    return res.json();
}

document.querySelectorAll('.save-image-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const row = btn.closest('tr');
        const id = row.dataset.id;
        const title = row.querySelector('.img-title').value.trim();
        const category = row.querySelector('.img-category').value.trim();
        if (!title || !category) { toast('Title and category are required', 'error'); return; }
        btn.disabled = true;
        const result = await postAction({ action: 'update_image', id, title, category });
        btn.disabled = false;
        if (result.success) toast('Image updated');
        else toast(result.error || 'Error', 'error');
    });
});

document.querySelectorAll('.delete-image-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        if (!confirm('Delete this image? This cannot be undone.')) return;
        const row = btn.closest('tr');
        const id = row.dataset.id;
        btn.disabled = true;
        const result = await postAction({ action: 'delete_image', id });
        if (result.success) { row.remove(); toast('Image deleted'); }
        else { btn.disabled = false; toast(result.error || 'Error', 'error'); }
    });
});

document.getElementById('save-hero-btn').addEventListener('click', async () => {
    for (const key of ['hero_title', 'hero_subtitle', 'hero_description']) {
        const val = document.getElementById(key).value;
        const result = await postAction({ action: 'update_content', key, value: val });
        if (!result.success) { toast(result.error || 'Error saving ' + key, 'error'); return; }
    }
    toast('Hero text saved');
});

document.getElementById('save-featured-btn').addEventListener('click', async () => {
    for (const key of ['featured_image_1', 'featured_image_2', 'featured_image_3']) {
        const val = document.getElementById(key).value;
        const result = await postAction({ action: 'update_content', key, value: val });
        if (!result.success) { toast(result.error || 'Error saving ' + key, 'error'); return; }
    }
    toast('Featured images saved');
    updateFeaturedPreviews();
});

['featured_image_1','featured_image_2','featured_image_3'].forEach(key => {
    document.getElementById(key).addEventListener('change', updateFeaturedPreviews);
});

function updateFeaturedPreviews() {
    const defaults = ['../pictures/base.png', '../pictures/beatas1.png', '../pictures/base.png'];
    ['featured_image_1','featured_image_2','featured_image_3'].forEach((key, i) => {
        const sel = document.getElementById(key);
        const img = document.getElementById('fp' + (i+1));
        const val = sel.value;
        img.src = val && imageMap[val] ? imageMap[val].url : defaults[i];
        img.style.display = 'block';
    });
}

document.getElementById('about_photo').addEventListener('input', function() {
    document.getElementById('about-photo-preview').src = this.value;
});

document.getElementById('save-about-photo-btn').addEventListener('click', async () => {
    const value = document.getElementById('about_photo').value;
    const result = await postAction({ action: 'update_content', key: 'about_photo', value });
    if (result.success) toast('Photo saved');
    else toast(result.error || 'Error', 'error');
});

document.getElementById('save-about-bio-btn').addEventListener('click', async () => {
    const value = document.getElementById('about_bio').value;
    const result = await postAction({ action: 'update_content', key: 'about_bio', value });
    if (result.success) toast('Bio saved');
    else toast(result.error || 'Error', 'error');
});
</script>
</body>
</html>
