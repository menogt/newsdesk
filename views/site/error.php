<?php /** Friendly error page. Variables: $code, $heading, $message */ ?>
<div class="wrap narrow error-page">
  <p class="error-code"><?= (int)$code ?></p>
  <h1><?= e($heading) ?></h1>
  <p><?= e($message) ?></p>
  <div class="error-actions">
    <a class="btn btn-primary" href="<?= url('home') ?>">Go to the homepage</a>
    <?php if (!user()): ?><a class="btn btn-outline" href="<?= url('login') ?>">Sign in</a><?php endif; ?>
  </div>
</div>
