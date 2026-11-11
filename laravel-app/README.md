# Janjgir e-Governance Portal Application

This is the Laravel 12 application for the bilingual Janjgir district e-Governance portal. The public site and staff administration share this application. The PHP target is 8.3; the database is MariaDB through Laravel's MySQL driver. Production frontend assets must be compiled locally.

## Local Setup

See the repository-level [Local Development Setup](../LOCAL_DEVELOPMENT.md) for prerequisites and the MariaDB setup. In brief:

```sh
composer install
cp .env.example .env
php artisan key:generate
```

Set the `DB_*` values in `.env` to the MariaDB database and user you created, then initialize the app:

```sh
php artisan migrate
php artisan serve
npm install
npm run dev
```

Use the PHP 8.3 executable for local parity. Do not commit `.env`, setup keys, database passwords, administrator passwords, uploaded private files, or production credentials. There is no default administrator password; create the first account through the one-time `/admin/initialize` flow after configuring a 64-character random `APP_SETUP_KEY` and applying the migrations/permission seeder. The initial password must be changed on first sign-in.

## Development Notes

- Default connection: MariaDB/MySQL, database `egov`, user `egov`.
- Sessions and cache use local files; jobs run synchronously. Redis and persistent workers are not required.
- Uploads use the private local filesystem by default. Public media must be explicitly marked public and served through the access-checked media route.
- English and Hindi are selected by session. Dynamic content has bilingual fields with a consistent fallback.
- Alpine.js and Spatie Laravel Permission are required project dependencies; additional dependency installation is pending working Node/Composer registry access.
