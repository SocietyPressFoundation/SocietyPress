# SocietyPress — WORKLOG
## v0.44d — 2026-03-26

### Session: Sample Data, Importer Fixes, Infrastructure

**Infrastructure:**
- SSH config alias (`ssh skystra`) — eliminates long SSH commands
- Local git clone at `~/Sites/SocietyPress/`
- Deploy script (`deploy.sh`) — one command to push plugin/themes to server
- Claude Code permissions configured for auto-approval of common operations

**Importer Fixes (Major):**
- 20+ ENS CSV fields that were dumped to custom meta now map to proper `sp_members` columns: contact, use_maiden, image_filename, toll_free_phone, international_phone, preferred_phone, alt_phone, alt_international_phone, alt_preferred_phone, alt_email, seasonal_address_2, membership_type, max_members, receive_print, acct_primary, login_count, last_login_date, last_updated_by, last_updated_date, ens_record_id
- Membership Tie ID now maps to `household_id` (links related members as households)
- Lifetime field now maps to `lifetime` column instead of hardcoded 0
- Deceased field auto-maps correctly (label mismatch fixed)
- Mapper UI dropdown now includes all fields with correct labels and auto-selection
- Fixed member_type detection: individuals with a `File Name` value no longer flagged as organizations — now checks `Membership Type` column
- `$member_data` insert array updated to write all new fields to database

**Calendar Bug Fix:**
- `.sp-events-wrap` was missing `max-width: var(--sp-content-width, 1100px)` — calendar view stretched to full viewport width on events listing page

**Admin UI:**
- "Import Membership List" button added to Members page header
- SocietyPress top-level menu click now goes to Dashboard (JS was clearing the submenu entry, causing it to fall through to audit log)

**Sample Data:**
- Generated 1,500 fake member CSV in ENS format (930 Single, 435 Joint, 75 Exchange Org, 30 Subscription Org, 15 Life, 15 Sustaining)
- ~90% regional addresses (60% local metro), realistic age/gender distribution
- 200 portrait photos downloaded from randomuser.me, renamed to `firstname-middlename-lastname-photo.jpg`, uploaded to server
- 1,494 members imported successfully (6 skipped as duplicate name+email collisions in generated data)

**Localization:**
- Generated `societypress.pot` translation file (9,121 lines) via `wp i18n make-pot`

**TO-DO Updates:**
- Added Membership Reports section (totals, by-tier, retention, revenue, exportable)
- Added UX section with AJAX progress bars for long-running operations

## v0.43d — 2026-03-24

### Inline Styles → CSS Classes Migration (Major Pass)

Systematic extraction of inline `style=` attributes from PHP template output into named, commented CSS classes. Each function now has a scoped `<style>` block at the top of its HTML output with prefixed class names (e.g., `sp-themes-`, `sp-export-`, `sp-leadership-`).

**Scale:** 1,722 → 753 inline styles remaining (~970 extracted across 30+ functions, ~56% reduction)

