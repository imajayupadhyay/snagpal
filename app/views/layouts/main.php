<?php
$meetingSlots = schedule_available_slots();
$meetingFlash = public_flash('meeting_booking');
$recommendationFlash = public_flash('recommendation_submission');
?>
<!DOCTYPE html>
<html lang="<?= e($page['lang'] ?? 'en') ?>">
<head>
<?php render('partials/head', ['site' => $site, 'page' => $page]); ?>
</head>
<body class="loading">
<?php render('partials/loader', ['site' => $site]); ?>
<?php render('partials/nav', ['site' => $site]); ?>

<?php render('sections/hero', ['site' => $site]); ?>
<?php render('sections/topics', ['site' => $site]); ?>
<?php render('sections/recommendations', ['site' => $site]); ?>
<?php render('sections/profile', ['site' => $site]); ?>
<?php render('sections/expertise', ['site' => $site]); ?>
<?php render('sections/voice', ['site' => $site]); ?>
<?php render('sections/focus', ['site' => $site]); ?>
<?php render('sections/research', ['site' => $site]); ?>
<?php render('sections/cohorts', ['site' => $site]); ?>
<?php render('sections/schedule', ['site' => $site]); ?>
<?php render('partials/footer', ['site' => $site]); ?>
<?php render('partials/meeting_modal', ['slots' => $meetingSlots, 'flash' => $meetingFlash]); ?>
<?php render('partials/recommendation_modal', ['flash' => $recommendationFlash]); ?>

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
