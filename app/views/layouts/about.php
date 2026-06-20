<?php
$meetingSlots = schedule_available_slots();
$meetingFlash = public_flash('meeting_booking');
$recommendationFlash = public_flash('recommendation_submission');
?>
<!DOCTYPE html>
<html lang="<?= e($page['lang'] ?? 'en') ?>">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>" />
<meta name="theme-color" content="<?= e($page['theme_color'] ?? '#0C5E55') ?>" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Roboto+Condensed:wght@400;500;700&family=Roboto+Mono:wght@400;500&family=Rubik+Distressed&display=swap" rel="stylesheet">
<link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<script>
  (function(){try{
    var saved=localStorage.getItem('theme');
    document.documentElement.setAttribute('data-theme',saved==='dark'?'dark':'light');
  }catch(e){document.documentElement.setAttribute('data-theme','light');}})();
</script>
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
