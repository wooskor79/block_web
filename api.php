<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php'; // Ensure pdo is available

// Authentication Check: Only allow logged-in users to use API
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$action = $data['action'] ?? '';
$pdo = get_db_connection();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($action === 'save_page') {
    $page = $data['page'] ?? 'home';
    $content = $data['content'] ?? [];
    $meta_title = $data['meta_title'] ?? '';
    $meta_description = $data['meta_description'] ?? '';
    $status = $data['status'] ?? 'published'; // published, draft, trash

    // Validate page name
    $page = preg_replace('/[^a-z0-9\-_]/i', '', $page);
    if (empty($page))
        $page = 'home';

    $jsonContent = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Check if page exists to determine INSERT or UPDATE
    $check = $pdo->prepare("SELECT id FROM pages WHERE slug = ?");
    $check->execute([$page]);

    if ($check->fetch()) {
        // Update
        $stmt = $pdo->prepare("UPDATE pages SET content = ?, meta_title = ?, meta_description = ?, status = ? WHERE slug = ?");
        if ($stmt->execute([$jsonContent, $meta_title, $meta_description, $status, $page])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update page']);
        }
    } else {
        // Insert
        // Note: admin.php usually calls create_page first, but save_page might be called for a new page if logic changes.
        // For now, we assume it exists if we are editing it, or we insert it if it's new (but create_page handles creation).
        // Let's support upsert just in case.
        $stmt = $pdo->prepare("INSERT INTO pages (slug, title, content, meta_title, meta_description, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$page, ucfirst($page), $jsonContent, $meta_title, $meta_description, $status])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save new page']);
        }
    }

} elseif ($action === 'create_page') {
    $title = $data['title'] ?? '';
    $slug = $data['slug'] ?? '';

    // Add regex validation for slug before passing to create_page (optional but good practice)
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);

    if (empty($title) || empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Title and Slug are required']);
        exit;
    }

    if (create_page($slug, $title)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create page (Slug might exist)']);
    }

} elseif ($action === 'delete_page') {
    $slug = $data['slug'] ?? '';
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);

    if ($slug === 'home') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete home page']);
        exit;
    }

    // Soft delete (Trash)
    $stmt = $pdo->prepare("UPDATE pages SET status = 'trash' WHERE slug = ?");
    if ($stmt->execute([$slug])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete page']);
    }

} elseif ($action === 'restore_page') {
    $slug = $data['slug'] ?? '';
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);

    $stmt = $pdo->prepare("UPDATE pages SET status = 'draft' WHERE slug = ?");
    if ($stmt->execute([$slug])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore page']);
    }

} elseif ($action === 'set_home') {
    $slug = $data['slug'] ?? '';
    $slug = preg_replace('/[^a-z0-9\-_]/i', '', $slug);

    if (empty($slug)) {
        echo json_encode(['success' => false, 'message' => 'Slug required']);
        exit;
    }

    // Transaction to ensure atomicity
    $pdo->beginTransaction();
    try {
        // Reset old home
        $pdo->exec("UPDATE pages SET is_home = 0");
        // Set new home
        $stmt = $pdo->prepare("UPDATE pages SET is_home = 1 WHERE slug = ?");
        $stmt->execute([$slug]);
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($action === 'save_settings') {
    $settings = $data['settings'] ?? [];

    try {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");

        foreach ($settings as $key => $value) {
            // Simple key validation
            if (preg_match('/^[a-z0-9_]+$/', $key)) {
                $stmt->execute([$key, $value]);
            }
        }
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} elseif ($action === 'get_settings') {
    try {
        $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        echo json_encode(['success' => true, 'settings' => $settings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
