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
<body class="about-page">
<?php render('partials/nav', ['site' => $site, 'currentPage' => 'about']); ?>

<?php render('pages/about_shweta', ['site' => $site]); ?>

<?php render('partials/footer', ['site' => $site]); ?>
<?php render('partials/meeting_modal', ['slots' => $meetingSlots, 'flash' => $meetingFlash]); ?>
<?php render('partials/recommendation_modal', ['flash' => $recommendationFlash]); ?>

<script>
window.portfolioData = <?= json_encode([
    'recommendations' => $site['about_page']['recommendations'] ?? $site['recommendations'],
    'topics' => $site['topics'],
    'quotes' => $site['quotes'],
    'meetingSlots' => $meetingSlots,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
