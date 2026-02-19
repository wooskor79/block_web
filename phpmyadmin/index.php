<?php
// Redirect to phpMyAdmin on port 8081
// Users accessing /phpmyadmin/index.php will be redirected
$host = $_SERVER['HTTP_HOST'];
$hostWithoutPort = explode(':', $host)[0];
header("Location: http://{$hostWithoutPort}:8081");
exit;
?>