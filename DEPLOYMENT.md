# HostingRaja Deployment Guide

This guide describes the release and deployment process for the Janjgir e-Governance Portal on HostingRaja Linux shared hosting. It assumes a cPanel-style control panel and a deployment by ZIP/File Manager or FTP. Menu names can differ by account and hosting plan.

> **Production release gate:** HostingRaja account capabilities and the production deployment layout have not yet been confirmed. Deploy to a staging subdomain first. Do not replace the existing public portal until staging acceptance, content migration, backup restoration, and rollback have all been verified.

## 1. Confirm the HostingRaja account

Before preparing a production release, open a support ticket and confirm these details for the actual account:

- Linux shared hosting, control panel type, and whether SSH/terminal is available (this guide does not require it).
- PHP 8.3 availability for the website and the PHP extensions `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `json`, `bcmath`, `curl`, and `gd`.
- MariaDB version, database host name, database limits, and phpMyAdmin availability.
- PHP memory limit, maximum execution time, `post_max_size`, and `upload_max_filesize`.
- Whether the domain document root can point to the Laravel `public/` directory.
- PHP write access to Laravel's `storage/` and `bootstrap/cache/` directories.
- SSL provisioning, backup/restore steps, and a supported database migration method.
- Staging subdomain availability and whether the one-time migration route described below is permitted on the account.

HostingRaja advertises multiple PHP versions, MariaDB, SSL, backups, and cron on its web hosting plans, but the available settings and limits vary by account. Check the specific plan rather than assuming a feature is enabled. HostingRaja's Laravel hosting page also notes that shared hosting has restrictions. If account limits or traffic expectations are not suitable, ask HostingRaja about an appropriate VPS or Laravel hosting option before production deployment.

## 2. Build a release locally

Use a supported local PHP 8.3 environment, Composer, and Node/npm. Do not run Composer or npm on production.

From `laravel-app/`, install the locked frontend dependencies if needed and compile production assets:

```bash
npm ci
npm run build
```

From the repository root, build a versioned release archive:

```bash
./scripts/package-release.sh 1.0.0
```

Choose a unique version label for each release. The script stages a clean copy, installs optimized Composer production dependencies from `laravel-app/composer.lock`, and writes `release-packages/janjgir-egov-1.0.0.zip`. It requires local `composer`, `rsync`, and `zip`; it does not require these tools on the server.

Before uploading, review the archive. It must contain the Laravel app, `vendor/`, compiled `public/build/` assets, migrations, and the deployment/admin/security notes. It must not contain `.env`, real credentials, `.git`, `node_modules`, local logs/caches, tests, or private user uploads. Keep the archive as the immutable copy of this release.

## 3. Set up staging and the database

1. In the HostingRaja panel, create a staging subdomain (for example, `staging.example.gov.in`) and enable HTTPS for it.
2. Select PHP 8.3 for that domain and enable the confirmed extensions. Set upload and execution limits to values compatible with the portal's configured upload limits, or reduce the portal limits to fit the account.
3. In cPanel, use **MySQL Database Wizard** (or the equivalent panel tool) to create a MariaDB database and a dedicated user. Grant that user privileges on this database only. Record the complete panel-prefixed database/user names and the database host.
4. Configure the staging document root as described in the next section.
5. Create a fresh database backup location and confirm the panel's database and file restore procedures before applying migrations.

HostingRaja's documented cPanel flow is **MySQL Database Wizard → create database → create user → grant privileges**. Use the exact database host shown by the provider; it is not necessarily `127.0.0.1`.

## 4. Use a safe document-root layout

### Preferred: point the domain at Laravel `public/`

Keep the release outside the web root, then configure the domain's document root to the release's `public/` directory. For example:

```text
/home/ACCOUNT/apps/janjgir-egov-1.0.0/       # Laravel root: .env, app, vendor, storage
/home/ACCOUNT/domains/staging.example.gov.in/public_html/  # points to app/.../public
```

The exact paths depend on the account. The key requirement is that only Laravel's `public/` contents are served by the web server. `.env`, source code, `vendor/`, logs, and private storage must not be web-accessible.

### If the panel cannot change the document root

Do not place the full Laravel project under `public_html` and hope that deny rules protect it. Ask HostingRaja support for an approved way to point the web root to Laravel's `public/` directory. A fallback layout needs a reviewed front controller and an explicit allow-list of public assets, while all application source, `.env`, logs, and private storage remain outside the web root. Do not use an improvised front controller or expose the site until that layout has been reviewed.

## 5. Upload the release and configure production settings

1. Upload the reviewed ZIP to the non-public application/release directory using cPanel File Manager or secure FTP/SFTP if available.
2. Extract it there. Confirm the release root contains `artisan`, `app/`, `bootstrap/`, `public/`, `vendor/`, and `.env.example`.
3. Create `.env` in the Laravel application root. Use `.env.example` as a template, but do not copy local development secrets. `.env` must be outside the web root and must never be uploaded to a public directory.
4. Set production values. At minimum:

```dotenv
APP_NAME="Janjgir e-Governance Portal"
APP_ENV=production
APP_KEY=base64:GENERATE_A_UNIQUE_PRODUCTION_KEY
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=HOST_FROM_HOSTINGRAJA
DB_PORT=3306
DB_DATABASE=FULL_CPANEL_DATABASE_NAME
DB_USERNAME=FULL_CPANEL_DATABASE_USER
DB_PASSWORD=LONG_UNIQUE_DATABASE_PASSWORD

