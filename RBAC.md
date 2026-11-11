# Role-Based Access Control

Use Spatie Laravel Permission with multiple roles per user. Feature groups scope responsibility and drive the admin sidebar; every route/action also checks permission server-side. A hidden menu item is never authorization. Permission assignment and role changes are audit logged.

| Feature group | Typical permissions | Super Admin | Admin | Assigned staff |
|---|---|---:|---:|---:|
| Dashboard | view | all | all content overview | authorized-module summary |
| Homepage, Banners, KPIs, Notices, Announcements | view, create, edit, delete, upload, publish, export, assign | all | all | explicitly assigned subset |
| Documents, Training, Team, Reports, Gallery | view, create, edit, delete, upload, publish, export, assign | all | all | explicitly assigned subset |
| Contact | view, edit | all | all | only if assigned |
| Support | view, edit, assign, export | all | all | scoped to assigned support work |
| Users | view, create, edit, disable, assign | all; protected actions | configurable only if granted; cannot grant Super Admin | none by default |
| Roles | view, create, edit, delete, assign | all | none by default | none |
| Settings | view, edit | all, including protected | permitted non-protected settings only if configured | none by default |
| Media | view, upload, publish, delete, view_private | all | all | explicitly assigned subset |
| Audit Logs | view, export | all | none by default | none |

Permission naming should be predictable, e.g. `documents.view`, `documents.create`, `documents.upload`, `documents.publish`. Publish and assign are distinct from edit. Add feature groups and permissions through seeders/migrations, not scattered role-name conditionals.

Super Admin can assign seeded module permissions directly to each non-Super-Admin user in User Management, as well as assigning one or more roles. Direct assignment excludes system-management groups (`users`, `roles`, `settings`, `audit-logs`) and `media.view_private`; use roles for broader platform access. Direct permission changes are audited by action/subject and field name without storing selected permissions or values. Users cannot modify their own direct permissions, and Super Admin accounts remain protected from normal CRUD.

Media uploads use the private `local` disk and remain private until a user with `media.publish` explicitly changes visibility. `media.view_private` permits viewing other users' private asset metadata/downloads; without it, users only see their own private assets. Content modules still enforce their own upload and publication permissions when attaching assets.

## Invariants

- Exactly one bootstrap Super Admin is created by the guarded initial setup. No UI/API action can create another Super Admin or grant its protected role.
- Normal administrators cannot delete the Super Admin, change protected permissions, or grant themselves privileges.
- An assigned user can exercise only the assigned module/action permissions. Backend authorization applies to list, detail, mutation, download, and export routes.
- The sidebar is generated from effective permissions; empty dashboards show only authorized modules.
- Public routes never expose admin content by virtue of an authenticated user session.

## Bootstrap and audits

The seeders install roles and a fixed permission catalog without fixed credentials. Dotted permission prefixes provide feature grouping; the app does not create a separate `feature_groups` table. The one-time installer accepts operator-supplied initial Super Admin and Admin identities/passwords and locks after their successful transaction. Log login events, content changes, file uploads, publication changes, user/role/permission changes, and protected settings changes. Never log passwords, session tokens, installer secrets, or other credentials.

## Laravel integration status

The Laravel app uses `spatie/laravel-permission`, `HasRoles` on `User`, a Super Admin `Gate::before` override, and `can:<feature>.<action>` checks for protected routes. `RolePermissionSeeder` creates the feature permissions and the configurable `Super Admin` and `Admin` roles without creating any account or password. The dashboard requires `dashboard.view`; public routes do not use admin abilities.

After installing dependencies in `laravel-app/`, run:

```sh
composer require 'spatie/laravel-permission:^7.0'
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
php artisan db:seed --class="Database\Seeders\RolePermissionSeeder"
```

These commands generate the app's lockfile, install Spatie's package migration/config, create the tables, and seed roles/permissions only. No default user or credentials are created.

For first setup, configure a fresh 32-byte random `APP_SETUP_KEY` as 64 hexadecimal characters in the private server environment, then open `/admin/initialize`. The operator enters the key and the initial Super Admin/Admin identities and temporary passwords. Both accounts, protected-role assignments, and the singleton database completion flag are created in one transaction; a row lock prevents concurrent initialization. Both users must change their passwords at first login. The route returns 404 after completion. The key is never stored in the database or flashed into the session; only its SHA-256 hash is held in Laravel configuration. Remove the environment value after setup as defense in depth. Do not assign the protected role through normal admin CRUD.
