<?php

declare(strict_types=1);

function recommendation_safe_referer_path(): string
{
    $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
    $path = trim((string) (parse_url($referer, PHP_URL_PATH) ?? ''), '/');
    $allowed = [
        trim(url_path(), '/'),
        trim(url_path('about-shweta/'), '/'),
    ];

    return in_array($path, $allowed, true) ? url_path($path) : url_path();
}

function recommendation_clean_text(mixed $value): string
{
    return trim(preg_replace('/\s+/', ' ', (string) $value) ?? '');
}

function recommendation_clean_multiline(mixed $value): string
{
    $value = trim(str_replace(["\r\n", "\r"], "\n", (string) $value));

    return preg_replace('/\n{3,}/', "\n\n", $value) ?? $value;
}

function recommendation_normalize_social_url(mixed $value): string
{
    $url = trim((string) $value);

    if ($url === '') {
        return '';
    }

    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) === 1 && ! preg_match('#^https?://#i', $url)) {
        return $url;
    }

    if (! preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    return $url;
}

function recommendation_social_url_error(string $url): ?string
{
    if ($url === '') {
        return null;
    }

    if (mb_strlen($url) > 500 || preg_match('/[\x00-\x1F\x7F\s]/', $url) === 1) {
        return 'Please enter a valid social media link.';
    }

    $parts = parse_url($url);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = (string) ($parts['host'] ?? '');

    if (! in_array($scheme, ['http', 'https'], true) || $host === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
        return 'Please enter a valid social media link.';
    }

    return null;
}

function recommendation_public_social_url(mixed $value): string
{
    $url = recommendation_normalize_social_url($value);

    return recommendation_social_url_error($url) === null ? $url : '';
}

/**
 * Keys carried on a published recommendation entry that are not edited as plain
 * text columns in the page editors. They must survive an editor save, otherwise
 * the link back to the submission (and the photo/social link) is lost.
 */
function recommendation_entry_meta_fields(): array
{
    return ['src_id', 'photo', 'social_url'];
}

function recommendation_entry_matches(array $entry, int $id, ?array $submission, bool $allowTextMatch): bool
{
    if (isset($entry['src_id']) && $entry['src_id'] !== '') {
        return (int) $entry['src_id'] === $id;
    }

    if (! $allowTextMatch || $submission === null) {
        return false;
    }

    // Entries published before src_id existed carry no link, so fall back to an
    // exact quote + attribution match against the submission they came from.
    $name = (string) ($submission['name'] ?? '');
    $designation = (string) ($submission['designation'] ?? '');
    $who = trim($name . ($designation !== '' ? ', ' . $designation : ''));

    return recommendation_clean_multiline($entry['q'] ?? '') === recommendation_clean_multiline($submission['quote'] ?? '')
        && recommendation_clean_text($entry['w'] ?? '') === recommendation_clean_text($who);
}

function recommendation_reject_entries(array $entries, int $id, ?array $submission, bool $allowTextMatch): array
{
    return array_values(array_filter($entries, static function (mixed $entry) use ($id, $submission, $allowTextMatch): bool {
        return ! is_array($entry) || ! recommendation_entry_matches($entry, $id, $submission, $allowTextMatch);
    }));
}

/**
 * Publishing copies a submission into the homepage / About page content, so
 * removing the submission must also retract those copies. Without this the
 * quote keeps rendering after the submission row is gone.
 */
function recommendation_remove_published(int $id, ?array $submission = null, ?int $adminId = null): void
{
    if ($id <= 0) {
        return;
    }

    $allowHomepageTextMatch = $submission !== null && (int) ($submission['added_to_homepage'] ?? 0) === 1;
    $allowAboutTextMatch = $submission !== null && (int) ($submission['added_to_about'] ?? 0) === 1;

    try {
        $homepage = homepage_content();
        $current = is_array($homepage['recommendations'] ?? null) ? $homepage['recommendations'] : [];
        $filtered = recommendation_reject_entries($current, $id, $submission, $allowHomepageTextMatch);

        if (count($filtered) !== count($current)) {
            $homepage['recommendations'] = $filtered;
            homepage_save_content($homepage, $adminId);
        }
    } catch (Throwable) {
        // A failure here must not block the delete/reject itself.
    }

    try {
        $about = about_page_content();
        $current = is_array($about['recommendations'] ?? null) ? $about['recommendations'] : [];
        $filtered = recommendation_reject_entries($current, $id, $submission, $allowAboutTextMatch);

        if (count($filtered) !== count($current)) {
            $about['recommendations'] = $filtered;
            about_page_save_content($about, $adminId);
        }
    } catch (Throwable) {
        // As above.
    }
}

