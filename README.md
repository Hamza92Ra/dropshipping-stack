# DropshippingStack — Setup Guide

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

In phpMyAdmin or MySQL terminal:
```
source /path/to/database.sql
```

---

## Step 2 — Fill in config.php

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

---

## Step 3 — Upload files

Upload everything to your hosting public_html (or www) folder.
Make sure mod_rewrite is enabled on Apache for .htaccess to work.

---

## Step 4 — Change admin password

Log into /admin/login.php with:
- Username: admin
- Password: Admin@1234

Then immediately change your password via phpMyAdmin:
```sql
UPDATE admins SET password = '$2y$12$YOUR_NEW_HASH' WHERE username = 'admin';
```

Generate a bcrypt hash at: https://bcrypt-generator.com

---

## Step 5 — Add your real affiliate links

In the admin panel go to Tools → Edit each tool and replace:
`https://shopify.com/?ref=YOUR_ID`
with your actual affiliate referral URL.

---

## Step 6 — View on localhost (XAMPP/MAMP)

### XAMPP (Windows/Mac):
1. Install XAMPP from https://apachefriends.org
2. Copy project folder to `C:/xampp/htdocs/dropshipping/`
3. Start Apache + MySQL in XAMPP Control Panel
4. Open phpMyAdmin at http://localhost/phpmyadmin
5. Import database.sql
6. Change SITE_URL to `http://localhost/dropshipping`
7. Visit http://localhost/dropshipping

### MAMP (Mac):
1. Install MAMP from https://mamp.info
2. Copy folder to `/Applications/MAMP/htdocs/dropshipping/`
3. Start servers in MAMP
4. phpMyAdmin: http://localhost:8888/phpMyAdmin
5. Visit http://localhost:8888/dropshipping

### Laragon (Windows — recommended):
1. Install from https://laragon.org
2. Place folder in `C:/laragon/www/dropshipping/`
3. It auto-creates a virtual host: http://dropshipping.test
4. Use HeidiSQL to import the database
