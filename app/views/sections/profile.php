<?php $profile = $site['profile']; ?>
<section id="profile">
  <div class="head"><h2><?= e($profile['heading']) ?></h2><span class="rule reveal"></span></div>
  <div class="profile-grid">
    <div class="profile-img reveal">
      <img src="<?= e(asset($profile['image']['src'])) ?>" alt="<?= e($profile['image']['alt']) ?>" loading="lazy" />
    </div>
    <div class="profile-body">
      <p class="lead reveal"><?= $profile['lead_html'] ?></p>
      <?php foreach ($profile['paragraphs_html'] as $index => $paragraph): ?>
        <p class="body reveal d<?= e((string) ($index + 1)) ?>"><?= $paragraph ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</section>
