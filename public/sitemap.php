<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$seo = seo_settings();
$urls = seo_sitemap_urls($seo);
$lastmod = date('Y-m-d');

header('Content-Type: application/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($urls as $url) {
    echo "  <url>\n";
    echo '    <loc>' . e($url['loc']) . "</loc>\n";
    echo '    <lastmod>' . e($lastmod) . "</lastmod>\n";
    echo '    <changefreq>' . e($url['changefreq']) . "</changefreq>\n";
    echo '    <priority>' . e($url['priority']) . "</priority>\n";
    echo "  </url>\n";
}

echo "</urlset>\n";
