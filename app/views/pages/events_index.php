<?php
$identity = $site['identity'];
$events = $site['events'] ?? [];
$items = array_values(array_filter($events['items'] ?? [], 'is_array'));
$upcoming = array_values(array_filter($items, static fn (array $item): bool => strtolower((string) ($item['status'] ?? '')) === 'upcoming'));
$past = array_values(array_filter($items, static fn (array $item): bool => strtolower((string) ($item['status'] ?? '')) !== 'upcoming'));
$schedule = $site['schedule'] ?? [];
?>
<main class="events-main">
  <header class="about-hero events-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal">Upcoming <span class="ac">&middot;</span> Past Events</div>
        <h1>
          <span class="clip"><span>Events</span></span>
          <span class="clip"><span class="a">Calendar</span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($site['hero']['role'] ?? '') ?></p>
        <p class="about-lede reveal d3"><?= e($events['intro'] ?? 'Talks, workshops, and public-sector technology engagements.') ?></p>
        <div class="about-actions reveal d4">
          <a class="about-text-link" href="#upcoming-events">Upcoming</a>
          <a class="about-text-link" href="#past-events">Past Events</a>
        </div>
      </div>

      <aside class="events-summary-panel reveal d2">
        <span class="mono">Event Library</span>
        <strong>Video-led event cards</strong>
        <p>Each container can use a YouTube/Vimeo link or an uploaded video file, with event text placed below the media.</p>
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
                <span><?= e($item['status'] ?? 'Upcoming') ?></span>
                <em><?= e($item['meta'] ?? '') ?></em>
              </div>
              <h3><?= e($item['title'] ?? '') ?></h3>
              <p><?= e($item['description'] ?? '') ?></p>
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
                <span><?= e($item['status'] ?? 'Past') ?></span>
                <em><?= e($item['meta'] ?? '') ?></em>
              </div>
              <h3><?= e($item['title'] ?? '') ?></h3>
              <p><?= e($item['description'] ?? '') ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (! empty($events['note'])): ?>
      <p class="events-note"><?= e($events['note']) ?></p>
    <?php endif; ?>
  </section>

  <section class="about-closing events-closing">
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
