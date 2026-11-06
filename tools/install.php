<?php
/**
 * Command-line installer (alternative to importing in phpMyAdmin).
 *   php tools/install.php          -> tables + demo data (database/demo.sql)
 *   php tools/install.php --empty  -> tables only (database/schema.sql)
 * WARNING: drops and recreates the NewsDesk tables in the configured database.
 */
if (PHP_SAPI !== 'cli') exit("Run this from the command line.\n");
$root = dirname(__DIR__);
$config = require (is_file("$root/config.php") ? "$root/config.php" : "$root/config.example.php");
$file = in_array('--empty', $argv, true) ? "$root/database/schema.sql" : "$root/database/demo.sql";

echo "This will DELETE and recreate the NewsDesk tables in '{$config['database']}'. Type yes to continue: ";
if (trim((string)fgets(STDIN)) !== 'yes') exit("Cancelled.\n");

$db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_MULTI_STATEMENTS => true]);
$db->exec(file_get_contents($file));
echo "Installed from " . basename($file) . ".\n";
