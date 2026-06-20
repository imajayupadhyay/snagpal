<nav id="nav">
  <a href="#top" class="brand"><?= e($site['identity']['first_name']) ?> <b><?= e($site['identity']['last_name']) ?></b></a>
  <div class="links">
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark mode" aria-pressed="false" title="Toggle theme">
      <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.5v2.4M12 19.1v2.4M4.6 4.6l1.7 1.7M17.7 17.7l1.7 1.7M2.5 12h2.4M19.1 12h2.4M4.6 19.4l1.7-1.7M17.7 6.3l1.7-1.7"/></svg>
      <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 14.2A8 8 0 1 1 9.8 3.5a6.3 6.3 0 0 0 10.7 10.7z"/></svg>
    </button>
    <?php foreach ($site['navigation'] as $item): ?>
      <?php
      $class = (string) ($item['class'] ?? '');
      $label = (string) ($item['label'] ?? '');
      $href = (string) ($item['href'] ?? '#');
      $isScheduleCta = str_contains($class, 'cta') && ($href === '#schedule' || stripos($label, 'schedule') !== false);
      ?>
      <?php if ($isScheduleCta): ?>
        <button class="<?= e($class) ?>" type="button" data-schedule-open><?= e($label) ?></button>
      <?php else: ?>
        <a href="<?= e($href) ?>"<?= $class !== '' ? ' class="' . e($class) . '"' : '' ?>><?= e($label) ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
</nav>
