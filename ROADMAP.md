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

Items that have landed recently. Kept here for a release cycle or two so
visitors can see momentum before being pruned into the changelog.

### ENS migration handbook — the reference half

Shipped 2026-08-27. The migration guide now carries a field-by-field table
of every column an EasyNetSites export contains and what each becomes,
a note on the three columns that do more than they look like — the tie
that makes a couple a couple, the record id that makes a second import
safe, and the date that governs overwriting — and a three-level plan for
backing out: undo the import, restore the site, or start over.

### Roadmap audit — twelve items were already done

Audited 2026-08-27 against the live sites and the source. Twelve entries
were describing work that had already shipped, in some cases months
earlier: site-wide search and the XML sitemap on getsocietypress.org, the
GitHub releases feed on the homepage, the mailing-list signup, Theme
Exchange Tier 2, the Kindred dataset on the demo site, the Hart Island
collection, the 29-guide documentation hub, the FAQ content, the
automated release pipeline, and the two platform features that shipped
the same evening. They have been removed rather than left to be picked
up again.

Seven things genuinely remain, and three of those are not code.

### In-app update checker — verified end to end

Verified 2026-08-27. A newer release on GitHub reaches WordPress's own
update transient, so the "Update available" notice appears in the
Plugins list without anybody visiting GitHub. The release asset
downloads, and extraction goes through WordPress's `unzip_file()`, which
rejects any archive entry pointing outside the target directory — so a
tampered release cannot write over files elsewhere on the site.

### Donations page on getsocietypress.org

Shipped 2026-08-17. The donations page is live and taking card payments
through Stripe, with the no-pressure tone the project has always used —
SocietyPress is free, stays free, and asking is not the same as charging.
`.github/FUNDING.yml` points at it.

### End-user docs hub — five new cross-cutting guides

Shipped 2026-05-03. The Harold-friendly module-guide library at
`getsocietypress.org/docs/modules/` adds five new cross-cutting
guides covering the questions every webmaster asks early:
[Insights](https://getsocietypress.org/docs/modules/?guide=insights),
[Backup & Restore](https://getsocietypress.org/docs/modules/?guide=backup-restore),
[User Access & Roles](https://getsocietypress.org/docs/modules/?guide=user-access),
[Email Setup](https://getsocietypress.org/docs/modules/?guide=email-setup),
and [Privacy & GDPR](https://getsocietypress.org/docs/modules/?guide=privacy-gdpr).
That brings the total to 28 guides spanning every toggleable module
plus the recurring cross-cutting concerns. FAQ links into each
where relevant.

### ENS migration handbook — Decisions section

Shipped 2026-05-03. The full ENS migration walkthrough now includes
an explicit "Decisions you'll make during the import" section
covering joint members (combine vs split), lapsed members, member
numbers, legacy fields, privacy defaults, and cutover timing
(parallel run vs hard switch). Closes the bulk-decision-points
expansion ask from the Documentation section.

### Insights — engagement & use metrics for boards

Shipped 2026-05-03. A single admin/board-only page (SocietyPress →
Insights) that pulls one headline number per enabled module across a
chosen time window — active members, events held, donations raised,
volunteer hours, records added, blasts sent, and so on — with a
sparkline trend on every card. Time-window dropdown supports rolling
30 / 90 / 365 days plus this and last fiscal year (the fiscal-year
boundary reuses the existing membership-start-month setting, so
societies don't configure it twice). Disabled modules are hidden;
permission gate uses the existing `sp_view_reports` capability so a
treasurer or membership chair can be granted access without giving
them broader admin rights.

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

## Dormant

Work that is real, wanted, and not moving right now. Nothing here is
abandoned and nothing here is scheduled — it is parked in one place so
that what is missing from SocietyPress is written down honestly rather
than remembered by one person.

Everything else that was on this roadmap has shipped.

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

### Theme Exchange — Tier 3 (full child themes)

**Motivation:** Tier 1 (design-token presets) and Tier 2 (`.spchildtheme`
bundles) both ship. Tier 3 adds full WordPress child themes through
curated review, with a "Reviewed by SocietyPress" badge for trust.
**Scope:** Submission queue, review checklist, badge, and the policy
that says what gets accepted.
**Blockers:** A written review policy has to exist first.

### 5-minute Getting Started screencast

**Motivation:** Most society administrators learn faster from watching
than from reading. A short video covering install → setup wizard →
first member import → first event closes the "can I actually do this?"
gap.
**Scope:** A 5-minute screencast with voiceover, published to YouTube
and embedded on the homepage and docs landing.
**Blockers:** A clean demo environment and time to record.

### ENS Migration demo walkthrough

**Motivation:** The single most effective sales tool for an ENS society
is a "watch us migrate one" demonstration. Having a live example with
realistic ENS-format data proves the migration works.
**Scope:** Use the ENS-format CSVs in `Sample Data/ENS Migration Demo/`
to perform and document a fresh migration on demo.getsocietypress.org.
Publish the walkthrough as a docs page.
**Blockers:** A decision about the demo site. It already carries the
Kindred dataset — 571 members, 8,149 records — and a walkthrough that
shows a migration arriving on an empty site means replacing that. Either
the demo is rebuilt from the ENS CSVs so the Kindred data *is* the
migrated data, or the walkthrough is recorded somewhere other than the
public demo.

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
