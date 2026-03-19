# SocietyPress — TO-DO

Reference spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
Architecture divergences from spec: function-based single-file (not OOP singleton), no Gutenberg blocks (page builder widgets instead), no license keys or update server (pure GPL, no restrictions)

---

## Completed

### Core Platform
- [x] Single-file plugin architecture (~48,000 lines, function-based, inline JS/CSS)
- [x] 43 database tables via dbDelta on activation (39 original + sp_pending_profile_changes + sp_orders + sp_order_items + sp_documents + sp_document_categories)
- [x] Constants: `SOCIETYPRESS_VERSION`, `SOCIETYPRESS_PLUGIN_DIR`, `SOCIETYPRESS_PLUGIN_URL`, `SOCIETYPRESS_PLUGIN_FILE`
- [x] Settings: single `societypress_settings` option array (68 keys), 8-tab admin page (Website, Organization, Membership, Directory, Events, Privacy, Design, Modules)
- [x] Module toggle system: 12 feature modules (Events, Library, Newsletters, Resources, Governance, Store, Records, Donations, Blast Email, Gallery, Research Help, Documents) — wizard step + settings page, gates admin menus, page templates, shortcodes, and crons
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
- [x] Full catalog: 19,418 items imported from SAGHS CSV
- [x] Data cleanup: media types and shelf locations normalized
- [x] Admin: catalog list table (sortable, filterable), item edit page, categories page, CSV import, CSV export, stats dashboard
- [x] Frontend: enhanced OPAC-style catalog widget — collection stats header, tabbed search (keyword/title/author/subject/call number), browse-by-type cards with SVG icons, popular subjects tag cloud, expandable detail rows (AJAX), faceted filters, smart pagination
- [x] Open Library API enrichment: batch LCCN/title+author lookup, cover images, admin enrichment page with progress bar
- [x] 6 media types: Book (16,248), Vertical File (1,351), Periodicals (816), Map (711), eBook (229), Rare Books (62)
- [x] 6 acq codes: Gift, Donation, Purchase, Memorial, Exchange, SAGHS Publication

### Committees & Leadership
- [x] Committee management with delegated permissions
- [x] Chairperson frontend management
- [x] Officer positions and terms tracking
- [x] Co-chair support

### Page Builder
- [x] 19 widget types: text_block, hero_slider, event_list, event_calendar, member_directory, library_catalog, contact_form, newsletter_archive, resource_links, gallery, records_search, donations, volunteer_opportunities, store, custom_html, spacer, divider, heading, image
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

### SAGHS Child Theme (v0.04d)
- [x] Front page template, 3-level dropdown nav, hamburger menu
- [x] Hero slider with per-line text styling
- [x] Footer: 2-column + contact bar + logo strip, white background
- [x] Header/nav: logo 140px, nav 13px/400 weight Poppins, body padding-top 220px
- [x] SAGHS palette: burgundy #632220, cream #fbebd2, taupe #7f7166, terracotta #ba5f36
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
- [ ] Committee-scoped access: automatic chair/member permissions from governance data (deferred)

## In Progress

(Nothing actively in flight)

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
- [x] Hardcoded SAGHS email — now pulls from org_email setting (v0.38d)
- [x] Unescaped get_the_title() XSS (v0.38d)

**Code quality (all fixed in v0.38d):**
- [x] date() → wp_date() for ~30 display-context calls
- [x] admin_url() wrapped in esc_url() (~40+ instances)
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
- [x] Merge tag syntax, GDPR donations, library AJAX nopriv, deprecated get_page_by_title, auto_update scope, rate limiting, help notifications, email log cron, breadcrumb settings, store SAGHS references, and 6 more

**Deferred:**
- [ ] jQuery → vanilla JS rewrite (contact form widget, album edit, page builder admin) — substantial effort, low user impact
- [ ] Server path exposure in 5 import flows — admin-only, nonce-protected, low risk

**i18n:**
- [x] Comprehensive i18n pass (v0.38d): ~500+ strings wrapped across 4 agent passes, text domain appears 2,564+ times, estimated ~95% coverage

---

## Membership — Remaining

- [ ] Member portal polish:
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
  - [ ] Normalized surname variants — deferred (needs design: auto-suggest? alias table?)
  - [x] 8 genealogy service integrations (WikiTree, FamilySearch, Geni, WeRelate, Ancestry, MyHeritage, Find A Grave, 23andMe) — My Account section with URL fields per service, AJAX save, directory member detail modal shows linked profiles as colored buttons. Stored as user_meta (`sp_genealogy_*`).
