<?php $active = $active ?? ''; ?>
<header class="admin-topbar">
  <button class="topbar-burger" type="button" data-sidebar-toggle aria-label="Toggle navigation" aria-controls="adminSidebar">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
  </button>
  <a class="topbar-brand" href="<?= e(admin_dashboard_url()) ?>">SN <span>Admin</span></a>
</header>

<aside class="sidebar" id="adminSidebar">
  <a href="<?= e(admin_dashboard_url()) ?>" class="sidebar-brand">SN <span>Admin</span></a>
  <nav class="sidebar-nav" aria-label="Admin navigation">
    <a class="nav-item<?= $active === 'dashboard' ? ' active' : '' ?>" href="<?= e(admin_dashboard_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      <span>Dashboard</span>
    </a>
    <a class="nav-item<?= $active === 'homepage' ? ' active' : '' ?>" href="<?= e(admin_homepage_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
      <span>Homepage</span>
    </a>
    <a class="nav-item<?= $active === 'header' ? ' active' : '' ?>" href="<?= e(admin_header_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16"/><path d="M4 12h10"/><path d="M4 18h16"/><path d="M17 10l3 2-3 2z"/></svg>
      <span>Header</span>
    </a>
    <a class="nav-item<?= $active === 'cohorts' ? ' active' : '' ?>" href="<?= e(admin_cohorts_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v14H4z"/><path d="m10 9 5 3-5 3z"/><path d="M4 19l4-4h12"/></svg>
      <span>Cohorts</span>
    </a>
    <a class="nav-item<?= $active === 'cohort-categories' ? ' active' : '' ?>" href="<?= e(admin_cohort_categories_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h7v7H4z"/><path d="M13 6h7v4h-7z"/><path d="M13 13h7v5h-7z"/><path d="M4 16h7v3H4z"/></svg>
      <span>Cohort Categories</span>
    </a>
    <a class="nav-item<?= $active === 'about' ? ' active' : '' ?>" href="<?= e(admin_about_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"/><path d="M5 20c.7-3.6 3.4-5.6 7-5.6s6.3 2 7 5.6"/></svg>
      <span>About</span>
    </a>
    <a class="nav-item<?= $active === 'awards' ? ' active' : '' ?>" href="<?= e(admin_awards_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="5"/><path d="M9 13.5 7.5 21l4.5-2.5 4.5 2.5L15 13.5"/></svg>
      <span>Awards</span>
    </a>
    <a class="nav-item<?= $active === 'recommendations' ? ' active' : '' ?>" href="<?= e(admin_recommendations_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.4 8.4 0 0 1-1.2 4.3L21 20l-4.3-1.2a8.5 8.5 0 1 1 4.3-7.3z"/><path d="M8 10h6M8 13h4"/></svg>
      <span>Recommendations</span>
    </a>
    <a class="nav-item<?= $active === 'events' ? ' active' : '' ?>" href="<?= e(admin_events_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/><path d="M8 13.5h2M8 17h2M14 13.5h2M14 17h2"/></svg>
      <span>Events</span>
    </a>
    <a class="nav-item<?= $active === 'schedule' ? ' active' : '' ?>" href="<?= e(admin_schedule_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v3M16 3v3"/></svg>
      <span>Schedule</span>
    </a>
    <a class="nav-item<?= $active === 'bookings' ? ' active' : '' ?>" href="<?= e(admin_bookings_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 5h18v11H14l-2 3-2-3H3z"/><path d="M8 9h8M8 12.5h5"/></svg>
      <span>Bookings</span>
    </a>
    <a class="nav-item<?= $active === 'users' ? ' active' : '' ?>" href="<?= e(admin_users_url()) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M16 5.2a3 3 0 0 1 0 5.6"/><path d="M17.5 13.4A5.5 5.5 0 0 1 20.5 18"/></svg>
      <span>Users</span>
    </a>
  </nav>
  <div class="sidebar-foot">
    <a class="nav-item" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 14v5H5V5h5"/></svg>
      <span>View site</span>
    </a>
    <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
      <?= csrf_field() ?>
      <button class="nav-item" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4h3a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-3"/><path d="M10 8l-4 4 4 4"/><path d="M6 12h10"/></svg>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
