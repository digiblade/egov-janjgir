# Janjgir e-Governance Portal

A bilingual civic information and content-management portal implemented as a Laravel 12 monolith in `laravel-app/`. The locked stack is PHP 8.3, MariaDB, Blade, Tailwind CSS, Alpine.js, session authentication, and Spatie Laravel Permission. See [REQUIREMENTS.md](REQUIREMENTS.md) for full functional scope and hosting constraints.

## Project Status

Laravel Framework 12.70.1 and the scaffold's Composer dependencies are installed in `laravel-app/`. The app `.env.example` configures MariaDB, file sessions/cache, and synchronous jobs. Local database provisioning/migration is pending because this tool sandbox cannot connect to the running MariaDB service. Alpine.js and Spatie Permission still need installation; frontend dependency installation/build is pending because Node cannot load its Homebrew `simdjson` library and Packagist DNS remains unavailable for additional PHP dependencies. See [LOCAL_DEVELOPMENT.md](LOCAL_DEVELOPMENT.md) and [PHASE_TRACKER.md](PHASE_TRACKER.md) for details.

## Documentation

- [PRD](PRD.md) — product scope and users
- [Architecture](ARCHITECTURE.md) and [Database](DATABASE.md) — application and data design
- [RBAC](RBAC.md), [Security](SECURITY.md), and [I18N](I18N.md) — access, security, and localization
- [UI/UX Guidelines](UI_UX_GUIDELINES.md) and [design system](design-system/janjgir-e-governance-portal/MASTER.md)
- [Implementation Plan](IMPLEMENTATION_PLAN.md) and [Phase Tracker](PHASE_TRACKER.md)
- [Local Development](LOCAL_DEVELOPMENT.md) and [Deployment](DEPLOYMENT.md)

## Local Setup

Follow [LOCAL_DEVELOPMENT.md](LOCAL_DEVELOPMENT.md) for setup from `laravel-app/`. Use MariaDB; PostgreSQL is not supported. Never add secrets or `.env` files to source control.
