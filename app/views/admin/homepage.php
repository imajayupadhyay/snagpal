<?php
$rows = static function (array $items, int $blankRows = 1): array {
    for ($i = 0; $i < $blankRows; $i++) {
        $items[] = [];
    }

    return $items;
};
$lines = static fn (array $items): string => implode("\n", $items);
$paragraphs = static fn (array $items): string => implode("\n\n", $items);
$headerNavigationPreview = site_navigation(
    $content['navigation'] ?? [],
    empty($content['header_navigation_managed']),
    true
);
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
  <?php render('admin/partials/sidebar', ['active' => 'homepage']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Content Management</p>
        <h1>Homepage</h1>
      </div>
      <div class="top-actions">
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

    <nav class="section-jump" id="sectionJump" aria-label="Jump to section"></nav>

    <form class="content-form" method="post" action="<?= e(admin_homepage_url()) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">SEO</p><h2>Page Metadata</h2></div></div>
        <div class="form-grid">
          <div class="field">
            <label for="page_title">Page title</label>
            <input id="page_title" name="page[title]" value="<?= e($content['page']['title'] ?? '') ?>">
          </div>
          <div class="field small">
            <label for="page_lang">Language</label>
            <input id="page_lang" name="page[lang]" value="<?= e($content['page']['lang'] ?? 'en') ?>">
          </div>
          <div class="field small">
            <label for="theme_color">Theme color</label>
            <input id="theme_color" name="page[theme_color]" value="<?= e($content['page']['theme_color'] ?? '#0C5E55') ?>">
          </div>
          <div class="field full">
            <label for="page_description">Meta description</label>
            <textarea id="page_description" name="page[description]" rows="3"><?= e($content['page']['description'] ?? '') ?></textarea>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Identity</p><h2>Profile Basics</h2></div></div>
        <div class="form-grid">
          <div class="field"><label>First name</label><input name="identity[first_name]" value="<?= e($content['identity']['first_name'] ?? '') ?>"></div>
          <div class="field"><label>Last name</label><input name="identity[last_name]" value="<?= e($content['identity']['last_name'] ?? '') ?>"></div>
          <div class="field"><label>Full name</label><input name="identity[full_name]" value="<?= e($content['identity']['full_name'] ?? '') ?>"></div>
          <div class="field"><label>Email</label><input name="identity[email]" value="<?= e($content['identity']['email'] ?? '') ?>"></div>
          <div class="field"><label>Tagline</label><input name="identity[tagline]" value="<?= e($content['identity']['tagline'] ?? '') ?>"></div>
          <div class="field"><label>Footer tagline</label><input name="identity[footer_tagline]" value="<?= e($content['identity']['footer_tagline'] ?? '') ?>"></div>
          <div class="field"><label>Location</label><input name="identity[location]" value="<?= e($content['identity']['location'] ?? '') ?>"></div>
          <div class="field"><label>LinkedIn label</label><input name="identity[linkedin][label]" value="<?= e($content['identity']['linkedin']['label'] ?? '') ?>"></div>
          <div class="field full"><label>LinkedIn URL</label><input name="identity[linkedin][url]" value="<?= e($content['identity']['linkedin']['url'] ?? '') ?>"></div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div><p class="eyebrow">Navigation</p><h2>Header Menu</h2></div>
          <a class="ghost-link" href="<?= e(admin_header_url()) ?>">Manage Header</a>
        </div>
        <div class="header-preview-strip header-preview-strip-inline" aria-label="Header menu preview">
          <?php foreach ($headerNavigationPreview as $item): ?>
            <?php if (site_navigation_is_cta($item)): ?>
              <span class="header-preview-button"><?= e($item['label'] ?? '') ?></span>
            <?php else: ?>
              <span class="header-preview-link"><?= e($item['label'] ?? '') ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <p class="hint panel-hint">The desktop menu, mobile menu, and header button are managed from the dedicated Header page.</p>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Hero</p><h2>Opening Section</h2></div></div>
        <div class="form-grid">
          <div class="field"><label>Kicker</label><input name="hero[kicker]" value="<?= e($content['hero']['kicker'] ?? '') ?>"></div>
          <div class="field"><label>Kicker suffix</label><input name="hero[kicker_suffix]" value="<?= e($content['hero']['kicker_suffix'] ?? '') ?>"></div>
          <div class="field full"><label>Role</label><textarea name="hero[role]" rows="2"><?= e($content['hero']['role'] ?? '') ?></textarea></div>
          <div class="field full"><label>Lede</label><textarea name="hero[lede]" rows="4"><?= e($content['hero']['lede'] ?? '') ?></textarea></div>
          <div class="field media-field">
            <label>Current hero image path</label>
            <input name="hero[image][src]" value="<?= e($content['hero']['image']['src'] ?? '') ?>">
            <?php if (! empty($content['hero']['image']['src'])): ?><img src="<?= e(asset($content['hero']['image']['src'])) ?>" alt=""><?php endif; ?>
          </div>
          <div class="field media-field">
            <label>Upload new hero image</label>
            <input type="file" name="hero_image_upload" accept="image/jpeg,image/png,image/webp">
            <label>Hero image alt text</label>
            <input name="hero[image][alt]" value="<?= e($content['hero']['image']['alt'] ?? '') ?>">
          </div>
        </div>
        <h3>Hero stats</h3>
        <div class="repeat-list">
          <?php foreach ($rows($content['hero']['stats'] ?? [], 2) as $stat): ?>
            <div class="repeat-row two-col">
              <input name="hero_stats[value][]" placeholder="Value" value="<?= e($stat['value'] ?? '') ?>">
              <input name="hero_stats[label][]" placeholder="Label" value="<?= e($stat['label'] ?? '') ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Ticker Content</p><h2>Topics & Recommendations</h2></div></div>
        <div class="form-grid">
          <div class="field full">
            <label>Topics</label>
            <textarea name="topics_text" rows="6"><?= e($lines($content['topics'] ?? [])) ?></textarea>
            <p class="hint">One topic per line.</p>
          </div>
          <div class="field full">
            <label>Recommendations note</label>
            <input name="recommendations_note" value="<?= e($content['recommendations_note'] ?? '') ?>">
          </div>
        </div>
        <h3>Recommendations</h3>
        <div class="repeat-list">
          <?php foreach ($rows($content['recommendations'] ?? [], 2) as $item): ?>
            <div class="repeat-row recommendation-row">
              <textarea name="recommendations[q][]" rows="2" placeholder="Quote"><?= e($item['q'] ?? '') ?></textarea>
              <input name="recommendations[w][]" placeholder="Designation / person" value="<?= e($item['w'] ?? '') ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Profile</p><h2>About Section</h2></div></div>
        <div class="form-grid">
          <div class="field full"><label>Heading</label><input name="profile[heading]" value="<?= e($content['profile']['heading'] ?? '') ?>"></div>
          <div class="field media-field">
            <label>Current profile image path</label>
            <input name="profile[image][src]" value="<?= e($content['profile']['image']['src'] ?? '') ?>">
            <?php if (! empty($content['profile']['image']['src'])): ?><img src="<?= e(asset($content['profile']['image']['src'])) ?>" alt=""><?php endif; ?>
          </div>
          <div class="field media-field">
            <label>Upload new profile image</label>
            <input type="file" name="profile_image_upload" accept="image/jpeg,image/png,image/webp">
            <label>Profile image alt text</label>
            <input name="profile[image][alt]" value="<?= e($content['profile']['image']['alt'] ?? '') ?>">
          </div>
          <div class="field full">
            <label>Lead text</label>
            <textarea id="profile_lead_html" class="rich-source" name="profile[lead_html]"><?= e($content['profile']['lead_html'] ?? '') ?></textarea>
            <div class="wysiwyg" data-rich-editor data-editor-for="profile_lead_html" data-rich-mode="single">
              <div class="wysiwyg-toolbar" aria-label="Formatting toolbar">
                <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
              </div>
              <div class="wysiwyg-surface" contenteditable="true" role="textbox" aria-multiline="true"><?= homepage_rich_text($content['profile']['lead_html'] ?? '') ?></div>
            </div>
          </div>
          <div class="field full">
            <label>Body paragraphs</label>
            <textarea id="profile_paragraphs_text" class="rich-source" name="profile_paragraphs_text"><?= e($paragraphs($content['profile']['paragraphs_html'] ?? [])) ?></textarea>
            <div class="wysiwyg" data-rich-editor data-editor-for="profile_paragraphs_text" data-rich-mode="paragraphs">
              <div class="wysiwyg-toolbar" aria-label="Formatting toolbar">
                <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
              </div>
              <div class="wysiwyg-surface wysiwyg-tall" contenteditable="true" role="textbox" aria-multiline="true"><?= $editorParagraphs($content['profile']['paragraphs_html'] ?? []) ?></div>
            </div>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Expertise</p><h2>Expertise Rows</h2></div></div>
        <div class="field compact"><label>Heading</label><input name="expertise[heading]" value="<?= e($content['expertise']['heading'] ?? '') ?>"></div>
        <div class="repeat-list">
          <?php foreach ($rows($content['expertise']['items'] ?? [], 2) as $item): ?>
            <div class="repeat-row expertise-row">
              <input name="expertise_items[number][]" placeholder="Number" value="<?= e($item['number'] ?? '') ?>">
              <input name="expertise_items[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>">
              <textarea name="expertise_items[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Voice</p><h2>Quotes</h2></div></div>
        <div class="form-grid">
          <div class="field"><label>Quote author</label><input name="quote_author" value="<?= e($content['quote_author'] ?? '') ?>"></div>
          <div class="field full">
            <label>Rotating quotes</label>
            <textarea name="quotes_text" rows="7"><?= e($lines($content['quotes'] ?? [])) ?></textarea>
            <p class="hint">One quote per line.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Focus</p><h2>Focus Cards</h2></div></div>
        <div class="field compact"><label>Heading</label><input name="focus[heading]" value="<?= e($content['focus']['heading'] ?? '') ?>"></div>
        <div class="repeat-list">
          <?php foreach ($rows($content['focus']['items'] ?? [], 2) as $item): ?>
            <div class="repeat-row focus-row">
              <input name="focus_items[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>">
              <textarea name="focus_items[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Research</p><h2>Media & Engagements</h2></div></div>
        <div class="field compact"><label>Heading</label><input name="research[heading]" value="<?= e($content['research']['heading'] ?? '') ?>"></div>
        <h3>Research media</h3>
        <div class="repeat-list">
          <?php foreach ($rows($content['research']['media'] ?? [], 2) as $index => $media): ?>
            <div class="repeat-row media-row">
              <div class="media-field">
                <input name="research_media[src][]" placeholder="Image path" value="<?= e($media['src'] ?? '') ?>">
                <?php if (! empty($media['src'])): ?><img src="<?= e(asset($media['src'])) ?>" alt=""><?php endif; ?>
                <input type="file" name="research_media_image_<?= e((string) $index) ?>" accept="image/jpeg,image/png,image/webp">
              </div>
              <input name="research_media[alt][]" placeholder="Alt text" value="<?= e($media['alt'] ?? '') ?>">
              <input name="research_media[caption][]" placeholder="Caption" value="<?= e($media['caption'] ?? '') ?>">
            </div>
          <?php endforeach; ?>
        </div>
        <h3>Research rows</h3>
        <div class="repeat-list">
          <?php foreach ($rows($content['research']['items'] ?? [], 2) as $item): ?>
            <div class="repeat-row research-row">
              <input name="research_items[year][]" placeholder="Year" value="<?= e($item['year'] ?? '') ?>">
              <input name="research_items[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>">
              <input name="research_items[kind][]" placeholder="Kind" value="<?= e($item['kind'] ?? '') ?>">
              <textarea name="research_items[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="field compact"><label>Research note</label><input name="research[note]" value="<?= e($content['research']['note'] ?? '') ?>"></div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Cohorts</p><h2>Recent Cohorts</h2></div></div>
        <div class="form-grid">
          <div class="field full"><label>Heading</label><input name="cohorts[heading]" value="<?= e($content['cohorts']['heading'] ?? '') ?>"></div>
          <div class="field full"><label>Intro</label><textarea name="cohorts[intro]" rows="2"><?= e($content['cohorts']['intro'] ?? '') ?></textarea></div>
        </div>
        <h3>Cohort cards</h3>
        <div class="repeat-list">
          <?php foreach ($rows($content['cohorts']['items'] ?? [], 1) as $index => $item): ?>
            <div class="repeat-row cohort-row">
              <input name="cohorts_items[title][]" placeholder="Title" value="<?= e($item['title'] ?? '') ?>">
              <input name="cohorts_items[meta][]" placeholder="Meta (e.g. Cohort 03 - 2026)" value="<?= e($item['meta'] ?? '') ?>">
              <textarea name="cohorts_items[description][]" rows="2" placeholder="Description"><?= e($item['description'] ?? '') ?></textarea>
              <div class="media-field">
                <input name="cohorts_items[video][]" placeholder="Video link (YouTube / Vimeo / MP4) or uploaded path" value="<?= e($item['video'] ?? '') ?>">
                <input type="file" name="cohort_video_<?= e((string) $index) ?>" accept="video/mp4,video/webm,video/ogg,video/quicktime">
              </div>
              <div class="media-field">
                <input name="cohorts_items[poster][]" placeholder="Poster image path (optional)" value="<?= e($item['poster'] ?? '') ?>">
                <?php if (! empty($item['poster'])): ?><img src="<?= e(asset($item['poster'])) ?>" alt=""><?php endif; ?>
                <input type="file" name="cohort_poster_<?= e((string) $index) ?>" accept="image/jpeg,image/png,image/webp">
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <p class="hint">Paste a YouTube/Vimeo link <em>or</em> upload a video file (MP4/WebM, up to 64&nbsp;MB). Poster is optional and shows before an uploaded video plays. A row needs a title to be saved; clear the title and save to remove it.</p>
        <div class="field compact"><label>Note</label><input name="cohorts[note]" value="<?= e($content['cohorts']['note'] ?? '') ?>"></div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Closing CTA</p><h2>Schedule Section</h2></div></div>
        <div class="form-grid">
          <div class="field"><label>Eyebrow</label><input name="schedule[eyebrow]" value="<?= e($content['schedule']['eyebrow'] ?? '') ?>"></div>
          <div class="field"><label>Heading</label><input name="schedule[heading]" value="<?= e($content['schedule']['heading'] ?? '') ?>"></div>
          <div class="field full"><label>Description</label><textarea name="schedule[description]" rows="3"><?= e($content['schedule']['description'] ?? '') ?></textarea></div>
          <div class="field full"><label>Email subject</label><input name="schedule[email_subject]" value="<?= e($content['schedule']['email_subject'] ?? '') ?>"></div>
          <div class="field"><label>Button label</label><input name="schedule[cta_label]" value="<?= e($content['schedule']['cta_label'] ?? '') ?>" maxlength="80"></div>
        </div>
        <p class="hint">The button label is shown on the closing "Let's talk" section of the About, Awards, Cohorts, and Events pages.</p>
      </section>

      <div class="sticky-actions">
        <button type="submit">Save Homepage</button>
        <a class="ghost-link" href="<?= e(admin_dashboard_url()) ?>">Cancel</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-dashboard';
require APP_PATH . '/views/admin/layout.php';
