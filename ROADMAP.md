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

### ENS migration walkthrough — published

Shipped 2026-10-16 at
[getsocietypress.org/docs/ens-migration-walkthrough/](https://getsocietypress.org/docs/ens-migration-walkthrough/).
One complete ENS import of an invented 22-member society, run on a clean
1.5.51 install, with screenshots of every step. The member file is rebuilt by
`scripts/generate-ens-walkthrough-fixture.py`, so anybody can repeat the run
and check the numbers. Re-running it turned up four defects, fixed in 1.5.51:
the importing webmaster was added twice, the Import screen's "Add Yourself"
button didn't link to the webmaster's account, the email entered there was
discarded, and editing an imported snowbird erased their seasonal dates.

The docs pages it runs on now render tables, images, and wrapped list items,
and link documents to one another. The migration guide's field-by-field
reference and six module guides had been showing their tables as raw pipes.

### SocietyPress 1.5.0 — the first finished release

Released 2026-08-28 as [v1.5.0](https://github.com/SocietyPressFoundation/SocietyPress/releases/tag/v1.5.0).
Eighteen optional modules, five child themes, the ENS migration path, and the Theme
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

## Submitted — waiting on someone else

Work that is finished on this side and sitting in another organization's
queue. Nothing here needs doing; it needs answering. Listed separately from
Dormant so that "not moving" is not mistaken for "not started" — both of
these were mistaken for unstarted work on 2026-08-29, which is why the
section exists.

### GitHub Sponsors / Open Collective

**Motivation:** Recurring sponsorship smooths income volatility and
gives sponsors a visible recognition channel.
**Scope:** Enable GitHub Sponsors on the `SocietyPressFoundation`
org, then add a `github:` line to `.github/FUNDING.yml` so the Sponsor
button appears on the repository. Alternative: Open Collective.
**Status:** Enrolled 2026-08-29, pending GitHub's review. The org is the
sponsored account; the Stripe payee is registered as an **individual**,
because the Foundation does not legally exist and has no EIN. Eligibility
was checked against GitHub's documentation on 2026-08-27: an organization
qualifies by contributing to open source and operating in a supported
region — 501(c)(3) status is not required, and the payout account may be a
personal one. What the 501(c)(3) does gate is the claim that a sponsorship
is tax-deductible, which the profile must not make until the Foundation
exists.
Still pending on 2026-10-15 with every dashboard step complete; asked
GitHub Support why in ticket **#4864889**.
**On approval:** add `github: SocietyPressFoundation` to
`.github/FUNDING.yml` beside the existing `custom:` line. Adding it before
approval means the Sponsor button silently fails to render.

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
**Status:** Tabled 2026-10-15. Softaculous answered ticket #992945 wanting
to list SocietyPress as a WordPress plugin rather than as its own platform.
That framing is not settled, so the submission is on hold until it is.

### 5-minute Getting Started screencast

**Motivation:** Most society administrators learn faster from watching
than from reading. A short video covering install → setup wizard →
first member import → first event closes the "can I actually do this?"
gap.
**Scope:** A 5-minute screencast with voiceover, published to YouTube
and embedded on the homepage and docs landing.
**Blockers:** A clean demo environment and time to record.

### Incorporate the SocietyPress Foundation as a 501(c)(3)

**Motivation:** SocietyPress as "a project Charles runs" is vulnerable
in ways a 501(c)(3) isn't. A foundation creates a legal home for the
project independent of any one person, enables donations to be
tax-deductible, qualifies for GitHub's nonprofit program, and gives
societies confidence in long-term sustainability.
**Scope:** Incorporation as a Texas nonprofit, IRS Form 1023 or 1023-EZ
filing, bylaws, board composition, fiscal sponsor arrangement if
appropriate.
**Blockers:** On hold until the founding board is chosen. The people come
first; the paperwork follows them.

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
