# SocietyPress — TO-DO

Reference spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
Architecture divergences from spec: function-based single-file (not OOP singleton), no Gutenberg blocks (page builder widgets instead), no license keys or update server (pure GPL, no restrictions)

---

## Completed

### Core Platform
- [x] Single-file plugin architecture (43,745 lines, function-based, inline JS/CSS)
- [x] 39 database tables via dbDelta on activation
- [x] Constants: `SOCIETYPRESS_VERSION`, `SOCIETYPRESS_PLUGIN_DIR`, `SOCIETYPRESS_PLUGIN_URL`, `SOCIETYPRESS_PLUGIN_FILE`
- [x] Settings: single `societypress_settings` option array (68 keys), 7-tab admin page (Website, Organization, Membership, Directory, Events, Privacy, Design)
- [x] Admin: unified sidebar with flyout groups (Communications, Finances), WP branding hidden, custom login page
- [x] Admin dashboard: stat cards (total/active/expiring/expired/new members), upcoming events, expiring members, recent signups, quick links, site info
- [x] Site lockdown: logged-in for frontend, admin-only for backend
- [x] XChaCha20-Poly1305 encryption via libsodium for sensitive fields
- [x] Email system: `pre_wp_mail` interceptor logs ALL emails to `sp_email_log`, dev mode blocking, configurable From/Reply-To headers
- [x] Email log admin: stat cards (sent/blocked/failed/total), status/type filters, search, single-entry detail with sandboxed iframe body preview
- [x] GDPR compliance: 5 privacy data exporters + 5 erasers, privacy policy content registration
- [x] Unified site search: searches events, library, resources, members (logged-in), newsletters (logged-in), WP pages — frontend template + AJAX JSON endpoint
- [x] Audit logging (partial): member CRUD, status changes, position/committee assignments
- [x] GitHub repo: cleaned up, current single-file plugin + theme, GPL-2.0

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
- [x] Public storefront (/store/): category sidebar with counts, product card grid, quantity selector, placeholder Add to Cart buttons
- [x] Products sourced from library catalog (`acq_code = 'SAGHS Publication'`, `item_value > 0`)
- [x] 8 auto-categorized store categories

### SAGHS Child Theme (v0.04d)
- [x] Front page template, 3-level dropdown nav, hamburger menu
- [x] Hero slider with per-line text styling
- [x] Footer: 2-column + contact bar + logo strip, white background
- [x] Header/nav: logo 140px, nav 13px/400 weight Poppins, body padding-top 220px
- [x] SAGHS palette: burgundy #632220, cream #fbebd2, taupe #7f7166, terracotta #ba5f36

### Finances Cleanup
- [x] Imported donation records no longer show recorder's name (`recorded_by = NULL` for imports)

### Join Form
- [x] Public signup shortcode `[societypress_join]`: tier selection, personal info, Stripe checkout
- [x] Stripe integration: direct REST API calls (no SDK), PaymentIntent flow, success/cancel handling

---

## In Progress

(Nothing actively in flight)

---

## Known Bugs & Technical Debt

See `Docs/KNOWN-ISSUES.md` for the full list from the March 2026 audit.

**Critical:**
- [ ] Version mismatch: plugin header says `0.25d`, constant says `0.30d` — pick one and update both
- [ ] Attendance NULL bug: `sp_event_attendance_count()` doesn't account for NULL `attended` column — counts are wrong
- [ ] Join form sends welcome email before payment completes — member created pre-payment

