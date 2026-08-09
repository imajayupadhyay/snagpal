<?php
/**
 * Shared photo-gallery panel for the Cohorts and Events editors.
 *
 * Expects:
 *   $ownerLabel  "cohort" | "event"  (used in copy only)
 *   $ownerId     int|null            (null while creating — uploads still work,
 *                                     they are stored right after the insert)
 *   $photos      array               rows from gallery_photos_for()
 */
$ownerLabel = $ownerLabel ?? 'entry';
$ownerId = isset($ownerId) && (int) $ownerId > 0 ? (int) $ownerId : null;
$photos = is_array($photos ?? null) ? $photos : [];
$folder = $ownerId !== null ? gallery_owner_directory($ownerLabel === 'event' ? 'event' : 'cohort', $ownerId) : '';
?>
<section class="panel form-panel">
  <div class="panel-head">
    <div>
      <p class="eyebrow">Media</p>
      <h2>Photo Gallery</h2>
    </div>
    <span class="muted"><?= e((string) count($photos)) ?> photo<?= count($photos) === 1 ? '' : 's' ?></span>
  </div>
  <p class="hint">
    Optional. Use this when the <?= e($ownerLabel) ?> has photos instead of (or as well as) a video.
    When there is no video, the public card shows these photos as a swipeable carousel;
    visitors can click any photo to open it full screen.
    Up to <?= e((string) gallery_max_photos()) ?> photos, JPG/PNG/WebP, 5 MB each.
  </p>

  <div class="form-grid">
    <div class="field full media-field">
      <label for="<?= e($ownerLabel) ?>_gallery_upload">Add photos</label>
      <input id="<?= e($ownerLabel) ?>_gallery_upload" type="file" name="gallery_upload[]" accept="image/jpeg,image/png,image/webp" multiple>
      <p class="hint">
        Select several files at once (up to <?= e((string) gallery_max_upload_batch()) ?> per save).
        <?php if ($ownerId === null): ?>
          They are stored as soon as this <?= e($ownerLabel) ?> is created.
        <?php else: ?>
          Stored in <code><?= e($folder) ?>/</code>, which deployments never overwrite.
        <?php endif; ?>
      </p>
    </div>
  </div>

  <?php if ($photos === []): ?>
    <p class="empty-state">No photos yet. Add some above and save to build the gallery.</p>
  <?php else: ?>
    <div class="gallery-admin-grid">
      <?php foreach ($photos as $index => $photo): ?>
        <?php $id = (int) $photo['id']; ?>
        <article class="gallery-admin-item">
          <img src="<?= e(asset($photo['image_path'])) ?>" alt="" loading="lazy">
          <div class="gallery-admin-fields">
            <div class="field">
              <label for="gallery_caption_<?= e((string) $id) ?>">Caption</label>
              <input id="gallery_caption_<?= e((string) $id) ?>" name="gallery_caption[<?= e((string) $id) ?>]" value="<?= e($photo['caption']) ?>" maxlength="255" placeholder="Shown under the photo">
            </div>
            <div class="field">
              <label for="gallery_alt_<?= e((string) $id) ?>">Alt text</label>
              <input id="gallery_alt_<?= e((string) $id) ?>" name="gallery_alt[<?= e((string) $id) ?>]" value="<?= e($photo['alt_text']) ?>" maxlength="255" placeholder="Describes the photo for screen readers">
            </div>
            <div class="gallery-admin-row">
              <div class="field">
                <label for="gallery_order_<?= e((string) $id) ?>">Order</label>
                <input id="gallery_order_<?= e((string) $id) ?>" type="number" step="1" name="gallery_order[<?= e((string) $id) ?>]" value="<?= e((string) $photo['sort_order']) ?>">
              </div>
              <label class="check-tile gallery-admin-remove">
                <input type="checkbox" name="gallery_remove[]" value="<?= e((string) $id) ?>">
                <span>Remove</span>
              </label>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="hint">Lower order numbers appear first. Ticking "Remove" deletes the photo and its file when you save.</p>
  <?php endif; ?>
</section>
