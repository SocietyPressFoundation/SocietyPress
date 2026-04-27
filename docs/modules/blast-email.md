# Blast Email

Send mass emails to all members or specific groups. Track delivery, suppress bouncers, honor opt-outs.

## What you can do

- Compose plain-text or HTML emails to any member group.
- Target by tier ("only Lifetime members"), by status ("only active"), by committee ("Library Committee only"), or by hand-picked list.
- Schedule for later or send immediately.
- Track per-recipient delivery status (sent / bounced / opened — opens require an open-tracking pixel).
- Honor opt-outs — members who unsubscribed from a category never receive future blasts in that category.
- Reuse templates — save common emails ("monthly newsletter announcement," "annual meeting reminder") and tweak before each send.

## How to send your first blast

**SocietyPress → Blast Email → Compose.**

- **From** — defaults to your site's admin email. Override per blast if you want.
- **Subject** — required.
- **Audience** — pick from dropdown (All active members / All members / Specific tiers / Specific committee / Custom list).
- **Body** — rich-text editor. Inserts merge tags (e.g., `{{first_name}}`) that personalize per recipient.
- **Schedule** — Send now / Schedule for [datetime].

Preview before sending — the preview renders as it'll appear in the recipient's inbox, with a sample member's data filled in.

Click **Send** (or **Schedule**). The blast queues and sends in batches (default 50/minute to avoid tripping host rate limits).

## How merge tags work

Available tags:

- `{{first_name}}` — recipient's first name.
- `{{last_name}}` — recipient's last name.
- `{{org_name}}` — your society's name (from Settings).
- `{{member_number}}` — their member ID.
- `{{expiration_date}}` — their dues expiration date.

Custom merge tags can be added by extending the merge-tag map in your child theme — see the developer reference (when written).

## How opt-outs work

Every blast email includes an unsubscribe link in the footer (required by anti-spam law). Clicking it presents the recipient with a granular opt-out:

- Unsubscribe from all SocietyPress emails (transactional confirmations like password resets are excluded).
- Unsubscribe from a specific category (newsletter / events / general announcements).

Their preferences are saved on their member record. Future blasts in opted-out categories skip them automatically.

To check who's opted out: **SocietyPress → Members → [member]** → Communication Preferences tab.

## How delivery tracking works

Each recipient gets their own row in the blast's recipients table. Status starts at `pending`, flips to `sent` when wp_mail returns success, flips to `bounced` if the host reports a hard bounce.

Open tracking is optional — when enabled, a 1px pixel is added to HTML emails. When the recipient opens the email and their client loads images, the pixel fires and the row's `opened_at` timestamp records.

(Some email clients block image loading by default, so open rates are always under-reported. The number is directional, not exact.)

To see results: **SocietyPress → Blast Email → [blast]** → Recipients tab.

## How to use templates

**SocietyPress → Blast Email → Templates.** Save commonly-used emails as templates. When composing a new blast, pick a template to pre-fill subject + body. Edit the body before sending.

Common templates: monthly meeting reminder, annual meeting announcement, dues renewal nudge, new-quarterly-issue alert, fundraiser kickoff.

## If something looks wrong

**Sent count is way lower than expected.** Some members are opted out of the category. Some have invalid email addresses (bounced previously). Some are inactive or lapsed. Check the blast's recipients tab for the per-status breakdown.

**Emails not arriving at all.** **SocietyPress → Settings → Email → Send test email** to confirm outbound mail. If the test fails, switch to SMTP under the same Settings page. Also check your host's anti-spam practices — some hosts throttle outbound mail aggressively.

**An invitation went out to someone who shouldn't have received it.** They might be in the targeted group via a tier or committee assignment. Check their member record. If they shouldn't be in the audience, either change their tier/assignment or use the "Custom list" audience type for one-off precision.

**Members complaining about too many emails.** Add categories to your blasts (newsletter, events, fundraiser) so members can opt out of categories they don't want without unsubscribing entirely. Send less.

**Open rate is implausibly low (or zero).** Open tracking depends on the recipient's email client loading images. Some clients block by default; iOS Mail privacy-protect adds noise. Treat the number as directional only.

## Related guides

- [Members](members.md) — audiences are defined by member attributes
- [Newsletters](newsletters.md) — new-issue announcement is a common blast use case
- [Events](events.md) — event reminders are another common use case
