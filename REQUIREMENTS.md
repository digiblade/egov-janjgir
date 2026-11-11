# MASTER DEVELOPMENT PROMPT
# Janjgir e-Governance Portal — Full-Stack Laravel Monolithic Application

## 1. Role and Objective

You are a Principal Software Architect, Senior Laravel Developer, Database Architect, UI/UX Designer, Security Engineer, and Quality Assurance Engineer.

Your task is to design and develop a complete, production-ready, dynamic, bilingual government e-Governance portal.

**Project Name:** Janjgir e-Governance Portal

**Existing Website:** https://egovjanjgir.com/Public/Default.aspx

**Primary Objective:** Replace the existing mostly static ASP.NET-based government portal with a modern, fully dynamic Laravel monolithic application.

The new portal must have:

- A premium, modern, attractive public-facing website.
- A professional and user-friendly administration dashboard.
- Completely database-driven content management.
- Hindi and English language support.
- Dynamic categories and content across all sections.
- Dynamic homepage banners and KPIs.
- Secure user authentication.
- Granular role-based access control (RBAC).
- Feature-group responsibility assignments.
- Secure document and media management.
- Full compatibility with HostingRaja shared hosting.

**Critical:** The application must be fully functional, not merely a frontend prototype. Every management operation must persist data in MariaDB and reflect correctly on the public website.

---

# 2. Final Technology Stack

The following technologies are finalized and must not be changed without approval.

| Layer | Technology |
|---|---|
| Architecture | Laravel Monolith |
| Backend | PHP 8.3 + Laravel 12 |
| Database | MariaDB |
| Frontend | Laravel Blade Templates |
| Styling | Tailwind CSS |
| Frontend Interactivity | Alpine.js |
| Dynamic Components | Livewire, only where justified |
| Authentication | Laravel Session-Based Authentication |
| Authorization | Spatie Laravel Permission |
| ORM | Laravel Eloquent |
| File Management | Laravel Filesystem |
| Localization | Laravel Localization + Bilingual Database Fields |
| Build Tool | Vite, executed locally |
| Testing | Pest / PHPUnit |
| Hosting | HostingRaja Shared Hosting |
| Deployment | ZIP Upload / FTP / File Manager |

## Mandatory Architecture Constraints

1. Use a single Laravel application for both public and admin interfaces.
2. Do not create a separate frontend application.
3. Do not introduce a separate Node.js backend.
4. Do not require Node.js to run in production.
5. Do not require SSH or terminal access on the production server.
6. Do not depend on Redis, Supervisor, Docker, or persistent queue workers.
7. Use MariaDB with Laravel's MySQL database driver.
8. Use server-rendered Blade pages for SEO and accessibility.
9. Use Alpine.js for lightweight client-side interactivity.
10. Compile Tailwind CSS and JavaScript locally before deployment.
11. Keep dependencies minimal and shared-hosting compatible.
12. Follow Laravel conventions and maintain modular, testable code.

---

# 3. Hosting and Deployment Constraints

The production environment is HostingRaja shared hosting.

Available infrastructure:

- PHP 8.3
- MariaDB
- Shared hosting control panel
- File Manager / FTP
- No terminal access
- No guaranteed persistent background processes

Before development, verify or document the requirements for:

- MariaDB server version
- PDO MySQL extension
- Required PHP extensions
- Composer dependency compatibility
- PHP memory limits
- Maximum upload size
- Maximum execution time
- File permissions
- Cron job availability
- Domain document root configuration
- SSL certificate
- Database backup and restore facilities

## Deployment Requirements

The application must support deployment without running commands on the production server.

The local build process must:

1. Install Composer dependencies.
2. Install frontend dependencies.
3. Compile frontend assets.
4. Run automated tests.
5. Prepare the deployment package.
6. Include the production `vendor` directory.
7. Include compiled Vite assets.
8. Exclude unnecessary development files.
9. Generate deployment instructions.
10. Create a versioned release ZIP.

Provide a secure initial installation and migration process that does not expose unrestricted command execution.

Use the hosting control panel or an authenticated, one-time installer for approved setup operations.

