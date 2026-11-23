<?php
/** Admin dashboard: today's work queue and a snapshot of readership. */
$stats = one("SELECT
  (SELECT COUNT(*) FROM articles WHERE status = 'approved') AS published,
  (SELECT COUNT(*) FROM articles WHERE status = 'pending') AS pending,
  (SELECT COUNT(*) FROM comments WHERE status = 'pending') AS comments,
  (SELECT COUNT(*) FROM users WHERE role = 'student') AS students,
  (SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS new_students,
  (SELECT COALESCE(SUM(views), 0) FROM article_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)) AS reads_week,
  (SELECT COALESCE(SUM(views), 0) FROM article_views WHERE view_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 13 DAY) AND DATE_SUB(CURDATE(), INTERVAL 7 DAY)) AS reads_prev");
$queue = rows(ARTICLE_SELECT . " WHERE a.status = 'pending' ORDER BY a.updated_at ASC LIMIT 6");
$recentComments = rows("SELECT c.*, u.name, a.title FROM comments c JOIN users u ON u.id = c.user_id JOIN articles a ON a.id = c.article_id WHERE c.status = 'pending' ORDER BY c.created_at ASC LIMIT 5");
$top = rows("SELECT a.id, a.title, SUM(v.views) AS read_count FROM article_views v JOIN articles a ON a.id = v.article_id WHERE v.view_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY a.id, a.title ORDER BY read_count DESC LIMIT 5");
$series = daily_reads(date('Y-m-d', strtotime('-13 days')), date('Y-m-d'));
$change = $stats['reads_prev'] ? round(100 * ($stats['reads_week'] - $stats['reads_prev']) / $stats['reads_prev']) : null;
?>
<p class="admin-greeting">Good <?= (int)date('G') < 12 ? 'morning' : ((int)date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e(strtok(user()['name'], ' ')) ?>. <?= $stats['pending'] || $stats['comments'] ? 'Here is what needs your attention.' : 'Nothing is waiting for review.' ?></p>

<div class="kpis">
  <a class="kpi<?= $stats['pending'] ? ' kpi-alert' : '' ?>" href="<?= url('admin_articles', ['status' => 'pending']) ?>"><span>Stories to review</span><strong><?= (int)$stats['pending'] ?></strong></a>
  <a class="kpi<?= $stats['comments'] ? ' kpi-alert' : '' ?>" href="<?= url('admin_comments') ?>"><span>Comments to moderate</span><strong><?= (int)$stats['comments'] ?></strong></a>
  <a class="kpi" href="<?= url('admin_reports') ?>"><span>Reads, last 7 days</span><strong><?= number_format((int)$stats['reads_week']) ?></strong><?php if ($change !== null): ?><em class="<?= $change >= 0 ? 'up' : 'down' ?>"><?= $change >= 0 ? '+' : '' ?><?= $change ?>% on the previous 7 days</em><?php endif; ?></a>
  <a class="kpi" href="<?= url('admin_users') ?>"><span>Student accounts</span><strong><?= number_format((int)$stats['students']) ?></strong><?php if ($stats['new_students']): ?><em class="up">+<?= (int)$stats['new_students'] ?> this week</em><?php endif; ?></a>
</div>

<div class="dash-grid">
  <section class="panel dash-queue">
    <div class="panel-head"><h2>Review queue</h2><a href="<?= url('admin_articles', ['status' => 'pending']) ?>">All pending</a></div>
    <?php if (!$queue): ?>
      <p class="muted">No stories are waiting. New student submissions will appear here.</p>
    <?php endif; ?>
    <ul class="queue">
      <?php foreach ($queue as $a): ?>
        <li>
          <?= cover($a, 'ratio-wide queue-thumb', '240') ?>
          <div class="queue-body">
            <a class="queue-title" href="<?= url('admin_article', ['id' => $a['id']]) ?>"><?= e($a['title']) ?></a>
            <span class="queue-meta"><?= e($a['author']) ?>, <?= e($a['category']) ?>, sent <?= e(time_ago($a['updated_at'])) ?><?= $a['published_at'] ? ' (edit of a live story)' : '' ?></span>
          </div>
          <a class="btn btn-sm btn-primary" href="<?= url('admin_article', ['id' => $a['id']]) ?>">Review</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Reads, last 14 days</h2><a href="<?= url('admin_reports') ?>">Full report</a></div>
    <?= column_chart($series, 'Story reads per day for the last 14 days') ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Comments waiting</h2><a href="<?= url('admin_comments') ?>">Moderate</a></div>
    <?php if (!$recentComments): ?><p class="muted">The moderation queue is empty.</p><?php endif; ?>
    <ul class="mini-comments">
      <?php foreach ($recentComments as $c): ?>
        <li>
          <p><strong><?= e($c['name']) ?></strong> on <a href="<?= url('article', ['id' => $c['article_id']]) ?>#comments"><?= e($c['title']) ?></a></p>
          <blockquote><?= e(mb_strimwidth($c['body'], 0, 160, '…')) ?></blockquote>
          <div class="row-actions">
            <?= action_button('comment_review', (int)$c['id'], icon('check') . 'Approve', 'btn btn-sm btn-outline', '', ['decision' => 'approved', 'return' => url('admin')]) ?>
            <?= action_button('comment_review', (int)$c['id'], icon('x') . 'Reject', 'btn btn-sm btn-quiet', '', ['decision' => 'rejected', 'return' => url('admin')]) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Most read this week</h2></div>
    <?php if (!$top): ?><p class="muted">No reads recorded this week yet.</p><?php endif; ?>
    <ol class="top-list">
      <?php foreach ($top as $t): ?><li><a href="<?= url('article', ['id' => $t['id']]) ?>"><?= e($t['title']) ?></a><span><?= number_format((int)$t['read_count']) ?></span></li><?php endforeach; ?>
    </ol>
  </section>
</div>
