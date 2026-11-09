<?php
/** Admin editor: review a submission (approve / reject with feedback) and edit any story. */
$id = get_int('id');
$a = $id ? one(ARTICLE_SELECT . ' WHERE a.id = ?', [$id]) : null;
if ($id && !$a) abort(404, 'Story not found', 'This story may have been deleted.');
$GLOBALS['page_title'] = $a ? ($a['status'] === 'pending' ? 'Review story' : 'Edit story') : 'New story';
$status = old('status', $a['status'] ?? 'approved');
$checked = fn($key) => (is_post() ? isset($_POST[$key]) : !empty($a[$key])) ? ' checked' : '';
?>
<a class="back-link" href="<?= url('admin_articles') ?>">Back to stories</a>

<?php if ($a && $a['status'] === 'pending'): ?>
<section class="review-box">
  <div>
    <h2>Waiting for review</h2>
    <p><?= e($a['author']) ?> sent this <?= e(time_ago($a['updated_at'])) ?><?= $a['published_at'] ? '. It is an edit of a story that was already published; approving keeps its original date' : '' ?>. Read it below, edit if needed, then decide.</p>
  </div>
  <form method="post" class="review-form" data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="article_review">
    <input type="hidden" name="id" value="<?= $a['id'] ?>">
    <div class="field">
      <label for="review_note">Feedback for the author <span class="optional">required when rejecting</span></label>
      <textarea id="review_note" name="review_note" rows="2" maxlength="300" placeholder="For example: please add the event date and venue"><?= e(old('review_note', $a['review_note'])) ?></textarea>
    </div>
    <div class="row-actions">
      <button class="btn btn-primary" name="decision" value="approved"><?= icon('check') ?>Approve and publish</button>
      <button class="btn btn-danger-outline" name="decision" value="rejected" data-needs="review_note"><?= icon('x') ?>Reject</button>
      <a class="btn btn-quiet" href="<?= url('article', ['id' => $a['id']]) ?>" target="_blank" rel="noopener"><?= icon('eye') ?>Preview</a>
    </div>
  </form>
</section>
<?php endif; ?>

<div class="admin-editor">
  <form id="story-form" class="editor-main panel" method="post" enctype="multipart/form-data" data-validate novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="article_save">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
    <?php view('partials/article_fields', ['a' => $a, 'admin' => true]); ?>
  </form>
  <aside class="editor-side">
    <section class="panel">
      <h2>Publishing</h2>
      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" form="story-form">
          <?php foreach (['approved' => 'Published', 'pending' => 'Pending review', 'rejected' => 'Rejected'] as $k => $label): ?>
            <option value="<?= $k ?>"<?= $status === $k ? ' selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <label class="check"><input type="checkbox" name="featured" value="1" form="story-form"<?= $checked('featured') ?>> Feature at the top of the homepage</label>
      <label class="check"><input type="checkbox" name="breaking" value="1" form="story-form"<?= $checked('breaking') ?>> Show in the breaking news strip</label>
      <button class="btn btn-primary btn-block" form="story-form"><?= $a ? 'Save changes' : 'Save story' ?></button>
      <?php if ($a): ?>
        <dl class="meta-list">
          <dt>Author</dt><dd><?= e($a['author']) ?></dd>
          <dt>Created</dt><dd><?= date_label($a['created_at'], 'j M Y, g:i a') ?></dd>
          <dt>Published</dt><dd><?= date_label($a['published_at'], 'j M Y, g:i a') ?></dd>
          <dt>Reads</dt><dd><?= number_format((int)$a['views']) ?></dd>
        </dl>
      <?php endif; ?>
    </section>
    <?php if ($a): ?>
      <section class="panel panel-danger">
        <h2>Delete story</h2>
        <p>Removes the story with its comments, bookmarks and read history.</p>
        <?= action_button('article_delete', (int)$a['id'], 'Delete story', 'btn btn-danger-outline btn-sm', 'Delete “' . $a['title'] . '” permanently?') ?>
      </section>
    <?php endif; ?>
  </aside>
</div>
