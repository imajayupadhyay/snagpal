<?php
$form = cohort_admin_normalize($form ?? cohort_admin_default());
$isEditing = ! empty($form['id']);
$pageContent = array_merge(cohorts_page_default_content(), is_array($pageContent ?? null) ? $pageContent : []);
$takeawaysText = cohort_admin_takeaways_text($form);
$formatDateTime = static function (?string $value): string {
    if (! $value) {
        return 'Not set';
    }

    try {
        return (new DateTimeImmutable($value))->format('M j, Y g:i A');
    } catch (Throwable) {
        return 'Not set';
    }
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'cohorts']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Publishing</p>
        <h1>Cohorts</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_cohorts_url()) ?>">New Cohort</a>
        <a class="ghost-link" href="<?= e(url_path('cohorts/')) ?>" target="_blank" rel="noopener">View Page</a>
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

    <section class="metric-grid">
      <article>
        <span>Total</span>
        <strong><?= e((string) ($overview['total'] ?? 0)) ?></strong>
        <p>Cohorts saved in the admin library.</p>
      </article>
      <article>
        <span>Published</span>
        <strong><?= e((string) ($overview['published'] ?? 0)) ?></strong>
        <p>Ready for the public cohort archive.</p>
      </article>
      <article>
        <span>Drafts</span>
        <strong><?= e((string) ($overview['draft'] ?? 0)) ?></strong>
        <p>Work in progress entries.</p>
      </article>
      <article>
        <span>Featured</span>
        <strong><?= e((string) ($overview['featured'] ?? 0)) ?></strong>
        <p>The primary cohort for the archive hero.</p>
      </article>
    </section>

    <form class="content-form" method="post" action="<?= e(admin_cohorts_url()) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_page">

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Page Header</p>
            <h2>Hero &amp; Page Content</h2>
          </div>
        </div>
        <p class="hint">The section heading and intro paragraph above the cohort list are managed from the Homepage editor's "Cohorts" panel. Use the fields below for the rest of this page's hero.</p>
        <div class="form-grid">
          <div class="field">
            <label for="page_kicker">Kicker label</label>
            <input id="page_kicker" name="kicker" value="<?= e($pageContent['kicker']) ?>" maxlength="120">
          </div>
          <div class="field">
            <label for="page_heading_line1">Heading word 1</label>
            <input id="page_heading_line1" name="heading_line1" value="<?= e($pageContent['heading_line1']) ?>" maxlength="60">
          </div>
          <div class="field">
            <label for="page_heading_line2">Heading word 2</label>
            <input id="page_heading_line2" name="heading_line2" value="<?= e($pageContent['heading_line2']) ?>" maxlength="60">
            <p class="hint">Displayed as two stacked lines, e.g. "Cohort" / "Library".</p>
          </div>
          <div class="field">
            <label for="page_browse_cta_label">Browse link label</label>
            <input id="page_browse_cta_label" name="browse_cta_label" value="<?= e($pageContent['browse_cta_label']) ?>" maxlength="80">
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Stat Row</p>
            <h2>Hero Stat Tiles</h2>
          </div>
        </div>
        <p class="hint">The first tile always shows the live published cohort count. These three are editable text.</p>
        <div class="form-grid">
          <div class="field">
            <label for="page_stat2_value">Stat 2 value</label>
            <input id="page_stat2_value" name="stat2_value" value="<?= e($pageContent['stat2_value']) ?>" maxlength="40">
          </div>
          <div class="field">
            <label for="page_stat2_label">Stat 2 label</label>
            <input id="page_stat2_label" name="stat2_label" value="<?= e($pageContent['stat2_label']) ?>" maxlength="120">
          </div>
          <div class="field">
            <label for="page_stat3_value">Stat 3 value</label>
            <input id="page_stat3_value" name="stat3_value" value="<?= e($pageContent['stat3_value']) ?>" maxlength="40">
          </div>
          <div class="field">
            <label for="page_stat3_label">Stat 3 label</label>
            <input id="page_stat3_label" name="stat3_label" value="<?= e($pageContent['stat3_label']) ?>" maxlength="120">
          </div>
          <div class="field">
            <label for="page_stat4_value">Stat 4 value</label>
            <input id="page_stat4_value" name="stat4_value" value="<?= e($pageContent['stat4_value']) ?>" maxlength="40">
          </div>
          <div class="field">
            <label for="page_stat4_label">Stat 4 label</label>
            <input id="page_stat4_label" name="stat4_label" value="<?= e($pageContent['stat4_label']) ?>" maxlength="120">
          </div>
        </div>
        <div class="sticky-actions">
          <button type="submit">Save Page Header</button>
        </div>
      </section>
    </form>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Library</p>
          <h2>Existing Cohorts</h2>
        </div>
        <span class="muted"><?= e((string) count($cohorts)) ?> entries</span>
      </div>

      <?php if ($cohorts === []): ?>
        <p class="empty-state">No cohorts have been created yet. Use the editor below to add the first one.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table cohort-admin-table">
            <thead>
              <tr>
                <th>Cohort</th>
                <th>Slug</th>
                <th>Status</th>
                <th>Publish</th>
                <th>Order</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cohorts as $cohort): ?>
                <tr>
                  <td>
                    <strong><?= e($cohort['title'] ?? '') ?></strong>
                    <?php if (! empty($cohort['is_featured'])): ?><span class="status-pill open" style="margin-left:8px;">Featured</span><?php endif; ?>
                    <?php if (! empty($cohort['excerpt'])): ?><br><span class="muted"><?= e($cohort['excerpt']) ?></span><?php endif; ?>
                  </td>
                  <td><code><?= e($cohort['slug'] ?? '') ?></code></td>
                  <td><span class="status-pill <?= e((string) ($cohort['status'] ?? 'draft')) ?>"><?= e(ucfirst((string) ($cohort['status'] ?? 'draft'))) ?></span></td>
                  <td><?= e($formatDateTime($cohort['published_at'] ?? null)) ?></td>
                  <td><?= e((string) ($cohort['sort_order'] ?? 0)) ?></td>
                  <td>
                    <div class="table-actions">
                      <a class="ghost-link ghost-link-small" href="<?= e(admin_cohorts_url(['edit' => (int) $cohort['id']])) ?>">Edit</a>
                      <form method="post" action="<?= e(admin_cohorts_url()) ?>" onsubmit="return confirm('Delete this cohort? This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_cohort">
                        <input type="hidden" name="cohort_id" value="<?= e((string) $cohort['id']) ?>">
                        <button class="ghost-btn small danger" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <form class="content-form cohort-editor-form" method="post" action="<?= e(admin_cohorts_url($isEditing ? ['edit' => (int) $form['id']] : [])) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_cohort">
      <input type="hidden" name="cohort_id" value="<?= e((string) ($form['id'] ?? '')) ?>">

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow"><?= $isEditing ? 'Edit' : 'New' ?></p>
            <h2><?= $isEditing ? 'Edit Cohort' : 'Create Cohort' ?></h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="cohort_title">Title</label>
            <input id="cohort_title" name="title" value="<?= e($form['title']) ?>" maxlength="180" required>
          </div>
          <div class="field">
            <label for="cohort_slug">URL slug</label>
            <input id="cohort_slug" name="slug" value="<?= e($form['slug']) ?>" maxlength="220" placeholder="ai-governance-foundations">
            <p class="hint">Leave blank on a new cohort to generate it from the title.</p>
          </div>
          <div class="field">
            <label for="cohort_meta">Meta label</label>
            <input id="cohort_meta" name="meta_label" value="<?= e($form['meta_label']) ?>" maxlength="120" placeholder="Cohort 01 · AI Governance">
          </div>
          <div class="field">
            <label for="cohort_status">Status</label>
            <select id="cohort_status" name="status">
              <?php foreach (cohort_admin_status_options() as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $form['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="cohort_sort">Sort order</label>
            <input id="cohort_sort" type="number" name="sort_order" value="<?= e((string) $form['sort_order']) ?>" step="1">
          </div>
          <div class="field">
            <label for="cohort_published_at">Published at</label>
            <input id="cohort_published_at" type="datetime-local" name="published_at" value="<?= e($form['published_at']) ?>">
            <p class="hint">Published cohorts get the current time automatically if this is empty.</p>
          </div>
          <label class="check-tile cohort-featured-toggle">
            <input type="checkbox" name="is_featured" value="1"<?= ! empty($form['is_featured']) ? ' checked' : '' ?>>
            <span>Feature this cohort</span>
          </label>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Content</p>
            <h2>Post Details</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field full">
            <label for="cohort_excerpt">Excerpt</label>
            <textarea id="cohort_excerpt" name="excerpt" rows="2" maxlength="500" placeholder="Short one-line summary for admin lists and future previews."><?= e($form['excerpt']) ?></textarea>
          </div>
          <div class="field full">
            <label for="cohort_description">Card description</label>
            <textarea id="cohort_description" name="description" rows="4" placeholder="Description shown on archive cards and detail hero."><?= e($form['description']) ?></textarea>
          </div>
          <div class="field full">
            <label for="cohort_content">Article content</label>
            <textarea id="cohort_content" class="rich-source" name="content"><?= e($form['content']) ?></textarea>
            <div class="wysiwyg wysiwyg-rich" data-rich-editor data-editor-for="cohort_content" data-rich-mode="html">
              <div class="wysiwyg-toolbar wysiwyg-toolbar-rich" aria-label="Article formatting toolbar">
                <button type="button" data-command="formatBlock" data-value="p" title="Paragraph">P</button>
                <button type="button" data-command="formatBlock" data-value="h2" title="Heading 2">H2</button>
                <button type="button" data-command="formatBlock" data-value="h3" title="Heading 3">H3</button>
                <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                <button type="button" data-command="insertUnorderedList" title="Bullet list">List</button>
                <button type="button" data-command="insertOrderedList" title="Numbered list">1. List</button>
                <button type="button" data-command="formatBlock" data-value="blockquote" title="Quote">Quote</button>
                <button type="button" data-command="createLink" title="Add link">Link</button>
                <button type="button" data-command="unlink" title="Remove link">Unlink</button>
                <button type="button" data-command="removeFormat" title="Clear formatting">Clear</button>
              </div>
              <div class="wysiwyg-surface wysiwyg-tall wysiwyg-article-surface" contenteditable="true" role="textbox" aria-multiline="true"><?= cohort_rich_content_for_editor($form['content']) ?></div>
            </div>
          </div>
          <div class="field full">
            <label for="cohort_takeaways">Takeaways</label>
            <textarea id="cohort_takeaways" name="takeaways_text" rows="5" placeholder="One takeaway per line."><?= e($takeawaysText) ?></textarea>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Media</p>
            <h2>Video & Poster</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="cohort_video_source">Video source</label>
            <select id="cohort_video_source" name="video_source_type">
              <?php foreach (cohort_admin_source_options() as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $form['video_source_type'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="cohort_video_url">Video link</label>
            <input id="cohort_video_url" name="video_url" value="<?= e($form['video_url']) ?>" maxlength="500" placeholder="https://youtube.com/watch?v=...">
            <p class="hint">YouTube, Vimeo, or a direct video URL.</p>
          </div>
          <div class="field">
            <label for="cohort_video_path">Current uploaded video path</label>
            <input id="cohort_video_path" name="video_path" value="<?= e($form['video_path']) ?>" maxlength="500" placeholder="uploads/cohorts/videos/file.mp4">
          </div>
          <div class="field media-field">
            <label for="cohort_video_upload">Upload video</label>
            <input id="cohort_video_upload" type="file" name="video_upload" accept="video/mp4,video/webm,video/ogg,video/quicktime">
            <p class="hint">MP4, WebM, OGG, or MOV. Max 64 MB.</p>
          </div>
          <div class="field media-field">
            <label for="cohort_poster_image">Current poster path</label>
            <input id="cohort_poster_image" name="poster_image" value="<?= e($form['poster_image']) ?>" maxlength="500" placeholder="uploads/cohorts/poster.jpg">
            <?php if ($form['poster_image'] !== ''): ?>
              <img src="<?= e(asset($form['poster_image'])) ?>" alt="">
            <?php endif; ?>
          </div>
          <div class="field media-field">
            <label for="cohort_poster_upload">Upload poster image</label>
            <input id="cohort_poster_upload" type="file" name="poster_upload" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG, or WebP. Max 5 MB.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Links</p>
            <h2>Related Resource</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="cohort_resource_label">Link label</label>
            <input id="cohort_resource_label" name="resource_label" value="<?= e($form['resource_label']) ?>" maxlength="120" placeholder="View programme notes">
          </div>
          <div class="field">
            <label for="cohort_resource_url">Link URL</label>
            <input id="cohort_resource_url" name="resource_url" value="<?= e($form['resource_url']) ?>" maxlength="500" placeholder="https://...">
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit"><?= $isEditing ? 'Save Cohort' : 'Create Cohort' ?></button>
        <a class="ghost-link" href="<?= e(admin_cohorts_url()) ?>">Reset Form</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-cohorts-page';
require APP_PATH . '/views/admin/layout.php';
