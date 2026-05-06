# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

---

## [Unreleased]

---

## [1.0.56] — 2026-05-06

### Changed
- **Admin i18n long tail.** ~150 more admin-side strings wrapped:
  Hero Slider widget admin (slide/line/size/weight/color labels and
  placeholders), all `wp.media()` picker titles and buttons, Events
  admin (page titles, table headers, registration form, slot tooltip,
  role options, Update/Create button), event categories and tiers
  tables, Speakers admin (page title, photo button, submit), Volunteers
  placeholders, Library admin (search, item form, Update/Add Item),
  newsletters search, Records guides el() builder, library enrich tool
  log messages, blast email log table cells/headers, member stats
  labels, breadcrumb aria-label, member-edit error transients,
  generate-occurrences messages, event timezone test messages,
  Stripe setup steps with placeholder substitutions, design settings
  descriptions, import preview heading and submit buttons, Found-N-events
  message, Expected Format heading. ~33 row-level CSV import error
  messages converted to `sprintf( __() )` with translator comments.
- **Inline-style hot-spot extraction.** "Feature Not Available" page,
  Members-only gate, bulk-delete admin overlay, and "Coming Soon"
  module placeholder all extracted to scoped CSS classes
  (`.sp-module-not-available__*`, `.sp-members-only-gate__*`,
  `.sp-delete-overlay__*`, `.sp-coming-soon-*`). Recurring postbox
  handle padding (16 sites in lineage + research-case admin)
  consolidated into a single `.sp-hndle-padded` utility class.
  Front-page hero dynamic background-image and overlay opacity
  moved to CSS custom properties on the section root. Marketing
  showcase + theme-gallery color swatches documented as intentional
  inline-style exceptions for per-row dynamic values.
- **Login Acknowledgment modal text is now admin-editable.** New
  Settings → Privacy → Login Notices section with two textarea
  fields (`login_pre_notice`, `login_ack_text`). Defaults preserved;
  every society should review and adapt the wording.
- **Function clarity.** `sp_render_member_edit_page()` data-prep
  extracted into `sp_member_edit_load_context()` returning an
  associative array. `sp_create_tables()` annotated with the
  explicit reason it's one function (every body is a `dbDelta()`,
  no migration logic mixed in). Adds a hook for adding a future
  `sp_run_upgrade_migrations()` helper if upgrade-path branches
  ever need to live separately.

### Fixed
- Marketing site header brand link `aria-label` now derives from
  `get_bloginfo( 'name' )` instead of hardcoded "SocietyPress home".
- 404 search input focus shadow opacity bumped from 15% to 50% so
  it actually meets WCAG 1.4.11 (non-text contrast).
- Pagination wrapper inline padding extracted to a CSS class.

### Performance
- Events frontend CSS (~1,082 lines) extracted from inline output to
  `Code/plugin/assets/css/events-frontend.css`. The browser caches
  it across page loads instead of re-parsing it on every events-page
  request. Plugin file dropped ~1,100 lines.

---

## [1.0.55] — 2026-05-06

### Security
- **Stripe webhook now rejects payloads when no signing secret is
  configured.** Previously the handler accepted any unauthenticated
  POST as legitimate when `stripe_webhook_secret` was missing — an
  attacker could mark donations paid, provision membership renewals,
  or trigger any other webhook-driven state change with zero credentials.
  Now returns 401 if the secret isn't configured. Use the Stripe
  Dashboard's sandbox signing secret during development.
- **Membership-manager role assignment is allowlisted.** Delegates
  with `sp_manage_members` (e.g., `membership_manager` template) can
  no longer escalate users to WordPress administrator via the member
  edit form. Only users with `manage_options` can assign elevated
  WordPress roles; everyone else can only assign safe member-tier roles.
- **ORDER BY columns allowlisted in 4 admin tables.** Payments report,
  volunteer roster, volunteer hours, and ballots now restrict
  `?orderby=` to known-safe columns. `sanitize_sql_orderby()` alone
  permitted column enumeration.
- libsodium fallback now `wp_die()`s loudly instead of silently
  base64-encoding plaintext if the extension is somehow missing.
  Backward-compatibility decrypt path for legacy `noenc:` data
  retained.
- Three minor unescaped-output sites fixed (`sort_order`, `user_id`).
- `@file_put_contents` suppressor on the template-shim creator
  removed; failures now surface to `error_log` instead of breaking
  silently.

