# Insights

One page that answers the question every board asks: "How are we doing?" It pulls a headline number from every feature you use — active members, events held, volunteer hours, money raised, newsletters published — and shows them side by side for the time window you pick.

Members never see it. Features you've turned off under **Settings → Features** don't appear.

## How to open it

**Reports → How We're Doing.**

If it isn't in your sidebar, your account needs the **Reports** access area. Administrators always have it; for anyone else, see [User Access](user-access.md).

## How to read the page

Each card shows the feature, a big number for the chosen window, a line saying what it counts, and a small line chart showing how it moved across the window.

The numbers count only what's actually recorded. If your records only go back three months, a year's window shows three months' worth.

## How to change the time window

Use **Time window** at the top:

- **Last 30 days** — what's changed since the last board meeting.
- **Last 90 days** — the default; this quarter's trend.
- **Last 365 days** — the past year.
- **This fiscal year** — from the start of your society's year to today.
- **Last fiscal year** — the whole of the previous year, for comparison.

Your society's year starts in the month set as **Period Starts** under **Settings → Membership** (July unless you change it). Every card uses the same window.

## What each card counts

| Feature | Number |
|---|---|
| Members | Active members — members who signed in, or whose page views were logged, during the window |
| Events | Events held |
| Library | Catalog items added |
| Newsletters | Issues published |
| Resource Links | Links added |
| Volunteers | Volunteer hours logged |
| Store | Orders placed |
| Records | Records added |
| Donations | Total raised |
| Email | Emails sent to members |
| Photos | Photos uploaded |
| Research Help | Questions asked |
| Documents | Documents uploaded |
| Voting | Ballots opened |
| Lineage Programs | Applications received |
| Paid Research | Cases opened |

## If something looks wrong

**Active members looks low.** It counts members who signed in during the window. If you also turn on **Log Page Views** under **Settings → Privacy → Activity Logging**, members who stay signed in between visits are counted too. How long those page views are kept is set there as well; a short retention limits how far back the count can see.

**This fiscal year looks wrong.** Check **Period Starts** under **Settings → Membership**. Many societies run July to June; others use the calendar year.

**A feature you use isn't showing.** Check it's turned on under **Settings → Features**.

**Total raised looks too low.** Failed, pending, cancelled and refunded donations aren't counted. A card payment that stays pending usually means Stripe couldn't reach your site to confirm it — check the Webhook Signing Secret under **Settings → Events → Online Payments**, and look for the gift under **Money → Donations**.

## Related guides

- [User Access](user-access.md) — who can see this page
- [Donations](donations.md) — what counts toward Total raised
- [Governance](governance.md) — how volunteer hours are logged
