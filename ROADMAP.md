# SocietyPress Roadmap

This is the public record of what's coming next. It's organized by theme
rather than by date because delivery estimates for a solo project are
notoriously unreliable. Each item describes the **motivation** (the
problem), **scope** (what the deliverable is), and **blockers** (what
has to happen first, if anything).

Items are listed in rough priority order within each section. Things near
the top will likely ship sooner; things near the bottom may move up,
move down, or be dropped entirely as the project evolves.

For what's already shipped, see [`CHANGELOG.md`](CHANGELOG.md).

---

## Recently shipped

Items that were on this roadmap and have now landed. Kept here for a
release cycle or two so visitors can see momentum before being pruned
into the changelog.

### Theme Exchange — Tier 1

Shipped 2026-04-26. Societies can export their site's design tokens
(palette, fonts, spacing, layout) as a portable JSON preset and import
presets from other societies. Admin page at SocietyPress → Theme
Presets handles both directions; tokens are sanitized through the
existing design-page validators on import. Public Theme Gallery at
`/themes/` lists curated presets (Heritage, Coastline, Prairie, Parlor,
Ledger to start).

### Lineage Programs (First Families, Pioneer Settlers, etc.)

Shipped 2026-04-26. Multi-program lineage / heritage recognition
module. Members apply through a public form, staff review in an admin
queue with status workflow, approved members appear on a public roster
with auto-generated certificate numbers and printable certificates at
`/?sp_certificate=NNN`. Optional application-fee Stripe redirect.
GDPR exporters/erasers wired.

### Public Donation form + Stripe recurring + PayPal one-time

Shipped 2026-04-26. New `[sp_donate]` shortcode delivers preset
amounts, custom amount, one-time / monthly / annual frequency, cover-
the-fee toggle, anonymous donations, in-honor-of dedications. Stripe
Checkout end-to-end for all three frequencies with a signature-
verified webhook handling renewals. PayPal Smart Buttons handle
one-time donations.

### Help Requests upgrade — public submission + comradery model

Shipped 2026-04-26. The Help Requests module pivots to "free by
default" comradery. Public submission with math captcha + email
verification + per-email rate limiting. Time-entry on every response
auto-writes to a unified volunteer-hours ledger keyed by source. Mark-
resolved + endorse-helpful + accept-as-answer. Public archive with
tag-filter pills. Admin bulk actions (approve / mark-resolved / hide /
delete). Member volunteer-hours summary widget.

### Paid Research Services (opt-in escalation)

Shipped 2026-04-26. The companion module for the rare case that
genuinely needs many hours of focused work. Public intake + Stripe
up-front payment, admin queue + single-case review, researcher
dashboard with one-click claim and inline log-hours, additional-hours
billing flow (researcher requests → Stripe-billed → case bumps
authorized hours), in-system case messaging with attachments and
email notifications, status-change emails on every transition,
convert-from-Help-Request escalation path.

### Comparison page — SocietyPress vs. ENS / Wild Apricot / custom WP

Shipped 2026-04-26 at `/comparison/`. Quick at-a-glance matrix, honest
"where we're weaker" section, full feature matrix, 5-year cost-of-
ownership comparison, ENS migration callout.

### Downloadable PDF info sheets (one-pagers)

Shipped 2026-04-26. Four print-optimized audience pages —
`/for-administrators/`, `/for-board-members/`, `/for-librarians/`,
`/for-treasurers/` — with a floating "Print as PDF" button on each
that triggers `window.print()`. Print CSS hides nav/footer/button so
the saved PDF is clean.

### First tagged GitHub release — `v1.0.19`

Shipped 2026-04-19. The repository now carries a semver tag, unblocking
the in-plugin update checker, the Softaculous submission flow, and the
planned homepage activity feed.

### Native store checkout

Shipped 2026-04-19 (plugin 1.0.20). The cart now mounts the Stripe
Payment Element (card, Apple Pay, Google Pay, Link) and PayPal Smart
Buttons (PayPal, Venmo) inline, with real refund buttons on the order
detail page and a persistent admin notice when neither processor is
configured.

### `security.txt` at `/.well-known/security.txt`

