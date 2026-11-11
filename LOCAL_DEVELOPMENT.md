# Local Development Setup

## Prerequisites

- PHP 8.3 with the extensions required by Laravel 12 and the application: PDO MySQL, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo, cURL, GD, and ZIP.
- Composer 2 for PHP dependencies.
- MariaDB with a local database and a dedicated application user.
- Node.js and npm for local Vite/Tailwind asset development and production builds. Node.js is not required on the production host.
- Git and a local web browser.

### Current Workspace Status

Laravel Framework 12.70.1, Composer dependencies, and Spatie Permission are installed under `laravel-app/`; Spatie's config and migration are published. PHP 8.3.36 is available at `/opt/homebrew/opt/php@8.3/bin/php`, although the default CLI may still select PHP 8.4.8. MariaDB 13.0.2 is running, but this agent shell cannot connect to the user's server because of its network sandbox. Runtime/asset steps below should be run from the user's terminal.

The current checkout has no `package-lock.json` or `public/build/manifest.json`; installed frontend dependencies/build output are not present. The agent shell's Node currently fails to load `libsimdjson.26.dylib`. Repair Node/npm in the user's terminal, then run `npm install` and `npm run dev` for local styling or `npm run build` before packaging. The PHP route/view checks do not verify that assets render.

## First Run

From the application directory (using PHP 8.3):

```sh
cd laravel-app
composer install
npm install
cp .env.example .env
php artisan key:generate
```

The `composer require` and `vendor:publish` steps are already complete for this checkout. If setting up from a fresh clone after changing dependencies, install the locked dependencies, then publish Spatie's config and migration before migrating:

```sh
composer install
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

Create a MariaDB database and least-privilege user (the template expects database and user `egov`), then set the password chosen during local database setup in `.env`. With a MariaDB account authorized to create databases:

```sql
CREATE DATABASE egov CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'egov'@'127.0.0.1' IDENTIFIED BY '<choose-a-local-secret>';
GRANT ALL PRIVILEGES ON egov.* TO 'egov'@'127.0.0.1';
```

Set `APP_URL` to the local URL and keep `.env` out of version control. Before opening the first-admin setup page, configure `APP_SETUP_KEY` with a fresh 32-byte cryptographic random value encoded as 64 hexadecimal characters. Generate one locally with:

```sh
php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
```

Copy that value into the private `.env` as `APP_SETUP_KEY=...`. The raw value is only accepted during one-time setup, is never stored in the database, and Laravel configuration stores only its SHA-256 hash. Keep the raw key out of logs and source control. If configuration is cached for deployment, generate the cache with the server key configured; do not ship a cache generated while `APP_SETUP_KEY` is blank.

Uploads use Laravel's `local` disk rooted at `storage/app/private`; new files remain private until explicitly published by a user with `media.publish`. Configure `PORTAL_MAX_UPLOAD_KB`, `PORTAL_MAX_VIDEO_UPLOAD_KB`, `PORTAL_MAX_IMAGE_DIMENSION`, and `PORTAL_MAX_OPTIMIZED_IMAGE_DIMENSION` to the limits supported by the server. PHP GD re-encodes supported images; PHP ZIP validates Office Open XML containers. Never create a public symlink for private media.

Document bulk intake supports up to 10 files per request, with a per-file limit from `PORTAL_MAX_UPLOAD_KB`. PHP `upload_max_filesize`, `post_max_size`, and `max_file_uploads` and the hosting request timeout must also accommodate the combined upload. Each accepted file creates a private media entry and an unpublished editable document draft in the selected active category. Staff must complete bilingual metadata, publish each file individually, and then explicitly publish each document record.

Set `DB_PASSWORD` in `.env` to the local secret. Initialize the database and start the app in separate terminals:

```sh
php artisan migrate
php artisan db:seed
php artisan serve
npm run dev
```

The standard seeder creates roles and permissions, the bilingual reference categories from `REQUIREMENTS.md`, one clearly marked bilingual document draft with a private placeholder PDF per document category, default quick links, and bilingual demo drafts for notices, announcements, and manual homepage updates. Demo records stay unpublished until an administrator reviews and publishes them. Initial administrator accounts are created only when both optional seed passwords are configured in `.env`; otherwise use the one-time setup flow below.

Open the URL printed by `php artisan serve` (usually `http://127.0.0.1:8000`). While `APP_SETUP_KEY` is set, open `/admin/initialize` and enter the key plus operator-chosen identities and unique strong temporary passwords for both the initial Super Admin and Admin. A database transaction creates both accounts, assigns their protected roles, and marks setup complete; both accounts must change their passwords at first login. Concurrent requests cannot create duplicates, and the initializer then returns 404. Remove `APP_SETUP_KEY` from the private environment after setup as defense in depth. No default credentials exist.

## Daily Development

Run `php artisan serve` and `npm run dev` while developing. Use `php artisan migrate` after schema changes and `npm run build` to compile assets. The automated suite is run with `php artisan test` when verification is requested. Do not expose local `.env`, uploaded private files, or debug mode on a public server.

## Hosting Notes

Production uses PHP 8.3 and MariaDB on HostingRaja shared hosting. Build assets and Composer production dependencies locally; the host must not need Node.js, SSH, a terminal, or a persistent worker. Confirm the host's PHP extensions, MariaDB version, upload limits, document-root options, permissions, SSL, and backup/restore facilities before deployment; these details are provider/account-specific and are not yet verified.

For a shared-hosting first run, set both `APP_SETUP_KEY` and the distinct `PORTAL_MIGRATION_KEY` as private server environment values (or in a protected `.env` outside the public document root) before deploying the configured app. Generate a fresh 64-character hex migration key the same way as the setup key. Run database migrations through the reviewed deployment workflow before visiting `/admin/initialize`. After the schema operation, remove `PORTAL_MIGRATION_KEY`; after creating the administrator, remove `APP_SETUP_KEY`. The database completion flag remains the authoritative one-time lock.

When HostingRaja provides no terminal/database migration workflow, use the fixed-operation web migration screen at `/deployment/migrate`. Before uploading a release, set a distinct `PORTAL_MIGRATION_KEY` to a fresh 64-character hex value in the protected server environment, deploy the reviewed code and compiled assets, and visit that path over HTTPS. The POST accepts only that key, runs Laravel's pending migrations plus `RolePermissionSeeder`, and records the key hash so it cannot be replayed. It exposes no command name or arguments. On success remove the migration key and complete first-admin setup using the already configured `APP_SETUP_KEY`. For each later release requiring schema changes, configure a new migration key; never reuse an old one. If the operation fails, inspect the protected server log and restore/repair the database before retrying. Confirm this approach with the hosting provider and rehearse it on staging first.
