<?php
$currentPage = (string) ($currentPage ?? 'home');
$navigation = array_values($site['navigation'] ?? []);
$hiddenNavigationLabels = ['expertise', 'focus areas', 'research'];
$hiddenNavigationHrefs = ['#expertise', '#focus', '#research'];
$navigation = array_values(array_filter($navigation, static function (array $item) use ($hiddenNavigationLabels, $hiddenNavigationHrefs): bool {
    $label = strtolower(trim((string) ($item['label'] ?? '')));
    $href = strtolower(trim((string) ($item['href'] ?? '')));

    return ! in_array($label, $hiddenNavigationLabels, true)
        && ! in_array($href, $hiddenNavigationHrefs, true);
}));
$hasAboutLink = false;
$hasAwardsLink = false;
$hasCohortsLink = false;
$hasEventsLink = false;

foreach ($navigation as $item) {
    $label = strtolower((string) ($item['label'] ?? ''));
    $href = strtolower((string) ($item['href'] ?? ''));

    if ($label === 'about shweta' || str_contains($href, 'about-shweta')) {
        $hasAboutLink = true;
    }

    if ($label === 'awards and recognitions' || str_contains($href, 'awards-and-recognitions')) {
        $hasAwardsLink = true;
    }

    if ($label === 'cohorts' || str_contains($href, 'cohorts')) {
        $hasCohortsLink = true;
    }

    if ($label === 'events' || str_contains($href, 'events')) {
        $hasEventsLink = true;
    }
}

if (! $hasAboutLink) {
    array_unshift($navigation, [
        'label' => 'About Shweta',
        'href' => url_path('about-shweta/'),
        'class' => 'nav-about',
    ]);
}

if (! $hasAwardsLink) {
    $awardsLink = [
        'label' => 'Awards & Recognitions',
        'href' => url_path('awards-and-recognitions/'),
        'class' => 'nav-awards',
    ];
    $insertAt = 0;

    foreach ($navigation as $index => $item) {
        $href = strtolower((string) ($item['href'] ?? ''));

        if (str_contains($href, 'about-shweta')) {
            $insertAt = $index + 1;
            break;
        }
    }

    array_splice($navigation, $insertAt, 0, [$awardsLink]);
}

if (! $hasCohortsLink) {
    $cohortsLink = [
        'label' => 'Cohorts',
        'href' => url_path('cohorts/'),
        'class' => 'nav-cohorts',
    ];
    $insertAt = 0;

    foreach ($navigation as $index => $item) {
        $href = strtolower((string) ($item['href'] ?? ''));

        if (str_contains($href, 'awards-and-recognitions')) {
            $insertAt = $index + 1;
            break;
        }

        if (str_contains($href, 'about-shweta')) {
            $insertAt = $index + 1;
        }
    }

    array_splice($navigation, $insertAt, 0, [$cohortsLink]);
}

if (! $hasEventsLink) {
    $eventsLink = [
        'label' => 'Events',
        'href' => url_path('events/'),
        'class' => 'nav-events',
    ];
    $insertAt = 0;

    foreach ($navigation as $index => $item) {
        $href = strtolower((string) ($item['href'] ?? ''));

        if (str_contains($href, 'cohorts')) {
            $insertAt = $index + 1;
            break;
        }

        if (str_contains($href, 'awards-and-recognitions') || str_contains($href, 'about-shweta')) {
            $insertAt = $index + 1;
        }
    }

    array_splice($navigation, $insertAt, 0, [$eventsLink]);
}

$brandHref = $currentPage === 'home' ? '#top' : url_path();
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
        <?php
        $class = (string) ($item['class'] ?? '');
        $label = (string) ($item['label'] ?? '');
        $href = (string) ($item['href'] ?? '#');
        if (str_contains($href, 'about-shweta') && ! str_contains($class, 'nav-about')) {
            $class = trim($class . ' nav-about');
        }
        if (str_contains($href, 'awards-and-recognitions') && ! str_contains($class, 'nav-awards')) {
            $class = trim($class . ' nav-awards');
        }
        if (str_contains($href, 'cohorts') && ! str_contains($class, 'nav-cohorts')) {
            $class = trim($class . ' nav-cohorts');
        }
        if (str_contains($href, 'events') && ! str_contains($class, 'nav-events')) {
            $class = trim($class . ' nav-events');
        }
        $displayHref = $currentPage !== 'home' && str_starts_with($href, '#') ? url_path($href) : $href;
        $isCurrent = ($currentPage === 'about' && str_contains($href, 'about-shweta'))
            || ($currentPage === 'awards' && str_contains($href, 'awards-and-recognitions'))
            || ($currentPage === 'cohorts' && str_contains($href, 'cohorts'))
            || ($currentPage === 'events' && str_contains($href, 'events'));
        $isScheduleCta = str_contains($class, 'cta') && ($href === '#schedule' || stripos($label, 'schedule') !== false);
        ?>
        <?php if ($isScheduleCta): ?>
          <button class="<?= e($class) ?>" type="button" data-schedule-open><?= e($label) ?></button>
        <?php else: ?>
          <a href="<?= e($displayHref) ?>"<?= $class !== '' || $isCurrent ? ' class="' . e(trim($class . ' ' . ($isCurrent ? 'is-current' : ''))) . '"' : '' ?><?= $isCurrent ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
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
      <?php
      $class = (string) ($item['class'] ?? '');
      $label = (string) ($item['label'] ?? '');
      $href = (string) ($item['href'] ?? '#');
      if (str_contains($href, 'about-shweta') && ! str_contains($class, 'nav-about')) {
          $class = trim($class . ' nav-about');
      }
      if (str_contains($href, 'awards-and-recognitions') && ! str_contains($class, 'nav-awards')) {
          $class = trim($class . ' nav-awards');
      }
      if (str_contains($href, 'cohorts') && ! str_contains($class, 'nav-cohorts')) {
          $class = trim($class . ' nav-cohorts');
      }
      if (str_contains($href, 'events') && ! str_contains($class, 'nav-events')) {
          $class = trim($class . ' nav-events');
      }
      $displayHref = $currentPage !== 'home' && str_starts_with($href, '#') ? url_path($href) : $href;
      $isCurrent = ($currentPage === 'about' && str_contains($href, 'about-shweta'))
          || ($currentPage === 'awards' && str_contains($href, 'awards-and-recognitions'))
          || ($currentPage === 'cohorts' && str_contains($href, 'cohorts'))
          || ($currentPage === 'events' && str_contains($href, 'events'));
      $isScheduleCta = str_contains($class, 'cta') && ($href === '#schedule' || stripos($label, 'schedule') !== false);
      ?>
      <?php if ($isScheduleCta): ?>
            <button class="<?= e(trim('mobile-menu-link ' . $class)) ?>" type="button" data-schedule-open><?= e($label) ?></button>
      <?php else: ?>
            <a href="<?= e($displayHref) ?>" class="<?= e(trim('mobile-menu-link ' . $class . ' ' . ($isCurrent ? 'is-current' : ''))) ?>"<?= $isCurrent ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
      </div>
    </div>
  </div>
</nav>
