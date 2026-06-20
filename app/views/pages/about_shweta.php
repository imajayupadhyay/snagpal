<?php
$identity = $site['identity'];
$aboutPage = is_array($site['about_page'] ?? null) ? $site['about_page'] : about_page_default_content();
$facts = array_values($aboutPage['facts']);
$pathItems = array_values($aboutPage['path_items']);
$stats = array_values($aboutPage['hero_stats']);
$focusItems = array_values($aboutPage['focus_items']);
$expertiseItems = array_values($aboutPage['expertise_items']);
$researchItems = array_values($aboutPage['research_items']);
$researchMedia = array_values($aboutPage['research_media']);
$profileParagraphs = array_values($aboutPage['profile_paragraphs_html']);
?>
<main class="about-main">
  <header class="about-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal">About Shweta <span class="ac">&middot;</span> <?= e($aboutPage['kicker_suffix']) ?></div>
        <h1>
          <span class="clip"><span><?= e($aboutPage['heading_line1']) ?></span></span>
          <span class="clip"><span class="a"><?= e($aboutPage['heading_line2']) ?></span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($aboutPage['hero_role']) ?></p>
        <p class="about-lede reveal d3"><?= e($aboutPage['hero_lede']) ?></p>
        <div class="about-actions reveal d4">
          <button class="cta" type="button" data-schedule-open><?= e($aboutPage['schedule_eyebrow']) ?></button>
        </div>
      </div>

      <aside class="about-identity-panel reveal d2" aria-label="Profile summary">
        <div class="about-portrait">
          <img src="<?= e(asset($aboutPage['portrait_src'] ?: 'images/profile-commemoration.jpg')) ?>" alt="<?= e($aboutPage['portrait_alt'] ?: $identity['full_name']) ?>" />
        </div>
        <div class="about-panel-body">
          <span class="mono"><?= e($aboutPage['mandate_eyebrow']) ?></span>
          <p><?= e($facts[0]['value'] ?? '') ?></p>
        </div>
      </aside>
    </div>

    <?php if ($stats !== []): ?>
      <div class="about-stat-row reveal d4">
        <?php foreach ($stats as $stat): ?>
          <div class="about-stat">
            <span class="about-stat-value"><?= e($stat['value'] ?? '') ?></span>
            <span class="about-stat-label"><?= e($stat['label'] ?? '') ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </header>

  <section class="about-story">
    <div class="about-story-grid">
      <div class="about-story-heading">
        <span class="mono reveal"><?= e($aboutPage['profile_eyebrow']) ?></span>
        <h2 class="reveal d1"><?= e($aboutPage['profile_heading']) ?></h2>
        <?php if (! empty($aboutPage['profile_lead_html'])): ?>
          <p class="about-lead reveal d2"><?= $aboutPage['profile_lead_html'] ?></p>
        <?php endif; ?>
      </div>
      <div class="about-story-body">
        <?php foreach ($profileParagraphs as $index => $paragraph): ?>
          <p class="reveal d<?= e((string) min($index + 1, 4)) ?>"><?= $paragraph ?></p>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="about-facts" aria-label="Profile facts">
      <?php foreach ($facts as $index => $fact): ?>
        <article class="about-fact reveal d<?= e((string) min($index + 1, 4)) ?>">
          <span><?= e($fact['label']) ?></span>
          <strong><?= e($fact['value']) ?></strong>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-path">
    <div class="head">
      <h2><?= e($aboutPage['path_heading']) ?></h2>
      <span class="rule reveal"></span>
    </div>
    <div class="about-path-grid">
      <?php foreach ($pathItems as $index => $item): ?>
        <article class="about-path-card reveal d<?= e((string) min($index + 1, 4)) ?>">
          <span class="mono"><?= e($item['label']) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-principles">
    <div class="about-principles-copy">
      <span class="mono reveal"><?= e($aboutPage['principles_eyebrow']) ?></span>
      <h2 class="reveal d1"><?= e($aboutPage['principles_heading']) ?></h2>
      <?php if (! empty($aboutPage['principles_quote'])): ?>
        <blockquote class="reveal d2"><?= e($aboutPage['principles_quote']) ?></blockquote>
      <?php endif; ?>
    </div>

    <?php if ($focusItems !== []): ?>
      <div class="about-principle-grid">
        <?php foreach ($focusItems as $index => $item): ?>
          <article class="about-principle-card reveal d<?= e((string) min($index + 1, 4)) ?>">
            <span class="about-card-mark"></span>
            <h3><?= e($item['title'] ?? '') ?></h3>
            <p><?= e($item['description'] ?? '') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <?php if ($expertiseItems !== []): ?>
    <section class="about-expertise">
      <div class="head">
        <h2><?= e($aboutPage['expertise_heading']) ?></h2>
        <span class="rule reveal"></span>
      </div>
      <div class="about-expertise-list">
        <?php foreach ($expertiseItems as $index => $item): ?>
          <article class="about-expertise-row reveal d<?= e((string) min($index + 1, 4)) ?>">
            <span><?= e($item['number'] ?? str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
            <h3><?= e($item['title'] ?? '') ?></h3>
            <p><?= e($item['description'] ?? '') ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="about-engagement" id="research">
    <div class="head">
      <h2><?= e($aboutPage['research_heading']) ?></h2>
      <span class="rule reveal"></span>
    </div>

    <?php if ($researchMedia !== []): ?>
      <div class="about-media-grid">
        <?php foreach ($researchMedia as $index => $media): ?>
          <figure class="reveal d<?= e((string) min($index + 1, 4)) ?>">
            <img src="<?= e(asset($media['src'] ?? '')) ?>" alt="<?= e($media['alt'] ?? '') ?>" loading="lazy" />
            <?php if (! empty($media['caption'])): ?>
              <figcaption><?= e($media['caption']) ?></figcaption>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($researchItems !== []): ?>
      <div class="res-list">
        <?php foreach ($researchItems as $index => $item): ?>
          <article class="res-row reveal d<?= e((string) min($index + 1, 4)) ?>">
            <span class="yr"><?= e($item['year'] ?? '') ?></span>
            <div>
              <h3 class="ti"><?= e($item['title'] ?? '') ?></h3>
              <p class="ds"><?= e($item['description'] ?? '') ?></p>
            </div>
            <span class="kind"><?= e($item['kind'] ?? '') ?></span>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (! empty($aboutPage['research_note'])): ?>
      <p class="res-note"><?= e($aboutPage['research_note']) ?></p>
    <?php endif; ?>
  </section>

  <section class="about-closing">
    <div>
      <span class="mono"><?= e($aboutPage['schedule_eyebrow']) ?></span>
      <h2><?= e($aboutPage['schedule_heading']) ?></h2>
      <p><?= e($aboutPage['schedule_description']) ?></p>
    </div>
    <div class="about-closing-actions">
      <button class="cta" type="button" data-schedule-open>Request a meeting slot</button>
      <?php if (! empty($identity['linkedin']['url'])): ?>
        <a class="about-text-link light" href="<?= e($identity['linkedin']['url']) ?>" target="_blank" rel="noopener"><?= e($identity['linkedin']['label'] ?? 'LinkedIn') ?></a>
      <?php endif; ?>
    </div>
  </section>
</main>
