# SocietyPress — TO-DO

Reference spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
Architecture divergences from spec: function-based single-file (not OOP singleton), no Gutenberg blocks (page builder widgets instead), no license keys or update server (pure GPL, no restrictions)

---

## Completed

- [x] Members: CRUD, individual + org support, CSV import/export, bulk actions
- [x] Members: Directory (frontend, members-only, privacy layers)
- [x] Members: Import field mapping — all 21 ENS fields wired
- [x] Events: 6 tables, categories, add/edit, recurring, registration, attendance, iCal export
- [x] Events: Featured image picker (wp.media deferred to window.load)
- [x] Events: Dual pricing display (member + non-member on listing and detail pages)
- [x] Library: Full catalog (19,418 items), AJAX detail view, faceted search/filters, CSV import/export, stats dashboard
- [x] Library: Data cleanup — media types and shelf locations normalized
- [x] Committees & Leadership: Delegated permissions, chairperson frontend management, officer tracking
- [x] Page Builder: 10 widget types, admin meta box, frontend rendering
- [x] Design System: CSS custom properties, 7 color pickers, font/size/width controls, live preview
- [x] Settings: Tabbed page (Website, Organization, Membership, Directory, Events, Privacy, Design)
- [x] Admin: Unified sidebar with flyout groups, WP branding hidden, custom login page
- [x] Site lockdown: Logged-in for frontend, admin-only for backend
- [x] GitHub repo: Cleaned up, current single-file plugin + theme, GPL-2.0
- [x] Member re-import: 385 members imported with all new fields populated
- [x] the society child theme: Front page, 3-level nav, hamburger, footer, hero slider
- [x] Resource Links: CSV import (EasyNetSites format), auto-category creation, frontend directory with search + category dropdown, unified search integration
- [x] Hero slider: Per-line text styling (size/weight/color per line, legacy migration)
- [x] the society child theme: Footer restyled to match reference site (2-column + contact bar + logo strip)
- [x] Blast Email: Compose/send mass emails to members by group or tier, batch sending via WP cron, merge tags, opt-out support, delivery tracking
- [x] Donations: Campaign-based donation tracking, CRUD, acknowledgment emails, anonymous + in-kind support
- [x] Member Groups Enhancement: Bulk "Assign to Group" action, group filter on Members list
- [x] Member portal (My Account): Profile photo, personal info, contact, address, seasonal address, communication preferences (incl. blast opt-out), directory privacy, interests & skills, research surnames (county/state/country/year range), my events, change password
- [x] Directory: Hide nav link for logged-out visitors, "Surname Being Researched" filter label
- [x] i18n: My Account page fully wrapped in __() / esc_html__() / esc_attr__() with societypress text domain
- [x] Email obfuscation: All frontend email addresses use JS-based obfuscation (base64 split, assembled on page load) to block scrapers
- [x] Admin dashboard: Stat cards (total/active/expiring/expired/new), upcoming events, expiring members list, recent signups, quick links, site info
- [x] the society child theme: Header/nav matching reference site (logo 140px, padding 220px)

## In Progress

---

## Membership — Remaining

- [ ] Member portal polish:
  - Optional admin approval for profile changes (setting already exists)
  - AJAX save (individual fields or all at once) — currently uses full-page POST/redirect
- [ ] Join form: Public signup with online payment ([societypress_join])
- [ ] Contact information table: Separate from core member record (spec: sp_member_contact)
  - Primary/secondary email, home/cell/work phone, preferred phone, home + mailing address
  - Phone auto-formatting, email validation
- [ ] Genealogy fields:
  - Surnames being researched (sp_member_surnames) with normalized variants and research notes
  - Geographic research areas (sp_member_research_areas) with time periods
  - Member relationships (sp_member_relationships): spouse, family, referred by
  - 8 genealogy service integrations (WikiTree, FamilySearch, Geni, WeRelate, Ancestry, MyHeritage, Find A Grave, 23andMe) — settings toggle exists, UI doesn't
- [ ] Contact data encryption: AES-256-GCM at rest for sensitive fields
- [ ] Couples sharing accounts: Revisit later

## Events — Remaining

- [ ] Time slots: Multiple slots per event with individual capacity limits
- [ ] Waitlist: Auto-promotion when cancellation occurs
- [ ] Payment tracking: Fee amounts and payment status per registration
- [x] Calendar: Page Builder widget + standalone page template, shared renderer, category filter, month navigation
- [ ] Calendar bug: Current month renders full-width, other months render narrower — same HTML/CSS from server, likely browser rendering or caching issue. Needs DevTools inspection.
- [ ] Add to calendar: Let members add registered events to their personal calendars (iCal/.ics download link on My Account and/or event detail page)
- [ ] Event change notifications: Email registrants when date/time/location changes
- [ ] "Notice only" events: Appear on calendar but no detail page or registration (board meetings, holidays)

## Email & Notifications — Remaining

- [x] Welcome email: Sent on new member creation, merge tags, configurable subject, enabled via settings
- [x] Renewal reminders: Automated at 30/15/7 days before expiration (daily cron), dedup via sp_renewal_reminders table
- [ ] Expiration notice: Sent after expiration (daily cron)
- [x] Registration confirmation: Sent on event registration (confirmed + waitlisted), includes event details
- [x] Event reminders: Daily cron sends reminders before upcoming events
- [ ] Waitlist promotion email: Sent when moved from waitlist to confirmed
- [ ] Communication preference check: Email vs postal mail vs both — respect before sending
- [x] Blast email: Compose, target by group/tier/all, batch send via WP cron, merge tags, opt-out, delivery stats
- [ ] Email template editor in admin with merge tag support

