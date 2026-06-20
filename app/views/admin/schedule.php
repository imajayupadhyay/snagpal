<?php
$currentMonth = (new DateTimeImmutable('first day of this month'))->format('Y-m');
$tomorrow = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$durationOptions = schedule_duration_options();
$bulkMonth = (string) ($old['bulk_month'] ?? $currentMonth);
$bulkWeekdays = schedule_weekdays_from_post($old['weekdays'] ?? [1, 2, 3, 4, 5]);
$bulkStartTime = (string) ($old['bulk_start_time'] ?? '10:00');
$bulkEndTime = (string) ($old['bulk_end_time'] ?? '12:00');
$slotDuration = (string) ($old['slot_duration'] ?? '30');
$customDuration = (string) ($old['custom_duration'] ?? '');
$bulkNotes = (string) ($old['bulk_notes'] ?? '');
$slotDate = (string) ($old['slot_date'] ?? $tomorrow);
$startTime = (string) ($old['start_time'] ?? '10:00');
$singleSlotDuration = (string) ($old['single_slot_duration'] ?? '30');
$singleCustomDuration = (string) ($old['single_custom_duration'] ?? '');
$slotStatus = (string) ($old['status'] ?? 'open');
$slotNotes = (string) ($old['notes'] ?? '');
$lockDate = (string) ($old['lock_date'] ?? $today);
$lockReason = (string) ($old['lock_reason'] ?? '');

if ($slotDuration !== 'custom' && ctype_digit($slotDuration) && ! isset($durationOptions[(int) $slotDuration])) {
    $customDuration = $slotDuration;
    $slotDuration = 'custom';
}