- [x] Contact data encryption at rest: XChaCha20-Poly1305 (libsodium) encryption for 7 sensitive fields — cell, work_phone, alt_phone, fax, address_1, address_2, seasonal_address_1. Phone (home) and city/state left plaintext for directory search/sort. Columns widened (VARCHAR 200/512) to fit ciphertext. Helper functions `sp_member_encrypt_fields()` / `sp_member_decrypt_row()` / `sp_member_decrypt_rows()` applied at all 9 write points (admin edit, CSV import, join form, AJAX/POST contact+address, profile change approval) and all 10 read points (admin edit, member list, directory listing+detail, CSV export, GDPR export, welcome email, My Account, generic getter, profile change comparison). One-time migration `sp_maybe_migrate_encrypt_contacts()` runs on activation, batched by 100, idempotent. Graceful fallback: if decryption fails (plaintext data), value passes through unchanged.
- [x] Couples / household accounts: `allows_joint` flag on tiers, `joint_first_name`/`joint_last_name`/`joint_preferred_name` columns on sp_members, admin member edit joint section with toggle, join form shows joint fields when tier allows it, My Account joint member management section, directory shows "John & Jane Smith" for joint memberships, form handler for My Account joint updates

## Events — Remaining

- [x] Time slots: individual capacity limits per slot — already implemented (`sp_slot_get_remaining_capacity()`, enforced in registration AJAX)
- [x] Waitlist: auto-promotion when cancellation occurs — already implemented (`sp_promote_waitlist()` called from cancel handler)
- [x] Payment tracking: fee amounts and payment status per registration — already implemented (fee_amount, payment_status, payment_method, payment_date columns + Stripe flow)
- [ ] Calendar bug: current month renders full-width, other months render narrower — same HTML/CSS from server, likely browser rendering/caching. Needs DevTools inspection.
- [x] Add to calendar: .ics download on event detail page (key details section) and My Account upcoming events. RFC 5545 compliant, handles timed + all-day events, UTC conversion, line folding.
- [x] Event change notifications: already implemented — save handler detects date/time/location/cancellation changes, `sp_send_event_update_emails()` and `sp_send_event_cancellation_emails()` send HTML emails to all registrants
- [x] "Notice only" events: `notice_only` column, admin checkbox, calendar shows them as non-clickable pills (muted style), excluded from list view, detail page shows title/date + "calendar notice" message

## Email & Notifications — Remaining

- [x] Expiration notice: `sp_process_expired_notices()` — already implemented, daily cron, dedup via reminder_key, auto-status-update, merge tags. Added settings UI (enable toggle + subject field) on Membership settings page.
- [x] Waitlist promotion email: `sp_send_waitlist_promotion_email()` — already fully implemented, called from cancel handler when waitlisted registrant gets promoted
- [x] Communication preference check: event reminders, update notifications, and cancellation emails now check `pref_email_events` on the member record. Registration confirmations always send (transactional). Renewal/expiration notices already checked `pref_email_notices`.
- [x] Email template editor in admin with merge tag support — tabbed editor (Welcome, Renewal Reminder, Expiration Notice) with wp_editor, merge tag reference sidebar with click-to-copy, reset-to-default button, shares subject keys with existing Membership settings

## Store — Remaining

- [ ] Real marketing descriptions (currently showing physical specs from library import)
- [x] Shopping cart / checkout flow: Full cart system — cart stored as user_meta (JSON), AJAX add/update/remove/get endpoints, store page "Add to Cart" buttons wired with JS (logged-in only, logged-out see "please log in"), cart badge in header (SVG cart icon with red count badge next to user menu, updates live via AJAX). Cart page (`sp-cart` template, auto-created on kndgs.org): responsive table with cover images, +/- quantity buttons, remove links, real-time AJAX updates without page reload, "Continue Shopping" link, total display, "Proceed to Checkout" button. Mobile: stacked card layout.
- [x] Order tracking: `sp_orders` + `sp_order_items` tables (41 total DB tables now). Admin "Store Orders" page with status filter tabs (all/paid/pending/shipped/completed/refunded), colored status badges, item count, customer info, date. Order detail page: 2-column layout (order info + customer), items table, status update form with admin note. Added to Finances flyout menu.
- [x] Payment integration: Stripe Checkout for store — reuses existing Stripe REST API pattern (no SDK). Checkout AJAX creates order (status=pending), builds multi-line-item Stripe session, redirects to Stripe. Return handler verifies session, updates order to "paid", clears cart, sends confirmation email (HTML receipt with item table + total). Supports multiple items per checkout. Audit logging for order create + payment.
- [x] Generalize store — replaced hardcoded `acq_code = 'SAGHS Publication'` with configurable `store_acq_code` setting (Settings → Organization → Store). Intro text also configurable. Blank acq_code shows all priced items.

