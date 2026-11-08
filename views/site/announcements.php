<?php
/** Official announcements: current ones first, then the archive. */
$active = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'announcement' AND " . ACTIVE_ANNOUNCEMENT . " ORDER BY a.published_at DESC");
$past = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'announcement' AND NOT " . ACTIVE_ANNOUNCEMENT . " ORDER BY a.published_at DESC LIMIT 20");
?>
<div class="wrap narrow">
  <header class="page-head">
    <h1>Announcements</h1>
    <p>Official notices from faculties, student services and clubs. Notices with a closing date move to the archive after that date.</p>
  </header>

  <?php if (!$active): ?>
    <?php view('partials/empty', ['heading' => 'No current announcements', 'text' => 'New notices from faculties and clubs will appear here.', 'icon' => 'megaphone']); ?>
  <?php endif; ?>

  <div class="notice-list">
    <?php foreach ($active as $n): ?>
      <article class="notice notice-wide">
        <time class="notice-date" datetime="<?= e($n['published_at']) ?>"><span><?= date('j', strtotime($n['published_at'])) ?></span><?= date('M', strtotime($n['published_at'])) ?></time>
        <div>
          <p class="notice-cat tone-<?= e($n['color']) ?>"><?= e($n['category']) ?></p>
          <h2><a href="<?= url('article', ['id' => $n['id']]) ?>"><?= e($n['title']) ?></a></h2>
          <p><?= e($n['summary']) ?></p>
          <?php if ($n['expires_at']): ?><p class="notice-until"><?= icon('clock') ?>Open until <?= date_label($n['expires_at'], 'l, j F') ?></p><?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($past): ?>
    <h2 class="subhead">Archive</h2>
    <ul class="archive-list">
      <?php foreach ($past as $n): ?>
        <li><a href="<?= url('article', ['id' => $n['id']]) ?>"><?= e($n['title']) ?></a><span>Closed <?= date_label($n['expires_at']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
