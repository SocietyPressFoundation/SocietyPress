# Privacy & GDPR

What SocietyPress does to protect member data, what privacy law still asks of you, and how to use the built-in tools when a member asks for their data or asks to be deleted.

This guide is information, not legal advice. Even a US society usually has a member or two covered by GDPR (EU residents) or California's CCPA, so it's worth reading once.

## What SocietyPress does for you

- **Encryption at rest.** Cell, work and other phone numbers, fax, street address lines and the seasonal street address are encrypted in the database (libsodium's XSalsa20-Poly1305). Someone holding a copy of the database without your site's `wp-config.php` can't read them. The main phone number, city, state and ZIP are not encrypted, because the directory searches and sorts on them.
- **Email addresses hidden from scrapers.** Emails shown on the directory, event pages and the site footer are put together in the visitor's browser, so bots that harvest addresses from page source get nothing usable.
- **Members choose what the directory shows.** Each member controls their own directory listing from **My Account**.
- **A record of who did what.** Sign-ins and admin actions are recorded under **Settings → Change History**.
- **Export and erase tools** that plug into WordPress's own — below.

## What you still need to do

- **Have a privacy policy.** SocietyPress creates a Privacy Policy page on new sites. Open it and make it true for your society: what you collect, why, who you share it with, and how members ask for their data or to be deleted. **Settings → Privacy** links WordPress to that page, and WordPress's own **Privacy Policy Guide** (in the same Settings group) suggests wording.
- **Get consent where it matters.** There's no "I agree to the privacy policy" checkbox on the join form yet. If you need one, link the policy in the join page's introduction text for now.
- **Know how to answer "send me my data" and "delete me".** Below.
- **Answer promptly.** Most laws give you about 30 days. Make sure someone reads the address your privacy policy gives.

## How to answer "send me everything you have on me"

1. **Tools → Export Personal Data** (WordPress's own screen).
2. Type their email address and click **Send Request**.
3. WordPress emails them a confirmation link. They click it.
4. Back on **Tools → Export Personal Data**, their request shows as confirmed. Click **Send Export**.
5. They get a download with everything SocietyPress (and any other privacy-aware plugin) holds on them.

## How to answer "delete me"

1. **Tools → Erase Personal Data.**
2. Type their email and click **Send Request**.
3. They confirm by email.
4. Click **Erase personal data**.

## What's covered

| Data | Export | Erase |
|---|---|---|
| **Member record** | Yes | The member record is deleted. |
| **Event registrations** | Yes, including as a guest | Kept for your finances and attendance counts, with name, email, phone and notes removed. |
| **Speaker profile** | Yes | Bio, email, phone and website cleared. |
| **Volunteer roles and hours** | Yes | Deleted. |
| **Research Help questions and answers** | Yes, including questions asked without an account | Deleted. |
| **Form and survey answers** | Yes, when the answer was given under their email | Removed. Anonymous survey answers carry no name to remove. |
| **Donations** | Yes | Kept for your books — amount, date, campaign and method stay; name, email, notes and the link to the member are removed. |
| **Email subscription** | Yes | Removed. |
| **Lineage applications** | Yes | Kept as a record that the ancestor was proved, with the link to the member, the narrative, sources and evidence removed, and taken off the public roster. |

Not covered yet: paid research cases, store orders, and ballot votes. Handle those by hand if someone asks, and see the [roadmap](https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md).

Erasing can't be undone.

## Privacy settings worth visiting once

**Settings → Privacy.**

- **Privacy Policy Page** — which page is your policy.
- **New-Member Email Defaults** — whether membership notices, event announcements, newsletters and surname-research contact start on or off for new members. Off means members opt in.
- **Email Delivery Records** — how long to keep the record of who each email went to: forever, 90 days, 6 months, or 1–7 years.
- **Member Self-Service** — lock sections of **My Account** so members can see but not change them.
- **Activity Logging** — optionally log sign-outs and page views, choose whose visits and which pages, and how long to keep them (30 days to forever).
- **Login Notices** — the wording above the sign-in form and in the notice members acknowledge after signing in.

SocietyPress sets no tracking cookies — only the sign-in cookie, which doesn't need consent. If you add analytics or advertising tools, you'll need a cookie-consent plugin for those.

## Where the encryption key comes from

The key is worked out from the secret keys in your site's `wp-config.php` (`AUTH_KEY` and `SECURE_AUTH_KEY`). Nothing to set up — but if those keys change or are lost, the encrypted fields can't be read again.

When moving to a new host, either keep the same `wp-config.php` secret keys, or move with a SocietyPress backup, which stores those fields readable so they're re-encrypted on the new site. See [Backup & Restore](backup-restore.md).

## If something looks wrong

**The export says there's no data for a member you know is there.** WordPress matches on the email of the person's WordPress account. Check under **Users → All Users** that their account email is the one in the request.

**Someone asks you to delete their data from Stripe, PayPal or a mailing service.** SocietyPress can only erase what it holds. Each of those services has its own way to delete a customer's data.

**A member asks why there's no cookie banner.** SocietyPress doesn't use tracking cookies, so it doesn't need one. That changes if you add analytics or advertising tools.

## Related guides

- [Members](members.md) — what's stored on a member record
- [Donations](donations.md) — what's kept after erasing a donor
- [Backup & Restore](backup-restore.md) — moving to a new host without losing encrypted fields
