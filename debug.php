<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';

echo "<h1>Debug Info</h1>";

$page = $_GET['page'] ?? 'home';
echo "Requested Page: <strong>" . htmlspecialchars($page) . "</strong><br>";

$sanitized = preg_replace('/[^a-z0-9-_]/i', '', $page);
echo "Sanitized Slug: <strong>" . htmlspecialchars($sanitized) . "</strong><br>";

$filePath = __DIR__ . "/data/pages/{$sanitized}.json";
echo "Target File Path: " . $filePath . "<br>";

echo "File Exists: " . (file_exists($filePath) ? "<span style='color:green'>YES</span>" : "<span style='color:red'>NO</span>") . "<br>";

if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    echo "File Content Raw: <pre>" . htmlspecialchars($content) . "</pre>";

    $json = json_decode($content, true);
    echo "JSON Decode Result: ";
    if ($json === null) {
        echo "<span style='color:red'>NULL (Error: " . json_last_error_msg() . ")</span>";
    } else {
        echo "<span style='color:green'>Valid Array (" . count($json) . " items)</span>";
    }
}

echo "<hr>";
echo "<h2>Test get_page_content()</h2>";
$blocks = get_page_content($page);
echo "Blocks count: " . count($blocks) . "<br>";
var_dump($blocks);
