<?php
/** Readership and engagement report for a chosen period. */
require_once ROOT_DIR . '/app/report_period.php';
[$range, $from, $to] = report_period();
$p = [$from, $to];
$fromTs = $from . ' 00:00:00'; $toTs = $to . ' 23:59:59';

$totals = one("SELECT
  (SELECT COALESCE(SUM(views), 0) FROM article_views WHERE view_date BETWEEN ? AND ?) AS reads_total,
  (SELECT COUNT(*) FROM articles WHERE published_at BETWEEN ? AND ? AND status = 'approved') AS published,
  (SELECT COUNT(*) FROM articles WHERE created_at BETWEEN ? AND ?) AS submissions,
  (SELECT COUNT(*) FROM comments WHERE created_at BETWEEN ? AND ?) AS comments,
  (SELECT COUNT(*) FROM bookmarks WHERE created_at BETWEEN ? AND ?) AS bookmarks,
  (SELECT COUNT(*) FROM subscriptions WHERE created_at BETWEEN ? AND ?) AS follows,
  (SELECT COUNT(*) FROM users WHERE role = 'student' AND created_at BETWEEN ? AND ?) AS new_users",
  [$from, $to, $fromTs, $toTs, $fromTs, $toTs, $fromTs, $toTs, $fromTs, $toTs, $fromTs, $toTs, $fromTs, $toTs]);

