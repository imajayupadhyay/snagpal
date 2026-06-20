<?php
$identity = $site['identity'];
$awardsPage = is_array($site['awards_page'] ?? null) ? $site['awards_page'] : awards_page_default_content();
$stats = array_values($awardsPage['hero_stats']);
$recognitions = array_values($awardsPage['recognitions']);
$standards = array_values($awardsPage['standards']);
?>
<main class="awards-main">
  <header class="about-hero awards-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal"><?= e($awardsPage['kicker']) ?></div>
        <h1>
          <span class="clip"><span><?= e($awardsPage['heading_line1']) ?></span></span>
          <span class="clip"><span class="a"><?= e($awardsPage['heading_line2']) ?></span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($awardsPage['hero_role']) ?></p>
        <p class="about-lede reveal d3"><?= e($awardsPage['hero_lede']) ?></p>
        <div class="about-actions reveal d4">
          <button class="cta" type="button" data-schedule-open><?= e($awardsPage['schedule_eyebrow']) ?></button>
        </div>
      </div>

      <aside class="awards-hero-card reveal d2" aria-label="Recognition summary">
        <div class="awards-hero-image">
          <img src="<?= e(asset($awardsPage['hero_card_image_src'] ?: 'images/research-felicitation.jpg')) ?>" alt="<?= e($awardsPage['hero_card_image_alt'] ?: $identity['full_name']) ?>" />
        </div>
        <div class="awards-hero-meta">
          <span class="mono"><?= e($awardsPage['hero_card_eyebrow']) ?></span>
          <strong><?= e($awardsPage['hero_card_title']) ?></strong>
          <p><?= e($awardsPage['hero_card_description']) ?></p>
        </div>
      </aside>
    </div>

    <?php if ($stats !== []): ?>
      <div class="about-stat-row awards-stat-row reveal d4">
        <?php foreach ($stats as $stat): ?>
          <div class="about-stat">
            <span class="about-stat-value"><?= e($stat['value'] ?? '') ?></span>
            <span class="about-stat-label"><?= e($stat['label'] ?? '') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </header>

  <section class="awards-ledger" id="recognitions">
    <div class="head">
      <h2><?= e($awardsPage['ledger_heading']) ?></h2>
      <span class="rule reveal"></span>
    </div>

    <div class="awards-grid">
      <?php foreach ($recognitions as $index => $item): ?>
        <article class="award-card reveal d<?= e((string) min($index + 1, 4)) ?>">
          <?php if (! empty($item['image'])): ?>
            <div class="award-card-media">
              <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['image_alt'] ?? '') ?>" loading="lazy" />
            </div>
          <?php endif; ?>
          <div class="award-card-body">
            <div class="award-card-top">
              <span><?= e($item['year'] ?? '') ?></span>
              <em><?= e($item['type'] ?? '') ?></em>
            </div>
            <h3><?= e($item['title'] ?? '') ?></h3>
            <p><?= e($item['description'] ?? '') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="awards-standards">
    <div class="awards-standards-copy">
      <span class="mono reveal"><?= e($awardsPage['standards_eyebrow']) ?></span>
      <h2 class="reveal d1"><?= e($awardsPage['standards_heading']) ?></h2>
      <p class="reveal d2"><?= e($awardsPage['standards_intro']) ?></p>
    </div>
    <div class="awards-standards-grid">
      <?php foreach ($standards as $index => $item): ?>
        <article class="awards-standard-card reveal d<?= e((string) min($index + 1, 4)) ?>">
          <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          <h3><?= e($item['title'] ?? '') ?></h3>
          <p><?= e($item['description'] ?? '') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-closing awards-closing">
    <div>
      <span class="mono"><?= e($awardsPage['schedule_eyebrow']) ?></span>
      <h2><?= e($awardsPage['schedule_heading']) ?></h2>
      <p><?= e($awardsPage['schedule_description']) ?></p>
    </div>
    <div class="about-closing-actions">
      <button class="cta" type="button" data-schedule-open><?= e($awardsPage['schedule_cta_label']) ?></button>
      <?php if (! empty($identity['linkedin']['url'])): ?>
        <a class="about-text-link light" href="<?= e($identity['linkedin']['url']) ?>" target="_blank" rel="noopener"><?= e($identity['linkedin']['label'] ?? 'LinkedIn') ?></a>
      <?php endif; ?>
    </div>
  </section>
</main>
