# User Access & Roles

The mechanism for letting different volunteers do different jobs without giving everyone the keys to everything. The treasurer manages donations without seeing voting ballots; the librarian runs the catalog without touching the donor list; the membership chair handles the roster without owning the website.

This is one of the most-asked-about features at conferences and one of the easiest to set up. If your society has more than one volunteer touching the website, you want this configured.

## How it works in two paragraphs

SocietyPress defines **10 access areas** — Members, Events, Library, Finances, Communications, Records, Governance, Content, Settings, Reports. Each volunteer gets some combination of those areas. They see only the SocietyPress menus that match. A volunteer with just "Events" sees the Events admin pages and nothing else; the rest of the SocietyPress sidebar is invisible.

Setting access one checkbox at a time would be tedious, so SocietyPress ships **8 role templates** — Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager, Content Editor. Pick a template and the right access areas are pre-checked. Adjust as needed. WordPress administrators (anyone with `manage_options`) always have full access — these templates layer on top.

## How to grant access to a volunteer

**SocietyPress → User Access → Add User Access** (or open an existing volunteer's row in the User Access list).

1. Pick the WordPress user. They need an account already; create one via the standard **Users → Add New** if not.
2. Pick a **role template** as a starting point (Treasurer, Librarian, etc.). The matching access areas check themselves.
3. Adjust by hand. Tick or untick individual access areas if the template doesn't quite fit.
4. Save.

The volunteer logs in. The SocietyPress sidebar shows only the areas they have access to. They never see admin pages they aren't authorized for.

## The 10 access areas, in plain English

| Area | What they can do |
|---|---|
| **Members** | View and manage all members, run imports/exports, manage groups and membership tiers. |
| **Events** | Create and manage events, categories, speakers, registrations, the calendar. |
| **Library** | Manage the library catalog, run imports, configure enrichment. |
| **Finances** | Donations, campaigns, store orders, financial reports. |
| **Communications** | Blast email, newsletters, the email log, email templates. |
| **Records** | Genealogical record collections, record imports, search configuration. |
| **Governance** | Officer positions, committees, volunteer opportunities, hours tracking. |
| **Content** | Pages, media library, resources, documents, gallery, the page builder. |
| **Settings** | All plugin settings, the design system, modules, user access management. |
| **Reports** | Membership reports, event statistics, financial summaries, the Insights page. |

## The 8 role templates, with what they're for

- **Webmaster** — full access to everything. Equivalent to a site administrator without granting WordPress's `manage_options` capability.
- **Membership Manager** — Members + Reports. Runs the roster, imports new members, sees membership reports for the board.
- **Treasurer** — Finances + Reports. Records cash and check donations, reconciles Stripe and PayPal, runs the donor reports for board meetings. Doesn't see the membership roster.
- **Event Coordinator** — Events. Creates events, manages registrations and speakers. Doesn't see donations or the member directory.
- **Librarian** — Library. Manages the catalog, runs imports. Doesn't touch events or members.
- **Communications Director** — Communications. Sends blast emails, manages newsletter archive, edits email templates. Doesn't see the donor list (member emails for blast emails come through the system without exposing the donor records).
- **Records Manager** — Records. Manages genealogical record collections and imports. Specialized role for societies with active records publishing.
- **Content Editor** — Content. Manages pages, resources, documents, gallery, media. Closest WordPress equivalent: Editor.

## How to revoke or change access

Same page (**SocietyPress → User Access**), edit the row, change the template or untick areas, save. To fully revoke: click the row's **Revoke** action. This removes the SocietyPress access areas; the underlying WordPress user account still exists with whatever WordPress role you set there.

If you delete the WordPress user account itself (Users → Delete), their SocietyPress access goes with them — but so does any content they authored. Consider revoking SocietyPress access and changing them to a "Subscriber" WordPress role instead.

## Common patterns

**Solo webmaster.** You have one person doing everything. Skip user access entirely; their WordPress administrator account already has full SocietyPress access.

**Webmaster + treasurer.** Two volunteers. Webmaster is a WordPress administrator. Treasurer gets a WordPress Subscriber account and the Treasurer role template.

**Full board with delegated roles.** President is webmaster (admin). Membership chair gets Membership Manager. Treasurer gets Treasurer. Event chair gets Event Coordinator. Newsletter editor gets Communications Director. Each one sees only their lane.

**Researcher access without admin.** Want a member to help curate the records collection without giving them broader admin? Give them the Records Manager template. They'll see only **SocietyPress → Records** and nothing else.

## If something looks wrong

**A volunteer can log in but doesn't see SocietyPress in the sidebar.** Their WordPress account exists but they have no SocietyPress access areas assigned. Open **SocietyPress → User Access → Add User Access**, pick their WordPress user, pick a role template, save. The sidebar appears on their next page load.

**The Insights menu doesn't show for the treasurer.** The treasurer needs the **Reports** access area, which the Treasurer template includes by default. Verify by opening their User Access record. If Reports isn't ticked, tick it and save.

**A volunteer reports they can see member phone numbers and you didn't expect that.** The Members access area exposes member records in full — that's what it's for. If you want a volunteer who can run member imports but not see private fields, that's not currently a configurable distinction. The two paths are: (1) trust the volunteer with full Members access, or (2) take the Members access away and have the webmaster handle imports.

**WordPress role keeps reverting after I change it.** You're probably editing the wrong field. SocietyPress access areas live under **SocietyPress → User Access**, not under **Users → All Users → Edit**. The two are separate systems that layer on each other.

**A WordPress administrator can't be restricted.** Correct. WordPress administrators (`manage_options`) always have full SocietyPress access; that's by design. To restrict an account, change it to a non-administrator WordPress role first (Subscriber is the most restrictive), then assign SocietyPress access areas.

## Related guides

- [Members](members.md) — what someone with the Members access area can do
- [Insights](insights.md) — gated by the Reports access area
- [Setup Wizard](setup-wizard.md) — how to seed the first admin during install
- [FAQ: role templates and access areas](https://getsocietypress.org/docs/faq/) — short version on the marketing site
