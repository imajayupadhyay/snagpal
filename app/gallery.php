<?php

declare(strict_types=1);

/**
 * Shared photo-gallery model for cohorts and events.
 *
 * A cohort/event may have a video, a photo gallery, both, or neither. Photos
 * are stored one row per image in `gallery_photos`, keyed by (owner_type,
 * owner_id), and the files themselves live in a per-owner folder under
 * public/uploads/<owner>/gallery/<id>/ so a deploy never has to touch them.
 */

function gallery_owner_types(): array
{
    return ['cohort', 'event'];
}

function gallery_owner_is_valid(string $ownerType): bool
{
    return in_array($ownerType, gallery_owner_types(), true);
}

/**
 * Upload folder (relative to public/) that holds one owner's photos.
 */
function gallery_owner_directory(string $ownerType, int $ownerId): string
{
    $base = $ownerType === 'event' ? 'uploads/events/gallery' : 'uploads/cohorts/gallery';

    return $base . '/' . $ownerId;
}

/**
 * How many photos a single cohort/event may hold.
 */
function gallery_max_photos(): int
{
    return 60;
}

/**
 * How many files one upload submit may carry. PHP's own max_file_uploads is
 * usually 20, so anything above that is silently dropped by the runtime.
 */
function gallery_max_upload_batch(): int
{
    return min(20, (int) ini_get('max_file_uploads') ?: 20);
}

function gallery_normalize(array $row): array
{
    return [
        'id' => isset($row['id']) ? (int) $row['id'] : null,
        'owner_type' => (string) ($row['owner_type'] ?? ''),
        'owner_id' => (int) ($row['owner_id'] ?? 0),
        'image_path' => (string) ($row['image_path'] ?? ''),
        'caption' => (string) ($row['caption'] ?? ''),
        'alt_text' => (string) ($row['alt_text'] ?? ''),
        'sort_order' => (int) ($row['sort_order'] ?? 0),
    ];
}

/**
 * Every photo for one owner, in display order. Returns [] when the table has
 * not been migrated yet so the rest of the site keeps working.
 */
function gallery_photos_for(string $ownerType, ?int $ownerId): array
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId === null || $ownerId <= 0) {
        return [];
    }

    try {
        $statement = db()->prepare(
            'SELECT * FROM gallery_photos
             WHERE owner_type = :owner_type AND owner_id = :owner_id
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['owner_type' => $ownerType, 'owner_id' => $ownerId]);

        return array_map('gallery_normalize', $statement->fetchAll());
    } catch (Throwable) {
        return [];
    }
}

/**
 * Batch-load photos for many owners at once, keyed by owner id. Used by the
 * listing pages so a page of cards costs one query instead of one per card.
 *
 * @return array<int, array<int, array>>
 */
function gallery_photos_for_many(string $ownerType, array $ownerIds): array
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ownerIds), static fn (int $id): bool => $id > 0)));

    if (! gallery_owner_is_valid($ownerType) || $ids === []) {
        return [];
    }

    try {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = db()->prepare(
            'SELECT * FROM gallery_photos
             WHERE owner_type = ? AND owner_id IN (' . $placeholders . ')
             ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(array_merge([$ownerType], $ids));

        $grouped = [];

        foreach ($statement->fetchAll() as $row) {
            $photo = gallery_normalize($row);
            $grouped[$photo['owner_id']][] = $photo;
        }

        return $grouped;
    } catch (Throwable) {
        return [];
    }
}

/**
 * Attach a public-shaped `gallery` array to each item of a public feed.
 * Items without a database id (static fallback content) simply get [].
 */
function gallery_attach(string $ownerType, array $items): array
{
    $ids = array_map(static fn (array $item): int => (int) ($item['id'] ?? 0), $items);
    $grouped = gallery_photos_for_many($ownerType, $ids);

    foreach ($items as $index => $item) {
        $ownerId = (int) ($item['id'] ?? 0);
        $items[$index]['gallery'] = gallery_public_items($grouped[$ownerId] ?? []);
    }

    return $items;
}

/**
 * Map stored rows to the shape the frontend carousel renders.
 */
function gallery_public_items(array $photos): array
{
    $items = [];

    foreach ($photos as $photo) {
        $path = trim((string) ($photo['image_path'] ?? ''));

        if ($path === '') {
            continue;
        }

        $caption = trim((string) ($photo['caption'] ?? ''));
        $alt = trim((string) ($photo['alt_text'] ?? ''));

        $items[] = [
            'src' => asset($path),
            'caption' => $caption,
            'alt' => $alt !== '' ? $alt : $caption,
        ];
    }

    return $items;
}

