<?php
$cohorts = $site['cohorts'] ?? null;

if (! is_array($cohorts) || empty($cohorts['items'])) {
    return;
}

$archiveUrl = url_path('cohorts/');
$totalPublished = (int) ($cohorts['total_published'] ?? count($cohorts['items']));
?>
<section id="cohorts" class="cohorts">
  <div class="head cohorts-head">
    <div class="cohorts-head-row">
      <h2><?= e($cohorts['heading']) ?></h2>
      <div class="cohorts-arrows">
        <button type="button" class="cohorts-arrow" data-cohorts="prev" aria-label="Previous cohorts">&#8249;</button>
        <button type="button" class="cohorts-arrow" data-cohorts="next" aria-label="Next cohorts">&#8250;</button>
      </div>
    </div>
    <span class="rule reveal"></span>
  </div>
  <?php if (! empty($cohorts['intro'])): ?>
    <p class="cohorts-intro reveal"><?= e($cohorts['intro']) ?></p>
  <?php endif; ?>
  <div class="cohorts-slider">
    <div class="cohorts-track" id="cohortsTrack" role="group" aria-label="Cohort recordings">
      <?php foreach ($cohorts['items'] as $index => $item): ?>
        <?php
        $embed = cohort_video_html($item['video'] ?? '', $item['title'] ?? '', $item['poster'] ?? '');
        $detailUrl = (string) ($item['detail_url'] ?? '');
        ?>
        <article class="cohort-card reveal<?= $index > 0 ? ' d' . e((string) min($index, 4)) : '' ?>">
          <?php if ($embed !== ''): ?>
            <div class="cohort-media"><?= $embed ?></div>
          <?php endif; ?>
          <div class="cohort-body">
            <?php if (! empty($item['meta'])): ?><span class="cohort-meta mono"><?= e($item['meta']) ?></span><?php endif; ?>
            <h3>
              <?php if ($detailUrl !== ''): ?>
                <a href="<?= e($detailUrl) ?>"><?= e($item['title']) ?></a>
              <?php else: ?>
                <?= e($item['title']) ?>
              <?php endif; ?>
            </h3>
            <?php if (! empty($item['description'])): ?><p><?= e($item['description']) ?></p><?php endif; ?>
            <?php if ($detailUrl !== ''): ?>
              <a class="cohorts-post-link cohort-card-link" href="<?= e($detailUrl) ?>">Open Detail</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if (count($cohorts['items']) > 1): ?>
    <div class="cohorts-dots" id="cohortsDots" aria-label="Cohort slide navigation">
      <?php foreach ($cohorts['items'] as $index => $item): ?>
        <button type="button" class="cohorts-dot" data-cohorts-dot="<?= e((string) $index) ?>" aria-label="Go to <?= e($item['title'] ?? ('slide ' . ($index + 1))) ?>"></button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="cohorts-actions">
    <a class="cohorts-all-link" href="<?= e($archiveUrl) ?>">
      <?= ! empty($cohorts['has_more']) ? 'View all ' . e((string) $totalPublished) . ' cohorts' : 'View all cohorts' ?>
    </a>
  </div>
  <?php if (! empty($cohorts['note'])): ?>
    <p class="cohorts-note"><?= e($cohorts['note']) ?></p>
  <?php endif; ?>
</section>