function recommendation_frontend_items(array $items): array
{
    return array_map(static function (array $item): array {
        $photo = trim((string) ($item['photo'] ?? ''));

        if ($photo !== '' && empty($item['photo_url'])) {
            $item['photo_url'] = asset($photo);
        }

        // Internal bookkeeping only — no reason to publish it to the page.
        unset($item['src_id']);

        return $item;
    }, $items);
}

function recommendation_submit(array $post, array $files = []): array
{
    $errors = [];

    if (! verify_public_csrf_token($post['_token'] ?? null)) {
        $errors[] = 'Your session expired. Please reopen the form and try again.';
    }

    if (
        trim((string) ($post['recommendation_reference'] ?? '')) !== ''
        || trim((string) ($post['website'] ?? '')) !== ''
    ) {
        $errors[] = 'Unable to submit this recommendation.';
    }

    $name = recommendation_clean_text($post['name'] ?? '');
    $designation = recommendation_clean_text($post['designation'] ?? '');
    $email = strtolower(recommendation_clean_text($post['email'] ?? ''));
    $quote = recommendation_clean_multiline($post['quote'] ?? '');
    $socialUrl = recommendation_normalize_social_url($post['social_url'] ?? '');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 160) {
        $errors[] = 'Please enter your name.';
    }

    if (mb_strlen($designation) < 2 || mb_strlen($designation) > 200) {
        $errors[] = 'Please enter your designation or organisation.';
    }

    if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (mb_strlen($quote) < 10) {
        $errors[] = 'Please share a few words for your recommendation.';
    } elseif (mb_strlen($quote) > 600) {
        $errors[] = 'Please keep your recommendation under 600 characters.';
    }

    $socialError = recommendation_social_url_error($socialUrl);

    if ($socialError !== null) {
        $errors[] = $socialError;
    }

    $old = [
        'name' => $name,
        'designation' => $designation,
        'email' => $email,
        'quote' => $quote,
        'social_url' => $socialUrl,
    ];

    if ($errors !== []) {
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    $photoPath = '';

    if ($files !== []) {
        require_once APP_PATH . '/upload.php';
        $photoPath = upload_recommendation_photo('photo', $files, $errors);

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors, 'old' => $old];
        }
    }

    try {
        $statement = db()->prepare(
            'INSERT INTO recommendation_submissions (name, designation, email, quote, photo_path, social_url, status)
             VALUES (:name, :designation, :email, :quote, :photo_path, :social_url, "pending")'
        );
        $statement->execute([
            'name' => $name,
            'designation' => $designation,
            'email' => $email,
            'quote' => $quote,
            'photo_path' => $photoPath !== '' ? $photoPath : null,
            'social_url' => $socialUrl !== '' ? $socialUrl : null,
        ]);
        $submissionId = (int) db()->lastInsertId();
    } catch (Throwable) {
        gallery_unlink_file($photoPath);

        return ['ok' => false, 'errors' => ['Unable to submit your recommendation right now. Please try again later.'], 'old' => $old];
    }

    admin_notification_create([
        'type' => 'recommendation_pending',
        'severity' => 'info',
        'title' => 'New recommendation submitted',
        'body' => $name . ' submitted a recommendation for review.',
        'action_label' => 'Review submission',
        'action_url' => url_path('sanchalak/recommendations/'),
        'source_type' => 'recommendation_submission',
        'source_id' => $submissionId,
    ]);

    return [
        'ok' => true,
        'message' => 'Thank you. Your recommendation has been submitted and will appear once it is reviewed.',
    ];
}

function recommendation_admin_status_options(): array
{
    return [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];
}

function recommendation_admin_all(): array
{
    return db()->query(
        'SELECT * FROM recommendation_submissions ORDER BY status = "pending" DESC, created_at DESC'
    )->fetchAll();
}

