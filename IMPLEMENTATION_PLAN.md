# Implementation Plan

This plan follows the phases in `REQUIREMENTS.md`. Phase 1 planning deliverables are complete: the provisional portal baseline, hosting compatibility checklist, architecture, ERD, permission matrix, sitemap/route contract, design system, deployment approach, and implementation plan are documented. Live portal/source-data inventory and HostingRaja account verification remain open discovery tasks recorded below and in `PHASE_TRACKER.md`. The user has authorized proceeding through implementation phases; production deployment remains gated on migration and acceptance approval.

| Phase | Work | Exit evidence |
|---|---|---|
| 1. Discovery and architecture | Review old portal/data/hosting; confirm provider limits; define monolith, ERD, RBAC, sitemap, design and release strategy | Architecture documents, decisions, open discovery items, design system |
| 2. Foundation | Laravel 12.70.1 scaffold, MariaDB/MySQL defaults, local env, locale middleware, public layout/theme, Alpine/Vite, base migrations | Scaffold and Composer checks pass; MariaDB migration and Vite build remain pending sandbox DB/Node access |
| 3. Authentication and RBAC | Session login/logout, guarded first-admin flow, Spatie roles/permissions, feature assignments, sidebar, audit logs | Not started; Spatie installation blocked by Packagist DNS |
| 4. Homepage CMS | Department info, notices, announcements, updates, quick links, banners, KPIs, section visibility | Schema/models and public homepage/search/notice/announcement routes/views in progress; no admin CRUD or DB migration verification yet |
| 5. Resource management | Shared media service, document/training/report categories and records, FAQs, search/filter/pagination | Validated uploads/downloads, private file handling, public publication rules |
| 6. Team and gallery | Dynamic categories/profiles, albums/images, ordering, captions and alt text | Responsive public listings/details and authorized admin CRUD |
| 7. Contact and settings | Contact content, rate-limited support workflow, notes/assignment, site/footer/SEO settings | Requests persist, staff permissions apply, settings render publicly |
| 8. Production readiness | Automated/security/accessibility/localization/performance checks, deployment ZIP, migration and backup/rollback instructions, admin guide | Release checklist passed in staging; no production shell dependency |

## Parallel work boundaries

Treat schema and authorization contracts as shared foundations. Assign bounded modules only after migrations/services/interfaces are agreed. Avoid duplicate upload logic and repeated layout systems. Each phase owner documents migrations, routes, permissions, and deployment impacts for integration.

## Discovery still required

The current portal could not be opened through the available browser tool. Obtain a manual page/content inventory and any SQL Server export; confirm HostingRaja PHP/MariaDB/extensions/document-root/upload/cron/backup capabilities. Build redirect mappings and a non-production migration rehearsal before changing DNS or live content.

## Development policy

Do not claim completion based on screens alone: management flows must persist, public output must respect authorization and lifecycle, and release checks need evidence. No production deployment occurs until migrated records/files, Hindi rendering, restore/rollback, and acceptance are verified.
