<?php
/** Choose categories to follow (subscriptions). */
$list = rows("SELECT c.*,
    EXISTS (SELECT 1 FROM subscriptions s WHERE s.category_id = c.id AND s.user_id = ?) AS followed,
    (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = 'approved') AS stories,
    (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS followers,
    (SELECT MAX(published_at) FROM articles a WHERE a.category_id = c.id AND a.status = 'approved') AS latest
  FROM categories c ORDER BY c.name", [user_id()]);
$count = count(array_filter($list, fn($c) => $c['followed']));
?>
<div class="wrap">
  <header class="page-head page-head-row">
    <div><h1>Categories you follow</h1><p>New stories in these categories appear in your feed, and you get a notification when one is published.</p></div>
    <a class="btn btn-outline" href="<?= url('feed') ?>">Open my feed</a>
  </header>
  <p class="result-count">Following <?= $count ?> of <?= count($list) ?></p>
  <div class="follow-grid">
    <?php foreach ($list as $c): ?>
      <section class="follow-card tone-<?= e($c['color']) ?><?= $c['followed'] ? ' is-followed' : '' ?>">
        <h2><?= e($c['name']) ?></h2>
        <p><?= e($c['description']) ?></p>
        <p class="follow-meta"><?= plural((int)$c['stories'], 'story', 'stories') ?>, <?= plural((int)$c['followers'], 'follower') ?><?= $c['latest'] ? ', latest ' . e(time_ago($c['latest'])) : '' ?></p>
        <?= action_button('subscribe', (int)$c['id'], $c['followed'] ? icon('check') . 'Following' : icon('plus') . 'Follow', $c['followed'] ? 'btn btn-sm btn-saved' : 'btn btn-sm btn-primary') ?>
      </section>
    <?php endforeach; ?>
  </div>
</div>