**Should Fix:**
- [ ] Merge tag syntax split: renewal/welcome emails use `{{double_braces}}`, blast emails use `{single_braces}}` — unify to one syntax
- [ ] GDPR gap: donations not covered by privacy exporters/erasers
- [ ] Library item detail AJAX missing `nopriv` handler — non-logged-in users can't expand catalog rows if catalog is public
- [ ] jQuery usage in contact form widget and album edit page (project policy: vanilla JS only)
- [ ] `get_page_by_title()` deprecated in WP 6.2 — replace with `WP_Query`
- [ ] `auto_update_plugin` filter affects ALL plugins, not just SocietyPress
- [ ] Event delete doesn't clean up associated time slots
- [ ] Duplicate `Deceased` key in CSV import column map (cosmetic)
- [ ] Duplicate `{{organization_name}}` merge tag key (cosmetic)
- [ ] No rate limiting on join form submissions
- [ ] Server path exposure in event import hidden field
- [ ] Orphaned temp files from CSV imports not cleaned up
- [ ] Breadcrumb settings exist in code but have no admin UI
- [ ] Help request notifications blast all site members with no opt-out

**i18n:**
- [ ] Retroactive i18n pass on all admin strings (volunteer section, store, resource links, many admin notices unwrapped)

---

## Membership — Remaining

- [ ] Member portal polish:
  - Optional admin approval for profile changes (setting already exists)
  - AJAX save (individual fields or all at once) — currently uses full-page POST/redirect
- [ ] Contact information table: Separate from core member record (spec: `sp_member_contact`)
  - Primary/secondary email, home/cell/work phone, preferred phone, home + mailing address
  - Phone auto-formatting, email validation
- [ ] Genealogy fields:
  - Surnames being researched (`sp_member_surnames`) with normalized variants and research notes — table exists, frontend rough
  - Geographic research areas (`sp_member_research_areas`) with time periods — table exists, frontend missing
  - Member relationships (`sp_member_relationships`): spouse, family, referred by — table exists, no UI
  - 8 genealogy service integrations (WikiTree, FamilySearch, Geni, WeRelate, Ancestry, MyHeritage, Find A Grave, 23andMe) — settings toggle exists, UI doesn't
- [ ] Contact data encryption: AES-256-GCM at rest for sensitive fields (encryption infrastructure built, not applied to contacts yet)
- [ ] Couples sharing accounts: revisit later

## Events — Remaining

- [ ] Time slots: individual capacity limits per slot (CRUD built, capacity logic incomplete)
- [ ] Waitlist: auto-promotion when cancellation occurs (volunteer waitlist works, event waitlist needs work)
- [ ] Payment tracking: fee amounts and payment status per registration
- [ ] Calendar bug: current month renders full-width, other months render narrower — same HTML/CSS from server, likely browser rendering/caching. Needs DevTools inspection.
- [ ] Add to calendar: let members add registered events to their personal calendars (iCal/.ics download link on My Account and/or event detail page)
- [ ] Event change notifications: email registrants when date/time/location changes
- [ ] "Notice only" events: appear on calendar but no detail page or registration (board meetings, holidays)

## Email & Notifications — Remaining

- [ ] Expiration notice: sent after expiration (daily cron) — renewal reminders built, post-expiration notice not
- [ ] Waitlist promotion email: sent when moved from waitlist to confirmed
- [ ] Communication preference check: email vs postal mail vs both — respect before sending
- [ ] Email template editor in admin with merge tag support

## Store — Remaining

- [ ] Real marketing descriptions (currently showing physical specs from library import)
- [ ] Shopping cart / checkout flow
- [ ] Order tracking
- [ ] Payment integration (Stripe infrastructure exists from join form, needs cart flow)
- [ ] Generalize store — currently hardcoded to `acq_code = 'SAGHS Publication'`

## Payment Processing — Remaining

- [ ] Stripe: card payments via PaymentIntent (join form has this, needs generalization)
- [ ] PayPal integration: Balance, Venmo, credit/debit, pay-later (SDK)
- [ ] Sandbox + live modes (settings toggle exists)
- [ ] Payment history table (`sp_payments`)
- [ ] Payment status tracking per member and per event registration

## Genealogical Records — Remaining

