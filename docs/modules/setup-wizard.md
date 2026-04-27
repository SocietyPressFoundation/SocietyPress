# Setup Wizard

The first thing SocietyPress shows you after install. Three short pages get the basics in place so the rest of the platform has somewhere to land. Takes about ten minutes if you have your society's information at hand.

## What you'll need

- Your society's name and mailing address.
- A working email address (for site notifications).
- An idea of your membership tiers and dues (you can edit these later).
- An idea of your color and font preferences (you can change these later too).

## Page 1 — Organization

The basics about your society: name, address, phone, email, website. The name shows up in the header, in email signatures, on member-facing forms. The address fills tax-receipt language on donation receipts. None of this is locked in — every field is editable later under **SocietyPress → Settings → Organization**.

If your society is a 501(c)(3), enter your EIN on this page. SocietyPress automatically appends the standard "no goods or services were provided" language to donation receipts when an EIN is configured.

## Page 2 — Membership

Pick your dues structure:

- **Annual** — most common. Members renew once a year on the anniversary of joining (or on a fixed calendar date — both options exist).
- **Rolling** — multi-year cycles, useful for societies that sell 2- or 5-year memberships at a discount.
- **Lifetime** — one-time payment, no expiration.

You can mix-and-match. The wizard creates a default Individual tier and a default Joint/Family tier. You can rename, re-price, add tiers, and remove tiers anytime from **SocietyPress → Members → Member Levels**. The defaults are placeholders.

You'll also pick whether members renew on their join anniversary or on a fixed date (e.g., January 1st for everyone). Most societies pick the anniversary model — it spreads renewal work across the year. The fixed-date model is simpler for societies that prefer a single bulk renewal cycle.

## Page 3 — Appearance

Pick a starting look:

- **Heritage** — navy + gold, formal serif headings. The classic "society" feel.
- **Coastline** — sea-glass blue + sand. For coastal historical groups.
- **Prairie** — terracotta + field green. For agricultural-region societies.
- **Ledger** — slate gray + cream. Library / research-focused, professional.
- **Parlor** — burgundy + ivory. Vintage 19th-century feel.

These are child themes. Activating one applies its color palette and font choices. You can tweak any setting individually under **SocietyPress → Settings → Design** afterward, or import a different preset from the [Theme Gallery](theme-presets.md).

If none of these match what you want, pick the closest one and modify. You can always change the active theme later (Themes lives at **SocietyPress → Themes**).

## What happens after

The wizard ends on a **You're ready** page. Three suggested next steps:

1. **Import members** — see [Members](members.md). The CSV importer recognizes the standard ENS export format if you're moving from EasyNetSites.
2. **Set up payments** — drop your Stripe and/or PayPal credentials in **SocietyPress → Settings → Payments**. Required before members can pay dues, before donations work, and before the store accepts orders.
3. **Add your first event** — see [Events](events.md). Most societies start with their next monthly meeting.

You don't have to do these in any particular order. The wizard's defaults are deliberate so the site is functional without any of them.

## If something looks wrong

**The wizard didn't appear after install.** It only runs once. To see it again, go to **SocietyPress → Settings → Reset Setup Wizard** (you may need to scroll the Settings sidebar). This won't delete any data; it just lets you walk through the wizard again.

**I picked the wrong theme.** Themes are easy to change. **SocietyPress → Themes** lets you switch. Activating a different child theme doesn't touch your members, events, or content.

**I picked the wrong dues structure.** Membership Levels (under **SocietyPress → Members**) is where you change tier prices, durations, and add/remove tiers. The renewal model (anniversary vs fixed date) lives at **SocietyPress → Settings → Membership** and changing it doesn't reset existing members' renewal dates.

**The address on my receipts is wrong.** **SocietyPress → Settings → Organization**. Edit, save. The next receipt picks up the new address.
