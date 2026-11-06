<?php
/**
 * Formatting and small domain helpers shared by the site and admin templates.
 */

/** Base SELECT for articles with their category and author. */
const ARTICLE_SELECT = "SELECT a.*, c.name AS category, c.color AS color, u.name AS author,
    (SELECT COUNT(*) FROM comments x WHERE x.article_id = a.id AND x.status = 'approved') AS comment_count
  FROM articles a
  JOIN categories c ON c.id = a.category_id
  JOIN users u ON u.id = a.author_id";

/** Condition for an announcement that has not expired. */
const ACTIVE_ANNOUNCEMENT = "(a.expires_at IS NULL OR a.expires_at >= CURDATE())";

function date_label(?string $value, string $format = 'j M Y'): string
{
    return $value ? date($format, strtotime($value)) : 'Not published';
}

/** "5 min ago", "3 hours ago", "Yesterday", then a date. */
function time_ago(?string $value): string
{
    if (!$value) return 'Not published';
    $seconds = time() - strtotime($value);
    if ($seconds < 60)     return 'Just now';
    if ($seconds < 3600)   return floor($seconds / 60) . ' min ago';
    if ($seconds < 86400)  { $h = floor($seconds / 3600); return $h . ($h == 1 ? ' hour ago' : ' hours ago'); }
    if ($seconds < 172800) return 'Yesterday';
    if ($seconds < 604800) return floor($seconds / 86400) . ' days ago';
    return date('j M Y', strtotime($value));
}

function read_time(string $body): string
{
    return max(1, (int)ceil(str_word_count($body) / 200)) . ' min read';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = mb_substr($parts[0] ?? '', 0, 1) . (count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '');
    return mb_strtoupper($letters);
}

/** Public URL for an article image: an uploaded file, a remote URL from the demo data, or ''. */
function image_src(?string $image): string
{
    if (!$image) return '';
    if (str_starts_with($image, 'https://')) return $image;
    return 'uploads/articles/' . rawurlencode($image);
}

/**
 * Article cover. The tinted block with the category name shows if there is no
 * image or the image fails to load (see app.js).
 */
function cover(array $a, string $class = '', string $size = '900'): string
{
    $src = image_src($a['image'] ?? '');
    if ($src && str_starts_with($src, 'https://images.unsplash.com')) $src .= '&w=' . $size . '&q=70';
    $img = $src ? '<img src="' . e($src) . '" alt="" loading="lazy" decoding="async">' : '';
    return '<figure class="cover tone-' . e($a['color'] ?? 'green') . ' ' . e($class) . '"><span class="cover-mark" aria-hidden="true">'
        . e($a['category'] ?? '') . '</span>' . $img . '</figure>';
}

function category_label(array $a): string
{
    return '<a class="cat tone-' . e($a['color']) . '" href="' . e(url('news', ['category' => $a['category_id']])) . '">' . e($a['category']) . '</a>';
}

function status_badge(string $status): string
{
    $labels = ['pending' => 'Pending review', 'approved' => 'Published', 'rejected' => 'Rejected',
               'active' => 'Active', 'blocked' => 'Blocked', 'announcement' => 'Announcement', 'news' => 'News'];
    return '<span class="badge badge-' . e($status) . '">' . e($labels[$status] ?? ucfirst($status)) . '</span>';
}

function plural(int $n, string $one, string $many = ''): string
{
    return number_format($n) . ' ' . ($n === 1 ? $one : ($many ?: $one . 's'));
}

