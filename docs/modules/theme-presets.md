# Theme Presets

A preset is your site's design — colors, fonts, spacing, layout — packaged as a small JSON file you can share with another society. They send you their preset; you import it; your site instantly takes on their look. Your content, members, logo, and configuration are not touched.

## What you can do

- Keep a shelf of saved looks on your own site and switch between them with one click.
- Export your current look as a JSON file. Save it as a backup or hand it to another society.
- Import a look from another society. The site immediately takes on its look.
- Import a themed bundle (`.spchildtheme`) — a look plus its own extra styling and images.
- Browse the Theme Exchange at https://societypress.net/themes/ for looks, bundles and reviewed child themes.

All of this is on **Website → Appearance → Saved Looks**.

## How to keep a shelf of looks

Under **Save current look as**, give your current design a name (and a note, if you like) and save it. It joins the **Saved looks** list, where **Apply** switches the site to it and **Delete** removes it. Save one before trying something new and you can always get back.

## How to export your look

Under **Export current look**, give it a name and an optional description ("Navy and gold, Garamond headings, generous spacing for older readers"), then click **Download This Look**. A `.json` file lands in your Downloads folder. That's it. Send it as an email attachment to the society that asked for your look. Or hold it as a backup before experimenting with imports.

## How to import a preset

Under **Import a preset**, choose the `.json` file and click **Apply This Look**. The site takes on the new colors, fonts and spacing straight away.

A themed bundle (`.spchildtheme`) is imported the same way under **Import a themed bundle**, with **Apply Bundle**. A bundle can carry its own extra styling and images as well as a look; SocietyPress strips anything in its styling that could load outside code.

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

## How the Theme Exchange works

https://societypress.net/themes/ is the Theme Exchange: five starter looks, looks and bundles other societies have shared, and full child themes that have been read line by line before they carry a "Reviewed by SocietyPress" badge — a statement about safety, nothing else.

Download a look or bundle from the Exchange, then import it on **Saved Looks**. A full child theme is a `.zip` you upload through WordPress's **Appearance → Themes → Add New → Upload Theme**. How to submit your own work is on the Exchange.

## How presets and child themes interact

The five child themes that ship with SocietyPress (Heritage, Coastline, Prairie, Parlor, Ledger) each set their own colors and fonts when you activate them, and each has its own layout. A saved look changes only colors, fonts and spacing — never the layout.

So the most recent action wins: activate a theme and its colors replace yours; apply a saved look and its colors replace the theme's, while the theme's layout stays. To try a look without losing your own, save your current design to the shelf first. See [Child Themes](child-themes.md).

## If something looks wrong

**Importing says "This file is not a saved look."** The JSON file is from somewhere else (not a SocietyPress preset). Double-check you grabbed it from the Gallery or another SocietyPress site's export. The format key in a valid preset is `"format": "societypress.preset.v1"`.

**I imported a look and the site looks identical.** The look may match your current settings closely. If you use a caching plugin, clear its cache so visitors get the new styling.

**My logo is gone after import.** It shouldn't be — presets don't touch the logo. Check the logo on **Website → Appearance → Colors & Fonts**.

**I want to undo an import.** Apply the look you saved before importing, from the **Saved looks** list. If you didn't save one, adjust the settings by hand on **Website → Appearance → Colors & Fonts**.

**The Exchange page is empty.** Visit https://societypress.net/themes/ in a browser. The Exchange is on the SocietyPress website, not yours, so if it won't load, try again later.

## Related guides

- [Setup Wizard](setup-wizard.md) — sets your first two colors
- [Page Builder](page-builder.md) — design tokens drive the page-builder rendering
