<?php
/** Profile details, password change and personal activity summary. */
$me = user();
$stats = one("SELECT
    (SELECT COUNT(*) FROM articles WHERE author_id = ? AND status = 'approved') AS published,
    (SELECT COUNT(*) FROM articles WHERE author_id = ? AND status = 'pending') AS pending,
    (SELECT COUNT(*) FROM comments WHERE user_id = ?) AS comments,
    (SELECT COUNT(*) FROM bookmarks WHERE user_id = ?) AS saved", array_fill(0, 4, user_id()));
$which = post('action');
?>
<div class="wrap narrow">
  <header class="page-head profile-head">
    <span class="avatar avatar-lg"><?= e(initials($me['name'])) ?></span>
    <div><h1><?= e($me['name']) ?></h1><p><?= is_admin() ? 'Editor' : 'Student' ?>, member since <?= date_label($me['created_at'], 'F Y') ?></p></div>
  </header>

  <dl class="stat-strip">
    <div><dt>Published stories</dt><dd><?= (int)$stats['published'] ?></dd></div>
    <div><dt>Waiting for review</dt><dd><?= (int)$stats['pending'] ?></dd></div>
    <div><dt>Comments</dt><dd><?= (int)$stats['comments'] ?></dd></div>
    <div><dt>Saved stories</dt><dd><?= (int)$stats['saved'] ?></dd></div>
  </dl>

  <div class="profile-forms">
    <form method="post" class="panel" data-validate novalidate>
      <h2>Your details</h2>
      <?= csrf_field() ?><input type="hidden" name="action" value="profile_save">
      <div class="field"><label for="p-name">Full name</label><input id="p-name" name="name" required minlength="2" maxlength="80" value="<?= e($which === 'profile_save' ? post('name') : $me['name']) ?>" autocomplete="name"></div>
      <div class="field"><label for="p-email">Email address</label><input id="p-email" name="email" type="email" required maxlength="190" value="<?= e($which === 'profile_save' ? post('email') : $me['email']) ?>" autocomplete="email"></div>
      <button class="btn btn-primary">Save details</button>
    </form>

    <form method="post" class="panel" data-validate novalidate>
      <h2>Change password</h2>
      <?= csrf_field() ?><input type="hidden" name="action" value="password_change">
      <div class="field"><label for="p-current">Current password</label><input id="p-current" name="current_password" type="password" required autocomplete="current-password"></div>
      <div class="field"><label for="p-new">New password</label><input id="p-new" name="new_password" type="password" required minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}" autocomplete="new-password" data-strength><small class="hint">At least 8 characters, with a letter and a number.</small><div class="strength" aria-hidden="true"><span></span></div></div>
      <div class="field"><label for="p-confirm">Confirm new password</label><input id="p-confirm" name="confirm_password" type="password" required autocomplete="new-password" data-match="p-new"></div>
      <button class="btn btn-primary">Change password</button>
    </form>
  </div>
</div>
