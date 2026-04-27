# Donations

Online giving — one-time, monthly, annually — with receipts. Plus campaign tracking and an admin ledger for hand-recorded donations (checks, cash, in-kind).

## What you can do

- Take online donations via Stripe (one-time, monthly, annually) and PayPal (one-time).
- Run named campaigns with progress bars ("Library Renovation: $4,200 of $10,000").
- Auto-send tax receipts with 501(c)(3) language when an EIN is configured.
- Let donors cover the processing fee so 100% of their gift reaches you.
- Accept anonymous donations (donor identifier scrubbed from the public-facing list).
- Take in-honor-of / in-memory-of dedications.
- Record check, cash, and in-kind donations by hand for complete books.
- Pseudonymize donor records on GDPR erasure request while keeping the financial total intact.

## Before you start

You need a Stripe account. Create one at stripe.com if you don't have one. Drop your **publishable key** and **secret key** in **SocietyPress → Settings → Payments**. Test mode keys work for trying things out; switch to live keys when you're ready to take real money.

PayPal is optional. If you want it, add your **client ID** and **secret** under the same Settings page. PayPal handles one-time donations in the form; recurring (monthly/annual) goes through Stripe.

## How to put a donate form on a page

Two paths:

- **Page builder.** Drop the "Donation Form" widget. Configure default frequency (one-time / monthly / annually), whether to show the in-honor-of field, optional campaign association.
- **Shortcode.** `[sp_donate]` on any page. Accepts the same options as attributes: `[sp_donate campaign="library-fund" default_frequency="monthly"]`.

The form has preset amount buttons ($25 / $50 / $100 / $250 / $500 by default) plus a custom-amount input. The donor picks one-time / monthly / annually, optionally toggles "cover the processing fee," fills in name + email, optionally adds a dedication and a note. Then they click Donate and Stripe Checkout takes over.

## How to set up a campaign

**SocietyPress → Donations → Campaigns → Add New.** Name, description, goal amount (optional), end date (optional), image (optional). Save.

Once a campaign exists, every donation form / widget can attribute gifts to it: pass `campaign="library-fund"` (using the campaign's slug) to the form, or set the campaign in the widget settings.

The campaign page (`/campaigns/[slug]/`) shows a progress bar with current total vs goal, recent donors (anonymous gifts excluded), and a donate button pre-tagged for the campaign.

## How to read the receipt

Receipts go out automatically the moment Stripe confirms the payment. The email includes:

- The amount.
- The frequency ("one-time donation" vs "monthly recurring donation").
- The dedication, if one was provided.
- 501(c)(3) language including your EIN — but only if the EIN is configured under **SocietyPress → Settings → Organization**. If it's blank, the receipt simply thanks the donor without tax-status language.

For check/cash donations recorded in the admin, receipts go out via a batch action on the donations list ("Send acknowledgment for selected"). Same template.

## How recurring donations work

When a donor picks Monthly or Annually, Stripe creates a subscription. SocietyPress records the first donation immediately (status: subscription_active). Each subsequent renewal arrives via webhook (`invoice.paid`) and creates a new donation row tagged with the original subscription ID, so your books match Stripe's records.

If a donor cancels their subscription (via Stripe's customer portal or by emailing you to cancel), the webhook (`customer.subscription.deleted`) flips your record to `subscription_canceled`. No more renewal rows are created.

To set up the webhook: in your Stripe dashboard, add an endpoint URL `https://yoursite.org/wp-json/societypress/v1/webhooks/stripe`. Pick the three events: `checkout.session.completed`, `invoice.paid`, `customer.subscription.deleted`. Copy the signing secret into **SocietyPress → Settings → Payments → Stripe Webhook Secret**.

## How to record a donation by hand

For checks, cash, in-kind contributions, or grants that don't flow through Stripe:

**SocietyPress → Donations → Record Donation.** Donor name, email, amount, type (cash / check / in-kind / grant), date, optional campaign, optional note. Save. The donation appears in the ledger alongside online donations; receipts can be sent on demand.

In-kind donations support a free-text description of what was given (e.g., "1850 census photocopies, ~200 sheets, value $50") plus an optional fair-market amount.

## If something looks wrong

**Donation form says "Stripe is not configured."** **SocietyPress → Settings → Payments**. Drop in your publishable + secret keys.

**Donations land but no receipt arrives.** **SocietyPress → Settings → Email → Send test email** to confirm outbound mail works. If mail works generally, check that the donor's email address is correctly captured (look at the donation row in the admin ledger).

**Recurring donations stop after the first payment.** The webhook isn't reaching your site. In Stripe's dashboard → Developers → Webhooks, look at recent attempts to your endpoint. If they're failing, the most common reasons are: signing secret doesn't match (re-copy it from Stripe), URL is wrong, or your host's firewall is blocking the request.

**Donor wants their data scrubbed (GDPR).** **SocietyPress → Tools → Privacy → Erase Personal Data**. Their donation rows are pseudonymized — donor name becomes "Anonymous Donor," email is cleared, the amount + date + payment method are kept (IRS recordkeeping requires this for at least 7 years). The financial total of past gifts isn't touched.

**Stripe processing fees are eating into small donations.** Turn on the "cover the processing fee" toggle in the form. Donors who tick it add ~3% to their gift so 100% reaches you. Most do, especially for smaller amounts.

## Related guides

- [Members](members.md) — donations link to member records when the email matches
- [Setup Wizard](setup-wizard.md) — picks your initial 501(c)(3) language
