<?php
$identity = $site['identity'];
$schedule = $site['schedule'];
$email = $identity['email'];
$mailto = 'mailto:' . $email . '?subject=' . rawurlencode($schedule['email_subject']);
?>
<section id="schedule" class="schedule">
  <div class="head" style="margin-bottom:0"><span class="mono"><?= e($schedule['eyebrow']) ?></span></div>
  <h2 class="grunge"><?= e($schedule['heading']) ?></h2>
  <p class="sub reveal d1"><?= e($schedule['description']) ?></p>
  <a class="connect" href="<?= e($mailto) ?>"><?= e($email) ?> &rarr;</a>
  <div class="row reveal d2">
    <div class="it"><div class="k">Connect</div><div class="v"><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div></div>
    <div class="it"><div class="k">LinkedIn</div><div class="v"><a href="<?= e($identity['linkedin']['url']) ?>" target="_blank" rel="noopener"><?= e($identity['linkedin']['label']) ?> &nearr;</a></div></div>
    <div class="it"><div class="k">Based in</div><div class="v"><?= e($identity['location']) ?></div></div>
  </div>
</section>