$series = daily_reads($from, $to);
$byCategory = array_map(fn($r) => ['label' => $r['name'], 'value' => $r['read_count'], 'tone' => $r['color']], rows(
  'SELECT c.name, c.color, COALESCE(SUM(v.views), 0) AS read_count FROM categories c
   LEFT JOIN articles a ON a.category_id = c.id
   LEFT JOIN article_views v ON v.article_id = a.id AND v.view_date BETWEEN ? AND ?
   GROUP BY c.id, c.name, c.color ORDER BY read_count DESC', $p));
$followers = array_map(fn($r) => ['label' => $r['name'], 'value' => $r['n'], 'tone' => $r['color']], rows(
  'SELECT c.name, c.color, COUNT(s.user_id) AS n FROM categories c LEFT JOIN subscriptions s ON s.category_id = c.id GROUP BY c.id, c.name, c.color ORDER BY n DESC'));
$top = rows("SELECT a.id, a.title, c.name AS category, SUM(v.views) AS read_count,
    (SELECT COUNT(*) FROM comments x WHERE x.article_id = a.id AND x.status = 'approved') AS comments,
    (SELECT COUNT(*) FROM bookmarks b WHERE b.article_id = a.id) AS saves
  FROM article_views v JOIN articles a ON a.id = v.article_id JOIN categories c ON c.id = a.category_id
  WHERE v.view_date BETWEEN ? AND ? GROUP BY a.id, a.title, c.name ORDER BY read_count DESC LIMIT 10", $p);
$discussed = rows("SELECT a.id, a.title, COUNT(*) AS n FROM comments x JOIN articles a ON a.id = x.article_id
  WHERE x.created_at BETWEEN ? AND ? GROUP BY a.id, a.title ORDER BY n DESC LIMIT 5", [$fromTs, $toTs]);
$writers = rows("SELECT u.name, COUNT(*) AS n, SUM(a.status = 'approved') AS ok FROM articles a JOIN users u ON u.id = a.author_id
  WHERE a.created_at BETWEEN ? AND ? AND u.role = 'student' GROUP BY u.id, u.name ORDER BY n DESC LIMIT 5", [$fromTs, $toTs]);
$statusMix = one("SELECT SUM(status = 'approved') ok, SUM(status = 'pending') wait, SUM(status = 'rejected') no FROM articles WHERE created_at BETWEEN ? AND ?", [$fromTs, $toTs]);
$days = count($series);
$args = $range === 'custom' ? ['range' => 'custom', 'from' => $from, 'to' => $to] : ['range' => $range];
?>
<div class="report-head">
  <nav class="tabs tabs-pills" aria-label="Period">
    <?php foreach (['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days'] as $k => $label): ?>
      <a href="<?= url('admin_reports', ['range' => $k]) ?>"<?= $range === $k ? ' aria-current="page"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <form class="toolbar" method="get" action="index.php" data-validate>
    <input type="hidden" name="page" value="admin_reports"><input type="hidden" name="range" value="custom">
    <label for="r-from">From</label><input id="r-from" name="from" type="date" required value="<?= e($from) ?>" max="<?= date('Y-m-d') ?>">
    <label for="r-to">To</label><input id="r-to" name="to" type="date" required value="<?= e($to) ?>" max="<?= date('Y-m-d') ?>" data-after="r-from">
    <button class="btn btn-sm btn-outline">Show</button>
  </form>
  <div class="report-actions">
    <a class="btn btn-sm btn-outline" href="<?= url('admin_reports', $args + ['export' => 'csv']) ?>"><?= icon('download') ?>Download CSV</a>
    <button class="btn btn-sm btn-quiet" type="button" data-print><?= icon('print') ?>Print</button>
  </div>
</div>

<p class="report-period">Showing <?= date_label($from, 'j M Y') ?> to <?= date_label($to, 'j M Y') ?> (<?= $days ?> days). Reads count one opening of a story per browser session; editor visits are not counted.</p>

<dl class="kpis kpis-6">
  <div class="kpi"><dt>Story reads</dt><dd><?= number_format((int)$totals['reads_total']) ?></dd><small><?= number_format($totals['reads_total'] / max(1, $days), 1) ?> a day</small></div>
  <div class="kpi"><dt>Stories published</dt><dd><?= (int)$totals['published'] ?></dd></div>
  <div class="kpi"><dt>Submissions</dt><dd><?= (int)$totals['submissions'] ?></dd><small><?= (int)$statusMix['ok'] ?> published, <?= (int)$statusMix['wait'] ?> waiting, <?= (int)$statusMix['no'] ?> rejected</small></div>
  <div class="kpi"><dt>Comments</dt><dd><?= (int)$totals['comments'] ?></dd></div>
  <div class="kpi"><dt>Stories saved</dt><dd><?= (int)$totals['bookmarks'] ?></dd></div>
  <div class="kpi"><dt>New students</dt><dd><?= (int)$totals['new_users'] ?></dd><small><?= (int)$totals['follows'] ?> new category follows</small></div>
</dl>

<section class="panel">
  <div class="panel-head"><h2>Reads per day</h2></div>
  <?= column_chart($series, 'Story reads per day from ' . $from . ' to ' . $to) ?>
</section>

<div class="report-grid">
  <section class="panel"><div class="panel-head"><h2>Reads by category</h2></div><?= bar_list($byCategory, 'reads') ?></section>
  <section class="panel"><div class="panel-head"><h2>Followers by category</h2><span class="muted">all time</span></div><?= bar_list($followers) ?></section>
</div>

<section class="panel">
  <div class="panel-head"><h2>Most read stories</h2></div>
  <?php if (!$top): ?><p class="muted">No reads in this period.</p><?php else: ?>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th scope="col">#</th><th scope="col">Story</th><th scope="col">Category</th><th scope="col" class="num">Reads in period</th><th scope="col" class="num">Comments</th><th scope="col" class="num">Saves</th></tr></thead>
      <tbody>
        <?php foreach ($top as $i => $t): ?>
          <tr><td><?= $i + 1 ?></td><td><a class="cell-title" href="<?= url('article', ['id' => $t['id']]) ?>"><?= e($t['title']) ?></a></td><td><?= e($t['category']) ?></td><td class="num"><?= number_format((int)$t['read_count']) ?></td><td class="num"><?= (int)$t['comments'] ?></td><td class="num"><?= (int)$t['saves'] ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>

<div class="report-grid">
  <section class="panel">
    <div class="panel-head"><h2>Most discussed</h2></div>
    <?php if (!$discussed): ?><p class="muted">No comments in this period.</p><?php endif; ?>
    <ol class="top-list"><?php foreach ($discussed as $d): ?><li><a href="<?= url('article', ['id' => $d['id']]) ?>"><?= e($d['title']) ?></a><span><?= plural((int)$d['n'], 'comment') ?></span></li><?php endforeach; ?></ol>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Top student contributors</h2></div>
    <?php if (!$writers): ?><p class="muted">No student submissions in this period.</p><?php endif; ?>
    <ol class="top-list"><?php foreach ($writers as $w): ?><li><span><?= e($w['name']) ?></span><span><?= plural((int)$w['n'], 'submission') ?>, <?= (int)$w['ok'] ?> published</span></li><?php endforeach; ?></ol>
  </section>
</div>
