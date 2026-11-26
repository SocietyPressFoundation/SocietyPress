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

### Backups copied to Google Drive — 1.5.185 and 1.5.186

Each backup is also copied into the society's own Google Drive, keeping as
many copies as "Keep this many". A volunteer connects once on the Export &
Backup page. Sign-in goes through a relay on societypress.net that holds
Google's app secret and stores nothing; files go straight from the site to
Google. SocietyPress sees only the files it creates. Tested end to end on
the demo 2026-11-26, and the Google app was published the same day. Dropbox
and OneDrive maybe later.

### AI Assistant — 1.5.176

"Ask the Society" answers visitors from the site's own calendar, membership
levels, library and pages, and says "contact the society" rather than guess;
"Ask SocietyPress" walks volunteers through how-to questions for the features
and screens they actually have. Each society brings its own Anthropic key;
Claude Haiku 5.5 by default, with per-visitor and per-day caps and a cost
summary on the settings screen.

### Report a Problem, and Send to SocietyPress — 1.5.175

Whoever looks after a site can file a Help Desk ticket from any page: a
"Report a Problem" link in the admin sidebar, and a small corner button on the
public site that only they see. The page address, window size and modules are
recorded with it. A ticket that looks like a SocietyPress fault can be sent to
the SocietyPress team in one click, without the reporter's name or email,
into a private queue on societypress.net rather than public GitHub issues.

### Add a list of names to a group or committee — 1.5.174

A group's or committee's screen takes a whole list of names at once — typed
or pasted one per line as "Mary Smith", "Smith, Mary" or an email address.
A check screen shows who was found, which names belong to more than one
member (pick the right one or skip it), and which matched nobody, before
anyone is added. Only active members are matched.

### Ask a question only after a certain answer — 1.5.166

A question can be asked only when someone picks certain answers to an earlier
multiple-choice, checkbox, drop down menu or picture question. It appears the
moment that answer is given; otherwise it is skipped, never required, and left
out of that person's answers.

### Forms & Surveys, rebuilt from scratch — 1.5.159 to 1.5.165

One builder for forms and surveys replaces the two old tools. You design it as
people will see it, with fifteen kinds of question, sections and paste-in
questions. It opens as a page on the site, can be emailed to members with
personal links and reminders, and has a Results screen with a summary, Compare
groups, each answer one at a time, a printable version and a spreadsheet.

### Fuller daily check-in — 1.5.163

Each site reports its versions, theme, features in use, active member count and
host once a day. societypress.net's Societies report reads it.

### Preview page changes and optional page approval — 1.5.158

### Meetings and minutes on the public site — 1.5.156

A Meetings & Minutes page type (offered from the Meetings screen) and
page-builder widget list past meetings with agenda, minutes and notes, filtered
by type and year, each honoring its Visibility setting.

### More join form questions — 1.5.155

Willing to volunteer (with skills), printed journal by mail (only when the
society mails one), how did you hear about us (editable drop down menu plus
Other), and an optional privacy policy agreement with its date kept. Each is
switched on or off under Join Form Questions; answers show on the member record.

### Notepad on the dashboard — 1.5.154

The notes board already existed (Notepad); its dashboard panel had been left
behind when the dashboard moved to cards. A Notepad card now opens the
dashboard with the five newest open notes.

### Picture Wall albums take member photos — 1.5.153

The album edit screen can switch an album on for member submissions, with
instructions shown above the form.

### Send a test email — 1.5.152

Settings → Organization → Sending Email: a Send a Test Email button that mails
the person clicking and reports what the mail server said, beside the sender
name and address fields (previously set only in the setup wizard).

### Research Requests settings — 1.5.151

Research Help moderation and daily limit, and the starting rate, hours paid up
front and response days for paid research, are now on Settings → Organization.
The tax-exempt statement on donation receipts was already wired to the EIN box
there; checked on the emailed receipt and the thank-you page.

### Restore from a backup — 1.5.145 and 1.5.146

Backups now hold the whole site (every table, plugins, themes, and the key to members' phone numbers and addresses), and Settings → Export & Backup can restore one — on the same site or a new host — replacing everything after first backing up the site as it is. Decisions made with Charles on 2026-11-18: replace rather than merge, works on a site with data, the person restoring always stays signed in, the key travels in the backup. Tested on the demo site through the real screen and onto a scratch install with a different prefix, keys and address.

### Admin screens save before they draw — 1.5.142 to 1.5.144

Every SocietyPress admin screen now handles its saves and redirects before WordPress starts the page, the standard way, instead of relying on the page being held open.

### Privacy export and erase for research, store orders and voting — 1.5.141

WordPress's privacy tools now find research requests, store orders and votes. Erasing keeps the money and the results, and removes the person.

### Secret ballots — 1.5.140

A ballot can be marked secret. The record of who voted is kept, so members still can't vote twice, but the votes themselves are stored with no member and no time attached.

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

Nothing else at high priority right now.

### Medium

#### iPad kiosk

**Motivation:** At a seminar door or a library desk, a society needs to sign
people in, take walk-in registrations and collect dues on a tablet, often
with poor Wi-Fi.
**Scope:** A native iPadOS app for event registration and check-in, library
visitor sign-in, and join or renew with card-present payment through a Stripe
reader; offline for everything except the payment itself. The design spec is
in draft PR #12 (`docs/KIOSK-SPEC.md`). Its open questions were settled
2026-11-25. Attended or unattended, the idle timeout, and which jobs it does
are each set per iPad. Receipts go on screen, by email, and optionally by
text through the society's own texting account. The app is on the public App
Store. Renewal is confirmed with a one-time code sent by email.
**Blockers:** None for the WordPress side and the app in the simulator.
Testing with a real Stripe card reader needs the hardware, and an App Store
listing needs an Apple Developer account.

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
