# Repository Guidelines

## Project Structure & Module Organization

The Laravel 12 monolith lives in `laravel-app/`; the root holds project requirements and implementation documentation. Public and admin interfaces share this app. Follow Laravel conventions: application code under `laravel-app/app/`, Blade/frontend under `laravel-app/resources/`, routes under `laravel-app/routes/`, migrations/seeders/factories under `laravel-app/database/`, translations under `laravel-app/lang/en` and `laravel-app/lang/hi`, and checks under `laravel-app/tests/Feature` and `laravel-app/tests/Unit`. Keep `.env` and private storage outside web access in deployment layouts that require it.

## Build, Test, and Development Commands

Run app commands from `laravel-app/`:

- `composer install` installs PHP dependencies.
- `npm install` installs frontend build dependencies; repair Node before use if its Homebrew `simdjson` dependency is missing.
- `php artisan serve` runs the local development server.
- `npm run dev` starts Vite's development asset workflow; `npm run build` compiles production assets.
- `php artisan migrate` applies database migrations to the configured local MariaDB database.
- `php artisan test` runs the Pest/PHPUnit suite.

Compile assets locally for production; shared hosting must not require Node.js, SSH, or terminal access. Package `vendor/` and compiled Vite assets as specified in `REQUIREMENTS.md`.

## Coding Style & Naming Conventions

Use PHP 8.3, Laravel 12 conventions, PSR-12 style, and four-space PHP indentation. Use descriptive Laravel names (such as `NoticeController`, `NoticePolicy`, `CreateNoticesTable`) and lowercase kebab-case Blade templates grouped by area (for example, `laravel-app/resources/views/admin/notices/`). Keep controllers focused on HTTP, validate with Form Requests, and put business logic in services. Use Blade and Alpine.js; add Livewire only where justified.

## Testing Guidelines

Use Pest or PHPUnit feature tests for routes, authentication, permissions, and persisted workflows; use unit tests for isolated business rules. Name tests for behavior (for example, `NoticePublicationTest`) and run `php artisan test` before release. Cover Hindi and English content, authorization, and validation in content-management changes.

## Commit & Pull Request Guidelines

No Git history is available to establish a commit format. Use concise imperative subjects, such as `Add bilingual notice management`. Pull requests should explain the change, note schema/configuration updates, link issues when available, include UI screenshots, and list checks and deployment steps.

## Security & Configuration

Keep secrets in `.env`; never commit credentials. Enforce validation, policies, and least-privilege permissions for admin actions. Store private uploads outside public access. Do not expose Artisan execution endpoints or add infrastructure dependencies that conflict with the shared-hosting requirements.
