<?php
/** Creating, editing and deleting stories (students and administrators). */

function action_article_save(int $id): void
{
    $existing = $id ? one('SELECT * FROM articles WHERE id = ?', [$id]) : null;
    if ($id && !$existing) throw new ValidationError('This story no longer exists.');
    if ($existing && !is_admin() && (int)$existing['author_id'] !== user_id()) {
        abort(403, 'Not your story', 'You can only edit stories you wrote.');
    }

    $title    = post('title');
    $summary  = post('summary');
    $body     = str_replace("\r\n", "\n", post('body'));
    $category = (int)post('category_id');
    $kind     = post('kind', 'news');
    $expires  = post('expires_at');

    require_length($title, 5, 180, 'Title');
    require_length($summary, 15, 300, 'Summary');
    require_length($body, 40, 20000, 'Story text');
    if (!one('SELECT id FROM categories WHERE id = ?', [$category])) throw new ValidationError('Choose a category.');
    if (!in_array($kind, ['news', 'announcement'], true)) throw new ValidationError('Choose news or announcement.');
    if ($expires !== '') {
        if (!valid_date($expires)) throw new ValidationError('Enter the expiry date as a full date.');
        if ($kind !== 'announcement') $expires = '';
        elseif ($expires < date('Y-m-d') && (!$existing || $existing['expires_at'] !== $expires)) throw new ValidationError('The expiry date cannot be in the past.');
    }

    // Students can never publish directly; every edit goes back to review.
    if (is_admin()) {
        $status = post('status', 'approved');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) throw new ValidationError('Choose a valid status.');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $breaking = isset($_POST['breaking']) ? 1 : 0;
        $note     = $existing['review_note'] ?? '';
    } else {
        $status = 'pending';
        $featured = 0;
        $breaking = 0;
        $note = '';
    }
    // Keep the original publication date when a published story is edited and re-reviewed.
    $publishedAt = match ($status) {
        'approved' => $existing['published_at'] ?? date('Y-m-d H:i:s'),
        'pending'  => $existing['published_at'] ?? null,
        default    => null,
    };

    // Image: new upload replaces the old one; "remove" clears it.
    $image = $existing['image'] ?? '';
    $uploaded = store_uploaded_image('image');
    if ($uploaded !== null) {
        delete_image_file($image);
        $image = $uploaded;
    } elseif (isset($_POST['remove_image'])) {
        delete_image_file($image);
        $image = '';
    }

    $values = [$category, $title, $summary, $body, $image, $kind, $status, $featured, $breaking, $expires ?: null, $note, $publishedAt];
    if ($existing) {
        query('UPDATE articles SET category_id = ?, title = ?, summary = ?, body = ?, image = ?, kind = ?, status = ?, featured = ?,
               breaking = ?, expires_at = ?, review_note = ?, published_at = ? WHERE id = ?', array_merge($values, [$id]));
    } else {
        query('INSERT INTO articles (category_id, title, summary, body, image, kind, status, featured, breaking, expires_at, review_note,
               published_at, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_merge($values, [user_id()]));
        $id = last_id();
    }

    $wasPublished = $existing && $existing['published_at'] !== null;
    if ($status === 'approved' && !$wasPublished) {
        announce_publication(one('SELECT * FROM articles WHERE id = ?', [$id]));
    }

    if (is_admin()) {
        flash($status === 'approved' ? 'Story published.' : 'Story saved.');
        redirect('admin_articles');
    }
    flash($existing ? 'Changes sent for review. The editors will check them before they go live.' : 'Story sent for review. You will get a notification when it is published.');
    redirect('my_stories');
}

function action_article_delete(int $id): void
{
    $article = one('SELECT * FROM articles WHERE id = ?', [$id]);
    if (!$article) throw new ValidationError('This story was already deleted.');
    if (!is_admin() && (int)$article['author_id'] !== user_id()) abort(403, 'Not your story', 'You can only delete stories you wrote.');

    // Comments, bookmarks and view counts are removed by ON DELETE CASCADE.
    query('DELETE FROM articles WHERE id = ?', [$id]);
    delete_image_file($article['image']);
    flash('Story deleted.');
    back(is_admin() ? 'admin_articles' : 'my_stories');
}
