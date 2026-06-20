# snagpal

Portfolio website and admin panel for Shweta Nagpal — a single-page public site with a database-backed content manager, meeting scheduling, and email notifications. Built in plain PHP (no framework, no Composer dependencies) with MySQL.

## Features

- Public one-page portfolio (hero, profile, expertise, focus areas, research, recent-cohorts video slider, schedule).
- Secure admin panel to manage all homepage content, with image/video uploads and a WYSIWYG editor.
- Meeting scheduling: visitors request a slot; admin confirms it. Bookings start **pending** and become **confirmed** by an admin.
- Email notifications over SMTP (visitor + admin on request, visitor on confirmation).

## Requirements

- PHP 8.1+
- MySQL 5.7+ / MariaDB

## Setup

```bash
# 1. Configure environment
cp .env.example .env
#    then edit .env with your DB credentials and MAIL_PASSWORD

# 2. Create the database tables
php scripts/migrate.php

# 3. Seed the admin user and homepage content
php scripts/seed_admin.php
php scripts/seed_homepage.php --force

# 4. Run locally
php -S localhost:8000 -t public
```

Then open:

- Site: http://localhost:8000
- Admin login: http://localhost:8000/sanchalak/

## Configuration

All configuration lives in `.env` (copy from `.env.example`):

- `DB_*` — database connection
- `APP_URL` — absolute site URL (used in email links)
- `MAIL_*` — SMTP settings (Hostinger: `smtp.hostinger.com`, ssl/465 or tls/587)

Verify email delivery with:

```bash
php scripts/test_mail.php you@example.com
```

## Structure

```
app/        application code (config, views, helpers, mailer, scheduling)
database/   SQL migrations
public/     web root (index.php, assets, admin)
scripts/    CLI tools (migrate, seeders, mail test)
storage/    sessions (gitignored)
```
