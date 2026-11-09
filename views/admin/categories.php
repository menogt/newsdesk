<?php
/** Manage categories (create, edit, delete). */
$edit = get_int('edit') ? one('SELECT * FROM categories WHERE id = ?', [get_int('edit')]) : null;
$list = rows("SELECT c.*,
   (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id) AS stories,
   (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.status = 'approved') AS published,
   (SELECT COUNT(*) FROM subscriptions s WHERE s.category_id = c.id) AS followers
 FROM categories c ORDER BY c.name");
$tones = ['green' => 'Green', 'blue' => 'Blue', 'orange' => 'Orange', 'purple' => 'Purple', 'teal' => 'Teal', 'rose' => 'Rose', 'slate' => 'Slate'];
$color = old('color', $edit['color'] ?? 'green');
?>
<div class="split">
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th scope="col">Category</th><th scope="col" class="num">Published</th><th scope="col" class="num">All stories</th><th scope="col" class="num">Followers</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
      <tbody>
      <?php foreach ($list as $c): ?>
        <tr<?= $edit && (int)$edit['id'] === (int)$c['id'] ? ' class="row-editing"' : '' ?>>
          <td><span class="cell-title"><span class="dot tone-<?= e($c['color']) ?>"></span><?= e($c['name']) ?></span><span class="cell-sub"><?= e($c['description']) ?></span></td>
          <td class="num"><?= (int)$c['published'] ?></td>
          <td class="num"><?= (int)$c['stories'] ?></td>
          <td class="num"><?= (int)$c['followers'] ?></td>
          <td class="cell-actions">
            <a class="btn btn-sm btn-outline" href="<?= url('admin_categories', ['edit' => $c['id']]) ?>">Edit</a>
            <?php if ((int)$c['stories'] === 0): ?>
              <?= action_button('category_delete', (int)$c['id'], '<span class="visually-hidden">Delete ' . e($c['name']) . '</span>' . icon('x'), 'icon-btn icon-btn-danger', 'Delete the ' . $c['name'] . ' category? Followers will lose this subscription.') ?>
            <?php else: ?>
              <span class="icon-btn is-disabled" title="Move or delete its stories first"><?= icon('x') ?></span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <form method="post" class="panel" data-validate novalidate>
    <h2><?= $edit ? 'Edit ' . e($edit['name']) : 'Add a category' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="category_save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="field"><label for="c-name">Name</label><input id="c-name" name="name" required minlength="2" maxlength="60" value="<?= e(old('name', $edit['name'] ?? '')) ?>"></div>
    <div class="field"><label for="c-desc">Description <span class="optional">optional</span></label><textarea id="c-desc" name="description" rows="3" maxlength="200" data-count><?= e(old('description', $edit['description'] ?? '')) ?></textarea></div>
    <fieldset class="field swatches">
      <legend>Colour</legend>
      <?php foreach ($tones as $k => $label): ?>
        <label class="swatch tone-<?= $k ?>"><input type="radio" name="color" value="<?= $k ?>"<?= $color === $k ? ' checked' : '' ?>><span><?= $label ?></span></label>
      <?php endforeach; ?>
    </fieldset>
    <div class="row-actions">
      <button class="btn btn-primary"><?= $edit ? 'Save changes' : 'Add category' ?></button>
      <?php if ($edit): ?><a class="btn btn-quiet" href="<?= url('admin_categories') ?>">Cancel</a><?php endif; ?>
    </div>
    <p class="hint">A category can only be deleted when it has no stories.</p>
  </form>
</div>
