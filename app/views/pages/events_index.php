<?php
$identity = $site['identity'];
$events = $site['events'] ?? [];
$upcoming = array_values(array_filter($events['upcoming'] ?? [], 'is_array'));
$past = array_values(array_filter($events['past'] ?? [], 'is_array'));
$schedule = $site['schedule'] ?? [];
$pageContent = is_array($site['events_page'] ?? null) ? $site['events_page'] : events_page_default_content();
?>
<main class="events-main">
  <header class="about-hero events-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal"><?= e($pageContent['kicker']) ?></div>
        <h1>
          <span class="clip"><span><?= e($pageContent['heading_line1']) ?></span></span>
          <span class="clip"><span class="a"><?= e($pageContent['heading_line2']) ?></span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($site['hero']['role'] ?? '') ?></p>
        <p class="about-lede reveal d3"><?= e($pageContent['intro']) ?></p>
        <div class="about-actions reveal d4">
          <a class="about-text-link" href="#upcoming-events">Upcoming</a>
          <a class="about-text-link" href="#past-events">Past Events</a>
        </div>
      </div>

      <aside class="events-summary-panel reveal d2">
        <span class="mono"><?= e($pageContent['panel_eyebrow']) ?></span>
        <strong><?= e($pageContent['panel_title']) ?></strong>
        <p><?= e($pageContent['panel_description']) ?></p>
        <div class="events-summary-stats">
          <div><b><?= e((string) count($upcoming)) ?></b><span>Upcoming</span></div>
          <div><b><?= e((string) count($past)) ?></b><span>Past</span></div>
        </div>
      </aside>
    </div>
  </header>

  <section class="events-section" id="upcoming-events">
    <div class="head events-head">
      <h2>Upcoming Events</h2>
      <span class="rule reveal"></span>
    </div>
    <?php if ($upcoming === []): ?>
      <p class="events-empty reveal">No upcoming events have been published yet.</p>
    <?php else: ?>
      <div class="events-grid">
        <?php foreach ($upcoming as $index => $item): ?>
          <?php $embed = cohort_video_html((string) ($item['video'] ?? ''), (string) ($item['title'] ?? ''), (string) ($item['poster'] ?? '')); ?>
          <article class="event-card is-upcoming reveal d<?= e((string) min($index + 1, 4)) ?>">
            <?php if ($embed !== ''): ?>
              <div class="event-card-media"><?= $embed ?></div>
            <?php endif; ?>
            <div class="event-card-body">
              <div class="event-card-meta">
                <span>Upcoming</span>
                <em><?= e($item['meta'] ?? '') ?></em>
              </div>
              <h3><?= e($item['title'] ?? '') ?></h3>
              <p><?= e($item['description'] ?? '') ?></p>
              <?php if (! empty($item['location'])): ?>
                <div class="event-card-detail"><?= e($item['location']) ?></div>
              <?php endif; ?>
              <?php if (! empty($item['registration_url'])): ?>
                <a class="about-text-link event-card-cta" href="<?= e($item['registration_url']) ?>" target="_blank" rel="noopener"><?= e($item['registration_label'] ?: 'Register') ?></a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="events-section events-past" id="past-events">
    <div class="head events-head">
      <h2>Past Events</h2>
      <span class="rule reveal"></span>
    </div>
    <?php if ($past === []): ?>
      <p class="events-empty reveal">No past events have been published yet.</p>
    <?php else: ?>
      <div class="events-grid">
        <?php foreach ($past as $index => $item): ?>
          <?php $embed = cohort_video_html((string) ($item['video'] ?? ''), (string) ($item['title'] ?? ''), (string) ($item['poster'] ?? '')); ?>
          <article class="event-card is-past reveal d<?= e((string) min($index + 1, 4)) ?>">
            <?php if ($embed !== ''): ?>
              <div class="event-card-media"><?= $embed ?></div>
            <?php endif; ?>
            <div class="event-card-body">
              <div class="event-card-meta">
                <span>Past</span>
                <em><?= e($item['meta'] ?? '') ?></em>
              </div>
              <h3><?= e($item['title'] ?? '') ?></h3>
              <p><?= e($item['description'] ?? '') ?></p>
              <?php if (! empty($item['location'])): ?>
                <div class="event-card-detail"><?= e($item['location']) ?></div>
              <?php endif; ?>
              <?php if (! empty($item['registration_url'])): ?>
                <a class="about-text-link event-card-cta" href="<?= e($item['registration_url']) ?>" target="_blank" rel="noopener"><?= e($item['registration_label'] ?: 'View link') ?></a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (! empty($pageContent['note'])): ?>
      <p class="events-note"><?= e($pageContent['note']) ?></p>
    <?php endif; ?>
  </section>

  <section class="about-closing events-closing">
    <div>
      <span class="mono"><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></span>
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
