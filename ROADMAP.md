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

## Distribution & Releases

Getting SocietyPress into the hands of actual societies with as little
friction as possible.

### Cut the first tagged GitHub release

**Motivation:** The in-plugin update checker, the marketing site's
changelog feed, and third-party installers all look for GitHub Releases
tagged with semver versions. Without tagged releases, none of those
surface updates.
**Scope:** Tag `v1.0.19` on the current `main`, attach a pre-built
`societypress.zip` bundle and a release note summarizing the changes,
and document the cut-a-release procedure so every version bump produces
one.
**Blockers:** None.

### Softaculous one-click install

**Motivation:** Most of the target audience hosts on cPanel providers
where Softaculous is the default installer. A one-click install cuts
the path from "heard about SocietyPress" to "running on my site" from
an afternoon to three minutes.
**Scope:** Build the Softaculous package per their submission spec
(`info.xml`, screenshots, bundle ZIP layout), validate it with
`scripts/build-softaculous.sh` end-to-end, and submit through the
partner program.
**Blockers:** First tagged GitHub release (the package points at
release assets).

### In-app update checker surfacing

**Motivation:** Once releases exist, WordPress administrators should
see an "Update available" notice inside their dashboard without having
to check GitHub manually.
**Scope:** The plugin already has `SOCIETYPRESS_GITHUB_REPO` wired to
the release-checking endpoint — verify that the notice appears, the
update ZIP downloads, and the extraction is zip-slip-safe.
**Blockers:** Tagged releases.

---

## Marketing Site (getsocietypress.org)

The front door. The templates exist for most of these; many need content
or final polish.

### Comparison page — SocietyPress vs. ENS / Blue Crab / Wild Apricot

**Motivation:** The most common question from societies considering a
move is "how does this compare to what I already have?" A direct
matrix answers it faster than prose.
**Scope:** A new `page-comparison.php` template with a side-by-side
feature matrix, pricing column, and honest "where we're weaker"
section. Anchor links from the migration-pitch pages.
**Blockers:** Research on the competition's current feature set.

### Donations / tip jar page

**Motivation:** SocietyPress is free forever, but voluntary donations
keep the lights on. The `.github/FUNDING.yml` points at this page.
**Scope:** `page-donate.php` with a clear "no pressure" tone, payment
options (Stripe, PayPal, check), and recognition language for
contributors who want it.
**Blockers:** Payment-processing decision (Stripe account, etc.).

### Downloadable PDF info sheets

**Motivation:** Society boards often want a one-page handout to pass
around before making a decision. PDFs travel better than URLs in that
context.
**Scope:** Four one-pagers — one each for administrator, board member,
treasurer, and librarian — covering the features most relevant to that
role.
**Blockers:** None (pure content work).

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

### `security.txt` at `/.well-known/security.txt`

**Motivation:** RFC 9116 standardizes where security researchers look
for disclosure instructions. Having a valid file signals that reports
will be handled professionally.
**Scope:** A static file at `/.well-known/security.txt` pointing at the
SECURITY.md process.
**Blockers:** None.

### Inline GitHub Releases feed on homepage

**Motivation:** Showing activity on the homepage — "v1.0.19 released
three days ago" — signals the project is alive.
**Scope:** Cached fetch of the GitHub Releases API, rendered as a
compact feed in the homepage footer or sidebar.
**Blockers:** Tagged releases.

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

### Complete the Store module's checkout

**Motivation:** The storefront and cart work; checkout and payment
capture are the remaining gap. Societies want to sell publications and
merchandise through their site, not a separate storefront.
**Scope:** Wire up Stripe and PayPal checkout for store orders with the
same flow already used by the join form and event registration. Order
confirmation emails, refund handling, basic stock management.
**Blockers:** None technical; priority call about whether the
checkout-less store is a near-term blocker for any society.

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