**Functions fully or substantially migrated:**
- sp_render_themes_page (90 → ~7 dynamic)
- sp_render_leadership_page (46 extracted, 3 kept)
- sp_render_dashboard_page (35 extracted, 1 dynamic PHP margin)
- sp_render_settings_design_page (37 extracted, 4 dynamic swatches)
- sp_render_import_page (40 extracted, 1 JS toggle)
- sp_render_export_page (22 extracted)
- sp_render_builder_widget_surname_lookup (32 extracted, 2 JS toggles)
- sp_frontend_help_requests (26 extracted, 7 kept — 6 email inline styles, 1 dynamic color)
- sp_builder_fields_hero_slider (31 extracted, 0 kept)
- sp_render_volunteer_opportunities_frontend (24 extracted, 5 dynamic PHP colors)
- sp_render_member_edit_page (25 extracted, 4 JS toggles)
- sp_render_event_registrations_section (26 extracted, 4 dynamic badges + JS toggles)
- sp_render_annual_report_page (20 extracted, 3 dynamic chart dimensions)
- sp_render_reports_page (27 extracted, 0 kept)
- sp_render_library_catalog_page (17 extracted, 0 kept + 3 bonus i18n wraps)
- sp_render_email_log_page (22 extracted, 1 dynamic badge)
- sp_render_privacy_policy_content (25 extracted, 0 kept)
- sp_render_library_enrich_page (18 extracted, 3 JS toggles)
- sp_render_event_categories_page (18 extracted, 6 dynamic + JS toggles)
- sp_render_external_calendars_page (20 extracted, 1 dynamic status color)
- sp_render_user_access_page (21 extracted, 0 kept)
- sp_render_import_events_page (15 extracted, 0 kept)
- sp_frontend_library_catalog (21 extracted, 0 kept)
- sp_render_builder_widget_photo_gallery (18 extracted, 3 dynamic widths + display:none)
- sp_render_volunteer_hours_page (19 extracted, 0 kept)
- sp_render_order_detail_page (17 extracted, 0 kept)
- sp_render_library_item_edit_page (18 extracted, 0 kept)
- sp_render_newsletter_archive_page (17 extracted, 0 kept)
- sp_render_email_log_detail (16 extracted, 1 dynamic badge)
- sp_render_record_collections_page (15 extracted, 0 kept)
- sp_render_record_collection_edit_page (11 extracted, 4 JS innerHTML)
- sp_render_newsletter_edit_page (10 extracted, 3 JS toggles)
- sp_render_donations_page (12 extracted, 0 kept)
- sp_render_document_bulk_upload_page (10 extracted, 5 JS innerHTML)
- sp_render_blast_email_compose_page (14 extracted, 2 dynamic PHP)
- sp_render_event_detail (11 extracted, 4 dynamic PHP)
- sp_render_builder_widget_member_stats (15 extracted, 0 kept)
- sp_render_album_edit_page (14 extracted, 1 JS toggle)
- Settings functions (3 extracted, 13 dynamic)

**Remaining 753 inline styles are:**
- Dynamic PHP variable values (colors, widths, display toggles based on DB state)
- JavaScript display:none toggles
- Styles inside JS innerHTML/template strings
- ~30 smaller functions (10-14 each) for future passes

**No functionality changes. PHP lint clean throughout. Plugin grew from ~58,000 to ~61,700 lines (CSS class blocks account for the growth).**

---

## v0.42d — 2026-03-23

### i18n Cleanup

- Generated .pot translation template (plugin/languages/societypress.pot) — zero warnings
- Added ~80 translator comments (/* translators: */ format) across plugin for all placeholder strings
- Numbered all multi-placeholder strings (%s → %1$s, %2$s) so translators can reorder for their language
- Split dual-context "Edit Member: %s" into _x() with distinct contexts (organization vs individual)

### Accessibility / UX Pass

Full UX review via @ux-reviewer agent — 25 findings, 22 fixed this session.

**High impact:**
- Skip navigation link (WCAG 2.4.1) — parent theme + the society headers + all 6 content templates
- Conditional h1/p site title — h1 only on front page, p elsewhere (WCAG 2.4.6)
- Mobile hamburger menu for parent theme — was wrapping/stacking below 768px, now proper toggle
- User dropdown click/tap toggle for touch devices (JS enhancement over CSS :hover)
- Custom confirmation modal (spConfirm) replacing browser confirm() on 4 most destructive member actions
- Admin sidebar visual separators between menu groups (CSS-only, uses existing data-group attrs)
- Inline form errors on member edit — field-level aria-describedby, red border, auto-focus

**Medium impact:**
- Setup wizard step labels + aria-current for screen readers
- Module toggle switches aria-labelledby associations
- Blast email filter tabs aria-current="page"
- Join form: tier radio type cast fix, "Join Now" button label, state field placeholder + hint
- Dashboard + member list status icons (Unicode symbols for colorblind users)
- Membership plans inline editing focus management + aria-live announcements
- Design settings "Jump to" anchor navigation
- Logo preview alt text, event image button context, table scope="col"
- Empty state actionable guidance (library, resources, donations)
- Search input focus ring contrast on dark header

