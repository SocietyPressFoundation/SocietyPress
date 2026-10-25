# Members

The roster. Every other module — events, donations, voting, library checkouts — attaches to members. Get this one right and most of the rest follows. This module is always on; you can't disable it.

## What you can do

- Keep a roster with contact info, household relationships, dues status, and renewal dates.
- Import members from a CSV (recognized ENS format included — no manual mapping needed).
- Put members on membership plans. Five come ready-made — Individual, Joint/Family, Student, Lifetime, Honorary — and you can add your own.
- Email renewal reminders automatically before a membership expires.
- Show a public member directory with privacy controls (each member chooses what's visible).
- Track surnames each member is researching for the public Surname Research database.
- Export everyone's data as CSV for backup or to move elsewhere.

## How to add your first member

**Members → Add Member.** Fill in name, email, phone, address. Pick a tier. Set a join date. Save. That's it.

For a birthday, use **Date of Birth** if you know the year. If the member would rather not give their age, leave it empty and pick just the month and day underneath — they'll still get the "Birthdays this month" email. Members can set either one themselves in My Account.

If they should have a login, check "Create WordPress account" on the form. SocietyPress generates a username, sets a temporary password, and emails them the welcome message. Members reset their own password via "Forgot password" on the login page.

## How to import an existing roster

**Members → Import Members.** The first time, it asks you to add yourself as a member — use the email address your old system has for you, so the import recognizes your own row. Then upload a CSV. SocietyPress reads the column headers, matches each one to a field, and shows you a sample value beside each match and the first five rows underneath.

If you're coming from EasyNetSites, export your member roster as CSV from your ENS admin and upload it here. SocietyPress recognizes the ENS column layout (87 columns) and maps them automatically. Each spouse arrives as their own member, linked into one household. Before anything is written, it asks you to confirm any row that matches an account already on the site — on a new site, that's you.

Importing the same file again is safe: members it already brought in are updated, not duplicated. That's also how photos get attached — upload the photo files to `wp-content/uploads/sp-profile-photos/` and import again. The [ENS migration guide](../ENS-MIGRATION-GUIDE.md) covers every column and every step.

For a 200-member society, the import takes about 30 seconds. For 2,000 members, about five minutes.

After import, **always** open three or four random members and verify their data looks right before announcing the new site to your membership.

## How to send a renewal reminder

Renewal reminders run automatically. Out of the box, each member gets an email 30 days, 15 days and 7 days before their membership expires — once each. Turn any of them off, and change the subject line, under **Settings → Membership → Renewal Reminders**. An optional "your membership has expired" email can be switched on in the same place.

There's no button to send one member a reminder on demand. To nudge someone, email them, or use **Blast Email** with the "Renewal due" audience to reach everyone expiring in the next 30 days.

## How to set up a member directory page

Two paths:

- **The Directory page.** SocietyPress creates a page called Directory, when it is first switched on, that shows the full membership directory. It's members-only by design: visitors who aren't signed in don't see it, and it's left out of the menu for them.
- **Page builder.** Create a page and drop the **Member Directory** widget on it. Tick **Require login to view** to keep it to members, and set how many results show per page.

Every active member is listed by default. A member leaves the directory by unticking **Show name** — on their record under **Preferences → Directory Visibility**, or themselves in My Account — and the same place has separate choices for address, phone, email, website and photo. **Settings → Directory** decides which of those columns the directory shows at all, whatever members choose.

## How to handle a household

Two members in one household = two member records with the same **Household ID**. Each has their own sign-in. Dues can be paid once at a Joint/Family price, or each at the Individual rate.

To link existing members, open each record and give them the same number in **Household ID**, in the Membership section. To add a second person while you're creating a member, tick **Joint Membership (couple/household)** and fill in their name.

## If something looks wrong

**Import says "X rows skipped."** The import results list each skipped row and why underneath the totals. Common reasons: a member already on the site with the same email and name, or a row missing a name. Fix the CSV in your spreadsheet and import it again — members already brought in are updated, not duplicated.

**Some imported members have no photo.** The import results say how many photos named in your file weren't found, with examples. Upload those files to `wp-content/uploads/sp-profile-photos/`, keeping their names exactly as they were, and import the same file again.

**A member says they can't log in.** Have them click "Forgot password" on the login page. Their password (if they had one in ENS or another system) didn't transfer — passwords are hashed and we never see them. They set a fresh password the first time they sign in. This is a feature, not a bug.

**Renewal reminders aren't going out.** Three things to check: (1) the reminders are switched on under **Settings → Membership → Renewal Reminders**; (2) your site can send email at all — the quickest test is "Lost your password?" on the sign-in page with your own address; if that email never arrives, see [Email Setup](email-setup.md); (3) the member actually has an email address on file. Each reminder goes to each member only once.

**The member directory shows everyone.** That's the default: every active member is listed. The Directory page SocietyPress made is already members-only; on a page-builder directory, tick **Require login to view**. To take one member out, open their record and untick **Show name** under **Preferences → Directory Visibility**.

**A member died.** Open their record, set status to "Deceased." This keeps the historical record intact (event registrations, donations) but removes them from active rosters, the directory, and renewal reminders. If a spouse is carrying on, their own record stays as it is — check their plan and expiration date.

## Related guides

- [Setup Wizard](setup-wizard.md) — picks the initial dues structure
- [Donations](donations.md) — receipt language and 501(c)(3) settings
- [Voting](voting.md) — tier-based ballot eligibility
- [Help Requests](help-requests.md) — non-members can ask too
