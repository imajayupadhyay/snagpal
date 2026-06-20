<?php

declare(strict_types=1);

function awards_page_default_content(): array
{
    return [
        'kicker' => 'Awards · Recognitions',
        'heading_line1' => 'Awards',
        'heading_line2' => 'Recognitions',
        'hero_role' => 'Nodal Officer - AI Governance - Bhakra Beas Management Board, Ministry of Power, Government of India',
        'hero_lede' => 'A curated record of recognitions, institutional mandates, and public-sector engagements connected to AI governance, critical infrastructure technology, and responsible digital transformation.',
        'hero_card_eyebrow' => 'Recognition Ledger',
        'hero_card_title' => 'Verified milestones and public engagements',
        'hero_card_description' => 'Formal awards can be added here with issuing body, date, citation, and source links.',
        'hero_card_image_src' => 'images/research-felicitation.jpg',
        'hero_card_image_alt' => 'Shweta Nagpal being felicitated on stage at the AI Conference and Workshop, Punjab School Education Board',
        'hero_stats' => [
            ['value' => '2026', 'label' => 'Recent public AI engagement'],
            ['value' => 'BBMB', 'label' => 'Institutional AI governance mandate'],
            ['value' => 'IndiaAI', 'label' => 'Mission and MeitY guideline alignment'],
            ['value' => 'Public', 'label' => 'Trust, auditability, and responsible adoption'],
        ],
        'ledger_heading' => 'Awards & Recognitions',
        'recognitions' => [
            [
                'year' => '2026',
                'type' => 'Felicitation',
                'title' => 'AI Conference & Workshop - Punjab School Education Board',
                'description' => 'Recognised during a public-sector AI conference and workshop focused on responsible adoption of artificial intelligence in institutions.',
                'image' => 'images/research-felicitation.jpg',
                'image_alt' => 'Shweta Nagpal being felicitated on stage at the AI Conference and Workshop, Punjab School Education Board',
            ],
            [
                'year' => '2026',
                'type' => 'Institutional Mandate',
                'title' => 'Nodal Officer for AI Governance, BBMB',
                'description' => 'Designated to lead BBMB alignment with the IndiaAI Mission and MeitY AI Governance Guidelines for accountable public-sector AI adoption.',
                'image' => '',
                'image_alt' => '',
            ],
            [
                'year' => '2026',
                'type' => 'Engagement',
                'title' => 'BBMB - 50 Years (1976-2026)',
                'description' => 'Participation in the Board\'s golden-jubilee programme on technology and the future of public infrastructure.',
                'image' => 'images/profile-commemoration.jpg',
                'image_alt' => 'Shweta Nagpal at the Bhakra Beas Management Board 50-year commemoration',
            ],
            [
                'year' => 'Ongoing',
                'type' => 'Public-Sector Technology',
                'title' => 'Critical Infrastructure Systems Responsibility',
                'description' => 'Nearly a decade of work across government and public-sector undertakings, supporting secure software, data, procurement, and e-governance systems.',
                'image' => '',
                'image_alt' => '',
            ],
        ],
        'standards_eyebrow' => 'Editorial Standard',
        'standards_heading' => 'Recognition should be precise, sourced, and useful.',
        'standards_intro' => 'This page is designed to grow into a verified record. Placeholder endorsements and unsourced claims should not be treated as awards.',
        'standards' => [
            [
                'title' => 'Verified before published',
                'description' => 'Formal awards, citations, and external recognitions should be added with source links, dates, and issuing institutions.',
            ],
            [
                'title' => 'Institutional context matters',
                'description' => 'Recognitions are presented with the public-sector mandate behind them, not as isolated badges without context.',
            ],
            [
                'title' => 'Public trust over volume',
                'description' => 'The page prioritises accurate, auditable recognition records over a long list of unverified claims.',
            ],
        ],
        'schedule_eyebrow' => 'Schedule a Meet',
        'schedule_heading' => 'Let\'s talk.',
        'schedule_description' => 'For advisory conversations, speaking engagements, and collaboration on AI governance and public technology.',
    ];
}

function awards_page_row_fields(): array
{
    return [
        'hero_stats' => ['value', 'label'],
        'recognitions' => ['year', 'type', 'title', 'description', 'image', 'image_alt'],
        'standards' => ['title', 'description'],
    ];
}

function awards_page_content(): array
{
    try {
        $statement = db()->prepare('SELECT content_json FROM site_contents WHERE content_key = :key LIMIT 1');
        $statement->execute(['key' => 'awards_page']);
        $row = $statement->fetch();

        if ($row) {
            $stored = json_decode((string) $row['content_json'], true, 512, JSON_THROW_ON_ERROR);

            if (is_array($stored)) {
                return awards_page_normalize($stored);
            }
        }

        $bootstrapped = awards_page_bootstrap_from_homepage();
        awards_page_save_content($bootstrapped, null);

        return $bootstrapped;
    } catch (Throwable) {
        return awards_page_default_content();
    }
}