function recommendation_admin_counts(): array
{
    $counts = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

    $rows = db()->query('SELECT status, COUNT(*) AS total FROM recommendation_submissions GROUP BY status')->fetchAll();

    foreach ($rows as $row) {
        $status = (string) ($row['status'] ?? '');
        $total = (int) ($row['total'] ?? 0);
        $counts['total'] += $total;

        if (isset($counts[$status])) {
            $counts[$status] = $total;
        }
    }

    return $counts;
}

function recommendation_admin_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM recommendation_submissions WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $row = $statement->fetch();

    return is_array($row) ? $row : null;
}

function recommendation_admin_publish(int $id, string $target, int $adminId): array
{
    $submission = recommendation_admin_find($id);

    if ($submission === null) {
        return ['That submission no longer exists.'];
    }

    if (! in_array($target, ['homepage', 'about', 'both'], true)) {
        return ['Choose where to publish this recommendation.'];
    }

    $name = (string) $submission['name'];
    $designation = (string) $submission['designation'];
    $entry = [
        'q' => (string) $submission['quote'],
        'w' => trim($name . ($designation !== '' ? ', ' . $designation : '')),
        // Links the published copy back to its submission so deleting or
        // rejecting the submission can retract it again.
        'src_id' => $id,
    ];
    $photoPath = trim((string) ($submission['photo_path'] ?? ''));
    $socialUrl = recommendation_public_social_url($submission['social_url'] ?? '');

    if ($photoPath !== '') {
        $entry['photo'] = $photoPath;
    }

    if ($socialUrl !== '') {
        $entry['social_url'] = $socialUrl;
    }

    $addedToHomepage = (int) $submission['added_to_homepage'];
    $addedToAbout = (int) $submission['added_to_about'];

    if ($target === 'homepage' || $target === 'both') {
        $homepage = homepage_content();
        $current = is_array($homepage['recommendations'] ?? null) ? $homepage['recommendations'] : [];
        // Drop any earlier copy of this submission so re-publishing updates it
        // in place instead of appending a duplicate.
        $homepage['recommendations'] = recommendation_reject_entries($current, $id, $submission, $addedToHomepage === 1);
        $homepage['recommendations'][] = $entry;
        homepage_save_content($homepage, $adminId);
        $addedToHomepage = 1;
    }

    if ($target === 'about' || $target === 'both') {
        $about = about_page_content();
        $current = is_array($about['recommendations'] ?? null) ? $about['recommendations'] : [];
        $about['recommendations'] = recommendation_reject_entries($current, $id, $submission, $addedToAbout === 1);
        $about['recommendations'][] = $entry;
        about_page_save_content($about, $adminId);
        $addedToAbout = 1;
    }

    $statement = db()->prepare(
        'UPDATE recommendation_submissions
         SET status = "approved", added_to_homepage = :added_to_homepage, added_to_about = :added_to_about,
             reviewed_by = :reviewed_by, reviewed_at = NOW()
         WHERE id = :id'
    );
    $statement->execute([
        'added_to_homepage' => $addedToHomepage,
        'added_to_about' => $addedToAbout,
        'reviewed_by' => $adminId,
        'id' => $id,
    ]);

    return [];
}

function recommendation_admin_reject(int $id, int $adminId): array
{
    if ($id <= 0) {
        return ['Invalid submission selected.'];
    }

    $submission = recommendation_admin_find($id);

    $statement = db()->prepare(
        'UPDATE recommendation_submissions
         SET status = "rejected", added_to_homepage = 0, added_to_about = 0,
             reviewed_by = :reviewed_by, reviewed_at = NOW()
         WHERE id = :id'
    );
    $statement->execute(['reviewed_by' => $adminId, 'id' => $id]);

    if ($statement->rowCount() === 0) {
        return ['That submission no longer exists.'];
    }

    // Rejecting an already-approved recommendation must also pull it off the
    // public pages it was published to.
    recommendation_remove_published($id, $submission, $adminId);

    return [];
}

function recommendation_admin_delete(int $id, ?int $adminId = null): array
{
    if ($id <= 0) {
        return ['Invalid submission selected.'];
    }

    // Read before deleting: the legacy text-match fallback needs the row.
    $submission = recommendation_admin_find($id);

    $statement = db()->prepare('DELETE FROM recommendation_submissions WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);

    if ($statement->rowCount() === 0) {
        return ['That submission no longer exists.'];
    }

    recommendation_remove_published($id, $submission, $adminId);

    return [];
}
