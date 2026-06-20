<?php $research = $site['research']; ?>
<section id="research">
  <div class="head"><h2><?= e($research['heading']) ?></h2><span class="rule reveal"></span></div>
  <div class="res-media">
    <?php foreach ($research['media'] as $index => $media): ?>
      <figure class="reveal<?= $index > 0 ? ' d' . e((string) $index) : '' ?>">
        <img src="<?= e(asset($media['src'])) ?>" alt="<?= e($media['alt']) ?>" loading="lazy" />
        <figcaption><?= e($media['caption']) ?></figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
  <div class="res-list">
    <?php foreach ($research['items'] as $item): ?>
      <div class="res-row reveal">
        <span class="yr"><?= e($item['year']) ?></span>
        <div>
          <div class="ti"><?= e($item['title']) ?></div>
          <div class="ds"><?= e($item['description']) ?></div>
        </div>
        <span class="kind"><?= e($item['kind']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <p class="res-note"><?= e($research['note']) ?></p>
</section>
