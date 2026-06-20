<?php
$fmtDate = static function (?string $value): string {
    if (! $value) {
        return '—';
    }
    try {
        return (new DateTimeImmutable($value))->format('M j, Y');
    } catch (Throwable) {
        return '—';
    }
};
$fmtDateTime = static function (?string $value): string {
    if (! $value) {
        return 'Never';
    }
    try {
        return (new DateTimeImmutable($value))->format('M j, Y g:i A');
    } catch (Throwable) {
        return 'Never';
    }
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'users']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Access</p>
        <h1>Users</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">View Site</a>
        <form method="post" action="<?= e(url_path('admin/logout.php')) ?>">
          <?= csrf_field() ?>
          <button class="ghost-btn" type="submit">Logout</button>
        </form>
      </div>
    </header>

    <?php if ($success): ?>
      <div class="notice success" role="status"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="notice error" role="alert">
        <?php foreach ($errors as $error): ?>
          <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Team</p>
          <h2>Admins</h2>
        </div>
        <span class="muted"><?= e((string) count($admins)) ?> total</span>
      </div>

      <?php if ($admins === []): ?>
        <p class="empty-state">No admins found.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created</th>
                <th>Last login</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($admins as $row): ?>
                <?php $isSelf = (int) $row['id'] === (int) $admin['id']; ?>
                <tr>
                  <td>
                    <strong><?= e($row['name']) ?></strong>
                    <?php if ($isSelf): ?><span class="status-pill confirmed" style="margin-left:8px;">You</span><?php endif; ?>
                  </td>
                  <td><?= e($row['email']) ?></td>
                  <td><?= e($fmtDate($row['created_at'] ?? null)) ?></td>
                  <td><?= e($fmtDateTime($row['last_login_at'] ?? null)) ?></td>
                  <td>
                    <?php if ($isSelf): ?>
                      <span class="muted">Signed in</span>
                    <?php else: ?>
                      <form method="post" action="<?= e(admin_users_url()) ?>" onsubmit="return confirm('Delete the admin &quot;<?= e($row['email']) ?>&quot;? This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_admin">
                        <input type="hidden" name="admin_id" value="<?= e((string) $row['id']) ?>">
                        <button class="ghost-btn small danger" type="submit">Delete</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <section class="panel form-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">New</p>
          <h2>Add New Admin</h2>
        </div>
      </div>
      <form class="schedule-create-form" method="post" action="<?= e(admin_users_url()) ?>" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_admin">
        <div class="form-grid">
          <div class="field">
            <label for="new_name">Name</label>
            <input id="new_name" name="name" maxlength="120" required>
          </div>
          <div class="field">
            <label for="new_email">Email</label>
            <input id="new_email" type="email" name="email" maxlength="190" required>
          </div>
          <div class="field">
            <label for="new_password">Password</label>
            <input id="new_password" type="password" name="password" minlength="8" autocomplete="new-password" required>
          </div>
          <div class="field">
            <label for="new_password_confirm">Confirm password</label>
            <input id="new_password_confirm" type="password" name="password_confirm" minlength="8" autocomplete="new-password" required>
          </div>
        </div>
        <div class="panel-actions">
          <button type="submit">Create Admin</button>
        </div>
        <p class="hint">The new admin can sign in immediately with this email and password. Passwords must be at least 8 characters.</p>
      </form>
    </section>

    <section class="panel form-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Security</p>
          <h2>Change Password</h2>
        </div>
      </div>
      <form class="schedule-create-form" method="post" action="<?= e(admin_users_url()) ?>" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_password">
        <div class="form-grid">
          <div class="field">
            <label for="pw_admin_id">Admin</label>
            <select id="pw_admin_id" name="admin_id" required>
              <?php foreach ($admins as $row): ?>
                <option value="<?= e((string) $row['id']) ?>"<?= (int) $row['id'] === (int) $admin['id'] ? ' selected' : '' ?>>
                  <?= e($row['name'] . ' (' . $row['email'] . ')') ?><?= (int) $row['id'] === (int) $admin['id'] ? ' — you' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field"></div>
          <div class="field">
            <label for="pw_password">New password</label>
            <input id="pw_password" type="password" name="password" minlength="8" autocomplete="new-password" required>
          </div>
          <div class="field">
            <label for="pw_password_confirm">Confirm new password</label>
            <input id="pw_password_confirm" type="password" name="password_confirm" minlength="8" autocomplete="new-password" required>
          </div>
        </div>
        <div class="panel-actions">
          <button type="submit">Update Password</button>
        </div>
        <p class="hint">Choose any admin and set a new password for them. The change takes effect on their next sign-in.</p>
      </form>
    </section>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-dashboard';
require APP_PATH . '/views/admin/layout.php';
