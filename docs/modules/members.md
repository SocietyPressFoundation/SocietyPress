# Members

The roster. Every other module — events, donations, voting, library checkouts — attaches to members. Get this one right and most of the rest follows. This module is always on; you can't disable it.

## What you can do

- Keep a roster with contact info, household relationships, dues status, and renewal dates.
- Import members from a CSV (recognized ENS format included — no manual mapping needed).
- Tier members into different membership levels (Individual, Joint, Lifetime, Honorary, Subscription).
- Auto-send renewal reminders before and after a member lapses.
- Show a public member directory with privacy controls (each member chooses what's visible).
- Track surnames each member is researching for the public Surname Research database.
- Export everyone's data as CSV for backup or to move elsewhere.

## How to add your first member

**SocietyPress → Members → Add New.** Fill in name, email, phone, address. Pick a tier. Set a join date. Save. That's it.

If they should have a login, check "Create WordPress account" on the form. SocietyPress generates a username, sets a temporary password, and emails them the welcome message. Members reset their own password via "Forgot password" on the login page.

## How to import an existing roster

**SocietyPress → Members → Import.** Upload a CSV. SocietyPress reads the column headers and shows you what it'll do — how many rows, the first few mapped to fields, any warnings.

If you're coming from EasyNetSites, export your member roster as CSV from your ENS admin and upload it here. SocietyPress recognizes the ENS column layout (74+ columns) and maps them automatically. Joint members can be combined into one record or split into two linked household members — you pick during import.

For a 200-member society, the import takes about 30 seconds. For 2,000 members, about five minutes.

After import, **always** open three or four random members and verify their data looks right before announcing the new site to your membership.

## How to send a renewal reminder

Renewal reminders run automatically. Out of the box, members get an email 30 days before expiration, 7 days before, and 30 days after (if they haven't renewed yet). Tweak the schedule under **SocietyPress → Settings → Membership → Renewal Reminders**.

To send an ad-hoc reminder to a specific member, open their member record and click "Send Renewal Reminder." This uses the same email template as the automatic schedule but sends it on demand.

## How to set up a member directory page

Two paths:

- **Page builder.** Create a page, drop the "Member Directory" widget on it. Configure visibility (logged-in only, public, etc.), columns shown, and search behavior. Save. Done.
- **Shortcode.** `[sp_directory]` on any page or post. Same widget, no page-builder required.

By default, only members who've opted in to directory listing appear. Each member toggles directory visibility (and per-field privacy: name, address, phone, email, photo) on their own profile.

## How to handle a household

Two members in one household = two member records linked by a `household_id`. Each has their own login (or one shares with the household — your call). Dues can be billed once per household at a "Joint" tier price, or independently at the "Individual" rate.

To link existing members into a household, open one of the member records → "Household" tab → "Add to household." Either create a new household or pick an existing one.

## If something looks wrong

**Import says "X rows skipped."** Open the import log (download link on the import-results page). Common reasons: duplicate email addresses (two members with the same email), missing required fields (no name), or malformed dates. Fix the CSV in your spreadsheet and re-import — the importer can either skip duplicates or update existing members.

**A member says they can't log in.** Have them click "Forgot password" on the login page. Their password (if they had one in ENS or another system) didn't transfer — passwords are hashed and we never see them. They set a fresh password the first time they sign in. This is a feature, not a bug.

**Renewal reminders aren't going out.** Two things to check: (1) the cron is running — visit **SocietyPress → Settings → Email** and click "Send test email" to verify your site can send mail at all; (2) the member actually has an email address on file. The renewal cron skips members without email.

**The member directory shows everyone.** That's the default until members opt in or out. To make it members-only, edit the directory page/widget and set "Visibility" to "Members only." To make individual members private, open their record and untick "Show in directory."

**A member died.** Open their record, set status to "Deceased." This keeps the historical record intact (event registrations, donations) but removes them from active rosters, the directory, and renewal reminders. You can also tick "Family will continue membership" to convert their membership to the surviving spouse.

## Related guides

- [Setup Wizard](setup-wizard.md) — picks the initial dues structure
- [Donations](donations.md) — receipt language and 501(c)(3) settings
- [Voting](voting.md) — tier-based ballot eligibility
- [Help Requests](help-requests.md) — non-members can ask too
