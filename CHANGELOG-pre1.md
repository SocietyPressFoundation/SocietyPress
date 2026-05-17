# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

---

## [Unreleased]

---

## [1.0.71] — 2026-05-06

### Accessibility
- Theme nav-menu submenu toggles now mirror open/closed state to
  `aria-expanded` on the parent link (mobile + placeholder-`#`
  parents). Previously only the `sp-submenu-open` CSS class was
  toggled, so screen readers that don&rsquo;t infer state from CSS
  visibility (NVDA + Firefox) announced submenus as collapsed even
  when open. Initial `aria-expanded="false"` is also injected on
  parent links if WordPress&rsquo; walker didn&rsquo;t.
- `spConfirm` modal now sets `inert` on every body sibling while the
  dialog is open. Modern AT honors `aria-modal` already; older AT
  (NVDA pre-2024, JAWS pre-2023) doesn&rsquo;t. `inert` (widely
  supported as of 2023) closes the gap with no other side effects.
- Research Services case-edit form: 6 `&lt;label&gt;` elements got
  matching `for=` attributes paired with `id=` on their inputs
  (status, researcher, hourly rate, hours authorized, SLA, internal
  admin note). Previously the labels were empty wrappers and screen
  readers announced no field name when tabbing in.
- Pending change-request &ldquo;Note to member&rdquo; textarea got an
  associated `&lt;label class="screen-reader-text"&gt;` so it is
  announced even when the placeholder text is gone.
- Bulk-delete spinner gained `aria-hidden="true"` to match the
  pattern set by import + gallery spinners.

### Refactoring
- Contact Card page-builder widget moved off inline `style="…"`
  per render. New `.sp-contact-card`, `.sp-contact-card__name`,
  `.sp-contact-card__row` (+ `--top` / `--last` modifiers),
  `.sp-contact-card__icon` (+ `--top`), `.sp-contact-card__address`
  classes. Child themes can now override visuals through normal CSS.
- Pending-changes admin form moved off inline `style=` to
  `.sp-pending-actions__form`.
- `.sp-surname-similar` (the &ldquo;X other members research this
  surname&rdquo; hint under each row of the my-account surname list)
  moved off inline `style=` to a stylesheet rule.

Plugin + parent theme: 1.0.71. Marketing theme: 0.43d.

---

## [1.0.70] — 2026-05-06

### Refactoring
- Membership Tiers page-builder widget moved off inline `style="…"`
  attributes per render. New `.sp-tier-card`, `.sp-tier-card--inactive`,
  `.sp-tier-card-name`, `.sp-tier-card-price`, `.sp-tier-card-period`,
  `.sp-tier-card-unavailable` classes. The `.sp-tiers-grid` rule no
  longer needs `!important` since the inline definition is gone.
  Child themes can now override tier-card visuals through normal CSS.
- Page-builder widget picker icons and card icons gained
  `.sp-builder-picker-icon` / `.sp-builder-card-icon` classes; the
  hardcoded `#2271b1` color moved to the stylesheet. Two more inline
  hex sites gone.

Plugin + parent theme: 1.0.70. Marketing theme: 0.43d.

---

## [1.0.69] — 2026-05-06

### Bug fixes
- Help-request resolved-status notification email is now sent. The
  email was wired through a priority-20 hook on the same `wp_ajax`
  action that the main handler sits on at priority 10 &mdash; but
  the main handler called `wp_send_json_success()` (which invokes
  `wp_die()`), so the priority-20 hook never fired. Email logic
  moved inline into `sp_handle_help_mark_resolved()` and the
  priority-5/priority-20 dead hooks removed.

