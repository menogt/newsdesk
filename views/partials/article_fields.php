<?php
/**
 * Story form fields, shared by the student "Write" page and the admin editor.
 * Variables: $a (existing article or null), $admin (bool)
 */
$a = $a ?? [];
$kind = old('kind', $a['kind'] ?? 'news');
$categoryId = old('category_id', $a['category_id'] ?? '');
$imageSrc = image_src($a['image'] ?? '');
?>
<div class="field">
  <label for="title">Headline</label>
  <input id="title" name="title" required minlength="5" maxlength="180" value="<?= e(old('title', $a['title'] ?? '')) ?>" placeholder="Say what happened, clearly and specifically" data-count>
</div>

<div class="field-row">
  <div class="field">
    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
      <option value="">Choose a category</option>
      <?php foreach (categories() as $c): ?>
        <option value="<?= $c['id'] ?>"<?= (string)$categoryId === (string)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <fieldset class="field segmented">
    <legend>Type</legend>
    <label><input type="radio" name="kind" value="news"<?= $kind === 'news' ? ' checked' : '' ?>><span>News story</span></label>
    <label><input type="radio" name="kind" value="announcement"<?= $kind === 'announcement' ? ' checked' : '' ?>><span>Announcement</span></label>
  </fieldset>
</div>

<div class="field" data-show-when="announcement">
  <label for="expires_at">Show on the notice board until <span class="optional">optional</span></label>
  <input id="expires_at" name="expires_at" type="date" min="<?= date('Y-m-d') ?>" value="<?= e(old('expires_at', $a['expires_at'] ?? '')) ?>">
  <small class="hint">After this date the announcement moves to the archive. Leave empty to keep it up.</small>
</div>

<div class="field">
  <label for="summary">Summary</label>
  <textarea id="summary" name="summary" required minlength="15" maxlength="300" rows="2" placeholder="One or two sentences that tell readers why this matters" data-count><?= e(old('summary', $a['summary'] ?? '')) ?></textarea>
</div>

<div class="field">
  <label for="body">Story</label>
  <textarea id="body" name="body" class="body-input" required minlength="40" maxlength="20000" rows="14" placeholder="Write the full story. Leave a blank line between paragraphs." data-count><?= e(old('body', $a['body'] ?? '')) ?></textarea>
</div>

<div class="field">
  <span class="label">Cover photo <span class="optional">optional</span></span>
  <div class="upload">
    <div class="upload-preview<?= $imageSrc ? ' has-image' : '' ?>" data-preview>
      <?php if ($imageSrc): ?><img src="<?= e($imageSrc . (str_starts_with($imageSrc, 'https://') ? '&w=480&q=60' : '')) ?>" alt="Current cover photo"><?php endif; ?>
      <span class="upload-empty"><?= icon('plus') ?>No photo yet</span>
    </div>
    <div class="upload-controls">
      <label class="btn btn-outline btn-sm file-btn" for="image"><?= $imageSrc ? 'Replace photo' : 'Choose photo' ?></label>
      <input id="image" class="visually-hidden" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-max-size="2097152">
      <small class="hint">JPG, PNG or WebP, up to 2 MB, at least 400 × 225 px. A landscape photo works best.</small>
      <?php if ($imageSrc): ?><label class="check"><input type="checkbox" name="remove_image" value="1"> Remove current photo</label><?php endif; ?>
    </div>
  </div>
</div>