## Payment Processing — Remaining

- [x] Stripe: card payments via Checkout Sessions — join form, event registration, and store all use `wp_remote_post()` to Stripe REST API (no SDK). Test/live mode toggle via settings. Store checkout sends multi-line-item sessions.
- [ ] PayPal integration: Balance, Venmo, credit/debit, pay-later (SDK)
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

## Theme — Remaining

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
- [ ] Generate `.pot` file: `wp i18n make-pot plugin/ plugin/languages/societypress.pot`
  - Master translation template — translators load this in Poedit/GlotPress/Loco Translate
  - Produces `.po` (human-editable) and `.mo` (compiled) files per language
  - Should be regenerated on every release
  - Ship the `.pot` in the repo so translators can grab it from GitHub
- [ ] Use WordPress date/time format settings for display dates
  - Replace hardcoded format strings like `'M j, Y'` with `get_option( 'date_format' )`
  - Replace hardcoded time formats with `get_option( 'time_format' )`
  - Internal/DB dates stay as `Y-m-d` — only display dates change
  - WHY: A German society expects "18. März 2026", not "March 18, 2026"

## Admin — Remaining

- [ ] Site health monitoring: REST API health-check endpoint (`/wp-json/societypress/v1/health`) that verifies DB connection, cron health, plugin status. Pair with external uptime monitor (e.g. UptimeRobot free tier) for downtime alerts. Internal alerting for failed crons, missing DB tables, PHP errors.
- [x] Admin dashboard: activity feed — recent 15 audit log entries with categorized icons (member/event/settings/email/volunteer), relative timestamps, user attribution. Full-width panel below the existing two-column dashboard layout.
- [x] Audit logging: expanded to cover member CRUD + bulk delete + "Delete All Others", settings saves, event CRUD + bulk delete, event registration + cancellation, group assignment, blast email send, volunteer role CRUD
- [x] Governance menu: already exists as `sp-governance` page with volunteer roles (officer positions, committee assignments). Renamed menu label from "Volunteers" to "Leadership & Committees" for clarity.

## Data Portability — Not Started

Principle: Every piece of data a society puts into SocietyPress can come back out in a standard format, with one click, at any time, no questions asked. No export fees, no "contact us," no degraded exports. Your data is yours. Always.

- [ ] Per-module CSV exports (extend existing member/library pattern to all modules):
  - [ ] Events: all events + categories + registrations with attendee info + speakers
  - [ ] Genealogical records: CSV per collection, all records with all field values
  - [ ] Donations: all donations, campaigns, acknowledgment status
  - [ ] Volunteer data: opportunities, signups, hours
  - [ ] Committees & leadership: positions, terms, members
  - [ ] Store orders: order history with line items
  - [ ] Email log: full email history
  - [ ] Resource links: all links with categories
  - [ ] Settings: JSON export for backup/migration
- [ ] Newsletters export: ZIP of all PDFs + metadata CSV
- [ ] Documents export: ZIP of all files + metadata CSV
- [ ] **Full Site Export** button in Settings → one click, one ZIP containing:
  - All module CSVs
  - All newsletter PDFs
  - All document files
  - Settings JSON
  - README explaining the file structure
  - WHY: This is the single feature that proves we mean it when we say "no lock-in." If a society can download their entire operation in one click, they know we're not holding them hostage. It also doubles as a complete backup.
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
- [ ] Self-delete after successful install (currently kept for demo resets — production version should auto-delete)
- [ ] Reset script for demo site (one-click wipe and rebuild)

## Demo Installation — LIVE

- [x] Demo site running at https://demo.getsocietypress.org
- [x] SocietyPress 0.38d active, parent theme active, 4 child themes installed, 57 tables
- [x] Installed via the one-click installer (proving it works end-to-end)
- [ ] Sample data: fake society with members, events, records, newsletters
- [ ] Reset mechanism for evaluators to rebuild from scratch