function gallery_count(string $ownerType, ?int $ownerId): int
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId === null || $ownerId <= 0) {
        return 0;
    }

    try {
        $statement = db()->prepare(
            'SELECT COUNT(*) FROM gallery_photos WHERE owner_type = :owner_type AND owner_id = :owner_id'
        );
        $statement->execute(['owner_type' => $ownerType, 'owner_id' => $ownerId]);

        return (int) $statement->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

/**
 * Insert already-uploaded photo paths for an owner, appended after whatever is
 * already there. Returns how many rows were created.
 */
function gallery_add_photos(string $ownerType, int $ownerId, array $paths, ?int $adminId = null): int
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId <= 0 || $paths === []) {
        return 0;
    }

    $statement = db()->prepare(
        'INSERT INTO gallery_photos (owner_type, owner_id, image_path, sort_order, created_by, updated_by)
         VALUES (:owner_type, :owner_id, :image_path, :sort_order, :created_by, :updated_by)'
    );

    $next = gallery_next_sort_order($ownerType, $ownerId);
    $added = 0;

    foreach ($paths as $path) {
        $path = trim((string) $path);

        if ($path === '') {
            continue;
        }

        $statement->execute([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'image_path' => $path,
            'sort_order' => $next,
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $next++;
        $added++;
    }

    return $added;
}

function gallery_next_sort_order(string $ownerType, int $ownerId): int
{
    try {
        $statement = db()->prepare(
            'SELECT COALESCE(MAX(sort_order), -1) + 1 FROM gallery_photos
             WHERE owner_type = :owner_type AND owner_id = :owner_id'
        );
        $statement->execute(['owner_type' => $ownerType, 'owner_id' => $ownerId]);

        return (int) $statement->fetchColumn();
    } catch (Throwable) {
        return 0;
    }
}

/**
 * Apply the caption/alt/order edits and the delete checkboxes posted by the
 * admin gallery panel. Only rows that belong to this owner are touched.
 */
function gallery_save_from_post(string $ownerType, int $ownerId, array $post, ?int $adminId, array &$errors): void
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId <= 0) {
        return;
    }

    $existing = gallery_photos_for($ownerType, $ownerId);

    if ($existing === []) {
        return;
    }

    $owned = [];

    foreach ($existing as $photo) {
        $owned[(int) $photo['id']] = $photo;
    }

    $removeIds = array_map('intval', array_values(array_filter((array) ($post['gallery_remove'] ?? []), 'is_scalar')));
    $captions = is_array($post['gallery_caption'] ?? null) ? $post['gallery_caption'] : [];
    $alts = is_array($post['gallery_alt'] ?? null) ? $post['gallery_alt'] : [];
    $orders = is_array($post['gallery_order'] ?? null) ? $post['gallery_order'] : [];

    foreach ($removeIds as $removeId) {
        if (isset($owned[$removeId])) {
            gallery_delete_photo($ownerType, $ownerId, $removeId, $errors);
            unset($owned[$removeId]);
        }
    }

    if ($owned === []) {
        return;
    }

    $statement = db()->prepare(
        'UPDATE gallery_photos
         SET caption = :caption, alt_text = :alt_text, sort_order = :sort_order, updated_by = :updated_by
         WHERE id = :id AND owner_type = :owner_type AND owner_id = :owner_id'
    );

    foreach ($owned as $id => $photo) {
        $caption = homepage_text($captions[$id] ?? $photo['caption']);
        $alt = homepage_text($alts[$id] ?? $photo['alt_text']);
        $order = array_key_exists($id, $orders) ? (int) $orders[$id] : (int) $photo['sort_order'];

        if ($caption === $photo['caption'] && $alt === $photo['alt_text'] && $order === (int) $photo['sort_order']) {
            continue;
        }

        $statement->execute([
            'caption' => $caption !== '' ? mb_substr($caption, 0, 255) : null,
            'alt_text' => $alt !== '' ? mb_substr($alt, 0, 255) : null,
            'sort_order' => $order,
            'updated_by' => $adminId,
            'id' => $id,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);
    }
}

/**
 * Delete one photo (row + file). The file is only removed once the row is gone,
 * and only when it sits inside this owner's own upload folder.
 */
function gallery_delete_photo(string $ownerType, int $ownerId, int $photoId, array &$errors): bool
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId <= 0 || $photoId <= 0) {
        return false;
    }

    try {
        $lookup = db()->prepare(
            'SELECT image_path FROM gallery_photos
             WHERE id = :id AND owner_type = :owner_type AND owner_id = :owner_id LIMIT 1'
        );
        $lookup->execute(['id' => $photoId, 'owner_type' => $ownerType, 'owner_id' => $ownerId]);
        $path = (string) ($lookup->fetchColumn() ?: '');

        $delete = db()->prepare(
            'DELETE FROM gallery_photos WHERE id = :id AND owner_type = :owner_type AND owner_id = :owner_id LIMIT 1'
        );
        $delete->execute(['id' => $photoId, 'owner_type' => $ownerType, 'owner_id' => $ownerId]);

        if ($delete->rowCount() > 0) {
            gallery_unlink_file($path);

            return true;
        }
    } catch (Throwable) {
        $errors[] = 'One of the gallery photos could not be removed.';
    }

    return false;
}

