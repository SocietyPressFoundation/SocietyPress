# SocietyPress — TO-DO

Reference spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
Architecture divergences from spec: function-based single-file (not OOP singleton), no Gutenberg blocks (page builder widgets instead), no license keys or update server (pure GPL, no restrictions)

---

## Completed

### Core Platform
- [x] Single-file plugin architecture (~68,000 lines, function-based, inline JS/CSS)
- [x] 47 database tables via dbDelta on activation (43 original + sp_ballots + sp_ballot_questions + sp_ballot_choices + sp_ballot_votes)
- [x] Constants: `SOCIETYPRESS_VERSION`, `SOCIETYPRESS_PLUGIN_DIR`, `SOCIETYPRESS_PLUGIN_URL`, `SOCIETYPRESS_PLUGIN_FILE`
- [x] Settings: single `societypress_settings` option array (68 keys), 8-tab admin page (Website, Organization, Membership, Directory, Events, Privacy, Design, Modules)
- [x] Module toggle system: 13 feature modules (Events, Library, Newsletters, Resources, Governance, Store, Records, Donations, Blast Email, Gallery, Research Help, Documents, Voting) — wizard step + settings page, gates admin menus, page templates, shortcodes, and crons
- [x] Admin: unified sidebar with flyout groups (Communications, Finances), WP branding hidden, custom login page
- [x] Admin dashboard: stat cards (total/active/expiring/expired/new members), upcoming events, expiring members, recent signups, quick links, site info
- [x] Site lockdown: logged-in for frontend, admin-only for backend
- [x] XChaCha20-Poly1305 encryption via libsodium for sensitive fields
- [x] Email system: `pre_wp_mail` interceptor logs ALL emails to `sp_email_log`, dev mode blocking, configurable From/Reply-To headers
- [x] Email log admin: stat cards (sent/blocked/failed/total), status/type filters, search, single-entry detail with sandboxed iframe body preview
- [x] GDPR compliance: 5 privacy data exporters + 5 erasers, privacy policy content registration
- [x] Unified site search: searches events, library, resources, members (logged-in), newsletters (logged-in), WP pages — frontend template + AJAX JSON endpoint
- [x] Audit logging: member CRUD, status changes, position/committee assignments, settings saves, event CRUD, event registration/cancellation, bulk member delete, group assignment, blast email send, volunteer role CRUD
- [x] GitHub repo: cleaned up, current single-file plugin + theme, GPL-2.0
- [x] GitHub update checker: `pre_set_site_transient_update_plugins` + `plugins_api` filters, 12-hour cached API check, `upgrader_source_selection` rename for zipball installs, one-click AJAX update from dashboard, audit logging
- [x] Parent theme update checker: `pre_set_site_transient_update_themes` filter, `sp_latest_parent_theme_version()`, one-click AJAX update from dashboard, `upgrader_source_selection` for theme directory extraction
- [x] Child theme gallery: `sp_get_theme_registry()` built-in catalog, enhanced Themes admin page with install/update for available themes, AJAX download from GitHub zipball with selective directory extraction, version comparison for update detection
- [x] Dashboard update banners: plugin update (blue), theme updates (amber) for parent + child themes, one-click update buttons, auto-hidden when current
- [x] Privacy policy template: `sp-privacy-policy` page template generates complete dynamic privacy policy based on site configuration (GA, Stripe, enabled modules), auto-assigned to Privacy Policy page on activation
- [x] Privacy Policy Builder: WordPress built-in `privacy-policy-guide.php` accessible via Settings flyout
- [x] Google Analytics: GA4 Measurement ID field on Website settings, gtag.js in wp_head, admin traffic exclusion

### Members
- [x] CRUD: individual + organizational support, custom fields for genealogical research
- [x] Membership tiers: 14 tiers, configurable pricing/duration
- [x] Statuses: Active, Expired, Pending, Cancelled, Deceased
- [x] CSV import: 86+ ENS column mappings, all 21 ENS fields wired
- [x] CSV export: AJAX with live count, filter pass-through
- [x] Bulk actions: status change, group assign, delete
- [x] Directory: frontend, members-only, privacy layers, member detail modal (AJAX)
- [x] Directory: hide nav link for logged-out visitors, "Surname Being Researched" filter label
- [x] Member groups: bulk "Assign to Group" action, group filter on Members list
- [x] Member re-import: 385 members imported with all new fields populated
- [x] Member portal (My Account): profile photo, personal info, contact, address, seasonal address, communication preferences (incl. blast opt-out), directory privacy, interests & skills, research surnames (county/state/country/year range), my events, change password
- [x] Email obfuscation: `sp_obfuscate_email()` + JS assembler — all frontend emails protected from scrapers (base64 split in data attrs, assembled by JS on page load)
- [x] i18n: My Account page fully wrapped in `__()` / `esc_html__()` / `esc_attr__()` with `societypress` text domain

### Events
- [x] 6 tables: events, categories, category_assignments, registrations, speakers, time_slots
- [x] Categories with add/edit/delete/list
- [x] Add/edit with featured image picker (wp.media deferred to window.load)
- [x] Recurring events: weekly, monthly nth-day
- [x] Registration system: capacity limits, confirmation, waitlist
- [x] Walk-in tracking and attendance management
- [x] iCal export
- [x] Dual pricing display: member + non-member on listing and detail pages
- [x] Calendar: Page Builder widget + standalone page template, shared renderer `sp_render_calendar_grid()`, category filter, month navigation
- [x] Event import from CSV
- [x] Speakers: add/edit speakers per event, bio, photo
- [x] Time slots: CRUD for multiple slots per event

### Library
- [x] Full catalog: 19,418 items imported from the society CSV
- [x] Data cleanup: media types and shelf locations normalized
- [x] Admin: catalog list table (sortable, filterable), item edit page, categories page, CSV import, CSV export, stats dashboard
- [x] Frontend: enhanced OPAC-style catalog widget — collection stats header, tabbed search (keyword/title/author/subject/call number), browse-by-type cards with SVG icons, popular subjects tag cloud, expandable detail rows (AJAX), faceted filters, smart pagination
- [x] Open Library API enrichment: batch LCCN/title+author lookup, cover images, admin enrichment page with progress bar
- [x] 6 media types: Book (16,248), Vertical File (1,351), Periodicals (816), Map (711), eBook (229), Rare Books (62)
- [x] 6 acq codes: Gift, Donation, Purchase, Memorial, Exchange, Society Publication

### Committees & Leadership
- [x] Committee management with delegated permissions
- [x] Chairperson frontend management
- [x] Officer positions and terms tracking
- [x] Co-chair support

### Page Builder
- [x] 21 widget types: text_block, hero_slider, event_list, event_calendar, member_directory, library_catalog, contact_form, newsletter_archive, resource_links, gallery, records_search, donations, volunteer_opportunities, store, custom_html, spacer, divider, heading, image, feature_cards, map_embed
- [x] Admin meta box on page editor
- [x] Frontend rendering engine
- [x] Hero slider: per-line text styling (size/weight/color per line, legacy overlay_text auto-migration)

### Design System
- [x] CSS custom properties throughout
- [x] 7 color pickers, font/size/width controls
- [x] Live preview in admin
- [x] Theme uses design system values with sensible fallback defaults

### Email & Notifications
- [x] Welcome email: sent on new member creation, merge tags, configurable subject, enabled via settings
- [x] Renewal reminders: automated at 30/15/7 days before expiration (daily cron), dedup via `sp_renewal_reminders` table
- [x] Registration confirmation: sent on event registration (confirmed + waitlisted), includes event details
- [x] Event reminders: daily cron sends reminders before upcoming events
- [x] Blast email: compose/send mass emails by group/tier/all, batch sending via WP cron, merge tags, opt-out, delivery tracking

### Resource Links
- [x] CSV import (EasyNetSites format), auto-category creation
- [x] 157 links imported
- [x] Frontend directory with search + category dropdown
- [x] Unified search integration

