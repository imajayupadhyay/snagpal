<?php
$flash = is_array($flash ?? null) ? $flash : null;
$old = is_array($flash['old'] ?? null) ? $flash['old'] : [];
$messages = is_array($flash['messages'] ?? null) ? $flash['messages'] : [];
$type = in_array($flash['type'] ?? '', ['success', 'error'], true) ? (string) $flash['type'] : '';
$autoOpen = $type !== '' || isset($_GET['event_registration']);
?>
<div
  class="meeting-modal event-registration-modal"
  id="eventRegistrationModal"
  role="presentation"
  hidden
  data-auto-open="<?= $autoOpen ? 'true' : 'false' ?>"
>
  <div class="meeting-backdrop" data-event-registration-close></div>
  <div class="meeting-dialog event-registration-dialog" role="dialog" aria-modal="true" aria-labelledby="eventRegistrationModalTitle">
    <button class="meeting-close" type="button" data-event-registration-close aria-label="Close registration form">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>

    <aside class="meeting-aside event-registration-aside">
      <p class="mono meeting-eyebrow">Upcoming Events</p>
      <h2 id="eventRegistrationModalTitle">Register for upcoming event</h2>
      <p class="meeting-aside-lead">Share your contact details and the team will use them for upcoming event registration communication.</p>
      <ol class="meeting-steps">
        <li><span class="meeting-step-n">1</span><span>Enter your name</span></li>
        <li><span class="meeting-step-n">2</span><span>Share email and phone</span></li>
        <li><span class="meeting-step-n">3</span><span>Submit your registration</span></li>
      </ol>
    </aside>

    <div class="meeting-main">
      <div
        class="meeting-alert <?= e($type) ?>"
        role="<?= $type === 'success' ? 'status' : 'alert' ?>"
        tabindex="-1"
        data-event-registration-alert
        <?= $messages === [] ? ' hidden' : '' ?>
      >
        <?php foreach ($messages as $message): ?>
          <p><?= e($message) ?></p>
        <?php endforeach; ?>
      </div>

      <form class="meeting-form event-registration-form" id="eventRegistrationForm" method="post" action="<?= e(url_path('event-registration/')) ?>">
        <?= public_csrf_field() ?>
        <input class="hp-field" type="text" name="event_registration_reference" tabindex="-1" autocomplete="new-password" aria-hidden="true">
        <input class="hp-field" type="text" name="website" tabindex="-1" autocomplete="new-password" aria-hidden="true">

        <div class="meeting-grid event-registration-grid">
          <label class="full">
            <span>Name</span>
            <input name="name" value="<?= e($old['name'] ?? '') ?>" autocomplete="name" maxlength="160" required>
          </label>

          <label class="full">
            <span>Email</span>
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" maxlength="190" required>
          </label>

          <label class="full">
            <span>Phone Number</span>
            <input name="phone" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel" inputmode="tel" maxlength="40" required>
          </label>
        </div>

        <button class="meeting-submit" type="submit">Submit Registration</button>
        <p class="meeting-fineprint">Only your name, email, and phone number are saved for event registration follow-up.</p>
      </form>
    </div>
  </div>
</div>
