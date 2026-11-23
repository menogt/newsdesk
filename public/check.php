<?php
/**
 * Setup checker. Open /public/check.php to see what is wrong with the installation.
 * DELETE THIS FILE once the site works: it shows server details.
 */
header('Content-Type: text/plain; charset=utf-8');
$root = dirname(__DIR__);
$ok = fn($t, $pass, $hint = '') => print(($pass ? '[OK]   ' : '[FAIL] ') . $t . ($pass || !$hint ? '' : "\n       -> $hint") . "\n");

echo "NewsDesk setup check\n====================\n";
$ok('PHP version ' . PHP_VERSION . ' (need 8.0+)', version_compare(PHP_VERSION, '8.0', '>='), 'Choose PHP 8.x in your hosting control panel.');
foreach (['pdo_mysql', 'mbstring', 'fileinfo', 'session'] as $ext) $ok("PHP extension $ext", extension_loaded($ext), "Enable $ext in your hosting PHP settings.");
$ok('config.php found next to the app folder', is_file("$root/config.php"), "Create $root/config.php (copy config.example.php).");
$ok('app folder found', is_file("$root/app/bootstrap.php"), 'Upload the app, views and public folders together, keeping the structure.');
$ok('views folder found', is_dir("$root/views"), 'Upload the views folder.');
$ok('uploads folder writable', is_writable(__DIR__ . '/uploads/articles'), 'Set public/uploads/articles permissions to 755.');

if (is_file("$root/config.php")) {
    $c = require "$root/config.php";
    echo "\nDatabase: host={$c['host']} port={$c['port']} db={$c['database']} user={$c['username']}\n";
    try {
        $db = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $ok('Connected to MySQL', true);
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $need = ['users', 'categories', 'articles', 'comments', 'bookmarks', 'subscriptions', 'article_views', 'notifications', 'login_attempts'];
        $missing = array_diff($need, $tables);
        $ok('All 9 tables exist', !$missing, 'Missing: ' . implode(', ', $missing) . '. Import database/demo.sql in phpMyAdmin (into THIS database).');
        if (!$missing) $ok('Demo data present (' . $db->query('SELECT COUNT(*) FROM articles')->fetchColumn() . ' stories)', $db->query('SELECT COUNT(*) FROM users')->fetchColumn() > 0, 'Import database/demo.sql.');
    } catch (PDOException $e) {
        $ok('Connected to MySQL', false, $e->getMessage() . "\n       Check host, database name, username and password in config.php against your control panel.");
    }
}
echo "\nWhen everything says OK, open index.php and DELETE check.php.\n";
