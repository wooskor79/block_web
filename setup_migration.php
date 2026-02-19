<?php
// setup_migration.php
// This script updates the database schema for the new features.

require_once 'db.php';

$pdo = get_db_connection();

if (!$pdo) {
    die("Database connection failed. Please ensure the database container is running.");
}

$start = microtime(true);
echo "Starting migration...<br>";

try {
    // 1. Update 'pages' table
    // MariaDB 10.2+ supports ADD COLUMN IF NOT EXISTS

    $alterQueries = [
        "ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255) DEFAULT ''",
        "ADD COLUMN IF NOT EXISTS meta_description TEXT",
        "ADD COLUMN IF NOT EXISTS status ENUM('published', 'draft', 'trash') DEFAULT 'published'",
        "ADD COLUMN IF NOT EXISTS is_home TINYINT(1) DEFAULT 0"
    ];

    foreach ($alterQueries as $q) {
        // We run them one by one to catch individual errors or skip if supported
        try {
            $pdo->exec("ALTER TABLE pages " . $q);
            echo "Executed: ALTER TABLE pages $q <br>";
        } catch (PDOException $e) {
            // If syntax error (older MariaDB?), we might need to check if column exists manually.
            // But assuming 10.11 as per docker-compose.
            echo "Warning: " . $e->getMessage() . " (Query: $q) <br>";
        }
    }

    // Add Indexes (IF NOT EXISTS is not standard for INDEX in ALTER TABLE in all versions, 
    // but MariaDB 10.5+ supports CREATE OR REPLACE INDEX or IF NOT EXISTS in CREATE INDEX)
    // Let's use robust approach: generic try-catch for indexes
    try {
        $pdo->exec("CREATE INDEX idx_status ON pages (status)");
        echo "Created index idx_status.<br>";
    } catch (Exception $e) { /* Ignore if exists */
    }

    try {
        $pdo->exec("CREATE INDEX idx_is_home ON pages (is_home)");
        echo "Created index idx_is_home.<br>";
    } catch (Exception $e) { /* Ignore if exists */
    }

    // 2. Create 'settings' table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        `key` VARCHAR(50) UNIQUE NOT NULL,
        `value` TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Created settings table.<br>";

    // 3. Insert default settings
    $defaults = [
        'site_title' => 'Modern Tech & Minimal Life',
        'footer_text' => '© 2026 Block Web. All rights reserved.',
        'logo_url' => ''
    ];

    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
    // Note: We use ON DUPLICATE KEY UPDATE to ensure values are reset? No, better to keep existing if they changed.
    // Actually, we should only Insert IGNORE or similar.
    // Let's use INSERT IGNORE to only set defaults if missing.
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (`key`, `value`) VALUES (?, ?)");

    foreach ($defaults as $k => $v) {
        $stmt->execute([$k, $v]);
    }
    echo "Inserted default settings.<br>";

    echo "Migration completed successfully in " . (microtime(true) - $start) . "s.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>