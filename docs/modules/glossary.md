# Glossary

Words SocietyPress uses, defined in plain English. If a term in another guide isn't obvious, it's probably in here. Organized by topic rather than alphabetically — most webmasters look things up by what they're trying to do, not what something starts with.

## People

**Member** — Someone in `sp_members`. By definition has a WordPress user account behind the scenes, even if they never log in. A member's identity within SocietyPress is their `member_record_id` and email; their identity in WordPress is their `user_id`. SocietyPress keeps the two in sync automatically.

**Visitor** — Someone reading your site without logging in. Visitors can see public pages (events, the directory if it's set to public, blog posts, the library catalog), but not member-only content.

**Admin / Webmaster** — The volunteer who manages the site. Usually a WordPress administrator with full SocietyPress access. Charles writes "Harold" when he means the canonical non-technical webmaster persona — the one every guide is written for.

**Society** — Your organization. The entity that owns the website. SocietyPress doesn't model multiple societies per install; one install = one society.

**Chair** — A user who chairs at least one committee. Not a permission level — it's read live from the `sp_committees.chair_user_id` column. A chair sees the **My Committee** menu item in the SocietyPress sidebar; full admins see everything.

## Membership terms

**Tier / Membership tier** — A class of membership with its own dues, term length, and benefits. Defaults are Individual, Joint, Lifetime, Honorary, and Subscription. Customizable per society in **Settings → Membership Tiers**.

**Joint member** — Two members in one household at one tier (typically a couple). Internally, joint members are two `sp_members` rows linked by the same `household_id`. The "Joint" tier exists so societies can charge once per household instead of per person.

**Household** — A group of `sp_members` rows sharing a `household_id`. Used for billing (one renewal per household) and physical-mail addressing (one envelope per household).

**Active / Inactive / Lapsed** — Member status values. Active = currently paid up. Inactive = explicitly archived (former member, deceased, etc.). Lapsed = membership expired and the grace period has run out. The renewal cron sends reminders before lapse and archives after.

**Grace period** — Days after expiration during which a member is still treated as active. Configured under **Settings → Membership**. After grace, status flips to Lapsed.

**Fiscal year / Membership year** — The 12-month window your society renews on. Many societies run a July-to-June fiscal year; the default is January. Configured as `membership_start_month` in **Settings → Membership**. Insights' "This fiscal year" window reuses this same setting.

**Renewal reminder** — Automatic email sent before and after expiration. Default schedule: 30 days before, 7 days before, 30 days after. Tunable in **Settings → Membership → Renewal Reminders**.

## Events terms

**Event** — A scheduled activity (meeting, workshop, cemetery walk, conference). Stored in `sp_events`.

**Registration / RSVP** — A member or guest signing up to attend an event. Stored in `sp_event_registrations`. "Registration" and "RSVP" are interchangeable in SocietyPress; the admin column is labeled Registrations.

**Slot** — A specific time and capacity within an event. Used for events with multiple sessions (a tour with 9am, 11am, 1pm slots) or capacity limits. Most events have one implicit slot.

**Speaker** — A presenter associated with an event. Stored separately so a speaker who returns gets their bio carried over. Multiple speakers per event supported.

**iCal feed** — A standard `.ics` URL members add to their personal calendar app to subscribe to your events. SocietyPress generates one automatically. **External Calendars** also lets you subscribe to *other* societies' iCal feeds and pull their events into yours.

## Library, records, and content

**Catalog** — Your library's book and media inventory. Stored in `sp_library_items`. Public-searchable; checkout tracking is optional.

**OPAC** — Online Public Access Catalog. Library jargon for the public-facing search interface to a library catalog. SocietyPress's library frontend is OPAC-style.

**Record collection** — A purpose-built database of transcribed records — cemetery indexes, census transcripts, marriage registers, obituary indexes. Each collection has its own custom fields. Stored as one row per collection in `sp_record_collections`, with the actual records in `sp_records` and per-field values in `sp_record_values` (EAV pattern).

**GENRECORD** — An open exchange format Charles maintains alongside SocietyPress for genealogical records. Lets one society's collection move to another platform that supports the format. See [genrecord.org](https://genrecord.org/).

**Surname research** — A members-only inquiry index where each member registers the surnames they're researching, plus county/state/year range. Visitors search with Soundex + Metaphone phonetic matching and contact the researching member through an anti-spam form.

**Page builder** — SocietyPress's drag-and-drop page composer. You add **widgets** to **columns** on a page. No code, no Gutenberg.

**Widget** — A reusable content block in the page builder. Examples: Member Directory, Upcoming Events, Donation Form, Contact Card. Each widget has a settings panel (columns, filters, etc.) and a frontend renderer.

**Shortcode** — A bracketed code (`[sp_donate]`, `[societypress_join]`, etc.) you can paste into any page or post and SocietyPress replaces it with the live feature. Every public-facing module has a shortcode; widgets are wrappers around them.

## Donations and money

**Campaign** — A fundraising drive with a goal amount, optional deadline, and a public progress bar. Donations can be tied to a campaign or "general fund."

**Cover the fee** — Optional checkbox on the donation form letting the donor pay the Stripe/PayPal processing fee (about 3%) so the society receives the full amount. SocietyPress calculates the bump and adds it to the charge.

**Recurring donation** — Monthly or annual giving via Stripe subscriptions. Stored as `frequency = monthly | annual` on the donation row. Renewals come through a webhook.

**Pseudonymization** — On a GDPR erasure request, donations don't get *deleted* (the IRS wants the dollar amount and date for nonprofit recordkeeping); the donor's name and contact info get *wiped* and replaced with placeholders. The amount and date stay on the books.

**501(c)(3)** — IRS designation for tax-exempt charitable nonprofits in the US. Donations to a 501(c)(3) are tax-deductible. Receipts must include specific language; SocietyPress fills it in automatically if you've entered your EIN under **Settings → Organization**.

## Communications

**Blast email** — Mass email sent to all members or a targeted group (tier, committee, custom group). Stored in `sp_blast_emails`. Not the same as transactional email.

**Transactional email** — System-generated email triggered by a single event: a renewal reminder, an event confirmation, a password reset. Always one-to-one. Goes through `wp_mail()`.

**SMTP** — Simple Mail Transfer Protocol. The plumbing email travels over. WordPress sends through PHP's `mail()` by default; for reliable delivery you point WordPress at a real SMTP service via a plugin like WP Mail SMTP. See the [Email Setup guide](email-setup.md).

**SPF / DKIM / DMARC** — DNS records that tell receiving mail servers your email is legitimate. SPF says "these IPs are allowed to send for my domain"; DKIM signs each message; DMARC tells the world what to do if SPF or DKIM fail. All three together is what gets you out of spam folders.

## Permissions

**Access area** — A permission scope. SocietyPress defines 10: Members, Events, Library, Finances, Communications, Records, Governance, Content, Settings, Reports. A volunteer with the "Library" access area sees only the Library admin pages.

**Role template** — A pre-built bundle of access areas. Pick "Treasurer" and the Finances + Reports areas auto-check. SocietyPress ships 8: Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager, Content Editor.

**WordPress administrator** — A WP-level role (`manage_options` capability) that bypasses SocietyPress's access-area system entirely. Always has full access. To restrict an account, change its WP role to Subscriber first, then assign SP access areas.

## Insights

**Insight panel** — One stat card on the **SocietyPress → Insights** page. Each panel shows a number, a label, and a sparkline for one module. Filterable through `sp_insights_panels` for child themes.

**Sparkline** — A tiny inline chart showing the trend of a number across the chosen time window. SocietyPress draws them as inline SVG (no chart library, no JavaScript). Up-and-to-the-right is good; flat is "no activity"; a recent dip means whatever you tried last quarter slowed down.

**Time window** — The period a stat is calculated over. Insights supports rolling 30 / 90 / 365 days, this fiscal year, and last fiscal year. Switching the window updates every card.

## Other terms you'll see

**ENS / EasyNetSites** — Easynet Solutions, the platform many genealogical societies came from before SocietyPress. SocietyPress reads the standard ENS 86-column member-export CSV directly. ENS-Classic was sunset on 5/31/2027; ENS-R is its successor.

**Picture Wall** — A member-submission gallery (typically ancestor portraits) inside the Gallery module. Submissions land in a moderation queue; staff approves before they go public.

**Society Sidebar** — An auto-assembled member-portal nav widget that lists the enabled modules a logged-in member has access to. Drop the widget on any page (or use the `[sp_society_sidebar]` shortcode) for an ENS-style left rail without configuring a menu.

**Lineage program** — A membership-recognition program for descendants of a specific ancestor group (First Families of [your county], Pioneer Settlers, Civil War Veterans Descendants, Mayflower Descendants). Members apply, staff reviews proofs, approved applicants appear on a public roster with a printable certificate.

**Help request** — A member-submitted research question with a public archive of responses. Time-tracked answers automatically log to the volunteer-hours ledger.

**Research case** — A paid escalation of a help request that genuinely needs many hours of focused work. Stripe-billed up front; additional hours invoiced as needed.

**Encrypted at rest** — Member phone numbers, addresses, and dates of birth are stored encrypted using XChaCha20-Poly1305 via libsodium. The decryption key lives in `wp-config.php` (not in the database). Backups decrypt the fields to plaintext for portability.

**Cron / wp-cron** — WordPress's scheduled-task system. Runs jobs like renewal reminders, blast email queues, and access-log pruning. Replacing `wp-cron` with a real OS-level cron job is recommended for low-traffic society sites; see the [Email Setup guide](email-setup.md#cron).

**Transient** — A short-lived cached value WordPress stores in the database with an expiration. SocietyPress uses transients for things that are expensive to compute and don't change often (Insights stats are *not* cached as transients today; that's a deliberate choice we revisit if a society reports slowness).

**Harold** — The canonical non-technical webmaster persona this whole project is written for. A senior volunteer who's served on a society board, isn't a developer, has limited time, and wants the site to stop fighting them.

## Related guides

- [Members](members.md) — most of the membership terms in action
- [User Access & Roles](user-access.md) — full breakdown of access areas and role templates
- [Insights](insights.md) — sparklines, time windows, panels
- [Email Setup](email-setup.md) — SMTP, SPF, DKIM, DMARC, cron
