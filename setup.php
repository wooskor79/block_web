<?php
session_start();
require_once __DIR__ . '/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? 'admin');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "Username and Password are required.";
    } else {
        $pdo = get_db_connection();
        if ($pdo) {
            try {
                // Create Tables
                $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");

                $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    slug VARCHAR(100) NOT NULL UNIQUE,
                    title VARCHAR(255) NOT NULL,
                    content MEDIUMTEXT,
                    meta_title VARCHAR(255) DEFAULT '',
                    meta_description TEXT,
                    status ENUM('published', 'draft', 'trash') DEFAULT 'published',
                    is_home TINYINT(1) DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");

                // Create Settings Table
                $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(50) UNIQUE NOT NULL,
                    `value` TEXT,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )");

                // Default Settings
                $defaults = [
                    'site_title' => 'Modern Tech & Minimal Life',
                    'footer_text' => '© 2026 Block Web. All rights reserved.',
                    'logo_url' => ''
                ];
                $stmtSet = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");
                foreach ($defaults as $k => $v) {
                    $stmtSet->execute([$k, $v]);
                }

                // Check if admin exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                $hash = password_hash($password, PASSWORD_DEFAULT);

                if ($user) {
                    // Update password
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
                    $stmt->execute([$hash, $username]);
                    $message = "Admin password updated successfully.";
                } else {
                    // Create user
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                    $stmt->execute([$username, $hash]);
                    $message = "Installation successful! Admin user created.";
                }

                // Check if we need to insert default home page
                $stmt = $pdo->query("SELECT count(*) FROM pages");
                if ($stmt->fetchColumn() == 0) {
                    $defaultContent = json_encode([
                        [
                            "id" => "block_init",
                            "type" => "hero",
                            "content" => [
                                "title" => "Welcome to Minimal CMS",
                                "subtitle" => "Dockerized & Database Powered",
                                "buttonText" => "Get Started",
                                "buttonLink" => "#"
                            ]
                        ]
                    ], JSON_UNESCAPED_UNICODE);

                    $stmt = $pdo->prepare("INSERT INTO pages (slug, title, content, is_home, status) VALUES ('home', 'Home', ?, 1, 'published')");
                    $stmt->execute([$defaultContent]);
                }

            } catch (PDOException $e) {
                $message = "Database Error: " . $e->getMessage();
            }
        } else {
            $message = "Could not connect to database. Check docker-compose.yml settings.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Setup</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: #f1f5f9;
        }

        .setup-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .alert {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="setup-card">
        <h1 style="text-align: center; margin-bottom: 30px; font-size: 1.5rem;">CMS Installation</h1>

        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'successful') !== false ? 'success' : 'alert'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php if (strpos($message, 'successful') !== false): ?>
                <div style="text-align:center; margin-top:20px;">
                    <a href="admin.php" class="btn">Go to Admin Login</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (strpos($message, 'successful') === false): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" value="admin" required>
                </div>
                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="password" required placeholder="Enter a strong password">
                </div>
                <button type="submit" class="btn" style="width: 100%;">Install & Create Admin</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>