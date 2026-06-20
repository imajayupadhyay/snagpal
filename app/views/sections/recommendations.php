<section class="recs" aria-label="Recommendations">
  <span class="lbl">Recommendations</span>
  <div class="marquee" id="marquee"></div>
  <div class="recs-cta">
    <button type="button" class="about-text-link light" data-recommend-open>Give a Recommendation</button>
  </div>
  <?php if (! empty($site['recommendations_note'])): ?>
    <span class="foot"><?= e($site['recommendations_note']) ?></span>
  <?php endif; ?>
</section>
