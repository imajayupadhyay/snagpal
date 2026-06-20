<?php
$identity = $site['identity'];
$hero = $site['hero'];
$profile = $site['profile'];
$focus = $site['focus'];
$expertise = $site['expertise'];
$research = $site['research'];
$schedule = $site['schedule'];
$quotes = array_values($site['quotes'] ?? []);
$profileParagraphs = array_values($profile['paragraphs_html'] ?? []);
$focusItems = array_values($focus['items'] ?? []);
$expertiseItems = array_values($expertise['items'] ?? []);
$researchItems = array_values($research['items'] ?? []);
$researchMedia = array_values($research['media'] ?? []);
$stats = array_values($hero['stats'] ?? []);

$facts = [
    ['label' => 'Current role', 'value' => 'Nodal Officer for AI Governance, BBMB'],
    ['label' => 'Institution', 'value' => 'Bhakra Beas Management Board, Ministry of Power'],
    ['label' => 'Base', 'value' => $identity['location'] ?? 'Chandigarh, India'],
    ['label' => 'Academic grounding', 'value' => 'M.Tech, Computer Science Engineering, PEC Chandigarh'],
];

$pathItems = [
    [
        'label' => 'Public technology',
        'title' => 'Engineering for systems people depend on',
        'text' => 'At BBMB, Shweta works with the software, data, and infrastructure layer behind critical water and power assets, where reliability, security, and auditability have public consequences.',
    ],
    [
        'label' => 'AI governance',
        'title' => 'Turning national guidance into institutional practice',
        'text' => 'As Nodal Officer for AI Governance, she helps translate the IndiaAI Mission and MeitY AI Governance Guidelines into usable adoption pathways, risk thinking, and accountability guardrails.',
    ],
    [
        'label' => 'e-Governance',
        'title' => 'Digitising process without losing discipline',
        'text' => 'Her work spans enterprise platforms, procurement systems, eOffice, workflow modernization, and large-scale government digitisation with scrutiny and transparency built in.',
    ],
    [
        'label' => 'Public trust',
        'title' => 'Technology as governance infrastructure',
        'text' => 'Her approach treats data, procurement, security, and AI adoption as public responsibilities, not only technical deployments.',
    ],
];
?>
<main class="about-main">
  <header class="about-hero" id="top">
    <div class="about-hero-grid">
      <div class="about-hero-copy">
        <div class="mono kick reveal">About Shweta <span class="ac">&middot;</span> <?= e($hero['kicker_suffix'] ?? 'Public-Sector Technology') ?></div>
        <h1>
          <span class="clip"><span>About</span></span>
          <span class="clip"><span class="a">Shweta</span></span>
        </h1>
        <p class="about-role reveal d2"><?= e($hero['role'] ?? '') ?></p>
        <p class="about-lede reveal d3"><?= e($hero['lede'] ?? '') ?></p>
        <div class="about-actions reveal d4">
          <button class="cta" type="button" data-schedule-open><?= e($schedule['eyebrow'] ?? 'Schedule a Meet') ?></button>
        </div>
      </div>

      <aside class="about-identity-panel reveal d2" aria-label="Profile summary">
        <div class="about-portrait">
          <img src="<?= e(asset($profile['image']['src'] ?? 'images/profile-commemoration.jpg')) ?>" alt="<?= e($profile['image']['alt'] ?? $identity['full_name']) ?>" />
        </div>
        <div class="about-panel-body">
          <span class="mono">Current Mandate</span>
          <p><?= e($facts[0]['value']) ?></p>
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
        <span class="mono reveal">Profile</span>
        <h2 class="reveal d1"><?= e($profile['heading'] ?? 'About Shweta Nagpal') ?></h2>
        <?php if (! empty($profile['lead_html'])): ?>
          <p class="about-lead reveal d2"><?= $profile['lead_html'] ?></p>
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
      <h2>Work & Mandate</h2>
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
      <span class="mono reveal">Operating Principles</span>
      <h2 class="reveal d1">Governance discipline for public technology.</h2>
      <?php if ($quotes !== []): ?>
        <blockquote class="reveal d2"><?= e($quotes[0]) ?></blockquote>
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
        <h2><?= e($expertise['heading'] ?? 'Expertise') ?></h2>
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
      <h2><?= e($research['heading'] ?? 'Research & Engagement') ?></h2>
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

    <?php if (! empty($research['note'])): ?>
      <p class="res-note"><?= e($research['note']) ?></p>
    <?php endif; ?>
  </section>

  <section class="about-closing">
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
