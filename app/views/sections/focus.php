<?php $focus = $site['focus']; ?>
<section id="focus" class="focus">
  <div class="head"><h2><?= e($focus['heading']) ?></h2><span class="rule reveal"></span></div>
  <div class="focus-grid">
    <?php foreach ($focus['items'] as $index => $item): ?>
      <div class="focus-card reveal<?= $index > 0 ? ' d' . e((string) $index) : '' ?>">
        <span class="fbar"></span>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e($item['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
