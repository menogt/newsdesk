<?php
/** Reader features: bookmarks, category following, comments and notifications. */

function action_bookmark(int $id): void
{
    if (!one("SELECT id FROM articles WHERE id = ? AND status = 'approved'", [$id])) throw new ValidationError('This story is no longer available.');
    $saved = one('SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?', [user_id(), $id]);
    if ($saved) {
        query('DELETE FROM bookmarks WHERE user_id = ? AND article_id = ?', [user_id(), $id]);
        flash('Removed from saved stories.', 'info');
    } else {
        query('INSERT IGNORE INTO bookmarks (user_id, article_id) VALUES (?, ?)', [user_id(), $id]);
        flash('Saved. Find it any time under Saved.');
    }
    back('article', ['id' => $id]);
}

function action_subscribe(int $id): void
{
    $category = one('SELECT name FROM categories WHERE id = ?', [$id]);
    if (!$category) throw new ValidationError('That category no longer exists.');
    $following = one('SELECT 1 FROM subscriptions WHERE user_id = ? AND category_id = ?', [user_id(), $id]);
    if ($following) {
        query('DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?', [user_id(), $id]);
        flash('You unfollowed ' . $category['name'] . '.', 'info');
    } else {
        query('INSERT IGNORE INTO subscriptions (user_id, category_id) VALUES (?, ?)', [user_id(), $id]);
        flash('You follow ' . $category['name'] . '. New stories will appear in your feed and notifications.');
    }
    back('following');
}

function action_comment_save(int $id): void
{
    $articleId = (int)post('article_id');
    $body = post('body');
    require_length($body, 2, 1000, 'Comment');
    if (!one("SELECT id FROM articles WHERE id = ? AND status = 'approved'", [$articleId])) throw new ValidationError('Comments are only open on published stories.');

    $status = is_admin() ? 'approved' : 'pending';
    if ($id) {
        $comment = one('SELECT * FROM comments WHERE id = ? AND article_id = ?', [$id, $articleId]);
        if (!$comment || (int)$comment['user_id'] !== user_id()) abort(403, 'Not your comment', 'You can only edit your own comments.');
        query('UPDATE comments SET body = ?, status = ? WHERE id = ?', [$body, $status, $id]);
    } else {
        query('INSERT INTO comments (article_id, user_id, body, status) VALUES (?, ?, ?, ?)', [$articleId, user_id(), $body, $status]);
    }
    flash(is_admin() ? 'Comment posted.' : 'Comment sent. It will appear once a moderator approves it.');
    header('Location: ' . url('article', ['id' => $articleId]) . '#comments');
    exit;
}

function action_comment_delete(int $id): void
{
    $comment = one('SELECT * FROM comments WHERE id = ?', [$id]);
    if (!$comment) throw new ValidationError('This comment was already deleted.');
    if (!is_admin() && (int)$comment['user_id'] !== user_id()) abort(403, 'Not your comment', 'You can only delete your own comments.');
    query('DELETE FROM comments WHERE id = ?', [$id]);
    flash('Comment deleted.');
    back('article', ['id' => $comment['article_id']]);
}

function action_notifications_read(int $id): void
{
    if ($id) query('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?', [$id, user_id()]);
    else query('UPDATE notifications SET is_read = 1 WHERE user_id = ?', [user_id()]);

    // Opening a single notification takes the reader to its story.
    if ($id) {
        $link = scalar('SELECT link FROM notifications WHERE id = ? AND user_id = ?', [$id, user_id()]);
        if ($link && str_starts_with($link, 'index.php?page=')) { header('Location: ' . $link); exit; }
    }
    flash('All notifications marked as read.', 'info');
    redirect('notifications');
}
