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

function recommendation_submit(array $post): array
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

    $old = [
        'name' => $name,
        'designation' => $designation,
        'email' => $email,
        'quote' => $quote,
    ];

    if ($errors !== []) {
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    try {
        $statement = db()->prepare(
            'INSERT INTO recommendation_submissions (name, designation, email, quote, status)
             VALUES (:name, :designation, :email, :quote, "pending")'
        );
        $statement->execute([
            'name' => $name,
            'designation' => $designation,
            'email' => $email,
            'quote' => $quote,
        ]);
        $submissionId = (int) db()->lastInsertId();
    } catch (Throwable) {
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
    ];

    $addedToHomepage = (int) $submission['added_to_homepage'];
    $addedToAbout = (int) $submission['added_to_about'];

    if ($target === 'homepage' || $target === 'both') {
        $homepage = homepage_content();
        $homepage['recommendations'][] = $entry;
        homepage_save_content($homepage, $adminId);
        $addedToHomepage = 1;
    }

    if ($target === 'about' || $target === 'both') {
        $about = about_page_content();
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

    $statement = db()->prepare(
        'UPDATE recommendation_submissions
         SET status = "rejected", reviewed_by = :reviewed_by, reviewed_at = NOW()
         WHERE id = :id'
    );
    $statement->execute(['reviewed_by' => $adminId, 'id' => $id]);

    return $statement->rowCount() > 0 ? [] : ['That submission no longer exists.'];
}

function recommendation_admin_delete(int $id): array
{
    if ($id <= 0) {
        return ['Invalid submission selected.'];
    }

    $statement = db()->prepare('DELETE FROM recommendation_submissions WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0 ? [] : ['That submission no longer exists.'];
}
