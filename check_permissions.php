<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== Permission & Environment Check ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current User: " . get_current_user() . " (UID: " . getmyuid() . ")\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$pagesDir = $baseDir . '/data/pages';
$homeFile = $pagesDir . '/home.json';

echo "Path: $baseDir\n\n";

// Check data directory
echo "Checking /data directory...\n";
if (!is_dir($dataDir)) {
    echo "[MISSING] /data directory does not exist. Attempting to create...\n";
    if (@mkdir($dataDir, 0777, true)) {
        echo "[FIXED] Created /data directory.\n";
    } else {
        echo "[ERROR] Failed to create /data directory. Please create it manually.\n";
    }
} else {
    echo "[OK] /data directory exists.\n";
    if (is_writable($dataDir)) {
        echo "[OK] /data is writable.\n";
    } else {
        echo "[WARNING] /data is NOT writable. Attempting chmod...\n";
        if (@chmod($dataDir, 0777)) {
            echo "[FIXED] Changed permissions for /data.\n";
        } else {
            echo "[ERROR] Failed to change permissions for /data.\n";
        }
    }
}

echo "\nChecking /data/pages directory...\n";
if (!is_dir($pagesDir)) {
    echo "[MISSING] /data/pages directory does not exist. Attempting to create...\n";
    if (@mkdir($pagesDir, 0777, true)) {
        echo "[FIXED] Created /data/pages directory.\n";
    } else {
        echo "[ERROR] Failed to create /data/pages directory. Please create it manually inside /data.\n";
    }
} else {
    echo "[OK] /data/pages directory exists.\n";
    if (is_writable($pagesDir)) {
        echo "[OK] /data/pages is writable.\n";
    } else {
        echo "[WARNING] /data/pages is NOT writable. Attempting chmod...\n";
        if (@chmod($pagesDir, 0777)) {
            echo "[FIXED] Changed permissions for /data/pages.\n";
        } else {
            echo "[ERROR] Failed to change permissions for /data/pages.\n";
        }
    }
}

echo "\nChecking home.json...\n";
if (!file_exists($homeFile)) {
    echo "[MISSING] home.json does not exist. Attempting to create default...\n";

    $defaultContent = [
        [
            "id" => "block_init",
            "type" => "hero",
            "content" => [
                "title" => "Welcome to Minimal CMS",
                "subtitle" => "Installation Successful!",
                "buttonText" => "Edit Page",
                "buttonLink" => "admin.php"
            ]
        ]
    ];

    if (@file_put_contents($homeFile, json_encode($defaultContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo "[FIXED] Created default home.json.\n";
    } else {
        echo "[ERROR] Failed to create home.json. Check directory permissions.\n";
    }
} else {
    echo "[OK] home.json exists.\n";
    if (is_writable($homeFile)) {
        echo "[OK] home.json is writable.\n";
    } else {
        echo "[WARNING] home.json is NOT writable. Attempting chmod...\n";
        if (@chmod($homeFile, 0666)) {
            echo "[FIXED] Changed permissions for home.json.\n";
        } else {
            echo "[ERROR] Failed to change permissions for home.json.\n";
        }
    }
}

echo "\n=== Check Complete ===\n";
