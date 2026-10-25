# Governance

Officers, committees, meetings and volunteer hours — who holds which office, who chairs what, when the board last met, and how many hours your volunteers gave this year.

These screens are under **Meetings & Board** (officers, committees, meetings) and **Volunteers** (roster, hours, opportunities) in the sidebar.

## What you can do

- List your officers and board members, and show them on your website automatically.
- Keep a list of committees with their chairs, and assign members to them.
- Give each committee chair a **My Committee** screen covering just their committees.
- Record board, membership and committee meetings, with attendance, notes, and links to the agenda and minutes.
- Track volunteer hours — logged by hand, or automatically when members answer research questions or work on research cases.

## Officers

**Meetings & Board → Officers.** Under **Officers & Board**, click **Add Officer**: find the member, type the position title ("President", "Treasurer"), and optionally a start and end date. Leave the end date blank for an ongoing position. Set an officer to Inactive when their term ends to keep the history.

## Committees

**Meetings & Board → Committees → Add Committee.** Name, an optional description, the chair, a sort order (lower numbers first), and **Active** — inactive committees drop out of the lists and dropdowns.

To put members on a committee, go to **Meetings & Board → Officers**, scroll to **Committees**, and click **Add Committee Assignment**: find the member, choose the committee, and type their role in it ("Member", "Vice-Chair"). The committee's chair is set on the committee itself.

## Showing officers and committees on your website

New sites get a **Leadership** page that lists both, and keeps itself current as you change them. On another page, put `[societypress_officers]` where the officers should go and `[societypress_committees]` for the committees. Officers show with their position; committees show with their chair.

## Committee chairs

Anyone set as a committee's chair gets **Meetings & Board → My Committee** when they sign in. It covers just their committees: upcoming meetings and events, open volunteer opportunities and waitlisted sign-ups, recent minutes, and buttons to **Record a Meeting**, **Add an Event** or **Post a Volunteer Opportunity**. Someone who chairs two committees sees both.

There's no separate role to assign. Chair a committee and the screen appears; stop chairing it and it goes away. Chairs can record meetings only for committees they chair.

## Meetings & minutes

**Meetings & Board → Meetings & Minutes → Record a Meeting.**

- **Meeting Type** — Board, Membership (general meeting), or Committee. A committee meeting also needs its committee.
- **Title** and **Meeting Date**, **Location**.
- **Attendance** — a count, a list of names, or both.
- **Agenda URL** and **Minutes URL** — upload the file under **Website → Files**, then paste its address here.
- **Inline Notes** — a summary, or the full minutes.
- **Visibility** — Public, Members only, or Committee only.

Meetings are kept in the admin for your records and for chairs' dashboards; there is no public page listing them yet, so the Visibility setting doesn't show them anywhere today. Minutes you want members to read belong in [Documents](documents.md), which has members-only access and a Month & year layout made for minutes.

## Volunteer hours

**Volunteers → Volunteer Hours** shows every hour logged, with this year's total and the number of active volunteers at the top.

To log hours by hand, use **Log Hours**: member, activity ("Library shelving"), an optional committee, hours and date.

Some hours log themselves:

- **Research Help answers** — a member answering a question says how long it took, and that time is added.
- **Paid Research cases** — time a researcher records on a case is added.

Signing up for a volunteer opportunity does not log hours; add them afterwards.

**Export CSV** downloads every entry — name, activity, committee, hours and date. For a year's total for a grant report or Form 990, filter the date column in your spreadsheet and sum the hours.

Members can see their own hours for the year on a page with the **My Volunteer Hours** widget (or `[sp_my_volunteer_hours]`), broken down by kind of work. The **Volunteer Stats** widget shows the society's total hours, active volunteers and top contributors.

## If something looks wrong

**A chair can't see My Committee.** Check the committee is Active and that the right person is picked as its chair.

**A member isn't showing as on a committee.** Check their assignment under **Meetings & Board → Officers → Committees** is Active. An assignment saved before committees were chosen from a list may show "(not a current committee)" — pick the right committee and save.

**The Leadership page shows bracketed placeholder text.** Updating to 1.5.58 or later replaces it with the live lists, as long as you haven't rewritten that text yourself. If you have, put `[societypress_officers]` and `[societypress_committees]` back where you want them.

**A committee is missing from dropdowns.** It's marked inactive. Edit it and tick **Active**.

## Related guides

- [Help Requests](help-requests.md) — answering questions logs volunteer hours
- [Research Services](research-services.md) — case hours log here too
- [Voting](voting.md) — ballots for elections and bylaw votes
- [Documents](documents.md) — where minutes for members go
