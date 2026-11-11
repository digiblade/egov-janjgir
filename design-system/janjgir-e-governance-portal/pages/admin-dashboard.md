# Staff Admin Dashboard Page Overrides

> **PROJECT:** Janjgir e-Governance Portal
> **Generated:** 2026-10-27 10:58:35
> **Page Type:** Dashboard / Data View

> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/MASTER.md`).
> Only deviations from the Master are documented here. For all other rules, refer to the Master.

---

## Page-Specific Rules

This is an authenticated staff workspace. Restrict every menu item and operation by backend permissions. The dashboard should prioritize content status, recent activity, pending support, and the user's authorized modules.

### Layout Overrides

- **Max Width:** 1400px or full-width
- **Grid:** 12-column grid for data flexibility
- **Navigation:** Collapsible permission-filtered sidebar, compact top bar, breadcrumb and page title. Keep staff identity and logout visible.

### Spacing Overrides

- **Content Density:** Medium — prioritize scanability over showing every metric at once

### Typography Overrides

- No overrides — use Master typography

### Color Overrides

- No overrides — use Master colors

### Component Overrides

- Avoid: Wide tables breaking layout
- Avoid: Use arbitrary large z-index values
- Avoid: Icon buttons without labels
- Avoid: Wide tables breaking layout
- Provide table-to-card responsive behavior at narrow widths; do not require page-level horizontal scrolling.
- Keep one primary action per screen and provide clear search/filter, empty, loading, and error states.
- Use labeled forms, inline validation, retained input on errors, and confirmation for destructive changes.

---

## Page-Specific Components

- No unique components for this page

---

## Recommendations

- Effects: Subtle row highlighting and restrained transition feedback; avoid animation that delays interaction.
- Responsive: Convert dense data tables to stacked cards or a deliberate compact table with no viewport overflow.
- Layout: Define z-index scale system (10 20 30 50)
- Accessibility: Visible focus, keyboard-operable sidebar, labels on icon-only controls, status text beyond color, and sufficient contrast.
