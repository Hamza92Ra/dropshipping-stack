# DropshippingStack

A full-stack PHP + MySQL platform for discovering, comparing, and managing the tools used to run a dropshipping business — think "Product Hunt meets G2," purpose-built for the dropshipping niche, with an affiliate-link monetization layer and an AI chat assistant baked in.

Visitors browse a curated directory of tools (store builders, suppliers, ad spy tools, email/SMS, reviews, analytics, etc.), compare them side by side, estimate real costs with a calculator, build a personal "stack," and get a step-by-step roadmap. Every outbound link is tracked and can carry your affiliate ID.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Folder Structure](#folder-structure)
- [Quick Start](#quick-start)
  - [1. Get the code](#1-get-the-code)
  - [2. Create the database](#2-create-the-database)
  - [3. Configure the app](#3-configure-the-app)
  - [4. Point a server at it](#4-point-a-server-at-it)
  - [5. Log into the admin panel](#5-log-into-the-admin-panel)
- [Running Locally (XAMPP / MAMP / Laragon)](#running-locally-xampp--mamp--laragon)
- [Database Schema](#database-schema)
- [Key Routes](#key-routes)
- [The AI Chat Assistant (Groq)](#the-ai-chat-assistant-groq)
- [Adding Your Affiliate Links](#adding-your-affiliate-links)
- [Security Notes](#security-notes)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## Features

- **Tool directory** — categorized listings (store builders, suppliers, ad spy tools, analytics, email/SMS, support, etc.) with logos, ratings, and pricing.
- **Search** — live/instant search over the tool catalog (`search.php` + `api/search.php`).
- **Compare** — side-by-side comparison of any two tools (`compare.php`).
- **Cost calculator** — estimates what a stack will actually cost against expected monthly revenue (`calculator.php`).
- **Stack builder** — logged-in users assemble and save a personal toolstack (`stack-builder.php` → `user_stack` table).
- **Roadmap** — a saved, step-by-step launch plan per user (`roadmap.php` → `user_roadmap` table).
- **Bookmarks / saved tools** — one-click save, viewable from a personal dashboard (`bookmarks.php`, `dashboard.php` → `bookmarks` table).
- **Price alerts** — opt in to be notified when a tracked tool's price changes (`price-alerts.php` + `check-price-changes.php` → `price_alerts` / `price_history` tables).
- **Upvotes & reviews** — community voting and written reviews per tool (`upvote.php`, `admin/reviews/` → `upvotes` / `reviews` tables).
- **User submissions** — visitors can submit new tools for review before they go live (`submit.php` → `submissions` table, moderated at `admin/submissions/`).
- **Accounts** — registration, email verification, login/logout, forgot/reset password (`register.php`, `verify-email.php`, `login.php`, `forgot-password.php`, `reset-password.php`) via PHPMailer.
- **Affiliate click tracking** — every outbound tool link is routed through `go.php`, logged to the `clicks` table, and redirected to the real affiliate URL.
- **AI chat widget** — a floating assistant (`assets/js/chat-widget.js` → `api/chat-api.php`) that answers questions about tools, backed by the Groq API (free tier, OpenAI-compatible).
- **Admin panel** — manage tools, categories, reviews, and submissions, all gated behind `admin/login.php`.
- **Dark mode**, SEO-friendly slugs, a dynamic `sitemap.php`, and `robots.txt`.

---

## Tech Stack

| Layer      | Technology |
|------------|------------|
| Language   | PHP 7.4+ (8.x recommended), procedural with some OOP (PHPMailer) |
| Database   | MySQL 5.7+ / MariaDB, accessed via PDO |
| Web server | Apache with `mod_rewrite` (`.htaccess` handles all pretty URLs) |
| Frontend   | Vanilla HTML/CSS/JS — no build step, no framework |
| Mail       | Bundled PHPMailer (SMTP, e.g. Gmail app passwords) |
| AI         | Groq API (free tier, no billing) for the chat widget |

---

## Requirements

- PHP 7.4+ (8.x recommended) with the `pdo_mysql` extension enabled
- MySQL 5.7+ or MariaDB
- Apache with `mod_rewrite` enabled (the whole site relies on `.htaccess` routing)
- A hosting environment or local stack (XAMPP, MAMP, or Laragon)
- (Optional) A free [Groq](https://console.groq.com) API key if you want the chat widget to work
- (Optional) An SMTP account (e.g. Gmail + app password) if you want verification/reset emails to send

---

## Folder Structure

```
/dropshipping/                     ← web root
├── config.php                     ← main app config (git-ignored — see config.example.php)
├── config.example.php             ← template to copy from
├── index.php                      ← homepage
├── header.php / footer.php        ← shared layout
├── go.php                         ← affiliate click tracker + redirect (logs to `clicks`)
├── search.php                     ← search results page
├── compare.php                    ← "compare two tools" page
├── calculator.php                 ← cost calculator
├── stack-builder.php              ← personal tool-stack builder
├── roadmap.php / save-roadmap.php ← saved launch roadmap
├── bookmarks.php                  ← AJAX endpoint: save/unsave a tool
├── dashboard.php                  ← "my saved tools" (logged-in users)
├── price-alerts.php               ← AJAX endpoint: toggle price alerts
├── check-price-changes.php        ← cron/manual script to detect price changes
├── upvote.php                     ← AJAX endpoint: upvote a tool
├── submit.php                     ← public "submit a tool" form
├── register.php / login.php / logout.php
├── forgot-password.php / reset-password.php
├── verify-email.php / resend-verification.php / send-verification.php
├── profile.php                    ← account settings
├── about.php / privacy.php / politique.php / affiliate-disclosure.php
├── sitemap.php                    ← dynamic XML sitemap
├── robots.txt
├── .htaccess                      ← HTTPS redirect, security headers, pretty-URL rewrites
├── dropshipping_stack.sql         ← full database schema (import this first)
│
├── partials/
│   └── tool-card.php              ← reusable tool-listing card
│
├── tool/
│   └── index.php                  ← /tool/{slug} — single tool page
│
├── category/
│   └── index.php                  ← /category/{slug} — category listing
│
├── api/
│   ├── search.php                 ← live/AJAX search endpoint
│   ├── chat-api.php                ← AI chat widget backend (Groq)
│   ├── llm-config.php              ← Groq API key / chat config
│   ├── db-config.php               ← standalone DB config used by some API endpoints
│   └── db-config.example.php       ← template to copy from
│
├── admin/
│   ├── index.php                  ← admin dashboard
│   ├── login.php / logout.php
│   ├── set-admin-password.php     ← one-time helper to (re)hash the admin password
│   ├── tools/                     ← add/edit/list tools
│   ├── reviews/                   ← moderate reviews
│   ├── submissions/               ← approve/reject user-submitted tools
│   └── partials/                  ← admin layout (head/sidebar/foot)
│
├── phpmailer/src/                 ← bundled PHPMailer library (SMTP sending)
│
└── assets/
    ├── css/style.css, darkmode.css
    ├── js/main.js, chat-widget.js, bookmarks.js, price-alert.js, darkmode.js
    └── img/logos/                 ← tool logos used across the directory
```

---

## Quick Start

### 1. Get the code

Clone or download this repository into your web server's document root.

### 2. Create the database

Import the full schema (tables, not just structure) before anything else:

```bash
mysql -u root -p -e "CREATE DATABASE dropshipping_stack"
mysql -u root -p dropshipping_stack < dropshipping_stack.sql
```

Or, in phpMyAdmin: create a database named `dropshipping_stack` (or any name you like — you'll set it in config), then **Import** → select `dropshipping_stack.sql`.

### 3. Configure the app

There are **two** config files to set up — the app uses both in different places.

**a) Main app config**

```bash
cp config.example.php config.php
```

Edit `config.php` and fill in:

```php
@define('GROQ_API_KEY', 'your-groq-api-key-here');   // optional, powers the chat widget
@define('DB_HOST', 'localhost');
@define('DB_USER', 'your-db-username');
@define('DB_NAME', 'dropshipping_stack');
@define('DB_PASS', 'your-db-password');
@define('SITE_NAME', 'DropshippingStack');
@define('SITE_URL', 'https://yourdomain.com');
@define('SITE_EMAIL', 'you@yourdomain.com');
@define('MAIL_HOST', 'smtp.gmail.com');
@define('MAIL_PORT', 587);
@define('MAIL_USERNAME', 'your-email@gmail.com');
@define('MAIL_PASSWORD', 'your-gmail-app-password');
@define('MAIL_FROM', 'your-email@gmail.com');
@define('SECRET_KEY', 'generate-64-random-chars-here');
```

Generate a secret key:

```bash
openssl rand -hex 32
```

**b) API-side DB config**

A few endpoints under `api/` use their own lightweight config instead of `config.php`:

```bash
cp api/db-config.example.php api/db-config.php
```

Fill in the same database credentials, plus your Groq key if you're using the chat widget.

> **Never commit `config.php` or `api/db-config.php` with real credentials.** Both are already listed in `.gitignore` — keep it that way.

### 4. Point a server at it

Make sure Apache's `mod_rewrite` is enabled — the site's clean URLs (`/tool/shopify`, `/category/store-builders`, `/go/shopify`, `/sitemap.xml`) all depend on the rules in `.htaccess`.

### 5. Log into the admin panel

Visit `/admin/login.php`. Credentials are set when you import `dropshipping_stack.sql` — check the `admins` table for the seeded username, and see [Security Notes](#security-notes) below for changing the password immediately.

---

## Running Locally (XAMPP / MAMP / Laragon)

### XAMPP (Windows/Mac)

1. Install XAMPP from [apachefriends.org](https://apachefriends.org)
2. Copy the project folder to `C:/xampp/htdocs/dropshipping/`
3. Start Apache + MySQL in the XAMPP Control Panel
4. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `dropshipping_stack.sql`
5. Set `SITE_URL` in `config.php` to `http://localhost/dropshipping`
6. Visit `http://localhost/dropshipping`

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
4. Use HeidiSQL (bundled) to import `dropshipping_stack.sql`

---

## Database Schema

`dropshipping_stack.sql` creates the following tables:

| Table | Purpose |
|---|---|
| `tools` | Core tool directory: name, slug, description, pricing, logo, category, affiliate URL |
| `categories` | Tool categories (store builders, suppliers, ad spy tools, etc.) |
| `admins` | Admin panel accounts |
| `users` | Public-facing user accounts |
| `user_settings` | Per-user preferences |
| `user_stack` | Saved tool "stacks" from the stack builder |
| `user_roadmap` | Saved launch roadmap per user |
| `bookmarks` | Saved/bookmarked tools per user |
| `upvotes` | Per-tool upvote records |
| `reviews` | User-submitted tool reviews |
| `submissions` | User-submitted tools awaiting admin approval |
| `clicks` | Affiliate click log (from `go.php`) |
| `price_alerts` | Which users want alerts for which tools |
| `price_history` | Historical price snapshots, used to detect changes |
| `password_resets` | Password reset tokens |

---

## Key Routes

Thanks to `.htaccess`, these clean URLs are available on top of the raw `.php` files:

| URL | Handled by |
|---|---|
| `/` | `index.php` |
| `/tool/{slug}` | `tool/index.php` |
| `/category/{slug}` | `category/index.php` |
| `/go/{slug}` | `go.php` (logs the click, then redirects to the real affiliate URL) |
| `/sitemap.xml` | `sitemap.php` |

---

## The AI Chat Assistant (Groq)

The floating chat widget (`assets/js/chat-widget.js`) talks to `api/chat-api.php`, which calls the [Groq API](https://console.groq.com) — a free, OpenAI-compatible inference API with a rate-limited (not billed) free tier, so no credit card is required.

To enable it:

1. Sign up at [console.groq.com](https://console.groq.com) (email or Google, no card needed)
2. Go to **API Keys → Create API Key**
3. Paste the key into `GROQ_API_KEY` in both `config.php` and `api/db-config.php`

If no key is set, the rest of the site works fine — the chat widget simply won't respond.

---

## Adding Your Affiliate Links

In the admin panel, go to **Tools → Edit** for each tool and replace the placeholder URL, e.g.:

```
https://shopify.com/?ref=YOUR_ID
```

with your real affiliate referral link. All outbound clicks from tool cards route through `/go/{slug}` (`go.php`), which logs the click to the `clicks` table before redirecting — so you get click analytics for free.

---

## Security Notes

- **Change the default admin password immediately** — log into `/admin/login.php`, then either use `admin/set-admin-password.php` or update it directly:

  ```sql
  UPDATE admins SET password = '$2y$12$YOUR_NEW_HASH' WHERE username = 'admin';
  ```

  Generate a bcrypt hash with PHP:

  ```php
  echo password_hash('your-new-password', PASSWORD_BCRYPT);
  ```

- Keep `config.php` and `api/db-config.php` out of source control — both hold real DB credentials and API keys.
- `.htaccess` already denies direct HTTP access to `config.php` and sets baseline security headers (HSTS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`); confirm `api/db-config.php` and `api/llm-config.php` are similarly protected on your host (deny access at the web-server level, not just via a PHP guard).
- Use a strong, randomly generated `SECRET_KEY` (`openssl rand -hex 32`).
- All forms that mutate state use CSRF tokens (`csrf_token()` / `csrf_verify()` in `config.php`) — don't strip these out if you add new forms.
- The bundled PHPMailer library in `phpmailer/src/` should be kept up to date; check upstream for security releases periodically.
- Delete or restrict `api/test-groq.php` and `api/tempCodeRunnerFile.php` before deploying to production — they look like leftover debug/scratch files, not app code.

---

## Troubleshooting

- **500 error / blank page** — check PHP's error log; most often it's a missing `config.php` (copy it from `config.example.php`) or a DB connection failure.
- **Pretty URLs (`/tool/...`, `/category/...`) 404** — `mod_rewrite` isn't enabled, or `.htaccess` isn't being read (check `AllowOverride All` in your Apache vhost).
- **Chat widget doesn't respond** — `GROQ_API_KEY` is missing or invalid in `config.php` / `api/db-config.php`.
- **Verification/reset emails never arrive** — double-check `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD`; for Gmail you need an **app password**, not your normal account password, and 2FA must be enabled on the Google account.

---

## License

*(Add your license here — e.g. MIT, or "All rights reserved" if this is a private/commercial project.)*