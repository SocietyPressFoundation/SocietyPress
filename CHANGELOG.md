# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

---

## [Unreleased]

### Changed
- Repository moved to the `SocietyPressFoundation` GitHub organization.
- Public documentation reorganized under `docs/`; internal planning files
  removed from the public tree.

### Added
- Standard open-source project files: `CODE_OF_CONDUCT.md`, `SECURITY.md`,
  `CONTRIBUTING.md`, `SUPPORT.md`, `CHANGELOG.md`, issue and pull-request
  templates, funding configuration.
- `ROADMAP.md` describing planned work grouped by theme.
- Optional gitignored local-configuration pattern for deploy and build
  scripts (`scripts/deploy.local.sh`, `scripts/build.local.sh`) so private
  testbeds can mirror the public deploy without committing site-specific
  details.

---

## [1.0.19] — 2026-04-15

### Added
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

[Unreleased]: https://github.com/SocietyPressFoundation/SocietyPress/compare/v1.0.19...HEAD
[1.0.19]: https://github.com/SocietyPressFoundation/SocietyPress/releases/tag/v1.0.19
