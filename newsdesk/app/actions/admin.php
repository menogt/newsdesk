<?php
/** Administrator-only actions: reviewing, categories, moderation and users. */

function action_article_review(int $id): void
{
    $article = one('SELECT * FROM articles WHERE id = ?', [$id]);
    if (!$article) throw new ValidationError('This story no longer exists.');
    $decision = post('decision');
    $note = post('review_note');
    if (!in_array($decision, ['approved', 'rejected'], true)) throw new ValidationError('Choose approve or reject.');
    require_length($note, 0, 300, 'Feedback');
    if ($decision === 'rejected' && $note === '') throw new ValidationError('Tell the author why the story was rejected so they can fix it.');

    query("UPDATE articles SET status = ?, review_note = ?,
           published_at = CASE WHEN ? = 'approved' THEN COALESCE(published_at, NOW()) ELSE NULL END,
           featured = IF(? = 'approved', featured, 0), breaking = IF(? = 'approved', breaking, 0)
           WHERE id = ?", [$decision, $note, $decision, $decision, $decision, $id]);

    $link = url('article', ['id' => $id]);
    if ($decision === 'approved') {
        if ($article['published_at'] === null) announce_publication($article);
        elseif ((int)$article['author_id'] !== user_id()) notify((int)$article['author_id'], 'Your changes to “' . $article['title'] . '” are live.', $link);
        flash('Published “' . $article['title'] . '”.');
    } else {
        notify((int)$article['author_id'], 'Your story “' . $article['title'] . '” needs changes: ' . $note, url('my_stories', ['status' => 'rejected']));
        flash('Rejected “' . $article['title'] . '”. The author has been notified.', 'info');
    }
    back('admin_articles', ['status' => 'pending']);
}

/** Toggle Featured or Breaking from the stories table. */
function action_article_flag(int $id): void
{
    $flag = post('flag');
    if (!in_array($flag, ['featured', 'breaking'], true)) throw new ValidationError('Unknown setting.');
    $article = one("SELECT id, title, status, $flag AS current FROM articles WHERE id = ?", [$id]);
    if (!$article) throw new ValidationError('This story no longer exists.');
    if ($article['status'] !== 'approved') throw new ValidationError('Publish the story before marking it as ' . $flag . '.');
    query("UPDATE articles SET $flag = ? WHERE id = ?", [$article['current'] ? 0 : 1, $id]);
    flash(($article['current'] ? 'Removed from ' : 'Added to ') . ($flag === 'featured' ? 'featured stories' : 'breaking news') . '.');
    back('admin_articles');
}

function action_category_save(int $id): void
{
    $name = post('name');
    $description = post('description');
    $color = post('color', 'green');
    require_length($name, 2, 60, 'Category name');
    require_length($description, 0, 200, 'Description');
    if (!in_array($color, ['green', 'blue', 'orange', 'purple', 'teal', 'rose', 'slate'], true)) throw new ValidationError('Choose a colour.');
    if (one('SELECT id FROM categories WHERE name = ? AND id <> ?', [$name, $id])) throw new ValidationError('A category called “' . $name . '” already exists.');

    if ($id) query('UPDATE categories SET name = ?, description = ?, color = ? WHERE id = ?', [$name, $description, $color, $id]);
    else query('INSERT INTO categories (name, description, color) VALUES (?, ?, ?)', [$name, $description, $color]);
    flash($id ? 'Category updated.' : 'Category added.');
    redirect('admin_categories');
}

function action_category_delete(int $id): void
{
    $count = (int)scalar('SELECT COUNT(*) FROM articles WHERE category_id = ?', [$id]);
    if ($count) throw new ValidationError("This category still has $count " . ($count === 1 ? 'story' : 'stories') . '. Move them to another category first.');
    query('DELETE FROM categories WHERE id = ?', [$id]);
    flash('Category deleted.');
    redirect('admin_categories');
}

function action_comment_review(int $id): void
{
    $decision = post('decision');
    if (!in_array($decision, ['approved', 'rejected'], true)) throw new ValidationError('Choose approve or reject.');
    $comment = one('SELECT c.*, a.title FROM comments c JOIN articles a ON a.id = c.article_id WHERE c.id = ?', [$id]);
    if (!$comment) throw new ValidationError('This comment was already deleted.');
    query('UPDATE comments SET status = ? WHERE id = ?', [$decision, $id]);
    if ($decision === 'approved' && $comment['status'] !== 'approved' && (int)$comment['user_id'] !== user_id()) {
        notify((int)$comment['user_id'], 'Your comment on “' . $comment['title'] . '” is now visible.', url('article', ['id' => $comment['article_id']]) . '#comments');
    }
    flash($decision === 'approved' ? 'Comment approved.' : 'Comment rejected.', $decision === 'approved' ? 'success' : 'info');
    back('admin_comments');
}

function action_comments_bulk(int $id): void
{
    $decision = post('decision');
    $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
    if (!$ids) throw new ValidationError('Select at least one comment.');
    if (!in_array($decision, ['approved', 'rejected', 'delete'], true)) throw new ValidationError('Choose an action.');
    foreach ($ids as $commentId) {
        if ($decision === 'delete') { query('DELETE FROM comments WHERE id = ?', [$commentId]); continue; }
        $comment = one('SELECT c.*, a.title FROM comments c JOIN articles a ON a.id = c.article_id WHERE c.id = ?', [$commentId]);
        if (!$comment) continue;
        query('UPDATE comments SET status = ? WHERE id = ?', [$decision, $commentId]);
        if ($decision === 'approved' && $comment['status'] !== 'approved' && (int)$comment['user_id'] !== user_id()) {
            notify((int)$comment['user_id'], 'Your comment on “' . $comment['title'] . '” is now visible.', url('article', ['id' => $comment['article_id']]) . '#comments');
        }
    }
    $n = count($ids);
    flash(plural($n, 'comment') . ' ' . ($decision === 'delete' ? 'deleted' : $decision) . '.');
    back('admin_comments');
}

/** Create a user, or change an existing user's name, email and role. */
function action_user_save(int $id): void
{
    $name = post('name');
    $email = strtolower(post('email'));
    $role = post('role', 'student');
    require_length($name, 2, 80, 'Full name');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new ValidationError('Enter a valid email address.');
    if (!in_array($role, ['student', 'admin'], true)) throw new ValidationError('Choose a role.');
    if (one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, $id])) throw new ValidationError('Another account already uses this email.');
    if ($id === user_id() && $role !== 'admin') throw new ValidationError('You cannot remove your own administrator access.');

    if ($id) {
        if (!one('SELECT id FROM users WHERE id = ?', [$id])) throw new ValidationError('This user no longer exists.');
        query('UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?', [$name, $email, $role, $id]);
        flash('User updated.');
    } else {
        $password = (string)($_POST['password'] ?? '');
        validate_password($password);
        query('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        flash('Account created for ' . $name . '. Share the temporary password with them privately.');
    }
    redirect('admin_users');
}

