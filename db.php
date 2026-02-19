<?php

/**
 * Get Database Connection
 *
 * @return PDO|null
 */
function get_db_connection()
{
    static $pdo = null;

    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db';
        $db = getenv('DB_NAME') ?: 'minimal_cms';
        $user = getenv('DB_USER') ?: 'cms_user';
        $pass = getenv('DB_PASS') ?: 'cms_password';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // For setup.php, we might need to connect without DB if it doesn't exist yet? 
            // Actually docker-compose creates the DB.
            // If connection fails, return null or throw
            return null;
        }
    }

    return $pdo;
}
