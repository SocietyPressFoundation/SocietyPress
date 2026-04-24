# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

---

## [Unreleased]

### Added
- **Live recurrence preview on the event editor.** Picking a recurrence
  type now shows a plain-English summary ("Every 2nd Wednesday through
  December 31, 2026 — 7 occurrences"), a collapsible list of the
  actual dates, and a 3-month mini-calendar with the occurrence dates
  highlighted. Updates live as the event date, recurrence type, or
  repeat-until date change — no save-and-regenerate round-trip needed.

### Changed
- Recurrence dropdown labels reworded to match what the feature
  actually does ("Every month on the same weekday" rather than
  "Monthly (e.g., 2nd Saturday)"), since the specific weekday is
  derived from the event date, not picked separately. The preview
  carries the specificity.
- Date generation logic extracted into shared `sp_compute_recurrence_dates()`
  and `sp_build_recurrence_rule()` helpers so the save path, the
  regeneration path, and the live preview can't drift.

### Fixed
- Widget sanitizer and renderer no longer emit "Undefined array key"
  warnings when a setting is missing. The old pattern was
  `in_array( $settings['x'] ?? 'default', $whitelist, true ) ? $settings['x'] : 'default'`:
  the `??` protected the `in_array` check, but when the default was
  itself in the whitelist the check passed and the true branch then
  reached back into the array without a default, warning loudly. Fix
  is mechanical — use a sentinel default that isn't in the whitelist
  (`''` for strings, `-1` for ints) so a missing key routes to the
  safe else branch. Applied to all widget settings, the page-builder
  sanitizer, and a handful of settings-page sanitizers.

---

## [1.0.29] — 2026-04-24

### Fixed
- Parent-theme update notifications now announce the correct version.
  `sp_latest_parent_theme_version()` had been hardcoded at `1.0.4`,
  silently going stale as the plugin moved forward. It now reads from
  `SOCIETYPRESS_VERSION` so plugin and theme stay in lockstep.

---

## [1.0.28] — 2026-04-21

### Added
- Admin member-edit photo upload now shows a live preview the moment a
  file is chosen, with a "click Save Member to apply" notice — was
  previously silent.
- User-menu avatar in the site header — round member photo to the left
  of the user's name, resolving through SocietyPress profile photo →
  member record photo → Gravatar.

### Changed
- `/download/` offers two equal paths — the single-file installer
  (primary) and the full bundle ZIP — with installer downloads served
  with `Content-Disposition: attachment` and PHP parsing disabled for
  the directory.
- Installation, Setup, Requirements, FAQ, and ENS Migration pages
  consolidated under `/docs/`; old URLs 301-redirect to the new
  locations.
- `/docs/installation/` rewritten around the installer-first flow; the
  manual-install path is kept as a collapsible advanced fallback.
- Installer deploy now lands `sp-installer.php` in `/downloads/` with
  an `.htaccess` that forces download behavior.
- Marketing deploy now syncs `docs/ENS-MIGRATION-GUIDE.md` to the
  server so the live docs reflect the repo.
- Hosted-version possibility moved from "Deliberately Not Doing" to
  Someday/Maybe on the public roadmap.
- Parent theme header polish — user-menu link matches surrounding nav
  typography, visited-link color pinned so logout links stop rendering
  two-toned, `.header-inner` constrained to the content width, 1px
  bottom shadow for light-header configurations.

### Fixed
- Design settings now actually apply — the inline CSS override was
  attaching to a stylesheet handle that doesn't exist
  (`societypress-parent-style`), silently swallowing every color-picker
  change on every install. Now correctly attached to
  `societypress-style`.
- Custom login page no longer renders the organization name as
  literal `&amp;` — entities are decoded and then re-escaped
  context-appropriately (HTML for the header, CSS-string-safe for the
  `::before` pseudo-element).
- `sp_get_my_account_url()` now locates the My Account page by its
  canonical `sp-my-account` template slug, with a fallback for older
  installs.
- Front-end photo uploads on My Account now report specific error
  codes for each PHP upload failure mode instead of a silent redirect.
- Top navigation no longer overshoots the page wrapper — parent and
  Heritage content-area widths aligned with the header, My Account
  form capped at 860px, front-page builder widgets no longer squeezed
  by a reading-column rule that shouldn't apply to the home page.
- Heritage child theme bumped to 1.1.2 alongside the parent-theme
  fixes.

---

## [1.0.20] — 2026-04-19

### Added
- **Native store checkout** — cart page mounts the Stripe Payment
  Element (card, Apple Pay, Google Pay, Link) and PayPal Smart
  Buttons (PayPal, Venmo) inline. No redirect to a hosted checkout
  page unless the card issuer forces 3-D Secure.
- **Real refunds** — Stripe and PayPal refund buttons on the order
  detail page call the respective refund APIs.
- **Processor-not-configured admin notice** — persistent notice when
  the Store module is enabled but neither Stripe nor PayPal has
  credentials.

### Changed
- Shared `sp_store_create_pending_order()` and
  `sp_store_finalize_paid_order()` helpers so card and PayPal rails
  follow the same accounting path.

---

## [1.0.19] — 2026-04-15

### Added
- **SocietyPressFoundation public release** — repository moved to the
  `SocietyPressFoundation` GitHub organization; standard open-source
  project files added (`CODE_OF_CONDUCT.md`, `SECURITY.md`,
  `CONTRIBUTING.md`, `SUPPORT.md`, `CHANGELOG.md`, issue and
  pull-request templates, funding configuration); `ROADMAP.md`
  describing planned work grouped by theme; public documentation
  reorganized under `docs/` and internal planning files removed from
  the public tree; optional gitignored local-configuration pattern
  (`scripts/deploy.local.sh`, `scripts/build.local.sh`) so private
  testbeds can mirror the public deploy without committing
  site-specific details.
- **Meetings & Minutes module** — unified `sp_meetings` table covering Board,
  Membership, and Committee meetings with type/committee filters, attendance
  tracking, agenda/minutes URLs, inline notes, and three-level visibility
  (public, members-only, committee-only).
- **PWA support** — web app manifest served at `?sp_manifest=1` plus a
  service worker at `?sp_sw=1`. Sites can be installed to phone home
  screens. Static assets are cache-first with version-based invalidation;
  pages are network-first with a friendly offline fallback.
- **Full-site export** — one-click ZIP (`sp_*` SQL dump with decrypted
  member fields plus a restore README) at Settings → Export & Backup.
- **Committees as a first-class module** — dedicated `sp_committees` table,
  admin list with member counts, add/edit form; volunteer-opportunity
  forms now pull committee names correctly.
- **Voting & Elections** — ballots, admin pages (list, edit, results),
  four supporting tables.
- **GDPR coverage for donations** — exporter plus a pseudonymizing eraser
  that retains amount/date/campaign/payment data per IRS recordkeeping
  rules while clearing donor identifiers.
- **Translator-ready** — `societypress.pot` generated for both the plugin
  (253 KB) and the parent theme (15 KB), committed under each component's
  `languages/` directory.
- **Store products separated from library** — new `sp_store_products`
  table, dedicated admin, unified storefront, source-aware cart,
  stock decrement on checkout, sold-out display.
- **ENS migration guide** — a Harold-friendly walkthrough covering export,
  install, import, verification, and cutover.

### Changed
- Bundle de-branded — all site-specific references removed from the
  shippable plugin and theme. The demo bundle is now entirely generic.
- Installer security pass — DB password removed from session; auto-login
  moved to a time-limited transient with a 256-bit secret; zip-slip
  mitigations on WP and bundle extraction; per-constant regex validation
  on database host; download size capped; error output escaped.
- i18n sweep — child theme text domains unified to `societypress`;
  ~50 previously-bare strings wrapped in translation calls; status and
  type labels centralized via `sp_localized_status()`.
- Child themes 1.1.0 — palette applied on activation, widget areas added.
- Parent theme version synchronized to plugin version for simplicity.

### Fixed
- Events calendar rendering — standalone template now matches the widget
  (width: 100% applied to both paths).
- Child theme version constants — all five wrapped in `defined()` guards
  to avoid redefinition warnings.
- Orphaned theme folder cleanup in the local working copy.
- ~47 findings resolved from combined security, code quality, UX, and
  i18n audits.

---

[Unreleased]: https://github.com/SocietyPressFoundation/SocietyPress/compare/v1.0.28...HEAD
[1.0.28]: https://github.com/SocietyPressFoundation/SocietyPress/compare/v1.0.20...v1.0.28
[1.0.20]: https://github.com/SocietyPressFoundation/SocietyPress/compare/v1.0.19...v1.0.20
[1.0.19]: https://github.com/SocietyPressFoundation/SocietyPress/releases/tag/v1.0.19