### Donations
- [x] Campaign-based donation tracking, CRUD
- [x] Acknowledgment emails
- [x] Anonymous + in-kind support
- [x] Progress bars (raised vs goal)
- [x] Reports integration

### Newsletter Archive
- [x] PDF upload through admin
- [x] Automatic cover thumbnail generation (Imagick)
- [x] Browse archive, inline preview modal with zoom/page nav
- [x] Download restricted to members only
- [x] Admin card grid, frontend grid, search

### Genealogical Records
- [x] EAV-based records system: 4 tables (collections, collection_fields, records, record_values)
- [x] 13 record type templates with default fields (Cemetery, Census, Church, Court, Immigration, Land, Marriage, Military, Newspaper, Obituary, Probate, Tax, Vital)
- [x] Admin: collection manager with drag-reorder field configurator, record browser/editor, CSV import with field mapping
- [x] Frontend: public search page, faceted filters (collection, search text), access-controlled fields per collection
- [x] Concatenated `search_text` column for fast full-text search without EAV joins
- [x] Page builder widget: `records_search`

### Volunteer System
- [x] Volunteer opportunities: title, description, location, type (one-time/recurring/ongoing), date, capacity, skills needed, status, committee association
- [x] Frontend signup/cancel via AJAX, shortcode `[societypress_volunteers]`
- [x] Waitlist with auto-promotion (`sp_volunteer_promote_waitlist()`)
- [x] Signup lifecycle: Confirmed → Waitlist → Completed → Cancelled → No-Show
- [x] Admin roster: volunteer role assignments with inline add/edit
- [x] Hours tracking: logged per signup, summary stats, CSV export

### Store
- [x] Public storefront (/store/): category sidebar with counts, product card grid, quantity selector, functional Add to Cart buttons (AJAX, logged-in users)
- [x] Products sourced from library catalog (configurable `store_acq_code` setting, `item_value > 0`)
- [x] 8 auto-categorized store categories
- [x] Shopping cart: user_meta storage, AJAX CRUD, responsive cart page with quantity controls, header badge with live count
- [x] Checkout: Stripe Checkout Sessions with multi-line-item support, order confirmation email
- [x] Admin order management: orders list with status filters, order detail with fulfillment controls, Finances flyout integration

### the society Child Theme (v0.04d)
- [x] Front page template, 3-level dropdown nav, hamburger menu
- [x] Hero slider with per-line text styling
- [x] Footer: 2-column + contact bar + logo strip, white background
- [x] Header/nav: logo 140px, nav 13px/400 weight Poppins, body padding-top 220px
- [x] the society palette: burgundy #632220, cream #fbebd2, taupe #7f7166, terracotta #ba5f36
- [x] Header layout fixes: search field positioned inline (not absolute), cart icon hidden, user trigger pill removed, caret spacing, header aligned to --sp-content-width guides
- [x] Social icons: brand-colored (Facebook blue, YouTube red), absolutely positioned top-right
- [x] Dual search forms: mobile (inside hamburger panel) + desktop (inline in nav bar)

### Finances Cleanup
- [x] Imported donation records no longer show recorder's name (`recorded_by = NULL` for imports)

### Join Form
- [x] Public signup shortcode `[societypress_join]`: tier selection, personal info, Stripe checkout
- [x] Stripe integration: direct REST API calls (no SDK), PaymentIntent flow, success/cancel handling

---

### Documents Module
- [x] Documents module: 2 tables (sp_documents, sp_document_categories), admin CRUD (list/add/edit/delete), category management, WP media library file picker, per-document access control (public/members_only), frontend page template (sp-documents) with category grouping and lock icons for restricted docs, seeded default categories (Meeting Minutes, Society Documents)
- [x] Bulk upload: multi-select media picker, shared category/access/status settings, auto-generated titles (cleaned filenames), auto-detected dates from filename patterns, per-file review/edit before submit
- [x] Members-only page checkbox: per-page `_sp_members_only` meta, branded login prompt on frontend for non-logged-in visitors, auto-hides restricted pages from nav menu

### Roles & Permissions
- [x] 10 access areas: Members, Events, Library, Finances, Communications, Records, Governance, Content, Settings, Reports
- [x] 8 pre-built role templates: Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager, Content Editor
- [x] `sp_user_can()` helper + `user_has_cap` filter — WP admins auto-get all SP capabilities
- [x] Centralized capability map remaps all 100+ menu items without touching individual `add_submenu_page` calls
- [x] User Access admin page: assign roles, customize per-user access areas, revoke access
- [x] Non-admin SP staff: redirected from WP dashboard to SP dashboard, see only permitted sections
- [x] Committee-scoped access: automatic chair/member permissions from governance data (v1.0.2 — committee_access_area column, sp_get_user_committee_access_areas(), committee_auto_permissions setting)

## Recently Completed

### Completed This Session (v1.0.6 — 2026-04-04)

**Genealogy Profiles Feature (new):**
- [x] 2 new DB tables: sp_genealogy_sites (admin-configurable site list), sp_member_genealogy_profiles (per-member links with privacy)
- [x] 26 default sites seeded across 7 categories (major platforms, cemetery/memorial, records, DNA testing, DNA analysis, software, general)
- [x] One-time migration from old 8-service user_meta to new table structure
- [x] 4 helper functions: sp_get_active_genealogy_sites, sp_get_member_genealogy_profiles, sp_get_genealogy_site_icon_url, sp_get_genealogy_site_categories
- [x] Admin page (Members → Genealogy Sites): full CRUD, category grouping, activate/deactivate, delete protection for defaults, member count badges
- [x] 3 AJAX handlers: save, toggle active, delete (nonce + capability checks)
- [x] My Account rewritten: dynamic DB-driven form with category grouping, site favicons, per-profile visibility toggle
- [x] Member detail modal: DB-driven with is_public filter, favicon icons in pill links
- [x] Admin member edit: new Genealogy Profiles section with category grouping between Surnames and Preferences
- [x] Cascade delete, GDPR export (all profiles with visibility status), GDPR erase all updated

**Theme Polish:**
- [x] Playfair Display added as default heading font (plus Cormorant Garamond, Crimson Text, Poppins)
- [x] front-page.php: full-viewport cinematic hero with video/image/gradient, headline, subtitle, CTA, scroll indicator
- [x] Footer rebuilt: 3-column layout (org info, quick links menu, social/tagline), copyright bar, footer menu registered
- [x] Nav refined: uppercase, letter-spacing, CTA-style Join button treatment
- [x] Typography polished: Playfair headings, font smoothing, tighter line-heights, refined blockquotes, hover transitions
- [x] Homepage Hero settings section added to Design tab: type, media upload, poster, headline, subtitle, CTA, overlay slider, height
- [x] CSS custom properties expanded: --sp-nav-font-size, --sp-nav-font-weight, --sp-header-padding wired to Design settings
- [x] Font system unified: sp_get_font_display_labels() as single source for all dropdowns, 16-font list, all maps synced (PHP, Google Fonts, JS preview, builder)

### Completed Previous Session (v0.50d — 2026-04-02)

**Security Audit — 17 Issues Fixed:**
- [x] CRIT: Settings JSON export now excludes all 6 payment credentials (was missing stripe_live_secret_key + all PayPal keys)
- [x] CRIT: Encryption fallback refuses to store without sodium (was silently storing base64 plaintext)
- [x] HIGH: Newsletter volume/issue numbers XSS — now escaped with esc_html()
- [x] HIGH: Event payment return handler — added ownership check (user_id match)
- [x] HIGH: Admin member edit page title — now uses wp_kses()
- [x] HIGH: Vote nonce reordered — generic nonce verified before touching user-supplied ballot_id
- [x] MED: Health endpoint hides PHP/WP/plugin versions from unauthenticated callers
- [x] MED: Quick edit page handler validates post_type === 'page'
- [x] MED: Batch delete verifies each ID exists in sp_members before deleting
- [x] MED: Vote submission has explicit user_id > 0 guard
- [x] MED: Newsletter PDFs served through AJAX endpoint only — direct attachment URLs no longer in HTML
- [x] LOW: sp_dismiss_login_ack now verifies nonce

