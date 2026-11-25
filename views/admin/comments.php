<?php
/** Comment moderation queue with bulk actions. */
$status = get_str('status', 'pending');
if (!in_array($status, ['pending', 'approved', 'rejected', 'all'], true)) $status = 'pending';
$counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'all' => 0];
foreach (rows('SELECT status, COUNT(*) n FROM comments GROUP BY status') as $r) { $counts[$r['status']] = (int)$r['n']; $counts['all'] += (int)$r['n']; }
$list = rows('SELECT c.*, u.name, u.email, a.title FROM comments c JOIN users u ON u.id = c.user_id JOIN articles a ON a.id = c.article_id'
    . ($status === 'all' ? '' : ' WHERE c.status = ?') . ' ORDER BY c.created_at ' . ($status === 'pending' ? 'ASC' : 'DESC') . ' LIMIT 100', $status === 'all' ? [] : [$status]);
$here = url('admin_comments', ['status' => $status]);
?>
<nav class="tabs" aria-label="Filter by status">
  <?php foreach (['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $k => $label): ?>
    <a href="<?= url('admin_comments', ['status' => $k]) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $label ?> <span class="tab-count<?= $k === 'pending' && $counts[$k] ? ' tab-count-alert' : '' ?>"><?= $counts[$k] ?></span></a>
  <?php endforeach; ?>
</nav>

<?php if (!$list): ?>
  <?php view('partials/empty', ['heading' => $status === 'pending' ? 'All caught up' : 'No comments here', 'text' => $status === 'pending' ? 'New student comments will wait here until you approve them.' : 'Comments with this status will be listed here.', 'icon' => 'chat']); ?>
<?php else: ?>
<form method="post" id="bulk" class="bulk-bar" data-bulk>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="comments_bulk">
  <input type="hidden" name="return" value="<?= e($here) ?>">
  <label class="check"><input type="checkbox" data-select-all> Select all</label>
  <span class="bulk-count" data-bulk-count>0 selected</span>
  <button class="btn btn-sm btn-outline" name="decision" value="approved" disabled><?= icon('check') ?>Approve</button>
  <button class="btn btn-sm btn-quiet" name="decision" value="rejected" disabled><?= icon('x') ?>Reject</button>
  <button class="btn btn-sm btn-danger-outline" name="decision" value="delete" disabled data-confirm-click="Delete the selected comments permanently?">Delete</button>
</form>

<ul class="mod-list">
  <?php foreach ($list as $c): ?>
    <li class="mod-item">
      <input type="checkbox" name="ids[]" value="<?= $c['id'] ?>" form="bulk" aria-label="Select comment by <?= e($c['name']) ?>">
      <div class="mod-main">
        <p class="mod-head"><strong><?= e($c['name']) ?></strong><span class="muted"><?= e($c['email']) ?></span><time datetime="<?= e($c['created_at']) ?>"><?= e(time_ago($c['created_at'])) ?></time><?= status_badge($c['status']) ?></p>
        <p class="mod-body"><?= nl2br(e($c['body'])) ?></p>
        <p class="mod-on">On <a href="<?= url('article', ['id' => $c['article_id']]) ?>#comments" target="_blank" rel="noopener"><?= e($c['title']) ?></a></p>
      </div>
      <div class="mod-actions">
        <?php if ($c['status'] !== 'approved'): ?><?= action_button('comment_review', (int)$c['id'], icon('check') . 'Approve', 'btn btn-sm btn-outline', '', ['decision' => 'approved', 'return' => $here]) ?><?php endif; ?>
        <?php if ($c['status'] !== 'rejected'): ?><?= action_button('comment_review', (int)$c['id'], icon('x') . 'Reject', 'btn btn-sm btn-quiet', '', ['decision' => 'rejected', 'return' => $here]) ?><?php endif; ?>
        <?= action_button('comment_delete', (int)$c['id'], 'Delete', 'link-btn link-danger', 'Delete this comment permanently?', ['return' => $here]) ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