if ($singleSlotDuration !== 'custom' && ctype_digit($singleSlotDuration) && ! isset($durationOptions[(int) $singleSlotDuration])) {
    $singleCustomDuration = $singleSlotDuration;
    $singleSlotDuration = 'custom';
}
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'schedule']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Meetings</p>
        <h1>Schedule</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_bookings_url()) ?>">View Bookings</a>
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
        <span>Open slots</span>
        <strong><?= e((string) $overview['open_slots']) ?></strong>
        <p>Future slots available to visitors.</p>
      </article>
      <article>
        <span>Upcoming bookings</span>
        <strong><?= e((string) $overview['upcoming_bookings']) ?></strong>
        <p>Confirmed meetings that have not passed.</p>
      </article>
      <article>
        <span>Total confirmed</span>
        <strong><?= e((string) $overview['confirmed_bookings']) ?></strong>
        <p>All currently confirmed bookings.</p>
      </article>
      <article>
        <span>Locked days</span>
        <strong><?= e((string) $overview['locked_days']) ?></strong>
        <p>Future dates hidden from public booking.</p>
      </article>
    </section>

    <section class="panel form-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Availability</p>
          <h2>Monthly Setup</h2>
        </div>
      </div>
      <form class="schedule-create-form" method="post" action="<?= e(admin_schedule_url()) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_month_slots">
        <div class="form-grid">
          <div class="field">
            <label for="bulk_month">Month</label>
            <input id="bulk_month" type="month" name="bulk_month" value="<?= e($bulkMonth) ?>" required>
          </div>
          <div class="field">
            <label for="slot_duration">Slot duration</label>
            <select id="slot_duration" name="slot_duration">
              <?php foreach ($durationOptions as $minutes => $label): ?>
                <option value="<?= e((string) $minutes) ?>"<?= $slotDuration === (string) $minutes ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
              <option value="custom"<?= $slotDuration === 'custom' ? ' selected' : '' ?>>Custom minutes</option>
            </select>
          </div>
          <div class="field">
            <label for="custom_duration">Custom duration minutes</label>
            <input id="custom_duration" type="number" name="custom_duration" value="<?= e($customDuration) ?>" min="5" max="480" step="5" placeholder="Example: 75">
          </div>
          <div class="field full">
            <label>Days</label>
            <div class="weekday-grid">
              <?php foreach (schedule_weekday_options() as $value => $label): ?>
                <label class="check-tile">
                  <input type="checkbox" name="weekdays[]" value="<?= e((string) $value) ?>"<?= in_array($value, $bulkWeekdays, true) ? ' checked' : '' ?>>
                  <span><?= e($label) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="field">
            <label for="bulk_start_time">Start time</label>
            <input id="bulk_start_time" type="time" name="bulk_start_time" value="<?= e($bulkStartTime) ?>" required>
          </div>
          <div class="field">
            <label for="bulk_end_time">End time</label>
            <input id="bulk_end_time" type="time" name="bulk_end_time" value="<?= e($bulkEndTime) ?>" required>
          </div>
          <div class="field full">
            <label for="bulk_notes">Internal notes</label>
            <input id="bulk_notes" name="bulk_notes" value="<?= e($bulkNotes) ?>" maxlength="255">
            <p class="hint">Example: choose June, Monday/Wednesday/Friday, 10:00-12:00, 1 hour 30 minutes to create every matching slot for the month. Use custom minutes only when the preset list does not fit.</p>
          </div>
        </div>
        <div class="panel-actions">
          <button type="submit">Create Monthly Slots</button>
        </div>
      </form>
    </section>

    <section class="panel form-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Emergency</p>
          <h2>Day Locks</h2>
        </div>
      </div>
      <form class="schedule-create-form" method="post" action="<?= e(admin_schedule_url()) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="lock_day">
        <div class="form-grid">
          <div class="field">
            <label for="lock_date">Date to lock</label>
            <input id="lock_date" type="date" name="lock_date" value="<?= e($lockDate) ?>" min="<?= e($today) ?>" required>
          </div>
          <div class="field">
            <label for="lock_reason">Reason</label>
            <input id="lock_reason" name="lock_reason" value="<?= e($lockReason) ?>" maxlength="255" placeholder="Emergency, travel, leave">
          </div>
        </div>
        <div class="panel-actions">
          <button class="danger-btn" type="submit">Lock Day</button>
        </div>
        <p class="hint">A locked day hides all open slots on that date from the public booking modal. Existing confirmed bookings stay visible on the bookings page so you can cancel them manually if needed.</p>
      </form>

      <?php if ($dayLocks === []): ?>
        <div class="inline-empty">No active day locks.</div>
      <?php else: ?>
        <div class="table-wrap inline-table">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Bookings</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dayLocks as $lock): ?>
                <tr>
                  <td><?= e(schedule_format_date((string) $lock['lock_date'])) ?></td>
                  <td><?= e($lock['reason'] ?? '') ?></td>
                  <td><?= e((string) $lock['confirmed_booking_count']) ?> confirmed</td>
                  <td>
                    <form method="post" action="<?= e(admin_schedule_url()) ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="unlock_day">
                      <input type="hidden" name="lock_id" value="<?= e((string) $lock['id']) ?>">
                      <button class="ghost-btn small" type="submit">Unlock</button>
                    </form>
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
          <p class="eyebrow">Exception</p>
          <h2>One-off Slot</h2>
        </div>
      </div>
      <form class="schedule-create-form" method="post" action="<?= e(admin_schedule_url()) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_slot">
        <div class="form-grid">
          <div class="field">
            <label for="slot_date">Date</label>
            <input id="slot_date" type="date" name="slot_date" value="<?= e($slotDate) ?>" required>
          </div>
          <div class="field">
            <label for="start_time">Start time</label>
            <input id="start_time" type="time" name="start_time" value="<?= e($startTime) ?>" required>
          </div>
          <div class="field">
            <label for="single_slot_duration">Slot duration</label>
            <select id="single_slot_duration" name="single_slot_duration">
              <?php foreach ($durationOptions as $minutes => $label): ?>
                <option value="<?= e((string) $minutes) ?>"<?= $singleSlotDuration === (string) $minutes ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
              <option value="custom"<?= $singleSlotDuration === 'custom' ? ' selected' : '' ?>>Custom minutes</option>
            </select>
          </div>
          <div class="field">
            <label for="single_custom_duration">Custom duration minutes</label>
            <input id="single_custom_duration" type="number" name="single_custom_duration" value="<?= e($singleCustomDuration) ?>" min="5" max="480" step="5" placeholder="Example: 75">
          </div>
          <div class="field">
            <label for="slot_status">Status</label>
            <select id="slot_status" name="status">
              <option value="open"<?= $slotStatus === 'open' ? ' selected' : '' ?>>Open</option>
              <option value="closed"<?= $slotStatus === 'closed' ? ' selected' : '' ?>>Closed</option>
            </select>
          </div>
          <div class="field full">
            <label for="slot_notes">Internal notes</label>
            <input id="slot_notes" name="notes" value="<?= e($slotNotes) ?>" maxlength="255">
          </div>
        </div>
        <div class="panel-actions">
          <button type="submit">Create Slot</button>
        </div>
        <p class="hint">Use this only for a single special slot. The end time is calculated from the start time and selected duration.</p>
      </form>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Availability</p>
          <h2>Slots</h2>
        </div>
        <?php if ($slots !== []): ?>
          <span class="muted"><?= e((string) count($slots)) ?> slot<?= count($slots) === 1 ? '' : 's' ?> &middot; grouped by day</span>
        <?php endif; ?>
      </div>

      <?php if ($slots === []): ?>
        <p class="empty-state">No slots have been created yet.</p>
      <?php else: ?>
        <?php
        $slotsByDate = [];
        foreach ($slots as $slot) {
            $slotsByDate[(string) $slot['slot_date']][] = $slot;
        }
        $todayStr = (new DateTimeImmutable('today'))->format('Y-m-d');
        $openDate = null;
        foreach (array_keys($slotsByDate) as $dateKey) {
            if ($dateKey >= $todayStr) { $openDate = $dateKey; break; }
        }
        if ($openDate === null) { $openDate = array_key_last($slotsByDate); }
        ?>
        <div class="slot-groups">
          <?php foreach ($slotsByDate as $date => $daySlots): ?>
            <?php
            $dayTotal = count($daySlots);
            $dayConfirmed = 0; $dayPending = 0; $dayOpen = 0; $dayClosed = 0;
            $dayLocked = ! empty($daySlots[0]['day_lock_id']);
            foreach ($daySlots as $ds) {
                $bs = (string) ($ds['booking_status'] ?? '');
                if ($bs === 'confirmed') { $dayConfirmed++; }
                elseif ($bs === 'pending') { $dayPending++; }
                elseif ($ds['status'] === 'open') { $dayOpen++; }
                else { $dayClosed++; }
            }
            ?>
            <details class="slot-group"<?= $date === $openDate ? ' open' : '' ?>>
              <summary class="slot-group-head">
                <span class="slot-date"><strong><?= e(schedule_format_date($date)) ?></strong></span>
                <span class="slot-tags">
                  <span class="status-pill"><?= e((string) $dayTotal) ?> slot<?= $dayTotal === 1 ? '' : 's' ?></span>
                  <?php if ($dayPending): ?><span class="status-pill pending"><?= e((string) $dayPending) ?> pending</span><?php endif; ?>
                  <?php if ($dayConfirmed): ?><span class="status-pill booked"><?= e((string) $dayConfirmed) ?> confirmed</span><?php endif; ?>
                  <?php if ($dayOpen): ?><span class="status-pill open"><?= e((string) $dayOpen) ?> open</span><?php endif; ?>
                  <?php if ($dayClosed): ?><span class="status-pill closed"><?= e((string) $dayClosed) ?> closed</span><?php endif; ?>
                  <?php if ($dayLocked): ?><span class="status-pill locked">Locked</span><?php endif; ?>
                </span>
                <span class="slot-chev" aria-hidden="true">&#9662;</span>
              </summary>
              <div class="table-wrap">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Time</th>
                      <th>Status</th>
                      <th>Booking</th>
                      <th>Notes</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($daySlots as $slot): ?>
                      <?php
                      $isBooked = ! empty($slot['booking_id']);
                      $isLocked = ! empty($slot['day_lock_id']);
                      $bookingStatus = (string) ($slot['booking_status'] ?? '');
                      $statusText = $isBooked ? ucfirst($bookingStatus) : ($isLocked ? 'Locked' : ucfirst((string) $slot['status']));
                      $statusClass = $isBooked ? ($bookingStatus === 'pending' ? 'pending' : 'booked') : ($isLocked ? 'locked' : (string) $slot['status']);
                      ?>
                      <tr>
                        <td><?= e(schedule_format_slot_label((string) $slot['start_time'], (string) $slot['end_time'])) ?></td>
                        <td><span class="status-pill <?= e($statusClass) ?>"><?= e($statusText) ?></span></td>
                        <td>
                          <?php if ($isBooked): ?>
                            <strong><?= e($slot['visitor_name']) ?></strong><br>
                            <a href="mailto:<?= e($slot['visitor_email']) ?>"><?= e($slot['visitor_email']) ?></a>
                          <?php else: ?>
                            <span class="muted">Not booked</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <?= e($slot['notes'] ?? '') ?>
                          <?php if ($isLocked): ?>
                            <br><span class="muted">Day lock: <?= e($slot['day_lock_reason'] ?: 'No reason given') ?></span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <div class="table-actions">
                            <?php if (! $isBooked): ?>
                              <form method="post" action="<?= e(admin_schedule_url()) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="slot_status">
                                <input type="hidden" name="slot_id" value="<?= e((string) $slot['id']) ?>">
                                <input type="hidden" name="status" value="<?= $slot['status'] === 'open' ? 'closed' : 'open' ?>">
                                <button class="ghost-btn small" type="submit"><?= $slot['status'] === 'open' ? 'Close' : 'Open' ?></button>
                              </form>
                              <form method="post" action="<?= e(admin_schedule_url()) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_slot">
                                <input type="hidden" name="slot_id" value="<?= e((string) $slot['id']) ?>">
                                <button class="ghost-btn small danger" type="submit">Delete</button>
                              </form>
                              <?php if ($isLocked): ?>
                                <span class="muted">Unlock day above</span>
                              <?php endif; ?>
                            <?php else: ?>
                              <span class="muted">Booked</span>
                            <?php endif; ?>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-dashboard';
require APP_PATH . '/views/admin/layout.php';
