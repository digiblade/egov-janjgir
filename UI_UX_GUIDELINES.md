# UI/UX Guidelines

Read `design-system/janjgir-e-governance-portal/MASTER.md` before creating or changing UI. The project direction is an accessible, contemporary civic information portal with a distinct staff sign-in path, not a sales gateway. Keep the public experience calm and content-first: government identity, bilingual navigation, current notices, key services/resources, trusted contact details, and clear routes to relevant material. The admin is a separate workspace with a collapsible navigation sidebar, role-scoped actions, searchable tables, filters, clear forms, upload previews, empty states, and confirmations for destructive actions.

## Visual system

Use the documented navy/blue and white palette with restrained green/saffron accents, semantic CSS tokens, consistent spacing, and one coherent SVG icon family. The generated master contains conflicting sample button styling: use blue `#1E40AF` with white text for primary actions (AA contrast), and green `#16A34A` only with dark text or as an accent. Never use white text on that green. Do not use gradients, emoji icons, decorative motion, or scattered color literals. Use heading/body fonts from the master with a reliable local sans-serif fallback; include Noto Sans Devanagari for Hindi, with `font-display: swap` and no unnecessary font preloads.

## Responsive and accessible behavior

Design mobile-first at 375, 768, 1024, and 1440px; prevent horizontal scrolling and reserve space for responsive images. Body copy should remain readable (16px minimum on mobile) with controlled line length. Use semantic headings, skip links, visible keyboard focus, labeled inputs, field-level errors, descriptive alt text, and ARIA names/states for icon controls. Maintain 4.5:1 text contrast; don't rely on color alone. Ensure touch targets are at least 44px where practical and provide adequate spacing. Respect `prefers-reduced-motion`; the carousel must have pause/stop controls and stop on focus/reduced motion. Keep focus visible around sticky navigation and dialogs.

## Admin workflow

Give each page one clear primary action. Show save/upload progress and disabled states, retain user input after validation errors, and make publication state/date visible. Provide preview before publishing, confirmation before destructive actions, and keyboard alternatives to drag ordering. Role-scoped dashboards and menus must correspond to server-side access. Check the language switch, forms, long Hindi strings, and real content lengths in both languages.