SESSION_DRIVER=file
SESSION_ENCRYPT=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

Generate a unique Laravel application key using a trusted local Laravel/PHP environment, then place that key only in the server's `.env`. Do not use a sample key or commit the key. Set `APP_DEBUG=false` before the site is reachable. Configure the site's PHP version in the control panel separately; `.env` does not select PHP.

5. Keep `APP_SETUP_KEY` and `PORTAL_MIGRATION_KEY` unset until the specific setup/migration step below. If the provider's panel has a protected environment-variable feature, use it for these temporary secrets. Otherwise, place them only in the private `.env` outside the web root.
6. Ensure PHP can write to `storage/` (including `storage/app/private/media`, `storage/framework/`, and `storage/logs/`) and `bootstrap/cache/`. Use the permissions recommended by HostingRaja; do not make the whole project world-writable.
7. Keep `storage/app/private/` private. Do not create a public symlink to private media. The application serves public media through its controller only when the asset is explicitly marked public.

## 6. Apply the database migrations

Prefer a HostingRaja-supported migration workflow if one is available. If there is no safe terminal/database workflow, the app includes a restricted, temporary migration screen at `/deployment/migrate`. It does not accept arbitrary commands; it applies pending Laravel migrations and the idempotent role/permission seeder. The route must still be approved and rehearsed with HostingRaja on staging before it is used in production.

For each migration release:

1. Back up the database and private media. Confirm that the backups can be restored and agree a rollback plan before continuing.
2. Deploy the reviewed release to staging first and test its migration there.
3. Generate a fresh 64-character hexadecimal random value for `PORTAL_MIGRATION_KEY`. Never reuse a key from a previous release.
4. Set the key in the protected server environment or the private `.env`.
5. While signed into the intended HTTPS host, open `https://your-domain.example/deployment/migrate` and submit the key through the page. Do not put the key in a URL, ticket, source file, or public document.
6. Confirm the migration screen reports success, then immediately remove `PORTAL_MIGRATION_KEY` from the environment. Keep the migration result and release identifier in the deployment record, not the secret.
7. If migration fails or times out, stop. Do not repeatedly submit the key or manually change the database. Check private Laravel logs and the provider's limits, restore if needed, and resolve the issue on staging with HostingRaja support.

The migration key is one-time and replay-protected. Generate a new key for each later release that requires migrations. Never add a public endpoint that runs arbitrary Artisan commands.

## 7. Create the first administrator accounts

After migrations succeed, create a separate fresh 64-character hexadecimal `APP_SETUP_KEY` and set it in the private environment. Open `https://your-domain.example/admin/initialize` over HTTPS and enter that key plus operator-selected credentials for the initial Super Admin and Admin accounts. The initial accounts must use strong, distinct passwords and are required to change them at first sign-in.

When initialization has completed, remove `APP_SETUP_KEY`. The initializer is disabled after successful setup. Do not put account passwords or setup keys in a deployment ticket or source control.

See [ADMIN_GUIDE.md](ADMIN_GUIDE.md) for role assignment, bilingual content setup, and administration instructions.

## 8. Staging acceptance checklist

Before production cutover, record the release identifier and verify:

- The public home page, navigation, page assets, and HTTPS work.
- English and Hindi switching and representative bilingual content work.
- Admin initialization is complete, login works, and first-login password changes are enforced.
- Role and permission checks behave as intended.
- Content can be created, edited, published, and displayed correctly.
- Allowed uploads, private downloads, and explicitly public media work; private files cannot be fetched by direct URL.
- Contact/support submissions and email behavior match the production configuration.
- `.env`, application source, logs, and private storage are not accessible over HTTP.
- Database and private-file backups restore successfully, and the previous release can be restored.
- PHP upload/execution limits support the configured upload sizes and migration duration.

Replace all seeded/demo content with department-approved information before publishing. Refer to [ADMIN_GUIDE.md](ADMIN_GUIDE.md) for details about seeded drafts and document placeholders.

## 9. Production cutover and rollback

Do not point the existing production domain at the replacement portal until the department has approved content migration and staging acceptance.

Before cutover:

1. Notify the department's site owner and select a maintenance window.
2. Take and verify fresh backups of the existing site, the new database, and private uploads.
3. Record current DNS, document-root, SSL, and existing-site configuration.
4. Deploy the already accepted release using the confirmed safe layout, set the production `.env`, and apply migrations only after the backup/restore check.
5. Switch the document root or DNS as planned. Verify HTTPS, public pages, admin access, uploads, and logs immediately.
6. Keep the previous site and its files untouched until the owner accepts production.

If a release must be rolled back, restore the prior code/document-root configuration and restore the matching database and private-file backups where schema/data changes require it. Do not assume that reverting PHP files alone reverses a database migration. Restore the previous release and database as a tested unit, then verify the old site before ending the maintenance window.

## 10. Routine releases and operations

For each later version, build a new release ZIP locally, review it, test on staging, back up production data, and rehearse every migration. Deploy new code into a versioned directory where possible, then change the document root only after the release is ready. Retain the current and previous known-good release until acceptance.

The app defaults to file sessions/cache and synchronous queues, so it does not require Redis, Supervisor, or a persistent worker. If scheduled tasks are introduced, confirm cron availability and supported intervals with HostingRaja first. Do not run unattended daemons on shared hosting.

Review Laravel logs through the hosting file manager when investigating errors. Never expose logs in the browser, leave `APP_DEBUG=true`, or share `.env`/keys with support. Rotate database credentials and temporary setup keys if they may have been disclosed.
