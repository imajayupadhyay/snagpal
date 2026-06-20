<?php $expertise = $site['expertise']; ?>
<section id="expertise">
  <div class="head"><h2><?= e($expertise['heading']) ?></h2><span class="rule reveal"></span></div>
  <div class="exp-list">
    <?php foreach ($expertise['items'] as $item): ?>
      <div class="exp-row reveal">
        <div class="ix"><?= e($item['number']) ?></div>
        <h3><?= e($item['title']) ?></h3>
        <p><?= e($item['description']) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>
