# Child Themes

SocietyPress ships with five GPL-licensed child themes. Each carries its own palette, font choices, and a few small layout tweaks. They're starting points — pick the one closest to your society's character, then tune from there.

## What you can do

- Activate a child theme to take on its palette + fonts in one click.
- Mix child themes with [Theme Presets](theme-presets.md) — activate Heritage, then import a different preset for a fresh palette without changing the underlying theme.
- Customize freely via **SocietyPress → Settings → Design** — child themes don't lock you in.
- Switch themes anytime — your content, members, events, photos, page-builder pages all stay where they are.
- Build your own child theme if none of these fit — child themes are folders of CSS overrides; no PHP required for most cases.

## The five shipped themes

### Heritage — Navy & Gold

The classic society look. Deep navy header (#0d1f3c), warm gold accents (#c9973a), Garamond serif throughout. Generous spacing, formal type scale, slow-pacing rhythm. Built for societies with hardbound minutes, brass-plaque entryways, and the air of a venerable institution. Pair with the [Heritage preset](theme-presets.md) for the full effect.

**Good fit for:** state-level historical societies, university genealogical societies, established societies with a long publication record.

### Coastline — Blue & Sand

Sea-glass blue (#2c5b7a), sand accents (#e8c89a). Lora for headings, Open Sans for body. Cleaner and more modern than Heritage; visual breathing room. Loosely inspired by sea-charts and old maritime publications.

**Good fit for:** coastal historical societies, barrier-island heritage groups, port-city archives, anything with maritime or coastal character.

### Prairie — Earth Tones

Terracotta (#7a3e1f), field green (#5b7a3e), aged ivory backgrounds. Merriweather slab-serif throughout, larger type scale, generous line height. Visually grounded, agricultural in feel.

**Good fit for:** farming-region heritage societies, rural county histories, prairie/plains genealogical groups, ranching family associations.

### Parlor — Vintage Burgundy

Deep burgundy (#6b1f3a), aged ivory (#fdf8f0), antique gold accents. Palatino headings and body — strong 19th-century parlor portrait energy. The most "vintage" of the five.

**Good fit for:** Daughters/Sons of [Confederacy/Revolution] groups, lineage societies, family associations focused on a specific ancestor or era, history-rich coastal societies.

### Ledger — Slate & Cream

Cool slate (#2d3a47), cream backgrounds, copper accents. Source Sans throughout — modern professional. Tighter nav spacing, smaller heading scale. Less ornate than the others; more "library catalog" than "parlor portrait."

**Good fit for:** genealogical libraries, research-focused societies, county-level archives, organizations whose primary identity is "we hold the records," not "we celebrate the heritage."

## How to switch themes

**SocietyPress → Themes.** Pick a theme card, click Activate. The site immediately picks up the new theme's palette, font choices, and any layout tweaks.

If you've imported a custom preset that overrides palette colors, those overrides stay — child themes set defaults; presets and Settings → Design overrides win.

To preview a theme without activating it on the live site, use the WordPress Customizer's preview mode (sometimes hidden — admin → Customize → top-left "Active theme" → Change → preview alongside the current theme).

## How presets and child themes interact

Child themes set their initial palette via a "palette-on-activation" hook — when you switch to Heritage, it writes the navy+gold tokens into your settings. Importing a preset (Theme Presets → Import) writes a different set of tokens.

Result: the most recent action wins. Activate Heritage → palette is navy+gold. Import the Coastline preset → palette is blue+sand. Activate Parlor → palette is burgundy+ivory.

If you want to combine a child theme's *layout* with a preset's *colors* — that's exactly what presets are for. Child theme = layout + defaults. Preset = colors + fonts + spacing overrides on top.

## How to customize a child theme

Most societies don't need to write code — Settings → Design gives you 12+ tunable knobs that don't require touching files.

When you do need code-level customization (custom layout for a specific page, a unique footer, an extra widget area), you'd:

1. Pick the closest child theme.
2. Make a *grandchild theme* — a folder with `style.css` declaring `Template: heritage` (or whichever) plus your overrides.
3. Activate the grandchild.

The five shipped child themes all extend the parent **societypress** theme. You can also extend any of them. WordPress only allows two levels of theme inheritance, so you can't extend a grandchild — but two levels covers every case I've seen.

## How to build a brand-new child theme

If none of the five fit, build one. The Heritage child theme is a useful template — copy its folder, rename, tweak its `style.css` palette and the `palette-on-activation` array.

Submit it to the [Theme Gallery](https://getsocietypress.org/themes/) for other societies to discover. Reviewed themes get a "Reviewed by SocietyPress" badge.

## If something looks wrong

**The colors didn't update after activation.** Caching. If a caching plugin is installed (W3 Total Cache, WP Super Cache, LiteSpeed Cache), purge it from the admin first — that's almost always the real culprit. Then check the page in an incognito window: if the new colors appear there but not in your regular browser, your browser is holding the old stylesheet and a normal reload after a minute or two will sort it.

**My logo disappeared.** It shouldn't — child themes don't touch the logo. Check **SocietyPress → Settings → Design → Logo** to confirm the media-library reference is still there. The logo image lives in the WordPress Media Library, not in the theme.

**Custom CSS I added isn't applying.** If you added CSS via Settings → Design → Custom CSS, that should still apply on top of the new child theme. If you added CSS by editing the previous child theme's `style.css` directly, that customization is lost — child theme files don't carry over. Use Settings → Design → Custom CSS or build a grandchild theme to keep customizations portable.

**The page-builder layouts look broken.** Page-builder layouts are stored on the page, not in the theme. They follow the theme's design tokens (colors, fonts) but the structure stays. If something genuinely looks broken, check whether a caching plugin is serving stale CSS — purge any caching plugin's cache first, then verify in an incognito window before assuming it's a real layout issue.

## Related guides

- [Theme Presets](theme-presets.md) — palette/fonts/spacing exports separate from child themes
- [Setup Wizard](setup-wizard.md) — picks the initial child theme during install
- [Page Builder](page-builder.md) — layouts compose with whichever theme is active
