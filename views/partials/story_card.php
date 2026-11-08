<?php
/** One story. Variables: $a (article row), $style = 'card' | 'row' | 'compact' */
$style = $style ?? 'card';
$link = url('article', ['id' => $a['id']]);
?>
<?php if ($style === 'compact'): ?>
  <article class="story-compact">
    <div class="story-meta"><?= category_label($a) ?><time datetime="<?= e($a['published_at']) ?>"><?= e(time_ago($a['published_at'])) ?></time></div>
    <h3><a href="<?= e($link) ?>"><?= e($a['title']) ?></a></h3>
  </article>
<?php elseif ($style === 'row'): ?>
  <article class="story-row">
    <a class="story-row-media" href="<?= e($link) ?>" tabindex="-1" aria-hidden="true"><?= cover($a, 'ratio-wide', '480') ?></a>
    <div class="story-row-body">
      <div class="story-meta"><?= category_label($a) ?><?php if ($a['kind'] === 'announcement'): ?><span class="kind-flag"><?= icon('megaphone') ?>Announcement</span><?php endif; ?></div>
      <h3><a href="<?= e($link) ?>"><?= e($a['title']) ?></a></h3>
      <p><?= e($a['summary']) ?></p>
      <div class="story-foot"><span><?= e($a['author']) ?></span><time datetime="<?= e($a['published_at']) ?>"><?= e(time_ago($a['published_at'])) ?></time><?php if ($a['comment_count']): ?><span class="with-icon"><?= icon('chat') ?><?= (int)$a['comment_count'] ?></span><?php endif; ?></div>
    </div>
  </article>
<?php else: ?>
  <article class="story-card">
    <a class="story-card-media" href="<?= e($link) ?>" tabindex="-1" aria-hidden="true"><?= cover($a, 'ratio-wide', '640') ?></a>
    <div class="story-meta"><?= category_label($a) ?><?php if ($a['kind'] === 'announcement'): ?><span class="kind-flag"><?= icon('megaphone') ?>Announcement</span><?php endif; ?></div>
    <h3><a href="<?= e($link) ?>"><?= e($a['title']) ?></a></h3>
    <p><?= e($a['summary']) ?></p>
    <div class="story-foot"><span><?= e($a['author']) ?></span><time datetime="<?= e($a['published_at']) ?>"><?= e(time_ago($a['published_at'])) ?></time><?php if ($a['comment_count']): ?><span class="with-icon" title="Comments"><?= icon('chat') ?><?= (int)$a['comment_count'] ?></span><?php endif; ?></div>
  </article>
<?php endif; ?>
