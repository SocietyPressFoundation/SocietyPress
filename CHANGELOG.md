# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

Pre-1.0 development iterations are archived in
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

---

## [Unreleased]

### Added

**Draft review links**

Any page can now be shared for review before it goes live. Save the page as
a draft and use the new **Review link** on the page editor: it's a private
link anyone can open — no account needed — to see the page exactly as it
will look, with a Preview button and a one-click Copy. The link only ever
unlocks that one draft, works even while the site is set to require login,
and can be turned off at any time by generating a new one. Publishing the
page makes it public in the normal way.

**Description and directions above the Events list**

The Events page now shows whatever you type into its page editor above the
list of events, so you can add a welcome, meeting location, or directions —
matching the other SocietyPress page templates.

### Fixed

**Dates and times displayed in the site's timezone instead of as entered**

Event times, meeting dates, newsletter publication dates, membership
expiration dates, document dates, and ballot voting windows are all stored
as plain wall-clock values — 3:00 PM means 3:00 PM, with no timezone
attached. Every screen that displayed them was converting them as though
they were UTC, shifting each one by the site's offset.

On a US Central site that meant:

- A 3:00 PM–5:30 PM event listed as 10:00 AM–12:30 PM
- Any date on the first of a month displayed as the previous month, and any
  date in January displayed as the previous year — a newsletter published
  2016-01-01 read "December 2015"
- Membership expiration dates displayed a day early, including the
  `{{expiration_date}}` token in renewal emails
- Ballot edit screens reloaded the shifted time into their own fields, so
  each re-save moved the voting window further back

The stored data was correct throughout; only the display was wrong, so no
repair of existing records is needed. Voting-window comparisons were already
consistent and were not affected.

**Buttons that appeared to do nothing**

Any admin action whose confirmation dialog re-sent the form lost the button
that triggered it, so the request arrived with no action and the page simply
reloaded unchanged. This affected **Forms → Delete** and **Picture Wall →
Reject**. Confirmation dialogs now also restore the browser validation that
had been skipped.

**Photo album imports silently stopped at 20 images**

Selecting more than 20 images imported only the first 20, with no error and
no indication anything was missing — PHP discards the surplus before the
upload reaches the plugin. Images now upload in small batches with a
progress bar and a per-image error list, so album size is limited only by
available storage.

**Page content ignored on SocietyPress page templates**

Pages using the Research Help, Resources, Library Catalog, Records, Store,
Cart, or Documents templates discarded anything typed into the page editor.
A shortcode placed on such a page never rendered, and there was no way to
add a line of explanation above the listing. Page content now appears above
the module.

**Other fixes**

- The Newsletter Archive page-builder widget showed "No newsletters
  published yet" on sites with a full archive; it was reading a location
  newsletters are no longer stored in.
- Reordering membership plans saved correctly but left the row in place, so
  the change was invisible until the page was reloaded.
- Blank lines added in the page builder's Rich Text block showed in the
  editor but not on the published page.
- Several buttons and time ranges displayed raw escape codes instead of an
  ellipsis or dash.

---

## [1.0.1] — 2026-06-27

A large maintenance-and-feature release on top of the first public 1.0:
bulk importers across every module, online membership dues, deeper
per-society configurability, and a sustained security, accessibility, and
performance pass.

### Added

**Importers**
- ENS Page Maintenance importer (Settings → Import ENS Pages)
- Newsletters bulk importer — multi-PDF upload with automatic cover
  generation and metadata
- Library catalog importer with column-variant auto-detection
- Events importer supporting recurrence, speakers, and slots
- Gallery importer — file upload or URL fetch into a new album
- Bulk records importer — N CSVs become N collections in one pass
- CSV export for record collections

**Membership & dues**
- Online membership dues and multi-year renewal via Stripe
- Escalating renewal banner on the My Account page
- Per-section member self-edit locks
- Society-configurable new-member email-preference defaults
- Admin-managed name prefix/suffix dropdowns
- Print / PDF view for the admin Members list

**Records & genealogy**
- GEDCOM 7.0 export
- Per-surname alternate-spelling lists, used in surname search
- A–Z browse mode for the surname widget
- Researched-surname collection on the join form
- Public (non-member) surname contact on public registries
- Split death-notice vs obituary fields in the obituary record template

