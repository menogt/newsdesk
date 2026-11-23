<?php
/** Student story editor. New stories and edits both go to the editors for review. */
$id = get_int('id');
$a = $id ? one('SELECT * FROM articles WHERE id = ?', [$id]) : null;
if ($id && !$a) abort(404, 'Story not found', 'This story may have been deleted.');
if ($a && !is_admin() && (int)$a['author_id'] !== user_id()) abort(403, 'Not your story', 'You can only edit stories you wrote.');
if ($a && is_admin()) redirect('admin_article', ['id' => $id]);
$GLOBALS['page_title'] = $a ? 'Edit story' : 'Write a story';
?>
<div class="wrap write-layout">
  <form class="write-form" method="post" enctype="multipart/form-data" data-validate novalidate>
    <header class="page-head">
      <h1><?= $a ? 'Edit your story' : 'Write a story' ?></h1>
      <p><?= $a ? 'Saving sends your changes to the editors. ' . ($a['status'] === 'approved' ? 'The story is hidden until they approve the new version.' : '') : 'Tell campus what is happening. The editors review every submission before it is published.' ?></p>
    </header>
    <?php if ($a && $a['review_note']): ?>
      <div class="editor-note"><strong>Note from the editors</strong><p><?= e($a['review_note']) ?></p></div>
    <?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="article_save">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
    <?php view('partials/article_fields', ['a' => $a, 'admin' => false]); ?>
    <div class="form-actions">
      <button class="btn btn-primary"><?= $a ? 'Send changes for review' : 'Send for review' ?></button>
      <a class="btn btn-quiet" href="<?= url('my_stories') ?>">Cancel</a>
    </div>
  </form>

  <aside class="write-tips">
    <h2>What the editors look for</h2>
    <ul>
      <li><strong>Relevant to campus.</strong> Faculty news, club activities, events, results and achievements.</li>
      <li><strong>Facts first.</strong> Who, what, when and where in the first paragraph.</li>
      <li><strong>Your own words and photos.</strong> Only upload pictures you took or have permission to use.</li>
      <li><strong>Respectful.</strong> No personal attacks or private information about others.</li>
    </ul>
    <p>You will get a notification when the editors publish your story or ask for changes.</p>
  </aside>
</div>
