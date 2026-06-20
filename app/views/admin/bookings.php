<?php ob_start(); ?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'bookings']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Meetings</p>
        <h1>Bookings</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_schedule_url()) ?>">Manage Schedule</a>
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
        <span>Pending</span>
        <strong><?= e((string) ($overview['pending'] ?? 0)) ?></strong>
        <p>Requests awaiting your confirmation.</p>
      </article>
      <article>
        <span>Upcoming</span>
        <strong><?= e((string) $overview['upcoming']) ?></strong>
        <p>Confirmed meetings still ahead.</p>
      </article>
      <article>
        <span>Confirmed</span>
        <strong><?= e((string) $overview['confirmed']) ?></strong>
        <p>All active confirmed bookings.</p>
      </article>
      <article>
        <span>Cancelled</span>
        <strong><?= e((string) $overview['cancelled']) ?></strong>
        <p>Bookings cancelled by admin.</p>
      </article>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Requests</p>
          <h2>All Bookings</h2>
        </div>
      </div>

      <?php if ($bookings === []): ?>
        <p class="empty-state">No bookings yet.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table booking-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Visitor</th>
                <th>Contact</th>
                <th>Message</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $booking): ?>
                <tr>
                  <td>
                    <?= e(schedule_format_date((string) $booking['slot_date'])) ?><br>
                    <span class="muted"><?= e(schedule_format_slot_label((string) $booking['start_time'], (string) $booking['end_time'])) ?></span>
                  </td>
                  <td><?= e($booking['visitor_name']) ?></td>
                  <td>
                    <a href="mailto:<?= e($booking['visitor_email']) ?>"><?= e($booking['visitor_email']) ?></a>
                    <?php if (! empty($booking['visitor_phone'])): ?>
                      <br><span class="muted"><?= e($booking['visitor_phone']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td><?= $booking['message'] !== null && $booking['message'] !== '' ? nl2br(e($booking['message'])) : '<span class="muted">No message</span>' ?></td>
                  <td><span class="status-pill <?= e((string) $booking['status']) ?>"><?= e(ucfirst((string) $booking['status'])) ?></span></td>
                  <td>
                    <?php if ($booking['status'] === 'cancelled'): ?>
                      <span class="muted">Cancelled</span>
                    <?php else: ?>
                      <div class="table-actions">
                        <?php if ($booking['status'] === 'pending'): ?>
                          <form method="post" action="<?= e(admin_bookings_url()) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="confirm_booking">
                            <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                            <button class="ghost-btn small" type="submit">Confirm</button>
                          </form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(admin_bookings_url()) ?>">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="cancel_booking">
                          <input type="hidden" name="booking_id" value="<?= e((string) $booking['id']) ?>">
                          <button class="ghost-btn small danger" type="submit">Cancel</button>
                        </form>
                      </div>
                    <?php endif; ?>
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
$bodyClass = 'admin-dashboard';
require APP_PATH . '/views/admin/layout.php';
