<?php
/** All stories and announcements with filters, review status and quick flags. */
$status = get_str('status');
$type = get_str('type');
$category = get_int('category');
$q = mb_substr(get_str('q'), 0, 150);

$where = ['1 = 1'];
$params = [];
if (in_array($status, ['pending', 'approved', 'rejected'], true)) { $where[] = 'a.status = ?'; $params[] = $status; } else $status = '';
if (in_array($type, ['news', 'announcement'], true)) { $where[] = 'a.kind = ?'; $params[] = $type; } else $type = '';
if ($category) { $where[] = 'a.category_id = ?'; $params[] = $category; }
if ($q !== '') { $where[] = '(a.title LIKE ? OR u.name LIKE ?)'; $like = '%' . addcslashes($q, '%_\\') . '%'; array_push($params, $like, $like); }
$sqlWhere = implode(' AND ', $where);

$counts = ['' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
foreach (rows('SELECT status, COUNT(*) n FROM articles GROUP BY status') as $r) { $counts[$r['status']] = (int)$r['n']; $counts[''] += (int)$r['n']; }

$total = (int)scalar("SELECT COUNT(*) FROM articles a JOIN users u ON u.id = a.author_id WHERE $sqlWhere", $params);
$perPage = 15;
$pages = max(1, (int)ceil($total / $perPage));
$current = min($pages, max(1, get_int('p', 1)));
$list = rows(ARTICLE_SELECT . " WHERE $sqlWhere ORDER BY (a.status = 'pending') DESC, a.updated_at DESC LIMIT $perPage OFFSET " . (($current - 1) * $perPage), $params);
$args = ['status' => $status, 'type' => $type, 'category' => $category, 'q' => $q];
$here = url('admin_articles', $args + ['p' => $current > 1 ? $current : 0]);
?>
<nav class="tabs" aria-label="Filter by status">
  <?php foreach (['' => 'All', 'pending' => 'Pending review', 'approved' => 'Published', 'rejected' => 'Rejected'] as $k => $label): ?>
    <a href="<?= url('admin_articles', ['status' => $k, 'type' => $type, 'category' => $category, 'q' => $q]) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= $label ?> <span class="tab-count<?= $k === 'pending' && $counts[$k] ? ' tab-count-alert' : '' ?>"><?= $counts[$k] ?></span></a>
  <?php endforeach; ?>
</nav>

<form class="toolbar" method="get" action="index.php">
  <input type="hidden" name="page" value="admin_articles">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <label class="visually-hidden" for="a-q">Search title or author</label>
  <input id="a-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Search title or author">
  <label class="visually-hidden" for="a-type">Type</label>
  <select id="a-type" name="type"><option value="">All types</option><option value="news"<?= $type === 'news' ? ' selected' : '' ?>>News</option><option value="announcement"<?= $type === 'announcement' ? ' selected' : '' ?>>Announcements</option></select>
  <label class="visually-hidden" for="a-cat">Category</label>
  <select id="a-cat" name="category"><option value="">All categories</option><?php foreach (categories() as $c): ?><option value="<?= $c['id'] ?>"<?= $category === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <button class="btn btn-outline btn-sm">Filter</button>
  <?php if ($q !== '' || $type || $category): ?><a class="btn btn-quiet btn-sm" href="<?= url('admin_articles', ['status' => $status]) ?>">Clear</a><?php endif; ?>
</form>

<?php if (!$list): ?>
  <?php view('partials/empty', ['heading' => 'No stories found', 'text' => 'Try another filter, or write a new story.', 'action_url' => url('admin_article'), 'action_label' => 'New story']); ?>
<?php else: ?>
<div class="table-wrap">
  <table class="data-table">
    <thead><tr><th scope="col">Story</th><th scope="col">Status</th><th scope="col">On homepage</th><th scope="col" class="num">Reads</th><th scope="col" class="num">Comments</th><th scope="col">Updated</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
    <tbody>
    <?php foreach ($list as $a): ?>
      <tr<?= $a['status'] === 'pending' ? ' class="row-pending"' : '' ?>>
        <td class="cell-story">
          <?= cover($a, 'ratio-wide table-thumb', '160') ?>
          <div>
            <a class="cell-title" href="<?= url('admin_article', ['id' => $a['id']]) ?>"><?= e($a['title']) ?></a>
            <span class="cell-sub"><span class="dot tone-<?= e($a['color']) ?>"></span><?= e($a['category']) ?>, <?= $a['kind'] === 'announcement' ? 'announcement' : 'news' ?>, by <?= e($a['author']) ?></span>
          </div>
        </td>
        <td><?= status_badge($a['status']) ?></td>
        <td class="cell-flags">
          <?php if ($a['status'] === 'approved'): ?>
            <?= action_button('article_flag', (int)$a['id'], icon('star') . 'Featured', 'flag' . ($a['featured'] ? ' is-on' : ''), '', ['flag' => 'featured', 'return' => $here]) ?>
            <?= action_button('article_flag', (int)$a['id'], icon('bolt') . 'Breaking', 'flag' . ($a['breaking'] ? ' is-on' : ''), '', ['flag' => 'breaking', 'return' => $here]) ?>
          <?php else: ?><span class="muted">After publishing</span><?php endif; ?>
        </td>
        <td class="num"><?= number_format((int)$a['views']) ?></td>
        <td class="num"><?= (int)$a['comment_count'] ?></td>
        <td class="nowrap"><?= e(time_ago($a['updated_at'])) ?></td>
        <td class="cell-actions">
          <a class="btn btn-sm <?= $a['status'] === 'pending' ? 'btn-primary' : 'btn-outline' ?>" href="<?= url('admin_article', ['id' => $a['id']]) ?>"><?= $a['status'] === 'pending' ? 'Review' : 'Edit' ?></a>
          <?= action_button('article_delete', (int)$a['id'], '<span class="visually-hidden">Delete ' . e($a['title']) . '</span>' . icon('x'), 'icon-btn icon-btn-danger', 'Delete “' . $a['title'] . '”? Its comments, bookmarks and read history are deleted too.', ['return' => $here]) ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php pagination('admin_articles', $args, $current, $pages); ?>
<?php endif; ?>
