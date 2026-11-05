<?php
/**
 * Front controller. Every page is index.php?page=NAME.
 * 1. Handle a submitted form (if any).
 * 2. Check access for the requested page.
 * 3. Render the page inside the site or admin layout.
 */
ob_start();
require dirname(__DIR__) . '/app/bootstrap.php';
require ROOT_DIR . '/app/actions.php';

// page name => [template, access, title]
$routes = [
    'home'             => ['site/home',          'public', 'Campus news'],
    'news'             => ['site/news',          'public', 'All news'],
    'announcements'    => ['site/announcements', 'public', 'Announcements'],
    'article'          => ['site/article',       'public', 'Story'],
    'login'            => ['site/login',         'public', 'Sign in'],
    'register'         => ['site/register',      'public', 'Create account'],
    'feed'             => ['site/feed',          'user',   'My feed'],
    'saved'            => ['site/saved',         'user',   'Saved stories'],
    'following'        => ['site/following',     'user',   'Categories you follow'],
    'my_stories'       => ['site/my_stories',    'user',   'My stories'],
    'write'            => ['site/write',         'user',   'Write a story'],
    'notifications'    => ['site/notifications', 'user',   'Notifications'],
    'profile'          => ['site/profile',       'user',   'Profile'],
    'admin'            => ['admin/dashboard',    'admin',  'Dashboard'],
    'admin_articles'   => ['admin/articles',     'admin',  'Stories'],
    'admin_article'    => ['admin/article_form', 'admin',  'Edit story'],
    'admin_categories' => ['admin/categories',   'admin',  'Categories'],
    'admin_comments'   => ['admin/comments',     'admin',  'Comments'],
    'admin_users'      => ['admin/users',        'admin',  'Users'],
    'admin_reports'    => ['admin/reports',      'admin',  'Reports'],
];

$page = get_str('page', 'home');
$error = null;
$status = 200;
$template = null;

try {
    try {
        handle_action();
    } catch (ValidationError $ex) {
        $error = $ex->getMessage();
        http_response_code(422);
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $error = 'The change could not be saved. Please try again.';
        http_response_code(500);
    }

    if (!isset($routes[$page])) abort(404, 'Page not found', 'The page you were looking for does not exist or has moved.');
    [$template, $access, $title] = $routes[$page];
    if ($access === 'user')  require_login();
    if ($access === 'admin') require_admin();
    if ($page === 'login' || $page === 'register') {
        if (user()) redirect(is_admin() ? 'admin' : 'home');
    }

    // The CSV export streams a file instead of a page.
    if ($page === 'admin_reports' && get_str('export') === 'csv') {
        view('admin/reports_csv');
        exit;
    }

    // Render the page body first so it can set the title or an error status.
    ob_start();
    view($template, ['error' => $error]);
    $content = ob_get_clean();
} catch (HttpError $ex) {
    while (ob_get_level() > 1) ob_end_clean();
    [$errTitle, $errMessage] = explode('|', $ex->getMessage(), 2);
    http_response_code($ex->getCode());
    $title = $errTitle;
    $template = str_starts_with($page, 'admin') && is_admin() ? 'admin/x' : 'site/x';
    ob_start();
    view('site/error', ['code' => $ex->getCode(), 'heading' => $errTitle, 'message' => $errMessage]);
    $content = ob_get_clean();
} catch (PDOException $ex) {
    while (ob_get_level() > 1) ob_end_clean();
    error_log($ex->getMessage());
    http_response_code(500);
    $title = 'Something went wrong';
    $template = 'site/x';
    ob_start();
    view('site/error', ['code' => 500, 'heading' => 'Something went wrong', 'message' => 'The page could not load its data. Refresh, or check the database installation.']);
    $content = ob_get_clean();
}

$layout = str_starts_with($template, 'admin/') ? 'admin' : 'site';
view("layout/$layout", ['page' => $page, 'title' => $GLOBALS['page_title'] ?? $title, 'content' => $content, 'error' => $error, 'flash' => take_flash()]);
