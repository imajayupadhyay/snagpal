<?php
$currentPage = (string) ($currentPage ?? 'home');
$navigation = site_navigation(
    $site['navigation'] ?? [],
    empty($site['header_navigation_managed']),
    true
);
$brandHref = $currentPage === 'home' ? '#top' : url_path();
$navItemData = static function (array $item) use ($currentPage): array {
    $class = site_navigation_item_classes($item);
    $label = (string) ($item['label'] ?? '');
    $href = (string) ($item['href'] ?? '#');
    $isCurrent = site_navigation_is_current($item, $currentPage);

    return [
        'label' => $label,
        'href' => $href,
        'displayHref' => site_navigation_display_href($href, $currentPage),
        'class' => trim($class . ' ' . ($isCurrent ? 'is-current' : '')),
        'isCurrent' => $isCurrent,
        'isScheduleCta' => site_navigation_is_schedule_cta([
            'label' => $label,
            'href' => $href,
            'class' => $class,
        ]),
    ];
};
?>
<nav id="nav">
  <a href="<?= e($brandHref) ?>" class="brand"><?= e($site['identity']['first_name']) ?> <b><?= e($site['identity']['last_name']) ?></b></a>
  <div class="nav-actions">
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark mode" aria-pressed="false" title="Toggle theme">
      <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.4M12 19.1v2.4M4.6 4.6l1.7 1.7M17.7 17.7l1.7 1.7M2.5 12h2.4M19.1 12h2.4M4.6 19.4l1.7-1.7M17.7 6.3l1.7-1.7"/></svg>
      <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 14.2A8 8 0 1 1 9.8 3.5a6.3 6.3 0 0 0 10.7 10.7z"/></svg>
    </button>
    <div class="links desktop-links">
      <?php foreach ($navigation as $item): ?>
        <?php $navItem = $navItemData($item); ?>
        <?php if ($navItem['isScheduleCta']): ?>
          <button class="<?= e($navItem['class']) ?>" type="button" data-schedule-open><?= e($navItem['label']) ?></button>
        <?php else: ?>
          <a href="<?= e($navItem['displayHref']) ?>"<?= $navItem['class'] !== '' ? ' class="' . e($navItem['class']) . '"' : '' ?><?= $navItem['isCurrent'] ? ' aria-current="page"' : '' ?><?= external_link_attrs($item) ?>><?= e($navItem['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <button class="menu-toggle" id="menuToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>

  <div class="mobile-menu" id="mobileMenu" hidden>
    <div class="mobile-menu-panel">
      <div class="mobile-menu-head">
        <span class="mono">Navigation</span>
        <span><?= e($site['identity']['full_name']) ?></span>
      </div>
      <div class="mobile-menu-links">
        <?php foreach ($navigation as $item): ?>
          <?php $navItem = $navItemData($item); ?>
          <?php if ($navItem['isScheduleCta']): ?>
            <button class="<?= e(trim('mobile-menu-link ' . $navItem['class'])) ?>" type="button" data-schedule-open><?= e($navItem['label']) ?></button>
          <?php else: ?>
            <a href="<?= e($navItem['displayHref']) ?>" class="<?= e(trim('mobile-menu-link ' . $navItem['class'])) ?>"<?= $navItem['isCurrent'] ? ' aria-current="page"' : '' ?><?= external_link_attrs($item) ?>><?= e($navItem['label']) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</nav>
