<?php
/** Browse all published news with search, filters and sorting. */
$cat = get_int('category') ? one('SELECT * FROM categories WHERE id = ?', [get_int('category')]) : null;
$GLOBALS['page_title'] = $cat ? $cat['name'] : 'All news';
?>
<div class="wrap">
  <header class="page-head<?= $cat ? ' tone-' . e($cat['color']) : '' ?>">
    <h1><?= e($cat['name'] ?? 'All news') ?></h1>
    <p><?= e($cat['description'] ?? 'Every published story and announcement from across campus.') ?></p>
    <?php if ($cat && user()):
      $following = one('SELECT 1 FROM subscriptions WHERE user_id = ? AND category_id = ?', [user_id(), $cat['id']]); ?>
      <?= action_button('subscribe', (int)$cat['id'], $following ? icon('check') . ' Following' : icon('plus') . ' Follow ' . e($cat['name']), $following ? 'btn btn-outline' : 'btn btn-primary', '', ['return' => url('news', ['category' => $cat['id']])]) ?>
    <?php endif; ?>
  </header>
  <?php view('partials/listing', [
      'page_name' => 'news', 'base_where' => [], 'base_params' => [],
      'empty_heading' => 'Nothing published here yet', 'empty_text' => 'Stories appear here as soon as the editors publish them.',
      'empty_url' => url('write'), 'empty_label' => 'Submit a story',
  ]); ?>
</div>