**Store**
- Member vs non-member pricing, with "Apply member pricing" on order detail
- Flat-rate shipping option and a complimentary (comp) order action
- Mail-in check donation path for `[sp_donate]`
- Search + date filters and inline sort-order editing in the orders and
  products admin

**Blast email**
- File attachments on blasts
- Status-based audience segments and per-tier audience narrowing
- Per-blast sender name / From-address override
- Clone action, opt-out override for critical notices, and audience
  name + count shown in the send confirmation
- One-click member email health scan

**Documents & resources**
- Members-only access at the document-category level
- Recency filter, per-category "last updated", and Month/Year display
  format for documents
- Drag-to-reorder and optional last-updated date for Resource Links;
  grid layout option on the Resource Links widget

**Events, donations & governance**
- Per-event file attachments and an "Email this event" row action
- Admin-configurable default range for the public Events page
- All-source income summary on the Finances page
- Society notification when a donation arrives; donation search by
  processor transaction ID; Year filter on the donations admin
- Chair dashboard widget and a URL shortener
- Contact form routing to a specific officer or committee chair, with an
  optional preset subject
- Theme exchange Tier 2 — `.spchildtheme` bundle import; Playfair Display
  as a selectable font; a saved-look preset library

### Changed
- Single source of truth for the genealogy service list
- Early renewals stack onto remaining days in rolling mode
- Duplicating an event resets its date to today instead of inheriting the
  original
- Pending/failed donations excluded from campaign raised totals
- Removed HostGator from recommended hosting; refreshed installer
  diagnostics with errno-aware failure messages

### Security
- Hardened the backup feature (capability, path, headers) and added a
  scheduled-backup admin UI with secure download and nightly email
- Closed a ballot double-vote race with an atomic participation gate
- Nonce ordering on refund/help handlers; tightened Reply-To and
  ownership checks
- `ENT_QUOTES`/UTF-8 flags on installer `htmlspecialchars` calls
- Post-rebaseline audit pass over the new importers

### Performance
- Bounded export memory and replaced whole-membership dropdowns
- Streamed GEDCOM export in batches
- Eliminated cold-load and ballot N+1 query overhead

### Accessibility
- `focus-visible` styles and color-contrast fixes
- `aria-describedby` field hints; modal `inert` and `aria-describedby`
- Clearer library catalog tab and calendar-cell semantics
- Confirm-dialog buttons labeled with the action verb; save confirmations
  brought into view and focused

### Fixed
- Frozen password-reset dialog on WordPress 7.0
- Login-acknowledgment modal that could re-trap members
- Theme builder modal that never opened, and numerous unclosed
  `spConfirm()` handlers across the admin
- Setup wizard blank page on Continue, and stray admin notices on the
  wizard screen
- My Account fatal from an unbuilt feature; preset import dropping valid
  fonts via a stale allowlist
- Dead Documents frontend; GENRECORD parser brought into spec conformance

---

## [1.0.0] — 2026-06-01

First public release.

SocietyPress is a free, GPL-licensed WordPress platform that gives a
volunteer-run genealogical or historical society everything it needs to
operate online — members, dues, events, library, newsletters, records,
volunteers, donations, blast email, committees, documents, photos, voting,
lineage programs, and research help — without third-party services.

### What's in 1.0

**Members** (always on)
- Full membership database with custom fields, household/joint members,
  research surnames, research areas, and skills/interests
- Renewal reminders, lapsed-member workflows, lifetime members
- Pending-changes queue so member-initiated edits land in moderation
- CSV import with field mapping; full data export
- XChaCha20-Poly1305 encryption (via libsodium) on phone numbers and
  street addresses at rest

**Optional modules** (toggle in Settings → Modules)
- **Events** — calendar, registration with capacity + waitlist, speakers,
  slot-based events, iCal subscribe, Stripe/PayPal payments, attendance
  check-in, recurring series, external iCal feed sync
- **Library** — full OPAC-style catalog with faceted search, browse-by
  sections, ISBN enrichment, donor tracking, condition + availability
- **Newsletters** — archive with searchable cover gallery and inline PDF
  viewer
