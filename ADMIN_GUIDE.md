# Administrator Guide

## First Installation

1. Follow [LOCAL_DEVELOPMENT.md](LOCAL_DEVELOPMENT.md) for a local install, or [DEPLOYMENT.md](DEPLOYMENT.md) for shared hosting. Confirm the application can connect to MariaDB and apply migrations before opening the admin area.
2. Set a fresh 64-character hexadecimal `APP_SETUP_KEY` in the private environment. Visit `/admin/initialize` over HTTPS and enter the key plus operator-selected credentials for the initial Super Admin and Admin accounts. No default passwords are provided.
3. Both accounts must change their initial passwords at first sign-in. Remove `APP_SETUP_KEY` from the environment after setup. The initializer is permanently disabled after successful completion.

## Roles, Users, and Access

The sidebar only shows modules the signed-in user can view, but each operation is also checked on the server. Assign work through **Users** and **Roles**. Roles can group feature permissions; users can also receive explicit feature permissions when the operator has `users.assign`. Give only the permissions each role needs, and use separate upload, publish, delete, and assignment permissions where available.

The Super Admin account and role are protected from normal user-management operations. Admin accounts cannot grant Super Admin access. Disable accounts that should no longer sign in; avoid deleting users who own content or support work.

## Managing Public Content

- **Demo drafts:** Database seeding adds clearly marked bilingual draft examples for the public notice board, announcements, and manual homepage updates. Replace them with verified information before publishing; drafts do not appear publicly.
- **Reference categories:** Database seeding adds the bilingual document, training, team, report, and gallery categories listed in `REQUIREMENTS.md`. New categories are active and editable; reseeding preserves existing edits and status.
- **Demo documents:** One clearly marked bilingual-metadata draft and private English placeholder PDF are seeded per document category. Replace each attachment and metadata with verified bilingual content before publication.
- **Homepage:** Edit department information and manage notices, announcements, banners, KPIs, quick links, manual updates, and homepage section order/visibility. New banner buttons default to `/about`; change the button URL only when a different destination is intended. Reorder sections and homepage content lists by dragging rows or using the keyboard up/down controls, then save the order; numeric order fields remain as a fallback. Resource, team, gallery, and category lists use the same save-order controls, and gallery image rows reorder in the album editor. Banner image and KPI cards show previews while editing. Use English and Hindi fields. Schedule content where applicable; publication permissions are separate from ordinary edit permissions.
- **Documents:** Maintain dynamic categories and use bulk intake to create one unpublished draft record per selected file (up to 10 at a time). The filename is only a temporary title in both languages; complete the Hindi/English metadata, publish the private file in Media, then publish the document record when ready. Each file is subject to the configured per-file limit and the server's aggregate request limits. Record versions; existing document versions are retained when a file is replaced.
- **Training and FAQs:** Manage categories and bilingual resources. Prefer approved external video hosts for large videos; keep uploaded files within the server's configured limits.
- **Reports:** Maintain categories, bilingual metadata, publication state, and authorized media attachments.
- **Team and gallery:** Maintain team categories/profiles and gallery categories/albums/images. Add descriptive Hindi and English captions and alt text. Contact details remain private unless explicit public-display approval is enabled.
- **About Us:** The public About Us page is linked after Home. Use **About Us sections** in the admin sidebar to create as many sections as needed, enter bilingual titles and content, publish or schedule each section, and set its display order. Draft and archived sections remain hidden publicly. Content supports safe Markdown; raw HTML is removed when displayed.
- **Website settings:** Edit website identity, SEO, footer, social links, and carousel behavior under **Website Settings**.
- **Contact and support:** Edit public contact information and website settings. Review incoming requests in **Support**, assign them to staff, update status, and use internal notes for staff-only context. Do not place sensitive details in public notes.
- **Media library:** New files are private by default. Publish only assets intended for public display. Deletion is refused while an asset is referenced. Image pickers preview the selected public image; use the picker in content forms instead of entering media IDs.
- **Audit log:** Review administrative actions by date and event. Audit entries record action and field names without storing submitted values or credentials.

## Localization and Accessibility

Use the site language control to switch between English and Hindi; the choice is retained in the session. Enter both language versions for public content and review Hindi text for spelling and line wrapping. Provide useful alt text for meaningful images. Rotating banners can be paused and respect reduced-motion preferences.

## Release and Recovery

Back up the database and private media before schema changes. On hosting without shell access, use only the documented one-time `/deployment/migrate` workflow with a fresh `PORTAL_MIGRATION_KEY`; remove the key after success. Never expose an unrestricted Artisan endpoint or place `.env` or private media under public web access. Keep a verified rollback copy of the prior release and follow [DEPLOYMENT.md](DEPLOYMENT.md) for package review and restore guidance.
