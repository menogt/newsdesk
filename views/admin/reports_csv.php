<?php
/** CSV export of per-story engagement for the selected period (opens in Excel / Google Sheets). */
require_once ROOT_DIR . '/app/report_period.php';
[, $from, $to] = report_period();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="newsdesk-report-' . $from . '-to-' . $to . '.csv"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Sinhala/Tamil and curly quotes correctly
fputcsv($out, ['Period', $from . ' to ' . $to], ',', '"', '');
fputcsv($out, [], ',', '"', '');
fputcsv($out, ['Story', 'Category', 'Type', 'Status', 'Author', 'Published', 'Reads in period', 'Reads all time', 'Approved comments', 'Bookmarks'], ',', '"', '');
$rows = rows("SELECT a.title, c.name AS category, a.kind, a.status, u.name AS author, a.published_at,
    (SELECT COALESCE(SUM(v.views), 0) FROM article_views v WHERE v.article_id = a.id AND v.view_date BETWEEN ? AND ?) AS period_reads,
    a.views,
    (SELECT COUNT(*) FROM comments x WHERE x.article_id = a.id AND x.status = 'approved') AS comments,
    (SELECT COUNT(*) FROM bookmarks b WHERE b.article_id = a.id) AS bookmarks
  FROM articles a JOIN categories c ON c.id = a.category_id JOIN users u ON u.id = a.author_id
  ORDER BY period_reads DESC, a.views DESC", [$from, $to]);
foreach ($rows as $r) {
    // Stop spreadsheet formula injection from user-written titles.
    foreach ($r as &$v) if (is_string($v) && preg_match('/^[=+\-@\t\r]/', $v)) $v = "'" . $v;
    unset($v);
    fputcsv($out, array_values($r), ',', '"', '');
}
fclose($out);
