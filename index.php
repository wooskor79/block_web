<?php
require_once __DIR__ . '/functions.php';

// Get Settings
$settings = get_settings();
$siteTitle = $settings['site_title'] ?? 'Modern Tech & Minimal Life';
$footerText = $settings['footer_text'] ?? '© ' . date('Y') . ' Minimal CMS.';

// Determine Page
$pageSlug = $_GET['page'] ?? '';

// If no page specified, look for Home Page in DB
if (empty($pageSlug)) {
    // Find page with is_home = 1
    $pdo = get_db_connection();
    if ($pdo) {
        $stmt = $pdo->query("SELECT slug FROM pages WHERE is_home = 1 LIMIT 1");
        $homeSlug = $stmt->fetchColumn();
        if ($homeSlug) {
            $pageSlug = $homeSlug;
        } else {
            $pageSlug = 'home';
        }
    } else {
        $pageSlug = 'home';
    }
}

// Get Page Info
$pageInfo = get_page_info($pageSlug);

// If page does not exist or not published (and not logged in admin)
// Check login status for draft preview
session_start();
$isAdmin = isset($_SESSION['user_id']);

if (!$pageInfo) {
    // 404
    http_response_code(404);
} elseif ($pageInfo['status'] !== 'published' && !$isAdmin) {
    // 403 or 404
    http_response_code(404); // Hide drafts
    $pageInfo = null;
}

$content = $pageInfo ? json_decode($pageInfo['content'], true) : [];
$allPages = get_all_pages(); // Only published/valid pages for menu

// Meta Data
$metaTitle = ($pageInfo['meta_title'] ?? '') ?: ($pageInfo['title'] ?? 'Home');
$metaDesc = ($pageInfo['meta_description'] ?? '') ?: '';

?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle); ?> - <?php echo htmlspecialchars($siteTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">

    <!-- Base tag for pretty URLs -->
    <base href="/">
    <link rel="stylesheet" as="style" crossorigin
        href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Dynamic Theme CSS -->
    <style>
        :root {
            --color-primary: <?php echo !empty($settings['theme_primary']) ? $settings['theme_primary'] : '#0F172A'; ?>;
            --color-secondary: <?php echo !empty($settings['theme_secondary']) ? $settings['theme_secondary'] : '#2563EB'; ?>;
            --color-accent: <?php echo !empty($settings['theme_secondary']) ? $settings['theme_secondary'] : '#2563EB'; ?>;
            --color-bg: <?php echo !empty($settings['theme_bg']) ? $settings['theme_bg'] : '#F8FAFC'; ?>;
            --color-text: <?php echo !empty($settings['theme_text']) ? $settings['theme_text'] : '#334155'; ?>;
        }
    </style>
</head>

<body>

    <header style="background: var(--color-card-bg); border-bottom: 1px solid var(--color-border); padding: 15px 0;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <a href="/" style="font-weight: 800; font-size: 1.5rem; color: var(--color-primary); letter-spacing: -1px;">
                <?php echo htmlspecialchars($siteTitle); ?>
            </a>
            <div style="display:flex; align-items:center; gap: 20px;">
                <nav>
                    <ul style="display: flex; gap: 24px; align-items: center; list-style: none; margin: 0; padding: 0;">
                        <?php foreach ($allPages as $p): ?>
                            <li><a href="?page=<?php echo htmlspecialchars($p); ?>"
                                    class="<?php echo ($p === $pageSlug) ? 'active' : ''; ?>"
                                    style="text-transform: capitalize;"><?php echo htmlspecialchars($p); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <li><a href="admin.php"
                                style="background: var(--color-primary); color: var(--color-bg); padding: 8px 16px; border-radius: 6px; font-size: 0.9rem; text-decoration: none; font-weight: 600;">Admin</a>
                        </li>
                    </ul>
                </nav>
                <button id="themeToggle"
                    style="background:none; border:none; color:var(--color-text); font-size:1.2rem; cursor:pointer;"
                    title="Toggle Dark Mode">
                    🌙
                </button>
            </div>
        </div>
    </header>

    <script>
        // Dark Mode Logic (Overrides Dynamic CSS if toggled? Optional handling needed eventually)
        const toggleBtn = document.getElementById('themeToggle');
        const root = document.documentElement;

        // Check saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            root.setAttribute('data-theme', 'dark');
            toggleBtn.innerText = '☀️';
        }

        toggleBtn.addEventListener('click', () => {
            const currentTheme = root.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                root.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                toggleBtn.innerText = '🌙';
            } else {
                root.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                toggleBtn.innerText = '☀️';
            }
        });
    </script>

    <main>
        <?php if (!$pageInfo): ?>
            <div class="container" style="padding: 100px 0; text-align: center;">
                <h2>404 Not Found</h2>
                <p>페이지를 찾을 수 없거나 비공개 상태입니다.</p>
                <a href="/" class="btn">홈으로 돌아가기</a>
            </div>
        <?php else: ?>
            <?php render_blocks($content); ?>
        <?php endif; ?>
    </main>

    <footer
        style="background: #0f172a; color: #64748b; padding: 40px 0; border-top: 1px solid #1e293b; text-align: center;">
        <div class="container">
            <p><?php echo htmlspecialchars($footerText); ?></p>
        </div>
    </footer>

</body>

</html>