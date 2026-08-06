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

**A Records page is created for you**

New sites now get a Records page, in the main menu alongside Library, showing
the searchable index of your transcribed records. Every other part of
SocietyPress that has a public page already got one automatically; records
did not, so a society could transcribe and import thousands of cemetery,
census or obituary entries and have nowhere for a visitor to reach them
short of building the page by hand. If you already have a site, add a page
and choose the **Genealogical Records Search** template.

**Folders for the media library**

Images, PDFs and documents are now sorted into folders — Newsletters, Photo
Galleries, Events, Documents and Site Design — instead of one long list.
Files are filed automatically as they arrive: importing newsletters puts
them in Newsletters, adding gallery photos puts them in Photo Galleries.
You can create your own folders and move files between them at any time,
and anything SocietyPress moves is never moved back out from under you.
A folder filter appears both in the media library and in the file picker.
Existing files are sorted on upgrade, so the folders are useful immediately
rather than after somebody sorts thousands of files by hand.

**Progress bars on every import**

Newsletter, records and bulk records imports now upload in small batches
with a progress bar, a running count, and a list naming any file that
failed. Previously a large upload could stop partway with nothing on screen
to say how far it got.

**Uploading more files than the server allows now says so**

Selecting more newsletters or CSVs than the server accepts in one go used to
discard the extras silently — pick forty newsletters, get twenty, with no
warning. The importers now upload in batches so there is no practical limit,
and the older non-JavaScript path says plainly when files were not received.

**Give someone access to only certain pages**

Content access used to be all or nothing. Under **User Access**, someone with
Content access can now be limited to specific pages: they see only those pages
in their list and cannot open the others. Administrators are never limited.

**List view for album photos**

The album editor has a **Thumbnails / List by name** toggle, so an album of a
hundred similar photographs can be scanned by filename instead of by sight.
Captions, reordering and removal work the same in both views.

**Sort a menu into alphabetical order**

Appearance → Menus has a **Sort A→Z** button. Items nested under another item
stay where they are and are sorted among themselves.

**Samples and previews for store items**

A store item can now link to a sample — a chapter PDF, a table of contents,
or a page on the site — shown as a **See a sample** link on the store page,
so a buyer can look before paying.

**Tidying the audit log**

The Audit Log screen now shows how many entries it holds, offers to delete
entries older than a period you choose, and lets you set how long entries are
kept from then on. Previously entries were kept for a year with no way to
change it.

**The shared notepad is open to everyone with backend access**

The notepad is a handoff board — what was left half-done, what the next
person needs to know — so it is now available to anyone who can edit content,
not only to people holding a SocietyPress access area. Members cannot see it.

**Hiding a document category**

A document category can be hidden from visitors without deleting it. Its
documents stay in place and it keeps working in the admin; it simply stops
appearing on the website.

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

**Editor screens filled the debug log with warnings**

If your site has WordPress debugging switched on, every visit to an editor
screen — adding a member, editing an event, writing up a document — wrote a
"Deprecated" warning into `debug.log`. Nothing was broken and nothing appeared
on screen, but over a working day the file filled with hundreds of identical
lines and buried anything that genuinely needed attention. The editor screens
are deliberately kept out of the sidebar, and WordPress was asking them for a
page title they had no way to give it.

**Apostrophes and quotation marks collected stray backslashes**

Anything typed with an apostrophe or a quotation mark came back with a
backslash in front of it — `Beginner's` saved as `Beginner\'s` — and
removing the backslash by hand didn't help, because saving the form put it
straight back. This affected event titles and descriptions, member names and
notes, research surnames, library and resource entries, document titles,
ballot questions, page-builder text, and the sign-up form when it redisplayed
what a visitor had typed.

Entries already stored with a stray backslash are cleaned up for you. The
repair runs by itself the next few times you visit the admin, works through
your existing entries a little at a time so nothing slows down, and notes in
the audit log how many it corrected. Genuine backslashes — a file path in a
document note, for instance — are left alone.

**Imported GENRECORD files lost their record type**

A GENRECORD file that named its type the way the published format guide
does — `Cemetery`, `Marriage`, `Military` and most of the rest — imported as
an untyped general collection. Ten of the fourteen documented types were
affected; only Census, Court, Land and Tax happened to work. The record type
decides how a collection is labelled and grouped, so a shared cemetery index
arrived looking like a miscellaneous pile. Both the spelled-out names and the
short codes are now recognised, and existing collections can be corrected by
editing the collection and choosing the right type.

**Event categories could not be added, renamed or deleted**

The Events → Categories screen looked complete but did nothing: adding a
category, renaming one, changing its colour and deleting one all silently
failed, on every site. Every society was limited to the categories created
when SocietyPress was installed. All of it now works. Deleting a category
leaves its events in place and simply uncategorises them.

**Colouring a table's header row appeared to do nothing**

Using Table → Row background color on a header row had no visible effect,
because the header's own grey covered it. Body rows were unaffected, which
made the feature look unreliable rather than broken.

**Sorting the page list by title appeared to do nothing**

The Pages list showed its Title column as already sorted A→Z when it was not,
so the first click sorted Z→A and looked like nothing had happened.

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
