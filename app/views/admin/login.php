<?php ob_start(); ?>
<main class="login-shell">
  <section class="login-card" aria-labelledby="login-title">
    <div class="login-brand">Shweta <span>Nagpal</span></div>
    <p class="login-kicker">Admin Panel</p>
    <h1 id="login-title">Secure Login</h1>

    <?php if ($errors): ?>
      <div class="alert" role="alert">
        <?php foreach ($errors as $error): ?>
          <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= e(admin_login_url()) ?>" novalidate>
      <?= csrf_field() ?>
      <label for="email">Email</label>
      <input id="email" type="email" name="email" value="<?= e($email ?? '') ?>" autocomplete="username" required autofocus>

      <label for="password">Password</label>
      <input id="password" type="password" name="password" autocomplete="current-password" required>

      <button type="submit">Login</button>
    </form>

    <a class="back-link" href="<?= e(url_path()) ?>">Back to website</a>
  </section>
</main>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-login';
require APP_PATH . '/views/admin/layout.php';
