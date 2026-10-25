# Glossary

Words SocietyPress uses, defined in plain English. If a term in another guide isn't obvious, it's probably in here. Organized by topic rather than alphabetically — most webmasters look things up by what they're trying to do, not what something starts with.

## People

**Member** — Someone on your roster (`sp_members`). Every member has a WordPress user account behind the scenes, even if they never sign in; the two are linked by the account's `user_id`.

**Visitor** — Someone reading your site without logging in. Visitors can see public pages (events, the directory if it's set to public, blog posts, the library catalog), but not member-only content.

**Admin / Webmaster** — The volunteer who manages the site. Usually a WordPress administrator with full SocietyPress access.

**Society** — Your organization. The entity that owns the website. SocietyPress doesn't model multiple societies per install; one install = one society.

**Chair** — A user who chairs at least one committee. Not a permission level — it's read live from the `sp_committees.chair_user_id` column. A chair sees **My Committee** under **Meetings & Board** in the sidebar.

## Membership terms

**Membership plan (tier)** — A class of membership with its own dues and length. New sites start with Individual, Joint/Family, Student, Lifetime and Honorary. Change them under **Members → Membership Plans**.

**Joint member** — Two people in one household on one membership (typically a couple): two member records linked by the same household, paying once.

**Household** — A group of `sp_members` rows sharing a `household_id`. Used for billing (one renewal per household) and physical-mail addressing (one envelope per household).

**Active / Expired / Inactive** — The main member statuses. Active = paid up (or within the grace period). Expired = the membership ran out and wasn't renewed; SocietyPress sets this automatically each night. Inactive = set by hand for someone who has left. Expired members are treated as visitors for members-only content.

**Grace period** — For an annual membership year, the months after the year turns during which unpaid members stay Active. Set as **Renewal Grace Period** under **Settings → Membership** (2 months unless you change it). Rolling memberships have none.

**Fiscal year / Membership year** — The 12-month window your society renews on. Set as **Period Starts** under **Settings → Membership**; July unless you change it. Insights' "This fiscal year" uses the same setting.

**Renewal reminder** — Automatic email sent before a membership expires: 30, 15 and 7 days before, each of which can be turned off under **Settings → Membership → Renewal Reminders**. An optional "your membership has expired" notice can follow.

## Events terms

**Event** — A scheduled activity (meeting, workshop, cemetery walk, conference). Stored in `sp_events`.

**Registration / RSVP** — A member or guest signing up to attend an event. Stored in `sp_event_registrations`. "Registration" and "RSVP" are interchangeable in SocietyPress; the admin column is labeled Registrations.

**Slot** — A specific time and capacity within an event. Used for events with multiple sessions (a tour with 9am, 11am, 1pm slots) or capacity limits. Most events have one implicit slot.

**Speaker** — A presenter associated with an event. Stored separately so a speaker who returns gets their bio carried over. Multiple speakers per event supported.

**iCal feed** — A standard `.ics` URL members add to their personal calendar app to subscribe to your events. SocietyPress generates one automatically. **External Calendars** also lets you subscribe to *other* societies' iCal feeds and pull their events into yours.

## Library, records, and content

**Catalog** — Your library's book and media inventory, searchable on your site.

**OPAC** — Online Public Access Catalog. Library jargon for the public-facing search interface to a library catalog. SocietyPress's library frontend is OPAC-style.

**Record collection** — A purpose-built database of transcribed records — cemetery indexes, census transcripts, marriage registers, obituary indexes. Each collection has its own custom fields. Stored as one row per collection in `sp_record_collections`, with the actual records in `sp_records` and per-field values in `sp_record_values` (EAV pattern).

**GENRECORD** — An open exchange format for transcribed genealogical records, maintained alongside SocietyPress. Any record collection can be exported in it, so a collection can move to another platform that reads the format. See [genrecord.org](https://genrecord.org/).

**Surname research** — A members-only inquiry index where each member registers the surnames they're researching, plus county/state/year range. Visitors search with Soundex + Metaphone phonetic matching and contact the researching member through an anti-spam form.

**Page builder** — SocietyPress's page composer. You stack **widgets** on a page and put them in order with arrow buttons. No code.

**Widget** — A ready-made block in the page builder. Examples: Member Directory, Upcoming Events, Donation Form, Contact Card. Each has its own settings.

**Shortcode** — A bracketed code (`[sp_donate]`, `[societypress_join]`, etc.) you can paste into any page or post, which SocietyPress replaces with the live feature. Many widgets have one, but not all, and some shortcodes have no widget.

## Donations and money

**Campaign** — A fundraising drive with a goal amount, optional deadline, and a public progress bar. Donations can be tied to a campaign or "general fund."

**Cover the fee** — Optional checkbox on the donation form letting the donor pay the Stripe/PayPal processing fee (about 3%) so the society receives the full amount. SocietyPress calculates the bump and adds it to the charge.

**Recurring donation** — Monthly or yearly giving through Stripe. Each later payment is recorded when Stripe tells your site about it, which needs the webhook set up under **Settings → Events → Online Payments**.

**Pseudonymization** — On a GDPR erasure request, donations don't get *deleted* (the IRS wants the dollar amount and date for nonprofit recordkeeping); the donor's name and contact info get *wiped* and replaced with placeholders. The amount and date stay on the books.

**501(c)(3)** — IRS designation for tax-exempt charitable nonprofits in the US. Donations to a 501(c)(3) are tax-deductible. Receipts for deductible gifts need specific language. SocietyPress can print it with your EIN, but there's no screen to enter the EIN yet — see the [roadmap](https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md).

## Communications

**Blast email** — An email sent to all members or a chosen group, from **Newsletters & Email → Send Email**. Not the same as transactional email.

**Transactional email** — System-generated email triggered by a single event: a renewal reminder, an event confirmation, a password reset. Always one-to-one. Goes through `wp_mail()`.

**SMTP** — Simple Mail Transfer Protocol. The plumbing email travels over. WordPress sends through PHP's `mail()` by default; for reliable delivery you point WordPress at a real SMTP service via a plugin like WP Mail SMTP. See the [Email Setup guide](email-setup.md).

**SPF / DKIM / DMARC** — DNS records that tell receiving mail servers your email is legitimate. SPF says "these IPs are allowed to send for my domain"; DKIM signs each message; DMARC tells the world what to do if SPF or DKIM fail. All three together is what gets you out of spam folders.

## Permissions

**Access area** — A permission scope. SocietyPress has 10: Members, Events, Library, Finances, Communications, Genealogical Records, Leadership & Volunteers, Content & Pages, Settings & Design, and Reports. A volunteer with only Library sees only the Library screens.

**Role template** — A pre-built bundle of access areas. Pick "Treasurer" and the Finances + Reports areas auto-check. SocietyPress ships 8: Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager, Content Editor.

**WordPress administrator** — A WP-level role (`manage_options` capability) that bypasses SocietyPress's access-area system entirely. Always has full access. To restrict an account, change its WP role to Subscriber first, then assign SP access areas.

## Insights

**Insight panel** — One card on **Reports → How We're Doing**, showing a number, a label and a sparkline for one feature. Developers can add their own through the `sp_insights_panels` filter.

**Sparkline** — A tiny inline chart showing the trend of a number across the chosen time window. SocietyPress draws them as inline SVG (no chart library, no JavaScript). Up-and-to-the-right is good; flat is "no activity"; a recent dip means whatever you tried last quarter slowed down.

**Time window** — The period a stat is calculated over. Insights supports rolling 30 / 90 / 365 days, this fiscal year, and last fiscal year. Switching the window updates every card.

## Other terms you'll see

**ENS / EasyNetSites** — Easynet Solutions, the platform many genealogical societies came from before SocietyPress. SocietyPress reads the standard ENS member export directly. ENS-Classic's announced end date is May 31, 2027; ENS-R is its successor.

**Picture Wall** — A gallery of member-submitted photos (typically ancestor portraits). Submissions wait for staff approval before they appear. Albums can't yet be marked to accept submissions from the admin — see the [roadmap](https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md).

**Society Sidebar** — An auto-assembled member-portal nav widget that lists the enabled modules a logged-in member has access to. Drop the widget on any page (or use the `[sp_society_sidebar]` shortcode) for an ENS-style left rail without configuring a menu.

**Lineage program** — A membership-recognition program for descendants of a specific ancestor group (First Families of [your county], Pioneer Settlers, Civil War Veterans Descendants, Mayflower Descendants). Members apply, staff reviews proofs, approved applicants appear on a public roster with a printable certificate.

**Help request** — A research question anyone can post, answered by members in a public archive. The time each answer took is added to the volunteer's hours.

**Research case** — A paid escalation of a help request that genuinely needs many hours of focused work. Stripe-billed up front; additional hours invoiced as needed.

**Encrypted at rest** — Members' cell and other phone numbers and street addresses are stored encrypted (libsodium). The key is worked out from the secret keys in `wp-config.php`, not kept in the database. See [Privacy & GDPR](privacy-gdpr.md).

**Cron / wp-cron** — WordPress's scheduled-task system. Runs jobs like renewal reminders, blast email queues, and access-log pruning. Replacing `wp-cron` with a real OS-level cron job is recommended for low-traffic society sites; see the [Email Setup guide](email-setup.md#cron-the-second-half-of-emails-not-arriving).

**Transient** — A short-lived cached value WordPress stores in the database with an expiry. SocietyPress uses them for things that are slow to work out and don't change often, such as the active-members count on Insights (kept for five minutes).

**Harold** — The non-technical webmaster SocietyPress is designed for: a volunteer who's served on a society board, isn't a developer, has limited time, and wants the site to stop fighting them. You'll see the name in the code's comments.

## Related guides

- [Members](members.md) — most of the membership terms in action
- [User Access & Roles](user-access.md) — full breakdown of access areas and role templates
- [Insights](insights.md) — sparklines, time windows, panels
- [Email Setup](email-setup.md) — SMTP, SPF, DKIM, DMARC, cron
