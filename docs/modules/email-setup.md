# Email Setup

Most software you've used emailed reliably out of the box. WordPress (and SocietyPress with it) doesn't, on most shared hosting, and that's not a SocietyPress problem — it's a fact of how email works in 2026. This guide tells you what to do about it once, so renewal reminders, event confirmations, blast emails, and password resets all land in inboxes instead of spam folders or the void.

If you've already got WP Mail SMTP (or similar) configured and email is reaching your members, skip this guide.

## Why the default doesn't work

WordPress sends mail through PHP's built-in `mail()` function. On a $5/month shared host, `mail()` hands the email to the server's `sendmail` daemon, which sends from your domain at your host's IP address. The receiving mail server (Gmail, Outlook, Yahoo) checks:

- Does the sending IP match the SPF record for your domain? Almost certainly not — your host's mail IP isn't listed in your SPF.
- Is the message DKIM-signed? Almost certainly not — `mail()` doesn't sign anything.
- Has this IP sent spam recently? Very possibly — shared hosts have neighbors who do.

Result: silent failures, spam-folder landings, or outright rejection. You don't see an error; the email just doesn't arrive.

The fix is to send through a real transactional email service that handles authentication for you.

## What you need

1. **An SMTP plugin** that intercepts `wp_mail()` and routes through a real provider. Two solid free options: [WP Mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/) (more features, more popular) and [FluentSMTP](https://wordpress.org/plugins/fluent-smtp/) (cleaner UI, fewer upsells). Either works.
2. **An account** with a transactional email service. Free tiers comfortably cover a society's volume:
   - **Amazon SES** — 62,000 emails/month free if you host at AWS, $0.10/1000 otherwise. Most reliable; setup is more involved.
   - **Postmark** — 100 emails/month free, $15/month for 10,000. Cleanest setup, best deliverability.
   - **Mailgun** — 100 emails/day free for 3 months, then $35/month. Good middle ground.
   - **SendGrid** — 100 emails/day free forever. Easy setup; deliverability is OK.
   - **Brevo (formerly Sendinblue)** — 300 emails/day free forever. European-friendly.
3. **Access to your domain's DNS** for adding SPF and DKIM records (your domain registrar's control panel — GoDaddy, Namecheap, Cloudflare, etc.).

For a society sending a couple hundred renewal reminders a month plus the occasional blast email, **SendGrid's or Brevo's free tier is enough forever**. Pick whichever has the simpler signup form.

## Recipe: SendGrid + WP Mail SMTP (the easy path)

