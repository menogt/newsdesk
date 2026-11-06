<?php
/**
 * Form handler. Every change to the database arrives here as a POST with a
 * CSRF token and an "action" name. Each action is mapped to a function and
 * the minimum access level it needs.
 */
require __DIR__ . '/actions/auth.php';
require __DIR__ . '/actions/articles.php';
require __DIR__ . '/actions/reader.php';
require __DIR__ . '/actions/admin.php';

function handle_action(): void
{
    if (!is_post()) return;

    if (!hash_equals(csrf_token(), post('csrf'))) {
        abort(403, 'This form expired', 'Your session timed out before the form was sent. Go back, reload the page and try again.');
    }

    $actions = [
        // action               => [handler,                 access]
        'register'              => ['action_register',        'guest'],
        'login'                 => ['action_login',           'guest'],
        'logout'                => ['action_logout',          'user'],
        'profile_save'          => ['action_profile_save',    'user'],
        'password_change'       => ['action_password_change', 'user'],
        'article_save'          => ['action_article_save',    'user'],
        'article_delete'        => ['action_article_delete',  'user'],
        'bookmark'              => ['action_bookmark',        'user'],
        'subscribe'             => ['action_subscribe',       'user'],
        'comment_save'          => ['action_comment_save',    'user'],
        'comment_delete'        => ['action_comment_delete',  'user'],
        'notifications_read'    => ['action_notifications_read', 'user'],
        'article_review'        => ['action_article_review',  'admin'],
        'article_flag'          => ['action_article_flag',    'admin'],
        'category_save'         => ['action_category_save',   'admin'],
        'category_delete'       => ['action_category_delete', 'admin'],
        'comment_review'        => ['action_comment_review',  'admin'],
        'comments_bulk'         => ['action_comments_bulk',   'admin'],
        'user_save'             => ['action_user_save',       'admin'],
        'user_status'           => ['action_user_status',     'admin'],
        'user_delete'           => ['action_user_delete',     'admin'],
    ];

    $name = post('action');
    if (!isset($actions[$name])) throw new ValidationError('Unknown action.');
    [$handler, $access] = $actions[$name];

    if ($access === 'user')  require_login();
    if ($access === 'admin') require_admin();

    $handler((int)post('id'));
}

/** Where to go after an action: a safe local "back" URL posted by the form, or a default page. */
function back(string $page, array $args = []): void
{
    $return = post('return');
    if ($return !== '' && preg_match('/^index\.php\?page=[a-z_]+(&[A-Za-z0-9_%=\-.&]*)?$/', $return)) {
        header('Location: ' . $return);
        exit;
    }
    redirect($page, $args);
}
