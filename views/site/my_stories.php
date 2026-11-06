<?php
/** The signed-in student's own submissions with status and editor feedback. */
$status = get_str('status');
if (!in_array($status, ['pending', 'approved', 'rejected'], true)) $status = '';
$counts = ['' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach (rows('SELECT status, COUNT(*) n FROM articles WHERE author_id = ? GROUP BY status', [user_id()]) as $r) { $counts[$r['status']] = (int)$r['n']; $counts[''] += (int)$r['n']; }
$params = [user_id()];
$sql = ARTICLE_SELECT . ' WHERE a.author_id = ?';
if ($status) { $sql .= ' AND a.status = ?'; $params[] = $status; }
$stories = rows($sql . ' ORDER BY a.updated_at DESC', $params);
?>
<div class="wrap narrow">
  <header class="page-head page-head-row">
    <div><h1>My stories</h1><p>Everything you have submitted, with its review status and any notes from the editors.</p></div>
    <a class="btn btn-primary" href="<?= url('write') ?>"><?= icon('pen') ?> Write a story</a>
  </header>

  <nav class="tabs" aria-label="Filter by status">
    <?php foreach (['' => 'All', 'pending' => 'Pending review', 'approved' => 'Published', 'rejected' => 'Needs changes'] as $k => $label): ?>
      <a href="<?= url('my_stories', ['status' => $k]) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $label ?> <span class="tab-count"><?= $counts[$k] ?></span></a>
    <?php endforeach; ?>
  </nav>

  <?php if (!$stories): ?>
    <?php view('partials/empty', ['heading' => $status ? 'Nothing here' : 'You have not submitted a story yet', 'text' => $status ? 'No stories have this status.' : 'Share a club update, an event or an achievement with the rest of campus.', 'action_url' => url('write'), 'action_label' => 'Write a story', 'icon' => 'pen']); ?>
  <?php endif; ?>

  <div class="mine-list">
    <?php foreach ($stories as $a): ?>
      <article class="mine">
        <?= cover($a, 'ratio-wide mine-thumb', '320') ?>
        <div class="mine-body">
          <p class="mine-top"><?= status_badge($a['status']) ?><span><?= e($a['category']) ?>, <?= $a['kind'] === 'announcement' ? 'announcement' : 'news' ?></span></p>
          <h2><a href="<?= url('article', ['id' => $a['id']]) ?>"><?= e($a['title']) ?></a></h2>
          <p class="mine-meta">
            <?php if ($a['status'] === 'approved'): ?>Published <?= date_label($a['published_at']) ?>, <?= plural((int)$a['views'], 'read') ?>, <?= plural((int)$a['comment_count'], 'comment') ?>
            <?php elseif ($a['status'] === 'pending'): ?>Sent <?= e(time_ago($a['updated_at'])) ?><?= $a['published_at'] ? ', edit of a published story' : '' ?>
            <?php else: ?>Reviewed <?= e(time_ago($a['updated_at'])) ?><?php endif; ?>
          </p>
          <?php if ($a['review_note'] && $a['status'] === 'rejected'): ?><p class="mine-note"><strong>Editor:</strong> <?= e($a['review_note']) ?></p><?php endif; ?>
          <div class="mine-actions">
            <a class="btn btn-sm btn-outline" href="<?= url('write', ['id' => $a['id']]) ?>"><?= icon('pen') ?><?= $a['status'] === 'rejected' ? 'Fix and resubmit' : 'Edit' ?></a>
            <?= action_button('article_delete', (int)$a['id'], 'Delete', 'link-btn link-danger', 'Delete “' . $a['title'] . '”? Its comments and bookmarks are deleted too.') ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</div>
