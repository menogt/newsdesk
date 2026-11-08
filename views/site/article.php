<?php
/** A single story with bookmark, share, comments and related stories. */
$a = one(ARTICLE_SELECT . ' WHERE a.id = ?', [get_int('id')]);
$isAuthor = $a && (int)$a['author_id'] === user_id();
if (!$a || ($a['status'] !== 'approved' && !is_admin() && !$isAuthor)) {
    abort(404, 'Story not found', 'This story is not published, or it has been removed.');
}
record_view($a);
$GLOBALS['page_title'] = $a['title'];

$saved = user() ? (bool)one('SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?', [user_id(), $a['id']]) : false;
$comments = rows("SELECT c.*, u.name, u.role FROM comments c JOIN users u ON u.id = c.user_id
                  WHERE c.article_id = ? AND (c.status = 'approved' OR c.user_id = ? OR ? = 1)
                  ORDER BY c.created_at ASC", [$a['id'], user_id(), is_admin() ? 1 : 0]);
$approvedCount = count(array_filter($comments, fn($c) => $c['status'] === 'approved'));
$related = rows(ARTICLE_SELECT . " WHERE a.status = 'approved' AND a.category_id = ? AND a.id <> ? ORDER BY a.published_at DESC LIMIT 3", [$a['category_id'], $a['id']]);
$here = url('article', ['id' => $a['id']]);
?>
<article class="story">
  <?php if ($a['status'] !== 'approved'): ?>
    <div class="wrap narrow"><div class="preview-banner"><?= status_badge($a['status']) ?><span>Preview. Only you<?= is_admin() ? ', the author' : '' ?> and the editors can see this story until it is published.</span>
      <?php if (is_admin()): ?><a class="btn btn-sm btn-primary" href="<?= url('admin_article', ['id' => $a['id']]) ?>">Review</a><?php endif; ?></div></div>
  <?php endif; ?>

  <header class="wrap narrow story-head">
    <div class="story-meta"><?= category_label($a) ?><?php if ($a['kind'] === 'announcement'): ?><span class="kind-flag"><?= icon('megaphone') ?>Announcement</span><?php endif; ?></div>
    <h1><?= e($a['title']) ?></h1>
    <p class="standfirst"><?= e($a['summary']) ?></p>
    <div class="byline">
      <span class="avatar"><?= e(initials($a['author'])) ?></span>
      <div class="byline-text">
        <strong><?= e($a['author']) ?></strong>
        <span><time datetime="<?= e($a['published_at']) ?>"><?= date_label($a['published_at'], 'j F Y, g:i a') ?></time>, <?= read_time($a['body']) ?></span>
      </div>
      <div class="story-tools">
        <?php if ($a['status'] === 'approved'): ?>
          <?php if (user()): ?>
            <?= action_button('bookmark', (int)$a['id'], icon('bookmark') . ($saved ? 'Saved' : 'Save'), 'btn btn-sm ' . ($saved ? 'btn-saved' : 'btn-outline')) ?>
          <?php else: ?>
            <a class="btn btn-sm btn-outline" href="<?= url('login') ?>"><?= icon('bookmark') ?>Save</a>
          <?php endif; ?>
          <button class="btn btn-sm btn-quiet" type="button" data-copy-link><?= icon('link') ?><span>Copy link</span></button>
        <?php endif; ?>
        <?php if ($isAuthor || is_admin()): ?><a class="btn btn-sm btn-quiet" href="<?= url(is_admin() ? 'admin_article' : 'write', ['id' => $a['id']]) ?>"><?= icon('pen') ?>Edit</a><?php endif; ?>
      </div>
    </div>
  </header>

  <?php if (image_src($a['image'])): ?>
    <div class="wrap story-figure"><?= cover($a, 'ratio-lead', '1600') ?></div>
  <?php endif; ?>

  <div class="wrap narrow">
    <?php if ($a['kind'] === 'announcement' && $a['expires_at']): ?>
      <p class="notice-until notice-until-box"><?= icon('clock') ?>This notice is open until <?= date_label($a['expires_at'], 'l, j F Y') ?>.</p>
    <?php endif; ?>
    <div class="prose">
      <?php foreach (preg_split('/\n\s*\n/u', trim($a['body'])) as $paragraph): ?>
        <p><?= nl2br(e($paragraph)) ?></p>
      <?php endforeach; ?>
    </div>
    <p class="story-stats"><?= icon('eye') ?><?= plural((int)$a['views'], 'read') ?></p>

    <?php if ($a['review_note'] && ($isAuthor || is_admin())): ?>
      <div class="editor-note"><strong>Note from the editors</strong><p><?= e($a['review_note']) ?></p></div>
    <?php endif; ?>

    <section id="comments" class="comments" aria-labelledby="comments-h">
      <h2 id="comments-h">Comments <span class="count"><?= $approvedCount ?></span></h2>

      <?php if (user() && $a['status'] === 'approved'): ?>
        <form method="post" class="comment-form" data-validate>
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="comment_save">
          <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
          <span class="avatar"><?= e(initials(user()['name'])) ?></span>
          <div class="field">
            <label for="comment-body" class="visually-hidden">Your comment</label>
            <textarea id="comment-body" name="body" rows="3" required minlength="2" maxlength="1000" placeholder="Add to the conversation" data-count><?= is_post() && post('action') === 'comment_save' && !post('id') ? e(post('body')) : '' ?></textarea>
            <div class="comment-form-foot">
              <small><?= is_admin() ? 'Comments from editors appear immediately.' : 'A moderator checks comments before they appear.' ?></small>
              <button class="btn btn-primary btn-sm">Post comment</button>
            </div>
          </div>
        </form>
      <?php elseif (!user()): ?>
        <p class="signin-prompt"><a href="<?= url('login') ?>">Sign in</a> or <a href="<?= url('register') ?>">create an account</a> to comment.</p>
      <?php endif; ?>

      <?php if (!$comments): ?>
        <p class="muted">No comments yet.</p>
      <?php endif; ?>
      <ol class="comment-list">
        <?php foreach ($comments as $c): $mine = (int)$c['user_id'] === user_id(); ?>
          <li class="comment<?= $c['status'] !== 'approved' ? ' comment-' . e($c['status']) : '' ?>">
            <span class="avatar avatar-sm"><?= e(initials($c['name'])) ?></span>
            <div class="comment-main">
              <p class="comment-head">
                <strong><?= e($c['name']) ?></strong>
                <?php if ($c['role'] === 'admin'): ?><span class="badge badge-editor">Editor</span><?php endif; ?>
                <time datetime="<?= e($c['created_at']) ?>"><?= e(time_ago($c['created_at'])) ?></time>
                <?php if ($c['status'] !== 'approved'): ?><?= status_badge($c['status']) ?><?php endif; ?>
              </p>
              <p class="comment-body"><?= nl2br(e($c['body'])) ?></p>
              <?php if ($mine || is_admin()): ?>
                <div class="comment-actions">
                  <?php if ($mine && $a['status'] === 'approved'): ?>
                    <details class="comment-edit">
                      <summary>Edit</summary>
                      <form method="post" data-validate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="comment_save">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="article_id" value="<?= $a['id'] ?>">
                        <label class="visually-hidden" for="edit-<?= $c['id'] ?>">Edit comment</label>
                        <textarea id="edit-<?= $c['id'] ?>" name="body" rows="3" required minlength="2" maxlength="1000"><?= e($c['body']) ?></textarea>
                        <button class="btn btn-sm btn-primary"><?= is_admin() ? 'Save' : 'Save and send for review' ?></button>
                      </form>
                    </details>
                  <?php endif; ?>
                  <?= action_button('comment_delete', (int)$c['id'], 'Delete', 'link-btn link-danger', 'Delete this comment? This cannot be undone.', ['return' => $here . '#comments']) ?>
                </div>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>
  </div>
</article>

<?php if ($related): ?>
<section class="related">
  <div class="wrap">
    <div class="section-head"><h2>More in <?= e($a['category']) ?></h2><a href="<?= url('news', ['category' => $a['category_id']]) ?>">See all</a></div>
    <div class="story-grid story-grid-3">
      <?php foreach ($related as $r) view('partials/story_card', ['a' => $r]); ?>
    </div>
  </div>
</section>
<?php endif; ?>
