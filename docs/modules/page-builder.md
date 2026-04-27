# Page Builder

Drag-and-drop page assembly. Every shortcode SocietyPress ships has a matching widget in the page builder, so you don't have to type shortcodes by hand if you don't want to.

The page builder is a SocietyPress feature, not a separate plugin. It works on top of WordPress's Pages — you create a Page, edit it with the SocietyPress builder, save.

## What you can do

- Drop pre-built widgets (24+ types) into single-column or multi-column rows.
- Configure each widget through a settings panel — no code, no shortcode syntax.
- Drag widgets to reorder. Resize columns by dragging the dividers.
- Preview as you build — what you see in the editor is roughly what visitors see.
- Save drafts, publish when ready. Revisions are tracked like any WordPress page.
- Any page can mix builder widgets with WordPress block-editor content if you switch back-and-forth.

## How to make a builder page

**SocietyPress → Pages → Add New.** Title the page. In the page editor, click the **Use SocietyPress Builder** toggle at the top. The block editor disappears; the builder canvas appears.

From here:

1. Click **+ Row** to add a row. Pick a column layout (1 column / 2 columns / 3 columns / etc.).
2. Click **+ Widget** in any column to open the widget picker.
3. Pick a widget. Configure its settings in the right-hand panel. Save.
4. Drag widgets to rearrange. Drag the column divider to resize.
5. Click **Save** at the top right when done.

The page is published the same way any WordPress page is — make sure the page status is "Published" (top-right of the WP page editor).

## What widgets ship

Categorized by what they do:

**Member-facing**

- Member Directory — public roster with privacy controls
- Membership Tiers — display the join page
- Surname Lookup — search member-researched surnames
- My Volunteer Hours — logged-in member's contribution summary
- My Research Cases — member's paid-research-case list
- My Research Assignments — researcher dashboard

**Content + display**

- Heading
- Rich Text
- Image
- Hero Slider
- Feature Cards
- Contact Card
- Map Embed
- Community Link
- Newsletter Archive
- Photo Gallery
- Picture Wall (member-submitted ancestor portraits)

**Events**

- Event Calendar (grid view)
- Upcoming Events (list view)

**Library + Records**

- Library Catalog
- Genealogical Records Search

**Donations + store**

- Donation Form
- Membership Tiers (drives the join flow)

**Help / Research**

- Help Request Submission Form
- Help Requests Archive
- Research Case Request

**Lineage Programs**

- Lineage Roster
- Lineage Application Form
- My Lineage Applications

**Information**

- Database Subscriptions
- Research Guide

**Forms + utility**

- Contact Form
- Button
- Member Stats
- Volunteer Stats

That's the full set. New modules ship with their widgets registered automatically — they appear in the picker as soon as you enable the module.

## How widget settings work

Each widget has a unique settings panel — the Donation Form has fields for default frequency and campaign; the Library Catalog has a per-page count and login-required toggle; the Member Directory has columns shown and search behavior.

Common patterns:

- **Login required** — gate the widget behind a login. Visitors without a session see a "please log in" message instead.
- **Per-page count** — how many items to show per page in paginated widgets.
- **Show search** — a checkbox to enable/disable a search box at the top of the widget.
- **Filter by [thing]** — pre-filter the widget to show only content matching some condition.

The settings panel updates the live preview as you change settings, so you see the impact before saving.

## How shortcodes and widgets relate

Every page-builder widget is a wrapper around a shortcode. If you'd rather use shortcodes (in a regular post body, in an email template, in a child-theme PHP template), every widget has a shortcode equivalent.

Convention: widget `donate` corresponds to shortcode `[sp_donate]`. Widget `lineage_roster` corresponds to `[sp_lineage_roster]`. Settings on the widget become attributes on the shortcode (`[sp_donate default_frequency="monthly"]`).

This means you can prototype with the page builder, then "graduate" to shortcodes when you want to embed the same content elsewhere — your WordPress post, your child theme, an email — without redoing the configuration.

## How responsive layouts work

Builder rows are responsive out of the box. A 3-column row on desktop becomes 2-column on tablet, then 1-column on phone. You don't configure this — it's automatic.

If you want a row that stays multi-column at all sizes (rare), edit the row settings → "Responsive Behavior" → pick a different breakpoint.

The widgets themselves are also responsive — tables collapse to cards on mobile, image grids reflow, font sizes scale. You shouldn't have to do anything for a builder page to work on a phone.

## How to embed a builder page elsewhere

If you've built a great page and want to drop part of it onto another page, the simplest path is shortcodes. Each widget on the original page is a configured shortcode somewhere; copy the shortcode (visible in the widget settings → "Shortcode" tab) and paste it on the second page.

You can also save a builder row as a "template" via the row's settings menu → "Save as template," then drop it into another page from the **Templates** widget category.

## If something looks wrong

**The builder toggle isn't appearing.** Make sure you're editing a Page (not a Post). The builder is page-only. Go **SocietyPress → Pages → All Pages**, click the page title to edit.

**A widget I expected isn't in the picker.** The widget probably belongs to a module that's currently disabled. **SocietyPress → Settings → Modules** — turn on the module that owns the widget.

**A widget shows raw shortcode instead of rendering.** Double-check the page is published (not draft) and you're viewing the front-end (not the editor preview). Shortcode resolution happens at front-end render.

**The page-builder layout looks broken on mobile.** Open the page in a phone-sized browser window. If a column is being too aggressive, edit the row → "Responsive Behavior" and pick a wider breakpoint.

**I want to copy a page.** WordPress's "Duplicate Page" plugin works fine for this. Or save the row(s) as templates and reassemble.

## Related guides

- [Theme Presets](theme-presets.md) — the design tokens that drive widget rendering
- [Setup Wizard](setup-wizard.md) — gets the design defaults in place
- Every other module guide — references its widget(s) at the bottom
