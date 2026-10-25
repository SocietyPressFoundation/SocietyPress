# Child Themes

SocietyPress ships with five GPL-licensed child themes. They differ in layout as well as color — one column or two, a sidebar on the left or the right, a magazine front page or a dashboard of cards — so pick the one whose shape suits your society, then tune the colors and fonts from there.

## What you can do

- Activate a child theme to take on its layout, palette and fonts in one click.
- Change any of its colors and fonts afterwards under **Website → Appearance → Colors & Fonts** — child themes don't lock you in.
- Switch themes any time — your content, members, events, photos and page-builder pages all stay where they are.
- Build your own child theme if none of these fit.

## The five shipped themes

### Heritage — browns, cream and antique gold

A warm, traditional theme inspired by old library stacks and leather-bound journals. Single column, generous white space, a full-width banner and a three-column footer. Merriweather throughout.

**Good fit for:** societies that want to feel established and scholarly.

### Coastline — navy, white and soft blue

A magazine-style layout with a sidebar on the right on a computer, which drops below the content on a phone. Inter throughout. Professional and approachable.

**Good fit for:** active societies with a lot happening at once.

### Prairie — warm greens and natural tones

An "explorer" layout with permanent navigation down the left side, so visitors always see where they are. Lora throughout.

**Good fit for:** content-heavy societies with deep page structures — big libraries, lots of records, research guides.

### Parlor — deep plum, warm ivory and rose gold

The traditional layout people know from older society websites: a centered banner, a menu across the top, an optional right sidebar, and a front page with a "Next Meeting" callout, upcoming events and recent news. EB Garamond throughout.

**Good fit for:** societies moving from EasyNetSites who want their members to feel at home straight away.

### Ledger — charcoal, ivory and deep burgundy

A modern, dashboard-style theme with card-based layouts, clean navigation and professional spacing. Source Sans throughout. Evokes courthouses, ledger books and official records.

**Good fit for:** societies that want to project authority — genealogical libraries, records-focused groups.

## How to switch themes

**Website → Appearance → Theme.** Pick a theme and click Activate. The site takes on the new theme's layout, palette and fonts straight away.

Activating a theme writes its colors and fonts into your settings, so it replaces any colors you'd chosen before. Save your current look first under **Website → Appearance → Saved Looks** if you might want it back.

## How themes and saved looks interact

The most recent action wins. Activate Heritage and the colors are Heritage's. Apply a saved look and its colors and fonts replace those, while the layout stays Heritage's. Activate Parlor and Parlor's colors replace everything again.

So a child theme decides the *layout* and gives you starting colors; a saved look changes the *colors, fonts and spacing* on top. See [Theme Presets](theme-presets.md).

## How to customize a child theme

Most societies never need to touch a file — **Website → Appearance → Colors & Fonts** has the colors, fonts, sizes, widths and header settings, and a **Custom CSS** box for small styling changes that survive switching themes.

For more than that — a custom layout, a unique footer — build your own child theme (below). WordPress allows one level of child theme, so you can't make a "child of Heritage"; copy Heritage instead and change your copy.

## How to build a brand-new child theme

Copy one of the five theme folders — whichever is closest — rename it, and change its name in `style.css` (keep `Template: societypress`). Its starting colors and fonts are set in the theme's `functions.php` when it's activated; change those to yours.

To share it with other societies, submit it to the [Theme Exchange](https://getsocietypress.org/themes/). Child themes are read line by line before they're listed with a "Reviewed by SocietyPress" badge.

## If something looks wrong

**The colors didn't update after activation.** If a caching plugin is installed (W3 Total Cache, WP Super Cache, LiteSpeed Cache), clear its cache. That's almost always the cause.

**My logo disappeared.** Themes don't touch the logo. Check the **Logo** section of **Website → Appearance → Colors & Fonts**. The image itself lives in the Media Library, not in the theme.

**Custom CSS I added isn't applying.** CSS in the **Custom CSS** box on Colors & Fonts carries over to whichever theme is active. CSS added by editing a theme's own `style.css` is lost when you switch away from that theme — move it into the Custom CSS box, or into your own copy of the theme.

**The page-builder pages look different.** Builder pages keep their widgets when you switch themes, but take on the new theme's colors, fonts and layout — a sidebar theme, for instance, gives them less width. That's expected; check them after switching.

## Related guides

- [Theme Presets](theme-presets.md) — saved looks: colors, fonts and spacing you can reuse and share
- [Page Builder](page-builder.md) — layouts compose with whichever theme is active
