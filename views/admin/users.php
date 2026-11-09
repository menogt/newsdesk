<?php
/** User management: search, add, edit role, block and delete. */
$q = mb_substr(get_str('q'), 0, 100);
$role = get_str('role');
$edit = get_int('edit') ? one('SELECT * FROM users WHERE id = ?', [get_int('edit')]) : null;
$where = ['1 = 1']; $params = [];
if ($q !== '') { $where[] = '(u.name LIKE ? OR u.email LIKE ?)'; $like = '%' . addcslashes($q, '%_\\') . '%'; array_push($params, $like, $like); }
if (in_array($role, ['student', 'admin'], true)) { $where[] = 'u.role = ?'; $params[] = $role; } else $role = '';
$list = rows('SELECT u.*,
   (SELECT COUNT(*) FROM articles a WHERE a.author_id = u.id) AS stories,
   (SELECT COUNT(*) FROM comments c WHERE c.user_id = u.id) AS comments
  FROM users u WHERE ' . implode(' AND ', $where) . " ORDER BY u.role = 'admin' DESC, u.created_at DESC LIMIT 200", $params);
$formRole = old('role', $edit['role'] ?? 'student');
?>
<div class="split split-wide">
  <div>
    <form class="toolbar" method="get" action="index.php">
      <input type="hidden" name="page" value="admin_users">
      <label class="visually-hidden" for="u-q">Search name or email</label>
      <input id="u-q" name="q" type="search" value="<?= e($q) ?>" placeholder="Search name or email">
      <label class="visually-hidden" for="u-role">Role</label>
      <select id="u-role" name="role"><option value="">All roles</option><option value="student"<?= $role === 'student' ? ' selected' : '' ?>>Students</option><option value="admin"<?= $role === 'admin' ? ' selected' : '' ?>>Administrators</option></select>
      <button class="btn btn-outline btn-sm">Filter</button>
      <?php if ($q !== '' || $role): ?><a class="btn btn-quiet btn-sm" href="<?= url('admin_users') ?>">Clear</a><?php endif; ?>
    </form>
    <p class="result-count"><?= plural(count($list), 'account') ?></p>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col" class="num">Stories</th><th scope="col" class="num">Comments</th><th scope="col">Last sign-in</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
        <tbody>
        <?php foreach ($list as $u): $self = (int)$u['id'] === user_id(); ?>
          <tr<?= $u['status'] === 'blocked' ? ' class="row-muted"' : '' ?>>
            <td class="cell-person"><span class="avatar avatar-sm"><?= e(initials($u['name'])) ?></span><div><span class="cell-title"><?= e($u['name']) ?><?= $self ? ' (you)' : '' ?></span><span class="cell-sub"><?= e($u['email']) ?></span></div></td>
            <td><?= $u['role'] === 'admin' ? '<span class="badge badge-editor">Administrator</span>' : 'Student' ?></td>
            <td><?= status_badge($u['status']) ?></td>
            <td class="num"><?= (int)$u['stories'] ?></td>
            <td class="num"><?= (int)$u['comments'] ?></td>
            <td class="nowrap"><?= $u['last_login_at'] ? e(time_ago($u['last_login_at'])) : '<span class="muted">Never</span>' ?></td>
            <td class="cell-actions">
              <a class="btn btn-sm btn-outline" href="<?= url('admin_users', ['edit' => $u['id'], 'q' => $q, 'role' => $role]) ?>">Edit</a>
              <?php if (!$self): ?>
                <?= action_button('user_status', (int)$u['id'], $u['status'] === 'active' ? 'Block' : 'Unblock', 'btn btn-sm btn-quiet', $u['status'] === 'active' ? 'Block ' . $u['name'] . '? They will be signed out and cannot sign in.' : '') ?>
                <?php if (!(int)$u['stories']): ?><?= action_button('user_delete', (int)$u['id'], '<span class="visually-hidden">Delete ' . e($u['name']) . '</span>' . icon('x'), 'icon-btn icon-btn-danger', 'Delete ' . $u['name'] . '’s account and their comments, bookmarks and follows?') ?><?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <form method="post" class="panel" data-validate novalidate>
    <h2><?= $edit ? 'Edit ' . e($edit['name']) : 'Add an account' ?></h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="user_save">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="field"><label for="u-name">Full name</label><input id="u-name" name="name" required minlength="2" maxlength="80" value="<?= e(old('name', $edit['name'] ?? '')) ?>"></div>
    <div class="field"><label for="u-email">Email address</label><input id="u-email" name="email" type="email" required maxlength="190" value="<?= e(old('email', $edit['email'] ?? '')) ?>"></div>
    <div class="field"><label for="u-role-f">Role</label>
      <select id="u-role-f" name="role"><option value="student"<?= $formRole === 'student' ? ' selected' : '' ?>>Student</option><option value="admin"<?= $formRole === 'admin' ? ' selected' : '' ?>>Administrator</option></select>
    </div>
    <?php if (!$edit): ?>
      <div class="field"><label for="u-pass">Temporary password</label><input id="u-pass" name="password" type="text" required minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}" autocomplete="off"><small class="hint">At least 8 characters with a letter and a number. Ask them to change it from their profile.</small></div>
    <?php endif; ?>
    <div class="row-actions">
      <button class="btn btn-primary"><?= $edit ? 'Save changes' : 'Create account' ?></button>
      <?php if ($edit): ?><a class="btn btn-quiet" href="<?= url('admin_users') ?>">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>
