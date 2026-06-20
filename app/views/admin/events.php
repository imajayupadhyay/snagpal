<?php
$form = event_admin_normalize($form ?? event_admin_default());
$isEditing = ! empty($form['id']);
$pageContent = array_merge(events_page_default_content(), is_array($pageContent ?? null) ? $pageContent : []);
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
$formatDate = static function (?string $value): string {
    if (! $value) {
        return 'No date set';
    }

    try {
        return (new DateTimeImmutable($value))->format('M j, Y');
    } catch (Throwable) {
        return 'No date set';
    }
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'events']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Publishing</p>
        <h1>Events</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_events_url()) ?>">New Event</a>
        <a class="ghost-link" href="<?= e(url_path('events/')) ?>" target="_blank" rel="noopener">View Page</a>
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
        <p>Events saved in the admin library.</p>
      </article>
      <article>
        <span>Published</span>
        <strong><?= e((string) ($overview['published'] ?? 0)) ?></strong>
        <p>Visible on the public events page.</p>
      </article>
      <article>
        <span>Drafts</span>
        <strong><?= e((string) ($overview['draft'] ?? 0)) ?></strong>
        <p>Work in progress entries.</p>
      </article>
      <article>
        <span>Upcoming</span>
        <strong><?= e((string) ($overview['upcoming'] ?? 0)) ?></strong>
        <p>Dated today or later, or with no date set.</p>
      </article>
      <article>
        <span>Past</span>
        <strong><?= e((string) ($overview['past'] ?? 0)) ?></strong>
        <p>Event date has already passed.</p>
      </article>
    </section>

    <form class="content-form" method="post" action="<?= e(admin_events_url()) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_page">

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Page Header</p>
            <h2>Hero &amp; Page Content</h2>
          </div>
        </div>
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
            <p class="hint">Displayed as two stacked lines, e.g. "Events" / "Calendar".</p>
          </div>
          <div class="field full">
            <label for="page_intro">Intro paragraph</label>
            <textarea id="page_intro" name="intro" rows="3"><?= e($pageContent['intro']) ?></textarea>
          </div>
          <div class="field">
            <label for="page_panel_eyebrow">Summary panel eyebrow</label>
            <input id="page_panel_eyebrow" name="panel_eyebrow" value="<?= e($pageContent['panel_eyebrow']) ?>" maxlength="80">
          </div>
          <div class="field">
            <label for="page_panel_title">Summary panel title</label>
            <input id="page_panel_title" name="panel_title" value="<?= e($pageContent['panel_title']) ?>" maxlength="120">
          </div>
          <div class="field full">
            <label for="page_panel_description">Summary panel description</label>
            <textarea id="page_panel_description" name="panel_description" rows="2"><?= e($pageContent['panel_description']) ?></textarea>
          </div>
          <div class="field full">
            <label for="page_note">Closing note</label>
            <textarea id="page_note" name="note" rows="2" placeholder="Shown below Past Events. Leave blank to hide."><?= e($pageContent['note']) ?></textarea>
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
          <h2>Existing Events</h2>
        </div>
        <span class="muted"><?= e((string) count($events)) ?> entries</span>
      </div>

      <?php if ($events === []): ?>
        <p class="empty-state">No events have been created yet. Use the editor below to add the first one.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Event</th>
                <th>Date</th>
                <th>Location</th>
                <th>Status</th>
                <th>Order</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($events as $event): ?>
                <tr>
                  <td>
                    <strong><?= e($event['title'] ?? '') ?></strong>
                    <?php if (! empty($event['meta_label'])): ?><br><span class="muted"><?= e($event['meta_label']) ?></span><?php endif; ?>
                  </td>
                  <td><?= e($formatDate($event['event_date'] ?? null)) ?></td>
                  <td><?= e($event['location'] ?? '') ?></td>
                  <td><span class="status-pill <?= e((string) ($event['status'] ?? 'draft')) ?>"><?= e(ucfirst((string) ($event['status'] ?? 'draft'))) ?></span></td>
                  <td><?= e((string) ($event['sort_order'] ?? 0)) ?></td>
                  <td>
                    <div class="table-actions">
                      <a class="ghost-link ghost-link-small" href="<?= e(admin_events_url(['edit' => (int) $event['id']])) ?>">Edit</a>
                      <form method="post" action="<?= e(admin_events_url()) ?>" onsubmit="return confirm('Delete this event? This cannot be undone.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_event">
                        <input type="hidden" name="event_id" value="<?= e((string) $event['id']) ?>">
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

    <form class="content-form" method="post" action="<?= e(admin_events_url($isEditing ? ['edit' => (int) $form['id']] : [])) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_event">
      <input type="hidden" name="event_id" value="<?= e((string) ($form['id'] ?? '')) ?>">

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow"><?= $isEditing ? 'Edit' : 'New' ?></p>
            <h2><?= $isEditing ? 'Edit Event' : 'Create Event' ?></h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="event_title">Title</label>
            <input id="event_title" name="title" value="<?= e($form['title']) ?>" maxlength="180" required>
          </div>
          <div class="field">
            <label for="event_meta">Meta label</label>
            <input id="event_meta" name="meta_label" value="<?= e($form['meta_label']) ?>" maxlength="120" placeholder="Workshop · 2026">
            <p class="hint">Shown on the card. Leave blank to show the date instead.</p>
          </div>
          <div class="field">
            <label for="event_status">Status</label>
            <select id="event_status" name="status">
              <?php foreach (event_admin_status_options() as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $form['status'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="event_sort">Sort order</label>
            <input id="event_sort" type="number" name="sort_order" value="<?= e((string) $form['sort_order']) ?>" step="1">
          </div>
          <div class="field">
            <label for="event_published_at">Published at</label>
            <input id="event_published_at" type="datetime-local" name="published_at" value="<?= e($form['published_at']) ?>">
            <p class="hint">Published events get the current time automatically if this is empty.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">When &amp; Where</p>
            <h2>Event Details</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="event_date">Event date</label>
            <input id="event_date" type="date" name="event_date" value="<?= e($form['event_date']) ?>">
            <p class="hint">Determines whether the event shows under Upcoming or Past. Leave blank to keep it in Upcoming.</p>
          </div>
          <div class="field">
            <label for="event_time_label">Time</label>
            <input id="event_time_label" name="event_time_label" value="<?= e($form['event_time_label']) ?>" maxlength="60" placeholder="10:00 AM IST">
          </div>
          <div class="field">
            <label for="event_location">Location</label>
            <input id="event_location" name="location" value="<?= e($form['location']) ?>" maxlength="180" placeholder="New Delhi, India or Online">
          </div>
          <div class="field full">
            <label for="event_description">Description</label>
            <textarea id="event_description" name="description" rows="4" placeholder="Shown on the event card."><?= e($form['description']) ?></textarea>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Media</p>
            <h2>Video &amp; Poster</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="event_video_source">Video source</label>
            <select id="event_video_source" name="video_source_type">
              <?php foreach (event_admin_source_options() as $value => $label): ?>
                <option value="<?= e($value) ?>"<?= $form['video_source_type'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="event_video_url">Video link</label>
            <input id="event_video_url" name="video_url" value="<?= e($form['video_url']) ?>" maxlength="500" placeholder="https://youtube.com/watch?v=...">
            <p class="hint">YouTube, Vimeo, or a direct video URL.</p>
          </div>
          <div class="field">
            <label for="event_video_path">Current uploaded video path</label>
            <input id="event_video_path" name="video_path" value="<?= e($form['video_path']) ?>" maxlength="500" placeholder="uploads/events/videos/file.mp4">
          </div>
          <div class="field media-field">
            <label for="event_video_upload">Upload video</label>
            <input id="event_video_upload" type="file" name="video_upload" accept="video/mp4,video/webm,video/ogg,video/quicktime">
            <p class="hint">MP4, WebM, OGG, or MOV. Max 64 MB.</p>
          </div>
          <div class="field media-field">
            <label for="event_poster_image">Current poster path</label>
            <input id="event_poster_image" name="poster_image" value="<?= e($form['poster_image']) ?>" maxlength="500" placeholder="uploads/events/poster.jpg">
            <?php if ($form['poster_image'] !== ''): ?>
              <img src="<?= e(asset($form['poster_image'])) ?>" alt="">
            <?php endif; ?>
          </div>
          <div class="field media-field">
            <label for="event_poster_upload">Upload poster image</label>
            <input id="event_poster_upload" type="file" name="poster_upload" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG, or WebP. Max 5 MB.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Links</p>
            <h2>Registration</h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="event_registration_label">Link label</label>
            <input id="event_registration_label" name="registration_label" value="<?= e($form['registration_label']) ?>" maxlength="120" placeholder="Register now">
          </div>
          <div class="field">
            <label for="event_registration_url">Link URL</label>
            <input id="event_registration_url" name="registration_url" value="<?= e($form['registration_url']) ?>" maxlength="500" placeholder="https://...">
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit"><?= $isEditing ? 'Save Event' : 'Create Event' ?></button>
        <a class="ghost-link" href="<?= e(admin_events_url()) ?>">Reset Form</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-events-page';
require APP_PATH . '/views/admin/layout.php';