/** Inline SVG icons (no icon font needed, CSP friendly). */
function icon(string $name): string
{
    $paths = [
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'bookmark' => '<path d="M6 3h12v18l-6-4-6 4z"/>',
        'bell'     => '<path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4z"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'pen'      => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13 7 4 4"/>',
        'home'     => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/>',
        'news'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
        'megaphone'=> '<path d="M3 10v4h4l8 5V5L7 10z"/><path d="M18 9a4 4 0 0 1 0 6"/>',
        'tag'      => '<path d="M3 3h8l10 10-8 8L3 11z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'chat'     => '<path d="M4 5h16v11H9l-5 4z"/>',
        'users'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20a7 7 0 0 1 14 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 13.5a7 7 0 0 1 4 6.5"/>',
        'chart'    => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v6H4V6h6"/>',
        'logout'   => '<path d="M15 4h4v16h-4"/><path d="M10 8l-4 4 4 4M6 12h10"/>',
        'link'     => '<path d="M10 14a4 4 0 0 0 6 0l3-3a4 4 0 0 0-6-6l-1 1"/><path d="M14 10a4 4 0 0 0-6 0l-3 3a4 4 0 0 0 6 6l1-1"/>',
        'eye'      => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'check'    => '<path d="m5 12 5 5 9-10"/>',
        'x'        => '<path d="M6 6l12 12M18 6 6 18"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'print'    => '<path d="M7 9V3h10v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 14h10v7H7z"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5M4 20h16"/>',
        'star'     => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/>',
        'bolt'     => '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
}

/** Numbered pagination that keeps the current filters. */
function pagination(string $page, array $args, int $current, int $pages): void
{
    if ($pages <= 1) return;
    echo '<nav class="pagination" aria-label="Pages">';
    if ($current > 1) echo '<a href="' . e(url($page, $args + ['p' => $current - 1])) . '">Previous</a>';
    for ($i = 1; $i <= $pages; $i++) {
        if ($pages > 7 && abs($i - $current) > 2 && $i !== 1 && $i !== $pages) {
            if (abs($i - $current) === 3) echo '<span class="gap">…</span>';
            continue;
        }
        echo '<a href="' . e(url($page, $args + ['p' => $i])) . '"' . ($i === $current ? ' aria-current="page"' : '') . '>' . $i . '</a>';
    }
    if ($current < $pages) echo '<a href="' . e(url($page, $args + ['p' => $current + 1])) . '">Next</a>';
    echo '</nav>';
}

/** A one-button POST form (delete, toggle, approve...). */
function action_button(string $action, int $id, string $label, string $class = 'btn btn-ghost btn-sm', string $confirm = '', array $extra = []): string
{
    $html = '<form method="post" class="inline-form"' . ($confirm ? ' data-confirm="' . e($confirm) . '"' : '') . '>' . csrf_field()
          . '<input type="hidden" name="action" value="' . e($action) . '"><input type="hidden" name="id" value="' . $id . '">';
    foreach ($extra as $k => $v) $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    return $html . '<button class="' . e($class) . '">' . $label . '</button></form>';
}

/* ---------- Domain helpers ---------- */

function categories(): array
{
    static $list = null;
    return $list ??= rows('SELECT * FROM categories ORDER BY name');
}

function notify(int $userId, string $message, string $link = ''): void
{
    query('INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)', [$userId, mb_substr($message, 0, 255), $link]);
}

function unread_notifications(): int
{
    return user() ? (int)scalar('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0', [user_id()]) : 0;
}

/**
 * Called when an article becomes published for the first time:
 * tells the author and everyone following the category.
 */
function announce_publication(array $article): void
{
    $link = url('article', ['id' => $article['id']]);
    $category = one('SELECT name FROM categories WHERE id = ?', [$article['category_id']])['name'] ?? 'a category you follow';
    if ((int)$article['author_id'] !== user_id()) {
        notify((int)$article['author_id'], 'Your story “' . $article['title'] . '” is now published.', $link);
    }
    $followers = rows('SELECT user_id FROM subscriptions WHERE category_id = ? AND user_id <> ?', [$article['category_id'], $article['author_id']]);
    foreach ($followers as $f) {
        notify((int)$f['user_id'], 'New in ' . $category . ': ' . $article['title'], $link);
    }
}

/** Count one read per article per session. Administrators are not counted. */
function record_view(array $article): void
{
    if ($article['status'] !== 'approved' || is_admin() || !empty($_SESSION['viewed'][$article['id']])) return;
    $_SESSION['viewed'][$article['id']] = true;
    query('UPDATE articles SET views = views + 1, updated_at = updated_at WHERE id = ?', [$article['id']]);   // keep "last edited" unchanged
    query('INSERT INTO article_views (article_id, view_date, views) VALUES (?, CURDATE(), 1)
           ON DUPLICATE KEY UPDATE views = views + 1', [$article['id']]);
}

/** Deletes an uploaded image file (remote demo URLs are ignored). */
function delete_image_file(string $image): void
{
    if ($image && !str_starts_with($image, 'https://') && preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $image)) {
        @unlink(UPLOAD_DIR . '/' . $image);
    }
}

/**
 * Validates and stores an uploaded cover image. Returns the new file name,
 * or null when no file was chosen.
 */
function store_uploaded_image(string $field): ?string
{
    $file = $_FILES[$field] ?? null;
    if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > 2 * 1024 * 1024) {
        throw new ValidationError('The cover image must be 2 MB or smaller.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new ValidationError('The image could not be uploaded. Please try again.');
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $size = @getimagesize($file['tmp_name']);
    if (!isset($types[$mime]) || !$size) throw new ValidationError('The cover image must be a JPG, PNG or WebP picture.');
    if ($size[0] < 400 || $size[1] < 225) throw new ValidationError('The cover image should be at least 400 × 225 pixels.');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) {
        throw new ValidationError('The image could not be saved on the server.');
    }
    return $name;
}

/**
 * Daily column chart as inline SVG (no chart library needed).
 * $series: ['2026-10-01' => 12, ...]. Includes a visually hidden data table for screen readers.
 */
function column_chart(array $series, string $label, string $unit = 'reads'): string
{
    $w = 720; $h = 220; $padL = 36; $padB = 26; $padT = 12;
    $max = max(1, ...array_values($series ?: [0]));
    $step = (int)ceil($max / 4);
    $step = $step > 10 ? (int)(ceil($step / 5) * 5) : max(1, $step);
    $top = $step * 4;
    $n = max(1, count($series));
    $slot = ($w - $padL) / $n;
    $barW = max(2, min(28, $slot * 0.62));
    $svg = '<svg class="chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="' . e($label) . '">';
    for ($i = 0; $i <= 4; $i++) {
        $y = $padT + ($h - $padT - $padB) * (1 - $i / 4);
        $svg .= '<line class="grid" x1="' . $padL . '" x2="' . $w . '" y1="' . $y . '" y2="' . $y . '"/>'
              . '<text class="axis" x="' . ($padL - 8) . '" y="' . ($y + 4) . '" text-anchor="end">' . ($step * $i) . '</text>';
    }
    $i = 0;
    $labelEvery = max(1, (int)ceil($n / 8));
    foreach ($series as $date => $value) {
        $x = $padL + $slot * $i + ($slot - $barW) / 2;
        $bh = ($h - $padT - $padB) * ($value / $top);
        $y = $h - $padB - $bh;
        $nice = date('j M', strtotime((string)$date));
        $svg .= '<rect class="bar" x="' . round($x, 1) . '" y="' . round($y, 1) . '" width="' . round($barW, 1) . '" height="' . round(max($bh, $value ? 1.5 : 0), 1) . '" rx="2"><title>' . e($nice) . ': ' . (int)$value . ' ' . e($unit) . '</title></rect>';
        if ($i % $labelEvery === 0) $svg .= '<text class="axis" x="' . round($x + $barW / 2, 1) . '" y="' . ($h - 8) . '" text-anchor="middle">' . e($nice) . '</text>';
        $i++;
    }
    $svg .= '</svg>';
    $table = '<div class="visually-hidden"><table><caption>' . e($label) . '</caption><tr><th>Date</th><th>' . e(ucfirst($unit)) . '</th></tr>';
    foreach ($series as $date => $value) $table .= '<tr><td>' . e($date) . '</td><td>' . (int)$value . '</td></tr>';
    return $svg . $table . '</table></div>';
}

/** Horizontal bars for a ranked list: [['label'=>..,'value'=>..,'tone'=>..], ...] */
function bar_list(array $items, string $unit = ''): string
{
    if (!$items) return '<p class="muted">No data for this period.</p>';
    $max = max(1, ...array_map(fn($r) => (int)$r['value'], $items));
    $html = '<ul class="bar-list">';
    foreach ($items as $r) {
        $pct = round(100 * (int)$r['value'] / $max);
        $html .= '<li><span class="bar-label">' . e($r['label']) . '</span><span class="bar-track"><span class="bar-fill tone-' . e($r['tone'] ?? 'green') . ' w-' . (int)(round($pct / 5) * 5) . '"></span></span><span class="bar-value">' . number_format((int)$r['value']) . ($unit ? ' ' . e($unit) : '') . '</span></li>';
    }
    return $html . '</ul>';
}

/** Daily totals from article_views between two dates, with missing days filled with 0. */
function daily_reads(string $from, string $to): array
{
    $series = [];
    for ($d = strtotime($from); $d <= strtotime($to); $d += 86400) $series[date('Y-m-d', $d)] = 0;
    foreach (rows('SELECT view_date, SUM(views) total FROM article_views WHERE view_date BETWEEN ? AND ? GROUP BY view_date', [$from, $to]) as $r) {
        $series[$r['view_date']] = (int)$r['total'];
    }
    return $series;
}
