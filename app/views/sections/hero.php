<?php $hero = $site['hero']; ?>
<header class="hero" id="top">
  <div class="hero-grid">
    <div>
      <div class="mono kick reveal"><?= e($hero['kicker']) ?> <span class="ac">&middot;</span> <?= e($hero['kicker_suffix']) ?></div>
      <h1>
        <span class="clip"><span><?= e($site['identity']['first_name']) ?></span></span>
        <span class="clip"><span class="a"><?= e($site['identity']['last_name']) ?></span></span>
      </h1>
      <p class="role reveal d2"><?= e($hero['role']) ?></p>
      <p class="lede reveal d3"><?= e($hero['lede']) ?></p>
    </div>
    <div class="hero-img reveal d2" id="heroImg">
      <img src="<?= e(asset($hero['image']['src'])) ?>" alt="<?= e($hero['image']['alt']) ?>" />
    </div>
  </div>
  <div class="hero-meta reveal d4">
    <?php foreach ($hero['stats'] as $stat): ?>
      <div class="m"><div class="v"><?= e($stat['value']) ?></div><div class="k"><?= e($stat['label']) ?></div></div>
    <?php endforeach; ?>
  </div>
</header>
