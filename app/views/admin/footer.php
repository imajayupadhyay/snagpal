<?php
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'footer']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Site Footer</p>
        <h1>Footer</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">View Site</a>
        <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
          <?= csrf_field() ?>
          <button class="ghost-btn" type="submit">Logout</button>
        </form>
      </div>
    </header>

    <p class="hint">This footer appears at the bottom of every page on the site &mdash; homepage, About, Cohorts, Awards, and Events.</p>

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
          <h2>Footer Preview</h2>
        </div>
      </div>
      <div class="header-preview-strip" aria-label="Footer preview" style="justify-content:space-between;">
        <span class="header-preview-link">&copy; <?= e((string) date('Y')) ?> <?= e($footer['copyright_name']) ?></span>
        <span class="header-preview-link"><?= e($footer['tagline']) ?></span>
        <span class="header-preview-link"><?= e($footer['back_to_top_label']) ?></span>
      </div>
    </section>

    <form class="content-form" method="post" action="<?= e(admin_footer_url()) ?>">
      <?= csrf_field() ?>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Footer</p>
            <h2>Footer Content</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="footer_copyright_name">Copyright name</label>
            <input id="footer_copyright_name" name="footer[copyright_name]" value="<?= e($footer['copyright_name']) ?>" maxlength="160" required>
            <p class="hint">Shown after the &copy; symbol and current year.</p>
          </div>
          <div class="field">
            <label for="footer_tagline">Tagline</label>
            <input id="footer_tagline" name="footer[tagline]" value="<?= e($footer['tagline']) ?>" maxlength="200">
          </div>
          <div class="field">
            <label for="footer_back_to_top_label">Back-to-top link label</label>
            <input id="footer_back_to_top_label" name="footer[back_to_top_label]" value="<?= e($footer['back_to_top_label']) ?>" maxlength="80" required>
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit">Save Footer</button>
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">Preview Site</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-footer-page';
require APP_PATH . '/views/admin/layout.php';
