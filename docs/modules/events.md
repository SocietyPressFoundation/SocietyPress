# Events

Calendar, registration, recurring events, and speaker management. The most-used module after Members.

## What you can do

- Publish a calendar of upcoming events with descriptions, locations, dates, times, and images.
- Take registrations online with optional fees (Stripe + PayPal).
- Run recurring events (weekly, monthly, every-other-Tuesday) without manually creating each occurrence.
- Manage speakers separately so the same speaker can be tagged on multiple events.
- Send reminder emails before an event.
- Show registered attendees to members or keep the list private.
- Pull external events into your calendar via iCal feeds.
- Publish your calendar as an iCal feed so members can subscribe in Google Calendar / Apple Calendar.

## How to add your first event

**SocietyPress → Events → Add New.** Title, description, date, time, location. Save.

If you want online registration, scroll down and tick "Enable registration." Set member price and non-member price (use 0.00 for free events). Set a registration limit if you have a venue capacity.

If you want a confirmation email to go out, the system handles it automatically once Settings → Email is configured.

## How to make an event recurring

On the event edit page, look for the **Recurrence** section.

- **Daily** — every N days.
- **Weekly** — every N weeks on specific days (e.g., every other Tuesday).
- **Monthly by date** — the 15th of every month.
- **Monthly by day** — the 2nd Tuesday of every month.

Pick an end date (or a maximum number of occurrences). Save. SocietyPress generates each occurrence as its own event row, so you can edit any single occurrence independently if (say) one month's meeting moves to a different room.

To detach an occurrence from its series — for example, the December meeting is at a restaurant instead of the usual library — open that occurrence and click "Detach from series." It becomes a standalone event you can edit without affecting the rest of the series.

## How to manage speakers

**SocietyPress → Events → Speakers.** Add a speaker once with their bio, photo, and contact info. Then on any event edit page, pick them from the Speaker dropdown. The event display pulls in their bio and photo automatically.

A single event can list multiple speakers (panel discussions, workshops with co-presenters). Just add them all on the event edit page.

## How to subscribe a feed

**SocietyPress → Events → External Calendars** lets you add iCal URLs from elsewhere — a partner society's calendar, a county genealogy library's events, etc. SocietyPress polls them on a schedule and merges their events into your calendar with a small "via [source]" label.

To publish your own iCal feed for members to subscribe to, the URL is `https://yoursite.org/?ical=1` (configurable). Members add this URL to their Google Calendar / Apple Calendar app's "Subscribe to a calendar" feature and it auto-syncs.

## How to put the calendar on a page

Two paths:

- **Page builder.** Drop the "Event Calendar" or "Upcoming Events" widget. The Calendar widget shows a grid (month view); the Upcoming widget shows a list. Both are filterable by category.
- **Shortcode.** `[sp_calendar]` for the grid, `[sp_upcoming_events]` for the list. Both accept attributes to limit the count, filter by category, etc.

Most societies put Upcoming Events on the homepage and a full Calendar on a dedicated /events/ page.

## If something looks wrong

**The recurrence isn't showing up.** Save the event, then refresh the events list. Recurrence generation runs at save time but isn't always instant — give it a few seconds and reload.

**Stripe registration says "not configured."** Drop your Stripe credentials in **SocietyPress → Settings → Payments** first. Test mode + sandbox keys work; switch to live mode when ready.

**A member registered but didn't get a confirmation email.** **SocietyPress → Settings → Email → Send test email** to verify outbound mail works. If the test fails, your host probably blocks WordPress's default mail — switch to SMTP under the same Settings page.

**An external iCal feed isn't pulling in.** **SocietyPress → Events → External Calendars** shows the last sync time and any errors. Click "Sync now" to force a refresh. If the URL has changed at the source, edit it. Some calendars require authentication (rare for genealogical societies) and SocietyPress doesn't currently support those.

**Recurring events are flooding the list.** The default cap is 100 occurrences per series. If a "weekly forever" series is generating too many, set an explicit end date (e.g., "December 2026") instead of leaving it open-ended.

## Related guides

- [Members](members.md) — registrations link to member records
- [Donations](donations.md) — events can collect donations on top of registration fees
- [Governance](governance.md) — committee meetings live here too
- [Blast Email](blast-email.md) — send event reminders to specific groups
