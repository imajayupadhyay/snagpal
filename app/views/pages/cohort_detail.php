<?php
$identity = $site['identity'];
$schedule = $site['schedule'] ?? [];
$cohorts = $site['cohorts'] ?? [];
$items = is_array($cohortItems ?? null) ? $cohortItems : cohort_items(is_array($cohorts) ? $cohorts : []);
$cohort = is_array($cohort ?? null) ? $cohort : null;

if ($cohort === null):
?>
<main class="cohorts-main">
  <section class="cohort-not-found" id="top">
    <span class="mono reveal">Cohort</span>
    <h1 class="reveal d1">Cohort not found.</h1>
    <p class="reveal d2">The requested cohort is unavailable or has moved.</p>
    <a class="cta reveal d3" href="<?= e(url_path('cohorts/')) ?>">View Cohorts</a>
  </section>
</main>
<?php
return;
endif;

$related = array_values(array_filter($items, static fn (array $item): bool => ($item['slug'] ?? '') !== ($cohort['slug'] ?? '')));
$related = array_slice($related, 0, 3);
$articleHtml = cohort_public_content_html($cohort['content'] ?? '', $cohort['description'] ?? '');
$takeaways = cohort_public_takeaways($cohort);
?>
<main class="cohorts-main">
  <header class="cohort-detail-hero" id="top">
    <div class="cohort-detail-kicker mono reveal">
      <a href="<?= e(url_path('cohorts/')) ?>">Cohorts</a>
      <span class="ac">&middot;</span>
      <span><?= e($cohort['meta'] ?? 'Cohort Detail') ?></span>
    </div>

    <div class="cohort-detail-grid">
      <div class="cohort-detail-copy">
        <h1>
          <span class="clip"><span><?= e($cohort['title'] ?? 'Cohort') ?></span></span>
        </h1>
        <p class="about-lede reveal d2"><?= e($cohort['description'] ?? '') ?></p>
        <div class="about-actions reveal d3">
          <button class="cta" type="button" data-schedule-open><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></button>
          <a class="about-text-link" href="#notes">Read Notes</a>
        </div>
      </div>

      <aside class="cohort-detail-meta reveal d2">
        <span class="mono">Session Record</span>
        <dl>
          <div>
            <dt>Series</dt>
            <dd><?= e($site['cohorts']['heading'] ?? 'Recent Cohorts') ?></dd>
          </div>
          <div>
            <dt>Format</dt>
            <dd>Video + field notes</dd>
          </div>
          <div>
            <dt>Focus</dt>
            <dd>AI governance and public-sector technology</dd>
          </div>
        </dl>
      </aside>
    </div>
  </header>

  <section class="cohort-watch">
    <div class="cohort-watch-grid">
      <div class="cohort-watch-media reveal">
        <?= cohort_video_html((string) ($cohort['video'] ?? ''), (string) ($cohort['title'] ?? ''), (string) ($cohort['poster'] ?? '')) ?>
      </div>
      <aside class="cohort-watch-note reveal d2">
        <span class="mono">Session Brief</span>
        <h2><?= e($cohort['title'] ?? '') ?></h2>
        <p><?= e($cohort['description'] ?? '') ?></p>
      </aside>
    </div>
  </section>

  <section class="cohort-article" id="notes">
    <article class="cohort-article-body">
      <span class="mono reveal">Field Notes</span>
      <h2 class="reveal d1">From recording to institutional practice.</h2>
      <div class="cohort-rich-body reveal d2"><?= $articleHtml ?></div>
      <?php if (! empty($cohort['resource_url'])): ?>
        <a class="about-text-link reveal d4" href="<?= e((string) $cohort['resource_url']) ?>" target="_blank" rel="noopener"><?= e($cohort['resource_label'] ?? 'Open resource') ?></a>
      <?php endif; ?>
    </article>

    <?php if ($takeaways !== []): ?>
      <aside class="cohort-takeaways">
        <span class="mono reveal">Takeaways</span>
        <?php foreach ($takeaways as $index => $takeaway): ?>
          <div class="cohort-takeaway reveal d<?= e((string) min($index + 1, 4)) ?>">
            <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
            <p><?= e($takeaway) ?></p>
          </div>
        <?php endforeach; ?>
      </aside>
    <?php endif; ?>
  </section>

  <?php if ($related !== []): ?>
    <section class="cohort-related">
      <div class="head">
        <h2>Related Cohorts</h2>
        <span class="rule reveal"></span>
      </div>
      <div class="cohort-related-grid">
        <?php foreach ($related as $index => $item): ?>
          <article class="cohort-related-card reveal d<?= e((string) min($index + 1, 4)) ?>">
            <span class="mono"><?= e($item['meta'] ?? 'Cohort') ?></span>
            <h3><a href="<?= e($item['detail_url']) ?>"><?= e($item['title'] ?? '') ?></a></h3>
            <p><?= e($item['description'] ?? '') ?></p>
            <a class="cohorts-post-link" href="<?= e($item['detail_url']) ?>">Open Detail</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="about-closing cohorts-closing">
    <div>
      <span class="mono"><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></span>
      <h2><?= e($schedule['heading'] ?? "Let's talk.") ?></h2>
      <p><?= e($schedule['description'] ?? '') ?></p>
    </div>
    <div class="about-closing-actions">
      <button class="cta" type="button" data-schedule-open><?= e($schedule['cta_label'] ?? 'Request a meeting slot') ?></button>
      <?php if (! empty($identity['linkedin']['url'])): ?>
        <a class="about-text-link light" href="<?= e($identity['linkedin']['url']) ?>" target="_blank" rel="noopener"><?= e($identity['linkedin']['label'] ?? 'LinkedIn') ?></a>
      <?php endif; ?>
    </div>
  </section>
</main>
