<?php
require_once __DIR__ . '/db.php';

function get_page_content($slug)
{
    // Sanitize
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);
    if (empty($slug))
        $slug = 'home';

    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT content FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            $json = $stmt->fetchColumn();
            return $json ? json_decode($json, true) : [];
        } catch (PDOException $e) {
            // Table not found -> Redirect to setup is handled in index.php usually,
            // but kept here for safety in other contexts.
            if ($e->getCode() == '42S02') {
                // If this function is called directly before index.php checks, redirect.
                if (!headers_sent()) {
                    header("Location: setup.php");
                    exit;
                }
            }
            return [];
        }
    } else {
        return [];
    }
}

function get_page_info($slug)
{
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);
    if (empty($slug))
        $slug = 'home';

    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
            $stmt->execute([$slug]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$res)
                return null;

            // Ensure content is array if needed, but here we return raw row usually
            // but for safety let's just return row
            return $res;
        } catch (Exception $e) {
            return null;
        }
    }
    return null;
}

function get_all_pages()
{
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            // Return only active pages for menu? Or all?
            // Usually for menu we want published pages.
            // But admin needs all.
            // This function seems used by admin.php for dropdown.
            // Let's return all non-trash pages for now.
            $stmt = $pdo->query("SELECT slug FROM pages WHERE status != 'trash' ORDER BY slug ASC");
            $pages = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Move 'home' to the top
            $key = array_search('home', $pages);
            if ($key !== false) {
                unset($pages[$key]);
                array_unshift($pages, 'home');
            }
            return array_values($pages);
        } catch (Exception $e) {
            return [];
        }
    }
    return [];
}

function get_all_pages_full()
{
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT slug, title, status, is_home FROM pages WHERE status != 'trash' ORDER BY is_home DESC, slug ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    return [];
}

function get_settings()
{
    $pdo = get_db_connection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
            return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // returns [key => value]
        } catch (Exception $e) {
            return [];
        }
    }
    return [];
}

function create_page($slug, $title)
{
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);
    if (empty($slug))
        return false;

    $pdo = get_db_connection();
    if (!$pdo)
        return false;

    // Check if exists (including trash?)
    // If trash, maybe restore? For now just fail.
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetchColumn() > 0)
        return false;

    // Default Template
    $defaultContent = json_encode([
        [
            "id" => "block_" . time(),
            "type" => "hero",
            "content" => [
                "title" => $title,
                "subtitle" => "New Page Created via DB",
                "buttonText" => "Go Home",
                "buttonLink" => "?page=home"
            ]
        ],
        [
            "id" => "block_" . (time() + 1),
            "type" => "text",
            "content" => [
                "heading" => "Start Editing",
                "text" => "This content is stored in the database."
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("INSERT INTO pages (slug, title, content, status) VALUES (?, ?, ?, 'draft')");
    return $stmt->execute([$slug, $title, $defaultContent]);
}

function render_blocks($blocks)
{
    if (empty($blocks) || !is_array($blocks))
        return;

    foreach ($blocks as $block) {
        if (!isset($block['type']) || !isset($block['content'])) {
            continue; // Skip invalid blocks
        }

        $type = $block['type'];
        $content = $block['content'];

        // Prevent directory traversal
        $type = preg_replace('/[^a-z0-9-_]/i', '', $type);
        $template = __DIR__ . "/templates/block-{$type}.php";

        if (file_exists($template)) {
            include $template;
        } else {
            echo "<!-- Block template not found: " . htmlspecialchars($type) . " -->";
        }
    }
}
