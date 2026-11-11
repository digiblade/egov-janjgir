# Legacy URL Redirects

This is a deliberately limited redirect list. Add further `.aspx` mappings only after the legacy site's URL inventory has been supplied and reviewed.

| Legacy URL | Destination | Status | Evidence |
|---|---|---|---|
| `/Public/Default.aspx` | `/` | Permanent GET/HEAD redirect (301) | The existing-site URL is recorded in `REQUIREMENTS.md` §1; the site root is the new portal homepage. |
| `/Public/Training/Default.aspx` | `/training` | Permanent GET/HEAD redirect (301) | Confirmed by the project web search as the existing training landing page; `/training` is the new training index. |

No other legacy `.aspx` URL is currently mapped. The requirements' module/navigation list describes the new portal destinations, not exact source `.aspx` paths, so it does not support additional redirects. Department pages, resource detail URLs, files, query-string behavior, and redirects for unlisted paths require a full source URL/content export before they can be safely mapped.
