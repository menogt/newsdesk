<?php
/** Personal feed: published stories from followed categories. */
$followed = rows('SELECT c.* FROM subscriptions s JOIN categories c ON c.id = s.category_id WHERE s.user_id = ? ORDER BY c.name', [user_id()]);
?>
<div class="wrap">
  <header class="page-head">
    <h1>My feed</h1>
    <p>Stories from the categories you follow.</p>
    <div class="chip-row">
      <?php foreach ($followed as $c): ?><a class="chip tone-<?= e($c['color']) ?>" href="<?= url('news', ['category' => $c['id']]) ?>"><?= e($c['name']) ?></a><?php endforeach; ?>
      <a class="chip chip-add" href="<?= url('following') ?>"><?= $followed ? 'Change categories' : 'Choose categories' ?></a>
    </div>
  </header>
  <?php if (!$followed): ?>
    <?php view('partials/empty', ['heading' => 'Your feed is empty', 'text' => 'Follow a few categories, such as Sports or Events, and their new stories will collect here.', 'action_url' => url('following'), 'action_label' => 'Choose categories', 'icon' => 'tag']); ?>
  <?php else: ?>
    <?php view('partials/listing', [
        'page_name' => 'feed',
        'base_where' => ['a.category_id IN (SELECT category_id FROM subscriptions WHERE user_id = ?)'], 'base_params' => [user_id()],
        'empty_heading' => 'Nothing new yet', 'empty_text' => 'The categories you follow have no published stories yet.',
    ]); ?>
  <?php endif; ?>
</div>