function action_user_status(int $id): void
{
    if ($id === user_id()) throw new ValidationError('You cannot block your own account.');
    $account = one('SELECT name, status FROM users WHERE id = ?', [$id]);
    if (!$account) throw new ValidationError('This user no longer exists.');
    $new = $account['status'] === 'active' ? 'blocked' : 'active';
    query('UPDATE users SET status = ? WHERE id = ?', [$new, $id]);
    flash($account['name'] . ($new === 'blocked' ? ' is blocked and can no longer sign in.' : ' can sign in again.'), $new === 'blocked' ? 'info' : 'success');
    back('admin_users');
}

function action_user_delete(int $id): void
{
    if ($id === user_id()) throw new ValidationError('You cannot delete your own account.');
    $account = one('SELECT name FROM users WHERE id = ?', [$id]);
    if (!$account) throw new ValidationError('This user was already deleted.');
    $stories = (int)scalar('SELECT COUNT(*) FROM articles WHERE author_id = ?', [$id]);
    if ($stories) throw new ValidationError($account['name'] . ' has written ' . plural($stories, 'story', 'stories') . '. Block the account instead, or delete those stories first.');
    query('DELETE FROM users WHERE id = ?', [$id]);   // comments, bookmarks, follows and notifications cascade
    flash('Deleted ' . $account['name'] . '’s account.');
    redirect('admin_users');
}
