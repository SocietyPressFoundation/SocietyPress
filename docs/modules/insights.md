# Insights

A single page that answers the question every board asks at every meeting: "How alive is our society right now?" Insights pulls the engagement and use numbers out of every enabled module — active members, events held, volunteer hours, donations raised, newsletters published — and shows them all on one screen across whatever time window you pick.

This is admin/board-only. Members never see it. Disabled modules are hidden, not greyed out — if you've turned off the Store module in **SocietyPress → Settings → Modules**, the Insights page acts as if the Store doesn't exist.

## What you can do

- See every active module's headline number on one page.
- Compare any two periods by switching the time window — last 30 days, last 90, last 365, this fiscal year, last fiscal year.
- Spot trends without reading anything: each card has a tiny line chart (a "sparkline") showing how the number moved across the window.
- Bring meaningful numbers to a board meeting without exporting a single CSV.

## How to open Insights

**SocietyPress → Insights** in the left sidebar. That's it.

If you don't see the menu item, the user account you're logged into doesn't have the **Reports** access area. Site administrators always do. To grant it to another volunteer (a board treasurer, for example), open their user record under **SocietyPress → User Access**, check **Reports**, and save.

## How to read the page

Each card shows four things:

1. **The module name** at the top, with its dashboard icon.
2. **A big number** — the headline metric for the chosen time window.
3. **A short label** under the number telling you what it counts.
4. **A sparkline** along the bottom — a line chart compressed to a thumbnail. Up-and-to-the-right is good; flat is "no activity"; a recent dip means whatever you tried last quarter slowed down.

The numbers are honest. If your sp_access_log was pruned to 90 days for privacy, asking for "last 365 days" of active members shows what's actually in the log, not a fabricated longer history. If donations were $0 in the chosen window, the card shows $0.

## How to change the time window

Top-left dropdown labeled **Time window**. Pick one:

- **Last 30 days** — the rolling-month view. Good for "what changed since our last board meeting."
- **Last 90 days** — the default. Good for "are we trending up or down this quarter?"
- **Last 365 days** — the rolling-year view. Good for "how does this year compare to last year?"
- **This fiscal year** — January 1 to today, or whatever fiscal-year-start month you set under **SocietyPress → Settings → Membership**. Good for board-meeting reports.
- **Last fiscal year** — the same window, one year earlier. Good for year-over-year comparisons.

The window applies to every card on the page at once. There's no per-card override and there doesn't need to be.

## Which numbers does each module show

| Module | Headline number |
|---|---|
| Members | Active members (anyone who logged into the site in the window) |
| Events | Events held |
| Library | Catalog items added |
| Newsletters | Issues published |
| Resources | Resources added |
| Governance | Volunteer hours logged |
| Store | Orders placed |
| Records | Records added |
| Donations | Total raised |
| Blast Email | Blasts sent |
| Gallery | Photos uploaded |
| Help Requests | Research help requests |
| Documents | Documents uploaded |
| Voting | Ballots opened |
| Lineage | Applications received |
| Research Services | Cases opened |

If a module is disabled, its row doesn't appear.

## If something looks wrong

**Active Members shows zero, but I know members are using the site.** Two things to check. First, is the URL access log running? It writes a row every time a logged-in member loads a page, and after a fresh install it can be empty for a few days while real traffic accrues. Second, what's the access-log retention? **SocietyPress → Settings → Privacy** has a "URL Log Retention Days" setting. If it's set to 30 and you're asking for 365 days, the older data simply isn't there.

**This fiscal year is empty.** Your fiscal-year-start month under **SocietyPress → Settings → Membership** may be set wrong. Many genealogy societies run a July-to-June fiscal year; the default is January. Check the setting, save, refresh Insights.

**A module I have enabled isn't showing up.** Three possibilities, in order. (1) The module's settings switch under **SocietyPress → Settings → Modules** is actually off. (2) The module's database tables didn't install correctly — visit **Plugins → Installed Plugins**, deactivate SocietyPress, reactivate it, and the missing tables get created. (3) You're viewing as a non-admin who has access to that module but not Reports — log in as a full administrator to confirm.

**Donations total looks too low.** Insights only counts donations where the status is `recorded` (manual cash/check entry), `paid` (Stripe one-time), or `subscription_active` (recurring). Failed, pending, cancelled, and refunded donations are excluded. If you see $0 but you know money came in, check **SocietyPress → Donations** for any rows still in `pending` — usually a Stripe webhook didn't deliver and the donation needs a manual status bump.

**The numbers feel slow to load.** This is rare on a typical society's data, but big sites with hundreds of thousands of records can feel a pause. The page does its own queries on every load (no cache yet). If you hit a real slowdown, file an issue and we'll add a transient cache.

## Related guides

- [Members](members.md) — where the Active Members count comes from
- [Donations](donations.md) — the status-filter logic in the Donations card
- [Governance](governance.md) — how Volunteer Hours are logged in the first place
