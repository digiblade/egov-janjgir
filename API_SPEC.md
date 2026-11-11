# Application Route Contract

The portal is a server-rendered Laravel application, not a separate JSON API. `routes/web.php` defines HTML pages and form actions; controllers return Blade views, redirects, downloads, or validation errors. Keep the route contract resource-oriented and named. A future external/mobile API requires a separate approved scope and versioning decision.

## Public route families

| Method and path | Purpose |
|---|---|
| `GET /` | Bilingual homepage with eligible homepage content |
| `GET /search?q=...` | Server-side search across currently implemented published homepage content; later resource modules extend this index |
| `GET /notices` / `GET /notices/{slug}` | Search/filter published notices and view a single notice |
| `GET /announcements` / `GET /announcements/{slug}` | Search published announcements and view a single announcement |
| `GET /media/{id}` | Stream an asset only when its recorded visibility is public; other files return 404 |
| `GET /documents` / `GET /documents/{document}` | Search/filter repository and view published metadata |
| `GET /documents/{document}/download` | Download published asset through access-checked handler |
| `GET /training` / `GET /training/{resource}` | Learning resources and FAQs |
| `GET /team` / `GET /team/{member}` | Public team directory/profile |
| `GET /reports` / `GET /reports/{report}` | Published reports and details/downloads |
| `GET /gallery` / `GET /gallery/{album}` | Published albums and images |
| `GET /contact` | Contact details and support form |
| `POST /support-requests` | Validate, rate-limit, and persist support request |
| `POST /language/{locale}` | Store only supported `en` or `hi` preference and redirect safely |

## Admin route families

Place under `/admin`, name `admin.*`, and require authentication. Resource actions follow `GET index/create/edit/show`, `POST store`, `PUT/PATCH update`, and `DELETE destroy`. Protect each action with the relevant Spatie permission/policy. Module groups include dashboard, homepage, banners, KPIs, notices, announcements, documents/categories, training/categories/FAQs, team/categories, reports/categories, gallery/categories/albums/images, contact, support/notes, users, roles, settings, media, and audit logs.

## Common behavior

Use Form Requests for validation and authorization, CSRF protection for every mutation, pagination and allow-listed sorting, inline field errors with old input preserved, flash feedback, and transactions for related writes. Public queries always filter lifecycle eligibility and language fallback. Uploads go through the shared media service. Downloads authorize visibility and use non-guessable storage keys; sensitive files require signed/authorized responses. Do not leak private IDs, file paths, errors, or unredacted exception details.
