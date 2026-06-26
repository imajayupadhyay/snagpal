<?php $footer = is_array($site['footer'] ?? null) ? $site['footer'] : []; ?>
<footer>
  <span>&copy; <span id="yr"></span> <?= e($footer['copyright_name'] ?? $site['identity']['full_name']) ?></span>
  <span><?= e($footer['tagline'] ?? $site['identity']['footer_tagline'] ?? '') ?></span>
  <span><a href="#top"><?= e($footer['back_to_top_label'] ?? 'Back to top ↑') ?></a></span>
  <span class="footer-sitemap"><a href="/sitemap.xml" rel="nofollow">sitemap.xml</a></span>
</footer>
