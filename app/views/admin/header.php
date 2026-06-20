<?php
$menuItems = array_values($menuItems ?? []);
$cta = $cta ?? site_default_header_cta();
$navigation = $navigation ?? array_merge($menuItems, [$cta]);
$renderMenuRow = static function (array $item, string $key, int $number): void {
    ?>
    <div class="repeat-row header-menu-row" data-repeat-row>
      <div class="header-row-number" data-index-label><?= e((string) $number) ?></div>
      <div class="field">
        <label>Menu label</label>
        <input name="menu_items[<?= e($key) ?>][label]" value="<?= e($item['label'] ?? '') ?>" maxlength="80" placeholder="About Shweta">
      </div>
      <div class="field">
        <label>Link</label>
        <input name="menu_items[<?= e($key) ?>][href]" value="<?= e($item['href'] ?? '') ?>" maxlength="500" placeholder="/about-shweta/">
      </div>
      <div class="field">
        <label>CSS class</label>
        <input name="menu_items[<?= e($key) ?>][class]" value="<?= e($item['class'] ?? '') ?>" maxlength="120" placeholder="nav-about">
      </div>
      <label class="header-check">
        <input type="checkbox" name="menu_items[<?= e($key) ?>][external]" value="1"<?= ! empty($item['external']) ? ' checked' : '' ?>>
        <span>Open in new tab</span>
      </label>
      <button class="ghost-btn danger header-remove" type="button" data-repeat-remove>Remove</button>
    </div>
    <?php
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'header']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Site Header</p>
        <h1>Header Menu</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_homepage_url()) ?>">Homepage</a>
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

    <section class="panel header-preview-panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Preview</p>
          <h2>Header Preview</h2>
        </div>
      </div>
      <div class="header-preview-strip" aria-label="Header preview">
        <?php foreach ($navigation as $item): ?>
          <?php if (site_navigation_is_cta($item)): ?>
            <span class="header-preview-button"><?= e($item['label'] ?? '') ?></span>
          <?php else: ?>
            <span class="header-preview-link"><?= e($item['label'] ?? '') ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </section>

    <form class="content-form header-manager-form" method="post" action="<?= e(admin_header_url()) ?>">
      <?= csrf_field() ?>

      <section class="panel form-panel header-builder" data-repeat-builder>
        <div class="panel-head">
          <div>
            <p class="eyebrow">Navigation</p>
            <h2>Menu Items</h2>
          </div>
          <button class="ghost-btn" type="button" data-repeat-add>Add Menu Item</button>
        </div>
        <div class="repeat-list header-menu-list" data-repeat-list>
          <?php foreach ($menuItems as $index => $item): ?>
            <?php $renderMenuRow($item, (string) $index, $index + 1); ?>
          <?php endforeach; ?>
        </div>
        <p class="hint">Use site paths like <code>/cohorts/</code>, page anchors like <code>#schedule</code>, or full external URLs. Retired homepage sections are blocked from the menu.</p>
        <template data-repeat-template>
          <?php $renderMenuRow([], '__INDEX__', 1); ?>
        </template>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Header Action</p>
            <h2>Button Settings</h2>
          </div>
        </div>
        <div class="form-grid header-cta-grid">
          <div class="field">
            <label for="header_cta_label">Button text</label>
            <input id="header_cta_label" name="header_cta[label]" value="<?= e($cta['label'] ?? '') ?>" maxlength="80" placeholder="Schedule a Meet">
          </div>
          <div class="field">
            <label for="header_cta_href">Button link or action</label>
            <input id="header_cta_href" name="header_cta[href]" value="<?= e($cta['href'] ?? '') ?>" maxlength="500" placeholder="#schedule">
            <p class="hint">Use <code>#schedule</code> to open the meeting modal.</p>
          </div>
          <div class="field">
            <label for="header_cta_class">Button CSS class</label>
            <input id="header_cta_class" name="header_cta[class]" value="<?= e($cta['class'] ?? 'cta') ?>" maxlength="120" placeholder="cta">
          </div>
          <label class="header-check header-cta-check">
            <input type="checkbox" name="header_cta[external]" value="1"<?= ! empty($cta['external']) ? ' checked' : '' ?>>
            <span>Open button link in a new tab</span>
          </label>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit">Save Header</button>
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">Preview Site</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-header-page';
require APP_PATH . '/views/admin/layout.php';
