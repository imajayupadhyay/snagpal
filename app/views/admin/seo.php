<?php
$canonical = is_array($settings['canonical'] ?? null) ? $settings['canonical'] : [];
$verification = is_array($settings['verification'] ?? null) ? $settings['verification'] : [];
$person = is_array($settings['person'] ?? null) ? $settings['person'] : [];
$socialLinks = is_array($settings['social_links'] ?? null) ? $settings['social_links'] : [];
$awards = is_array($settings['awards'] ?? null) ? $settings['awards'] : [];
$orgTypes = ['GovernmentOrganization', 'Organization', 'EducationalOrganization'];

$imagePreview = static function (string $path): void {
    if (trim($path) === '') {
        return;
    }
    ?>
    <div class="image-preview"><img src="<?= e(asset($path)) ?>" alt="Current image" loading="lazy"></div>
    <?php
};
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'seo']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">SEO</p>
        <h1>SEO &amp; Site Settings</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(url_path('sitemap.xml')) ?>" target="_blank" rel="noopener">View sitemap</a>
        <a class="ghost-link" href="<?= e(url_path('robots.txt')) ?>" target="_blank" rel="noopener">View robots.txt</a>
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">View Site</a>
      </div>
    </header>

    <?php if ($success): ?>
      <div class="notice success" role="status"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="notice error" role="alert">
        <?php foreach ($errors as $error): ?>
          <p><?= e($error) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <p class="hint">These settings power the share cards (Open Graph / Twitter), canonical URLs, structured data, robots.txt, and the sitemap across every public page. Individual pages can still override their title, description, and OG image from their own editor.</p>

    <nav class="section-jump" id="sectionJump" aria-label="Jump to section"></nav>

    <form class="content-form" method="post" action="<?= e(admin_seo_url()) ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Global</p><h2>Site Settings</h2></div></div>
        <div class="form-grid">
          <div class="field">
            <label for="site_name">Site name</label>
            <input id="site_name" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>" maxlength="120">
            <p class="hint">Used for <code>og:site_name</code> and the WebSite schema.</p>
          </div>
          <div class="field small">
            <label for="locale">Locale</label>
            <input id="locale" name="locale" value="<?= e($settings['locale'] ?? 'en_US') ?>" maxlength="10" placeholder="en_US">
          </div>
          <div class="field">
            <label for="default_robots">Default meta robots</label>
            <input id="default_robots" name="default_robots" value="<?= e($settings['default_robots'] ?? '') ?>" maxlength="160">
            <p class="hint">Site-wide indexing default, e.g. <code>index, follow, max-image-preview:large</code>.</p>
          </div>
          <div class="field">
            <label for="twitter_handle">Default social handle (X / Twitter)</label>
            <input id="twitter_handle" name="twitter_handle" value="<?= e($settings['twitter_handle'] ?? '') ?>" maxlength="40" placeholder="@handle">
          </div>
          <div class="field">
            <label for="analytics_id">Analytics / GTM ID</label>
            <input id="analytics_id" name="analytics_id" value="<?= e($settings['analytics_id'] ?? '') ?>" maxlength="40" placeholder="G-XXXXXXXX or GTM-XXXXXX">
            <p class="hint">Accepts a GA4 <code>G-</code>, Universal Analytics <code>UA-</code>, or Tag Manager <code>GTM-</code> ID.</p>
          </div>
          <div class="field full">
            <label for="default_og_image_upload">Default share image (1200×630)</label>
            <?php $imagePreview((string) ($settings['default_og_image'] ?? '')); ?>
            <input type="file" id="default_og_image_upload" name="default_og_image_upload" accept="image/png,image/jpeg,image/webp">
            <input type="hidden" name="default_og_image" value="<?= e($settings['default_og_image'] ?? '') ?>">
            <p class="hint">Fallback share card for any page without its own OG image. Upload a PNG/JPG/WebP under 1&nbsp;MB at 1200×630.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Canonical</p><h2>Domain &amp; Redirects</h2></div></div>
        <div class="form-grid">
          <div class="field">
            <label for="canonical_host">Canonical domain</label>
            <input id="canonical_host" name="canonical[host]" value="<?= e($canonical['host'] ?? '') ?>" maxlength="120" placeholder="shwetanagpal.com">
            <p class="hint">Bare domain, no <code>https://</code> or <code>www.</code> — those are set by the toggles below.</p>
          </div>
          <label class="header-check">
            <input type="checkbox" name="canonical[https]" value="1"<?= ! empty($canonical['https']) ? ' checked' : '' ?>>
            <span>Force HTTPS</span>
          </label>
          <label class="header-check">
            <input type="checkbox" name="canonical[www]" value="1"<?= ! empty($canonical['www']) ? ' checked' : '' ?>>
            <span>Use the <code>www.</code> prefix</span>
          </label>
          <label class="header-check">
            <input type="checkbox" name="canonical[trailing_slash]" value="1"<?= ! empty($canonical['trailing_slash']) ? ' checked' : '' ?>>
            <span>Add a trailing slash to canonical URLs</span>
          </label>
        </div>
        <p class="hint">The matching 301 redirect rules (force HTTPS, drop <code>www.</code>) live in <code>public/.htaccess</code> and only run on the live domain — localhost is left alone.</p>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Verification</p><h2>Search Engines</h2></div></div>
        <div class="form-grid">
          <div class="field">
            <label for="verify_google">Google Search Console token</label>
            <input id="verify_google" name="verification[google]" value="<?= e($verification['google'] ?? '') ?>" maxlength="120">
            <p class="hint">Paste only the <code>content</code> value from the verification meta tag.</p>
          </div>
          <div class="field">
            <label for="verify_bing">Bing Webmaster token</label>
            <input id="verify_bing" name="verification[bing]" value="<?= e($verification['bing'] ?? '') ?>" maxlength="120">
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Crawling</p><h2>robots.txt</h2></div></div>
        <div class="form-grid">
          <div class="field full">
            <label for="robots_txt">robots.txt content</label>
            <textarea id="robots_txt" name="robots_txt" rows="6" placeholder="Leave blank to auto-generate from the canonical domain."><?= e($settings['robots_txt'] ?? '') ?></textarea>
            <p class="hint">Leave blank to auto-generate (allows everything, disallows <code>/sanchalak/</code>, and links the sitemap). Served live at <code>/robots.txt</code>.</p>
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Structured Data</p><h2>Person Schema</h2></div></div>
        <label class="header-check">
          <input type="checkbox" name="person[enabled]" value="1"<?= ! empty($person['enabled']) ? ' checked' : '' ?>>
          <span>Emit Person JSON-LD on every page</span>
        </label>
        <div class="form-grid">
          <div class="field">
            <label for="person_name">Full name</label>
            <input id="person_name" name="person[name]" value="<?= e($person['name'] ?? '') ?>" maxlength="120">
          </div>
          <div class="field">
            <label for="person_job">Job title</label>
            <input id="person_job" name="person[job_title]" value="<?= e($person['job_title'] ?? '') ?>" maxlength="160">
          </div>
          <div class="field">
            <label for="person_org">Organization name</label>
            <input id="person_org" name="person[org_name]" value="<?= e($person['org_name'] ?? '') ?>" maxlength="160">
          </div>
          <div class="field">
            <label for="person_org_url">Organization URL</label>
            <input id="person_org_url" name="person[org_url]" value="<?= e($person['org_url'] ?? '') ?>" maxlength="300">
          </div>
          <div class="field small">
            <label for="person_org_type">Organization type</label>
            <select id="person_org_type" name="person[org_type]">
              <?php foreach ($orgTypes as $type): ?>
                <option value="<?= e($type) ?>"<?= ($person['org_type'] ?? '') === $type ? ' selected' : '' ?>><?= e($type) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field full">
            <label for="person_image_upload">Profile image</label>
            <?php $imagePreview((string) ($person['image'] ?? '')); ?>
            <input type="file" id="person_image_upload" name="person_image_upload" accept="image/png,image/jpeg,image/webp">
            <input type="hidden" name="person[image]" value="<?= e($person['image'] ?? '') ?>">
          </div>
          <div class="field full">
            <label for="person_description">Short bio / description</label>
            <textarea id="person_description" name="person[description]" rows="3"><?= e($person['description'] ?? '') ?></textarea>
          </div>
          <div class="field">
            <label for="person_email">Email</label>
            <input id="person_email" name="person[email]" value="<?= e($person['email'] ?? '') ?>" maxlength="160">
          </div>
          <label class="header-check">
            <input type="checkbox" name="person[email_public]" value="1"<?= ! empty($person['email_public']) ? ' checked' : '' ?>>
            <span>Show the email publicly in the schema</span>
          </label>
          <div class="field">
            <label for="person_tel">Telephone</label>
            <input id="person_tel" name="person[telephone]" value="<?= e($person['telephone'] ?? '') ?>" maxlength="40">
          </div>
          <div class="field small">
            <label for="person_city">City / Locality</label>
            <input id="person_city" name="person[address_locality]" value="<?= e($person['address_locality'] ?? '') ?>" maxlength="120">
          </div>
          <div class="field small">
            <label for="person_region">Region / State</label>
            <input id="person_region" name="person[address_region]" value="<?= e($person['address_region'] ?? '') ?>" maxlength="120">
          </div>
          <div class="field small">
            <label for="person_country">Country code</label>
            <input id="person_country" name="person[address_country]" value="<?= e($person['address_country'] ?? '') ?>" maxlength="4" placeholder="IN">
          </div>
        </div>
      </section>

      <section class="panel form-panel">
        <div class="panel-head"><div><p class="eyebrow">Profiles</p><h2>Social &amp; Awards</h2></div></div>
        <div class="form-grid">
          <div class="field full">
            <label for="social_links">Social / profile links (sameAs)</label>
            <textarea id="social_links" name="social_links" rows="5" placeholder="https://www.linkedin.com/in/..."><?= e(implode("\n", $socialLinks)) ?></textarea>
            <p class="hint">One URL per line — LinkedIn, X, any official/government bio. This is how search engines link the entity together.</p>
          </div>
          <div class="field full">
            <label for="awards">Awards</label>
            <textarea id="awards" name="awards" rows="5" placeholder="One award per line"><?= e(implode("\n", $awards)) ?></textarea>
            <p class="hint">One award per line — feeds the <code>award[]</code> array on the Person schema.</p>
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit">Save SEO Settings</button>
        <a class="ghost-link" href="<?= e(url_path()) ?>" target="_blank" rel="noopener">Preview Site</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-seo-page';
require APP_PATH . '/views/admin/layout.php';