## Volunteer System — Not Started

- [ ] Volunteer opportunities: Title, description, location, type (one-time/recurring/ongoing), date, capacity, skills needed, status, committee association
- [ ] Signups: Frontend browsing + signup via shortcode ([societypress_volunteer_opportunities])
- [ ] Waitlist with auto-promotion
- [ ] Signup lifecycle: Confirmed → Waitlist → Completed → Cancelled → No-Show
- [ ] Hours tracking: Logged per signup, builds contribution record
- [ ] Tables: sp_volunteer_opportunities, sp_volunteer_signups

## Newsletter Archive — Done

- [x] PDF upload through admin
- [x] Automatic cover thumbnail generation (Imagick)
- [x] Browse archive, inline preview modal with zoom/page nav
- [x] Download restricted to members only
- [x] Admin card grid, frontend grid, search

## Genealogical Records Module — Not Started

- [ ] Records database: Searchable transcribed genealogical records (cemetery indexes, census transcriptions, church records, obituary indexes, etc.)
  - Distinct from Library (which catalogs physical items) — this is digitized/transcribed record data
  - Tables: sp_record_collections, sp_records, sp_record_fields (flexible schema per collection type)
  - Field-level access control: public vs members-only per field per collection
  - Public-facing search with faceted filters (record type, date range, location, surname)
  - Full-text search across all record fields
  - CSV/spreadsheet import per collection type with field mapping
  - Admin: collection manager, record browser, import tool, field configuration
  - Frontend: public search page ([societypress_records]), detail view with access-controlled fields
- [ ] This is the EasyNetSites "Unified Data Module" equivalent — the white paper claims parity, so this needs to deliver

## Payment Processing — Not Started

- [ ] PayPal integration: Balance, Venmo, credit/debit, pay-later (SDK)
- [ ] Stripe integration: Card payments
- [ ] Sandbox + live modes (settings toggle exists)
- [ ] Payment history table (sp_payments)
- [ ] Payment status tracking per member and per event registration

## Theme — Remaining

- [ ] Style presets: 6 named presets (Parchment, Slate, Ledger, Hearth, Archive, Chronicle)
  - Curated color/font/token combos, one-click selection, further customization allowed
- [ ] Starter content on activation: Auto-create pages (Home, About, Events, Join, Contact, News, Portal, Directory, Newsletters), menus, reading settings, remove default WP post/page
- [ ] Smart nav behavior: Join link hides for logged-in members, Directory hides for logged-out visitors
- [ ] Search dropdown in primary nav
- [ ] My Account menu for logged-in members (profile link + logout)

## Admin — Remaining

- [x] Admin dashboard: Stat cards (total/active/expiring/expired/new), upcoming events table, expiring members table, recent members table, quick links, site info
- [ ] Admin dashboard: Activity feed (future enhancement)
- [ ] Audit logging: Complete coverage — member CRUD, status changes, position/committee assignments, volunteer activity (partially built)

## Demo Installation — Not Started

- [ ] Standalone demo site (separate from example.org production)
  - Fake society with sample members, events, records, newsletters
  - Read-only or resettable so evaluators can explore without breaking anything
  - White paper offers this twice — needs to actually exist before the paper circulates

## ENS Migration — Not Started

- [ ] EasyNetSites migration guide: Document how to export ENS data and import into SP
  - Member data: CSV export → SP member import with ENS field mapping
  - Genealogical records: If ENS provides any export, build an import path
  - Content pages: Manual migration guidance (copy/paste is realistic for most societies)
- [ ] Migration assistance as a selling point — reduce the switching-cost objection

## getsocietypress.org — On Hold

- [ ] Documentation pages (waiting until SP features are more complete)
- [ ] Feedback form (structured: bug report / feature request / general question) — future companion plugin

## AI — Not Started

- [ ] AI-powered Q&A: Let members (or the public) ask natural-language questions and get answers drawn from society data
  - Could cover library catalog, events, newsletters, resource links, genealogical records, FAQs, etc.
  - Needs clear scoping: what data sources feed the AI, what stays private vs public
  - Consider embeddings + vector search vs API-based retrieval-augmented generation (RAG)
  - Privacy implications: member data must NEVER leak into AI responses unless explicitly intended
  - Could start simple (FAQ-style knowledge base) and expand to full RAG later
  - Admin controls: toggle on/off, choose which data sources are indexed, review/audit responses

## Integrations — Not Started

- [ ] Mailchimp: Sync member list to Mailchimp audience (white paper claims this)
- [ ] Google Analytics: Integration beyond what the getsocietypress.org companion plugin does
- [ ] Zoom: Event integration for online programming (white paper mentions this)
- [ ] Note: PayPal and Stripe are under Payment Processing above

## White Paper Alignment — Review Needed

- [ ] Tighten white paper language: "dues processing" listed as current feature — payments aren't built yet
- [ ] "Core feature" claim for genealogical records search — module doesn't exist yet
- [ ] Consider adding a Roadmap section to the white paper to separate built/in-progress/planned
- [ ] Or soften present-tense claims to "core and planned features include..."

## Not Doing (spec divergences)

- ~~OOP singleton architecture~~ → function-based single-file
- ~~Gutenberg blocks (15)~~ → page builder widgets serve this role
- ~~License keys / auto-update server~~ → pure GPL, no restrictions
- ~~Swiper.js~~ → custom slider
- ~~Separate volunteer admin panel (/sp-admin/)~~ → existing admin panel is sufficient
- ~~"Contributions welcomed" language~~ → open source ≠ open to contributions
