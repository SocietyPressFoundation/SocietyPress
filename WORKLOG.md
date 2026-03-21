# SocietyPress — WORKLOG

## v0.39d — 2026-03-20

### Child Themes, Custom Theme Builder & Governance Overhaul

**5 Default Child Themes:**
- Heritage (warm brown/cream/gold, Merriweather), Coastline (navy/white/sky blue, Inter), Prairie (sage green/cream/clay, Lora), Ledger (charcoal/ivory/burgundy, Source Sans 3), Parlor (plum/ivory/rose gold, EB Garamond)
- Fixed all child theme functions.php — child stylesheets weren't being enqueued (`:root` overrides never loaded)
- Theme registry updated with all 6 entries (the society + 5 new)

**Custom Theme Builder:**
- "Create Your Own Theme" card with purple gradient in the gallery
- Modal: theme name, 7 color pickers with hex text fields, 2 font selectors, live preview swatch
- AJAX create/update/delete — generates real WordPress child themes on disk
- Custom themes tracked in `sp_custom_themes` option for re-editing
- "Custom" badge, Edit/Delete buttons on custom theme cards

**"Match My Current Site" Color Extractor:**
- Paste a URL → fetch page → parse inline CSS, external stylesheets (skip frameworks), Google Fonts links, meta theme-color
- Extract colors by context (header, footer, links, headings, buttons) with CSS cascade awareness
- Detect fonts from Google Fonts URLs, map to available font options
- Confidence scoring (high/medium/low) based on signal count
- Fixed `!important` breaking color normalization, cascade-ordered CSS chunks (external first, inline last)

**Theme Preview System:**
- "Preview" button on every non-active theme card
- Opens real homepage rendered with the selected theme via `stylesheet`/`template` filter hooks
- Purple preview banner: "Previewing: [Name]" with Activate and Back to Themes buttons
- Admin bar hidden during preview, body margin-top for banner clearance

**Governance — Leadership & Committees:**
- New `sp_render_leadership_page()` with two sections: Officers & Board (card grid) + Committees (grouped collapsible sections)
- Added `role_type` column to `sp_volunteer_roles` (officer/committee/volunteer) with auto-migration
- Volunteer Roster split to separate `sp-volunteer-roster` menu item
- SP_Volunteers_List_Table filtered to volunteer-type roles only

**Bug Fixes:**
- Duplicate "Log Out" in admin user dropdown
- `\n` literal in confirm dialogs (single-quoted PHP string)

**Infrastructure:**
- Demo site cloned from example.org (database + uploads + themes)
- example.org being retired — demo.getsocietypress.org is primary dev site

## v0.38d — 2026-03-18

### Comprehensive Code Review & Fix Session
Full 52,000-line code review with parallel agent analysis, followed by systematic fix pass.

**Critical Bugs Fixed:**
- DB version never saved after upgrade — dbDelta and all seeding ran on every admin page load
- Record collection field ID orphaning — editing field schema broke all existing record data
- Incomplete cascade delete — extracted `sp_cascade_delete_member_data()` covering 16 related tables

**Security Fixed:**
- Document downloads bypass members-only access — added `sp_ajax_document_download()` AJAX handler
- Guest registration cancel ownership gap — null user_id check
- Missing nonce on blast recipient count AJAX
- Hardcoded the society email (President@upstream-society.org) → pulls from org_email setting
- Unescaped `get_the_title()` XSS

**Code Quality (12 fixes):**
- ~30 `date()` → `wp_date()` for timezone-correct display
- ~40 `admin_url()` wrapped in `esc_url()`
- Stripe refund handles 'pending' status
- ICS line folding uses `mb_strlen`/`mb_substr` for multibyte safety
- `preferred_name` fallback prevents PHP 8.x notice
- `wp_enqueue_media()` moved before output in speaker edit
- N+1 query eliminated on frontend event listing (batch registration count query)
- Library stats transients invalidated on write operations
- Email template uses brand colors from Design settings
- Dashicons only loaded for logged-in users (saves 46KB for visitors)
- Merge tag documentation unified to `{{double_braces}}`
- Blank template moved from `/tmp/` to `wp_upload_dir()`

**i18n:**
- Comprehensive pass: ~500+ strings wrapped across 4 agent passes
- Text domain `societypress` now appears 2,564+ times (~95% coverage)

**Localization:**
- Currency setting: `sp_format_currency()` helper + `currency_symbol`/`currency_position` settings
- Replaced all 44 hardcoded `$` currency instances (PHP + JS)
- Fixed pre-existing bug: `store_acq_code` and `store_intro_text` settings weren't saving

**Structural Improvements:**
- 25 GET-based delete handlers converted to POST forms (groups, pages, payments, events, speakers, albums, etc.)
- Font-family map extracted to `sp_get_font_family_css()` / `sp_get_font_family_options()` (was defined 3x)
- Member statuses extracted to `sp_get_member_statuses()` (was defined independently in multiple places)
- Library catalog page template unified to use OPAC-style widget (deprecated basic 85-line version)
- Donation acknowledgment email wired into template editor (4th tab, merge tags, no more hardcoded English)

**New: Competitive Matrix**
- `Docs/COMPETITIVE-MATRIX.md` — markdown comparison vs Blue Crab/ENS, Wild Apricot, ClubExpress
- `Docs/competitive-matrix.html` — styled HTML graphic with SocietyPress branding

**New: One-Click Installer**
- `installer/install.php` — single-file installer: requirements check → config form → downloads WordPress + SocietyPress → configures → activates → self-deletes → redirects to setup wizard

**New: 4 Demo Child Themes**
- Heritage (brown/cream/gold, Merriweather serif)
- Coastline (navy/white/sky blue, Inter sans)
- Prairie (forest green/wheat/sage, Lora serif)
- Ledger (charcoal/ivory/burgundy, Source Sans 3)

**Documentation Updated:**
- FEATURES.md, ARCHITECTURE.md, PROJECT-PROMPT.md brought up to v0.38d
- KNOWN-ISSUES.md rewritten (43 items tracked, 41 fixed, 2 deferred)
- TO-DO.md updated: voting/elections, PWA, bbPress, data portability, localization, installer, demo themes

## v0.37d — 2026-03-15

### Google Analytics, Couples, Roles, Update System, Privacy Policy
(see git log for details)

## v0.36d — 2026-03-14

### Documents Module — Bulk Upload
- Added bulk upload admin page (`sp-document-bulk-upload`): select multiple files from WP Media Library, set shared category/access/status, review auto-generated titles and auto-detected dates, submit all at once
- Title auto-generation: strips file extension, removes ENS-style trailing numeric IDs (`_1630604484`), replaces underscores/hyphens with spaces
- Date auto-detection: parses YYYY-MM-DD, MM-DD-YYYY, "Month YYYY", "Month DD YYYY", "DD Month YYYY", "YYYY_M_Month" patterns from filenames
- Duplicate prevention: files already in the preview table are skipped on re-select
- "Bulk Upload" button added to Documents list page alongside "Add New"
- Success notice with proper `_n()` pluralization on redirect
- Bulk upload handler: validates nonce + capability, inserts one `sp_documents` row per file with shared settings

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
