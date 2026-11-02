<?php
/** Registration, sign in / out and account settings. */

function action_register(int $id): void
{
    $name = post('name');
    $email = strtolower(post('email'));
    $password = (string)($_POST['password'] ?? '');

    require_length($name, 2, 80, 'Full name');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new ValidationError('Enter a valid email address, like name@students.nsbm.ac.lk.');
    validate_password($password);
    if ($password !== ($_POST['confirm_password'] ?? '')) throw new ValidationError('The two passwords do not match.');
    if (one('SELECT id FROM users WHERE email = ?', [$email])) throw new ValidationError('An account with this email already exists. Sign in instead.');

    // Role is never taken from the form: registration always creates a student.
    query('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

    // Sign the new student in straight away.
    session_regenerate_id(true);
    $_SESSION['user_id'] = last_id();
    unset($_SESSION['csrf']);
    flash('Welcome to NewsDesk, ' . $name . '. Follow a few categories to build your feed.');
    redirect('following');
}

function validate_password(string $password): void
{
    if (strlen($password) < 8) throw new ValidationError('Use a password with at least 8 characters.');
    if (strlen($password) > 72) throw new ValidationError('Passwords can be at most 72 characters.');
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        throw new ValidationError('Use at least one letter and one number in your password.');
    }
}

function action_login(int $id): void
{
    $email = strtolower(post('email'));
    $password = (string)($_POST['password'] ?? '');
    if ($email === '' || $password === '') throw new ValidationError('Enter your email and password.');

    // Throttle: 10 failures per email or 50 per IP address in 15 minutes.
    $keys = [hash('sha256', 'email:' . $email), hash('sha256', 'ip:' . ($_SERVER['REMOTE_ADDR'] ?? ''))];
    foreach ($keys as $i => $key) {
        $recent = (int)scalar('SELECT COUNT(*) FROM login_attempts WHERE identifier = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$key]);
        if ($recent >= ($i === 0 ? 10 : 50)) throw new ValidationError('Too many failed attempts. Wait 15 minutes, then try again.');
    }

    $account = one('SELECT * FROM users WHERE email = ?', [$email]);
    if (!$account || !password_verify($password, $account['password_hash'])) {
        foreach ($keys as $key) query('INSERT INTO login_attempts (identifier) VALUES (?)', [$key]);
        throw new ValidationError('The email or password is incorrect.');
    }
    if ($account['status'] === 'blocked') throw new ValidationError('This account is suspended. Contact the NewsDesk team for help.');

    query('DELETE FROM login_attempts WHERE identifier = ? OR attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)', [$keys[0]]);
    query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$account['id']]);
    if (password_needs_rehash($account['password_hash'], PASSWORD_DEFAULT)) {
        query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $account['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$account['id'];
    unset($_SESSION['csrf']);
    flash('Welcome back, ' . strtok($account['name'], ' ') . '.');

    $after = $_SESSION['after_login'] ?? '';
    unset($_SESSION['after_login']);
    if ($account['role'] === 'admin') redirect('admin');
    if (is_string($after) && preg_match('#/index\.php\?page=[a-z_]+[^"<>]*$#', $after) && !str_contains($after, 'page=admin')) {
        header('Location: index.php' . substr($after, strpos($after, '?')));
        exit;
    }
    redirect('home');
}

function action_logout(int $id): void
{
    $_SESSION = [];
    session_regenerate_id(true);
    flash('You have signed out.', 'info');
    redirect('home');
}

function action_profile_save(int $id): void
{
    $name = post('name');
    $email = strtolower(post('email'));
    require_length($name, 2, 80, 'Full name');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new ValidationError('Enter a valid email address.');
    if (one('SELECT id FROM users WHERE email = ? AND id <> ?', [$email, user_id()])) throw new ValidationError('Another account already uses this email.');

    query('UPDATE users SET name = ?, email = ? WHERE id = ?', [$name, $email, user_id()]);
    flash('Profile updated.');
    redirect('profile');
}

function action_password_change(int $id): void
{
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $hash = scalar('SELECT password_hash FROM users WHERE id = ?', [user_id()]);
    if (!password_verify($current, (string)$hash)) throw new ValidationError('Your current password is incorrect.');
    validate_password($new);
    if ($new !== ($_POST['confirm_password'] ?? '')) throw new ValidationError('The new passwords do not match.');

    query('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), user_id()]);
    session_regenerate_id(true);
    flash('Password changed.');
    redirect('profile');
}
    