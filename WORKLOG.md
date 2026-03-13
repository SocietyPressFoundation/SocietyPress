# SocietyPress — WORKLOG

## v0.35d — 2026-03-11

### the society Child Theme — Header & Footer Fixes
- Fixed desktop search form overlapping Store nav item: added `position: static; transform: none; z-index: auto` to `.sp-header-search-desktop` (parent CSS set `position: absolute` for a toggle-style search, but the society uses always-visible inline)
- Hidden cart icon via `display: none !important` (overrides inline style from `sp_user_menu()`)
- Removed gray pill background/border from `.sp-user-trigger` — cleaner look matching ENS reference
- Added `margin-left: 6px` to `.sp-user-caret` so dropdown arrow doesn't clip the username
- Aligned header to `--sp-content-width` guides (`max-width: var(--sp-content-width, 1400px); margin: 0 auto`)
- Synced git clone `theme-society/` with authoritative server `society/` directory (files had diverged)
- Discovered deploy path mismatch: active theme is `society/`, not `societypress-society/` — updated memory

## v0.32d — 2026-03-08

### Full Codebase Audit
- Audited all 43,745 lines of societypress.php across 6 parallel passes
- Audited all 39 database tables with schemas and row counts
- Documented 22 known issues (3 critical, 10 should-fix, 9 cosmetic/low-priority)

### Library Catalog Enhancement
- Rewrote `sp_render_builder_widget_library_catalog()` — OPAC-style frontend with collection stats header, tabbed search (keyword/title/author/subject/call number), browse-by-type cards with SVG icons, popular subjects tag cloud
- Updated `sp_builder_fields_library_catalog()` — added `show_landing` setting
- Attached library_catalog widget to catalog page (ID 39), fixed template to `sp-builder`

### Documentation Suite (new `Docs/` directory)
- `ARCHITECTURE.md` — Full technical reference: 39 tables, 34+ AJAX endpoints, 5 cron jobs, 10 templates, 19 widgets, admin menu map, encryption, email system, GDPR
- `FEATURES.md` — Complete 18-module feature inventory with summary matrix
- `KNOWN-ISSUES.md` — 22 items from audit, organized by severity with fix recommendations
- `PROJECT-PROMPT.md` — Drop-in recreation prompt for bootstrapping new sessions

### Rewrites
- `TO-DO.md` — Complete rewrite reflecting actual codebase state from audit
- `README.md` — Updated to v0.30d with accurate feature descriptions

## v0.27d — 2026-03-03

### Member Portal (My Account)
- Created WP page (ID 148, slug `my-account`, template `page-my-account.php`)
- Fixed broken Research Surnames: replaced nonexistent `location` column with real schema (`county`, `state`, `country`, `year_from`, `year_to`) in both template and plugin handler
- Added Interests & Skills section (two textareas, new `update_interests` handler)
- Added Blast Email Opt-out checkbox to Communication Preferences
- Moved all inline styles (surnames, events sections) to scoped CSS classes
- Full i18n pass: all user-facing strings wrapped in `__()` / `esc_html__()` / `esc_attr__()` with `societypress` text domain
- Added success messages for interests, surnames, and event cancellation

### Directory
- Added `wp_nav_menu_objects` filter to hide Directory nav link for logged-out visitors (template-based, not hardcoded to menu item ID)
- Changed surname filter label from "Surname" to "Surname Being Researched"
- Set matching height (38px + box-sizing) on filter selects and inputs

### User Cleanup
- Merged duplicate `charleswstricklin` accounts (282 + 3958): moved member record to 282, fixed email typo (txfsghs → upstream-society), deleted empty 3958

### Known Issues
- Calendar width inconsistency: current month full-width, other months narrower. Server HTML is identical — needs browser-side investigation.
