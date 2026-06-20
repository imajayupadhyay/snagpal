<?php
$pageContent = array_merge(about_page_default_content(), is_array($pageContent ?? null) ? $pageContent : []);
$rows = static function (array $items, int $blankRows = 1): array {
    for ($i = 0; $i < $blankRows; $i++) {
        $items[] = [];
    }

    return $items;
};
$paragraphsText = static fn (array $items): string => implode("\n\n", $items);
$editorParagraphs = static function (array $items): string {
    $html = '';

    foreach ($items as $item) {
        $paragraph = homepage_rich_text($item);

        if ($paragraph !== '') {
            $html .= '<p>' . $paragraph . '</p>';
        }
    }

    return $html !== '' ? $html : '<p><br></p>';
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'about']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Publishing</p>
        <h1>About Page</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path('about-shweta/')) ?>" target="_blank" rel="noopener">View Page</a>
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

    <p class="hint" style="margin-bottom:1.2rem;">Everything on this page is managed here, independently of the homepage. The form below follows the page top to bottom — use the jump bar to move between sections.</p>

    <nav class="section-jump" id="sectionJump" aria-label="Jump to section"></nav>

    <form class="content-form" method="post" action="<?= e(admin_about_url()) ?>" enctype="multipart/form-data">
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
            <label for="heading_line1">Heading word 1</label>
            <input id="heading_line1" name="heading_line1" value="<?= e($pageContent['heading_line1']) ?>" maxlength="40">
          </div>
          <div class="field">
            <label for="heading_line2">Heading word 2</label>
            <input id="heading_line2" name="heading_line2" value="<?= e($pageContent['heading_line2']) ?>" maxlength="40">
            <p class="hint">Displayed as two stacked lines, e.g. "About" / "Shweta".</p>
          </div>
          <div class="field">
            <label for="kicker_suffix">Kicker suffix</label>
            <input id="kicker_suffix" name="kicker_suffix" value="<?= e($pageContent['kicker_suffix']) ?>" maxlength="80">
            <p class="hint">Shown after "About Shweta ·" in the small label above the heading.</p>
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
              <input name="hero_stats[value][]" placeholder="Value (e.g. ~10 yrs)" value="<?= e($stat['value'] ?? '') ?>" maxlength="40">
              <input name="hero_stats[label][]" placeholder="Label" value="<?= e($stat['label'] ?? '') ?>" maxlength="120">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">2. Hero Portrait</p>
            <h2>Photo &amp; Mandate Panel</h2>
          </div>
        </div>
        <p class="hint">The photo card next to the hero heading.</p>
        <div class="form-grid">
          <div class="field media-field">
            <label for="portrait_src">Current portrait image path</label>
            <input id="portrait_src" name="portrait_src" value="<?= e($pageContent['portrait_src']) ?>" maxlength="500">
            <?php if ($pageContent['portrait_src'] !== ''): ?>
              <img src="<?= e(asset($pageContent['portrait_src'])) ?>" alt="">
            <?php endif; ?>
          </div>
          <div class="field media-field">
            <label for="portrait_image_upload">Upload new portrait</label>
            <input id="portrait_image_upload" type="file" name="portrait_image_upload" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG, or WebP. Max 5 MB.</p>
          </div>
          <div class="field">
            <label for="portrait_alt">Portrait alt text</label>
            <input id="portrait_alt" name="portrait_alt" value="<?= e($pageContent['portrait_alt']) ?>" maxlength="180">
          </div>
          <div class="field">
            <label for="mandate_eyebrow">Mandate label</label>
            <input id="mandate_eyebrow" name="mandate_eyebrow" value="<?= e($pageContent['mandate_eyebrow']) ?>" maxlength="60">
            <p class="hint">Label above the mandate line (e.g. "Current Mandate"). The value shown is the first fact tile below.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">3. Recommendations</p>
            <h2>Quote Marquee</h2>
          </div>
        </div>
        <p class="hint">Scrolling quote strip shown right after the hero. Leave a row's quote blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['recommendations'], 1) as $item): ?>
            <div class="repeat-row two-col">
              <textarea name="recommendations[q][]" rows="2" placeholder="Quote"><?= e($item['q'] ?? '') ?></textarea>
              <input name="recommendations[w][]" placeholder="Who (e.g. Chief Engineer, Power Utility)" value="<?= e($item['w'] ?? '') ?>" maxlength="120">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="field compact">
          <label for="recommendations_note">Footer note</label>
          <input id="recommendations_note" name="recommendations_note" value="<?= e($pageContent['recommendations_note']) ?>" maxlength="160">
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">4. Profile</p>
            <h2>Story &amp; Fact Tiles</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="profile_eyebrow">Section eyebrow</label>
            <input id="profile_eyebrow" name="profile_eyebrow" value="<?= e($pageContent['profile_eyebrow']) ?>" maxlength="60">
          </div>
          <div class="field">
            <label for="profile_heading">Section heading</label>
            <input id="profile_heading" name="profile_heading" value="<?= e($pageContent['profile_heading']) ?>" maxlength="120">
          </div>
          <div class="field full">
            <label for="profile_lead_html">Lead text</label>
            <textarea id="profile_lead_html" class="rich-source" name="profile_lead_html"><?= e($pageContent['profile_lead_html']) ?></textarea>
            <div class="wysiwyg" data-rich-editor data-editor-for="profile_lead_html" data-rich-mode="single">
              <div class="wysiwyg-toolbar" aria-label="Formatting toolbar">
                <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
              </div>
              <div class="wysiwyg-surface" contenteditable="true" role="textbox" aria-multiline="true"><?= homepage_rich_text($pageContent['profile_lead_html']) ?></div>
            </div>
          </div>
          <div class="field full">
            <label for="profile_paragraphs_text">Body paragraphs</label>
            <textarea id="profile_paragraphs_text" class="rich-source" name="profile_paragraphs_text"><?= e($paragraphsText($pageContent['profile_paragraphs_html'])) ?></textarea>
            <div class="wysiwyg" data-rich-editor data-editor-for="profile_paragraphs_text" data-rich-mode="paragraphs">
              <div class="wysiwyg-toolbar" aria-label="Formatting toolbar">
                <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
              </div>
              <div class="wysiwyg-surface wysiwyg-tall" contenteditable="true" role="textbox" aria-multiline="true"><?= $editorParagraphs($pageContent['profile_paragraphs_html']) ?></div>
            </div>
          </div>
        </div>
        <p class="hint">Fact tiles shown below the story. Leave a row blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['facts'], 1) as $fact): ?>
            <div class="repeat-row two-col">
              <input name="facts[label][]" placeholder="Label (e.g. Current role)" value="<?= e($fact['label'] ?? '') ?>" maxlength="80">
              <input name="facts[value][]" placeholder="Value" value="<?= e($fact['value'] ?? '') ?>" maxlength="180">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">5. Work &amp; Mandate</p>
            <h2>Four Cards</h2>
          </div>
        </div>
        <div class="field compact">
          <label for="path_heading">Section heading</label>
          <input id="path_heading" name="path_heading" value="<?= e($pageContent['path_heading']) ?>" maxlength="80">
        </div>
        <p class="hint">Leave a card's label blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['path_items'], 1) as $item): ?>
            <div class="repeat-row">
              <input name="path[label][]" placeholder="Label (e.g. Public technology)" value="<?= e($item['label'] ?? '') ?>" maxlength="60">
              <input name="path[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="120">
              <textarea name="path[text][]" rows="2" placeholder="Body text"><?= e($item['text'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">6. Operating Principles</p>
            <h2>Quote &amp; Focus Cards</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="principles_eyebrow">Eyebrow</label>
            <input id="principles_eyebrow" name="principles_eyebrow" value="<?= e($pageContent['principles_eyebrow']) ?>" maxlength="60">
          </div>
          <div class="field full">
            <label for="principles_heading">Heading</label>
            <input id="principles_heading" name="principles_heading" value="<?= e($pageContent['principles_heading']) ?>" maxlength="160">
          </div>
          <div class="field full">
            <label for="principles_quote">Quote</label>
            <textarea id="principles_quote" name="principles_quote" rows="2"><?= e($pageContent['principles_quote']) ?></textarea>
          </div>
        </div>
        <p class="hint">Focus cards in the grid next to the quote. Leave a card's title blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['focus_items'], 1) as $item): ?>
            <div class="repeat-row focus-row">
              <input name="focus[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="100">
              <textarea name="focus[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">7. Expertise</p>
            <h2>Expertise Rows</h2>
          </div>
        </div>
        <div class="field compact">
          <label for="expertise_heading">Section heading</label>
          <input id="expertise_heading" name="expertise_heading" value="<?= e($pageContent['expertise_heading']) ?>" maxlength="80">
        </div>
        <p class="hint">Leave a row's title blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['expertise_items'], 1) as $item): ?>
            <div class="repeat-row expertise-row">
              <input name="expertise[number][]" placeholder="Number (e.g. E.01)" value="<?= e($item['number'] ?? '') ?>" maxlength="10">
              <input name="expertise[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="120">
              <textarea name="expertise[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">8. Research &amp; Engagement</p>
            <h2>Media &amp; Rows</h2>
          </div>
        </div>
        <div class="field compact">
          <label for="research_heading">Section heading</label>
          <input id="research_heading" name="research_heading" value="<?= e($pageContent['research_heading']) ?>" maxlength="80">
        </div>
        <h3>Photos</h3>
        <p class="hint">Leave a row's path blank and don't upload a file to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['research_media'], 1) as $index => $media): ?>
            <div class="repeat-row media-row">
              <div class="media-field">
                <input name="research_media[src][]" placeholder="Image path" value="<?= e($media['src'] ?? '') ?>" maxlength="500">
                <?php if (! empty($media['src'])): ?><img src="<?= e(asset($media['src'])) ?>" alt=""><?php endif; ?>
                <input type="file" name="research_media_image_<?= e((string) $index) ?>" accept="image/jpeg,image/png,image/webp">
              </div>
              <input name="research_media[alt][]" placeholder="Alt text" value="<?= e($media['alt'] ?? '') ?>" maxlength="180">
              <input name="research_media[caption][]" placeholder="Caption" value="<?= e($media['caption'] ?? '') ?>" maxlength="180">
            </div>
          <?php endforeach; ?>
        </div>
        <h3>Engagement rows</h3>
        <p class="hint">Leave a row's title blank to remove it.</p>
        <div class="repeat-list">
          <?php foreach ($rows($pageContent['research_items'], 1) as $item): ?>
            <div class="repeat-row research-row">
              <input name="research[year][]" placeholder="Year" value="<?= e($item['year'] ?? '') ?>" maxlength="20">
              <input name="research[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>" maxlength="180">
              <input name="research[kind][]" placeholder="Kind (e.g. Speaker)" value="<?= e($item['kind'] ?? '') ?>" maxlength="40">
              <textarea name="research[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="field compact">
          <label for="research_note">Closing note</label>
          <input id="research_note" name="research_note" value="<?= e($pageContent['research_note']) ?>" maxlength="220">
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">9. Closing</p>
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
        <button type="submit">Save About Page</button>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-about-page';
require APP_PATH . '/views/admin/layout.php';
