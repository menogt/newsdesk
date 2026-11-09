<?php /** Student registration. */ ?>
<div class="auth">
  <div class="auth-card">
    <h1>Create your account</h1>
    <p class="auth-lead">Follow the categories you care about, save stories, comment and submit your own news.</p>
    <form method="post" data-validate novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="register">
      <div class="field">
        <label for="name">Full name</label>
        <input id="name" name="name" required minlength="2" maxlength="80" autocomplete="name" value="<?= e(old('name')) ?>">
      </div>
      <div class="field">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e(old('email')) ?>" placeholder="name@students.nsbm.ac.lk">
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" pattern="(?=.*[A-Za-z])(?=.*\d).{8,72}" aria-describedby="pw-hint" data-strength>
          <button type="button" class="link-btn reveal" data-reveal="password">Show</button>
        </div>
        <small id="pw-hint" class="hint">At least 8 characters, with a letter and a number.</small>
        <div class="strength" aria-hidden="true"><span></span></div>
      </div>
      <div class="field">
        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password" type="password" required autocomplete="new-password" data-match="password">
      </div>
      <button class="btn btn-primary btn-block">Create account</button>
    </form>
    <p class="auth-switch">Already have an account? <a href="<?= url('login') ?>">Sign in</a></p>
  </div>
</div>
