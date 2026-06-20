<?php
$formatDateTime = static function (?string $value): string {
    if (! $value) {
        return 'Not set';
    }

    try {
        return (new DateTimeImmutable($value))->format('M j, Y g:i A');
    } catch (Throwable) {
        return 'Not set';
    }
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'recommendations']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Moderation</p>
        <h1>Recommendations</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">View Site</a>
        <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
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

    <section class="metric-grid">
      <article>
        <span>Total</span>
        <strong><?= e((string) ($overview['total'] ?? 0)) ?></strong>
        <p>Recommendations submitted by visitors.</p>
      </article>
      <article>
        <span>Pending</span>
        <strong><?= e((string) ($overview['pending'] ?? 0)) ?></strong>
        <p>Waiting for review.</p>
      </article>
      <article>
        <span>Approved</span>
        <strong><?= e((string) ($overview['approved'] ?? 0)) ?></strong>
        <p>Published to the homepage or About page.</p>
      </article>
      <article>
        <span>Rejected</span>
        <strong><?= e((string) ($overview['rejected'] ?? 0)) ?></strong>
        <p>Declined and hidden from public view.</p>
      </article>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Inbox</p>
          <h2>Submissions</h2>
        </div>
        <span class="muted"><?= e((string) count($submissions)) ?> entries</span>
      </div>

      <?php if ($submissions === []): ?>
        <p class="empty-state">No recommendations have been submitted yet.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>From</th>
                <th>Recommendation</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($submissions as $submission): ?>
                <tr>
                  <td>
                    <strong><?= e($submission['name'] ?? '') ?></strong>
                    <br><span class="muted"><?= e($submission['designation'] ?? '') ?></span>
                    <br><span class="muted"><?= e($submission['email'] ?? '') ?></span>
                  </td>
                  <td style="max-width:320px;"><?= e($submission['quote'] ?? '') ?></td>
                  <td>
                    <span class="status-pill <?= e((string) ($submission['status'] ?? 'pending')) ?>"><?= e(ucfirst((string) ($submission['status'] ?? 'pending'))) ?></span>
                    <?php if (! empty($submission['added_to_homepage'])): ?><br><span class="muted">On homepage</span><?php endif; ?>
                    <?php if (! empty($submission['added_to_about'])): ?><br><span class="muted">On About page</span><?php endif; ?>
                  </td>
                  <td><?= e($formatDateTime($submission['created_at'] ?? null)) ?></td>
                  <td>
                    <div class="table-actions" style="flex-direction:column;align-items:stretch;gap:6px;">
                      <?php if (($submission['status'] ?? '') === 'pending'): ?>
                        <form method="post" action="<?= e(admin_recommendations_url()) ?>">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="publish">
                          <input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>">
                          <select name="target" style="margin-bottom:4px;width:100%;">
                            <option value="homepage">Publish to Homepage</option>
                            <option value="about">Publish to About Page</option>
                            <option value="both">Publish to Both</option>
                          </select>
                          <button class="ghost-btn small" type="submit">Publish</button>
                        </form>
                        <form method="post" action="<?= e(admin_recommendations_url()) ?>" onsubmit="return confirm('Reject this submission?');">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>">
                          <button class="ghost-btn small danger" type="submit">Reject</button>
                        </form>
                      <?php endif; ?>
                      <form method="post" action="<?= e(admin_recommendations_url()) ?>" onsubmit="return confirm('Delete this submission? This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="submission_id" value="<?= e((string) $submission['id']) ?>">
                        <button class="ghost-btn small danger" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-recommendations-page';
require APP_PATH . '/views/admin/layout.php';