1. **Sign up at sendgrid.com.** Verify your email and complete the brief intake form.
2. **Create a sender identity.** SendGrid asks "what address will you send from?" — answer with your society's address (e.g., `noreply@yoursociety.org` or `webmaster@yoursociety.org`). They send a verification email to that address; click the link.
3. **Get an API key.** SendGrid → Settings → API Keys → Create API Key. Pick "Restricted Access" → enable "Mail Send" only. Copy the key (you'll see it once).
4. **Install WP Mail SMTP** in your WordPress admin (Plugins → Add New → search → install → activate).
5. **Configure WP Mail SMTP.** Settings → WP Mail SMTP → set:
   - From Email: your verified sender (`noreply@yoursociety.org`).
   - From Name: your society's name.
   - Mailer: SendGrid.
   - API Key: paste from step 3.
   - Save.
6. **Send a test email.** WP Mail SMTP → Email Test → enter your own address → Send. Check your inbox. If it arrives, you're done.

Total time: 15 minutes including coffee.

## Add SPF and DKIM (the part most societies skip)

After the recipe above, your emails reach inboxes most of the time. To make it the rest of the time — Gmail and Outlook are getting strict — add SPF and DKIM records to your domain's DNS.

**SPF** tells receiving servers which IPs are allowed to send on your domain's behalf. **DKIM** signs each message so receivers can verify it really came from your domain. Together, they push your inbox-placement rate from "usually" to "consistently."

Each provider gives you the exact records to add. SendGrid's are at Settings → Sender Authentication → Authenticate Your Domain. They give you 3-4 CNAME records to add to your DNS:

```
em.yoursociety.org    CNAME  u<numbers>.wl.sendgrid.net
s1._domainkey         CNAME  s1.domainkey.u<numbers>.wl.sendgrid.net
s2._domainkey         CNAME  s2.domainkey.u<numbers>.wl.sendgrid.net
```

Add those at your registrar's DNS panel (GoDaddy: My Products → DNS; Namecheap: Domain List → Manage → Advanced DNS; Cloudflare: DNS → Records). Save. Wait 5-30 minutes for DNS to propagate, then click "Verify" in SendGrid. Done.

DMARC is the third-leg of the stool but most providers don't require it, and a basic policy is fine to add later. If your registrar makes DMARC easy, set this:

```
_dmarc.yoursociety.org  TXT  "v=DMARC1; p=none; rua=mailto:webmaster@yoursociety.org"
```

That tells receivers to send you a weekly report on email sent under your domain. You'll see if anyone is spoofing you.

## Recipe: Postmark + FluentSMTP (the polished path)

Same shape, slightly nicer experience:

1. Sign up at postmarkapp.com.
2. Create a "Sender Signature" with your society's email and verify.
3. Postmark → Server → API Tokens → copy the Server API token.
4. Install FluentSMTP, configure with mailer = Postmark, paste API token, save.
5. Send test email.
6. Add SPF and DKIM records Postmark gives you (Servers → your server → DKIM).

Postmark is faster and has cleaner reports than SendGrid; you pay for that after the first 100 emails/month.

## Cron — the second half of "emails not arriving"

WordPress's built-in `wp_cron` runs scheduled tasks (renewal reminders, event reminders, retry queue) only when someone visits the site. Low-traffic society sites can go a day or two between visits, during which cron tasks pile up and don't fire.

**Replace it with a real cron trigger.** In cPanel → Cron Jobs, add one that hits `wp-cron.php` every 15 minutes:

```
*/15 * * * * wget -q -O - https://yoursociety.org/wp-cron.php?doing_wp_cron >/dev/null 2>&1
```

Then disable WP's pseudo-cron by adding this to `wp-config.php` just before "That's all, stop editing!":

```php
define( 'DISABLE_WP_CRON', true );
```

Now scheduled tasks fire on a real clock instead of when someone happens to load the home page.

## How to verify deliverability

Three places to check:

- **Your own inbox.** Most boring, most accurate. Send a test renewal reminder to yourself. Did it land in inbox or spam?
- **SocietyPress → Email Log.** Records every email the system tries to send, with the result (sent / failed). If a failure shows up here, the SMTP plugin is configured wrong; if it shows "sent" but the recipient didn't get it, the deliverability problem is downstream (their spam filter, your authentication setup).
- **mail-tester.com.** Send a test email to the address it gives you, then click "Then check your score." A score of 9-10/10 means you're set. Anything under 7 means SPF/DKIM/DMARC need work.

## If something looks wrong

**Test email never arrives.** Check **WP Mail SMTP → Email Test → Result**. If it says "failed," the SMTP credentials are wrong — go back to the configuration screen and recheck your API key. If it says "success" but the email isn't in your inbox, check spam — your authentication isn't fully set up yet.

**Renewal reminders aren't going out, but test emails work.** WP cron isn't running. Set up the cPanel cron trigger above and disable wp-cron. Manually trigger by visiting `yoursociety.org/wp-cron.php` in a browser; check **SocietyPress → Email Log** for new entries.

**Some members get the email, others don't.** Almost always the recipient's spam filter, not your sending. Check the headers on a delivered copy; you'll see `Authentication-Results: ... spf=pass dkim=pass`. If pass-pass, your end is fine — affected members need to whitelist your sending address. Email them from a personal address with instructions ("Look in spam for messages from `noreply@yoursociety.org` and click 'Not Spam'").

**WP Mail SMTP shows "sent" but the recipient claims they got nothing.** Look at the email log in your provider's dashboard (SendGrid → Email Activity, Postmark → Activity). Was it accepted? Was it deferred? Did the receiving server reject it (with what reason)? Most providers retain delivery details for 7-30 days and the message tells you exactly what happened.

**Blast email runs but stops partway through.** Your provider's burst limit. Free tiers cap at 100/day or so; if you blast 500 members in one go, the first 100 send and the rest queue or fail. Either upgrade the tier or use SocietyPress's built-in throttle (Blast Email → Settings → Send rate).

## Related guides

- [Blast Email](blast-email.md) — sends through whatever SMTP you set up here
- [Members](members.md) — renewal reminders are a member-module feature that depends on this
- [Help Requests](help-requests.md) — verification emails go through this pipe too
- [Troubleshooting](https://getsocietypress.org/docs/troubleshooting/) — short version on the marketing site