- **Resource Links** — categorized external links with maintenance tools
- **Governance** — committees, meeting records, leadership roster
- **Store** — online publication sales with per-item shipping, Stripe
  and PayPal checkout, order management
- **Records** — collection-based historical record sets with custom fields,
  member submissions, search, `.genrecord` open-format import/export, and
  GEDCOM import (5.5/5.5.1/7.0) and export (5.5.1 or 7.0)
- **Donations** — one-time and recurring donations, campaigns,
  acknowledgments, year-end reports
- **Blast Email** — newsletter blasts, segmented sends, plain-text or
  templated HTML, delivery log
- **Photo Gallery** — albums with member uploads, Picture Wall public view
- **Help Requests** — public-facing research-question intake with member
  responses and admin moderation
- **Documents** — gated members-only document library with categories
- **Voting & Elections** — multi-question ballots, eligibility rules,
  anonymous secrecy, results with charts and CSV export
- **Lineage Programs** — "First Families"-style heritage recognition with
  application review and optional public roster
- **Research Services** — paid research request workflow with assignment,
  hours tracking, invoicing

### Themes
- Parent theme + five GPL child themes (Coastline, Heritage, Ledger,
  Parlor, Prairie), each with palette-on-activation and per-section
  Customizer controls

### Site building
- 21+ page-builder widgets covering hero sliders, contact cards, event
  lists, calendars, donation forms, member directories, library catalogs,
  research guides, and more
- Page templates for members-only content, search, and login modal
- PWA manifest with offline fallback page

### Security
- All AJAX endpoints nonce + capability gated
- Stripe + PayPal webhook signature verification
- Server-side outbound HTTP for iCal sync and color extraction is
  DNS-rebinding pinned (CURLOPT_RESOLVE)
- File uploads validated against an explicit MIME allowlist; `.htaccess`
  drops PHP execution in upload paths
- Email header CR/LF stripping in `sp_get_email_headers()` so a stray
  newline in admin-edited From/Reply-To settings cannot inject Bcc/Cc
- Legacy `noenc:` decryption fallback removed; on activation any surviving
  rows are re-encrypted via `sp_maybe_migrate_noenc_members()`
- Stripe Checkout redirect URLs validated via `sp_safe_stripe_checkout_url()`
  at every checkout site (lineage applications, donations, research cases,
  invoice payments)
- GDPR: five exporters and five erasers (donations pseudonymize for IRS
  recordkeeping rather than delete)

### Accessibility
- Custom `spConfirm`/`spAlert` modals with focus management, focus traps,
  inert siblings, and screen-reader-friendly labels
- WCAG AA color contrast across status badges, links, and admin tables
- Form labels, aria-live regions, and `prefers-reduced-motion` guards
- Login acknowledgment modal that requires explicit dismissal
- Filter-bar `<select>` controls in the Members, Events, and Email Log
  list tables now expose visible-label-equivalent `aria-label` text
- Page builder widget cards are keyboard-operable (Enter / Space toggles
  open/closed; `aria-expanded` tracks the body's visible state)
- 48 page-builder widget fields now have programmatically associated
  labels (`for=`/`id=`) across flat and repeater field patterns
- Hero slider per-line size/weight/color selects and per-slide Button
  Text / Button URL inputs each have their own labels
- Theme builder hex-color text inputs are labeled per color
- Lineage application form fields are fully label-associated
- Donations bulk-action "select all" checkbox has an associated label

### Internationalization
- 4,600+ translatable strings, generated `.pot` files for plugin and
  parent theme
- Page template names ("Library Catalog", "Genealogical Records Search",
  "Store", "Shopping Cart", "Documents", "Interest Groups") now
  translatable in the WordPress page Template dropdown
- Frontend event-count plural moved to `_n()` ("1 event" / "%s events")
- Frontend event location labels for virtual events translated
- Design Settings, Event Categories, Event price fields, Page Builder
  "Standard Pages" description, and Events Import file-format helper
  strings wrapped with the `societypress` text domain

### Operations
- One-file installer (`sp-installer.php`) — drop into web root, browse to
  it, follow the wizard, the installer self-deletes after success
- Built-in dashboard update checker against GitHub releases
- Daily maintenance cron, email-log cleanup, renewal reminder cron,
  event reminder cron, iCal feed sync cron

### License
- GPL-2.0-or-later

---
