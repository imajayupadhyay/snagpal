<?php
$pageContent = array_merge(awards_page_default_content(), is_array($pageContent ?? null) ? $pageContent : []);
$rows = static function (array $items, int $blankRows = 1): array {
    for ($i = 0; $i < $blankRows; $i++) {
        $items[] = [];
    }

    return $items;
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'awards']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Publishing</p>
        <h1>Awards Page</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path('awards-and-recognitions/')) ?>" target="_blank" rel="noopener">View Page</a>
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

    <p class="hint" style="margin-bottom:1.2rem;">Everything on this page is managed here, independently of the homepage and other pages. The form below follows the page top to bottom — use the jump bar to move between sections.</p>

    <nav class="section-jump" id="sectionJump" aria-label="Jump to section"></nav>

    <form class="content-form" method="post" action="<?= e(admin_awards_url()) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">1. Hero</p>
            <h2>Top Banner</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="kicker">Kicker label</label>
            <input id="kicker" name="kicker" value="<?= e($pageContent['kicker']) ?>" maxlength="80">
          </div>
          <div class="field">
            <label for="heading_line1">Heading word 1</label>
            <input id="heading_line1" name="heading_line1" value="<?= e($pageContent['heading_line1']) ?>" maxlength="40">
          </div>
          <div class="field">
            <label for="heading_line2">Heading word 2</label>
            <input id="heading_line2" name="heading_line2" value="<?= e($pageContent['heading_line2']) ?>" maxlength="40">
            <p class="hint">Displayed as two stacked lines, e.g. "Awards" / "Recognitions".</p>
          </div>
          <div class="field full">
            <label for="hero_role">Role line</label>
            <input id="hero_role" name="hero_role" value="<?= e($pageContent['hero_role']) ?>" maxlength="220">
          </div>
          <div class="field full">
            <label for="hero_lede">Lede paragraph</label>
            <textarea id="hero_lede" name="hero_lede" rows="3"><?= e($pageContent['hero_lede']) ?></textarea>
          </div>
        </div>
        <p class="hint">Stat row shown below the hero. Leave a row's value blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['hero_stats'], 1) as $stat): ?>
            <div class="repeat-row two-col">
              <input name="hero_stats[value][]" placeholder="Value (e.g. 2026)" value="<?= e($stat['value'] ?? '') ?>" maxlength="40">
              <input name="hero_stats[label][]" placeholder="Label" value="<?= e($stat['label'] ?? '') ?>" maxlength="120">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">2. Hero Card</p>
            <h2>Recognition Ledger Panel</h2>
          </div>
        </div>
        <p class="hint">The image and copy card next to the hero heading.</p>
        <div class="form-grid">
          <div class="field media-field">
            <label for="hero_card_image_src">Current image path</label>
            <input id="hero_card_image_src" name="hero_card_image_src" value="<?= e($pageContent['hero_card_image_src']) ?>" maxlength="500">
            <?php if ($pageContent['hero_card_image_src'] !== ''): ?>
              <img src="<?= e(asset($pageContent['hero_card_image_src'])) ?>" alt="">
            <?php endif; ?>
          </div>
          <div class="field media-field">
            <label for="hero_card_image_upload">Upload new image</label>
            <input id="hero_card_image_upload" type="file" name="hero_card_image_upload" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG, or WebP. Max 5 MB.</p>
          </div>
          <div class="field">
            <label for="hero_card_image_alt">Image alt text</label>
            <input id="hero_card_image_alt" name="hero_card_image_alt" value="<?= e($pageContent['hero_card_image_alt']) ?>" maxlength="180">
          </div>
          <div class="field">
            <label for="hero_card_eyebrow">Eyebrow</label>
            <input id="hero_card_eyebrow" name="hero_card_eyebrow" value="<?= e($pageContent['hero_card_eyebrow']) ?>" maxlength="60">
          </div>
          <div class="field full">
            <label for="hero_card_title">Title</label>
            <input id="hero_card_title" name="hero_card_title" value="<?= e($pageContent['hero_card_title']) ?>" maxlength="160">
          </div>
          <div class="field full">
            <label for="hero_card_description">Description</label>
            <textarea id="hero_card_description" name="hero_card_description" rows="2"><?= e($pageContent['hero_card_description']) ?></textarea>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">3. Recognition Ledger</p>
            <h2>Award Cards</h2>
          </div>
        </div>
        <div class="field compact">
          <label for="ledger_heading">Section heading</label>
          <input id="ledger_heading" name="ledger_heading" value="<?= e($pageContent['ledger_heading']) ?>" maxlength="80">
        </div>
        <p class="hint">Leave a card's title blank to remove it. Each card's photo is optional.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['recognitions'], 1) as $index => $item): ?>
            <div class="repeat-row">
              <input name="recognitions[year][]" placeholder="Year" value="<?= e($item['year'] ?? '') ?>" maxlength="20">
              <input name="recognitions[type][]" placeholder="Type (e.g. Felicitation)" value="<?= e($item['type'] ?? '') ?>" maxlength="60">
              <input name="recognitions[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="180">
              <textarea name="recognitions[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
              <div class="media-field">
                <input name="recognitions[image][]" placeholder="Image path (optional)" value="<?= e($item['image'] ?? '') ?>" maxlength="500">
                <?php if (! empty($item['image'])): ?><img src="<?= e(asset($item['image'])) ?>" alt=""><?php endif; ?>
                <input type="file" name="recognition_image_<?= e((string) $index) ?>" accept="image/jpeg,image/png,image/webp">
              </div>
              <input name="recognitions[image_alt][]" placeholder="Image alt text" value="<?= e($item['image_alt'] ?? '') ?>" maxlength="180">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">4. Editorial Standard</p>
            <h2>Standards Section</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="standards_eyebrow">Eyebrow</label>
            <input id="standards_eyebrow" name="standards_eyebrow" value="<?= e($pageContent['standards_eyebrow']) ?>" maxlength="60">
          </div>
          <div class="field full">
            <label for="standards_heading">Heading</label>
            <input id="standards_heading" name="standards_heading" value="<?= e($pageContent['standards_heading']) ?>" maxlength="160">
          </div>
          <div class="field full">
            <label for="standards_intro">Intro paragraph</label>
            <textarea id="standards_intro" name="standards_intro" rows="2"><?= e($pageContent['standards_intro']) ?></textarea>
          </div>
        </div>
        <p class="hint">Standards cards in the grid. Leave a card's title blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['standards'], 1) as $item): ?>
            <div class="repeat-row focus-row">
              <input name="standards[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="100">
              <textarea name="standards[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">5. Closing</p>
            <h2>Schedule a Meet Banner</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="schedule_eyebrow">Eyebrow</label>
            <input id="schedule_eyebrow" name="schedule_eyebrow" value="<?= e($pageContent['schedule_eyebrow']) ?>" maxlength="60">
          </div>
          <div class="field">
            <label for="schedule_heading">Heading</label>
            <input id="schedule_heading" name="schedule_heading" value="<?= e($pageContent['schedule_heading']) ?>" maxlength="80">
          </div>
          <div class="field full">
            <label for="schedule_description">Description</label>
            <textarea id="schedule_description" name="schedule_description" rows="2"><?= e($pageContent['schedule_description']) ?></textarea>
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit">Save Awards Page</button>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-awards-page';
require APP_PATH . '/views/admin/layout.php';
