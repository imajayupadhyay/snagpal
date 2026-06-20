<?php
$identity = $site['identity'];
$cohorts = $site['cohorts'] ?? [];
$items = is_array($cohortItems ?? null) ? $cohortItems : cohort_items(is_array($cohorts) ? $cohorts : []);
$featured = $items[0] ?? null;
$schedule = $site['schedule'] ?? [];
?>
<main class="cohorts-main">
  <header class="about-hero cohorts-archive-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal">Cohorts <span class="ac">&middot;</span> Video Notes</div>
        <h1>
          <span class="clip"><span>Cohort</span></span>
          <span class="clip"><span class="a">Library</span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($site['hero']['role'] ?? '') ?></p>
        <p class="about-lede reveal d3"><?= e($cohorts['intro'] ?? 'Recordings and field notes from recent capability-building cohorts on AI governance and public-sector technology.') ?></p>
        <div class="about-actions reveal d4">
          <a class="about-text-link" href="#cohort-list">Browse Cohorts</a>
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
        <span class="about-stat-value">Video</span>
        <span class="about-stat-label">Recording-led learning format</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value">AI</span>
        <span class="about-stat-label">Governance and public technology</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value">Admin</span>
        <span class="about-stat-label">Database-backed publishing workflow</span>
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
      <div class="cohorts-archive-grid">
        <?php foreach ($items as $index => $item): ?>
          <article class="cohorts-post-card reveal d<?= e((string) min($index + 1, 4)) ?>">
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
