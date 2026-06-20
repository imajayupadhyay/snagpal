<?php ob_start(); ?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'dashboard']); ?>

  <main class="dashboard-main">
    <header class="welcome-head">
      <p class="eyebrow">Admin Dashboard</p>
      <h1>Welcome, <?= e($admin['name']) ?></h1>
      <p>Manage the website content, meeting availability, and booking requests from one place. Pick where you want to start.</p>
    </header>

    <div class="launch-grid">
      <a class="launch-card" href="<?= e(admin_homepage_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg></span>
        <h2>Homepage</h2>
        <p>Edit every section of the public site: hero, profile, expertise, cohorts, and more.</p>
        <span class="go">Manage content &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_schedule_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/></svg></span>
        <h2>Schedule</h2>
        <p>Create meeting slots by month, add one-off slots, and lock days when you are unavailable.</p>
        <span class="go">Manage availability &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_bookings_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h18v11H14l-2 3-2-3H3z"/><path d="M8 9h8M8 12.5h5"/></svg></span>
        <h2>Bookings</h2>
        <p>Review confirmed meeting requests, see visitor details, and cancel bookings when needed.</p>
        <span class="go">View requests &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5H5V5h5"/></svg></span>
        <h2>Live site</h2>
        <p>Open the public homepage in a new tab to preview exactly what visitors see.</p>
        <span class="go">Open website &rarr;</span>
      </a>
    </div>

    <div class="dash-foot">
      <form method="post" action="<?= e(url_path('admin/logout.php')) ?>">
        <?= csrf_field() ?>
        <button class="ghost-btn" type="submit">Logout</button>
      </form>
    </div>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-dashboard';
require APP_PATH . '/views/admin/layout.php';
