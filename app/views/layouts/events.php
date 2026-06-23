<?php
$meetingSlots = schedule_available_slots();
$meetingFlash = public_flash('meeting_booking');
?>
<!DOCTYPE html>
<html lang="<?= e($page['lang'] ?? 'en') ?>">
<head>
<?php render('partials/head', ['site' => $site, 'page' => $page]); ?>
</head>
<body class="events-page">
<?php render('partials/nav', ['site' => $site, 'currentPage' => 'events']); ?>

<?php render('pages/events_index', ['site' => $site]); ?>

<?php render('partials/footer', ['site' => $site]); ?>
<?php render('partials/meeting_modal', ['slots' => $meetingSlots, 'flash' => $meetingFlash]); ?>

<script>
window.portfolioData = <?= json_encode([
    'recommendations' => $site['recommendations'],
    'topics' => $site['topics'],
    'quotes' => $site['quotes'],
    'meetingSlots' => $meetingSlots,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
