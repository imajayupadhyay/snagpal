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
      <a class="launch-card" href="<?= e(admin_header_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h10"/><path d="M4 18h16"/><path d="M17 10l3 2-3 2z"/></svg></span>
        <h2>Header</h2>
        <p>Manage menu links for desktop and mobile navigation, including the header button text.</p>
        <span class="go">Manage header &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_cohorts_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v14H4z"/><path d="m10 9 5 3-5 3z"/><path d="M4 19l4-4h12"/></svg></span>
        <h2>Cohorts</h2>
        <p>Create and manage cohort posts with video, excerpts, article content, links, and publish status.</p>
        <span class="go">Manage cohorts &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_about_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"/><path d="M5 20c.7-3.6 3.4-5.6 7-5.6s6.3 2 7 5.6"/></svg></span>
        <h2>About Page</h2>
        <p>Edit the profile facts, "Work &amp; Mandate" cards, and the operating principles heading.</p>
        <span class="go">Manage about page &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_awards_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="5"/><path d="M9 13.5 7.5 21l4.5-2.5 4.5 2.5L15 13.5"/></svg></span>
        <h2>Awards Page</h2>
        <p>Edit the recognition ledger, stat row, and editorial standards cards.</p>
        <span class="go">Manage awards page &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(admin_events_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/><path d="M8 13.5h2M8 17h2M14 13.5h2M14 17h2"/></svg></span>
        <h2>Events</h2>
        <p>Publish upcoming and past events with dates, location, video, and registration links.</p>
        <span class="go">Manage events &rarr;</span>
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
      <a class="launch-card" href="<?= e(admin_users_url()) ?>">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M17.5 13.4A5.5 5.5 0 0 1 20.5 18"/></svg></span>
        <h2>Users</h2>
        <p>Manage admin accounts — add a new admin or change passwords.</p>
        <span class="go">Manage admins &rarr;</span>
      </a>
      <a class="launch-card" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">
        <span class="launch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5H5V5h5"/></svg></span>
        <h2>Live site</h2>
        <p>Open the public homepage in a new tab to preview exactly what visitors see.</p>
        <span class="go">Open website &rarr;</span>
      </a>
    </div>

    <div class="dash-foot">
      <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
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