**Code Review — 27 Issues Fixed:**
- [x] SQL injection: Reports dashboard queries now use $wpdb->prepare()
- [x] date() → wp_date(): 30+ additional calls converted (crons, calendar, recurrence, volunteers, donations)
- [x] wp_redirect() → wp_safe_redirect(): all ~97 internal redirects converted
- [x] admin_url() in JS: 6 locations wrapped in esc_js()
- [x] admin_url() in HTML href: 4 locations wrapped in esc_url(), back links i18n-wrapped
- [x] Dashboard activity feed: malformed icon_char HTML fixed (missing class=), output via wp_kses()
- [x] Account save AJAX: global nonce check added before any DB query
- [x] confirm() fallbacks: 2 remaining in media manager removed, spConfirm() now unconditional
- [x] Hardcoded "Society Publication": replaced with dynamic DISTINCT query from database
- [x] i18n: ~40+ strings wrapped (setup wizard, speakers, library stats, blast email, donations, page editor)
- [x] Blast email detail: inline style on <th> replaced with utility class
- [x] Explanatory comments on public endpoints (unified search, library detail), backup export, version constant

**UX Review — 25 Issues Fixed:**
- [x] the society: skip navigation link added, conditional h1/p for site title, hamburger aria-label i18n-wrapped
- [x] spConfirm: focus trap added, Enter key removed (prevents accidental confirm), visible X close button
- [x] Flyout menu: role="menu" removed (was incomplete ARIA contract), focus() on first link when panel opens
- [x] Join form: field-level validation with inline errors, aria-invalid, aria-describedby, auto-scroll to first error
- [x] My Account notices: role="alert" on success/error, role="status" on pending, inline styles → CSS class
- [x] Members-only gate: 5 inline styles → CSS classes, id="main-content" for skip link, design system color fallback corrected
- [x] Footer link contrast: rgba opacity 0.7 → 0.85 (meets WCAG 4.5:1)
- [x] Search inputs: aria-label added in parent theme + the society (desktop + mobile)
- [x] Photo upload: capture="user" removed (was forcing camera, preventing file picker)
- [x] Rate limit message: now shows wait time (1 hour) and org contact email
- [x] "Delete All Others": moved from header bar to dedicated danger zone at page bottom
- [x] Calendar sync: spinner animation replaces "..." text during AJAX sync
- [x] Reusable sp-spinner CSS class added to admin utilities