### Dashboard Inline Styles Refactor

First section of inline-styles-to-CSS migration. Extracted all 42 inline style= attributes from sp_render_dashboard_page() into named CSS classes in the existing style block. Only 2 dynamic PHP margin values remain. ~1,660 inline styles remain across other admin functions.

### TO-DO Updates

- Added database backup system (manual download + server storage + email delivery with size guard)
- Marked .pot file generation done

---


## v0.41d — 2026-03-22

### Code Review — 28 Issues Fixed

Full code review via custom @code-reviewer agent. 6 critical, 16 warnings, 10 suggestions — 28 fixed, 4 deferred.

**Critical fixes:**
- Plugin header/constant version mismatch synced
- Import temp file path traversal closed — hidden fields store basename only, readback validates with realpath + directory containment
- sp_unified_search rate-limiting (30 req/min per IP) for unauthenticated users
- Annual Report JS echoes wrapped with esc_js()
- OOP prototype files removed (societypress-core.php, admin/, includes/)
- Newsletter wp_die() messages i18n-wrapped, server path leak replaced with user-friendly text

**Warning fixes:**
- 58 AJAX error/success strings i18n-wrapped
- Theme output escaping, date('Y') → wp_date('Y'), SQL hardening
- GPL license alignment across themes, theme version sync
- Installer bridge script token auth + relative redirect
- getsocietypress.org download page updated to v0.40d, features page stats corrected (12/43/19), nav walker CSS classes fixed

**Suggestion fixes:**
- @unlink → wp_delete_file (11 instances), absint() on integer echoes, capability checks, duplicate comment removed

### Calendar Subscription Feed

- Public feed (?sp_ical_feed=public) and token-authenticated members-only feed (?sp_ical_feed=TOKEN)
- Shared iCal helpers extracted from existing .ics download — both features use identical RFC 5545-compliant output
- Subscribe dropdown UI on events listing, calendar page, and event detail — webcal:// one-click subscribe + copy URL for Google Calendar
- Per-user tokens stored in user_meta with AJAX regeneration and Regenerate URL button
- 1-hour transient cache invalidated on event create/update/delete/cancel
- Settings toggle: events_ical_feed_enabled (default: on)

### External Events — Manual + iCal Feed Import

**Manual external events:**
- external_url field on event edit form — when set, event links to external site instead of internal detail page
- Visual indicators: dashed border + arrow (↗) on calendar pills, External badge on listing, minimal card with Visit Event Page button on detail
- external_source column tracks origin: null (normal), 'manual' (hand-entered URL), 'ical_feed' (imported)

**iCal feed import:**
- New sp_ical_feeds table (11 columns) for managing subscribed feeds
- External Calendars admin page under Events — add/edit/delete feeds with label, URL, category, sync interval
- Pure PHP iCal parser: RFC 5545 line unfolding, VEVENT block extraction, TZID/UTC/DATE timezone handling, text unescaping
- sp_sync_ical_feed() — fetches feed, parses events, INSERT new / UPDATE changed / DELETE removed (matched by external_uid + feed_id)
- Hourly cron (sp_ical_feed_sync_cron) checks each feed's interval and last_synced_at
- Sync Now, Pause/Resume, Delete actions via AJAX with admin UI feedback
- Imported events are read-only with Detach from Feed option to convert to editable manual events
- Page builder Upcoming Events widget handles external URLs with target=_blank

## v0.40d — 2026-03-21

### Leadership & Committees — Member Search

**Server-Side Member Search in Forms:**
- Officer and committee assignment forms now have their own "Find Member" search — type a name, click Search, page reloads with only matching members in the dropdown
- Dropdowns start empty ("Search for a member first") — no more 2,500-name dropdown to scroll through
- Separate GET parameters per form (`officer_search`, `committee_search`) so each works independently
- SQL LIKE search matches first_name, last_name, or "Last, First" concatenation
- Forms auto-open when their search is active; Clear button resets everything
- Editing existing roles pre-loads the assigned member without a search
- Added `assets/js/leadership-search.js` as a properly enqueued admin script for future client-side enhancement

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
