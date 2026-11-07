<?php
/** Administrator layout with sidebar. Variables: $page, $title, $content, $error, $flash */
$me = user();
$pendingStories  = (int)scalar("SELECT COUNT(*) FROM articles WHERE status = 'pending'");
$pendingComments = (int)scalar("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
$menu = [
    'admin'            => ['Dashboard', 'grid', 0],
    'admin_articles'   => ['Stories', 'news', $pendingStories],
    'admin_comments'   => ['Comments', 'chat', $pendingComments],
    'admin_categories' => ['Categories', 'tag', 0],
    'admin_users'      => ['Users', 'users', 0],
    'admin_reports'    => ['Reports', 'chart', 0],
];
$current = $page === 'admin_article' ? 'admin_articles' : $page;
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | NewsDesk Admin</title>
  <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preload" href="assets/fonts/archivo.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="assets/css/site.css">
  <link rel="stylesheet" href="assets/css/admin.css">
  <script src="assets/js/app.js" defer></script>
</head>
<body class="admin page-<?= e($page) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar">
    <a class="logo logo-light" href="<?= url('admin') ?>">
      <span class="logo-mark" aria-hidden="true">N</span>
      <span class="logo-text"><span class="logo-name">NewsDesk</span><span class="logo-sub">Editorial admin</span></span>
    </a>
    <nav class="side-nav" aria-label="Admin">
      <?php foreach ($menu as $key => [$label, $ic, $count]): ?>
        <a href="<?= url($key) ?>"<?= $current === $key ? ' aria-current="page"' : '' ?>><?= icon($ic) ?><span><?= e($label) ?></span><?php if ($count): ?><span class="side-count" title="Waiting for review"><?= $count ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a href="<?= url('home') ?>" target="_blank" rel="noopener"><?= icon('external') ?><span>View public site</span></a>
      <div class="side-user">
        <span class="avatar avatar-light"><?= e(initials($me['name'])) ?></span>
        <span class="side-user-text"><strong><?= e($me['name']) ?></strong><small>Administrator</small></span>
      </div>
      <form method="post" action="index.php?page=home"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="side-logout"><?= icon('logout') ?><span>Sign out</span></button></form>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="icon-btn side-toggle" type="button" aria-expanded="false" aria-controls="sidebar" aria-label="Open admin menu"><?= icon('menu') ?></button>
      <h1 class="admin-title"><?= e($title) ?></h1>
      <a class="btn btn-primary btn-sm" href="<?= url('admin_article') ?>"><?= icon('plus') ?> New story</a>
    </header>
    <main id="main" class="admin-content">
      <?php if ($error): ?><div class="toast toast-error" role="alert"><?= icon('x') ?><span><?= e($error) ?></span></div><?php endif; ?>
      <?php if ($flash): ?><div class="toast toast-<?= e($flash['type']) ?>" role="status"><?= icon($flash['type'] === 'success' ? 'check' : 'bell') ?><span><?= e($flash['message']) ?></span><button type="button" class="toast-close" aria-label="Dismiss"><?= icon('x') ?></button></div><?php endif; ?>
      <?= $content ?>
    </main>
  </div>
</div>
</body>
</html>