- [x] Inline styles refactor (#8): 305 inline styles extracted to CSS classes (772 → 467), then voting module added ~35 more. 50+ utility CSS classes in `<style id="sp-admin-utilities">`. Remaining ~500 are JS-toggled display:none (risky), dynamic PHP values, or unique one-offs.
- [x] Per-module CSV exports: events, donations, leadership/committees, store orders (with line items), email log, resource links — all follow library export pattern (AJAX handler, UTF-8 BOM, streaming output). Export buttons on all 6 admin pages. Volunteer hours already had one.
- [x] Settings JSON export: "Export Settings (JSON)" button on Website settings page. Downloads `societypress_settings` + `sp_enabled_modules` as dated JSON file. Stripe secret keys excluded for security.
- [x] Site health REST endpoint: `/wp-json/societypress/v1/health` — checks all 50 DB tables exist, 5 cron jobs scheduled, PHP/WP versions, libsodium. Returns 200 (ok) or 503 (degraded/error). No auth required for external uptime monitors. Version details only shown to authenticated admins (v0.50d).
- [x] Custom confirmation modal: `spConfirm()` function + global `data-sp-confirm` interceptors for forms/links/buttons. All 54 `confirm()` calls migrated — zero native confirm() remaining.
- [x] Member photo prompt: "Your profile is missing a photo" nudge on My Account for members without a custom photo.

### Completed This Session (v0.49d — 2026-03-28)

**Voting & Elections Module (new):**
- [x] 4 new DB tables: sp_ballots, sp_ballot_questions, sp_ballot_choices, sp_ballot_votes (UNIQUE KEY prevents double-voting)
- [x] Module registration in sp_get_modules(), template map, capability map, flyout menu
- [x] Admin: Ballots list page (WP_List_Table, status filter tabs, vote counts, row actions)
- [x] Admin: Ballot edit page (title, type, eligibility, voting period, question/choice repeaters with vanilla JS)
- [x] Admin: Ballot results page (participation stats, CSS bar charts, winner highlighting, CSV export)
- [x] Frontend: sp-voting page template (open ballot voting, closed ballot results, AJAX vote submission)
- [x] AJAX: sp_submit_vote (eligibility, period, one-vote enforcement, DB transaction), sp_export_ballot_results (CSV)
- [x] 6 audit log events (ballot_created/updated/deleted/opened/closed, vote_cast — ballot secrecy preserved)
- [x] Supports election, referendum, and survey types; single-choice, multi-choice, and yes/no questions

**jQuery Rewrites (3 violations → 0):**
- [x] Color picker initialization: vanilla JS selectors, jQuery kept only for wpColorPicker() init (unavoidable WP dependency)
- [x] Page builder admin JS: full rewrite (~140 lines) — event delegation, cloneNode, display toggles, reindexing
- [x] Album edit page JS: full rewrite — media library callbacks, drag-drop, photo management

**Code Quality & Debt:**
- [x] Known Issues 39–43 verified FIXED, KNOWN-ISSUES.md updated (43/43 tracked, 41 fixed, 2 deferred)
- [x] Remaining 6 confirm() calls migrated to spConfirm() — zero native confirm() in plugin
- [x] 305 inline styles extracted to 50+ CSS utility classes (sp-field-label, sp-text-danger, sp-full-width, sp-card, etc.)
- [x] Mobile hamburger menu code-reviewed (proper aria-expanded, escape key, click-outside, z-index)

### Completed Previous Session (v0.47d — 2026-03-27/28)

**Page Builder — New Widgets & Builder-Driven Home Page:**
- [x] New `feature_cards` widget: repeater-based grid of image cards with title, description, button — replaces hardcoded feature sections
- [x] New `map_embed` widget: Google Maps embed from address field, configurable height
- [x] `upcoming_events` widget: added "Photo Cards (grid)" layout option alongside original compact list
- [x] Universal `section_heading` field on all widgets: optional centered heading with decorative divider, sanitized in save handler
- [x] the society front-page.php rewritten: renders page builder widgets instead of hardcoded sections — Harold controls the entire home page from the builder
- [x] Home page populated with 4 builder widgets: hero slider, upcoming events (cards), feature cards (2×2), map

**Page Builder — Bug Fixes:**
- [x] Template input pollution: hidden widget templates submitted ghost widgets on every save (hero_slider kept reappearing) — fixed by disabling template inputs, re-enabling on clone
- [x] the society front-page.php had wrong meta key (`_sp_builder_widgets` → `_sp_page_widgets`) and passed whole widget object instead of settings to renderer
- [x] Builder frontend CSS not loading on the society front page — front-page.php now hooks `sp_builder_frontend_styles` and `sp_builder_frontend_scripts` with `has_action()` guard

**Design Settings:**
- [x] Color pickers not initializing — deferred wpColorPicker init to `window.load` (script loads in footer, was running before available)
- [x] Footer Link Color field moved from Header & Navigation section to Colors section
- [x] Footer links in the society child theme + parent theme now use `var(--sp-color-footer-link)` CSS variable from Design settings

**Code Review — 17 Issues Fixed:**
- [x] Missing `sp_builder_frontend_scripts` hook in front-page.php (Critical)
- [x] Contact form frontend JS rewritten from jQuery to vanilla fetch() (Critical)
- [x] Unescaped `$height`/`$columns` in HTML attributes — now use `absint()` (Critical)
- [x] Raw `$link_target` echo replaced with individual `esc_attr()` calls
- [x] Contact form labels wrapped in `esc_html__()` (i18n)
- [x] Contact form inline styles moved to `sp_builder_frontend_styles()`
- [x] Login-required messages wrapped with `printf()` + `__()` + `wp_kses()`
- [x] Duplicate style hook guard with `has_action()`
- [x] Feature cards remove-card listener scoped to widget container (not document)
- [x] `get_the_ID()` → `get_queried_object_id()` in front-page.php
- [x] Dead CSS removed from the society style.css
- [x] Widget CSS consolidated into `sp_builder_frontend_styles()`
- [x] Map embed inline styles moved to CSS class
- [x] Section heading wrapper inline style moved to CSS

**Security Audit — 12 Issues Fixed:**
- [x] H1: 11 SQL LIMIT/OFFSET clauses converted to `$wpdb->prepare()` with `%d` placeholders
- [x] H2: 6 SQL IN() clauses converted from `implode()` to `$wpdb->prepare()` with placeholder pattern
- [x] H3: Stripe secret keys now encrypted at rest via `sp_encrypt()`/`sp_decrypt()`
- [x] H4: Profile photo upload extension derived from validated MIME type, not client filename
- [x] M1: `$font_family` CSS output escaped with `esc_attr()`
- [x] M2: `rich_text` widget output re-sanitized with `wp_kses_post()`
- [x] M4: Profile photo directory gets `.htaccess` blocking PHP execution
- [x] M5: Rate limiter checks `X-Forwarded-For` for proxy-aware IP detection
- [x] M6: Contact form rate limited to 5 submissions/hour per IP
- [x] M7: Settings sanitize callback has explicit `current_user_can('manage_options')` check

**Deploy Script:**
- [x] the society deploy target changed from example.org to demo site
- [x] `./deploy.sh all` now includes the society child theme

### Completed Previous Sessions

**v0.46d (2026-03-26): CSS cascade fix, Design settings expansion, header polish**

**v0.42d–0.45d (2026-03-23–25): i18n, accessibility, sample data, reports**

**i18n Cleanup:**
- [x] Generated `.pot` translation template — zero warnings
- [x] Added ~80 translator comments with ordered placeholders across plugin
- [x] Numbered all multi-placeholder strings (`%s` → `%1$s`, `%2$s`) for translator reordering
- [x] Split dual-context "Edit Member: %s" string into `_x()` with distinct contexts

**Accessibility / UX Pass (25 issues from UX review, 22 fixed):**
- [x] Skip navigation link (WCAG 2.4.1) — parent theme + the society + all 6 templates
- [x] Conditional `<h1>`/`<p>` site title — `<h1>` only on front page (WCAG 2.4.6)
- [x] Mobile hamburger menu for parent theme (was wrapping/stacking below 768px)
- [x] User dropdown click/tap toggle for touch devices
- [x] Custom confirmation modal (`spConfirm`) replacing `confirm()` on 4 most destructive actions
- [x] Admin sidebar visual separators between menu groups
- [x] Inline form errors on member edit (field-level `aria-describedby` + focus)
- [x] Setup wizard step labels for screen readers
- [x] Module toggle `aria-labelledby` associations
- [x] Blast email filter tabs `aria-current`
- [x] Join form: tier radio type cast, "Join Now" label, state field hint
- [x] Dashboard + member list status icons (non-color indicators)
- [x] Membership plans inline editing focus management + `aria-live`
- [x] Design settings anchor navigation
- [x] Logo preview alt text, event image button context, table `scope="col"`
- [x] Empty state actionable guidance (library, resources, donations)
- [x] Search input focus ring contrast fix
- [x] Dashboard inline styles extracted to CSS classes (42 → 2)

**Accessibility Cleanup (v1.0.4 — 2026-04-03):**
- [x] 10 a11y fixes applied: library catalog aria-labels, event capacity progressbar, page groups keyboard accessible header, proxy form aria-live, join form tier error aria-describedby, committee table captions, page item drag handle labels, newsletter date color contrast, admin gallery folder card labels
- [x] 1 security fix: push REST endpoint nonce verification for logged-in users (MED-1)
- [x] .pot regenerated with new translatable strings

**Deferred UX item:**
- [x] Mobile hamburger for parent theme: tested on demo site with 7 real nav items — nav was wrapping at 768–1024px. Bumped collapse breakpoint from 768px to 1024px (matches the society child theme). Hamburger, X animation, Escape close, click-outside close, aria-expanded all working.

### Completed Previous Session (v0.41d — 2026-03-22)

**Code Review — 28 Issues Fixed:**
- [x] Plugin header/constant version mismatch (C-1)
- [x] Import temp file path traversal — now stores basename only, validates with realpath (C-2)
- [x] sp_unified_search rate-limiting for unauthenticated users (C-3)
- [x] Annual Report JS escaping with esc_js() (C-4)
- [x] Removed OOP prototype files — societypress-core.php, admin/, includes/ (C-5)
- [x] Newsletter wp_die() messages i18n-wrapped, server path leak removed (C-6)
- [x] 58 AJAX error/success strings i18n-wrapped (W-1)
- [x] Theme output escaping, date('Y') → wp_date('Y'), SQL hardening (W-2/3/6/7/8)
- [x] GPL license alignment across themes (W-14)
- [x] Installer bridge script token auth + relative redirect (W-15/16)
- [x] @unlink → wp_delete_file, absint() on integer echoes, capability checks (S-2/3/8/9)
- [x] getsocietypress.org: download page v0.40d, features page stats corrected (12/43/19), nav walker CSS classes (W-10/11/S-5)

**Calendar Subscription Feed:**
- [x] iCal/webcal feed URLs — public (?sp_ical_feed=public) and token-authenticated members-only
- [x] Shared iCal helpers extracted from existing .ics download (sp_ical_fold_line, sp_ical_escape_text, sp_ical_build_dt_lines, sp_ical_build_location, sp_ical_build_description)
- [x] Subscribe UI on events listing, calendar page, and event detail — webcal:// one-click + copy URL + members-only feed
- [x] Per-user tokens in user_meta with AJAX regeneration
- [x] 1-hour transient cache invalidated on event create/update/delete
- [x] Settings toggle under Events settings (events_ical_feed_enabled)

**External Events — Manual + iCal Import:**
- [x] external_url field on events — links to external site instead of internal detail page
- [x] Visual indicators: dashed border + arrow on calendar pills, "External" badge on listing, minimal card on detail page
- [x] New sp_ical_feeds table for managing subscribed iCal feeds
- [x] External Calendars admin page under Events — add/edit/delete/sync feeds
- [x] Pure PHP iCal parser: line unfolding, VEVENT extraction, TZID/UTC/DATE timezone handling
- [x] sp_sync_ical_feed() — insert/update/delete sync matched by external_uid + feed_id
- [x] Hourly cron with per-feed configurable interval (1-24h)
- [x] Sync Now, Pause/Resume, Delete actions with AJAX
- [x] Imported events read-only with "Detach from Feed" option
- [x] Page builder Upcoming Events widget handles external URLs

### Completed Previous Session (v0.40d — 2026-03-21)

**Leadership & Committees — Member Search:**
- [x] Server-side member search in officer and committee assignment forms — no more 2,500-name dropdown
- [x] Each form has its own "Find Member" search (GET form): type a name, click Search, page reloads with filtered dropdown
- [x] Dropdowns start empty ("Search for a member first") until a search is performed
- [x] Search matches first name, last name, or "Last, First" format via SQL LIKE
- [x] Clear button resets search; forms auto-open when search is active
- [x] Editing existing roles pre-loads the assigned member without requiring a search
- [x] Deduplicated members by user_id in search results
- [x] Added `leadership-search.js` as a properly enqueued admin script (wp_enqueue_script + wp_localize_script) for future JS enhancement

### Completed Previous Session (v0.39d — 2026-03-20)

**Child Themes & Theme Builder:**
- [x] 5 default child themes: Heritage, Coastline, Prairie, Ledger, Parlor — each with distinct palette, Google Font, and personality
- [x] Fixed all child theme functions.php to properly enqueue child stylesheets (CSS wasn't loading before)
- [x] Theme registry updated with all 5 themes + the society
- [x] Custom Theme Builder: "Create Your Own Theme" card in gallery, modal with 7 color pickers + 2 font selectors + live preview, AJAX create/update/delete, generates real WordPress child themes on disk, re-editable
- [x] "Match My Current Site" color extractor: paste a URL, we fetch the page, parse CSS (inline + external stylesheets), extract colors by context (header, footer, links, headings, buttons), detect fonts from Google Fonts links, pre-populate the builder
- [x] Theme preview system: "Preview" button on each theme card opens real homepage rendered with that theme, purple preview banner with "Activate" and "Back to Themes", admin bar hidden during preview

**Governance — Leadership & Committees:**
- [x] New `sp_render_leadership_page()` — proper two-section page (Officers & Board card grid + Committees grouped by name with collapsible sections)
- [x] Added `role_type` column to `sp_volunteer_roles` table (officer/committee/volunteer) with auto-migration
- [x] Volunteer Roster split to separate menu item (`sp-volunteer-roster`)
- [x] Volunteer list table filtered to only show volunteer-type roles

**Bug Fixes:**
- [x] Duplicate "Log Out" in admin user dropdown — removed redundant `sp-logout` node
- [x] `\n` not rendering as line breaks in confirm dialogs — switched from single to double-quoted PHP strings
- [x] `!important` breaking CSS color extraction — stripped before normalization

**Infrastructure:**
- [x] Demo site (demo.getsocietypress.org) cloned from example.org — full database + uploads + themes
- [x] example.org being retired — demo.getsocietypress.org is now the primary dev/test site

---

## Known Bugs & Technical Debt

See `Docs/KNOWN-ISSUES.md` for the full list (43 items tracked, 41 fixed, 2 deferred).

**Critical (all fixed):**
- [x] Version mismatch (v0.30d)
- [x] Attendance NULL bug (v0.30d)
- [x] Join form welcome email before payment (v0.30d)
- [x] DB version never saved after upgrade — dbDelta ran every admin page load (v0.38d)
- [x] Record collection field ID orphaning — editing schema orphaned all record data (v0.38d)
- [x] Incomplete cascade delete — only 4 of 16 tables cleaned up, now uses `sp_cascade_delete_member_data()` (v0.38d)

**Security (all fixed):**
- [x] Document download bypasses access control — added AJAX handler with access checks (v0.38d)
- [x] Guest registration cancel ownership gap — null user_id check added (v0.38d)
- [x] Missing nonce on blast recipient count (v0.38d)
- [x] Hardcoded the society email — now pulls from org_email setting (v0.38d)
- [x] Unescaped get_the_title() XSS (v0.38d)

**Code quality (all fixed v0.38d–v0.50d):**
- [x] date() → wp_date() — initial ~30 calls (v0.38d), then additional 30+ calls in crons, calendar, recurrence, volunteers, donations (v0.50d). Zero PHP date() remaining in display/SQL contexts.
- [x] admin_url() wrapped in esc_url() (~40+ in v0.38d), then all remaining bare admin_url() in JS (esc_js) and HTML href contexts (v0.50d)
- [x] wp_redirect() → wp_safe_redirect() for all ~97 internal redirects (v0.50d). Zero wp_redirect() remaining.
- [x] Stripe refund handles 'pending' status
- [x] ICS multibyte line folding (mb_strlen/mb_substr)
- [x] preferred_name fallback PHP 8.x notice
- [x] wp_enqueue_media() moved before output in speaker edit
- [x] N+1 query on frontend event listing (batch query)
- [x] Library stats transient invalidation on write operations
- [x] Email template uses brand colors from settings
- [x] Dashicons only loaded for logged-in users
- [x] Merge tag docs unified to {{double_braces}}
- [x] Blank template moved from /tmp/ to wp_upload_dir()

**Previously fixed (v0.30d-v0.37d):**
- [x] Merge tag syntax, GDPR donations, library AJAX nopriv, deprecated get_page_by_title, auto_update scope, rate limiting, help notifications, email log cron, breadcrumb settings, store the society references, and 6 more

**Deferred:**
- [x] jQuery → vanilla JS rewrite — all 3 violations resolved (v0.49d). Color picker keeps jQuery for wpColorPicker() only (unavoidable WP dependency). Page builder admin + album edit fully rewritten to vanilla JS.
- [x] Server path exposure in 5 import flows — FIXED: hidden fields now store basename only, readback validates with realpath + directory containment check

**i18n:**
- [x] Comprehensive i18n pass (v0.38d): ~500+ strings wrapped across 4 agent passes, text domain appears 2,564+ times, estimated ~95% coverage
- [x] Additional i18n pass (v0.50d): setup wizard (all 4 steps), speakers page, library catalog stats, blast email detail labels, donations, page editor buttons, join form rate limit message. ~40+ additional strings wrapped.

---

## Membership — Remaining

- [x] Membership reports dashboard: trends, retention rates, tier breakdowns, expiring pipeline, CSV export — fully implemented in `sp_render_reports_page()` and `sp_render_membership_reports_page()`
- [x] Member photo improvements:
  - [x] Add photo upload field to admin member edit form — Harold can photograph members at meetings. Photo section with preview, upload, and remove checkbox. Same upload directory + naming convention as My Account.
  - [x] ~~`capture="user"` attribute~~ — removed. Forced mobile camera instead of allowing file picker. `accept="image/jpeg,image/png,image/gif"` is sufficient.
  - [x] Prompt new members to add a photo on My Account after signup — "Your profile is missing a photo" info notice with "Add a Photo" button linking to photo section
- [x] Surname "sounds like" matching in directory and My Account (v0.50d):
  - [x] "Include similar spellings" checkbox on directory surname search — queries soundex_code/metaphone_code columns
  - [x] "N other members are researching similar surnames" note under each surname in My Account
  - [x] "Also showing members with similar surname spellings" note when similar toggle is active
- [x] Member portal polish:
  - [x] Optional admin approval for profile changes — new `sp_pending_profile_changes` table, `profile_changes_require_approval` setting in Directory settings, queues Personal Info/Contact/Address changes for review when enabled. Admin review page (`sp-pending-changes`) with side-by-side diff, approve/reject with optional notes, email notifications both ways (admin on submit, member on decision). Preferences/privacy/interests/genealogy save immediately. Count badge in Members flyout menu.
  - [x] AJAX save for 6 text/checkbox sections (profile, contact, address, preferences, privacy, interests) — inline success/error messages, no page reload, old POST handlers remain as no-JS fallback. Photo and password forms still use traditional POST.
- [x] Contact information enhancements (spec called for separate `sp_member_contact` table — implemented as enhancements to existing `sp_members` instead, avoiding a massive query refactor across 40+ locations):
  - [x] `preferred_phone` column: home/cell/work radio in My Account + select in admin member edit, saved to sp_members, shown in pending changes review
  - [x] Phone auto-formatting: JS on blur converts 10-digit input to (555) 123-4567 format, handles 1+ prefix, leaves international numbers alone
  - [x] Email validation: real-time blur check on email field, red border + hint for invalid format
  - Existing fields already cover: primary email (wp_users), alt_email, home/cell/work/fax/alt phone, home + seasonal/mailing address
- [ ] Genealogy fields:
  - [x] Surnames: added `note` column for research notes, notes display in My Account + add form
  - [x] Geographic research areas (`sp_member_research_areas`): new table + full My Account section (add/remove/display with area type, year range, notes)
  - [x] Member relationships: read-only "Family Connections" section in My Account (admin manages via member edit page)
  - [x] Normalized surname variants — sp_surname_variants table with canonical↔variant explicit mappings (v1.0.2)
  - [x] 8 genealogy service integrations (WikiTree, FamilySearch, Geni, WeRelate, Ancestry, MyHeritage, Find A Grave, 23andMe) — My Account section with URL fields per service, AJAX save, directory member detail modal shows linked profiles as colored buttons. Stored as user_meta (`sp_genealogy_*`).
- [x] Contact data encryption at rest: XChaCha20-Poly1305 (libsodium) encryption for 7 sensitive fields — cell, work_phone, alt_phone, fax, address_1, address_2, seasonal_address_1. Phone (home) and city/state left plaintext for directory search/sort. Columns widened (VARCHAR 200/512) to fit ciphertext. Helper functions `sp_member_encrypt_fields()` / `sp_member_decrypt_row()` / `sp_member_decrypt_rows()` applied at all 9 write points (admin edit, CSV import, join form, AJAX/POST contact+address, profile change approval) and all 10 read points (admin edit, member list, directory listing+detail, CSV export, GDPR export, welcome email, My Account, generic getter, profile change comparison). One-time migration `sp_maybe_migrate_encrypt_contacts()` runs on activation, batched by 100, idempotent. Graceful fallback: if decryption fails (plaintext data), value passes through unchanged.
- [x] Couples / household accounts: `allows_joint` flag on tiers, `joint_first_name`/`joint_last_name`/`joint_preferred_name` columns on sp_members, admin member edit joint section with toggle, join form shows joint fields when tier allows it, My Account joint member management section, directory shows "John & Jane Smith" for joint memberships, form handler for My Account joint updates

## Events — Complete

- [x] Time slots: individual capacity limits per slot — already implemented (`sp_slot_get_remaining_capacity()`, enforced in registration AJAX)
- [x] Waitlist: auto-promotion when cancellation occurs — already implemented (`sp_promote_waitlist()` called from cancel handler)
- [x] Payment tracking: fee amounts and payment status per registration — already implemented (fee_amount, payment_status, payment_method, payment_date columns + Stripe flow)
- [x] Calendar subscription feed: iCal/webcal URLs for Google Calendar, Apple Calendar, Outlook. Public + token-authenticated members-only feeds. Subscribe UI on events listing, calendar, and detail pages. 1-hour transient cache, invalidated on event changes.
- [x] External events: manual external_url field + automatic iCal feed import. External Calendars admin page, pure PHP iCal parser, hourly cron sync, visual indicators on calendar/listing/detail.
- [x] Calendar bug: current month full-width — verified not reproducible. Both CSS versions use `repeat(7, 1fr)` grid constrained by parent; standalone page has explicit `width: 100%` with comment explaining why. No issue in current code.
- [x] Add to calendar: .ics download on event detail page (key details section) and My Account upcoming events. RFC 5545 compliant, handles timed + all-day events, UTC conversion, line folding.
- [x] Event change notifications: already implemented — save handler detects date/time/location/cancellation changes, `sp_send_event_update_emails()` and `sp_send_event_cancellation_emails()` send HTML emails to all registrants
- [x] "Notice only" events: `notice_only` column, admin checkbox, calendar shows them as non-clickable pills (muted style), excluded from list view, detail page shows title/date + "calendar notice" message

## Email & Notifications — Complete

- [x] Expiration notice: `sp_process_expired_notices()` — already implemented, daily cron, dedup via reminder_key, auto-status-update, merge tags. Added settings UI (enable toggle + subject field) on Membership settings page.
- [x] Waitlist promotion email: `sp_send_waitlist_promotion_email()` — already fully implemented, called from cancel handler when waitlisted registrant gets promoted
- [x] Communication preference check: event reminders, update notifications, and cancellation emails now check `pref_email_events` on the member record. Registration confirmations always send (transactional). Renewal/expiration notices already checked `pref_email_notices`.
- [x] Email template editor in admin with merge tag support — tabbed editor (Welcome, Renewal Reminder, Expiration Notice) with wp_editor, merge tag reference sidebar with click-to-copy, reset-to-default button, shares subject keys with existing Membership settings

## Store — Remaining

- [x] Real marketing descriptions: `store_description` column on library_items, separate from physical description, store frontend prefers it with fallback (v1.0.2)
- [x] Shopping cart / checkout flow: Full cart system — cart stored as user_meta (JSON), AJAX add/update/remove/get endpoints, store page "Add to Cart" buttons wired with JS (logged-in only, logged-out see "please log in"), cart badge in header (SVG cart icon with red count badge next to user menu, updates live via AJAX). Cart page (`sp-cart` template, auto-created on example.org): responsive table with cover images, +/- quantity buttons, remove links, real-time AJAX updates without page reload, "Continue Shopping" link, total display, "Proceed to Checkout" button. Mobile: stacked card layout.
- [x] Order tracking: `sp_orders` + `sp_order_items` tables (41 total DB tables now). Admin "Store Orders" page with status filter tabs (all/paid/pending/shipped/completed/refunded), colored status badges, item count, customer info, date. Order detail page: 2-column layout (order info + customer), items table, status update form with admin note. Added to Finances flyout menu.
- [x] Payment integration: Stripe Checkout for store — reuses existing Stripe REST API pattern (no SDK). Checkout AJAX creates order (status=pending), builds multi-line-item Stripe session, redirects to Stripe. Return handler verifies session, updates order to "paid", clears cart, sends confirmation email (HTML receipt with item table + total). Supports multiple items per checkout. Audit logging for order create + payment.
- [x] Generalize store — replaced hardcoded `acq_code = 'Society Publication'` with configurable `store_acq_code` setting (Settings → Organization → Store). Intro text also configurable. Blank acq_code shows all priced items.

## Payment Processing — Remaining

- [x] Stripe: card payments via Checkout Sessions — join form, event registration, and store all use `wp_remote_post()` to Stripe REST API (no SDK). Test/live mode toggle via settings. Store checkout sends multi-line-item sessions.
- [x] PayPal integration: Join form (PayPal + Stripe buttons for paid tiers), event registration (already done), store checkout (already done), donations form (new sp-donate page template + page builder widget) (v1.0.2)
- [x] Sandbox + live modes: `stripe_test_mode` setting switches between test/live keys
- [x] Payment history: `sp_orders` table tracks store purchases with Stripe session ID + payment intent. Event registrations track via `sp_event_registrations.payment_status`.
- [x] Payment status tracking: orders have full lifecycle (pending → paid → shipped → completed → refunded), event registrations have payment_status + payment_date

## Genealogical Records — Remaining

- [ ] Needs real data imported — no collections populated yet
- [ ] This is the EasyNetSites "Unified Data Module" equivalent — needs to deliver on parity claim

## Installation Wizard — Extend Existing

Existing wizard (4 steps): Org Info → Membership → Feature Selection → Appearance/Email. Triggers on first activation, redirects admin, saves to `societypress_settings`, marks complete via `sp_wizard_completed` option.

- [x] Add Step 3 (between Membership and Appearance): Feature Selection
  - Checklist of 11 modules with dashicons, descriptions, and checkboxes
  - Plain-language descriptions of each so non-technical admins understand what they're choosing
  - All modules on by default — admin unchecks what they don't need
  - Saved as `sp_enabled_modules` option (array of module slugs)
  - Step indicators updated from 3 circles to 4
- [x] Feature toggle system: enable/disable modules after wizard via Settings → Modules
  - Settings page with card grid, toggle switches, Enable All / Disable All buttons
  - Disabled modules: admin menu items hidden (priority 1000 filter), page templates removed from dropdown (priority 999 filter), frontend pages show "Feature Not Available" (priority 5 template_include guard)
  - Tables stay intact when a module is disabled — data is never destroyed
  - Re-enabling surfaces everything again immediately
  - Key principle: toggling off is never destructive, toggling on is seamless
- [x] Wire module checks into the codebase:
  - Module registry: `sp_get_modules()` (11 modules), `sp_module_enabled()`, `sp_get_enabled_modules()`
  - Admin menu filtering: priority 1000 `admin_menu` hook removes submenus for disabled modules
  - Template dropdown: `theme_page_templates` filter (priority 999) hides disabled module templates
  - Frontend guard: `template_include` filter (priority 5) blocks disabled module page rendering
  - Template→module map: `sp_template_module_map()` — 10 template slugs mapped to modules
  - Shortcodes: `[societypress_volunteers]` gated by `governance` module
  - Cron: event reminder cron gated by `events` module (unschedules when disabled)
  - Members is always enabled (hardcoded in `sp_module_enabled()`)

## Theme — Complete

- [x] Style presets: 6 named presets (Parchment, Slate, Ledger, Hearth, Archive, Chronicle) — clickable cards on Design settings page, each fills in all 7 colors + 2 fonts, instant live preview update, highlighted selection state. Harold picks a preset then fine-tunes.
- [x] Starter content on activation: auto-creates 15 pages (Home, About, Membership, Events, Calendar, Directory, My Account, Join, Newsletters, Library, Search, Contact, Resources, Leadership, News + Privacy Policy), removes WP default post/page/comment, sets static front page + blog page, creates Primary Menu with all key pages assigned to `primary` theme location. Smart nav filter handles visibility. Only runs on fresh installs (skips if pages already exist).
- [x] Smart nav behavior: `wp_nav_menu_objects` filter hides Join page for logged-in members, Directory/My Account for logged-out visitors. Template + shortcode detection, cached lookups.
- [x] Search dropdown in primary nav — magnifying glass icon button that expands to a search input on click. Click-away and Escape to close. Slide-in animation, aria-expanded for accessibility.
- [x] My Account menu for logged-in members — already implemented: `sp_user_menu()` shows avatar + preferred name + dropdown (My Account/Log Out for members, Dashboard/Log Out for admins)

## Localization

- [x] Complete i18n string wrapping pass — ~500+ strings wrapped across 52K lines, text domain appears 2,564+ times (v0.38d)
- [x] Fixed all date() → wp_date() for timezone-correct display (~30 instances)
- [x] Fixed all admin_url() → esc_url(admin_url()) (~40+ instances)
- [x] Currency setting: `sp_format_currency()` helper + `currency_symbol`/`currency_position` settings (v0.38d, in progress)
- [x] Generate `.pot` file: `wp i18n make-pot plugin/ plugin/languages/societypress.pot`
  - Master translation template — translators load this in Poedit/GlotPress/Loco Translate
  - Produces `.po` (human-editable) and `.mo` (compiled) files per language
  - Should be regenerated on every release
  - Ship the `.pot` in the repo so translators can grab it from GitHub
- [x] Use WordPress date/time format settings for display dates — `get_option('date_format')` and `get_option('time_format')` used throughout with `wp_date()`

## UX — Remaining

- [x] AJAX progress bars for long-running operations: batched imports with progress percentage, bulk delete with progress overlay — implemented via `sp_process_import_batch()` and AJAX progress handlers
- [x] Alphabetize pages by name in the admin page list (v0.50d — default sort changed from menu_order to post_title)
- [x] Menu organizer / Page Groups: drag-and-drop page organization, auto-nav integration, AJAX CRUD (v1.0.2)
- [x] Media folders: organize Media Library items into folders for easier management — renamed to "Photos & Videos", nested folders (5 levels), YouTube video support, AJAX folder CRUD, breadcrumb navigation
- [x] Child theme logo fallback: header.php checks for img/logo.svg or img/logo.png in child theme when no admin logo is set

## Admin — Remaining

- [x] Site health monitoring: REST API health-check endpoint (`/wp-json/societypress/v1/health`) — checks all 46 DB tables exist, 5 cron jobs scheduled, PHP/WP versions, libsodium. Returns 200 (ok) or 503 (degraded/error). No auth required for external uptime monitors. Pair with UptimeRobot free tier for downtime alerts.
- [x] Admin dashboard: activity feed — recent 15 audit log entries with categorized icons (member/event/settings/email/volunteer), relative timestamps, user attribution. Full-width panel below the existing two-column dashboard layout.
- [x] Audit logging: expanded to cover member CRUD + bulk delete + "Delete All Others", settings saves, event CRUD + bulk delete, event registration + cancellation, group assignment, blast email send, volunteer role CRUD
- [x] Governance menu: already exists as `sp-governance` page with volunteer roles (officer positions, committee assignments). Renamed menu label from "Volunteers" to "Leadership & Committees" for clarity.
- [x] Database backup system (v0.50d):
  - [x] Manual "Back Up Now" button on Backups admin page — one click, generates full SQL dump + settings + uploads ZIP
  - [x] Storage: saved to server in `wp-content/uploads/sp-backups/` with `.htaccess` protection; download via admin UI
  - [x] Automated monthly backup via WP cron — toggle + day-of-month selector (1-28) in settings
  - [x] Retention setting: keep last N backups (default 3), auto-delete older ones
  - [x] Admin notification email when backup completes (or fails)
  - [x] Email backup delivery: attaches ZIP if under 10MB, otherwise sends download link
  - [x] Backups admin page: list table with date/size/type/status, download/delete actions, settings form
  - [x] Audit logging for backup create/delete operations

## Data Portability — Remaining

Principle: Every piece of data a society puts into SocietyPress can come back out in a standard format, with one click, at any time, no questions asked. No export fees, no "contact us," no degraded exports. Your data is yours. Always.

- [x] Per-module CSV exports (extend existing member/library pattern to all modules):
  - [x] Events: all events with category names, dates, locations, prices, descriptions
  - [x] Genealogical records: CSV per collection with dynamic columns from field definitions, "CSV" button on collections page (v1.0.2)
  - [x] Donations: all donations with campaign names, recorder names, acknowledgment status
  - [x] Volunteer data: hours already had export; leadership/committees export added
  - [x] Committees & leadership: all roles with member names, dates, types, statuses
  - [x] Store orders: denormalized export (one row per line item with order data repeated)
  - [x] Email log: metadata only (recipient, subject, status, type, timestamps). Body excluded (too large for CSV).
  - [x] Resource links: all links with category names, featured/active flags
  - [x] Settings: JSON export — "Export Settings (JSON)" button on Website settings page. Downloads `societypress_settings` + `sp_enabled_modules` as dated JSON file. Stripe secret keys excluded for security.
- [x] Newsletters export: ZIP of all PDFs + metadata CSV (v0.50d — sp_export_newsletters_zip, "Export All (ZIP)" button on admin page)
- [x] Documents export: ZIP of all files + metadata CSV (v0.50d — sp_export_documents_zip, organized by category subfolders)
- [x] **Full Site Export** button on Website settings page (v0.50d) — one click, one ZIP containing:
  - [x] 9 module CSVs (members, events, donations, library, orders, email log, resources, leadership, volunteer hours)
  - [x] All newsletter PDFs in `newsletters/` subfolder
  - [x] All document files in `documents/{Category}/` subfolders
  - [x] Settings JSON (payment secrets excluded)
  - [x] README.txt explaining the archive structure
- [x] Full site export now includes member profile photos (member-photos/ folder) and photo album media (photo-albums/ organized by album name) (v1.0.2)
- [ ] Marketing: "Your data is yours" messaging on getsocietypress.org — front and center, not buried in a FAQ

## One-Click Installer — COMPLETE (separate project)

Single-file `install.php` that takes Harold from empty hosting to running SocietyPress in one step. Upload one file, fill out one form, done.

- [x] Server requirements check: PHP 8.0+, MySQL/MariaDB, ZipArchive, curl/allow_url_fopen, libsodium, writable directory
- [x] Download WordPress latest from wordpress.org/latest.zip
- [x] Extract WordPress to web root
- [x] Collect DB credentials + site info via branded web form
- [x] Demo mode: detects config file outside web root, shows grayed-out coaching DB fields with real credentials via hidden inputs
- [x] Generate security keys (WordPress salt API, with local fallback)
- [x] Write wp-config.php
- [x] Run WordPress install via bridge script (browser redirect to sp-bridge-install.php — avoids wp-settings.php bootstrap issues on restrictive hosts)
- [x] Install SocietyPress plugin + parent theme + 4 child themes from bundle ZIP (local filesystem copy, HTTP download, GitHub fallback — 3-tier)
- [x] Activate plugin and theme via mu-plugin (auto-fires on first admin load, self-destructs)
- [x] Set permalink structure to /%postname%/
- [x] Error handling: clear branded messages for every failure point
- [x] Branding: SocietyPress logo, Inter font, gold/navy color scheme — professional first impression
- [x] Repo: `installer/install.php` in SocietyPress repo, ships with `societypress-bundle.zip` alongside
- [x] Self-delete after successful install — bridge script now deletes both itself and install.php after WordPress installs successfully
- [x] Reset script for demo site: `reset-demo.sh` — truncates all SP tables, re-seeds defaults, optional `--full` flag for plugin reactivation

## Demo Installation — LIVE

- [x] Demo site running at https://demo.getsocietypress.org
- [x] SocietyPress 0.38d active, parent theme active, 4 child themes installed, 57 tables
- [x] Installed via the one-click installer (proving it works end-to-end)
- [x] Remove the society data/theme from demo (v0.50d — switched to parent theme, removed society dir, renamed to "Heritage Valley Historical Society", cleared the society logo)
- [ ] Sample data: fake society with members, events, records, newsletters
- [x] Reset mechanism: `reset-demo.sh` script truncates all SP tables via wp eval-file, re-seeds defaults. `--full` flag deactivates/reactivates plugin for full activation hook run.

## ENS Migration — Not Started

- [ ] EasyNetSites migration guide: document how to export ENS data and import into SP
  - Member data: CSV export → SP member import with ENS field mapping (import tool built)
  - Genealogical records: if ENS provides any export, build an import path
  - Content pages: manual migration guidance (copy/paste is realistic for most societies)
- [ ] Migration assistance as a selling point — reduce the switching-cost objection

## getsocietypress.org — Remaining

- [ ] Getting Started guide — illustrated walkthrough for non-technical admins:
  1. Choose hosting (visual guide for top 3 hosts: Bluehost, SiteGround, DreamHost)
  2. Create a MySQL database (screenshots from cPanel)
  3. Upload install.php + societypress-bundle.zip
  4. Run the installer (what each field means)
  5. Setup wizard (org info, membership, modules, design)
  6. Add yourself as the first member (why this matters)
  7. Import your membership list — or load sample data to explore first
  8. "After adding yourself as the first member, go to Import Members to import your membership list. If you'd prefer to explore with sample data first, we've prepared 2,500 realistic fake members you can load and clear anytime."
- [ ] Documentation pages (feature-by-feature guides)
- [ ] Feedback form (structured: bug report / feature request / general question) — future companion plugin

## Voting & Elections — Remaining

- [x] Ballot / election system for officer elections (built v0.49d):
  - [x] 4 DB tables: sp_ballots, sp_ballot_questions, sp_ballot_choices, sp_ballot_votes (UNIQUE KEY prevents double-voting)
  - [x] Create ballot with positions and candidates
  - [x] Configurable voting window (open/close dates)
  - [x] One vote per member, verified by user_id
  - [x] Secret ballot (votes stored without voter identity link)
  - [x] Results page with vote counts, automatic winner determination, CSV export
  - [x] Admin can create, open, close, and publish results
  - [x] Supports election, referendum, and survey types; single-choice, multi-choice, and yes/no questions
  - [x] 6 audit log events (ballot CRUD, open/close, vote cast — secrecy preserved)
- [x] Email notification when a ballot opens (v0.50d — sp_send_ballot_open_emails() sends to eligible members respecting communication prefs)
- [x] Absentee / proxy voting support: allow_absentee, allow_proxy, proxy_limit columns, proxy vote AJAX endpoint (v1.0.2)

## Mobile — Not Started

- [x] Progressive Web App (PWA) layer (v1.0.2)
  - [x] Web app manifest REST endpoint (name, icons, theme color, display: standalone)
  - [x] Service worker with 3 cache strategies
  - [x] Offline message setting (pwa_offline_message)
  - [x] Off by default (pwa_enabled toggle on Website settings)
  - [x] Push notifications: VAPID key auto-generation, sp_push_subscriptions table, REST subscribe endpoint, service worker push handler, sp_send_push_notification() helper, push_enabled toggle (v1.0.2)
  - [x] Icon generation for various device sizes — 192/512 standard, 192/512 maskable (80% safe zone), 180 apple-touch-icon, 32/16 favicons. Maskable icons in manifest, favicons output always (not gated by pwa_enabled).
  - [x] "Add to Home Screen" install prompt UX (already implemented with install banner)

## AI — Built (v1.0.2)

- [x] AI-powered Q&A: Claude API RAG over society data (v1.0.2)
  - [x] Settings page: API key (encrypted), model selector (Haiku/Sonnet), access level, data source checkboxes, custom system prompt, monthly query limit
  - [x] 5 data sources: events, library catalog, resource links, genealogical records, WP pages
  - [x] Privacy: member PII never sent to API — only public/semi-public metadata
  - [x] Keyword extraction + SQL search + Claude API call pipeline
  - [x] Rate limiting (10 queries/hr per user/IP) + monthly limit enforcement
  - [x] Query logging (sp_ai_queries table) with admin audit view
  - [x] Frontend: chat-style widget + sp-ai-assistant page template
  - [x] Page builder widget: ai_assistant with login_required option

## Integrations — Remaining

- [x] Google Analytics: GA4 Measurement ID setting on Website page, gtag.js output in wp_head, admin traffic exclusion option
- [x] bbPress forum integration (v0.50d — ~250 lines of glue code):
  - [x] Module: `forums` in sp_get_modules(), dashicons-format-chat, gated by sp_module_enabled + class_exists('bbPress')
  - [x] Admin notice nudges Harold to install bbPress if module is enabled but plugin missing
  - [x] Access control: active members can post, expired get read-only, non-members blocked on _sp_members_only forums
  - [x] Role sync: active → bbp_participant, expired/cancelled/pending → bbp_spectator (on save + daily cron reconciliation)
  - [x] Nav integration: auto-injects "Forums" link for logged-in members, highlights on bbPress pages
  - [x] Admin flyout: bbPress admin page added to Communications group
- [x] Mailchimp: full settings page (API key encrypted, audience ID, auto-sync toggle, connection test, manual Sync Now AJAX endpoint) (v1.0.2)
- [x] Zoom: settings page with API key/secret (encrypted), connection test, JWT auth helper. Events already have is_virtual + virtual_url fields. (v1.0.2)
Note: PayPal and Stripe are under Payment Processing above.

## Not Doing (spec divergences)

- ~~OOP singleton architecture~~ → function-based single-file
- ~~Gutenberg blocks (15)~~ → page builder widgets serve this role
- ~~License keys / auto-update server~~ → pure GPL, no restrictions
- ~~Swiper.js~~ → custom slider
- ~~Separate volunteer admin panel (/sp-admin/)~~ → existing admin panel is sufficient
- ~~"Contributions welcomed" language~~ → open source ≠ open to contributions
- ~~Built-in forums~~ → Most societies already use Facebook groups; building a quality forum engine is thousands of lines for minimal adoption. bbPress exists for societies that want WP-native forums. The existing `community_link` page builder widget lets Harold point members to wherever discussion happens.
