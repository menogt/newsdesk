<?php /** Sign-in form for students and administrators. */ ?>
<div class="auth">
  <div class="auth-card">
    <h1>Sign in</h1>
    <p class="auth-lead">Students and editors use the same sign-in. Editors go straight to the admin dashboard.</p>
    <form method="post" data-validate novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="login">
      <div class="field">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e(old('email')) ?>" autofocus>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input id="password" name="password" type="password" required autocomplete="current-password">
          <button type="button" class="link-btn reveal" data-reveal="password">Show</button>
        </div>
      </div>
      <button class="btn btn-primary btn-block">Sign in</button>
    </form>
    <p class="auth-switch">New to NewsDesk? <a href="<?= url('register') ?>">Create an account</a></p>
  </div>
  <aside class="auth-demo">
    <h2>Demo accounts</h2>
    <p>For marking and the video walkthrough. Remove them before real use.</p>
    <dl>
      <dt>Administrator</dt><dd>admin@newsdesk.test<br>CampusAdmin26!</dd>
      <dt>Student</dt><dd>student@newsdesk.test<br>CampusStudent26!</dd>
    </dl>
  </aside>
</div>
