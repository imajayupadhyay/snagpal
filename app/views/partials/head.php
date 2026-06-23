<?php
/**
 * Shared <head> for every public page.
 *
 * Reads the per-page `$page` array (title, description, theme_color, plus
 * optional SEO overrides: robots, canonical, og_type, og_title,
 * og_description, og_image, og_image_alt) and layers it over the global
 * `seo_settings()` defaults to emit canonical, Open Graph, Twitter,
 * verification, icons, JSON-LD (Person + WebSite), and analytics.
 */
$page = is_array($page ?? null) ? $page : [];
$site = is_array($site ?? null) ? $site : [];
$seo = seo_settings();

$title = (string) ($page['title'] ?? ($seo['site_name'] ?? 'Shweta Nagpal'));
$description = (string) ($page['description'] ?? '');
$themeColor = (string) ($page['theme_color'] ?? '#0C5E55');
$robots = (string) ($page['robots'] ?? ($seo['default_robots'] ?? 'index, follow'));

$canonicalPath = (string) ($page['canonical'] ?? seo_current_path());
$canonicalUrl = seo_canonical_url($seo, $canonicalPath);

$ogType = (string) ($page['og_type'] ?? 'website');
$ogTitle = trim((string) ($page['og_title'] ?? '')) !== '' ? (string) $page['og_title'] : $title;
$ogDescription = trim((string) ($page['og_description'] ?? '')) !== '' ? (string) $page['og_description'] : $description;

$ogImageRaw = trim((string) ($page['og_image'] ?? ''));
foreach ([$seo['default_og_image'] ?? '', $seo['person']['image'] ?? '', $site['hero']['image']['src'] ?? ''] as $fallback) {
    if ($ogImageRaw === '') {
        $ogImageRaw = trim((string) $fallback);
    }
}
$ogImage = seo_absolute_url($seo, $ogImageRaw);
$ogImageAlt = trim((string) ($page['og_image_alt'] ?? '')) !== '' ? (string) $page['og_image_alt'] : $ogTitle;

$siteName = (string) ($seo['site_name'] ?? 'Shweta Nagpal');
$locale = (string) ($seo['locale'] ?? 'en_US');
$twitterHandle = (string) ($seo['twitter_handle'] ?? '');
$googleVerify = (string) ($seo['verification']['google'] ?? '');
$bingVerify = (string) ($seo['verification']['bing'] ?? '');
$analyticsId = trim((string) ($seo['analytics_id'] ?? ''));

$personSchema = seo_person_schema($seo);
$websiteSchema = seo_website_schema($seo);
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;
?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>" />
<meta name="robots" content="<?= e($robots) ?>" />
<link rel="canonical" href="<?= e($canonicalUrl) ?>" />
<meta name="theme-color" content="<?= e($themeColor) ?>" />
<?php if ($googleVerify !== ''): ?>
<meta name="google-site-verification" content="<?= e($googleVerify) ?>" />
<?php endif; ?>
<?php if ($bingVerify !== ''): ?>
<meta name="msvalidate.01" content="<?= e($bingVerify) ?>" />
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="<?= e($ogType) ?>" />
<meta property="og:title" content="<?= e($ogTitle) ?>" />
<meta property="og:description" content="<?= e($ogDescription) ?>" />
<meta property="og:url" content="<?= e($canonicalUrl) ?>" />
<meta property="og:site_name" content="<?= e($siteName) ?>" />
<meta property="og:locale" content="<?= e($locale) ?>" />
<?php if ($ogImage !== ''): ?>
<meta property="og:image" content="<?= e($ogImage) ?>" />
<meta property="og:image:alt" content="<?= e($ogImageAlt) ?>" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<?php endif; ?>

<!-- Twitter / X -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= e($ogTitle) ?>" />
<meta name="twitter:description" content="<?= e($ogDescription) ?>" />
<?php if ($ogImage !== ''): ?>
<meta name="twitter:image" content="<?= e($ogImage) ?>" />
<meta name="twitter:image:alt" content="<?= e($ogImageAlt) ?>" />
<?php endif; ?>
<?php if ($twitterHandle !== ''): ?>
<meta name="twitter:site" content="<?= e($twitterHandle) ?>" />
<meta name="twitter:creator" content="<?= e($twitterHandle) ?>" />
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Roboto+Condensed:wght@400;500;700&family=Roboto+Mono:wght@400;500&family=Rubik+Distressed&display=swap">
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Roboto+Condensed:wght@400;500;700&family=Roboto+Mono:wght@400;500&family=Rubik+Distressed&display=swap" rel="stylesheet">
<link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="manifest" href="<?= e(url_path('site.webmanifest')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">

<script type="application/ld+json"><?= json_encode($websiteSchema, $jsonFlags) ?></script>
<?php if ($personSchema !== []): ?>
<script type="application/ld+json"><?= json_encode($personSchema, $jsonFlags) ?></script>
<?php endif; ?>
<?php if ($analyticsId !== '' && str_starts_with($analyticsId, 'GTM-')): ?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?= e($analyticsId) ?>');</script>
<?php elseif ($analyticsId !== '' && (str_starts_with($analyticsId, 'G-') || str_starts_with($analyticsId, 'UA-'))): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($analyticsId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($analyticsId) ?>');</script>
<?php endif; ?>
<script>
  (function(){try{
    var saved=localStorage.getItem('theme');
    document.documentElement.setAttribute('data-theme',saved==='dark'?'dark':'light');
  }catch(e){document.documentElement.setAttribute('data-theme','light');}})();
</script>
