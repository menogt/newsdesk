<?php /** Empty state. Variables: $heading, $text, optional $action_url, $action_label */ ?>
<div class="empty-state">
  <span class="empty-icon"><?= icon($icon ?? 'news') ?></span>
  <h2><?= e($heading) ?></h2>
  <p><?= e($text) ?></p>
  <?php if (!empty($action_url)): ?><a class="btn btn-primary" href="<?= e($action_url) ?>"><?= e($action_label) ?></a><?php endif; ?>
</div>