### Added
- **Admin-editable login notices** — new Settings → Privacy → Login
  Notices section with two textarea fields. The acknowledgment
  modal that appears after every successful login, and the privacy
  notice above the login form, are now configurable per society.
  Sensible defaults ship with the plugin.

### Accessibility
- ARIA dialog semantics on three modals: Login Acknowledgment
  (`role="dialog"`, `aria-modal="true"`, `aria-labelledby`), Theme
  Builder, and the JS-built Member Detail modal. Focus is now
  managed and returned to the triggering element on close.
- `aria-live` regions on four AJAX status containers so screen
  readers announce progress: dashboard update status, bulk-delete
  progress, import overlay, event registration messages.
- Visible focus indicators replace `outline:none` at 8 sites
  (member directory search/filter, events search, registration
  form, library catalog search, marketing site 404 search,
  admin sidebar group headers).
- `spConfirm()` captures `document.activeElement` on open and
  restores focus on close — keyboard users no longer lose their
  place after canceling a destructive action.
- Color contrast: ~70 sites of `#999`/`#888`/`#777` text on white
  bumped to `#767676` (4.54:1, WCAG AA).
- Hardcoded 11px font sizes converted to `0.75rem` so small UI
  labels scale with the user's base font size preference.
- Contact form fields now have `id`/`for` label associations
  (Name, Email, Message).
- Admin list tables auto-wrapped in `.sp-table-scroll` containers
  on SocietyPress admin pages so they scroll horizontally below
  ~900px instead of clipping.
- Bulk-delete overlay countdown promoted to `aria-live="assertive"`.