Never expose a public Artisan command execution endpoint.

If the hosting provider does not permit Laravel's `public` directory as the domain document root, design a safe deployment layout that keeps application source code, `.env`, and private storage outside the web-accessible directory.

Do not proceed with an insecure deployment layout.

---

# 4. Application Architecture

Implement a modular Laravel monolith.

Suggested structure:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   ├── Admin/
│   │   └── Auth/
│   ├── Requests/
│   └── Middleware/
├── Models/
├── Policies/
├── Services/
│   ├── Content/
│   ├── Media/
│   ├── Localization/
│   ├── Authorization/
│   └── Support/
├── Jobs/
├── Enums/
└── Support/

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── public/
│   ├── admin/
│   └── auth/
├── css/
└── js/

database/
├── migrations/
├── seeders/
└── factories/

lang/
├── en/
└── hi/

routes/
├── web.php
└── console.php

tests/
├── Feature/
└── Unit/
```

Use:

- Controllers for HTTP request handling.
- Form Requests for validation.
- Services for business logic.
- Eloquent models for database access.
- Policies and permissions for authorization.
- Blade components for reusable UI.
- Database transactions for related operations.
- Events or jobs only where necessary.
- Shared services for media and category management.

Avoid unnecessary repository abstractions or microservice-style complexity.

---

# 5. Public Website — Complete Feature Requirements

The public website must be accessible without authentication.

Main navigation:

1. Home / Dashboard
2. Documents Repository
3. Training & Learning Center
4. Team Information
5. Reports
6. Photo & Media Gallery
7. Contact Us

All content must be dynamically retrieved from MariaDB.

No content categories or public statistics may be hardcoded.

## 5.1 Homepage

The homepage must contain the following dynamic sections.

### A. Department Introduction

Display:

- Department title
- Department description
- Department image/logo
- Optional mission and objectives
- Relevant introductory content

All text must support Hindi and English.

Admin must be able to edit the content using a secure rich text editor.

### B. Important Notice Board

Fields:

- Notice ID
- Title Hindi
- Title English
- Description Hindi
- Description English
- Notice type
- Priority
- Publication date
- Expiry date
- Optional attachment
- Optional link
- Pinned status
- Display order
- Publication status

Frontend features:

- Latest notices
- Priority highlighting
- Download attachments
- View all notices
- Search and pagination
- Automatic expiry handling

### C. Today's Updates

Display recent updates from published content.

Possible sources:

- Notices
- Documents
- Reports
- Training materials
- Announcements

Admin must be able to configure which modules contribute updates.

Support manually created updates when required.

### D. Quick Links

Admin-managed fields:

- Title Hindi
- Title English
- Icon
- URL
- Internal/external link
- Open in new tab
- Display order
- Active status

Display attractive icon-based quick-link cards.

### E. Recent Announcements

Create a separate announcement module.

Fields:

- Title Hindi/English
- Description Hindi/English
- Featured image
- Publication date
- Expiry date
- Attachment
- Priority
- Status

Provide announcement listing and detail pages.

---

# 6. Dynamic Banner Carousel

Create a premium homepage carousel.

## Banner Fields

- Banner ID
- Title Hindi
- Title English
- Subtitle Hindi
- Subtitle English
- Desktop image
- Mobile image
- CTA text Hindi
- CTA text English
- CTA URL
- Text alignment
- Text color
- Overlay color
- Overlay opacity
- Display order
- Start date
- End date
- Active status

## Public Features

- Responsive full-width banners
- Automatic sliding
- Previous/next buttons
- Pagination indicators
- Touch/swipe navigation
- Keyboard accessibility
- Optional CTA buttons
- Configurable autoplay duration
- Smooth transitions
- Reduced-motion support

## Admin Features

- Create/edit/delete banners
- Upload banner images
- Preview before publishing
- Reorder banners
- Enable/disable banners
- Configure carousel behavior

Use optimized responsive images.

---

# 7. Dynamic KPI Management

Implement an admin-managed statistics section.

## KPI Fields

- KPI ID
- Title Hindi
- Title English
- Description Hindi
- Description English
- Numeric value
- Prefix
- Suffix
- Icon
- Background color
- Text color
- Icon color
- Display order
- Active status

Example KPIs:

- Total CSC Centers
- Total Operators
- Total Training Programs
- Total Documents
- Services Available

These are examples only.

## Admin Features

- Create KPI
- Edit KPI
- Delete KPI
- Choose icon
- Choose colors
- Update count
- Reorder cards
- Preview
- Enable/disable

KPI values will initially be manually managed.

Do not automatically calculate values unless explicitly configured.

## Public Design

Use attractive, modern statistic cards with:

- Professional icons
- Configurable colors
- Large readable numbers
- Optional count-up animation
- Responsive layouts

---

# 8. Documents Repository

Create a complete dynamic document management system.

## Initial Reference Categories

- SOP Documents
- Guidelines
- Circulars
- Government Orders
- Application Forms
- Downloads

Categories must be completely dynamic.

## Category Management

Fields:

- Category ID
- Name Hindi
- Name English
- Description Hindi
- Description English
- Slug
- Icon
- Parent category (optional)
- Display order
- Active status

Admins can create unlimited categories.

## Document Management

Fields:

- Document ID
- Title Hindi
- Title English
- Description Hindi
- Description English
- Category
- File
- File type
- File size
- Original filename
- Version
- Reference number (optional)
- Issuing authority (optional)
- Uploaded by
- Publication date
- Status

Supported formats:

- PDF
- DOC/DOCX
- XLS/XLSX
- PPT/PPTX
- JPG/JPEG
- PNG
- WebP

## Public Features

- Category navigation
- Document listing
- Search
- File type filters
- Date filters
- Pagination
- File preview where supported
- Download
- File format icons
- File size display

## Admin Features

- Create/edit/delete categories
- Upload documents
- Replace document files
- Edit metadata
- Publish/unpublish
- Archive documents
- Bulk upload where hosting limits permit
- Search/filter documents

Prevent deletion of categories containing active documents without reassignment or confirmation.

---

# 9. Training & Learning Center

Create a dynamic training resource platform.

## Initial Categories

- Training Videos
- Recorded Webinars
- PPT Presentations
- User Manuals
- FAQs
- New Operator Induction Materials

All categories must be dynamic.

## Training Resource Fields

- Resource ID
- Title Hindi/English
- Description Hindi/English
- Category
- Resource type
- File or external URL
- Thumbnail
- Duration (optional)
- Training date
- Display order
- Status

Supported resources:

- YouTube videos
- External video URLs
- Uploaded videos where hosting permits
- PDF
- Word
- Excel
- PowerPoint
- Images

Prefer external video hosting for large media files.

## FAQ Management

Fields:

- Question Hindi
- Question English
- Answer Hindi
- Answer English
- Category
- Display order
- Active status

## Public Features

- Category navigation
- Training cards
- Video playback
- Downloadable materials
- Search
- FAQ accordion
- Responsive layout

## Admin Features

- Category CRUD
- Resource CRUD
- Video link management
- File upload
- FAQ CRUD
- Publishing controls
- Resource ordering

---

# 10. Team Information

Create a professional public team directory.

## Initial Categories

- District e-Governance Team
- Block Coordinators
- Operators List
- Roles & Responsibilities

Categories must be dynamic.

## Team Member Fields

- Member ID
- Full name Hindi
- Full name English
- Photograph
- Email
- Registration number
- Designation Hindi
- Designation English
- Team category
- Office/block (optional)
- Contact number (optional)
- Responsibilities Hindi
- Responsibilities English
- Display order
- Active status

## Public Features

Display professional team cards containing:

- Photograph
- Name
- Designation
- Email
- Registration number
- Team/department

Support:

- Category filters
- Name search
- Designation search
- Office/block filtering
- Pagination
- Profile detail view

## Admin Features

- Category management
- Add/edit/delete members
- Upload photographs
- Assign team categories
- Manage designations
- Manage responsibilities
- Reorder members
- Publish/unpublish

Public team profiles and administrative user accounts must remain separate entities.

Only display personal contact information approved for public publication.

---

# 11. Reports Repository

Create a dynamic report management system.

## Initial Categories

- Service-wise Reports
- Office-wise Reports
- CSC/LSK Center Details
- Inspection Reports
- Operator Details
- Performance Ranking

These are example categories only.

## Report Fields

- Report ID
- Title Hindi
- Title English
- Description Hindi
- Description English
- Category
- Reporting period
- Report date
- File
- File type
- File size
- Office/block (optional)
- Uploaded by
- Publication status

Supported formats:

- PDF
- DOC/DOCX
- XLS/XLSX
- PPT/PPTX

## Public Features

- Category listing
- Report listing
- Search
- Date filtering
- Reporting period filtering
- Preview where supported
- Download
- Pagination

## Admin Features

- Category CRUD
- Report CRUD
- File upload/replacement
- Publishing
- Archiving
- Search and filtering

Do not assume uploaded reports automatically become interactive analytics dashboards.

---

# 12. Photo & Media Gallery

Create a dynamic category-based gallery.

## Initial Categories

- Training Photos
- Workshop Photos
- Events Gallery
- Achievement Gallery

All categories must be dynamic.

## Gallery Structure

Category → Album → Images

## Category Fields

- Name Hindi
- Name English
- Description
- Cover image
- Display order
- Active status

## Album Fields

- Title Hindi
- Title English
- Description
- Event date
- Cover image
- Category
- Publication status

## Image Fields

- Image file
- Caption Hindi
- Caption English
- Alt text Hindi
- Alt text English
- Display order

## Public Features

- Category listing
- Album listing
- Responsive image grid
- Lightbox
- Previous/next navigation
- Image captions
- Lazy loading

## Admin Features

- Category CRUD
- Album CRUD
- Multiple image uploads
- Image reordering
- Caption editing
- Cover image selection
- Publishing controls

---

# 13. Contact Us and Support

Create a dynamic contact page.

## Contact Information

Admin-managed fields:

- Department name
- Office address Hindi/English
- Support email
- Contact number
- Office hours
- Optional map link

## Contact Support Form

Fields:

- Full name
- Email
- Mobile number (optional)
- Subject
- Message
- Optional category

## Backend Requirements

- Server-side validation
- Spam protection
- Rate limiting
- Secure database storage
- Unique reference number
- Submission timestamp

## Admin Support Management

- View support requests
- Search/filter
- Assign responsible person
- Update status
- Add internal notes
- Mark resolved
- Archive

Statuses:

- New
- In Progress
- Resolved
- Closed

---

# 14. Authentication and User Management

Implement secure administrative authentication.

## Initial Accounts

Seed:

1. One Super Admin
2. One Admin

Passwords must be configured securely and changed on first login.

Do not hardcode credentials.

## Super Admin

- Full system access
- Cannot be created from UI/API
- Cannot be deleted by normal administrators
- Can create admins and custom users
- Can create roles
- Can assign permissions
- Can assign feature responsibilities
- Can view audit logs
- Can manage protected settings

## Admin

- Full content management access
- Can manage all public content modules
- Can manage categories, documents, reports, banners, KPIs, and gallery
- Cannot create Super Admin
- Cannot modify protected Super Admin permissions

Reserve role and permission management for Super Admin unless explicitly configured otherwise.

## Custom Users

Super Admin can create users and assign roles.

Examples:

- Document Manager
- Training Manager
- Team Manager
- Report Manager
- Gallery Manager
- Support Executive

These roles must be configurable, not hardcoded.

---

# 15. Feature-Group RBAC

Use Spatie Laravel Permission.

Create feature groups:

- Dashboard
- Homepage
- Banners
- KPIs
- Notices
- Announcements
- Documents
- Training
- Team
- Reports
- Gallery
- Contact
- Support
- Users
- Roles
- Settings
- Audit Logs

Each feature group supports relevant permissions:

- View
- Create
- Edit
- Delete
- Upload
- Publish
- Export
- Assign

## Responsibility Assignment

Super Admin must be able to assign specific feature groups to specific users.

Example:

| User | Module | Permissions |
|---|---|---|
| User A | Documents | View, Create, Edit, Upload |
| User B | Training | View, Create, Edit, Publish |
| User C | Gallery | Full gallery management |
| Admin | All content modules | Full content access |
| Super Admin | Entire system | Full system access |

## Dynamic Admin Sidebar

Generate navigation according to effective permissions.

Users must only see authorized feature groups.

All backend routes must independently validate permissions.

Never rely only on hiding UI elements.

Support multiple roles per user where appropriate.

Maintain permission assignment audit logs.

---

# 16. Admin Dashboard

Build an attractive SaaS-style admin dashboard.

Dashboard cards:

- Total notices
- Total documents
- Total training resources
- Total team members
- Total reports
- Total gallery images
- Total banners
- Total KPIs
- Pending support requests

Additional sections:

- Recent activity
- Recent uploads
- Pending publication items
- Quick actions
- Content status summary

Super Admin and Admin see all relevant data.

Restricted users see only data associated with their authorized feature groups.

---

# 17. Hindi and English Localization

Support:

- Hindi (`hi-IN`)
- English (`en-IN`)

Use Laravel translation files for interface text.

Use bilingual database fields for dynamic content.

Examples:

- `title_hi`
- `title_en`
- `description_hi`
- `description_en`

## Requirements

- Language selector in header
- Preserve language selection across navigation
- Translate buttons, menus, forms, errors, and messages
- Proper Hindi typography
- Noto Sans Devanagari
- Correct Unicode storage
- Proper Hindi search handling
- No broken or clipped Hindi text
- Clear fallback behavior for missing translations

The Hindi interface must be as polished as the English interface.

---

# 18. Premium UI/UX Design Requirements

This project must look like a modern, high-quality government digital platform.

**Do not create an outdated or generic government website.**

## Public Design

Use:

- Premium government-style header
- Elegant typography
- Professional navy/blue/white palette
- Restrained saffron and green accents
- Beautiful responsive hero carousel
- Attractive KPI cards
- Clean notice board
- Modern resource cards
- Professional team cards
- Elegant gallery layouts
- Spacious sections
- Clear content hierarchy
- Professional footer

## Admin Design

Create a polished enterprise dashboard with:

- Collapsible sidebar
- Clean top navigation
- Dashboard cards
- Searchable tables
- Filter panels
- Modern forms
- Upload previews
- Color pickers
- Drag-and-drop ordering
- Confirmation dialogs
- Toast notifications
- Loading skeletons
- Empty states

## Design Principles

- Prioritize readability.
- Use consistent spacing.
- Avoid clutter.
- Use meaningful icons.
- Keep workflows simple.
- Make frequent actions obvious.
- Use smooth, restrained animations.
- Ensure accessible color contrast.
- Maintain consistent design across every module.

Use Alpine.js and CSS transitions for lightweight animations.

Avoid heavy animation dependencies unless justified.

## Design Approval

Before implementing all modules, prepare:

1. Public homepage desktop design
2. Public homepage mobile design
3. Document repository design
4. Admin dashboard design
5. Admin CRUD management page
6. Role assignment interface

Establish a reusable design system before implementing remaining pages.

---

# 19. Shared Media Management

Create a reusable Laravel media service.

Supported files:

- PDF
- Word
- Excel
- PowerPoint
- Images
- Videos where permitted

Store metadata:

- File ID
- Original filename
- Storage key
- MIME type
- File size
- Checksum
- Uploaded by
- Upload timestamp
- Visibility

Requirements:

- Secure upload
- Extension and MIME validation
- Configurable file size limits
- Unique filenames
- Image optimization
- Safe download
- Access control
- File cleanup
- Orphaned file handling

Do not duplicate upload logic in each module.

Use private storage for restricted files.

Public files must be explicitly published.

---

# 20. Database Design

Use MariaDB with:

- InnoDB
- `utf8mb4`
- Foreign keys
- Appropriate indexes
- Eloquent ORM
- Migrations
- Seeders
- Transactions

Suggested entities:

## Authentication

- users
- roles
- permissions
- model_has_roles
- model_has_permissions
- role_has_permissions
- feature_groups
- user_feature_assignments
- audit_logs

## Homepage

- department_information
- notices
- announcements
- quick_links
- banners
- kpis
- homepage_sections

## Documents

- document_categories
- documents
- document_versions

## Training

- training_categories
- training_resources
- faq_categories
- faqs

## Team

- team_categories
- team_members

## Reports

- report_categories
- reports

## Gallery

- gallery_categories
- gallery_albums
- gallery_images

## Contact

- contact_details
- support_requests
- support_notes

## Shared

- media_assets
- site_settings

Use appropriate data types and constraints.

Do not store large binary files directly in MariaDB.

---

# 21. Content Lifecycle

Implement relevant content states:

- Draft
- Published
- Archived
- Scheduled

Support:

- Create
- Edit
- Preview
- Publish
- Unpublish
- Archive
- Scheduled publication
- Expiration

Public pages must never expose draft or archived content.

Scheduled publishing must work without persistent queue workers.

Use request-time publication eligibility checks, with optional cron support.

---

# 22. Search and Filtering

Provide reusable server-side search, sorting, filtering, and pagination.

Support:

- Hindi keyword search
- English keyword search
- Category filtering
- Date filtering
- File type filtering
- Office/block filtering where relevant

Use efficient MariaDB queries.

Do not load entire datasets into the browser.

---

# 23. Website Settings

Create a centralized admin settings module.

Fields:

- Website title Hindi/English
- Department name
- Logo
- Favicon
- Contact details
- Footer content
- Copyright text
- Social media links
- Homepage section visibility
- Carousel configuration
- Default language
- SEO metadata
- Upload limits within server constraints

Protect sensitive settings using permissions.

---

# 24. Security Requirements

Implement:

- HTTPS
- Secure password hashing
- Session regeneration after login
- CSRF protection
- XSS prevention
- SQL injection protection
- Server-side validation
- Rate limiting
- Login throttling
- Secure file uploads
- Protected admin routes
- Role and permission enforcement
- Audit logging
- Secure configuration
- Database backups
- Appropriate security headers

Use Laravel's built-in security mechanisms wherever possible.

Consider MFA for privileged accounts.

Do not expose sensitive data or credentials in public responses.

---

# 25. Audit Logs

Record important administrative actions.

Fields:

- User ID
- Action
- Module
- Entity type
- Entity ID
- Timestamp
- IP address where appropriate
- Change summary

Track:

- Login activity
- Content creation
- Content updates
- Deletion
- Publication changes
- File uploads
- User creation
- Role changes
- Permission changes
- Settings changes

Audit logs must not contain passwords or secrets.

---

# 26. Existing Website Migration

The existing portal uses ASP.NET `.aspx` URLs.

The new system uses Laravel.

Before replacing the website:

1. Inspect existing public pages.
2. Inventory existing content.
3. Identify existing files and documents.
4. Analyze any available SQL Server export.
5. Map old data to MariaDB tables.
6. Convert SQL Server data types.
7. Preserve Hindi content encoding.
8. Migrate files and references.
9. Validate record counts and relationships.
10. Create redirects for important `.aspx` URLs.
11. Test migrated content.
12. Prepare backup and rollback procedures.

Do not modify or replace the live production portal during development.

---

# 27. Testing Requirements

Implement automated unit, feature, and browser tests.

Critical test cases:

1. Super Admin login works.
2. Admin login works.
3. Initial users are seeded securely.
4. Super Admin cannot be created through UI/API.
5. Super Admin can create roles.
6. Feature permissions work.
7. Restricted users cannot access unauthorized routes.
8. Dynamic categories can be created.
9. Documents upload and download correctly.
10. Reports upload correctly.
11. Training resources display correctly.
12. Team profiles display correctly.
13. Gallery albums and images work.
14. KPIs update on the public homepage.
15. Banners update on the public homepage.
16. Hindi/English switching works.
17. Draft content is hidden publicly.
18. Contact requests are saved.
19. Audit logs are generated.
20. Mobile layouts work correctly.
21. Invalid files are rejected.
22. Deployment works without production terminal access.

Test authorization at the backend, not only in the UI.

---

# 28. Implementation Phases

## Phase 1 — Discovery and Architecture

Deliver:

- Existing portal analysis
- Requirement validation
- Hosting compatibility checklist
- Laravel architecture
- MariaDB ER diagram
- RBAC permission matrix
- Public/admin sitemap
- UI design system
- Deployment architecture
- Implementation plan

Stop and request approval.

## Phase 2 — Project Foundation

Implement:

- Laravel setup
- MariaDB configuration
- Blade layouts
- Tailwind setup
- Alpine.js setup
- Localization
- Shared components
- Migrations
- Base seeders

## Phase 3 — Authentication and RBAC

Implement:

- Login/logout
- Super Admin seed
- Admin seed
- User management
- Role management
- Permissions
- Feature assignments
- Dynamic sidebar
- Audit logging

## Phase 4 — Homepage CMS

Implement:

- Department introduction
- Notices
- Announcements
- Quick links
- Today's updates
- Banners
- KPIs
- Homepage section configuration

## Phase 5 — Resource Management

Implement:

- Documents
- Dynamic document categories
- Training resources
- Training categories
- FAQs
- Reports
- Dynamic report categories

## Phase 6 — Team and Gallery

Implement:

- Team categories
- Team members
- Roles and responsibilities
- Gallery categories
- Albums
- Image management

## Phase 7 — Contact and Settings

Implement:

- Contact details
- Support requests
- Support administration
- Website settings
- Footer management
- SEO configuration

## Phase 8 — Production Readiness

Complete:

- Automated tests
- Security testing
- Hindi localization testing
- Accessibility review
- Performance optimization
- Hosting deployment package
- Migration verification
- Backup/rollback documentation
- Administrator user guide

---

# 29. Required Documentation

Generate:

- `README.md`
- `PRD.md`
- `ARCHITECTURE.md`
- `DATABASE.md`
- `API_SPEC.md`
- `RBAC.md`
- `UI_UX_GUIDELINES.md`
- `I18N.md`
- `SECURITY.md`
- `IMPLEMENTATION_PLAN.md`
- `DEPLOYMENT.md`
- `.env.example`

Keep documentation synchronized with the implementation.

---

# 30. Final Development Instructions

Follow these rules strictly:

1. Do not begin implementation before completing architecture and design planning.
2. Do not change the finalized technology stack without approval.
3. Do not create a separate frontend or backend application.
4. Do not hardcode dynamic website content.
5. Do not use SQL Server in the new application.
6. Do not require production terminal access.
7. Do not introduce mandatory Redis or persistent worker dependencies.
8. Do not ignore Hindi localization.
9. Do not implement UI-only CRUD operations.
10. Do not bypass server-side authorization.
11. Do not expose private files publicly.
12. Do not compromise security to simplify shared-hosting deployment.
13. Do not build an outdated or visually generic interface.
14. Do not mark features complete without functional testing.
15. Do not deploy to the existing live domain until migration and acceptance testing are complete.

## Expected First Response

Before coding, present:

1. Understanding of the complete project.
2. Existing portal analysis and gaps.
3. Proposed Laravel monolith architecture.
4. MariaDB database design.
5. Authentication and RBAC strategy.
6. Public and admin page structure.
7. Premium UI/UX design direction.
8. HostingRaja compatibility risks.
9. Shared-hosting deployment strategy.
10. Phased implementation plan.
11. Open questions and required approvals.

**Wait for approval before writing production code.**

## Final Project Goal

Build a complete, modern, premium, user-friendly, bilingual Janjgir e-Governance Portal using Laravel 12, MariaDB, Blade, Tailwind CSS, and Alpine.js.

The result must be a secure, maintainable, fully dynamic government CMS with granular RBAC, comprehensive administration, responsive public pages, and reliable deployment on HostingRaja shared hosting without terminal access.

The public website must be attractive and intuitive for citizens and government employees. The admin panel must be simple enough for non-technical staff to manage independently.

**Deliver a complete working application, not a static template, incomplete prototype, or UI demonstration.**