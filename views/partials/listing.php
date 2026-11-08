<?php
/**
 * Searchable, filterable, paginated list of published stories.
 * Variables: $page_name, $base_where (array of SQL conditions), $base_params,
 *            $empty_heading, $empty_text, optional $empty_url / $empty_label
 */
$q        = mb_substr(get_str('q'), 0, 150);
$category = get_int('category');
$type     = get_str('type');
$from     = get_str('from');
$to       = get_str('to');
$sort     = get_str('sort', 'newest');

$where  = array_merge(["a.status = 'approved'"], $base_where);
$params = $base_params;
$notes  = [];

if ($q !== '') {
    $where[] = '(a.title LIKE ? OR a.summary LIKE ? OR a.body LIKE ?)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    array_push($params, $like, $like, $like);
}
if ($category) { $where[] = 'a.category_id = ?'; $params[] = $category; }
if (in_array($type, ['news', 'announcement'], true)) { $where[] = 'a.kind = ?'; $params[] = $type; } else { $type = ''; }
if ($from !== '' && !valid_date($from)) { $from = ''; $notes[] = 'The “from” date was not a valid date and was ignored.'; }
if ($to !== '' && !valid_date($to))     { $to = '';   $notes[] = 'The “to” date was not a valid date and was ignored.'; }
if ($from !== '' && $to !== '' && $from > $to) { [$from, $to] = [$to, $from]; $notes[] = 'The dates were the wrong way round, so they were swapped.'; }
if ($from !== '') { $where[] = 'a.published_at >= ?'; $params[] = $from . ' 00:00:00'; }
if ($to !== '')   { $where[] = 'a.published_at <= ?'; $params[] = $to . ' 23:59:59'; }

$orders = [
    'newest'    => 'a.published_at DESC, a.id DESC',
    'oldest'    => 'a.published_at ASC, a.id ASC',
    'popular'   => 'a.views DESC, a.published_at DESC',
    'discussed' => 'comment_count DESC, a.published_at DESC',
];
if (!isset($orders[$sort])) $sort = 'newest';

$sqlWhere = implode(' AND ', $where);
$total    = (int)scalar("SELECT COUNT(*) FROM articles a WHERE $sqlWhere", $params);
$pages    = max(1, (int)ceil($total / PER_PAGE));
$current  = min($pages, max(1, get_int('p', 1)));
$offset   = ($current - 1) * PER_PAGE;
$results  = rows(ARTICLE_SELECT . " WHERE $sqlWhere ORDER BY {$orders[$sort]} LIMIT " . PER_PAGE . " OFFSET $offset", $params);

$args = ['q' => $q, 'category' => $category, 'type' => $type, 'from' => $from, 'to' => $to, 'sort' => $sort === 'newest' ? '' : $sort];
$filtered = $q !== '' || $category || $type !== '' || $from !== '' || $to !== '';
?>
<form class="filter-bar" method="get" action="index.php" data-validate>
  <input type="hidden" name="page" value="<?= e($page_name) ?>">
  <div class="field field-search">
    <label for="f-q">Search</label>
    <input id="f-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Keyword or phrase" maxlength="150">
  </div>
  <div class="field">
    <label for="f-category">Category</label>
    <select id="f-category" name="category">
      <option value="">All categories</option>
      <?php foreach (categories() as $c): ?><option value="<?= $c['id'] ?>"<?= $category === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field">
    <label for="f-type">Type</label>
    <select id="f-type" name="type">
      <option value="">All types</option>
      <option value="news"<?= $type === 'news' ? ' selected' : '' ?>>News only</option>
      <option value="announcement"<?= $type === 'announcement' ? ' selected' : '' ?>>Announcements only</option>
    </select>
  </div>
  <div class="field field-date">
    <label for="f-from">From</label>
    <input id="f-from" name="from" type="date" value="<?= e($from) ?>" max="<?= date('Y-m-d') ?>">
  </div>
  <div class="field field-date">
    <label for="f-to">To</label>
    <input id="f-to" name="to" type="date" value="<?= e($to) ?>" data-after="f-from">
  </div>
  <div class="field">
    <label for="f-sort">Sort by</label>
    <select id="f-sort" name="sort">
      <?php foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'popular' => 'Most read', 'discussed' => 'Most discussed'] as $k => $label): ?>
        <option value="<?= $k ?>"<?= $sort === $k ? ' selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="filter-actions">
    <button class="btn btn-primary">Apply</button>
    <?php if ($filtered || $sort !== 'newest'): ?><a class="btn btn-quiet" href="<?= url($page_name) ?>">Clear</a><?php endif; ?>
  </div>
</form>

<?php foreach ($notes as $note): ?><p class="form-note"><?= e($note) ?></p><?php endforeach; ?>

<p class="result-count" aria-live="polite">
  <?= plural($total, 'story', 'stories') ?><?= $q !== '' ? ' matching “' . e($q) . '”' : '' ?><?= $pages > 1 ? ", page $current of $pages" : '' ?>
</p>

<?php if (!$results): ?>
  <?php view('partials/empty', $filtered
      ? ['heading' => 'No stories match these filters', 'text' => 'Try a shorter search, another category or a wider date range.', 'action_url' => url($page_name), 'action_label' => 'Clear filters', 'icon' => 'search']
      : ['heading' => $empty_heading, 'text' => $empty_text, 'action_url' => $empty_url ?? '', 'action_label' => $empty_label ?? '']); ?>
<?php else: ?>
  <div class="story-grid">
    <?php foreach ($results as $a) view('partials/story_card', ['a' => $a]); ?>
  </div>
  <?php pagination($page_name, $args, $current, $pages); ?>
<?php endif; ?>
