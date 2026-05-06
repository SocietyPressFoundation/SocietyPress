# SocietyPress — Feature Reference

Last updated: April 19, 2026
Version: 1.0.19

This is the authoritative inventory of everything SocietyPress ships with.
Each module section describes the module's purpose, what administrators
can do with it, what end-users see, the database tables backing it, and
the shortcodes and widgets it exposes.

For the technical reference (hooks, AJAX endpoints, cron jobs, template
files, helper functions), see [`ARCHITECTURE.md`](ARCHITECTURE.md).

---

## Contents

- [How modules are turned on and off](#how-modules-are-turned-on-and-off)
- [Access areas and permissions](#access-areas-and-permissions)
- Modules:
  1. [Members](#1-members) (always on)
  2. [Events](#2-events)
  3. [Library](#3-library)
  4. [Newsletters](#4-newsletters)
  5. [Resources](#5-resources)
  6. [Committees & Governance](#6-committees--governance)
  7. [Volunteers](#7-volunteers)
  8. [Donations & Campaigns](#8-donations--campaigns)
  9. [Blast Email](#9-blast-email)
  10. [Genealogical Records](#10-genealogical-records)
  11. [Store](#11-store)
  12. [Documents](#12-documents)
  13. [Photos & Videos](#13-photos--videos)
  14. [Voting & Elections](#14-voting--elections)
- Cross-cutting features:
  - [Page Builder](#page-builder)
  - [Design System](#design-system)
  - [Email System](#email-system)
  - [Search](#search)
  - [Security & Privacy](#security--privacy)
  - [Administration](#administration)
  - [Site Branding](#site-branding)
  - [Import & Export](#import--export)
  - [Internationalization](#internationalization)
  - [Progressive Web App](#progressive-web-app)

---

## How modules are turned on and off

Every module except Members is toggleable. An administrator turns modules
on or off in **Settings → Modules** using a card grid of labeled switches
with Enable All and Disable All helpers. Disabling a module:

- Hides its admin pages and menu entries
- Removes its widgets from the Page Builder palette
- Strips its shortcodes from published frontend pages (shortcodes render
  empty)
- Disables its AJAX endpoints so no data leaks via URL-poking

Data in disabled modules is retained, not deleted. Turning the module
back on restores everything.

## Access areas and permissions

SocietyPress defines **ten access areas** — logical groupings like
"Members Admin," "Events Admin," "Finance," "Committees" — and **eight
role templates** that map WordPress roles onto those areas. An
administrator can change who sees what by reassigning access-area
permissions without editing code. The default role templates:

- **Society Administrator** — all areas
- **Membership Chair** — Members, Volunteers, Committees
- **Events Chair** — Events, Volunteers
- **Librarian** — Library
- **Newsletter Editor** — Newsletters, Blast Email
- **Treasurer** — Finance (donations, store, reports)
- **Webmaster** — Website, Design, Pages
- **Standard Member** — frontend-only; no admin access

---

## 1. Members

**Status:** Always on. Cannot be disabled — it's the base of everything else.
**Tables:** 6 (`sp_members`, `sp_member_groups`, `sp_member_group_assignments`,
`sp_member_surnames`, `sp_member_research_areas`, `sp_member_relationships`)
plus `sp_membership_tiers`.

The core module: people who belong to the society, their dues, their
groups, and what they're researching.

### What administrators can do

- Create, edit, and delete member records — individuals or organizations
- Assign statuses: Active, Expired, Pending, Cancelled, Deceased
- Configure up to 14 membership tiers with custom pricing and duration
- Organize members into named groups with bulk assignment
- Track per-member **surnames being researched**, complete with time
  periods and geographic scope
- Record member-to-member **relationships** (spouse, family, referral)
- Import from CSV — the importer understands 86+ EasyNetSites column
  mappings plus a generic format, with live preview and auto field-mapping
- Export any filtered view to CSV with live-count confirmation
- Perform bulk operations (status change, group assignment, delete) on
  selected members

### What members see

- A **members-only directory** with configurable privacy layers — members
  can independently hide their address, phone, email, or listing entirely
- A personal **"My Account"** portal with tabs for:
  - Profile photo and basic identity
  - Personal info, contact, mailing and seasonal addresses
  - Communication preferences including blast-email opt-out
  - Directory privacy settings
  - Interests and skills
  - Researched surnames with date ranges
  - Registered events history
  - Password change

### Shortcodes / widgets

- `[societypress_join]` — public join form with paid and free tier support,
  Stripe and PayPal checkout, and member-account provisioning
- `member_directory` widget — privacy-aware searchable directory

### Privacy

- All sensitive fields (name, address, phone, email, tier, notes,
  research content, group) are encrypted at rest via XChaCha20-Poly1305
- Frontend email addresses are obfuscated with base64 splitting and
  JavaScript reassembly to defeat scrapers
- Directory visibility is controlled by a combination of per-field
  privacy toggles and a global directory policy (members-only, public,
  or completely hidden)

---

## 2. Events

**Tables:** 6 (`sp_events`, `sp_event_categories`,
`sp_event_category_assignments`, `sp_event_registrations`,
`sp_event_speakers`, `sp_event_time_slots`)

Meetings, workshops, lectures, and society outings — scheduled, promoted,
registered, and checked in.

### What administrators can do

- Create events with start/end dates, recurring schedules (weekly,
  monthly nth-day), and multiple time slots per event
- Categorize events with a custom taxonomy
- Set capacity limits with automatic waitlist on overflow
- Configure dual pricing (member and non-member rates)
- Accept online registration with member and non-member flows
- Track walk-in attendance with a simple check-in interface
- Add multiple speakers per event with bios
- Import events from CSV (supports EasyNetSites format)
- Export attendee lists, registration records, and full event rosters

### What members and visitors see

- Monthly calendar grid, embeddable on any page
- Upcoming events lists, filterable by category
- Event detail pages with registration form, speakers, and location
- iCal export per event (so it drops into Google Calendar, Outlook, Apple
  Calendar)

### Shortcodes / widgets

- `event_list` widget — upcoming events list
- `event_calendar` widget — month-grid calendar

---

## 3. Library

**Tables:** 2 (`sp_library_items`, `sp_library_categories`)

A full OPAC-style catalog for society libraries. Tested at 19,000+ items.

### What administrators can do

- Catalog items of any media type (books, maps, periodicals, microfilm,
  manuscripts, etc.) with title, author, publication year, ISBN,
  call number, subjects, and keywords
- Import items from CSV with field mapping
- Bulk-enrich the catalog with cover images from the Open Library API,
  with a progress bar for long imports
- Track collection statistics: total value, breakdowns by type and source,
  acquisition history
- Export the catalog to CSV

### What members and visitors see

- **OPAC-style frontend** — tabbed search (keyword, title, author, subject,
  call number), browse-by-type cards, a popular-subjects tag cloud, and
  expandable detail rows
- Pagination with result counts
- Subject and media-type filtering

### Shortcodes / widgets

- `library_catalog` widget — searchable public catalog

---

## 4. Newsletters

**Tables:** 1 (`sp_newsletters`)

PDF archive of society publications, with thumbnails and a members-only
download gate.

### What administrators can do

- Upload PDF issues with title, issue number, date, and description
- Thumbnails are generated automatically from the first page using
  Imagick (falls back to a stock cover if Imagick isn't installed)
- Control access per issue (public or members-only)
- Search titles, descriptions, and metadata

### What members and visitors see

- Grid view of past issues with cover thumbnails
- Inline PDF viewer modal that opens on the page (no download required)
- Members-only issues display a lock and prompt for sign-in
- Search across the full archive

### Shortcodes / widgets

- `newsletter_archive` widget — grid of past issues

---

## 5. Resources

**Tables:** 2 (`sp_resources`, `sp_resource_categories`)

Curated collection of external links and downloadable resources — the
"useful links" list your society's research committee maintains.

### What administrators can do

- Add resources with title, URL, description, and category
- Import from CSV (EasyNetSites-compatible)
- Auto-create categories from imported data
- Organize by sort order within categories

### What visitors see

- Frontend directory with search and category filtering
- Integrated into the site-wide unified search

### Shortcodes / widgets

- `resource_links` widget

---

## 6. Committees & Governance

**Tables:** 4 (`sp_committees`, `sp_committee_members`,
`sp_leadership_positions`, `sp_leadership_terms`)

Formal society structure — committees, their chairs, leadership
positions, and officer terms.

### What administrators can do

- Create committees with descriptions and member rosters
- Designate chairs and co-chairs with delegated admin permissions
  (chairs can edit their own committee's volunteers, events, and
  communications without full administrative access)
- Track leadership positions (President, Treasurer, etc.) with election
  dates and term boundaries

### What members see

- Committee pages listing membership and chairs
- Leadership page showing current officers

---

## 7. Volunteers

**Tables:** 4 (`sp_volunteer_opportunities`, `sp_volunteer_signups`,
`sp_volunteer_hours`, `sp_volunteer_skills`)

Opportunities, sign-ups, waitlists, and hours tracking.

### What administrators can do

- Post volunteer opportunities with skill requirements, capacity limits,
  and scheduling windows
- Review signups and manage waitlists (auto-promote when a slot opens)
- Log volunteer hours with descriptions
- Export hours for annual recognition or grant reporting

### What members see

- Frontend list of open opportunities
- One-click signup and cancel via shortcode
- Personal "my hours" view in their account portal

### Shortcodes / widgets

- `volunteer_opportunities` widget

---

## 8. Donations & Campaigns

**Tables:** 2 (`sp_campaigns`, `sp_donations`)

Track contributions against campaigns. Supports cash, check, online, and
in-kind donations, with anonymous-donor handling.

### What administrators can do

- Create donation campaigns with goals and progress bars
- Record donations against campaigns, including in-kind gifts
- Mark donors anonymous (preserves the amount, hides the identity)
- Bulk-send acknowledgment emails with merge tags

### What visitors see

- Donations widget showing campaign progress
- Donation form with campaign selection
- Anonymous donor recognition where applicable

### Shortcodes / widgets

- `donations` widget

---

## 9. Blast Email

**Tables:** 2 (`sp_blast_emails`, `sp_blast_email_log`)

Mass email to members, with batching, merge tags, delivery tracking, and
opt-out handling.

### What administrators can do

- Compose and send emails to segments — by group, by tier, or to all
  active members
- Use merge tags for personalization (member name, renewal date, etc.)
- Queue sends in batches (via WP-Cron) to respect host sending limits
- Track per-recipient delivery status

### What members see

- A blast-email opt-out preference in their account portal
- Unsubscribe links in every blast message

---

## 10. Genealogical Records

**Tables:** 4 (`sp_record_collections`, `sp_record_collection_fields`,
`sp_records`, `sp_record_values`)

A flexible EAV-based repository for genealogical records. Societies can
publish indexed cemeteries, census transcriptions, marriage records,
obituaries, and anything else their research committee has digitized.

### What administrators can do

- Create **collections** with custom field schemas — each collection
  defines its own columns (a cemetery collection has "Section" and
  "Lot"; an obituary collection has "Newspaper" and "Publication Date")
- Start from 13 **record type templates**: Cemetery, Census, Church,
  Marriage, Vital, Military, Land, Probate, Immigration, Newspaper,
  Tax, Obituary, General
- Configure per-field visibility (public or members-only) and
  searchability
- Import records from CSV with auto field-mapping
- Export collections to CSV or the GENRECORD exchange format

### What visitors see

- Collection landing pages with full-text search (FULLTEXT index on a
  per-record search-text column keeps queries fast)
- Per-field access control — a "Notes" field might be members-only while
  Name/Date stay public
- Result pages with contextual highlighting

---

## 11. Store

**Tables:** 3 (`sp_store_products`, `sp_store_orders`,
`sp_store_order_items`)

A storefront for publications, merchandise, and library items offered
for sale or loan.

### What administrators can do

- Create products with pricing, stock levels, and descriptions
- Sell library items directly from the catalog (shared backend, unified
  frontend)
- Manage orders and update order status
- Decrement stock on checkout, mark items sold-out when exhausted

### What visitors see

- Public storefront with category sidebar and product grid
- Unified listing of store products and available library items
- Cart and checkout flow *(payment processing is in progress)*

---

## 12. Documents

**Tables:** 2 (`sp_documents`, `sp_document_categories`)

Board documents, policies, bylaws, forms — organized, access-controlled,
and searchable.

### What administrators can do

- Upload documents with title, description, category, and date
- Set visibility: public, members-only, or board-only
- Organize by custom categories with sort order

### What members see

- Categorized document library
- Members-only documents are listed with a lock icon even when
  inaccessible, so members know what exists

---

## 13. Photos & Videos

**Tables:** 2 (`sp_gallery_folders`, `sp_gallery_items`)

Nested-folder gallery (up to 5 levels deep) combining images from the
WordPress media library with embedded YouTube videos.

### What administrators can do

- Create nested folder structures via AJAX (drag-reorder, rename, delete)
- Add images by picking from the WP Media Library
- Embed YouTube videos by URL
- Control visibility per folder

### What visitors see

- Folder-tree browser on the frontend
- Lightbox viewer with keyboard navigation
- YouTube embeds play in-lightbox

---

## 14. Voting & Elections

**Tables:** 4 (`sp_ballots`, `sp_ballot_questions`,
`sp_ballot_options`, `sp_ballot_votes`)

In-platform elections for officer slates, bylaws amendments, or any
member vote.

### What administrators can do

- Create ballots with multiple questions (yes/no, single choice, multi
  choice)
- Schedule voting windows with explicit open and close times
- Restrict eligible voters by member status and tier
- View live tallies and exportable results

### What members see

- An "Active Ballots" prompt in their account portal during open
  elections
- A ballot form that tracks votes cast to prevent duplicates
- Post-close results if the ballot is configured to publish them

---

## 15. Lineage Programs

**Tables:** 3 (`sp_lineage_programs`, `sp_lineage_applications`,
`sp_lineage_proofs`)

Recognition programs for members who can document descent from
historically significant ancestors — First Families, Pioneer Settlers,
Civil War Veterans Descendants, Mayflower Descendants. Multi-program:
a society can run any number of programs simultaneously.

### What administrators can do

- Define any number of programs with custom requirements per program
- Review applications in an admin queue with a status workflow
  (pending → under review → approved / rejected / needs more proof)
- Approve applications and watch the public roster update with
  auto-numbered printable certificates
- Charge an optional Stripe-billed application fee
- Export applications and roster as CSV

### What members and visitors see

- A public application form (`[sp_lineage_apply]`) with proof-document
  upload
- A logged-in member's submitted-applications view
  (`[sp_lineage_my_applications]`)
- The public roster (`[sp_lineage_roster]`) of approved members
- Printable certificates at `/?sp_certificate=NNN`

### Shortcodes / widgets

- `[sp_lineage_apply]` — application form
- `[sp_lineage_my_applications]` — member's own submissions
- `[sp_lineage_roster]` — public approved-member list

---

## 16. Help Requests (Research Help)

**Tables:** 2 (`sp_help_requests`, `sp_help_responses`)

Public Q&A archive on the duty-librarian model. Anyone can submit a
question; members respond with time-tracked answers. Responses
automatically log to the unified volunteer-hours ledger.

### What administrators can do

- Approve incoming requests (math captcha + email verification + per-
  email rate limiting filter most spam at submission)
- Bulk actions: approve, mark-resolved, hide, delete
- Endorse helpful responses; mark a response as the accepted answer
- Convert a Help Request to a paid Research Case if it's beyond
  comradery scope

### What members and visitors see

- Public submission form (`[sp_help_request_submit]`)
- Public archive (`[sp_help_requests_archive]`) with tag-filter pills
- Members can respond, edit their own responses, log time per response
- A member's volunteer-hours summary widget
  (`[sp_my_volunteer_hours]`)

### Shortcodes / widgets

- `[sp_help_request_submit]` — public submission form
- `[sp_help_requests_archive]` — public archive
- `[sp_my_volunteer_hours]` — logged-in member's hours summary

---

## 17. Research Services (Paid)

**Tables:** 3 (`sp_research_cases`, `sp_research_invoices`,
`sp_research_messages`)

The opt-in escalation from Help Requests for cases that genuinely need
many hours of focused work. Stripe-billed up front, additional hours
invoiced as needed.

### What administrators can do

- Configure intake form, hourly rate, minimum hours, intake fee
- Review case queue, assign a researcher, set status
- Researcher dashboard with one-click claim and inline log-hours
- Bill additional hours: researcher requests → Stripe-billed →
  case bumps authorized hours
- In-system case messaging with attachments and email notifications
- Status-change emails on every transition

### What members and visitors see

- Public intake form (`[sp_research_request]`) with Stripe checkout
- Logged-in member's submitted-cases view (`[sp_my_research_cases]`)
- Researcher's claimed-cases dashboard
  (`[sp_my_research_assignments]`)

### Shortcodes / widgets

- `[sp_research_request]` — public intake form
- `[sp_my_research_cases]` — member's cases
- `[sp_my_research_assignments]` — researcher dashboard

---

# Cross-cutting features

These aren't modules — they're capabilities that show up everywhere.

## Page Builder

A classic-editor page builder with **21 widget types**, designed so
senior volunteers can build pages without writing HTML:

Hero slider (per-line text styling), events calendar, event list, member
directory, library catalog, newsletter archive, volunteer opportunities,
resource links, donations progress, call-to-action bar, feature grid,
testimonial, FAQ accordion, image gallery, photo gallery link, YouTube
embed, text + image, contact form, raw HTML, separator, and spacer.

Every widget is configurable with the same form patterns as the core
admin — no separate UX to learn.

## Design System

A live-preview theme configurator under **Appearance → Design**:

- **Seven color pickers** covering background, text, heading, link,
  accent, border, and button colors
- Font family, font size, heading font, and heading size controls
- Content width, sidebar width, and layout controls
- Style presets you can save and re-apply
- A live-preview iframe so changes are visible without reloading
- A **site color extractor** that samples an uploaded logo to propose a
  coherent palette

All values are written as CSS custom properties, consumed by the theme
with sensible fallbacks, so a freshly activated theme looks good even
before the wizard finishes.

## Insights

**Tables:** none (reads from existing module tables)

A single admin/board-only landing page (**SocietyPress → Insights**)
that pulls one headline number per enabled module across a chosen
time window. Active members, events held, donations raised, volunteer
hours, records added, blasts sent — all on one screen with sparkline
trends.

- 16-card grid (one per active module); disabled modules are hidden
- Time-window dropdown: rolling 30 / 90 / 365 days, this fiscal year,
  last fiscal year (the fiscal-year boundary reuses
  `membership_start_month`)
- Inline-SVG sparklines (no charting library, no JavaScript)
- Permission gate uses the existing `sp_view_reports` access area, so
  a treasurer or membership chair can be granted access without full
  admin rights
- Filterable through `sp_insights_panels` so child themes can append
  custom stat cards

## Email System

**Table:** 1 (`sp_email_log`)

- Every outgoing email is **logged** with status (queued, sent, failed),
  recipient, subject, and delivery metadata
- **Dev mode** blocks sending entirely for testing — logs the intended
  recipient and body
- Configurable From name and Reply-To headers so society emails don't
  look like "WordPress <wordpress@yoursociety.org>"
- Transactional templates: welcome emails, renewal reminders,
  registration confirmations, event reminders, acknowledgments
- Merge-tag support in all templates

## Search

One unified search box, across everything:

- Events, library items, resources, members (respecting directory
  privacy), newsletters, and WordPress pages
- Per-module result sections with appropriate access controls
- An AJAX JSON endpoint powers instant-search experiences if a widget
  needs them

## Security & Privacy

- **XChaCha20-Poly1305** field-level encryption via libsodium for all
  sensitive member data at rest
- **Nonce verification** on every admin form and AJAX endpoint
- **Capability checks** on every admin page
- **Frontend email obfuscation** (base64 split + JS reassembly)
- **Site lockdown** mode that requires login to view any page
- **Custom login page** that uses society branding rather than WordPress
  defaults
- **GDPR compliance** — five data exporters (personal info, events,
  library activity, communications, donations) and five erasers
  (including pseudonymizing erasers for donations that preserve financial
  records per IRS requirements)
- **ZIP slip mitigations** on the installer and bundle extraction
- **Per-constant regex validation** on database configuration

## Administration

- **Unified admin sidebar** with labeled flyout groups — Members,
  Events, Library, etc. — collapsing the native WP admin menu
- **WordPress branding hidden** throughout the admin — society members
  and volunteer admins see the society's name, not "WordPress"
- **Dashboard** with stat cards (members, dues, events, recent activity),
  upcoming events widget, expiring memberships widget, recent signups
  widget
- **Setup wizard** that runs on first activation — three steps: society
  info, membership configuration, appearance and email
- **WP-CLI** compatibility for server-side administration

## Site Branding

- Custom logo support at header, login, and email-footer positions
- Per-position logo fallback (falls back to text-based site name)
- Favicon and apple-touch-icon support
- Configurable primary and secondary colors propagated across theme,
  admin, emails, and PWA manifest

## Import & Export

- **Full-site export** at Settings → Export & Backup — one ZIP
  containing an SQL dump of every `sp_*` table (with member fields
  decrypted to plaintext) plus a restore README
- **Per-module CSV import/export** — Members, Events, Library, Resources,
  Records
- **GENRECORD export** — the open genealogical records exchange format,
  for interoperability with other platforms
- **EasyNetSites compatibility** — the Members importer understands
  ENS's 86-column CSV format natively

## Internationalization

- ~4,150 text-domain-wrapped strings across the plugin and parent theme
- Text domain: `societypress` (unified across all child themes)
- Generated `societypress.pot` files for both plugin and parent theme,
  committed under each component's `languages/` directory
- Translation-ready from day one

## Progressive Web App

- Web app manifest served at `?sp_manifest=1`, populated from society
  name, theme color, and icons
- Service worker at `?sp_sw=1` — pages are network-first with a friendly
  offline fallback page; static assets are cache-first with
  auto-invalidation on plugin version bump
- `<head>` emits `rel="manifest"`, `theme-color`, and
  `apple-touch-icon` so sites can be installed to a phone home screen
- Non-GET requests and admin/login/AJAX/REST/cron paths are never
  intercepted

---

## Version history

For the release-by-release story of how these features came together,
see [`../CHANGELOG.md`](../CHANGELOG.md).
