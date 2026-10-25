# User Access & Roles

Let different volunteers do different jobs without giving everyone the keys to everything. The treasurer manages donations without seeing ballots; the librarian runs the catalog without touching the donor list; the membership chair handles the roster without owning the website.

If more than one volunteer works in your site's admin, set this up.

## How it works

SocietyPress has **11 access areas**. Each volunteer gets some combination of them, and sees only the screens those areas cover. A volunteer with just Events sees the Events screens and nothing else.

To save ticking boxes one at a time, there are **8 role templates** — Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager and Content Editor. Pick one and its areas are ticked for you; adjust from there.

WordPress administrators always have full access, and only administrators can give access to others.

## How to give a volunteer access

**Settings → Who Can Do What.**

1. Under **Grant Access to a User**, search for the person by name or email and pick them. Members already have an account; for someone who isn't a member, create one first under **Users → Add New** with the Subscriber role.
2. Choose a **Role Template**, or leave it on **Custom**.
3. Tick or untick **Access Areas** until it fits.
4. For **Content & Pages**, **Which pages?** can narrow them to only the pages you tick — useful for a committee chair who looks after their committee's pages.
5. Click **Save Access**.

Next time they sign in, the sidebar shows only what they can use.

## The 11 access areas

| Area | What it covers |
|---|---|
| **Members** | View and manage members, import and export, groups and membership plans. |
| **Events** | Events, categories, speakers, registrations and the calendar. |
| **Library** | The library catalog, imports and enrichment. |
| **Finances** | Donations, campaigns, store orders and financial reports. |
| **Communications** | Email to members, newsletters, the sent-email log and email templates. |
| **Genealogical Records** | Record collections, record imports and search. |
| **Leadership & Volunteers** | Officers, committees, meetings, ballots, volunteer opportunities and hours. |
| **Content & Pages** | Pages, the media library, resource links, documents, photos and the page builder. |
| **Settings & Design** | SocietyPress settings, colors and fonts, and features. |
| **Reports** | Membership reports, event statistics, financial summaries and Insights. |
| **Paid Research** | Picking up and working paid research cases on the My Research Assignments page. This one doesn't open the admin side — a researcher signs in like any member. |

## The 8 role templates

- **Webmaster** — every area. Still not an administrator: they can't give others access.
- **Membership Manager** — Members and Reports.
- **Treasurer** — Finances and Reports. Doesn't see the member roster.
- **Event Coordinator** — Events.
- **Librarian** — Library.
- **Communications Director** — Communications.
- **Records Manager** — Genealogical Records.
- **Content Editor** — Content & Pages.

## How to change or remove access

On **Settings → Who Can Do What**, find them under **Users with SocietyPress Access**. **Edit** to change their areas; **Revoke** removes all their SocietyPress access. Their account stays, with whatever WordPress role it had.

Deleting the account itself (**Users → All Users**) removes their access too, but also asks what to do with anything they wrote. Revoking is usually what you want.

## Common patterns

**Solo webmaster.** One person does everything as a WordPress administrator. Nothing to set up.

**Webmaster and treasurer.** The webmaster is an administrator; the treasurer gets the Treasurer template.

**A full board.** The webmaster is an administrator. The membership chair gets Membership Manager, the treasurer Treasurer, the program chair Event Coordinator, the newsletter editor Communications Director. Each sees only their own screens.

**Committee chairs.** Chairs get their **My Committee** screen automatically — see [Governance](governance.md). Give them Content & Pages limited to their committee's pages if they should edit those too.

## If something looks wrong

**A volunteer signs in but sees no SocietyPress menus.** They have no access areas yet. Grant them access as above.

**Insights doesn't show for the treasurer.** Insights needs the Reports area. Open their access and check Reports is ticked.

**A volunteer can see member phone numbers.** The Members area shows member records in full; there's no way yet to give someone member access without the private fields.

**An administrator can't be restricted.** That's by design. To restrict someone, change their WordPress role from Administrator to Subscriber under **Users → All Users**, then give them access areas here.

## Related guides

- [Members](members.md) — what the Members area covers
- [Insights](insights.md) — needs the Reports area
- [Governance](governance.md) — committee chairs' access