- [ ] Needs real data imported — no collections populated yet
- [ ] This is the EasyNetSites "Unified Data Module" equivalent — needs to deliver on parity claim

## Installation Wizard — Extend Existing

Existing wizard (3 steps, lines ~11436-11710): Org Info → Membership → Appearance/Email. Triggers on first activation, redirects admin, saves to `societypress_settings`, marks complete via `sp_wizard_completed` option.

- [ ] Add Step 2.5 (between Membership and Appearance): Feature Selection
  - Checklist of modules: Members, Events, Library, Newsletters, Committees & Leadership, Store, Genealogical Records, Donations, Blast Email, Resource Links, Volunteers, etc.
  - Plain-language descriptions of each so non-technical admins understand what they're choosing
  - All modules on by default — admin unchecks what they don't need
  - Saved as `sp_enabled_modules` option (array of module slugs)
- [ ] Feature toggle system: enable/disable modules after wizard via Settings tab
  - Disabled modules: hide admin menu items, suppress frontend output, skip cron jobs
  - Tables stay intact when a module is disabled — data is never destroyed
  - Re-enabling surfaces everything again, creates any missing tables if needed
  - Key principle: toggling off is never destructive, toggling on is seamless
- [ ] Wire module checks into the codebase: each module's menu registration, shortcode, widget, cron, and frontend rendering checks `sp_module_enabled( 'library' )` (or similar) before executing

## Theme — Remaining

- [ ] Style presets: 6 named presets (Parchment, Slate, Ledger, Hearth, Archive, Chronicle)
  - Curated color/font/token combos, one-click selection, further customization allowed
- [ ] Starter content on activation: auto-create pages (Home, About, Events, Join, Contact, News, Portal, Directory, Newsletters), menus, reading settings, remove default WP post/page
- [ ] Smart nav behavior: Join link hides for logged-in members, Directory hides for logged-out visitors
- [ ] Search dropdown in primary nav
- [ ] My Account menu for logged-in members (profile link + logout)

## Admin — Remaining

- [ ] Admin dashboard: activity feed (future enhancement)
- [ ] Audit logging: complete coverage — currently partial (member CRUD, status, positions)
- [ ] Governance menu: admin UI for creating/managing leadership positions and committees (backend exists, needs dedicated admin menu entry)

## Demo Installation — Not Started

- [ ] Standalone demo site (separate from kndgs.org production)
  - Fake society with sample members, events, records, newsletters
  - Read-only or resettable so evaluators can explore without breaking anything
  - White paper offers this twice — needs to actually exist before the paper circulates

## ENS Migration — Not Started

- [ ] EasyNetSites migration guide: document how to export ENS data and import into SP
  - Member data: CSV export → SP member import with ENS field mapping (import tool built)
  - Genealogical records: if ENS provides any export, build an import path
  - Content pages: manual migration guidance (copy/paste is realistic for most societies)
- [ ] Migration assistance as a selling point — reduce the switching-cost objection

## getsocietypress.org — On Hold

- [ ] Documentation pages (waiting until SP features are more complete)
- [ ] Feedback form (structured: bug report / feature request / general question) — future companion plugin

## AI — Not Started

- [ ] AI-powered Q&A: let members (or the public) ask natural-language questions and get answers drawn from society data
  - Could cover library catalog, events, newsletters, resource links, genealogical records, FAQs, etc.
  - Needs clear scoping: what data sources feed the AI, what stays private vs public
  - Consider embeddings + vector search vs API-based retrieval-augmented generation (RAG)
  - Privacy implications: member data must NEVER leak into AI responses unless explicitly intended
  - Could start simple (FAQ-style knowledge base) and expand to full RAG later
  - Admin controls: toggle on/off, choose which data sources are indexed, review/audit responses

## Integrations — Not Started

- [ ] Mailchimp: sync member list to Mailchimp audience (white paper claims this)
- [ ] Google Analytics: integration beyond what the getsocietypress.org companion plugin does
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
