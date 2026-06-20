<?php
$form = cohort_category_admin_normalize($form ?? cohort_category_admin_default());
$isEditing = ! empty($form['id']);
ob_start();
?>
<div class="dashboard-shell">
  <?php render('admin/partials/sidebar', ['active' => 'cohort-categories']); ?>

  <main class="dashboard-main">
    <header class="dashboard-top">
      <div>
        <p class="eyebrow">Taxonomy</p>
        <h1>Cohort Categories</h1>
      </div>
      <div class="top-actions">
        <a class="ghost-link" href="<?= e(admin_cohorts_url()) ?>">Back to Cohorts</a>
        <a class="ghost-link" href="<?= e(url_path('cohorts/')) ?>" target="_blank" rel="noopener">View Page</a>
        <form method="post" action="<?= e(url_path('sanchalak/logout.php')) ?>">
          <?= csrf_field() ?>
          <button class="ghost-btn" type="submit">Logout</button>
        </form>
      </div>
    </header>

    <p class="hint">Categories appear as filter tabs on the public Cohorts page. Assign a category to each cohort from the Cohorts editor. A category with no published cohorts won't show a tab.</p>

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

    <section class="metric-grid">
      <article>
        <span>Total</span>
        <strong><?= e((string) ($overview['total'] ?? 0)) ?></strong>
        <p>Categories available as cohort tabs.</p>
      </article>
    </section>

    <section class="panel">
      <div class="panel-head">
        <div>
          <p class="eyebrow">Library</p>
          <h2>Existing Categories</h2>
        </div>
        <span class="muted"><?= e((string) count($categories)) ?> entries</span>
      </div>

      <?php if ($categories === []): ?>
        <p class="empty-state">No categories yet. Use the form below to add the first one.</p>
      <?php else: ?>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Slug</th>
                <th>Cohorts</th>
                <th>Order</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categories as $category): ?>
                <tr>
                  <td><strong><?= e($category['name'] ?? '') ?></strong></td>
                  <td><code><?= e($category['slug'] ?? '') ?></code></td>
                  <td><?= e((string) ($category['cohort_count'] ?? 0)) ?></td>
                  <td><?= e((string) ($category['sort_order'] ?? 0)) ?></td>
                  <td>
                    <div class="table-actions">
                      <a class="ghost-link ghost-link-small" href="<?= e(admin_cohort_categories_url(['edit' => (int) $category['id']])) ?>">Edit</a>
                      <form method="post" action="<?= e(admin_cohort_categories_url()) ?>" onsubmit="return confirm('Delete this category? Cohorts using it will become uncategorized.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= e((string) $category['id']) ?>">
                        <button class="ghost-btn small danger" type="submit">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <form class="content-form" method="post" action="<?= e(admin_cohort_categories_url($isEditing ? ['edit' => (int) $form['id']] : [])) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_category">
      <input type="hidden" name="category_id" value="<?= e((string) ($form['id'] ?? '')) ?>">

      <section class="panel form-panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow"><?= $isEditing ? 'Edit' : 'New' ?></p>
            <h2><?= $isEditing ? 'Edit Category' : 'Create Category' ?></h2>
          </div>
        </div>
        <div class="form-grid">
          <div class="field">
            <label for="category_name">Name</label>
            <input id="category_name" name="name" value="<?= e($form['name']) ?>" maxlength="120" required placeholder="AI Governance">
          </div>
          <div class="field">
            <label for="category_slug">URL slug</label>
            <input id="category_slug" name="slug" value="<?= e($form['slug']) ?>" maxlength="140" placeholder="ai-governance">
            <p class="hint">Leave blank to generate it from the name.</p>
          </div>
          <div class="field">
            <label for="category_sort">Sort order</label>
            <input id="category_sort" type="number" name="sort_order" value="<?= e((string) $form['sort_order']) ?>" step="1">
            <p class="hint">Lower numbers appear first among the tabs.</p>
          </div>
        </div>
      </section>

      <div class="sticky-actions">
        <button type="submit"><?= $isEditing ? 'Save Category' : 'Create Category' ?></button>
        <a class="ghost-link" href="<?= e(admin_cohort_categories_url()) ?>">Reset Form</a>
      </div>
    </form>
  </main>
</div>
<?php
$content = ob_get_clean();
$bodyClass = 'admin-page admin-cohort-categories-page';
require APP_PATH . '/views/admin/layout.php';