Shipped 2026-04-15. RFC 9116 disclosure file live at
`https://getsocietypress.org/.well-known/security.txt`, pointing at the
[Security Policy](https://getsocietypress.org/security-policy/) page.

---

## Distribution & Releases

Getting SocietyPress into the hands of actual societies with as little
friction as possible.

### Softaculous one-click install

**Motivation:** Most of the target audience hosts on cPanel providers
where Softaculous is the default installer. A one-click install cuts
the path from "heard about SocietyPress" to "running on my site" from
an afternoon to three minutes.
**Scope:** Build the Softaculous package per their submission spec
(`info.xml`, screenshots, bundle ZIP layout), validate it with
`scripts/build-softaculous.sh` end-to-end, and submit through the
partner program.
**Blockers:** None.

### In-app update checker surfacing

**Motivation:** Once releases exist, WordPress administrators should
see an "Update available" notice inside their dashboard without having
to check GitHub manually.
**Scope:** The plugin already has `SOCIETYPRESS_GITHUB_REPO` wired to
the release-checking endpoint — verify that the notice appears, the
update ZIP downloads, and the extraction is zip-slip-safe.
**Blockers:** None.

---

## Marketing Site (getsocietypress.org)

The front door. The templates exist for most of these; many need content
or final polish.

### Theme Exchange — Tier 2 (themed bundles) and Tier 3 (full child themes)

**Motivation:** Tier 1 (design-token JSON presets) shipped. Tier 2
adds custom CSS + asset files inside a sandboxed `.spchildtheme`
archive — fancier customization without crossing the PHP-execution
boundary. Tier 3 adds full WordPress child themes via curated review
with a "Reviewed by SocietyPress" badge for trust.
**Scope:** Tier 2 — bundle parser, CSS sanitizer, image-only asset
allowlist, admin import flow extension. Tier 3 — submission queue,
manual review process, badge system on the gallery.
**Blockers:** None for Tier 2. Tier 3 wants a documented review
checklist before launch.

### Donations / tip jar page on getsocietypress.org

**Motivation:** SocietyPress is free forever, but voluntary donations
keep the lights on. The `.github/FUNDING.yml` points at this page.
**Scope:** `page-donate.php` with a clear "no pressure" tone, payment
options (Stripe, PayPal, check), and recognition language for
contributors who want it.
**Blockers:** Payment-processing decision (Stripe account, etc.).

### 5-minute Getting Started screencast

**Motivation:** Most society administrators learn faster from watching
than from reading. A short video covering install → setup wizard →
first member import → first event closes the "can I actually do this?"
gap.
**Scope:** A 5-minute screencast with voiceover, published to YouTube
and embedded on the homepage and docs landing.
**Blockers:** A clean demo environment and time to record.

### Mailing list signup + release announcements

**Motivation:** Release notifications, security advisories, and
occasional tips are more reliably delivered by email than by hoping
people check the site.
**Scope:** A simple opt-in form on the marketing site plus a newsletter
that **uses SocietyPress's own blast-email feature** (eating our own
dogfood on a site that also functions as the marketing home).
**Blockers:** Blast-email integration with a non-society context (may
need a lightweight "subscribers" model separate from members).

### Site-wide search

**Motivation:** As docs and showcase pages grow, finding the right
page becomes harder.
**Scope:** A WordPress-native search page that searches docs, FAQ,
showcase, and blog posts with per-source filtering.
**Blockers:** None.

### XML sitemap

**Motivation:** Search engines find the site faster with an explicit
sitemap.
**Scope:** Dynamically generated XML sitemap at `/sitemap.xml` covering
pages, blog posts, docs, and the showcase.
**Blockers:** None.

### Inline GitHub Releases feed on homepage

**Motivation:** Showing activity on the homepage — "v1.0.19 released
three days ago" — signals the project is alive.
**Scope:** Cached fetch of the GitHub Releases API, rendered as a
compact feed in the homepage footer or sidebar.
**Blockers:** None.

---

## Demo Site (demo.getsocietypress.org)

Making the live demo show off everything the software can do.

### Import the Kindred Genealogical Society dataset

**Motivation:** The demo currently shows an empty society. With a
realistic dataset — members, records, newsletters — it becomes a
working reference for what a real deployment looks like.
**Scope:** Import the `Sample Data/Kindred Genealogical Society/` bundle
(members CSV, 12 record collections, 12 newsletter PDFs) into
demo.getsocietypress.org. The sample data is already assembled and
gitignored.
**Blockers:** None.

### ENS Migration demo walkthrough

**Motivation:** The single most effective sales tool for an ENS society
is a "watch us migrate one" demonstration. Having a live example with
realistic ENS-format data proves the migration works.
**Scope:** Use the ENS-format CSVs in `Sample Data/ENS Migration Demo/`
to perform and document a fresh migration on demo.getsocietypress.org.
Publish the walkthrough as a docs page.
**Blockers:** Kindred dataset loaded (so we start from a realistic
pre-migration state).

### Hart Island / NYC Marriage GENRECORD showcase

