<?php
$identity = $site['identity'];
$hero = $site['hero'];
$profile = $site['profile'];
$research = $site['research'];
$schedule = $site['schedule'];
$researchMedia = array_values($research['media'] ?? []);
$heroImage = $researchMedia[0] ?? ($profile['image'] ?? $hero['image'] ?? []);

$recognitions = [
    [
        'year' => '2026',
        'type' => 'Felicitation',
        'title' => 'AI Conference & Workshop - Punjab School Education Board',
        'description' => 'Recognised during a public-sector AI conference and workshop focused on responsible adoption of artificial intelligence in institutions.',
        'image' => $researchMedia[0]['src'] ?? '',
        'image_alt' => $researchMedia[0]['alt'] ?? '',
    ],
    [
        'year' => '2026',
        'type' => 'Institutional Mandate',
        'title' => 'Nodal Officer for AI Governance, BBMB',
        'description' => 'Designated to lead BBMB alignment with the IndiaAI Mission and MeitY AI Governance Guidelines for accountable public-sector AI adoption.',
        'image' => '',
        'image_alt' => '',
    ],
    [
        'year' => '2026',
        'type' => 'Engagement',
        'title' => 'BBMB - 50 Years (1976-2026)',
        'description' => 'Participation in the Board\'s golden-jubilee programme on technology and the future of public infrastructure.',
        'image' => $profile['image']['src'] ?? '',
        'image_alt' => $profile['image']['alt'] ?? '',
    ],
    [
        'year' => 'Ongoing',
        'type' => 'Public-Sector Technology',
        'title' => 'Critical Infrastructure Systems Responsibility',
        'description' => 'Nearly a decade of work across government and public-sector undertakings, supporting secure software, data, procurement, and e-governance systems.',
        'image' => '',
        'image_alt' => '',
    ],
];

$standards = [
    [
        'title' => 'Verified before published',
        'description' => 'Formal awards, citations, and external recognitions should be added with source links, dates, and issuing institutions.',
    ],
    [
        'title' => 'Institutional context matters',
        'description' => 'Recognitions are presented with the public-sector mandate behind them, not as isolated badges without context.',
    ],
    [
        'title' => 'Public trust over volume',
        'description' => 'The page prioritises accurate, auditable recognition records over a long list of unverified claims.',
    ],
];
?>
<main class="awards-main">
  <header class="about-hero awards-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal">Awards <span class="ac">&middot;</span> Recognitions</div>
        <h1>
          <span class="clip"><span>Awards</span></span>
          <span class="clip"><span class="a">Recognitions</span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($hero['role'] ?? '') ?></p>
        <p class="about-lede reveal d3">A curated record of recognitions, institutional mandates, and public-sector engagements connected to AI governance, critical infrastructure technology, and responsible digital transformation.</p>
        <div class="about-actions reveal d4">
          <a class="about-text-link" href="#recognitions">View Recognitions</a>
          <button class="cta" type="button" data-schedule-open><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></button>
        </div>
      </div>

      <aside class="awards-hero-card reveal d2" aria-label="Recognition summary">
        <div class="awards-hero-image">
          <img src="<?= e(asset($heroImage['src'] ?? 'images/research-felicitation.jpg')) ?>" alt="<?= e($heroImage['alt'] ?? $identity['full_name']) ?>" />
        </div>
        <div class="awards-hero-meta">
          <span class="mono">Recognition Ledger</span>
          <strong>Verified milestones and public engagements</strong>
          <p>Formal awards can be added here with issuing body, date, citation, and source links.</p>
        </div>
      </aside>
    </div>

    <div class="about-stat-row awards-stat-row reveal d4">
      <div class="about-stat">
        <span class="about-stat-value">2026</span>
        <span class="about-stat-label">Recent public AI engagement</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value">BBMB</span>
        <span class="about-stat-label">Institutional AI governance mandate</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value">IndiaAI</span>
        <span class="about-stat-label">Mission and MeitY guideline alignment</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-value">Public</span>
        <span class="about-stat-label">Trust, auditability, and responsible adoption</span>
      </div>
    </div>
  </header>

  <section class="awards-ledger" id="recognitions">
    <div class="head">
      <h2>Awards & Recognitions</h2>
      <span class="rule reveal"></span>
    </div>

    <div class="awards-grid">
      <?php foreach ($recognitions as $index => $item): ?>
        <article class="award-card reveal d<?= e((string) min($index + 1, 4)) ?>">
          <?php if ($item['image'] !== ''): ?>
            <div class="award-card-media">
              <img src="<?= e(asset($item['image'])) ?>" alt="<?= e($item['image_alt']) ?>" loading="lazy" />
            </div>
          <?php endif; ?>
          <div class="award-card-body">
            <div class="award-card-top">
              <span><?= e($item['year']) ?></span>
              <em><?= e($item['type']) ?></em>
            </div>
            <h3><?= e($item['title']) ?></h3>
            <p><?= e($item['description']) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="awards-standards">
    <div class="awards-standards-copy">
      <span class="mono reveal">Editorial Standard</span>
      <h2 class="reveal d1">Recognition should be precise, sourced, and useful.</h2>
      <p class="reveal d2">This page is designed to grow into a verified record. Placeholder endorsements and unsourced claims should not be treated as awards.</p>
    </div>
    <div class="awards-standards-grid">
      <?php foreach ($standards as $index => $item): ?>
        <article class="awards-standard-card reveal d<?= e((string) min($index + 1, 4)) ?>">
          <span><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
          <h3><?= e($item['title']) ?></h3>
          <p><?= e($item['description']) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-closing awards-closing">
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
