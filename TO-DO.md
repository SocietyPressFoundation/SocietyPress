# SocietyPress — TO-DO
Plugin: 1.0.19
Parent theme: 1.0.19
Marketing theme: 0.08d
Last updated: 2026-04-15

---

## Awaiting Charles's Input

- [ ] Genealogical Records — needs real data imported (source data required)

## To Do

### getsocietypress.org marketing site — fill out the public face

Pages and features Charles called out:
- [ ] Documentation hub — install, setup, every module, child-theme guide
- [ ] Bug reports flow (link to GitHub Issues with a pre-filled template)
- [ ] Feedback intake
- [ ] Feature requests intake
- [x] Support forums (bbPress) — bbPress styled to match theme, full-width layout, font sizes bumped (2026-04-15)
- [ ] FAQ
- [ ] Feature list page (top 12 from the marketing list, with a "...and more" expansion)
- [ ] Downloadable information sheets (PDF one-pagers per audience: society admin, board member, treasurer, librarian)
- [ ] Tip jar / donations

Additional pages worth adding while we're building it out:
- [ ] Changelog / release notes (auto-pulled from GitHub Releases once those exist)
- [ ] Public roadmap (what's next, what's done, what's deferred)
- [ ] Comparison page — SocietyPress vs. ENS / Blue Crab / Wild Apricot (the migration-pitch page)
- [ ] Testimonials wall (when we have them — start with placeholder)
- [ ] Showcase / case studies of real societies running SocietyPress
- [ ] Getting Started video walkthrough (5-min screencast: install → setup wizard → first member → first event)
- [ ] Screenshot gallery — admin and frontend, organized by module
- [ ] Prominent live-demo link on every page (currently demo.getsocietypress.org exists but isn't surfaced)
- [ ] Accessibility statement (ATAG/WCAG 2.1 AA target)
- [ ] Privacy policy + Terms of use (the marketing site's own, distinct from the policies we generate for installs)
- [ ] security.txt (RFC 9116) — disclosure contact for security researchers
- [ ] Contact form (mailto for now per project notes; email-on-submit later via the companion plugin)
- [ ] Mailing list signup — release announcements, security advisories
- [ ] Newsletter (eat our own dogfood: use SocietyPress's blast email feature on getsocietypress.org)
- [ ] Sponsor / contributor recognition page (when applicable)
- [ ] Inline GitHub Releases feed on the homepage / docs index
- [ ] Sitemap (XML and HTML)
- [ ] Site search across docs + FAQ + showcase + blog
- [ ] Status / uptime page (optional — for paid hosting partners later)

### Softaculous installer

- [ ] Build the Softaculous package per their format spec (config XML, screenshot PNG, description, ZIP layout)
- [ ] Verify against `scripts/build-softaculous.sh` (already stubbed in the repo per project notes; needs to actually run end-to-end)
- [ ] Submit to Softaculous Limited via their partner program
- [ ] Track the review/approval cycle
- [ ] Add "One-click install via Softaculous on any cPanel host" to the marketing feature list (item 24-ish)

### GitHub releases pipeline (prerequisite for #3 working)

- [ ] Make the `charles-stricklin/SocietyPress` GitHub repo public (or create it if it doesn't exist yet)
- [ ] Cut the first tagged release (`v1.0.18` matching the current plugin) with a properly-named `societypress.zip` asset attached
- [ ] Document the release process so each version bump produces a release artifact
- [ ] Once releases exist, update notifications will start surfacing inside SocietyPress sites automatically — no further code change required

## Completed

- [x] Meetings & Minutes module — new `sp_meetings` table covers Board, Membership (general), and Committee meetings in one schema with a `meeting_type` discriminator and an optional `committee_id`. Dedicated admin page (Meetings & Minutes) with type/committee filters, list view, and an add/edit form that captures title, date, location, attendance, agenda URL, minutes URL, inline notes, and visibility (public / members-only / committee-only). The Committee dropdown shows/hides via JS based on the chosen meeting type.
- [x] Bundle de-branded — every reference to society identifiers removed from the shippable plugin and parent theme. Theme registry entry deleted, "Society Publication" acq_code option replaced with "Society Publication", placeholders generalized, local cities kept in the Central Time label (plus Houston, Dallas, Austin, Chicago) since it's a city, not a society. the society-specific work for samplesociety.com lives separately in its own child theme.
- [x] PWA service worker (conservative caching) — `?sp_sw=1` endpoint serves a versioned SW; pages are network-first with a friendly offline fallback page; static assets (CSS, JS, fonts, images) are cache-first with auto-invalidation on plugin version bump; non-GET requests and admin/login/AJAX/REST/cron paths are never intercepted. Footer-registered idempotently on every public page load.
- [x] the society logo redo — Charles re-did it on 2026-04-14
- [x] PWA manifest — `?sp_manifest=1` endpoint emits a real web app manifest using society name, theme color, and icons; `<head>` carries `rel="manifest"`, `theme-color`, `apple-mobile-web-app-capable`, and `apple-touch-icon`. Sites can now be installed to a phone home screen.
- [x] Full-site export — Settings → Export & Backup. One ZIP containing `societypress.sql` (every sp_* table; member fields decrypted to plaintext) plus a `README.txt` with restore steps. Streamed to download with sane PHP timeout/memory bumps.
- [x] Committees module — first-class `sp_committees` table + dedicated admin (list view with member counts, add/edit form). Volunteer-opportunity edit form's committee dropdown now populates correctly. Volunteer-opportunity list shows committee names instead of `Committee #1`.
- [x] Voting & Elections — was already built; TO-DO was stale. 4 tables, admin pages (sp-ballots, sp-ballot-edit, sp-ballot-results), all wired in.
- [x] GDPR donations exporter & eraser — also already built; cleaned up i18n and rewrote the eraser around pseudonymization (clears donor identifiers, sets `is_anonymous=1`, retains amount/date/campaign/payment per IRS recordkeeping rules).
- [x] .pot file generation for translators — `wp i18n make-pot` ran for both plugin (253 KB) and parent theme (15 KB); `.pot` files committed under each component's `languages/` directory.
- [x] Import path exposure — already resolved in five places (every `temp_file` is `basename()`-stripped on output and `sanitize_file_name()`-validated on input). Removed from list.
- [x] jQuery rewrite — verified 20 remaining references are all wpColorPicker integrations, which require jQuery. Item is effectively unactionable; removed from list.
- [x] Store products separated from library — `sp_store_products` table, dedicated admin (Store Products list + add/edit), unified storefront listing, source-aware cart, order_items tracks library_item_id OR product_id, stock decrements on checkout, "Sold out" surfaces in storefront.
- [x] Store description split — `store_description` lives on library_items as marketing copy; storefront prefers it with fallback to the catalog description.
- [x] ENS migration guide — `Documentation/ENS-MIGRATION-GUIDE.md`, Harold-friendly walkthrough covering export, install, import, verify, cutover (230 lines).
- [x] Installer security pass — DB password no longer in session; auto-login uses time-limited transient + 256-bit secret; zip-slip mitigations on WP and bundle extraction; db_host injection blocked via per-constant regex; "already installed" guard tightened (AND→OR); demo-config path hardcoded; admin_user/membership_period server-side validated; download size capped; error output escaped; PHP 8 guard moved up.
- [x] i18n cleanup pass — child theme text domains unified to `'societypress'`; ~50 bare strings wrapped across donations, blast email, page builder, events, library, settings, navigation, parent theme; `sp_localized_status()` helper centralizes status/type label translation across ~25 locations; email_type slugs translated via dedicated context map; WP_List_Table headers verified clean.
- [x] Bundle rebuild — `downloads/societypress-latest.zip` refreshed with every change above; nested marketing theme stripped; child themes 1.1.0 with palette-on-activation + widget areas now canonical.
- [x] Calendar bug — standalone events template now matches widget (width:100% fix applied to both render paths).
- [x] Parent theme version sync — style.css matched to SOCIETYPRESS_THEME_VERSION constant.
- [x] Child theme define guards — all 5 child themes wrap their version constant in `if ( ! defined( ... ) )`.
- [x] Orphaned theme cleanup — removed five duplicate `societypress-*` folders + stale `society` and `san-antonio-genealogical-historical-soci` from local working copy.
- [x] 47 audit findings resolved (security, code quality, UX, i18n).
- [x] PayPal integration — join form supports Stripe and PayPal.
- [x] i18n pass — 3,949 text-domain calls in plugin, 186 in parent theme (~97% coverage).
- [x] 13 feature modules with toggle system (now 14 with first-class Committees).
- [x] 10 access areas + 8 role templates.
- [x] 5 child themes — Heritage, Coastline, Prairie, Ledger, Parlor.
- [x] 21 page builder widgets.
- [x] 56+ custom database tables.
- [x] Custom theme builder with site color extractor.
- [x] Demo site — demo.getsocietypress.org (primary dev/test).
