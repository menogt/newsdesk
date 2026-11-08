<?php
/** Homepage: breaking strip, lead story, latest list, notice board, category rows. */
$breaking = rows("SELECT id, title FROM articles WHERE status = 'approved' AND breaking = 1 ORDER BY published_at DESC LIMIT 3");
$lead = one(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.featured = 1 ORDER BY a.published_at DESC LIMIT 1")
     ?? one(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'news' ORDER BY a.published_at DESC LIMIT 1");
$leadId = (int)($lead['id'] ?? 0);
$second = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.featured = 1 AND a.id <> ? ORDER BY a.published_at DESC LIMIT 2", [$leadId]);
$shown = array_merge([$leadId], array_map(fn($a) => (int)$a['id'], $second));
$latest = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'news' AND a.id NOT IN (" . implode(',', $shown) . ") ORDER BY a.published_at DESC LIMIT 6");
$notices = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'announcement' AND " . ACTIVE_ANNOUNCEMENT . " ORDER BY a.published_at DESC LIMIT 4");
$popular = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.published_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY a.views DESC LIMIT 5");
$sections = rows("SELECT c.* FROM categories c WHERE EXISTS (SELECT 1 FROM articles a WHERE a.category_id = c.id AND a.status = 'approved' AND a.kind = 'news') ORDER BY c.name");
?>

<?php if ($breaking): ?>
<div class="breaking" role="region" aria-label="Breaking news">
  <div class="wrap breaking-inner">
    <span class="breaking-label"><?= icon('bolt') ?>Breaking</span>
    <ul class="breaking-items">
      <?php foreach ($breaking as $b): ?><li><a href="<?= url('article', ['id' => $b['id']]) ?>"><?= e($b['title']) ?></a></li><?php endforeach; ?>
    </ul>
  </div>
</div>
<?php endif; ?>

<div class="wrap">
  <p class="dateline"><?= date('l, j F Y') ?></p>

  <?php if (!$lead): ?>
    <?php view('partials/empty', ['heading' => 'No stories published yet', 'text' => 'Once the editors publish the first story, it will appear here.', 'action_url' => url('write'), 'action_label' => 'Write the first story']); ?>
  <?php else: ?>
  <section class="front" aria-label="Top stories">
    <article class="lead">
      <a class="lead-media" href="<?= url('article', ['id' => $lead['id']]) ?>" tabindex="-1" aria-hidden="true"><?= cover($lead, 'ratio-lead', '1400') ?></a>
      <div class="lead-body">
        <div class="story-meta"><?= category_label($lead) ?><time datetime="<?= e($lead['published_at']) ?>"><?= e(time_ago($lead['published_at'])) ?></time></div>
        <h1 class="lead-title"><a href="<?= url('article', ['id' => $lead['id']]) ?>"><?= e($lead['title']) ?></a></h1>
        <p class="lead-summary"><?= e($lead['summary']) ?></p>
        <p class="byline-small">By <?= e($lead['author']) ?>, <?= read_time($lead['body']) ?></p>
      </div>
    </article>

    <div class="front-side">
      <?php foreach ($second as $a): ?>
        <article class="second">
          <a href="<?= url('article', ['id' => $a['id']]) ?>" tabindex="-1" aria-hidden="true"><?= cover($a, 'ratio-wide', '640') ?></a>
          <div class="story-meta"><?= category_label($a) ?></div>
          <h2><a href="<?= url('article', ['id' => $a['id']]) ?>"><?= e($a['title']) ?></a></h2>
        </article>
      <?php endforeach; ?>
    </div>

    <aside class="latest" aria-labelledby="latest-h">
      <h2 id="latest-h" class="rail-heading">Latest</h2>
      <?php foreach ($latest as $a) view('partials/story_card', ['a' => $a, 'style' => 'compact']); ?>
      <a class="rail-more" href="<?= url('news') ?>">See all news</a>
    </aside>
  </section>
  <?php endif; ?>

  <?php if ($notices): ?>
  <section class="noticeboard" aria-labelledby="notice-h">
    <div class="section-head">
      <h2 id="notice-h">Notice board</h2>
      <a href="<?= url('announcements') ?>">All announcements</a>
    </div>
    <div class="notice-grid">
      <?php foreach ($notices as $n): ?>
        <article class="notice">
          <time class="notice-date" datetime="<?= e($n['published_at']) ?>"><span><?= date('j', strtotime($n['published_at'])) ?></span><?= date('M', strtotime($n['published_at'])) ?></time>
          <div>
            <p class="notice-cat tone-<?= e($n['color']) ?>"><?= e($n['category']) ?></p>
            <h3><a href="<?= url('article', ['id' => $n['id']]) ?>"><?= e($n['title']) ?></a></h3>
            <?php if ($n['expires_at']): ?><p class="notice-until">Until <?= date_label($n['expires_at'], 'j M') ?></p><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <div class="home-columns">
    <div class="home-sections">
      <?php foreach ($sections as $s):
        $items = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.kind = 'news' AND a.category_id = ? AND a.id NOT IN (" . implode(',', $shown) . ") ORDER BY a.published_at DESC LIMIT 3", [$s['id']]);
        if (!$items) continue;
      ?>
        <section class="cat-section tone-<?= e($s['color']) ?>" aria-labelledby="sec-<?= $s['id'] ?>">
          <div class="section-head">
            <h2 id="sec-<?= $s['id'] ?>"><?= e($s['name']) ?></h2>
            <a href="<?= url('news', ['category' => $s['id']]) ?>">More <?= e(mb_strtolower($s['name'])) ?></a>
          </div>
          <div class="story-grid story-grid-3">
            <?php foreach ($items as $a) view('partials/story_card', ['a' => $a]); ?>
          </div>
        </section>
      <?php endforeach; ?>
    </div>

    <aside class="home-rail">
      <?php if ($popular): ?>
      <section class="rail-box" aria-labelledby="pop-h">
        <h2 id="pop-h" class="rail-heading">Most read this month</h2>
        <ol class="most-read">
          <?php foreach ($popular as $a): ?>
            <li><a href="<?= url('article', ['id' => $a['id']]) ?>"><?= e($a['title']) ?></a><span><?= plural((int)$a['views'], 'read') ?></span></li>
          <?php endforeach; ?>
        </ol>
      </section>
      <?php endif; ?>

      <section class="rail-box rail-cta">
        <h2>Got news from your faculty or club?</h2>
        <p>Send it in. The NewsDesk editors review every submission and publish the ones that matter to campus.</p>
        <a class="btn btn-primary" href="<?= url('write') ?>"><?= icon('pen') ?> Submit a story</a>
      </section>

      <?php if (!user()): ?>
      <section class="rail-box">
        <h2 class="rail-heading">Make it yours</h2>
        <p>Create a free account to follow categories, save stories and join the conversation.</p>
        <a class="btn btn-outline" href="<?= url('register') ?>">Create an account</a>
      </section>
      <?php endif; ?>
    </aside>
  </div>
</div>
