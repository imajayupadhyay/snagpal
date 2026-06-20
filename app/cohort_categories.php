<?php

declare(strict_types=1);

function cohort_category_slug(string $value): string
{
    $slug = cohort_slug($value);

    return $slug !== 'cohort' ? $slug : 'category';
}

function cohort_category_admin_default(): array
{
    return [
        'id' => null,
        'name' => '',
        'slug' => '',
        'sort_order' => 0,
    ];
}

function cohort_category_admin_all(): array
{
    return db()->query(
        'SELECT c.*, (
            SELECT COUNT(*) FROM cohorts WHERE cohorts.category_id = c.id
         ) AS cohort_count
         FROM cohort_categories c
         ORDER BY c.sort_order ASC, c.name ASC'
    )->fetchAll();
}

function cohort_category_admin_counts(): array
{
    $statement = db()->query('SELECT COUNT(*) FROM cohort_categories');

    return ['total' => (int) $statement->fetchColumn()];
}

function cohort_category_admin_find(int $id): ?array
{
    if ($id <= 0) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM cohort_categories WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);
    $row = $statement->fetch();

    return is_array($row) ? cohort_category_admin_normalize($row) : null;
}

function cohort_category_admin_normalize(array $category): array
{
    return array_merge(cohort_category_admin_default(), [
        'id' => isset($category['id']) ? (int) $category['id'] : null,
        'name' => (string) ($category['name'] ?? ''),
        'slug' => (string) ($category['slug'] ?? ''),
        'sort_order' => (int) ($category['sort_order'] ?? 0),
        'cohort_count' => (int) ($category['cohort_count'] ?? 0),
    ]);
}

function cohort_category_admin_from_post(array $post, ?array $current = null): array
{
    $current = $current !== null ? cohort_category_admin_normalize($current) : cohort_category_admin_default();
    $name = homepage_text($post['name'] ?? '');
    $slug = homepage_text($post['slug'] ?? '');

    if ($slug === '' && $name !== '') {
        $slug = $name;
    }

    return [
        'id' => $current['id'],
        'name' => $name,
        'slug' => cohort_category_slug($slug),
        'sort_order' => (int) ($post['sort_order'] ?? 0),
    ];
}

function cohort_category_admin_validate(array $category, ?int $ignoreId = null): array
{
    $errors = [];

    if ($category['name'] === '') {
        $errors[] = 'Add a category name.';
    }

    if ($category['slug'] === '') {
        $errors[] = 'Add a URL slug.';
    } elseif (cohort_category_admin_slug_exists((string) $category['slug'], $ignoreId)) {
        $errors[] = 'That URL slug is already used by another category.';
    }

    return $errors;
}

function cohort_category_admin_slug_exists(string $slug, ?int $ignoreId = null): bool
{
    $sql = 'SELECT id FROM cohort_categories WHERE slug = :slug';
    $params = ['slug' => $slug];

    if ($ignoreId !== null && $ignoreId > 0) {
        $sql .= ' AND id <> :id';
        $params['id'] = $ignoreId;
    }

    $sql .= ' LIMIT 1';
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return (bool) $statement->fetchColumn();
}

function cohort_category_admin_save(array $category): int
{
    if (! empty($category['id'])) {
        $statement = db()->prepare(
            'UPDATE cohort_categories SET name = :name, slug = :slug, sort_order = :sort_order WHERE id = :id'
        );
        $statement->execute([
            'name' => (string) $category['name'],
            'slug' => (string) $category['slug'],
            'sort_order' => (int) $category['sort_order'],
            'id' => (int) $category['id'],
        ]);

        return (int) $category['id'];
    }

    $statement = db()->prepare(
        'INSERT INTO cohort_categories (name, slug, sort_order) VALUES (:name, :slug, :sort_order)'
    );
    $statement->execute([
        'name' => (string) $category['name'],
        'slug' => (string) $category['slug'],
        'sort_order' => (int) $category['sort_order'],
    ]);

    return (int) db()->lastInsertId();
}

function cohort_category_admin_delete(int $id): array
{
    if ($id <= 0) {
        return ['Invalid category selected.'];
    }

    $statement = db()->prepare('DELETE FROM cohort_categories WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $id]);

    return $statement->rowCount() > 0 ? [] : ['That category no longer exists.'];
}

function cohort_categories_lookup(): array
{
    static $lookup = null;

    if ($lookup !== null) {
        return $lookup;
    }

    try {
        $rows = db()->query('SELECT id, name, slug FROM cohort_categories ORDER BY sort_order ASC, name ASC')->fetchAll();
    } catch (Throwable) {
        $rows = [];
    }

    $lookup = [];

    foreach ($rows as $row) {
        $lookup[(int) $row['id']] = [
            'name' => (string) $row['name'],
            'slug' => (string) $row['slug'],
        ];
    }

    return $lookup;
}

function cohort_categories_for_filter(array $items): array
{
    $counts = [];

    foreach ($items as $item) {
        $slug = (string) ($item['category_slug'] ?? '');

        if ($slug === '') {
            continue;
        }

        if (! isset($counts[$slug])) {
            $counts[$slug] = [
                'slug' => $slug,
                'name' => (string) ($item['category_name'] ?? $slug),
                'count' => 0,
            ];
        }

        $counts[$slug]['count']++;
    }

    $lookup = cohort_categories_lookup();
    $ordered = [];

    foreach ($lookup as $category) {
        if (isset($counts[$category['slug']])) {
            $ordered[] = $counts[$category['slug']];
            unset($counts[$category['slug']]);
        }
    }

    return array_merge($ordered, array_values($counts));
}
