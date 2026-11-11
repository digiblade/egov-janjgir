# Public Homepage Page Overrides

> **PROJECT:** Janjgir e-Governance Portal
> **Generated:** 2026-10-27 10:58:35
> **Page Type:** Bilingual civic information portal

> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/MASTER.md`).
> Only deviations from the Master are documented here. For all other rules, refer to the Master.

---

## Page-Specific Rules

The homepage is for residents and government staff seeking information, documents, training, reports, and contact details. It is not a sales funnel. Treat current notices and public resources as primary tasks; staff sign-in is a secondary utility link.

### Layout Overrides

- **Max Width:** 1200px (standard)
- **Layout:** Full-width sections, centered content
- **Responsive order:** Department identity and search > banner/announcement > notices and today's updates > quick links > KPIs > recent documents/training/reports > contact/footer.
- Use bilingual labels with a persistent language switch; keep text readable and unclipped in Hindi.

### Spacing Overrides

- No overrides — use Master spacing

### Typography Overrides

- No overrides — use Master typography

### Color Overrides

- No overrides — use Master colors

### Component Overrides

- Avoid: Red/green only for error/success
- Avoid: Ask users to retype the same address or account data without necessity
- Avoid: Empty or missing alt attributes
- Banner carousel must include pause/previous/next controls, stop autoplay on keyboard focus, and disable autoplay under reduced motion.
- Use a single primary action per section. Prefer blue primary controls with white text; green accent controls use black text.

---

## Page-Specific Components

- No unique components for this page

---

## Recommendations

- Effects: restrained 150–250ms state transitions; no decorative motion. Honor `prefers-reduced-motion`.
- Accessibility: visible keyboard focus, skip link, semantic headings, accessible carousel controls, descriptive alt text, and text/icon in addition to color.
- Search: visible label, Hindi/English query support, clear empty and error states, and server-side pagination.
- Verify at 375px, 768px, 1024px, and 1440px without horizontal overflow.
