# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

Pre-1.0 development iterations are archived in
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

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
  member submissions, search, and `.genrecord` open-format import/export
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
- GDPR: five exporters and five erasers (donations pseudonymize for IRS
  recordkeeping rather than delete)

### Accessibility
- Custom `spConfirm`/`spAlert` modals with focus management, focus traps,
  inert siblings, and screen-reader-friendly labels
- WCAG AA color contrast across status badges, links, and admin tables
- Form labels, aria-live regions, and `prefers-reduced-motion` guards
- Login acknowledgment modal that requires explicit dismissal

### Internationalization
- 4,600+ translatable strings, generated `.pot` files for plugin and
  parent theme

### Operations
- One-file installer (`sp-installer.php`) — drop into web root, browse to
  it, follow the wizard, the installer self-deletes after success
- Built-in dashboard update checker against GitHub releases
- Daily maintenance cron, email-log cleanup, renewal reminder cron,
  event reminder cron, iCal feed sync cron

### License
- GPL-2.0-or-later

---
