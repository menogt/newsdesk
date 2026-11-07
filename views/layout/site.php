<?php
/** Public / student layout. Variables: $page, $title, $content, $error, $flash */
$me = user();
$unread = unread_notifications();
$nav = ['home' => 'Home', 'news' => 'All news', 'announcements' => 'Announcements'];
$activeCategory = $page === 'news' ? get_int('category') : 0;
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | NSBM NewsDesk</title>
  <meta name="description" content="Campus news, announcements and student stories from NSBM Green University.">
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preload" href="assets/fonts/archivo.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="assets/css/site.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body class="site page-<?= e($page) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="masthead">
  <div class="masthead-inner wrap">
    <a class="logo" href="<?= url() ?>" aria-label="NSBM NewsDesk home">
      <span class="logo-mark" aria-hidden="true">N</span>
      <span class="logo-text"><span class="logo-name">NewsDesk</span><span class="logo-sub">NSBM Green University</span></span>
    </a>

    <form class="header-search" method="get" action="index.php" role="search">
      <input type="hidden" name="page" value="news">
      <label class="visually-hidden" for="header-q">Search news</label>
      <?= icon('search') ?>
      <input id="header-q" name="q" type="search" placeholder="Search news, clubs, events" value="<?= $page === 'news' ? e(get_str('q')) : '' ?>" maxlength="150">
    </form>

    <div class="header-actions">
      <?php if ($me): ?>
        <a class="btn btn-primary btn-sm hide-sm" href="<?= url('write') ?>"><?= icon('pen') ?> Write</a>
        <a class="icon-btn" href="<?= url('notifications') ?>" aria-label="Notifications<?= $unread ? " ($unread unread)" : '' ?>">
          <?= icon('bell') ?><?php if ($unread): ?><span class="dot-count"><?= $unread > 9 ? '9+' : $unread ?></span><?php endif; ?>
        </a>
        <details class="account-menu">
          <summary aria-label="Account menu"><span class="avatar"><?= e(initials($me['name'])) ?></span></summary>
          <div class="menu-panel">
            <p class="menu-who"><strong><?= e($me['name']) ?></strong><span><?= e($me['email']) ?></span></p>
            <?php if (is_admin()): ?><a class="menu-admin" href="<?= url('admin') ?>"><?= icon('grid') ?> Admin dashboard</a><?php endif; ?>
            <a href="<?= url('feed') ?>"><?= icon('news') ?> My feed</a>
            <a href="<?= url('saved') ?>"><?= icon('bookmark') ?> Saved stories</a>
            <a href="<?= url('my_stories') ?>"><?= icon('pen') ?> My stories</a>
            <a href="<?= url('following') ?>"><?= icon('tag') ?> Following</a>
            <a href="<?= url('profile') ?>"><?= icon('user') ?> Profile and password</a>
            <form method="post" action="index.php?page=home"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button><?= icon('logout') ?> Sign out</button></form>
          </div>
        </details>
      <?php else: ?>
        <a class="btn btn-quiet btn-sm" href="<?= url('login') ?>">Sign in</a>
        <a class="btn btn-primary btn-sm" href="<?= url('register') ?>">Join NewsDesk</a>
      <?php endif; ?>
      <button class="icon-btn nav-toggle" type="button" aria-expanded="false" aria-controls="section-nav" aria-label="Open menu"><?= icon('menu') ?></button>
    </div>
  </div>

  <nav id="section-nav" class="section-nav" aria-label="Sections">
    <div class="wrap section-nav-inner">
      <div class="nav-primary">
        <?php foreach ($nav as $key => $label): ?>
          <a href="<?= url($key) ?>"<?= $page === $key && !$activeCategory ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
        <span class="nav-sep" aria-hidden="true"></span>
        <?php foreach (categories() as $c): ?>
          <a class="nav-cat tone-<?= e($c['color']) ?>" href="<?= url('news', ['category' => $c['id']]) ?>"<?= $activeCategory === (int)$c['id'] ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <?php if ($me): ?>
        <div class="nav-personal">
          <a href="<?= url('feed') ?>"<?= $page === 'feed' ? ' aria-current="page"' : '' ?>>My feed</a>
          <a href="<?= url('saved') ?>"<?= $page === 'saved' ? ' aria-current="page"' : '' ?>>Saved</a>
          <a class="show-sm" href="<?= url('write') ?>">Write a story</a>
        </div>
      <?php else: ?>
        <div class="nav-personal show-sm">
          <a href="<?= url('register') ?>">Create an account</a>
        </div>
      <?php endif; ?>
    </div>
  </nav>
</header>

<main id="main" class="main">
  <?php if ($flash || $error): ?>
    <div class="wrap toasts">
      <?php if ($error): ?><div class="toast toast-error" role="alert"><?= icon('x') ?><span><?= e($error) ?></span></div><?php endif; ?>
      <?php if ($flash): ?><div class="toast toast-<?= e($flash['type']) ?>" role="status"><?= icon($flash['type'] === 'success' ? 'check' : 'bell') ?><span><?= e($flash['message']) ?></span><button type="button" class="toast-close" aria-label="Dismiss"><?= icon('x') ?></button></div><?php endif; ?>
    </div>
  <?php endif; ?>
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div class="footer-about">
      <a class="logo logo-light" href="<?= url() ?>"><span class="logo-mark" aria-hidden="true">N</span><span class="logo-text"><span class="logo-name">NewsDesk</span><span class="logo-sub">NSBM Green University</span></span></a>
      <p>News, announcements and stories from faculties, clubs and students across the Pitipana campus.</p>
    </div>
    <div>
      <h2>Sections</h2>
      <ul>
        <?php foreach (categories() as $c): ?><li><a href="<?= url('news', ['category' => $c['id']]) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
        <li><a href="<?= url('announcements') ?>">Announcements</a></li>
      </ul>
    </div>
    <div>
      <h2>Take part</h2>
      <ul>
        <li><a href="<?= url('write') ?>">Submit a story</a></li>
        <li><a href="<?= url('following') ?>">Follow categories</a></li>
        <li><a href="<?= url($me ? 'profile' : 'register') ?>"><?= $me ? 'Your profile' : 'Create an account' ?></a></li>
      </ul>
    </div>
  </div>
  <div class="wrap footer-note">
    <span>© <?= date('Y') ?> NSBM NewsDesk</span>
    <span>Web and Mobile Application Development coursework, Batch 26.1 IFSL. Sample content for demonstration.</span>
  </div>
</footer>
</body>
</html>
