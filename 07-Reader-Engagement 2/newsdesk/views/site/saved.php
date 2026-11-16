<?php /** Bookmarked stories. */ ?>
<div class="wrap">
  <header class="page-head">
    <h1>Saved stories</h1>
    <p>Stories you bookmarked to read later. Use the bookmark button on any story to add it here.</p>
  </header>
  <?php view('partials/listing', [
      'page_name' => 'saved',
      'base_where' => ['a.id IN (SELECT article_id FROM bookmarks WHERE user_id = ?)'], 'base_params' => [user_id()],
      'empty_heading' => 'No saved stories yet', 'empty_text' => 'Press Save on any story and it will wait for you here.',
      'empty_url' => url('news'), 'empty_label' => 'Browse news',
  ]); ?>
</div>