## ENS Migration — Not Started

- [ ] EasyNetSites migration guide: document how to export ENS data and import into SP
  - Member data: CSV export → SP member import with ENS field mapping (import tool built)
  - Genealogical records: if ENS provides any export, build an import path
  - Content pages: manual migration guidance (copy/paste is realistic for most societies)
- [ ] Migration assistance as a selling point — reduce the switching-cost objection

## getsocietypress.org — On Hold

- [ ] Documentation pages (waiting until SP features are more complete)
- [ ] Feedback form (structured: bug report / feature request / general question) — future companion plugin

## Voting & Elections — Not Started

- [ ] Ballot / election system for officer elections
  - Create ballot with positions and candidates
  - Configurable voting window (open/close dates)
  - One vote per member, verified by user_id
  - Secret ballot (votes stored without voter identity link)
  - Results page with vote counts, automatic winner determination
  - Admin can create, open, close, and publish results
  - Email notification when a ballot opens
  - Absentee / proxy voting support (optional, society-configurable)
  - ENS and ClubExpress both have this — table stakes for society software

## Mobile — Not Started

- [ ] Progressive Web App (PWA) layer
  - Web app manifest (name, icons, theme color, display: standalone)
  - Service worker for offline caching (directory, events, library)
  - "Add to Home Screen" prompt for mobile visitors
  - Push notifications for event reminders, blast emails, renewal notices
  - WHY: Native apps (iOS + Android) would be a second full-time project with app store overhead. A PWA gives app-like experience from the browser with zero friction. Can wrap in Capacitor later if store presence is ever needed.

## AI — Not Started

- [ ] AI-powered Q&A: let members (or the public) ask natural-language questions and get answers drawn from society data
  - Could cover library catalog, events, newsletters, resource links, genealogical records, FAQs, etc.
  - Needs clear scoping: what data sources feed the AI, what stays private vs public
  - Consider embeddings + vector search vs API-based retrieval-augmented generation (RAG)
  - Privacy implications: member data must NEVER leak into AI responses unless explicitly intended
  - Could start simple (FAQ-style knowledge base) and expand to full RAG later
  - Admin controls: toggle on/off, choose which data sources are indexed, review/audit responses

## Integrations — Not Started

- [x] Google Analytics: GA4 Measurement ID setting on Website page, gtag.js output in wp_head, admin traffic exclusion option
- [ ] bbPress forum integration (if installed):
  - Detect bbPress via `class_exists('bbPress')`
  - Members-only forum access via existing `_sp_members_only` page mechanism
  - Role sync: active members can post, expired get read-only, non-members blocked — hook `bbp_current_user_can_access_forum_id` and `bbp_current_user_can_publish_topics`
  - Nav integration: auto-add Forums link if bbPress detected
  - Admin flyout: show under Communications group
  - Module toggle: "Forums (bbPress)" in Settings → Modules, nudge to install bbPress if not present
  - ~200-300 lines of glue code — let bbPress handle the forum engine
- [ ] Mailchimp: sync member list to Mailchimp audience (white paper claims this)
- [ ] Zoom: event integration for online programming (white paper mentions this)
- [ ] Note: PayPal and Stripe are under Payment Processing above

## White Paper Alignment — Review Needed

- [ ] Tighten white paper language: "dues processing" listed as current feature — payments aren't fully built yet
- [ ] "Core feature" claim for genealogical records search — module exists now but needs real data
- [ ] Consider adding a Roadmap section to the white paper to separate built/in-progress/planned
- [ ] Or soften present-tense claims to "core and planned features include..."

---

## Not Doing (spec divergences)

- ~~OOP singleton architecture~~ → function-based single-file
- ~~Gutenberg blocks (15)~~ → page builder widgets serve this role
- ~~License keys / auto-update server~~ → pure GPL, no restrictions
- ~~Swiper.js~~ → custom slider
- ~~Separate volunteer admin panel (/sp-admin/)~~ → existing admin panel is sufficient
- ~~"Contributions welcomed" language~~ → open source ≠ open to contributions
- ~~Built-in forums~~ → Most societies already use Facebook groups; building a quality forum engine is thousands of lines for minimal adoption. bbPress exists for societies that want WP-native forums. The existing `community_link` page builder widget lets Harold point members to wherever discussion happens.
