# Events

Calendar, registration, recurring events, and speaker management. The most-used module after Members.

## What you can do

- Publish a calendar of upcoming events with descriptions, locations, dates, times, and images.
- Take registrations online, with separate member and non-member prices and card payment through Stripe.
- Ask each attendee a question when they register — a meal choice, say — where each answer can change the price and have its own limit.
- Repeat an event weekly or monthly without creating each one by hand.
- Keep speakers in one list, so the same speaker can be added to many events.
- Email attendees a reminder before an event, and a confirmation when they register.
- Pull other organizations' events into your calendar from their iCal feeds.
- Publish your calendar as an iCal feed members can subscribe to in Google Calendar or Apple Calendar.

## How to add your first event

**Events → Add Event.** Title, description, date, time, location. Save.

If you want online registration, tick **Enable registration**. Set the member price and the non-member price (0.00 for a free event), and a registration limit if the room only holds so many.

A confirmation email goes out to everyone who registers. Its subject line is under **Settings → Events**.

## How to make an event recurring

On the event edit page, find **Recurring Event** and pick from **Recurrence**:

- **Every week on the same day**
- **Every month on the same weekday** — the second Tuesday, the last Friday
- **Every month on the same date** — the 15th

Set **Repeat Until**, or leave it blank for twelve months. Save. SocietyPress creates each occurrence as its own event, so you can change one without touching the rest — when one month's meeting moves to a different room, edit just that one.

To take an occurrence out of its series completely — the December meeting is at a restaurant instead of the library — open it and click **Detach from Series**. It becomes a standalone event. This can't be undone.

## How to manage speakers

**Events → Speakers.** Add a speaker once with their bio, photo, and contact info. Then on any event's edit page, add them to the event and pick their role: Speaker, Instructor, Moderator or Panelist. The event page shows their bio and photo.

An event can have several speakers — a panel, or a workshop with co-presenters.

## How to subscribe to other calendars

**Events → External Calendars** takes iCal addresses from elsewhere — a partner society's calendar, the county library's events. SocietyPress checks them on a schedule and adds their events to your calendar. Each feed shows when it last synced and the last error, if any; **Sync now** checks immediately. To edit one imported event on its own, open it and click **Detach from Feed**.

Your own calendar is published as a feed too. Your public events pages have a **Subscribe** menu that opens it in a calendar app or gives the address to copy into Google Calendar; a signed-in member gets their own address that includes members-only events. Subscribed calendars stay up to date on their own. The feed can be switched off under **Settings → Events**.

## How to put the calendar on a page

Use the page builder: drop the **Events Calendar** widget on a page for a month grid, or **Upcoming Events** for a list.

Most societies put Upcoming Events on the homepage and a full calendar on a dedicated events page.

## If something looks wrong

**The occurrences aren't showing up.** Save the event, then reload the events list. They're created when you save.

**Registration says Stripe isn't set up.** Add your Stripe keys under **Settings → Events → Online Payments**. Test mode with Stripe's test keys works for trying it out; switch to live keys when you're ready.

**A member registered but didn't get a confirmation email.** Check your site can send email at all — "Lost your password?" on the sign-in page with your own address is the quickest test. If that never arrives, your host is probably blocking WordPress's mail; see [Email Setup](email-setup.md).

**An external iCal feed isn't coming in.** **Events → External Calendars** shows when each feed last synced and its last error. Click **Sync now** to try again. If the address has changed at the source, edit it. Feeds that need a password aren't supported.

**A weekly series runs too far ahead.** Set **Repeat Until** to the date it should stop. Left blank, a series runs twelve months.

## Related guides

- [Members](members.md) — registrations link to member records
- [Donations](donations.md) — events can collect donations on top of registration fees
- [Governance](governance.md) — committee meetings live here too
- [Blast Email](blast-email.md) — send event reminders to specific groups
