# DropshippingStack — Setup Guide

A full-stack PHP platform that helps beginners start and grow a dropshipping business.

## Table of Contents

- [Requirements](#requirements)
- [Folder Structure](#folder-structure)
- [Step 1 — Run the database](#step-1--run-the-database)
- [Step 2 — Fill in config.php](#step-2--fill-in-configphp)
- [Step 3 — Upload files](#step-3--upload-files)
- [Step 4 — Change the admin password](#step-4--change-the-admin-password)
- [Step 5 — Add your real affiliate links](#step-5--add-your-real-affiliate-links)
- [Step 6 — Run it locally (XAMPP/MAMP/Laragon)](#step-6--run-it-locally-xamppmamplaragon)
- [Security Notes](#security-notes)
- [License](#license)

---

## Requirements

- PHP 7.4+ (8.x recommended)
- MySQL 5.7+ or MariaDB
- Apache with `mod_rewrite` enabled (for `.htaccess` routing)
- A hosting environment or local stack (XAMPP, MAMP, or Laragon)

---

## Folder Structure

```
/dropshippingstack.com/          ← your web root
├── config.php
├── index.php                    ← homepage
├── header.php
├── footer.php
├── go.php                       ← affiliate click tracker
├── search.php
├── submit.php
├── about.php
├── privacy.php
├── affiliate-disclosure.php
├── .htaccess
├── partials/
│   └── tool-card.php
├── tool/
│   └── index.php                ← /tool/shopify
├── category/
│   └── index.php                ← /category/store-builders
├── api/
│   └── search.php               ← live search JSON
├── admin/
│   ├── index.php                ← dashboard
│   ├── login.php
│   ├── logout.php
│   ├── tools/
│   │   ├── index.php
│   │   └── edit.php
│   ├── reviews/
│   │   └── index.php
│   ├── submissions/
│   │   └── index.php
│   └── partials/
│       ├── head.php
│       ├── sidebar.php
│       └── foot.php
└── assets/
    ├── css/style.css
    └── js/main.js
```

---

## Step 1 — Run the database

Import the schema in phpMyAdmin, or from the MySQL terminal:

```sql
source /path/to/database.sql
```

> Replace `/path/to/database.sql` with the actual path to the SQL file in this repo.

---

## Step 2 — Fill in config.php

Copy `config.example.php` to `config.php`, then set your own values:

```php
@define('DB_USER', 'your_db_username');
@define('DB_PASS', 'your_db_password');
@define('SITE_URL', 'https://yourdomain.com');
@define('SITE_EMAIL', 'you@yourdomain.com');
@define('SECRET_KEY', 'generate-64-random-chars-here');
```

Generate a secret key:

```bash
openssl rand -hex 32
```

> **Never commit `config.php` with real credentials.** Keep it out of version control (it should already be listed in `.gitignore`).

---

## Step 3 — Upload files

Upload everything to your hosting `public_html` (or `www`) folder.

Make sure `mod_rewrite` is enabled on Apache so `.htaccess` routing works correctly.

---

## Step 4 — Change the admin password

Log into `/admin/login.php` with the default credentials, then **change the password immediately**:

- Username: `admin`
- Password: *(set during database import — see `database.sql`)*

Update it via phpMyAdmin or SQL:

```sql
UPDATE admins SET password = '$2y$12$YOUR_NEW_HASH' WHERE username = 'admin';
```

Generate a bcrypt hash at [bcrypt-generator.com](https://bcrypt-generator.com), or with PHP:

```php
echo password_hash('your-new-password', PASSWORD_BCRYPT);
```

---

## Step 5 — Add your real affiliate links

In the admin panel, go to **Tools → Edit** for each tool and replace the placeholder URL:

```
https://shopify.com/?ref=YOUR_ID
```

with your actual affiliate referral link.

---

## Step 6 — Run it locally (XAMPP/MAMP/Laragon)

### XAMPP (Windows/Mac)

1. Install XAMPP from [apachefriends.org](https://apachefriends.org)
2. Copy the project folder to `C:/xampp/htdocs/dropshipping/`
3. Start Apache + MySQL in the XAMPP Control Panel
4. Open phpMyAdmin at `http://localhost/phpmyadmin`
5. Import `database.sql`
6. Set `SITE_URL` to `http://localhost/dropshipping`
7. Visit `http://localhost/dropshipping`

### MAMP (Mac)

1. Install MAMP from [mamp.info](https://mamp.info)
2. Copy the folder to `/Applications/MAMP/htdocs/dropshipping/`
3. Start the servers in MAMP
4. phpMyAdmin: `http://localhost:8888/phpMyAdmin`
5. Visit `http://localhost:8888/dropshipping`

### Laragon (Windows — recommended)

1. Install from [laragon.org](https://laragon.org)
2. Place the folder in `C:/laragon/www/dropshipping/`
3. Laragon auto-creates a virtual host: `http://dropshipping.test`
4. Use HeidiSQL to import the database

---

## Security Notes

- Change the default admin password before deploying anywhere public.
- Keep `config.php` and any real API/affiliate credentials out of source control.
- Use a strong, randomly generated `SECRET_KEY`.
- Run `composer audit` (if using Composer) or otherwise keep the bundled PHPMailer library up to date.

---

## License

*(Add your license here — e.g. MIT, or "All rights reserved" if this is a private/commercial project.)*