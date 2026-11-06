<?php
/**
 * Create an administrator from the command line (for a clean install without demo data).
 *   NEWSDESK_ADMIN_PASSWORD='your-password' php tools/create_admin.php "Full Name" you@example.com
 */
if (PHP_SAPI !== 'cli') exit("Run this from the command line.\n");
[$_, $name, $email] = $argv + [null, '', ''];
$password = getenv('NEWSDESK_ADMIN_PASSWORD') ?: '';
if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    exit("Usage: NEWSDESK_ADMIN_PASSWORD='at-least-8-chars' php tools/create_admin.php \"Full Name\" email@example.com\n");
}
$root = dirname(__DIR__);
$config = require (is_file("$root/config.php") ? "$root/config.php" : "$root/config.example.php");
$db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')")->execute([$name, strtolower($email), password_hash($password, PASSWORD_DEFAULT)]);
echo "Administrator created: $email\n";