/**
 * Remove every photo belonging to an owner, plus its upload folder. Called when
 * the cohort/event itself is deleted so no orphan files are left behind.
 */
function gallery_delete_for_owner(string $ownerType, int $ownerId): void
{
    if (! gallery_owner_is_valid($ownerType) || $ownerId <= 0) {
        return;
    }

    try {
        $statement = db()->prepare(
            'SELECT image_path FROM gallery_photos WHERE owner_type = :owner_type AND owner_id = :owner_id'
        );
        $statement->execute(['owner_type' => $ownerType, 'owner_id' => $ownerId]);
        $paths = $statement->fetchAll(PDO::FETCH_COLUMN);

        $delete = db()->prepare('DELETE FROM gallery_photos WHERE owner_type = :owner_type AND owner_id = :owner_id');
        $delete->execute(['owner_type' => $ownerType, 'owner_id' => $ownerId]);

        foreach ($paths as $path) {
            gallery_unlink_file((string) $path);
        }
    } catch (Throwable) {
        return;
    }

    $directory = PUBLIC_PATH . '/' . gallery_owner_directory($ownerType, $ownerId);

    if (is_dir($directory) && (glob($directory . '/*') ?: []) === []) {
        @rmdir($directory);
    }
}

/**
 * Delete an uploaded file, but only when it really sits inside public/uploads —
 * never follow a path that escapes the uploads tree.
 */
function gallery_unlink_file(string $path): void
{
    $path = trim($path);

    if ($path === '' || ! str_starts_with($path, 'uploads/') || str_contains($path, '..')) {
        return;
    }

    $full = PUBLIC_PATH . '/' . $path;
    $real = realpath($full);
    $uploadsRoot = realpath(PUBLIC_PATH . '/uploads');

    if ($real === false || $uploadsRoot === false || ! str_starts_with($real, $uploadsRoot . DIRECTORY_SEPARATOR)) {
        return;
    }

    if (is_file($real)) {
        @unlink($real);
    }
}

/**
 * Build the click-through photo carousel used on cards and detail pages.
 *
 * Layout is CSS scroll-snap, so touch swiping works with no JavaScript at all;
 * main.js only layers on the arrows, dots, counter, and lightbox.
 */
function gallery_carousel_html(array $photos, string $title = '', string $variant = 'card'): string
{
    $photos = array_values(array_filter($photos, 'is_array'));

    if ($photos === []) {
        return '';
    }

    $label = $title !== '' ? $title : 'Photo gallery';
    $total = count($photos);
    $slides = '';

    foreach ($photos as $index => $photo) {
        $src = trim((string) ($photo['src'] ?? ''));

        if ($src === '') {
            continue;
        }

        $caption = trim((string) ($photo['caption'] ?? ''));
        $alt = trim((string) ($photo['alt'] ?? ''));

        if ($alt === '') {
            $alt = $label . ' photo ' . ($index + 1) . ' of ' . $total;
        }

        $slides .= '<figure class="gal-slide" data-gal-slide="' . e((string) $index) . '">'
            . '<img class="gal-image" src="' . e($src) . '" alt="' . e($alt) . '"'
            . ' loading="' . ($index === 0 ? 'eager' : 'lazy') . '" decoding="async"'
            . ' data-gal-caption="' . e($caption) . '">'
            . ($caption !== '' ? '<figcaption class="gal-caption">' . e($caption) . '</figcaption>' : '')
            . '</figure>';
    }

    if ($slides === '') {
        return '';
    }

    $controls = '';

    if ($total > 1) {
        $dots = '';

        for ($i = 0; $i < $total; $i++) {
            $dots .= '<button type="button" class="gal-dot' . ($i === 0 ? ' on' : '') . '" data-gal-dot="' . $i . '"'
                . ' aria-label="Go to photo ' . ($i + 1) . '"></button>';
        }

        $controls = '<button type="button" class="gal-nav gal-prev" data-gal="prev" aria-label="Previous photo">'
            . '<span aria-hidden="true">&#8249;</span></button>'
            . '<button type="button" class="gal-nav gal-next" data-gal="next" aria-label="Next photo">'
            . '<span aria-hidden="true">&#8250;</span></button>'
            . '<div class="gal-dots" data-gal-dots>' . $dots . '</div>';
    }

    return '<div class="gal gal-' . e($variant) . '" data-gallery data-gal-title="' . e($label) . '" role="group"'
        . ' aria-roledescription="carousel" aria-label="' . e($label) . ' photo gallery">'
        . '<div class="gal-track" data-gal-track tabindex="0">' . $slides . '</div>'
        . $controls
        . '<span class="gal-count mono" data-gal-count>1 / ' . $total . '</span>'
        . '<span class="gal-badge mono">' . $total . ' ' . ($total === 1 ? 'photo' : 'photos') . '</span>'
        . '</div>';
}