### Changed
- WordPress jargon removed from admin UI: member edit username
  field, Export & Backup page (FTP / cPanel guidance softened),
  User Access page ("Site administrators" replaces "WordPress
  administrators").
- Shortcode syntax removed from admin help text on Database
  Subscriptions, Research Guides, Research Cases. Picture Wall
  configuration error gated to admins only — anonymous visitors
  see a generic "Album not found" message.
- `confirm()` dialogs replaced with `spConfirm()` in 6 places
  (Help Request resolve/accept, refund button, Help-to-Case
  conversion, Account-page surname/research/event forms).
- "Delete?" terse confirmation messages replaced with full
  contextual sentences ("Delete this request and all responses?
  This cannot be undone.").
- Empty states for library catalog, records browser, and tier
  preview now give users a next step.
- Class definitions (`SP_*_List_Table`, `SP_Society_Sidebar_Widget`)
  now have an explicit "convention exemption" docblock noting they
  exist solely because WordPress core APIs require class inheritance
  and that no other classes should be introduced.

### Performance
- Insights dashboard: `sp_insights_bucket_counts()` rewritten to
  pull window data once with a single SQL fetch and bucket in PHP,
  replacing 13 queries per panel (1 total + 12 per-bucket COUNTs).
  With ~13 enabled-module panels that previously fired ~180 queries
  per pageview. Results cached in a 5-minute transient.
- Event Categories admin and Membership Plans admin: row-level
  N+1 `COUNT(*)` queries replaced with single `GROUP BY` lookups
  built before the render loop.

### i18n
- ~150 member-facing strings wrapped: all event email notifications
  (waitlist, confirmation, promotion, cancellation, update, reminder)
  with translator comments and `_n()` plural forms; join-form error
  messages; Research Help notices, email subjects, and form
  placeholders; Library catalog headings and search placeholder;
  Newsletter empty state; Groups front-end (Join/Leave buttons,
  status messages, Leader/member labels); PayPal donate JS error
  messages; Event Not Found page; Surname Contact modal; member-edit
  error transients; address-form JS state/postal labels.

---

## [1.0.54] — 2026-05-05

### Added
- **Custom CSS field on the Design settings page.** Optional textarea
  under SocietyPress → Settings → Design where webmasters can drop
  in their own CSS rules. Output is appended after every other
  stylesheet on the public-facing site so user CSS overrides
  anything above. Sanitized on save and again on output:
  `<style>`-block escapes, HTML tags, `expression(...)`,
  `behavior:`, `javascript:` URLs, `@import` statements, and
  HTML-comment markers are all stripped. Foundation for future
  Theme Exchange Tier 2 (themed bundles).

---

## [1.0.53] — 2026-05-05

### Added
- **Classic `WP_Widget` registration for the Society Sidebar.** The
  auto-assembled member-portal navigation is now droppable into any
  registered widget area via Appearance → Widgets, with a title field
  and the same three options (icons, login link, current-page
  highlight) as the existing shortcode and page-builder widget. The
  shortcode `[sp_society_sidebar]` and the page-builder widget
  continue to work unchanged.

---

## [1.0.52] — 2026-05-05

### Added
- **Help Requests Tags admin — usability polish.** Click any tag in
  the usage list to load it into the Source field; the Delete action
  now triggers the standard `spConfirm()` modal so a single misclick
  can't strip a tag from every thread that uses it.

### Fixed
- **Help Requests Tags rename/merge — empty destination.** Submitting
  Rename or Merge with an empty Destination field used to silently
  strip the source tag (the empty replacement was filtered out by
  `array_filter`, equivalent to a Delete). The handler now blocks
  the action with an inline error message.

---

## [1.0.51] — 2026-05-05

### Added
- **Help Requests email lifecycle — full coverage.**
  `sp_help_send_status_email()` now handles the `approved`
  (pending_review → open) and `reopened` events in addition to the
  existing `resolved` and `closed` events. The reopen action on the
  admin page emails the asker; bulk Approve and bulk Mark Resolved
  emit one email per row that actually transitions (a two-stage hook
  snapshots eligible IDs at priority 5 and dispatches at priority 20
  so no-op rows aren't emailed). Hidden and Deleted bulk actions
  remain silent — they're moderation actions, not asker-facing
  transitions.

---

## [1.0.50] — 2026-05-03

### Added
- **`sp_insights_panels` filter.** Child themes and add-ons can append
  custom stat cards to the Insights page. Each panel supplies a name,
  icon, and a callback that receives the resolved time window and
  returns label/value/value_kind/sparkline. Documented in
  `developer-reference.md`.

---

## [1.0.49] — 2026-05-03

### Security
- **`sp_backup_export_table` now whitelists table names.** The function only
  ever ran on the output of `sp_backup_get_tables()` — but because the
  argument flowed into raw SQL via backticks (table identifiers can't be
  parameterized through `$wpdb->prepare()`), a future caller sourcing the
  argument from outside that helper would have reached the queries
  unchecked. The function now refuses anything that isn't an SP table or
  a WordPress user table, and confirms the table exists via parameterized
  `SHOW TABLES LIKE` before any other SQL runs. Closes the last item from
  the security audit's low-risk tier.

---

## [1.0.48] — 2026-05-03

### Added
- **Insights — engagement & use metrics.** A single admin/board-only page
  (SocietyPress → Insights) that pulls one headline number per enabled
  module across a chosen time window. Active members, events held,
  catalog items added, issues published, resources added, volunteer
  hours, orders placed, records added, total raised, blasts sent,
  photos uploaded, research help requests, documents uploaded, ballots
  opened, lineage applications, research cases — all on one screen.
  Sparkline trend on every card. Time-window dropdown supports last
  30 / 90 / 365 days, this fiscal year, and last fiscal year (the
  fiscal-year boundary reuses the existing membership-start-month
  setting, so societies don't configure it twice). Disabled modules
  are hidden, not greyed out. Permission gate uses the existing
  `sp_view_reports` capability, so a board treasurer or membership
  chair can be granted access without giving them broader admin rights.

### Documentation
- New end-user guide for Insights at `/docs/modules/?guide=insights`,
  plus the README index entry.

---

## [1.0.47] — 2026-04-27

### Fixed
- **Event detail page no longer logs an undefined-variable warning** for
  `$show_pay_btn` when a guest registration form is rendered for an event
  with no fee. The payment-button visibility flags are now computed once,
  near the top of the event template, so every render branch has them
  defined whether the buttons are shown or not.

### Added
- **Society Sidebar widget.** Auto-assembled member-portal nav from enabled
  modules — ENS-style left rail without the manual menu-builder work. Drop
  the "Society Sidebar" widget into any page-builder column or use the
  `[sp_society_sidebar]` shortcode. Items are filtered by which modules are
  enabled (no Library entry if Library is off) and by login state (My
  Account is hidden for visitors). Filter `sp_society_sidebar_items` lets
  child themes add, rename, or remove entries.
- **Theme Exchange — Tier 1 (preset export/import).** Societies can package
  their site's design tokens (palette, fonts, spacing, layout) as a small
  JSON preset and share it with other societies. Admin page at SocietyPress
  → Theme Presets handles export and import; tokens are sanitized through
  the existing design-page validators on import. Companion Theme Gallery at
  `getsocietypress.org/themes/` ships five curated starter presets (one per
  child theme).
- **Help Requests upgrade — comradery model.** The module pivots from
  member-only Q&A to a public "duty librarian" forum:
  - Public submission via `[sp_help_request_submit]` with math captcha,
    email verification, and per-email/IP rate limiting.
  - Time-entry on every response — picker captures minutes spent,
    auto-writes a row to `sp_volunteer_hours` with `source_type='help_request'`.
  - Endorse-helpful (★) on responses with toggle-on/off per member.
  - Mark-resolved + Accept-as-answer (asker or staff). Accepted answers
    get a green left-border + "ACCEPTED ANSWER" badge.
  - Public archive via `[sp_help_requests_archive]` with tag-filter pills
    (top 12 tags surfaced as click-to-filter chips).
  - Admin bulk actions on the queue: approve / mark-resolved / hide / delete.
  - Tags taxonomy admin at SocietyPress → Help Request Tags for renaming /
    merging / deleting accumulated free-text tags.
  - Status-change emails to the asker on close + resolved (the new-response
    email was already wired).
  - Admin notice on the queue page surfaces pending-verification +
    pending-review counts so moderators see incoming work.
- **Paid Research Services module (opt-in).** Companion to Help Requests for
  the rare extensive case that warrants paid work:
  - Public intake via `[sp_research_request]` — visitor describes the case,
    Stripe Checkout for the up-front fee (rate × max_hours_authorized).
  - Admin queue at SocietyPress → Research Cases with status filter, search,
    bulk-friendly list view.
  - Single-case admin with status workflow (pending_payment / open / claimed
    / in_progress / needs_more_hours / completed / cancelled / refunded),
    researcher assignment, rate + SLA + max-hours overrides, internal
    admin notes, hours-logging ledger, invoices list.
  - Hours flow into the unified `sp_volunteer_hours` ledger with
    `source_type='research_case'`.
  - Researcher dashboard via `[sp_my_research_assignments]`: cards for
    active cases (with inline log-hours form) plus open cases anyone can
    claim with a one-click "I can take this case" button.
  - Member-facing my-cases via `[sp_my_research_cases]` with pending-
    invoice authorize-and-pay buttons.
  - Additional-hours billing flow: researcher requests extra hours, system
    creates a pending invoice, requester gets a payment-link email, paying
    bumps the case's authorized hours and returns it to in_progress.
  - In-system case messaging with file attachments + email notifications
    to the other party.
  - Convert-from-Help-Request flow: staff promote a free thread into a
    paid case, original conversation preserved as the source.
  - Status-change emails on every transition (claimed, in_progress,
    needs_more_hours, completed, cancelled, refunded).
- **Volunteer-hours source linking.** New `source_type` + `source_id` columns
  on `sp_volunteer_hours` so every helping action traces back to its source
  (help_request / research_case / committee / event / meeting / library_duty
  / other). Member volunteer-hours summary widget (`[sp_my_volunteer_hours]`)
  groups by source with per-source counts.
- **Lineage Programs module (First Families, Pioneer Settlers, etc.).** New
  toggleable module for societies that recognize members documenting descent
  from historically significant ancestors. Each program defines its own
  cutoff year, geographic scope, requirements, and optional application fee.
  Members apply through a public form (`[sp_lineage_apply]`), staff review
  in an admin queue with status workflow, approved members appear on a
  public roster (`[sp_lineage_roster]`), and each approval generates a
  unique certificate number plus a printable certificate page at
  `/?sp_certificate=NNN`. Status changes auto-email the applicant. Paid
  programs route the submitter through Stripe Checkout. Page-builder
  widgets and full GDPR exporters/erasers included.
- **Picture Wall (member-submitted ancestor portraits).** New gallery type
  extending the existing Photo Albums module. Members upload an ancestor
  photo plus name, relationship, and optional caption via
  `[sp_picture_wall_submit]`; staff approve in a moderation inbox at
  SocietyPress → Picture Wall Pending; approved photos display via
  `[sp_picture_wall]` with submitter credits. Email notifications to staff
  on submission and to the submitter on approval.
- **Public Donation form (Stripe + PayPal).** New `[sp_donate]` shortcode
  delivers a complete online giving form: preset amounts, custom amount,
  one-time / monthly / annual frequency, "cover the processing fee"
  toggle, anonymous donations, in-honor-of dedications, optional message,
  and auto-fill from the logged-in user. Stripe Checkout handles all three
  frequencies (one-time + monthly + annual) end to end, with a
  signature-verified webhook at
  `/wp-json/societypress/v1/webhooks/stripe` that handles
  `checkout.session.completed`, `invoice.paid` (auto-creates donation rows
  for renewals), and `customer.subscription.deleted`. Receipt emails fire
  immediately on success and include 501(c)(3) language when the EIN is
  configured. PayPal Smart Buttons handle one-time donations; PayPal
  recurring is a follow-up.
- **Surname Research Database backfill.** The existing `sp_member_surnames`
  table and `surname_lookup` page-builder widget were verified end-to-end
  for parity with the EasyNetSites Surname Inquiry feature.
- **Database Subscriptions panel.** New admin page at SocietyPress →
  Database Subscriptions to manage genealogy databases the society pays
  for (Ancestry, Fold3, FamilySearch affiliate, NEHGS, etc.). Display
  via `[sp_database_subscriptions]` shortcode or page-builder widget,
  with optional members-only access control per entry.
- **Research Guides authoring.** New admin page at SocietyPress → Research
  Guides for building structured resource guides (e.g., "Researching Sample
  County" with sections for births, marriages, cemeteries, etc., each with
  local + external resource links). JS-driven category/resource editor.
  Display via the new "Research Guide" page-builder widget keyed by guide
  slug.
- **Per-item shipping on the store.** New `shipping_fee` columns on
  `sp_store_products` and `sp_library_items`, plus `shipping_total` on
  `sp_orders`. Admin forms for both inventory tables now expose the
  shipping fee field. Cart totals show a Subtotal / Shipping / Total
  breakdown when shipping > 0. Stripe and PayPal cart checkouts both
  charge the shipping-inclusive total.
- **Subscription membership tier seeded by default.** Fresh installs now
  receive a sixth default tier ("Subscription") for societies that offer
  newsletter-only non-voting memberships. Voting eligibility is enforced
  per ballot via `eligible_tiers` JSON, so excluding the Subscription
  tier from bylaw votes is a configuration choice (no code change
  needed). Existing installs are not affected.
- **GDPR exporters and erasers for Lineage applications.** Hooked into
  WordPress's privacy-tools framework alongside members, donations,
  registrations, etc. Erasure pseudonymizes (keeps the lineage record
  for organizational integrity, scrubs applicant link and narrative).
- **Username display on the member edit page.** A read-only "Username"
  field now sits at the top of the Contact Information section so admins
  can see the WordPress login name without leaving SocietyPress. Useful
  for password resets, login troubleshooting, and identity verification
  over the phone. Display-only — WordPress doesn't allow usernames to
  be changed once an account is created.
- **Committee chair dashboard.** Anyone set as `chair_user_id` on an
  active committee now sees a "My Committee" item in the SocietyPress
  menu, landing on a scoped view of their committee(s): upcoming
  meetings, upcoming events, open volunteer opportunities, and recent
  minutes, with one-click links to create or edit each. Chair-ness is
  derived live from the committees table — no role assignment step to
  forget. A chair can chair multiple committees; the view aggregates
  across all of them. Admins still see the full dashboard, unchanged.
- **Events can be tagged to a committee.** The event editor has a new
  optional "Committee" dropdown; setting it lets chairs manage the
  event and unlocks a "Committee Members Only" visibility option that
  hides the event from everyone except members of the tagged committee.
  Same visibility rule already used for committee meetings.
- **Auto-scoped admin list pages.** When a committee chair (not a full
  admin) opens the Events, Meetings, or Volunteer Opportunities list
  pages, those pages automatically filter to their committees. Edit
  screens enforce the same scope — a chair can't edit another
  committee's events, meetings, or opportunities even via a crafted
  URL.
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
