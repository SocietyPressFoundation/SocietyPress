# Theme Presets

A preset is your site's design — colors, fonts, spacing, layout — packaged as a small JSON file you can share with another society. They send you their preset; you import it; your site instantly takes on their look. Your content, members, logo, and configuration are not touched.

## What you can do

- Export your current look as a JSON file. Save it as a backup or hand it to another society.
- Import a preset from another society. The site immediately takes on its look.
- Browse the public Theme Gallery at https://getsocietypress.org/themes/ for curated starter presets.
- Submit your preset to the Gallery for other societies to discover.

## How to export your look

**SocietyPress → Theme Presets → Export.**

- **Name** — defaults to "[Your Society] theme." Edit if you want.
- **Description** — optional. A short note explaining the look ("Navy + gold palette, Garamond headings, generous spacing for older readers").

Click **Download Preset**. A `.json` file lands in your Downloads folder. That's it. Send it as an email attachment to the society that asked for your look. Or hold it as a backup before experimenting with imports.

## How to import a preset

**SocietyPress → Theme Presets → Import.**

Pick the `.json` file. Click **Apply Preset**. The site picks up the new colors, fonts, and spacing immediately — refresh the front-end and you'll see the change.

Before you do this for the first time, **export your current preset first** as a backup. If you don't love the imported look, re-import your original to roll back.

## What's in a preset

Just design tokens. Specifically:

- All seven palette colors (primary, primary hover, accent, header bg, header text, footer bg, footer text, footer link).
- Body font + heading font (from a list of 10 web-safe / Google Fonts options).
- Font size scale + heading size scale (compact / comfortable / large).
- Content width (narrow / standard / wide / custom in pixels).
- Header height + logo height + header padding.
- Nav font size + nav spacing + nav font weight.
- A few binary toggles (show header title, show social icons).

## What's NOT in a preset

Presets deliberately exclude anything site-specific or sensitive:

- **Your logo.** Logos are media-library uploads tied to a specific install.
- **Members, events, content, photos, donations, anything in the database.**
- **Your organization's name, address, contact info.**
- **Stripe / PayPal credentials, API keys, any secrets.**
- **PHP code, templates, or files of any kind.** A preset is a JSON file with maybe two dozen number/string values. Open it in any text editor before importing — what you see is what gets applied.

This is by design. A preset is the *minimum* needed to express a look. Anything bigger crosses into "you're trusting the source."

## How the Theme Gallery works

https://getsocietypress.org/themes/ is a curated catalog of presets. As of this writing it ships with five starter presets matching the five GPL child themes:

- **Heritage** — Navy & Gold (formal serif, the classic society feel)
- **Coastline** — Blue & Sand (clean sans-serif, coastal)
- **Prairie** — Earth Tones (slab-serif, agricultural region feel)
- **Parlor** — Vintage Burgundy (Palatino, 19th-century parlor)
- **Ledger** — Slate & Cream (modern professional, library-focused)

Click any card → **Download Preset** → save the JSON. Then import it via **SocietyPress → Theme Presets → Import**. Two clicks from "I want that look" to "my site has that look."

To submit your preset for the Gallery, use the **Submit your preset** link at the bottom of the Gallery page. Reviewed presets get listed for other societies to discover.

## How presets and child themes interact

The five child themes (Heritage, Coastline, Prairie, Parlor, Ledger) shipped with SocietyPress include their own palette-on-activation hooks — when you activate Heritage, the palette flips to navy+gold automatically.

The matching preset (also called Heritage) sets the *exact* same tokens via the Theme Presets path. The end result is identical.

The difference: child themes carry PHP, custom widget areas, and template overrides; presets are tokens only. Most societies are well-served picking a child theme + importing the matching preset, then tweaking individual settings under **SocietyPress → Settings → Design**.

If you want to change palettes without changing themes, just import a different preset. The active child theme stays the same.

## What the future tiers add

Tier 1 (presets — what's described here) is the foundation. Two more tiers on the roadmap:

- **Tier 2 — Themed Bundle** — a `.spchildtheme` archive with `tokens.json` + `custom.css` + image assets. Same import flow, but the bundle can carry custom CSS rules and asset images. CSS is sanitized (no `@import`, no script injection) so the security boundary stays intact.
- **Tier 3 — Full Child Theme** — a WordPress child theme with PHP, templates, custom functions. Carries real risk; gated behind a manual SocietyPress review with a "Reviewed by SocietyPress" badge before listing in the Gallery.

Tier 2 and Tier 3 don't exist yet. Tier 1 is what's shippable today.

## If something looks wrong

**The Import button says "Preset format not recognized."** The JSON file is from somewhere else (not a SocietyPress preset). Double-check you grabbed it from the Gallery or another SocietyPress site's export. The format key in a valid preset is `"format": "societypress.preset.v1"`.

**I imported a preset and the site looks identical.** Either the preset's tokens matched your current ones (unlikely if you actually picked a different look), or browser cache. Hard-refresh the front-end with Cmd+Shift+R / Ctrl+Shift+R.

**My logo is gone after import.** It shouldn't be — presets don't touch the logo. Check **SocietyPress → Settings → Design → Logo** — the logo media-library reference is on that page.

**I want to undo an import.** Re-import your original preset (the one you exported as a backup before importing the new one). If you didn't export a backup first, manually adjust the tokens via **SocietyPress → Settings → Design** and the Customizer.

**The Gallery page is empty.** Visit https://getsocietypress.org/themes/ in a browser; it should show 5 starter presets. If it's blank, the SocietyPress site (yours, not getsocietypress.org) has a network or cache issue — the Gallery itself doesn't depend on your site.

## Related guides

- [Setup Wizard](setup-wizard.md) — picks the initial child theme + initial palette
- [Page Builder](page-builder.md) — design tokens drive the page-builder rendering
