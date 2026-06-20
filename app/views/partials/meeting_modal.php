<?php
$flash = is_array($flash ?? null) ? $flash : null;
$old = is_array($flash['old'] ?? null) ? $flash['old'] : [];
$messages = is_array($flash['messages'] ?? null) ? $flash['messages'] : [];
$type = in_array($flash['type'] ?? '', ['success', 'error'], true) ? (string) $flash['type'] : '';
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$selectedDate = (string) ($old['meeting_date'] ?? '');
$selectedSlot = (string) ($old['slot_id'] ?? '');
$autoOpen = $type !== '' || isset($_GET['booking']);
$slots = is_array($slots ?? null) ? $slots : [];
?>
<div
  class="meeting-modal"
  id="meetingModal"
  role="presentation"
  hidden
  data-auto-open="<?= $autoOpen ? 'true' : 'false' ?>"
>
  <div class="meeting-backdrop" data-schedule-close></div>
  <div class="meeting-dialog" role="dialog" aria-modal="true" aria-labelledby="meetingModalTitle">
    <button class="meeting-close" type="button" data-schedule-close aria-label="Close booking form">&times;</button>

    <div class="meeting-copy">
      <p class="mono">Schedule a Meet</p>
      <h2 id="meetingModalTitle">Request a meeting slot</h2>
    </div>

    <div
      class="meeting-alert <?= e($type) ?>"
      role="<?= $type === 'success' ? 'status' : 'alert' ?>"
      tabindex="-1"
      data-meeting-alert
      <?= $messages === [] ? ' hidden' : '' ?>
    >
      <?php foreach ($messages as $message): ?>
        <p><?= e($message) ?></p>
      <?php endforeach; ?>
    </div>

    <?php if ($slots === []): ?>
      <div class="meeting-empty">
        <p>No meeting slots are open right now. Please check back soon or use the email link below.</p>
      </div>
    <?php else: ?>
      <form class="meeting-form" method="post" action="<?= e(url_path('book-meeting/')) ?>">
        <?= public_csrf_field() ?>
        <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

        <div class="meeting-grid">
          <label>
            <span>Date</span>
            <input
              type="date"
              name="meeting_date"
              min="<?= e($today) ?>"
              value="<?= e($selectedDate) ?>"
              required
              data-meeting-date
            >
          </label>

          <label>
            <span>Slot</span>
            <select name="slot_id" required data-meeting-slot>
              <option value="">Select a date first</option>
              <?php foreach ($slots as $slot): ?>
                <option
                  value="<?= e((string) $slot['id']) ?>"
                  data-date="<?= e($slot['date']) ?>"
                  <?= $selectedSlot === (string) $slot['id'] ? ' selected' : '' ?>
                >
                  <?= e($slot['date_label'] . ' | ' . $slot['time_label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            <span>Name</span>
            <input name="visitor_name" value="<?= e($old['visitor_name'] ?? '') ?>" autocomplete="name" required>
          </label>

          <label>
            <span>Email</span>
            <input type="email" name="visitor_email" value="<?= e($old['visitor_email'] ?? '') ?>" autocomplete="email" required>
          </label>

          <label>
            <span>Phone</span>
            <input name="visitor_phone" value="<?= e($old['visitor_phone'] ?? '') ?>" autocomplete="tel">
          </label>

          <label class="full">
            <span>Message</span>
            <textarea name="message" rows="4" maxlength="1000"><?= e($old['message'] ?? '') ?></textarea>
          </label>
        </div>

        <button class="meeting-submit" type="submit">Send Request</button>
      </form>
    <?php endif; ?>
  </div>
</div>
