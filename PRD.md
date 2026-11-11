# Product Requirements Document

## Product

Janjgir e-Governance Portal replaces the mostly static ASP.NET citizen portal with a bilingual, database-backed Laravel monolith. Citizens and government staff use one server-rendered website; only staff authenticate.

## Users and outcomes

- **Citizens and operators:** find current notices, documents, reports, learning materials, teams, gallery albums, and support contacts in Hindi or English.
- **Content staff:** create, review, publish, schedule, expire, and archive assigned content without technical assistance.
- **Administrators:** manage all content, users, site settings, and support requests.
- **Super Admin:** manage identities, roles, feature responsibility, protected settings, and audit history.

## Product scope

Public scope: dynamic homepage, department introduction, notices, announcements, updates, quick links, banners, manually maintained KPIs, document/training/report repositories, FAQs, team directory, galleries, contact details, and support form. Admin scope: dashboard, CMS for those modules, role-based navigation, user/role/permission management, shared media, settings, audit logs, and content lifecycle controls.

All public content is persisted in MariaDB; user-generated content is never hardcoded in templates. Every content type supports Hindi and English fields where it contains text. Public listings provide server-side search, filters, and pagination. Files are stored in Laravel Filesystem, not in database blobs.

## Acceptance outcomes

Draft and archived content stay private; scheduled and expired content follows publication windows. Every management action persists and is authorization-checked on the server. Hindi/English switching works across navigation and public content. Uploads are validated and private downloads are protected. The site remains usable on mobile and by keyboard/screen-reader users. Release deployment works through a package and shared-hosting file tools without production shell access.

## Constraints and exclusions

Use PHP 8.3, Laravel 12, MariaDB, Blade, Tailwind CSS, Alpine.js, session authentication, Spatie Permission, and locally built Vite assets. Do not use a separate frontend/backend, Redis, Docker, persistent queue worker, or production Node runtime. Live production migration, replacement, or deployment is out of scope until inventory, migration rehearsal, backup, rollback, and acceptance are complete. Interactive analytics from uploaded reports are not implied.

## Discovery status

Search-result inspection of the existing portal indicates a bilingual Hindi/English hero/banner, KPI counts (offices, districts, operators, services, documents, and trainings), About content, notices, announcements, Today's Updates, Upcoming Meetings, Quick Links, and Recent Documents. Direct page access was unavailable to this agent, so this is a provisional baseline, not a complete inventory. Confirm pages, content, files, URL patterns, and any SQL Server export manually before migration; do not modify the live site.