**Motivation:** GENRECORD is the open exchange format being developed
alongside SocietyPress. Showcasing real public-domain data published
through GENRECORD demonstrates interoperability beyond CSV.
**Scope:** Load the `Sample Data/Real Public Domain Sources/` `.gedrec`
files (Hart Island burials, NYC marriage certificates) into a Records
module collection on demo.getsocietypress.org, with a docs page
explaining the format and how to export to it.
**Blockers:** None (data is staged, format is implemented).

---

## Documentation

Getting the user-facing docs up to the same quality bar as the code.

### End-user documentation hub

**Motivation:** The repo has technical docs (`docs/ARCHITECTURE.md`,
`FEATURES.md`); what's missing is the walkthrough-style documentation
for society volunteers. Harold (the canonical non-technical end-user
persona) needs step-by-step guides, not architecture references.
**Scope:** A full docs hub at `getsocietypress.org/docs` covering:
installation, setup wizard, every module from a user's point of view,
the ENS migration, and troubleshooting. Structured so a senior
volunteer can find the answer without scrolling.
**Blockers:** None (pure content work).

### FAQ page content

**Motivation:** The FAQ page template is built but needs real answers
to real questions.
**Scope:** 20-30 anticipated questions with clear answers, organized
by category (installing, configuring, members, events, etc.).
**Blockers:** Observation of what actual questions come in (GitHub
issues, support forums, feedback form).

### ENS migration handbook (expanded)

**Motivation:** The current `docs/ENS-MIGRATION-GUIDE.md` is a concise
walkthrough. Societies moving thousands of records and hundreds of
members need a more detailed guide with edge cases.
**Scope:** Expanded handbook covering field-by-field ENS → SocietyPress
mapping, bulk decision points (what to do with "legacy" fields, how to
handle overlapping memberships), and a rollback plan.
**Blockers:** None.

---

## Platform Features

New capabilities for the software itself.

### Events recurring-series improvements

**Motivation:** The monthly-nth-day recurring logic is correct but
hard to configure. Administrators want to say "third Thursday of every
month through June" without thinking about `nth_day_of_month`.
**Scope:** Rewritten UI with calendar-picker preview and plain-language
summaries ("This event will occur on: June 20, July 18, August 15,
September 19…").
**Blockers:** None.

### Dashboard widgets for chairs

**Motivation:** Committee chairs currently see the same dashboard as
full admins. A chair-scoped dashboard showing their committee's
upcoming events, open volunteer slots, and pending sign-ups would
reduce the reliance on the society administrator.
**Scope:** Role-aware dashboard with per-role widget sets. Reuses the
existing stat-card framework.
**Blockers:** None.

---

## Operations

Making the project itself easier to run long-term.

### Incorporate the SocietyPress Foundation as a 501(c)(3)

**Motivation:** SocietyPress as "a project Charles runs" is vulnerable
in ways a 501(c)(3) isn't. A foundation creates a legal home for the
project independent of any one person, enables donations to be
tax-deductible, qualifies for GitHub's nonprofit program, and gives
societies confidence in long-term sustainability.
**Scope:** Incorporation as a Texas nonprofit, IRS Form 1023 or 1023-EZ
filing, bylaws, board composition, fiscal sponsor arrangement if
appropriate.
**Blockers:** Legal and tax advice; time.

### GitHub Sponsors / Open Collective

**Motivation:** Recurring sponsorship smooths income volatility and
gives sponsors a visible recognition channel.
**Scope:** Enable GitHub Sponsors on the `SocietyPressFoundation`
org (probably requires 501(c)(3) verification first). Alternative: Open
Collective.
**Blockers:** 501(c)(3) status for tax-deductibility claims.

### Automated release pipeline

**Motivation:** Cutting a release currently means tagging, building the
bundle, uploading to GitHub Releases and the downloads server, and
updating the marketing changelog. Automation reduces the chance of
missing a step.
**Scope:** GitHub Action that on tag push builds the bundle, creates a
Release, uploads the ZIP asset, and posts to the marketing site's
changelog endpoint.
**Blockers:** First manual release to establish the pattern.

---

## Rejected / Deferred

Things that got considered seriously and deliberately left out. Listed
here so they don't get rediscovered and re-proposed.

- **Accepting external PRs.** Explicitly not a project goal — see
  [CONTRIBUTING.md](CONTRIBUTING.md).
- **Gutenberg / block editor support.** SocietyPress is an intentional
  classic-editor + page-builder product. The block editor adds
  complexity senior volunteers don't benefit from.
- **Tailwind or any CSS framework on the marketing theme.** The theme
  is deliberately hand-written with CSS custom properties so it can be
  customized without a toolchain.
- **React-based admin.** Vanilla JS only, for the same reason.

---

## Changing this document

This roadmap is a living document. Items move up, down, or off the
list as the project's priorities evolve. Material changes are noted in
`CHANGELOG.md` under the Unreleased section.
