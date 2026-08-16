<?php

/**
 * Reconciles published recommendation copies against the submissions table.
 *
 * Publishing copies a submission into the homepage / About page content. Before
 * src_id existed those copies carried no link back, so a deleted submission left
 * its copy rendering forever. This script:
 *
 *   --backfill              stamps src_id onto copies that still match a live
 *                           submission, so future deletes retract them (safe,
 *                           non-destructive)
 *   --remove=home:5,about:2 removes specific entries by page and index
 *
 * With no flags it only reports. Removal is deliberately explicit: an entry with
 * no src_id may equally be a quote an admin typed by hand in the editor, and
 * that must never be deleted by guesswork.
 *
 * Usage:
 *   php portfolio/scripts/reconcile_recommendations.php
 *   php portfolio/scripts/reconcile_recommendations.php --backfill
 *   php portfolio/scripts/reconcile_recommendations.php --remove=home:5
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$args = array_slice($argv, 1);
$backfill = in_array('--backfill', $args, true);
$removeSpec = '';

foreach ($args as $arg) {
    if (str_starts_with($arg, '--remove=')) {
        $removeSpec = substr($arg, strlen('--remove='));
    }
}

$removals = ['home' => [], 'about' => []];

foreach (array_filter(explode(',', $removeSpec)) as $token) {
    $parts = explode(':', trim($token));

    if (count($parts) !== 2 || ! isset($removals[$parts[0]]) || ! is_numeric($parts[1])) {
        fwrite(STDERR, "Invalid --remove token: {$token} (expected home:<index> or about:<index>)\n");
        exit(1);
    }

    $removals[$parts[0]][] = (int) $parts[1];
}

$submissions = db()->query('SELECT * FROM recommendation_submissions')->fetchAll();
$byId = [];
$byText = [];

foreach ($submissions as $submission) {
    $byId[(int) $submission['id']] = $submission;
    $name = (string) $submission['name'];
    $designation = (string) $submission['designation'];
    $who = trim($name . ($designation !== '' ? ', ' . $designation : ''));
    $key = recommendation_clean_multiline($submission['quote']) . "\x00" . recommendation_clean_text($who);
    $byText[$key] = $submission;
}

$pages = [
    'home' => [
        'label' => 'Homepage',
        'entries' => homepage_content()['recommendations'] ?? [],
        'defaults' => homepage_default_content()['recommendations'] ?? [],
    ],
    'about' => [
        'label' => 'About page',
        'entries' => about_page_content()['recommendations'] ?? [],
        'defaults' => about_page_default_content()['recommendations'] ?? [],
    ],
];

$defaultKey = static function (array $entry): string {
    return recommendation_clean_multiline($entry['q'] ?? '') . "\x00" . recommendation_clean_text($entry['w'] ?? '');
};

$changed = ['home' => false, 'about' => false];

foreach ($pages as $slug => $page) {
    echo "\n=== {$page['label']} ===\n";

    $defaults = [];

    foreach ($page['defaults'] as $entry) {
        $defaults[$defaultKey($entry)] = true;
    }

    $kept = [];

    foreach ($page['entries'] as $index => $entry) {
        if (! is_array($entry)) {
            continue;
        }

        $who = (string) ($entry['w'] ?? '');
        $srcId = isset($entry['src_id']) && $entry['src_id'] !== '' ? (int) $entry['src_id'] : null;
        $key = $defaultKey($entry);
        $status = '';

        if ($srcId !== null) {
            $status = isset($byId[$srcId])
                ? "linked to submission #{$srcId}"
                : "ORPHAN - submission #{$srcId} no longer exists";
        } elseif (isset($byText[$key])) {
            $matchedId = (int) $byText[$key]['id'];
            $status = "unlinked, matches live submission #{$matchedId}";

            if ($backfill) {
                $entry['src_id'] = $matchedId;
                $changed[$slug] = true;
                $status .= ' - BACKFILLED';
            }
        } elseif (isset($defaults[$key])) {
            $status = 'hand-authored default';
        } else {
            $status = 'UNLINKED - no matching submission (deleted publication, or typed by hand)';
        }

        $marked = in_array($index, $removals[$slug], true);
        printf("  [%d] %-42s %s%s\n", $index, mb_substr($who, 0, 42), $status, $marked ? '  <-- REMOVING' : '');

        if ($marked) {
            $changed[$slug] = true;
            continue;
        }

        $kept[] = $entry;
    }

    $pages[$slug]['kept'] = $kept;
}

if (! $backfill && $removeSpec === '') {
    echo "\nReport only. Re-run with --backfill and/or --remove=home:<index> to apply changes.\n";
    exit(0);
}

if ($changed['home']) {
    $homepage = homepage_content();
    $homepage['recommendations'] = array_values($pages['home']['kept']);
    homepage_save_content($homepage, null);
    echo "\nHomepage content updated.\n";
}

if ($changed['about']) {
    $about = about_page_content();
    $about['recommendations'] = array_values($pages['about']['kept']);
    about_page_save_content($about, null);
    echo "About page content updated.\n";
}

if (! $changed['home'] && ! $changed['about']) {
    echo "\nNothing to change.\n";
}
