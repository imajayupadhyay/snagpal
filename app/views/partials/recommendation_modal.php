<?php
$flash = is_array($flash ?? null) ? $flash : null;
$old = is_array($flash['old'] ?? null) ? $flash['old'] : [];
$messages = is_array($flash['messages'] ?? null) ? $flash['messages'] : [];
$type = in_array($flash['type'] ?? '', ['success', 'error'], true) ? (string) $flash['type'] : '';
$autoOpen = $type !== '' || isset($_GET['recommendation']);
?>
<div
  class="meeting-modal"
  id="recommendationModal"
  role="presentation"
  hidden
  data-auto-open="<?= $autoOpen ? 'true' : 'false' ?>"
>
  <div class="meeting-backdrop" data-recommend-close></div>
  <div class="meeting-dialog" role="dialog" aria-modal="true" aria-labelledby="recommendationModalTitle">
    <button class="meeting-close" type="button" data-recommend-close aria-label="Close recommendation form">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
    </button>

    <aside class="meeting-aside">
      <p class="mono meeting-eyebrow">Recommendations</p>
      <h2 id="recommendationModalTitle">Give a recommendation</h2>
      <p class="meeting-aside-lead">Share a short testimonial about working with Shweta. It will be reviewed before it appears on the site.</p>
      <ol class="meeting-steps">
        <li><span class="meeting-step-n">1</span><span>Share your name &amp; role</span></li>
        <li><span class="meeting-step-n">2</span><span>Write your recommendation</span></li>
        <li><span class="meeting-step-n">3</span><span>It's reviewed before publishing</span></li>
      </ol>
    </aside>

    <div class="meeting-main">
      <div
        class="meeting-alert <?= e($type) ?>"
        role="<?= $type === 'success' ? 'status' : 'alert' ?>"
        tabindex="-1"
        data-recommend-alert
        <?= $messages === [] ? ' hidden' : '' ?>
      >
        <?php foreach ($messages as $message): ?>
          <p><?= e($message) ?></p>
        <?php endforeach; ?>
      </div>

      <form class="meeting-form" id="recommendationForm" method="post" action="<?= e(url_path('give-recommendation/')) ?>">
        <?= public_csrf_field() ?>
        <input class="hp-field" type="text" name="recommendation_reference" tabindex="-1" autocomplete="new-password" aria-hidden="true">

        <div class="meeting-grid">
          <label class="full">
            <span>Name</span>
            <input name="name" value="<?= e($old['name'] ?? '') ?>" autocomplete="name" required maxlength="160">
          </label>

          <label class="full">
            <span>Designation / Organisation</span>
            <input name="designation" value="<?= e($old['designation'] ?? '') ?>" required maxlength="200" placeholder="e.g. Chief Engineer, Power Utility">
          </label>

          <label class="full">
            <span>Email <em>(not published, for verification only)</em></span>
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required maxlength="190">
          </label>

          <label class="full">
            <span>Your recommendation</span>
            <textarea name="quote" rows="4" maxlength="600" required placeholder="Share a short recommendation..."><?= e($old['quote'] ?? '') ?></textarea>
          </label>
        </div>

        <button class="meeting-submit" type="submit">Submit Recommendation</button>
        <p class="meeting-fineprint">Submissions are reviewed before being published — no charge, no spam.</p>
      </form>
    </div>
  </div>
</div>
