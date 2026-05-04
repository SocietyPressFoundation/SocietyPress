# Privacy & GDPR

What SocietyPress does to protect member data, what the law requires of you (yes, even US-based societies — at least one of your members is probably an EU resident or a Californian who falls under CCPA), and how to use the built-in tools that make compliance approximately a checkbox exercise instead of a panic.

This guide is informational; it isn't legal advice. If your society has lawyers, ask them. Most don't.

## What SocietyPress does for you automatically

Five things you get out of the box, no configuration required:

- **Encryption at rest.** Phone numbers, street addresses, dates of birth, and a handful of other sensitive fields are encrypted in the database using XChaCha20-Poly1305 via libsodium — the same modern cipher Signal and WireGuard use. Someone with read-only access to the database (a malicious shared-hosting neighbor, a leaked backup) can't read these fields. The encryption key lives in `wp-config.php`, not in the database.
- **Email obfuscation on public pages.** Member emails on the directory and surname-research pages are output behind a small JavaScript indirection so scraping bots collect garbage instead of working addresses.
- **Per-field directory privacy.** Each member chooses what shows in the public directory: name, address, phone, email, photo. Nothing is opt-in by default unless you change the setting.
- **Audit log.** Every administrative action that touches a member record (view, edit, delete, export) is logged with who, when, and what. Useful for both compliance and "who deleted that?" investigations.
- **Personal-data export and erasure** through WordPress's standard tools — covered in detail below.

## What you still need to do

GDPR (and CCPA, and Australia's Privacy Act, and Canada's PIPEDA) doesn't care about your software stack. It cares about your processes:

- **Have a privacy policy.** A page on your site explaining what you collect, why, who you share it with (probably no one), and how members exercise their rights. SocietyPress doesn't ship one — copy a template from a similar small-nonprofit, or have a board member draft one. Two pages is plenty.
- **Get consent where it matters.** When a member signs up, they should affirmatively check a box agreeing to your privacy policy. The join form has this field; turn it on under **SocietyPress → Settings → Privacy**.
- **Know how to handle a data-subject request.** "Export everything you have on me" or "delete me" — every covered law gives members this right. The next two sections walk through both.
- **Respond promptly.** Most laws give you 30 days to comply. Set up a real email address (the one tied to your privacy policy) and check it.

## How to handle "Export all data you have on me"

A member emails: "Under GDPR Article 15 / CCPA / state law, please send me everything you have on me."

1. **WordPress admin → Tools → Export Personal Data.**
2. Type their email address. Click **Send Request**.
3. WordPress emails the member a confirmation link. They click it.
4. You return to **Tools → Export Personal Data**. Their request shows. Click **Send Export**.
5. WordPress generates a ZIP with everything SocietyPress (and any other GDPR-aware plugin) has on them — member record, event registrations, library loans, donations, volunteer hours, lineage applications, help-request submissions, the works. Ten plugins-worth of data, one ZIP, automatic.

The whole exchange takes you about three minutes of clicks. The member gets a complete, machine-readable record of their data.

## How to handle "Delete me"

Same shape, different button:

1. **WordPress admin → Tools → Erase Personal Data.**
2. Type their email. Click **Send Request**.
3. They confirm via email link.
4. Click **Force Erase Personal Data**.

Everything that can be deleted, is. Everything that *shouldn't* be deleted (donation amounts and dates for IRS recordkeeping, volunteer hours that affect official tallies) is **pseudonymized** instead — the dollar amount stays, the donor name and contact info are wiped. SocietyPress's GDPR exporters and erasers handle this distinction automatically. You don't have to remember which fields are pseudonymize-not-delete.

## What gets exported / erased

Five exporters and five erasers ship with SocietyPress, hooked into WordPress's standard data-subject framework:

| Source | Export covers | Erase covers |
|---|---|---|
| **Member record** | Name, contact info, tier, status, dates, custom fields | Name, contact, custom fields zeroed; account deactivated |
| **Event registrations** | Every event they registered for, party size, payment | Name redacted; event still shows the registration count |
| **Volunteer hours** | Every shift logged, dates, hours | Name redacted; total hours stay (committee statistics) |
| **Help requests / responses** | Every question asked + answered | Name redacted; question stays in archive |
| **Donations** | Every gift, amount, date, dedication | Pseudonymized: amount + date stay; donor name and contact wiped |

Lineage applications, research-services cases, and store orders are also covered — the table above is the simplified version.

## Privacy settings worth visiting once

**SocietyPress → Settings → Privacy.** A few specific settings worth tuning:

- **URL access log retention.** The plugin records every page view by logged-in members for activity insights. Default 90 days. Lower it (30 days) if your privacy policy requires data minimization, or set to 0 to disable entirely. Insights on the last 365 days will be limited by whatever you set here.
- **Default directory visibility.** "Public" means new members appear in the directory unless they opt out; "Members only" means only logged-in members see it; "Private by default" means new members are hidden until they opt in. Default-private is the most member-friendly stance.
- **Profile-change approval.** When members edit their own profile, do their changes go live immediately or land in an approval queue first? Approval queue catches mistakes (and the rare malicious change) at the cost of some admin time.
- **Cookie notice.** SocietyPress doesn't show a cookie banner by default because it doesn't set tracking cookies — only login session cookies, which legally don't require consent. If your jurisdiction requires a banner anyway, install a cookie-consent plugin (CookieYes, Complianz) and configure it.

## Where the encryption key lives

`wp-config.php`, in the constant `SP_LIBSODIUM_KEY`. The setup wizard generates one on install. **Do not lose it.** If you migrate to a new server and the key doesn't come with you, the encrypted fields turn into gibberish that can't be decrypted ever.

The full-site backup ZIP decrypts these fields to plaintext for portability. Migrating via backup-and-restore is the safe path. If you want to move the running database directly, copy `wp-config.php`'s `SP_LIBSODIUM_KEY` over too.

## If something looks wrong

**The Export Personal Data tool says "no personal data found" for a member you know is in the system.** WordPress matches by the member's WordPress-account email, not their `sp_members` email — usually the same, sometimes not. Check **Users → All Users**, find their account, confirm the email matches the request.

**A member asks you to delete a donation that they then claim was a mistake.** You can't undo an erasure — the personal info is gone. But the dollar amount and date persist (pseudonymized for IRS). If the member donates again under a fresh account, you have a new record without history.

**The site doesn't show a cookie banner and a member is asking why not.** Because SocietyPress doesn't set tracking cookies, GDPR doesn't require one. Login sessions and shopping-cart cookies are exempt from consent requirements. If you've added Google Analytics, Facebook Pixel, or any tracking plugin, that's a different story — those need a banner and you need a consent plugin.

**An auditor / lawyer asks "where's the data processing record?"** Article 30 GDPR requires processing-activities records for organizations of 250+. Most societies fall well below that threshold. Smaller orgs are exempt unless processing is "not occasional" or involves special categories — read the actual regulation if your society approaches the threshold or processes sensitive data (medical records, criminal records, etc.).

**Member emails the privacy address with a request, you handle it, they reply "did you also delete X external service?"** SocietyPress only handles data inside SocietyPress. Mailing-list services (Mailchimp), payment processors (Stripe, PayPal), event platforms (Eventbrite if you use one) all have their own data and you'll need to handle requests for those separately. Stripe has a one-click delete; PayPal has a request form; Mailchimp has a profile-deletion link.

## Related guides

- [Members](members.md) — what's stored on a member record
- [Donations](donations.md) — pseudonymization details for the donor record
- [Backup & Restore](backup-restore.md) — how the encryption key moves between sites
- [Settings → Privacy](https://getsocietypress.org/docs/) — the admin page itself
