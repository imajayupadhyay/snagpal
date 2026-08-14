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

$phoneHref = static function (mixed $value): string {
    $phone = preg_replace('/[^\d+]/', '', (string) $value) ?? '';

    return $phone !== '' ? 'tel:' . $phone : '';
};

$eventLabel = static function (array $registration): string {
    $title = trim((string) ($registration['event_title'] ?? ''));
    $date = trim((string) ($registration['event_date_label'] ?? ''));

    if ($title === '') {
        return 'Not recorded';
    }

    return $date !== '' ? $title . ' - ' . $date : $title;
};

ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'event-registrations']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Events</p>
        <h1>Event Registrations</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_events_url()) ?>">Manage Events</a>
        <a class="ghost-link" href="<?= e(url_path('events/')) ?>" target="_blank" rel="noopener">View Events Page</a>
        <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
          <?= csrf_field() ?>
          <button class="ghost-btn" type="submit">Logout</button>
        </form>
      </div>
    </header>

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
        <p>Registrations submitted from the public Events page.</p>
      </article>
      <article>
        <span>Today</span>
        <strong><?= e((string) ($overview['today'] ?? 0)) ?></strong>
        <p>Registrations received since midnight.</p>
      </article>
      <article>
        <span>This Month</span>
        <strong><?= e((string) ($overview['month'] ?? 0)) ?></strong>
        <p>Registrations received in the current month.</p>
      </article>
      <article>
        <span>Latest</span>
        <strong><?= e($formatDateTime($overview['latest'] ?? null)) ?></strong>
        <p>Most recent public event registration.</p>
      </article>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Inbox</p>
          <h2>Submitted Registrations</h2>
        </div>
        <span class="muted"><?= e((string) count($registrations)) ?> entries</span>
      </div>

      <?php if ($registrations === []): ?>
        <p class="empty-state">No event registrations have been submitted yet.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Event</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Submitted</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($registrations as $registration): ?>
                <?php $tel = $phoneHref($registration['phone'] ?? ''); ?>
                <tr>
                  <td><strong><?= e($eventLabel($registration)) ?></strong></td>
                  <td><strong><?= e($registration['name'] ?? '') ?></strong></td>
                  <td><a href="mailto:<?= e($registration['email'] ?? '') ?>"><?= e($registration['email'] ?? '') ?></a></td>
                  <td>
                    <?php if ($tel !== ''): ?>
                      <a href="<?= e($tel) ?>"><?= e($registration['phone'] ?? '') ?></a>
                    <?php else: ?>
                      <?= e($registration['phone'] ?? '') ?>
                    <?php endif; ?>
                  </td>
                  <td><span class="muted"><?= e($registration['source_path'] ?? '/events/') ?></span></td>
                  <td><?= e($formatDateTime($registration['created_at'] ?? null)) ?></td>
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
$bodyClass = 'admin-page admin-event-registrations-page';
require APP_PATH . '/views/admin/layout.php';