### Security / consistency
- Picture Wall pending-submissions admin page now uses
  `sp_manage_content` for both menu registration and POST handler
  (was `manage_options` for the menu, `sp_manage_content` for the
  handler &mdash; meaning a content moderator could moderate via
  POST but couldn't reach the page through the UI).

### Refactoring
- `page-my-account.php` preferred-phone radio group + uppercase
  surname input moved off inline `style=` to `.sp-radio-row`,
  `.sp-radio-row__option`, and `.sp-input--uppercase` utility
  classes in the parent theme stylesheet.

Plugin + parent theme: 1.0.69. Marketing theme: 0.43d.

---

## [1.0.68] — 2026-05-06

### Security
- HIGH: `sp_ajax_member_detail` now requires the requester's
  membership status to be `active` (or `sp_manage_members`
  capability). Previously gated on `is_user_logged_in()` only — an
  expired or suspended member retaining their WP subscriber account
  could harvest the page nonce and hit the endpoint to enumerate
  member detail (phone, email, city/state, surnames, interests).
  The directory list view already enforced this; the detail AJAX
  did not.
- MEDIUM: Store-order finalize is now atomic. Stripe 3DS redirect
  + webhook can land in parallel; the previous read-then-write
  let both pass the "is it already paid" check, producing a
  duplicate receipt email and audit row. New conditional
  `UPDATE ... WHERE status != 'paid'` returns 0 affected rows for
  the loser, which short-circuits cleanly.
- LOW: events refund handler now passes the extracted PaymentIntent
  ID through `sp_stripe_payment_intent_id_is_valid()` before the
  Stripe API call, matching the store flow.

### Refactoring
- Removed dead `'lifetime'` from two access allowlists. `lifetime`
  is a separate boolean column on `sp_members`, not a status value
  &mdash; it could never match. Document download and surname-contact
  guards now check `status === 'active'` directly.
- Calendar mobile tap-panel JS guards against double-init via
  `window.spCalendarTapPanelInit`. When the calendar widget renders
  on the events template, both `sp_events_frontend_scripts` and
  `sp_events_calendar_scripts` used to fire, double-binding the
  click listener so each tap fired its handler twice.
- Order detail item-quantity output cast through `absint()` for
  consistency with the rest of the codebase.

Plugin + parent theme: 1.0.68. Marketing theme: 0.43d.

---

## [1.0.67] — 2026-05-06

### Accessibility
- Hero-slider previous/next arrows gained `aria-label` ("Previous
  slide" / "Next slide"). Were `&lsaquo;` / `&rsaquo;` glyphs only.
- Records-edit "remove field" buttons (admin) gained `aria-label`s
  in both the PHP-rendered and JS-cloned variants.
- Voting "remove choice" buttons gained `aria-label`s across the
  four sites where they appear (PHP-rendered + JS-cloned + new-row
  template).
- Bulk-attended button dropped its redundant `title` tooltip — the
  visible text "Mark All Attended" is already complete.

Plugin + parent theme: 1.0.67. Marketing theme: 0.43d.

---

## [1.0.66] — 2026-05-06

### Refactoring
- New text utility classes — `.sp-text-success-strong`,
  `.sp-text-info`, `.sp-text-info-link`, `.sp-text-muted-italic`,
  and `.sp-empty-search-result` — to eat the most-repeated inline
  status-color patterns (Active, Published, Draft, Available,
  Cancelled, "no results"). 8 inline `style="color:#…"` sites
  converted to semantic class names; the rest of the inline colors
  in the file are dynamic (status-badge backgrounds, theme-injected
  values) and stay inline.

Plugin + parent theme: 1.0.66. Marketing theme: 0.43d.

---

## [1.0.65] — 2026-05-06

### Security
- Site-color extractor SSRF guard now honors an explicit URL port
  before falling back to scheme defaults. A URL like
  `http://example.com:8080/` used to pin the host to port 80 while
  the cURL connect went to 8080, leaving the pin ineffective.
- Help-request rate-limit counters now bump only after the row
  actually inserts, so a downstream failure (DB insert error, etc.)
  doesn&rsquo;t shrink the legitimate caller&rsquo;s daily quota. New
  `sp_help_rate_limit_record()` helper splits commit from check.
- Newsletter download flushes any active output buffer before
  emitting the binary PDF stream so an upstream notice or stray
  output can&rsquo;t corrupt the response.

### Refactoring
- Volunteer card capacity and sign-up status colors are now
  controlled by modifier classes (`.sp-vol-card-capacity--full`,
  `.sp-vol-status-label--confirmed`, `.sp-vol-status-label--waitlisted`)
  instead of inline `style="color: …"` per render. The waitlisted
  color also moved from `#dba617` (3.55:1 on white) to `#8a6500`
  (4.5:1) at the same time. Two more inline-style hot spots gone.
- Volunteer card capacity counts now cast through `(int)` for
  defensive output even though `$signup_count` and
  `$opp-&gt;capacity` are already integers from a DB query.

Plugin + parent theme: 1.0.65. Marketing theme: 0.43d.

---

## [1.0.64] — 2026-05-06

### Security
- Defense-in-depth: full-site SQL export rejects table names containing
  backticks before interpolating into `SHOW CREATE TABLE` /
  `SELECT *`. Names already came from `$wpdb-&gt;prepare("SHOW TABLES
  LIKE %s",...)`, but a backtick guard prevents the pattern from
  ever reaching unsafe contexts as the codebase grows.

### Accessibility
- Surname repeater inputs (member edit) gained `aria-label`s so
  screen-reader users hear "Surname / County / State / Country / Year
  from / Year to" per cell. Previously announced only the
  `placeholder`, which disappears once the user types. The dynamic
  JS-cloned row got matching labels.
- Three loading spinners (`sp-import-spinner`,
  `.sp-newsletter-edit-spinner`) gained `aria-hidden="true"` so
  screen readers don&rsquo;t describe purely decorative motion. The
  adjacent `role="status"` regions still announce progress.
- Album-grid photo count now uses `_n()` for proper plural handling
  ("1 photo" / "5 photos") and `number_format_i18n` for locale-aware
  thousand-separators. Was bare PHP echo + hardcoded "photos".
- "Surname: %s" research-services label wrapped with sprintf-style
  i18n.

### Reliability
- Calendar grid month default + invalid-fallback now uses
  `wp_date( 'Y-m' )` so the initial month matches WordPress's
  configured timezone, not the server's.
- Events listing date-range filters (30 days / 3 / 6 / 12 months)
  now use `wp_date()` for the upper bound. Stored event dates are in
  WP timezone; the bound was computed in server timezone, so the
  filter could miss or include events at the boundary on servers
  with a different system clock.
- CSV exports got proper `Content-Disposition: attachment;
  filename="..."` quoting (RFC 6266) at two more sites; the
  volunteer-hours export filename now uses `wp_date()` instead of
  `date()`.

Plugin + parent theme: 1.0.64. Marketing theme: 0.43d.

---

## [1.0.63] — 2026-05-06

### Security
- HIGH: newsletter download path-containment + MIME enforcement.
  `sp_ajax_newsletter_download()` now `realpath()`s the resolved
  attachment, refuses anything that doesn't live inside `wp-uploads`,
  and uses `finfo_file()` to confirm `application/pdf` before
  streaming. `wp_redirect()` for the login bounce switched to
  `wp_safe_redirect()`. `nocache_headers()` added before the binary
  stream.
- MEDIUM: event-cancel handler now uses a distinct
  `sp_event_cancel_{event_id}` nonce so a leaked register-form nonce
  cannot be replayed against the cancel endpoint.
- LOW: installer adds `session.cookie_secure=1` when the request is
  over HTTPS so the install-session cookie (CSRF nonce) can&rsquo;t leak
  over plaintext if the user accidentally hits the http:// URL.

### Accessibility
- 389 `&lt;th&gt;` column headers in the plugin and 3 in the parent theme
  gained `scope="col"`. Screen readers now correctly announce
  "column" context when navigating data tables (members, events,
  payments, library, registrations, audit log, etc.).
- 8 `&lt;th&gt;` row headers in the blast-email detail key/value table
  gained `scope="row"` so the field label is announced alongside
  each value cell.
- Newsletter PDF viewer modal now properly manages keyboard focus:
  saves the opener element on open, focuses the close button, traps
  Tab/Shift-Tab inside the modal, and returns focus to the opener
  on close. Newsletter card covers gained `role="button"` +
  `tabindex="0"` + a translatable `aria-label` so keyboard users
  can open the viewer with Enter or Space.
- Surname contact modal got the same focus-management treatment:
  saves opener, focuses first field on open, Escape closes,
  Tab/Shift-Tab cycle inside the modal, focus returns to the
  triggering button on close.
- Cart quantity `+`/`&minus;` buttons gained `aria-label` ("Increase
  quantity" / "Decrease quantity") &mdash; previously announced as
  bare "plus, button" / "minus, button" with no context.
- Album-edit photo remove buttons (admin) and bulk-document remove
  buttons gained `aria-label`s in place of (or alongside) `title`,
  which screen readers don't announce reliably on interactive
  elements.
- Volunteer status badges (waitlisted) switched from `#dba617`
  (3.55:1) to `#8a6500` (4.5:1) at 12px so they meet WCAG AA on
  the tinted-background pattern. Matches the v1.0.58 pending-changes
  fix.
- Last 4 occurrences of `#787c82` muted text replaced with `#6d7175`
  (the established WCAG-AA-safe value). Consistency cleanup after
  the v1.0.60 plugin-wide pass.

### i18n
- Member-edit seasonal-address From/To month dropdowns: 12 month
  names wrapped with `__()`.
- Settings &rarr; Membership &rarr; Period Start month dropdown:
  same 12 names wrapped.
- Member-edit Country dropdown: 25 country names wrapped, plus the
  `&mdash; Other (type below) &mdash;` sentinel.
- Page-template registration: 6 dropdown labels wrapped &mdash;
  SocietyPress Builder, Events, Newsletter Archive, Site Search,
  Research Help Requests, Resource Links Directory.

Plugin + parent theme: 1.0.63. Marketing theme: 0.43d.

---

## [1.0.62] — 2026-05-06

### Refactoring
- `sp_render_member_edit_page()` shed two more sections to dedicated
  helpers: `sp_render_member_edit_admin_notes_section()` and
  `sp_render_member_edit_payment_history_section()`. The render
  function lost ~125 lines and the two extracted helpers each match
  the load-context helper introduced in v1.0.55.
- Payment History table headers gained `scope="col"`.

### Accessibility / i18n leftovers
- "Unknown" author fallback in admin notes is now wrapped with
  `__()` (was a bare string).

Plugin + parent theme: 1.0.62. Marketing theme: 0.43d.

---

## [1.0.61] — 2026-05-06

### Bug fixes
- Document downloads work for the first time. The Documents module
  was rendering links to `admin-ajax.php?action=sp_document_download`
  but no handler was registered, so every click returned `0`. The
  handler is now in place: validates the document exists, checks
  `published` status (drafts only reachable by editors with
  `sp_manage_content`), enforces `members_only` access by requiring
  an authenticated session, same-origin guards the file URL, and
  redirects via `wp_safe_redirect`.

### Accessibility
- Library catalog active-filter remove links got translatable
  `aria-label`s ("Remove %s filter") so screen-reader users hear
  context, not just "times".
- Library catalog sortable column headers now emit `aria-sort`
  ("ascending"/"descending") plus `scope="col"`, and the arrow glyph
  carries `aria-hidden`.
- Genealogical record rows (`sp-record-row`) gained dynamic
  `aria-label`s built from the first non-empty field value, so
  screen-reader users know which record they're about to expand.
- Join form `:focus`/`:focus-within` styles for the submit button
  and the membership-tier radio wrappers.

### i18n
- GDPR personal data exporters (Speaker Profile, Volunteer Hours,
  Research Help Requests, Research Help Responses) now wrap every
  `group_label`, `group_description`, and field-name string with
  `__()`. Members see translatable labels in their personal-data
  export.

Plugin + parent theme: 1.0.61. Marketing theme: 0.43d.

---

## [1.0.60] — 2026-05-06

### Security
- HIGH defense-in-depth Stripe `payment_intent` ID validator added
  (`sp_stripe_payment_intent_id_is_valid()`). Two `wp_remote_get` sites
  in the store finalize/return path now reject anything that isn&rsquo;t a
  proper `pi_*` ID before it reaches the Stripe API URL. Mirrors the
  v1.0.58 session-ID validator pattern.
- iCal feed sync re-runs `sp_validate_external_feed_url()` at sync
  time, not just at save time. The cron path used to fetch
  `$feed-&gt;url` directly from the database; now it re-validates
  before calling `wp_remote_get` so the SSRF guard runs on every
  fetch.
- Reports / volunteer-hours / Insights SQL switched from raw
  `date( 'Y-01-01' )` / `date( 'Y-m-01' )` to `wp_date()` so the
  &ldquo;this year/month&rdquo; window matches WordPress&rsquo;s configured timezone
  rather than the server&rsquo;s.

### Accessibility
- Page-builder widget picker is now a real keyboard-accessible dialog:
  added `role="dialog"` + `aria-modal="true"` + `aria-labelledby`.
  Opening focuses the close button; Escape and overlay-click return
  focus to the &ldquo;Add Widget&rdquo; trigger; Tab/Shift-Tab cycles inside
  the picker so focus can&rsquo;t escape behind the modal.
- Page-builder card buttons (`.sp-builder-btn`) and picker items
  (`.sp-builder-picker-item`) gained `:focus`/`:focus-visible`
  rulesets. Library catalog tabs (`.sp-catalog-tab`) too.
- Member directory modal got a Tab/Shift-Tab focus trap so keyboard
  users can&rsquo;t tab past the close button into obscured page content.
- Library catalog filter `&lt;select&gt;` elements (media type, source,
  sort) gained `aria-label`s. Without them screen readers read only
  the first option text.
- 12 `.sp-text-error` required asterisks gained `aria-hidden="true"`,
  joining the matching fix in v1.0.58 for `.sp-member-edit-required`,
  `.sp-event-edit-required`, `.sp-contact-required`. Two
  `.sp-ext-cal-required` asterisks too.
- `#767676` muted text replaced with `#6d7175` plugin-wide (~60
  sites). At 12px on white the old value missed WCAG AA by 0.02; the
  new value clears it at 5.0:1.
- 404 template now uses `&lt;main id="main-content"&gt;` instead of `&lt;div&gt;`,
  so the skip-to-main link lands on a real landmark region.

### i18n
- Member-facing: `Available Sessions`, `Library Catalog`,
  `Research Help`, `Ask a Question` headings + button text.
- Admin: PayPal Show buttons in both sandbox and live credential
  rows (Stripe was already correct), Members list filter checkboxes
  ("Individuals" / "Organizations"), speaker form Name field,
  donation campaign Campaign Name field, blast email status filter
  tabs, blast email detail table headers + delivery line, audit log
  pagination + footer text, CSV import field-map "Skip" / "Store as
  custom field" options, member export column-group legends
  (Identity, Membership, Contact, Address, Seasonal Address,
  Preferences, Directory Visibility, Custom Fields, Legacy / Import
  Data).

### Code review follow-ups
- Event registrations table: `$type_label` now wraps `Member`/`Guest`
  in `esc_html__()` (was bare string echo).

Plugin + parent theme: 1.0.60. Marketing theme: 0.43d.

---

## [1.0.59] — 2026-05-06

### Accessibility
- Page-builder primary, secondary, and outline buttons gained
  `:focus`/`:focus-visible` rulesets in the frontend stylesheet so
  keyboard users can see focus on every CTA the builder emits. Voting
  choices got matching `:focus-within` ring on the radio/checkbox
  label — picking up keyboard focus inside the wrapper now lights the
  whole option.

### Marketing site
- Hardcoded `/cms/wp-content/uploads/` paths in `page-features.php`
  replaced with `wp_upload_dir()` lookups. Image references now move
  with the install.
- Comparison and requirements tables: every `<th>` carries
  `scope="col"` so screen readers announce row context correctly.
- Footer column headings promoted from `<h4>` to `<h2>` (`.footer-links__heading`)
  to fix the heading-level skip; CSS selector updated.
- Manual-upload helper on the download page promoted from `<h4>` to
  `<h3>` to sit cleanly under the section's `<h2>`.
- "Events &rarr; Add New" wording in the installation guide changed
  to the SocietyPress-native "Events &rarr; Add Event" — "Add New"
  is WordPress core chrome.
- "Harold-friendly" internal-persona phrase removed from the public
  docs page and replaced with "plain-English."
- "No shortcodes to memorize" replaced with "No codes, no templates,
  no technical knowledge required" on the for-administrators page.

### Other
- Parlor child theme regained its `after_switch_theme` palette-push
  hook (the only child theme that was missing one). Activating Parlor
  now writes its rose/plum palette into SocietyPress design settings.
- Installer security hardening: stream-context SSL verification
  enforced explicitly; mysqli error messages logged via `error_log()`
  with a generic message shown to the user; trailing-separator path
  comparison fixed; session strict-mode + httponly cookies; randomized
  config filename with mu-plugin glob lookup; cleanup now removes
  `.htaccess.sp-bak`; `preg_replace_callback` used everywhere user
  values land in regex replacements (no more `$1` backreference
  injection).

Plugin + parent theme: 1.0.59. Marketing theme: 0.43d.

---

## [1.0.58] — 2026-05-06

### Security
- HIGH Stripe `session_id` path-injection closed at all 5
  `wp_remote_get` sites. `sanitize_text_field()` doesn't strip slashes,
  so a crafted `?sp_session=../v1/charges/ch_xxx` would have hit a
  different Stripe API endpoint. New `sp_stripe_session_id_is_valid()`
  helper enforces the documented `cs_(test|live)_[A-Za-z0-9]{20,}`
  shape; called from the events, donations, lineage, research-case,
  and research-invoice return handlers.
- `$wpdb->last_error` stripped from library/records/collection import
  error displays — leaked schema details to admins. Replaced with
  `error_log()` plus a generic admin message.
- `sp_admin_capability_map` filter now actually fires.
  `sp_get_menu_capability_map()` previously returned a hardcoded
  array, so theme-presets and help-tags capability filters were
  dead. Restructured to `$map = [...]; return apply_filters(...);`.
- Capability guards added to `sp_render_audit_log_page()` and
  `sp_render_access_log_page()` for defense-in-depth.
- iCal SSRF helper distinguishes "hostname could not be resolved"
  from "private/reserved IP" so admins get actionable error messages.
- Two raw `date()` concatenations in Reports SQL routed through
  `$wpdb->prepare()` to keep the codebase pattern consistent.
- One unescaped `$total_volunteers` echo cast to `(int)`.

### Code review follow-ups
- Critical `spConfirm` Enter-key fix: pressing Enter with focus on
  Cancel used to fire `onConfirm` regardless. Enter now delegates to
  whichever button has focus, so Cancel-then-Enter doesn't silently
  complete a destructive action.
- 13 `add_submenu_page` calls had split-string i18n
  (`__('Foo', 'societypress') . ' — SocietyPress'`) — translators
  only saw `Foo`. Merged into single-string form. Affects External
  Calendars, Documents, Document Categories, Edit Document, Bulk
  Upload Documents, Lineage Programs, Lineage Applications, Edit
  Lineage Program, Review Lineage Application, Research Cases, Review
  Research Case, Theme Presets, Help Request Tags.
- Media/Menus/Widgets/Customize submenu titles wrapped in `__()`.

### Accessibility
- Five modal dialogs gained `role="dialog"` + `aria-modal` +
  `aria-labelledby`: the newsletter PDF viewer, surname-contact
  modal, plus focus-trap work on `spConfirm`.
- Three payment error containers (`#sp-stripe-error`,
  `#sp-paypal-error`, `#sp-donate-paypal-error`) gained
  `role="alert" aria-live="assertive" aria-atomic="true"` so screen
  readers announce card declines.
- Library catalog search-tab buttons carry `aria-selected` (initial
  PHP + click-handler toggle).
- Bulk delete + import progressbars update `aria-valuenow` as fill
  width changes.
- 13 icon-only buttons switched from `title=` to `aria-label=` —
  `title` isn't reliably exposed by screen readers on interactive
  elements.
- Newsletter PDF, email-log, and newsletter-blast-preview iframes
  gained `title` attributes; admin design-page logo preview gained
  `alt`; surname contact modal fields gained id/for label
  associations.
- `#dba617` amber text replaced with `#8a6500` (4.5:1 on white) at
  three sites; `.sp-text-muted` tightened from `#787c82` to `#6d7175`.

### i18n
- Member-facing: newsletter widget Download / Members Only / Public
  badges, events listing pagination Previous/Next, registrations
  refund button, surname search placeholder, certificate-not-found
  page, Yes/No-show attended dropdown options, library catalog
  Featured/Active badges.
- Admin: Reports dashboard stat labels (Active Members, Events This
  Year, Volunteer Hours, Library Items, Open Help Requests, Donations
  This Year, Top Campaign — with translator-comment plurals), email
  log stat labels (Sent/Blocked/Failed/Total (30d)), library catalog
  item-condition options, builder pages publish/draft options,
  campaigns active/draft/closed, Stripe currency labels (USD/CAD/GBP/
  EUR/AUD), volunteer-opportunities waitlist count plural,
  pending-changes "CHANGED" flag.
- Picture-wall slug-error message now shows admin-only configuration
  guidance and a friendly fallback to public visitors.
- Help requests "Request not found." wrapped.

### UX language
- Removed "slug" jargon from picture-wall public error.
- `spConfirm` Confirm button gained matching `:focus` styling.

### Refactoring
- New helper `sp_stripe_session_id_is_valid()` called from 5 sites
  that used to concatenate `sanitize_text_field()` output into the
  Stripe API URL.
- `sp_get_menu_capability_map()` restructured to use a `$map`
  variable + `apply_filters()` return.

### .pot files
- Plugin and parent theme .pot regenerated. Plugin .pot is now
  ~18,400 lines covering ~4,650+ strings.

Plugin + parent theme: 1.0.58. Marketing theme: 0.42d.

---

## [1.0.57] — 2026-05-06

### Security
- **Stored XSS via CSV import error display closed.** The error rows
  shown after a member-import run included unsanitized CSV-supplied
  values (first/last names, MySQL errors, `wp_insert_user()` error
  messages) and were inserted via `innerHTML`. An attacker with
  `sp_manage_members` could craft a row whose name contained
  `<img src=x onerror=…>` and exfiltrate session cookies / nonces
  whenever the row tripped the duplicate or DB-error path. Switched
  to per-row `textContent` writes so CSV-borne HTML can never reach
  a DOM sink.
- **SSRF via iCal feed URL closed.** Feed URLs (saved by
  `sp_manage_events`) were stored after `esc_url_raw()` and fetched
  by cron and on-demand sync without scheme or private-IP guards.
  An attacker could save a URL pointing at the cloud metadata
  endpoint (e.g. `http://169.254.169.254/...`) or LAN admin panels
  and trigger the server to fetch them. New
  `sp_validate_external_feed_url()` helper rejects non-http(s)
  schemes and private/reserved IPs at both add and update save
  paths.
- **`$page_title` escape fragility closed in member-edit.** The title
  was pre-`esc_html()`'d inside the new `sp_member_edit_load_context()`
  helper and echoed bare. Currently safe but one refactor away from
  XSS. Moved the escape to point of output and let the context
  builder return raw values, per WPCS.
- **Rate limit on `sp_ajax_library_item_detail`** (60 requests per
  minute per IP for unauthenticated callers) so the catalog can't
  be enumerated by walking item IDs.
- **`$wpdb->last_error` no longer leaked to admin** in import error
  messages — the raw error logs to `error_log()` and users see a
  generic "check the server error log" notice.
- Member-edit save handler now allowlists `$status` (against
  `sp_get_member_statuses()`) and `$member_type` (`individual` |
  `organization`) — both previously fell through `sanitize_text_field()`
  alone.
- Member-edit role dropdown now mirrors the save-handler allowlist,
  so non-admin delegates only see the roles they can actually assign.
- Removed dead `sp_process_import()` (1,000-line synchronous duplicate
  of the AJAX batch version) — its row logic was a maintenance hazard
  flagged by audits.
- PayPal donation amount cast goes through `sanitize_text_field()`
  before `floatval()` (was raw `(float)`-cast).

### Accessibility
- spConfirm dialog: focus trap (Tab/Shift+Tab cycles between Cancel
  and Confirm), screen-reader-only "Confirm action" heading via
  `aria-labelledby`, message exposed via `aria-describedby`, and
  high-contrast `:focus`/`:focus-visible` outlines on both buttons.
- Bulk-delete and member-import progress bars now have
  `role="progressbar"` plus `aria-valuemin`/`valuemax`/`valuenow`,
  and the JS updates `aria-valuenow` as the fill width changes.
- All 14 required-field asterisks (`*`) marked `aria-hidden="true"`
  so screen readers don't double-announce required-state.
- Form-label `id`/`for` associations added to the Research intake
  form (~10 inputs), Volunteer hours log form (5 inputs), Donations
  filter form (5 inputs), and the Leadership "Find Member" search.
- Login Acknowledgment "I Understand" button and Member Detail close
  button now have visible `:focus` styles.
- Cart book-cover placeholder color bumped from `#bbb` (1.5:1, hard
  WCAG fail) to `#767676`.
- Marketing newsletter card date color `#666` → `#595959`.
- Theme header search `outline:none` scoped to its `:focus` rule
  only (was on the base state, which silently killed the browser
  default for keyboard users).
- Event registration `#sp-reg-message` swaps `role` to `alert` /
  `aria-live="assertive"` for errors and back to `status` / `polite`
  for success notices.
- Join form result notice gained matching `role`/`aria-live` and
  uses a CSS class instead of inline styles.
- Nine hardcoded `font-size: 10px` declarations converted to
  `0.625rem` so they scale with the user's base-font preference.

### Changed (i18n + UX language)
- ~150 additional strings wrapped: full event-detail page (Members:/
  Non-Members:/Free/Speakers/role labels/Website/slot status/spots
  remaining plurals), event registration JS confirmation messages
  (waitlisted/registered/pay-at-door/reminder), Pages "Update Page"/
  "Create Page"/"Cancel", event admin (Title field, slot Start/End/
  Capacity/Label, +Add Time Slot, registration meta with plurals,
  +Add Walk-in/Export CSV buttons), library catalog sort headers,
  member directory modal section labels and field labels, Stripe
  Show/Hide PayPal toggle, "Generating…"/"Testing…" inline-JS
  buttons, donation `/mo` `/yr` suffixes (currency symbol now from
  `sp_get_currency_symbol()`), address state/postal JS labels,
  `WP_List_Table::search_box()` button labels for Members/Pages/
  Events/Speakers.
- All ~80 `add_submenu_page()` calls bulk-wrapped: page titles
  (`'X — SocietyPress'`) and menu labels both translatable.
- `Slug` renamed to `URL Name` in 7 admin contexts (library
  categories, resource categories, committees, documents, landing
  pages, research guides edit + list).
- "Media Library" jargon replaced with "Files section" in three
  help texts (meeting agenda/minutes upload, store product photo).
- "blog posts" → "pages" in the export-page help.
- "deactivating and reactivating the plugin" → module-toggle
  instructions in the Newsletter category fallback notice.
- "Recent posts from the Newsletter category" widget description
  rewritten as "Newsletters you publish under SocietyPress →
  Newsletters will appear here".
- "Post-login acknowledgment" Settings field label renamed to
  "Notice shown after sign-in".

### Refactoring
- Removed dead `global $wpdb; $prefix = …;` lines in
  `sp_render_member_edit_page()` (left over from the v1.0.56
  context split).
- Hero slider `$media_url` now `esc_url()`-escaped at both output
  sites (background-image and `<source src>`) instead of relying on
  the pre-escape at assignment.
- `sp_member_edit_load_context()` docblock documents that callers
  are responsible for capability checks (the function itself reads
  encrypted member data).

### Empty states
- Research guides admin table: empty row now includes a `+ New
  Guide` button.
- Four List Tables (`SP_Speakers`, `SP_Volunteers`,
  `SP_VolunteerHours`, `SP_Ballots`) override `no_items()` with
  module-specific guidance and a link to the create flow.

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
