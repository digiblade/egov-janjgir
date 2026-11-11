# Security Plan

## Application controls

- Require HTTPS in production; configure secure, HTTP-only, SameSite session cookies and regenerate sessions after authentication.
- Use Laravel password hashing, CSRF middleware, login throttling, rate limits on support submissions/downloads where appropriate, and generic authentication errors.
- Validate and authorize every request with Form Requests, policies, and Spatie permissions. Enforce backend access independently of the sidebar.
- Escape content by default in Blade. Rich text must pass through a restrictive HTML sanitizer and only privileged users can edit it. Use Eloquent/query bindings and allow-list sort columns.
- Validate uploads by extension, detected MIME, size, and image dimensions. Generate random storage keys; store private files outside public access. Serve downloads through authorization checks; checksum and record metadata. Do not trust original filenames or direct browser MIME values.
- Protect against CSRF, XSS, SQL injection, path traversal, mass assignment, unauthorized file access, and open redirects. Add security headers and a restrictive Content Security Policy compatible with locally compiled assets.
- The CSP-compatible Alpine build avoids `unsafe-eval`. Inline style attributes remain allowed only through `style-src-attr` for Alpine visibility and data-driven banner/KPI colors; style elements and external styles remain same-origin. Keep authored HTML escaped and verify Alpine expressions when rebuilding frontend assets.

## Secrets and first installation

Never commit `.env`, credentials, passwords, installer keys, or private media. Provide `.env.example` without real secrets. Role/permission seeders contain no default password. Use the guarded one-time installer described in `ARCHITECTURE.md`; accept separate temporary passwords from the operator for initial Super Admin and Admin accounts, require password changes at first login, and disable the installer atomically after both are created. No public arbitrary Artisan endpoint is allowed. Remove installer secret/configuration after setup.

## Data retention and incident response

Apply these operational defaults after the department's data owner confirms they fit applicable records-retention obligations: retain resolved support submissions for 24 months, then securely delete or anonymize them unless a documented hold applies; review unresolved submissions every 12 months. Retain metadata-only admin audit events for 12 months. Keep encrypted, access-controlled backups on a 30-day rolling schedule and securely expire older copies. Do not include secrets, session identifiers, message bodies, or unnecessary personal data in audit events. Restrict support and team contact data to authorized staff; publish contact details only with explicit consent.

For a suspected incident: (1) notify the designated department security contact promptly and record discovery time/scope; (2) restrict compromised accounts or affected upload/download paths and preserve relevant logs; (3) rotate affected credentials and keys, patch the cause, and restore from a verified clean backup if needed; (4) have the department decide required notifications under its applicable policy; (5) document impact, actions, and follow-up, then review access and retention. Assign named operational contacts and validate notification timelines before go-live.

Verify HostingRaja PHP extensions, HTTPS, file permissions, document root, upload/exec limits, database access, and backup restoration in a staging account. Prepare rollback before migration or go-live; never modify the existing live portal during development.

## Release gate

Before production, perform dependency review, security review, authorization checks for every route, upload/download abuse review, migration rehearsal, backup restore rehearsal, and deployment-layout review. A publicly reachable `.env`, app source, private storage, arbitrary command execution, or unprotected administrative route blocks release.
