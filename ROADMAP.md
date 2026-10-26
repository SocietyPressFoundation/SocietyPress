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

### Paid research is for researchers — 1.5.63

Open paid research cases, and the button to claim one, are now limited to
people ticked for the new **Paid Research** area under Who Can Do What. The
tick doesn't open the admin side, so researchers keep signing in like any
member.

### Module guides checked against the software — 1.5.57 to 1.5.61

Every guide under `docs/modules/` was read beside the screens it describes and
corrected to what the software does, with menu paths matching the sidebar's
groups. Bugs found along the way were fixed in the same releases rather than
written around: ballots that never closed, a Voting page type no one could
choose, feature widgets the page builder never offered, a grace period that was
never applied, and others listed in the changelog. Gaps that need building are
the new items below.

Items that have landed recently. Kept here for a release cycle or two so
visitors can see momentum before being pruned into the changelog.

### ENS migration walkthrough — published

Shipped 2026-10-16 at
[societypress.net/docs/ens-migration-walkthrough/](https://societypress.net/docs/ens-migration-walkthrough/).
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
earlier: site-wide search and the XML sitemap on societypress.net, the
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

## Next up

Work that is wanted and unblocked, by priority. Collected 2026-10-21 from
every SocietyPress working session since 1.5.0 and checked against the code,
so that nothing lives only in a conversation. When a session leaves something
unfinished, it goes here or in [`docs/KNOWN-ISSUES.md`](docs/KNOWN-ISSUES.md)
before the session ends.

### High

#### Surveys, rebuilt from scratch

**Motivation:** The survey editor works but asks a volunteer to think in
form-builder terms. Surveys should be something a non-technical officer can
build and read without help.
**Scope:** A complete redesign, started fresh (decided 2026-10-25). The
`wip/survey-editor-redesign` branch is kept for reference only. Questions to
settle first, one at a time: whether surveys get their own tool or keep sharing
the Forms builder; who can answer (members only, the public, chosen groups);
and what happens to surveys already on existing sites.
**Blockers:** The design questions above.

#### Handle admin form saves before the page renders

**Motivation:** About thirty admin screens save their forms inside the
function that draws the screen, after output has begun. 1.5.x holds the
response open so the redirect after a save still works, but that is a floor
under the symptom, not the fix.
**Scope:** Move each screen's POST handling to `admin_init` or
`load-{$page_hook}`, one screen group at a time with a test after each. The
diagnosis and the rule against widening the buffer are in
[`docs/KNOWN-ISSUES.md`](docs/KNOWN-ISSUES.md). Also still owed: one person
saving a real form and confirming the debug log stays clean.
**Blockers:** None.

#### Secret ballots

**Motivation:** Many bylaws require board elections by secret ballot. Today
every vote is stored with the member who cast it (`sp_ballot_votes.user_id`),
so a society bound by that rule can't run its election online. Members never
see who voted for what, but anyone with database access could.
**Scope:** A "Secret ballot" choice on the ballot. One-vote-per-member stays
enforced through `sp_ballot_voters`, which records only *that* someone voted;
the votes themselves are written with no member attached, and nothing on the
results screen, CSV export or privacy export can join them back.
**Blockers:** None.

#### Privacy export and erase: research cases, store orders, ballots

**Motivation:** WordPress's Export and Erase Personal Data tools cover most of
what SocietyPress holds, but not paid research cases (requester name, email
and case details), store orders (customer name, email and address) or ballot
votes (which record the voter). A society answering a deletion request has to
find those by hand.
**Scope:** Exporters and erasers for the three, following the donations
pattern: keep what the books need (order totals, case fees, vote counts),
remove what identifies the person.
**Blockers:** None.

#### Restore from a backup

**Motivation:** Scheduled backups capture everything a society needs —
SocietyPress data, sign-in accounts, settings and uploaded files — but getting
it back means phpMyAdmin, matching table prefixes and copying wp-config keys by
hand. The one-click Site Export leaves out sign-in accounts entirely. A
volunteer facing a dead host needs a button.
**Scope:** "Restore a backup" on Settings → Export & Backup for a fresh
install: upload a backup ZIP, check it, load the tables (rewriting the table
prefix), settings and uploaded files, and re-encrypt member fields under the
new site's key, so a backup moves between hosts without the old wp-config.
**Blockers:** None.

### Medium

#### Meetings and minutes on the public site

**Motivation:** Each meeting has a Visibility setting — Public, Members only,
Committee only — but nothing on the public site lists meetings, so the
setting does nothing. Societies that record minutes on the Meetings screen
have to upload them again under Documents for members to read them.
**Scope:** A Meetings page type (and page-builder widget) listing past
meetings by type and year, with agenda and minutes links and inline notes,
honoring each meeting's visibility, and offered by the Meetings screen's
"make the page for me" banner.
**Blockers:** None.

#### Send a test email

**Motivation:** "Is my site's email working?" is the first question behind
most "nobody got the reminder" problems, and seven guides once sent readers to
a Send test email button that doesn't exist. Today the only test is asking for
a password reset.
**Scope:** A button on a settings screen that sends a test message to the
person clicking it and reports whether WordPress accepted it, with the same
"from" name members' emails use.
**Blockers:** None.

#### Paid Research Services: set your own rate

**Motivation:** Every new paid research request is quoted at $30 an hour,
for one hour up front, with a 14-day response time. An admin can change the
terms on each case afterwards, but not the starting terms. The code reads `research_default_rate`,
`research_default_max_hours` and `research_sla_days`, but no screen sets them.
**Scope:** Put the three on a settings screen. The intake form's quote and
every new case already use them.
**Blockers:** None.

#### Research Help: moderation and limits

**Motivation:** A society facing spam can't hold questions for a moderator or
change the three-a-day limit. The code already reads
`help_requests_require_approval` and `help_requests_per_email_per_day`, but
no screen sets them.
**Scope:** Put the approval switch and the daily limit on a settings screen.
**Blockers:** None.

#### Donation receipts: the tax-exempt statement

**Motivation:** A donor claiming a deduction needs the society's EIN and a
501(c)(3) statement on the receipt. The receipt code prints them when
`tax_id` is set, but no screen sets it, so no receipt has ever carried them.
**Scope:** An EIN box on **Settings → Organization**, and confirm the
statement appears on the emailed and printable receipts.
**Blockers:** None.

#### Picture Wall: let an album accept submissions

**Motivation:** The Picture Wall submission form only accepts photos into an
album marked to take them, and the album screen has no way to mark one, so
the form never has anywhere to send a photo.
**Scope:** "Accept member submissions" and submission type on the album edit
screen, writing the existing `accepts_submissions` and `submission_type`
fields.
**Blockers:** None.

#### Join form: volunteering, print by mail, how they heard about us

**Motivation:** Three questions on nearly every society's paper application
are missing from the online one, so a society moving online loses them.
**Scope:** An optional "Willing to volunteer?" checkbox that reveals a skills
box (writes the existing `volunteer` and `skills` columns); "I'd like the
printed journal by mail" (writes the existing `receive_print` column); and
"How did you hear about us?" with an Other box, kept in member meta. Also an optional "I agree to the privacy policy" checkbox, linked to the
page set under Settings → Privacy, with the date of agreement kept on the
member record. Built
once on a pre-1.5 branch in PR #5 and never merged; rebuild it on the current
join form rather than merging that.
**Blockers:** None.

#### AI assistant and problem reports

**Motivation:** Officers answer the same questions by email — when the next
meeting is, whether the library has anything on a county, what membership
costs — and volunteers ask the same how-do-I questions of whoever set the
site up.
**Scope:** An optional module, built once on a pre-1.5 branch in PR #7 and
never merged; rebuild it on current code from that design:
- A public assistant that answers from the site's own search results, never
  from memory, and says "contact the society" rather than guess. The member
  directory is excluded entirely; retrieval goes through
  `sp_do_unified_search()` so a visitor can never surface members-only content.
- "Ask SocietyPress" for volunteers: how-to answers based on the modules
  that are on and the screens that person can actually open.
- "Report a Problem": files a fault from the page it happened on with the
  technical details captured automatically and no IP address, into a queue
  under Settings, with an optional button to send it to GitHub that never
  includes the reporter's name or email.
- The society supplies its own Anthropic key, stored encrypted or set in
  `wp-config.php`; per-person and per-day caps; every exchange logged with
  its cost; both features covered by the privacy export and erase tools.
**Blockers:** None. The Anthropic round trip, the GitHub push and the upgrade
path were never run against a live site on the old branch; they are part of
the rebuild.

#### Notes between admins

**Motivation:** Volunteers run a society in shifts and rarely meet. There is
nowhere in the admin to leave "waiting on the treasurer to confirm the
refund" or "don't send the newsletter until Friday" for whoever signs in
next, so it happens by email and gets lost.
**Scope:** A simple notes board in the admin, visible only to staff: post a
note, see who wrote it and when, mark it resolved or delete it. The newest
open notes show on the dashboard. Never on the public site, gated behind an
admin capability, and deliberately not a ticketing system. First written up
in PR #6.
**Blockers:** None.

#### iPad kiosk

**Motivation:** At a seminar door or a library desk, a society needs to sign
people in, take walk-in registrations and collect dues on a tablet, often
with poor Wi-Fi.
**Scope:** A native iPadOS app for event registration and check-in, library
visitor sign-in, and join or renew with card-present payment through a Stripe
reader; offline for everything except the payment itself. The design spec is
in draft PR #12 (`docs/KIOSK-SPEC.md`), including the open questions it
deliberately leaves to be decided.
**Blockers:** The spec's open questions — attended or unattended use, idle
timeout, receipts and printing, App Store distribution, identity check on
renewal.

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

Everything else that was on this roadmap has shipped or is listed under
Next up.

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
