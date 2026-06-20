# Database Placeholder

This folder stores database setup files.

Completed:

- Added admin authentication migration at `migrations/001_create_admin_users.sql`.
- Added migration runner at `../scripts/migrate.php`.
- Added admin seeder at `../scripts/seed_admin.php`.

Run:

```bash
php portfolio/scripts/migrate.php
php portfolio/scripts/seed_admin.php
```
