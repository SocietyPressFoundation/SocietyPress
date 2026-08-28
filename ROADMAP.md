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

### SocietyPress 1.5.0 — the first finished release

Released 2026-08-28 as [v1.5.0](https://github.com/SocietyPressFoundation/SocietyPress/releases/tag/v1.5.0).
Sixteen modules, five child themes, the ENS migration path, and the Theme
Exchange with all three tiers open. The number is 1.5.0 rather than 1.0
because 1.0.0 and 1.0.1 were already published tags from May and June — and
because a release numbered below the 1.1.x builds already in the field would
read as a downgrade and never be offered to them.

The changelog starts again at 1.5.0; the development line is archived in
`CHANGELOG-1.x.md`.

### Theme Exchange — Tier 3 (full child themes)

Shipped 2026-08-28. The Exchange's top tier is open. A published review
policy states what is accepted, what is refused, and what the "Reviewed
by SocietyPress" badge is a statement about — safety, and nothing else.
Submissions are GitHub issues on the org against a checklist drawn from
the policy, so the queue is public and its wait times are visible without
anything being built to display them. Anybody may submit; the gate is a
named, answerable author rather than a credential, with one open
submission per author at a time and the right to decline a review without
it counting as a rejection. Societies install a reviewed theme by
downloading its `.zip` and uploading it through WordPress; one-click
install from inside the plugin waits until the process has seen real
submissions.

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

For everything that shipped before 1.5.0, see
[CHANGELOG-1.x.md](CHANGELOG-1.x.md) and
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

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
org, then add a `github:` line to `.github/FUNDING.yml` so the Sponsor
button appears on the repository. Alternative: Open Collective.
**Blockers:** None. Checked against GitHub's documentation on
2026-08-27: an organization qualifies by contributing to open source and
operating in a supported region — 501(c)(3) status is not required, and
the payout account may be a personal one. What the 501(c)(3) does gate is
the claim that a sponsorship is tax-deductible, which the profile must
not make until the Foundation exists.

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