function awards_page_bootstrap_from_homepage(): array
{
    $default = awards_page_default_content();

    try {
        $homepage = homepage_content();
    } catch (Throwable) {
        return $default;
    }

    $hero = is_array($homepage['hero'] ?? null) ? $homepage['hero'] : [];
    $profile = is_array($homepage['profile'] ?? null) ? $homepage['profile'] : [];
    $research = is_array($homepage['research'] ?? null) ? $homepage['research'] : [];
    $schedule = is_array($homepage['schedule'] ?? null) ? $homepage['schedule'] : [];
    $researchMedia = is_array($research['media'] ?? null) ? array_values($research['media']) : [];
    $heroImage = $researchMedia[0] ?? ($profile['image'] ?? ($hero['image'] ?? []));

    $seed = $default;
    $seed['hero_role'] = (string) ($hero['role'] ?? $default['hero_role']);
    $seed['hero_card_image_src'] = (string) ($heroImage['src'] ?? $default['hero_card_image_src']);
    $seed['hero_card_image_alt'] = (string) ($heroImage['alt'] ?? $default['hero_card_image_alt']);
    $seed['schedule_eyebrow'] = (string) ($schedule['eyebrow'] ?? $default['schedule_eyebrow']);
    $seed['schedule_heading'] = (string) ($schedule['heading'] ?? $default['schedule_heading']);
    $seed['schedule_description'] = (string) ($schedule['description'] ?? $default['schedule_description']);

    return awards_page_normalize($seed);
}

function awards_page_normalize(array $raw): array
{
    $default = awards_page_default_content();
    $merged = array_merge($default, $raw);

    foreach (awards_page_row_fields() as $key => $fields) {
        $merged[$key] = awards_page_normalize_rows($raw[$key] ?? null, $default[$key], $fields);
    }

    return $merged;
}

function awards_page_normalize_rows(mixed $rows, array $default, array $fields): array
{
    if (! is_array($rows) || $rows === []) {
        return $default;
    }

    $normalized = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $entry = [];

        foreach ($fields as $field) {
            $entry[$field] = $field === 'description'
                ? homepage_textarea($row[$field] ?? '')
                : homepage_text($row[$field] ?? '');
        }

        $normalized[] = $entry;
    }

    return $normalized === [] ? $default : $normalized;
}

function awards_page_content_from_post(array $post): array
{
    $default = awards_page_default_content();
    $rowsFromPost = [];

    foreach (awards_page_row_fields() as $key => $fields) {
        $rowsFromPost[$key] = awards_page_rows_from_post(
            is_array($post[$key] ?? null) ? $post[$key] : [],
            $fields,
            $default[$key]
        );
    }

    return [
        'kicker' => homepage_text($post['kicker'] ?? $default['kicker']),
        'heading_line1' => homepage_text($post['heading_line1'] ?? $default['heading_line1']),
        'heading_line2' => homepage_text($post['heading_line2'] ?? $default['heading_line2']),
        'hero_role' => homepage_text($post['hero_role'] ?? $default['hero_role']),
        'hero_lede' => homepage_textarea($post['hero_lede'] ?? $default['hero_lede']),
        'hero_card_eyebrow' => homepage_text($post['hero_card_eyebrow'] ?? $default['hero_card_eyebrow']),
        'hero_card_title' => homepage_text($post['hero_card_title'] ?? $default['hero_card_title']),
        'hero_card_description' => homepage_textarea($post['hero_card_description'] ?? $default['hero_card_description']),
        'hero_card_image_src' => homepage_text($post['hero_card_image_src'] ?? $default['hero_card_image_src']),
        'hero_card_image_alt' => homepage_text($post['hero_card_image_alt'] ?? $default['hero_card_image_alt']),
        'hero_stats' => $rowsFromPost['hero_stats'],
        'ledger_heading' => homepage_text($post['ledger_heading'] ?? $default['ledger_heading']),
        'recognitions' => $rowsFromPost['recognitions'],
        'standards_eyebrow' => homepage_text($post['standards_eyebrow'] ?? $default['standards_eyebrow']),
        'standards_heading' => homepage_text($post['standards_heading'] ?? $default['standards_heading']),
        'standards_intro' => homepage_textarea($post['standards_intro'] ?? $default['standards_intro']),
        'standards' => $rowsFromPost['standards'],
        'schedule_eyebrow' => homepage_text($post['schedule_eyebrow'] ?? $default['schedule_eyebrow']),
        'schedule_heading' => homepage_text($post['schedule_heading'] ?? $default['schedule_heading']),
        'schedule_description' => homepage_textarea($post['schedule_description'] ?? $default['schedule_description']),
    ];
}

function awards_page_rows_from_post(array $columns, array $fields, array $default): array
{
    $count = 0;

    foreach ($fields as $field) {
        if (isset($columns[$field]) && is_array($columns[$field])) {
            $count = max($count, count($columns[$field]));
        }
    }

    if ($count === 0) {
        return $default;
    }

    $rows = [];

    for ($i = 0; $i < $count; $i++) {
        $row = [];
        $hasContent = false;

        foreach ($fields as $field) {
            $value = $field === 'description'
                ? homepage_textarea($columns[$field][$i] ?? '')
                : homepage_text($columns[$field][$i] ?? '');
            $row[$field] = $value;

            if ($value !== '') {
                $hasContent = true;
            }
        }

        if ($hasContent) {
            $rows[] = $row;
        }
    }

    return $rows === [] ? $default : $rows;
}

function awards_page_save_content(array $content, ?int $adminId = null): void
{
    $json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    $statement = db()->prepare(
        'INSERT INTO site_contents (content_key, content_json, updated_by)
         VALUES (:content_key, :content_json, :updated_by)
         ON DUPLICATE KEY UPDATE
             content_json = VALUES(content_json),
             updated_by = VALUES(updated_by),
             updated_at = CURRENT_TIMESTAMP'
    );

    $statement->execute([
        'content_key' => 'awards_page',
        'content_json' => $json,
        'updated_by' => $adminId,
    ]);
}
