<?php
$identity = $site['identity'];
$cohorts = $site['cohorts'] ?? [];
$items = is_array($cohortItems ?? null) ? $cohortItems : cohort_items(is_array($cohorts) ? $cohorts : []);
$featured = $items[0] ?? null;
$schedule = $site['schedule'] ?? [];
$pageContent = is_array($site['cohorts_page'] ?? null) ? $site['cohorts_page'] : cohorts_page_default_content();
$cohortCategories = cohort_categories_for_filter($items);
?>
<main class="cohorts-main">
  <header class="about-hero cohorts-archive-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal"><?= e($pageContent['kicker']) ?></div>
        <h1>
          <span class="clip"><span><?= e($pageContent['heading_line1']) ?></span></span>
          <span class="clip"><span class="a"><?= e($pageContent['heading_line2']) ?></span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($site['hero']['role'] ?? '') ?></p>
        <p class="about-lede reveal d3"><?= e($cohorts['intro'] ?? 'Recordings and field notes from recent capability-building cohorts on AI governance and public-sector technology.') ?></p>
        <div class="about-actions reveal d4">
          <a class="about-text-link" href="#cohort-list"><?= e($pageContent['browse_cta_label']) ?></a>
        </div>
      </div>

      <?php if ($featured !== null): ?>
        <aside class="cohorts-featured-panel reveal d2">
          <div class="cohorts-featured-media">
            <?= cohort_video_html((string) ($featured['video'] ?? ''), (string) ($featured['title'] ?? ''), (string) ($featured['poster'] ?? '')) ?>
          </div>
          <div class="cohorts-featured-body">
            <span class="mono"><?= e($featured['meta'] ?? 'Featured Cohort') ?></span>
            <h2><?= e($featured['title'] ?? '') ?></h2>
            <p><?= e($featured['description'] ?? '') ?></p>
            <a class="about-text-link" href="<?= e($featured['detail_url']) ?>">Read Notes</a>
          </div>
        </aside>
      <?php endif; ?>
    </div>

    <div class="about-stat-row cohorts-stat-row reveal d4">
      <div class="about-stat">
        <span class="about-stat-value"><?= e((string) count($items)) ?></span>
        <span class="about-stat-label">Published cohort entries</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value"><?= e($pageContent['stat2_value']) ?></span>
        <span class="about-stat-label"><?= e($pageContent['stat2_label']) ?></span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value"><?= e($pageContent['stat3_value']) ?></span>
        <span class="about-stat-label"><?= e($pageContent['stat3_label']) ?></span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value"><?= e($pageContent['stat4_value']) ?></span>
        <span class="about-stat-label"><?= e($pageContent['stat4_label']) ?></span>
      </div>
    </div>
  </header>

  <section class="cohorts-archive" id="cohort-list">
    <div class="head">
      <h2><?= e($cohorts['heading'] ?? 'Cohorts') ?></h2>
      <span class="rule reveal"></span>
    </div>

    <?php if ($items === []): ?>
      <p class="cohorts-empty reveal">No cohorts have been published yet.</p>
    <?php else: ?>
      <?php if ($cohortCategories !== []): ?>
        <div class="cohort-tabs" role="tablist" aria-label="Filter cohorts by category" data-cohort-tabs>
          <button type="button" class="cohort-tab is-active" role="tab" aria-selected="true" data-cohort-tab="all">All <span><?= e((string) count($items)) ?></span></button>
          <?php foreach ($cohortCategories as $category): ?>
            <button type="button" class="cohort-tab" role="tab" aria-selected="false" data-cohort-tab="<?= e($category['slug']) ?>"><?= e($category['name']) ?> <span><?= e((string) $category['count']) ?></span></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="cohorts-archive-grid" data-cohort-grid>
        <?php foreach ($items as $index => $item): ?>
          <article class="cohorts-post-card reveal d<?= e((string) min($index + 1, 4)) ?>" data-cohort-category="<?= e((string) ($item['category_slug'] ?? '')) ?>">
            <div class="cohorts-post-media">
              <?= cohort_video_html((string) ($item['video'] ?? ''), (string) ($item['title'] ?? ''), (string) ($item['poster'] ?? '')) ?>
            </div>
            <div class="cohorts-post-body">
              <div class="cohorts-post-meta">
                <span><?= e($item['meta'] ?? ('Cohort ' . ($index + 1))) ?></span>
                <em><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></em>
              </div>
              <h3><a href="<?= e($item['detail_url']) ?>"><?= e($item['title'] ?? '') ?></a></h3>
              <p><?= e($item['description'] ?? '') ?></p>
              <a class="cohorts-post-link" href="<?= e($item['detail_url']) ?>">Open Detail</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="cohorts-empty cohorts-empty-filtered" data-cohort-empty hidden>No cohorts in this category yet.</p>
    <?php endif; ?>
  </section>

  <section class="about-closing cohorts-closing">
    <div>
      <span class="mono"><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></span>
      <h2><?= e($schedule['heading'] ?? "Let's talk.") ?></h2>
      <p><?= e($schedule['description'] ?? '') ?></p>
    </div>
    <div class="about-closing-actions">
      <button class="cta" type="button" data-schedule-open>Request a meeting slot</button>
      <?php if (! empty($identity['linkedin']['url'])): ?>
        <a class="about-text-link light" href="<?= e($identity['linkedin']['url']) ?>" target="_blank" rel="noopener"><?= e($identity['linkedin']['label'] ?? 'LinkedIn') ?></a>
      <?php endif; ?>
    </div>
  </section>
</main>
