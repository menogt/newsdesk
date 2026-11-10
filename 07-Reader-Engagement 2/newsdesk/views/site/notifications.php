<?php
/** In-app notifications for the signed-in user. */
$items = rows('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT 50', [user_id()]);
$unread = count(array_filter($items, fn($n) => !$n['is_read']));
?>
<div class="wrap narrow">
  <header class="page-head page-head-row">
    <div><h1>Notifications</h1><p><?= $unread ? plural($unread, 'unread notification') : 'You are all caught up.' ?></p></div>
    <?php if ($unread): ?><?= action_button('notifications_read', 0, 'Mark all as read', 'btn btn-outline') ?><?php endif; ?>
  </header>
  <?php if (!$items): ?>
    <?php view('partials/empty', ['heading' => 'No notifications yet', 'text' => 'You will hear here when your stories are reviewed, your comments go live, or a category you follow publishes something new.', 'action_url' => url('following'), 'action_label' => 'Follow categories', 'icon' => 'bell']); ?>
  <?php else: ?>
    <ul class="notif-list">
      <?php foreach ($items as $n): ?>
        <li class="notif<?= $n['is_read'] ? '' : ' is-unread' ?>">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="notifications_read">
            <input type="hidden" name="id" value="<?= $n['id'] ?>">
            <button class="notif-btn">
              <span class="notif-dot" aria-hidden="true"></span>
              <span class="notif-text"><?= e($n['message']) ?><time datetime="<?= e($n['created_at']) ?>"><?= e(time_ago($n['created_at'])) ?></time></span>
              <?php if (!$n['is_read']): ?><span class="visually-hidden">(unread)</span><?php endif; ?>
            </button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
