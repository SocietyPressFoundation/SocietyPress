# Page Builder

Build a page by stacking ready-made widgets — a heading, some text, the events calendar, a donate form — instead of writing in an editor. It's part of SocietyPress, not a separate plugin, and it was made deliberately simple: pick a widget, fill in its settings, move it up or down, save.

SocietyPress turns off WordPress's block editor. Pages are either plain pages with ordinary text, or builder pages made of widgets.

## What you can do

- Add any of about 40 widgets to a page, from one list.
- Fill in each widget's settings in plain boxes — no code, no shortcodes to remember.
- Put the widgets in order with the up and down arrows.
- Save drafts and publish when ready, like any WordPress page.

## How to make a builder page

1. **SocietyPress → Pages → Add New Page.** Give it a title.
2. Choose **Page Builder** as the page template, and save the page once — the builder appears after the first save.
3. Click **Add Widget**, pick one from the list, and fill in its settings.
4. Add more widgets the same way. Use the ▲ and ▼ arrows on each widget to put them in order.
5. Save. Make sure the page is published when you want visitors to see it.

Widgets stack one above another, full width. There are no side-by-side columns; widgets that show lists or grids — calendars, galleries, feature cards — arrange their own contents and narrow themselves on a phone.

## What widgets ship

**Members**

- Member Directory
- Surname Lookup
- Membership Tiers
- Quick Stats
- My Volunteer Hours

**Content and layout**

- Heading / Divider
- Rich Text
- Image
- Button / CTA
- Hero Slider
- Feature Cards
- Contact Card
- Map
- Community Link
- Latest News
- Society Sidebar

**Events and volunteers**

- Upcoming Events
- Events Calendar
- Volunteer Opportunities
- Volunteer Stats

**Library, records and research**

- Library Catalog
- Genealogical Records Search
- Resource Links
- Research Guide
- Database Subscriptions
- Newsletter Archive

**Photos**

- Photo Gallery
- Picture Wall
- Picture Wall — Submission Form

**Giving and forms**

- Donation Form
- Contact Form
- Form

**Research Help and Paid Research**

- Help Request Submission Form
- Help Requests Archive
- Research Case Request
- My Research Cases
- My Research Assignments

**Lineage Programs**

- Lineage Roster
- Lineage Application Form
- My Lineage Applications

A widget that belongs to a feature appears in the list only while that feature is turned on.

## How widget settings work

Each widget has its own settings — the Donation Form asks for a campaign and a starting frequency; the Library Catalog asks how many items to show per page and whether visitors must sign in; the Member Directory asks the same.

Settings that come up often:

- **Require login** — only signed-in members see the widget; everyone else sees a prompt to sign in.
- **Per page** — how many items show before the list pages.
- **Show search** — whether the widget has its own search box.

## Widgets and shortcodes

Many widgets also exist as shortcodes — `[sp_donate]`, `[sp_lineage_roster]`, `[sp_help_requests_archive]` and others — for use outside a builder page. The widget's settings become the shortcode's options, such as `[sp_donate default_frequency="monthly"]`. Each feature's guide lists its shortcode where there is one. Not every widget has a shortcode, and not every shortcode has a widget.

## If something looks wrong

**The builder isn't appearing.** Make sure the page's template is **Page Builder** and that you've saved the page at least once. The builder is for pages only, not posts.

**A widget I expected isn't in the list.** It probably belongs to a feature that's turned off. **SocietyPress → Settings → Features** — turn on the feature that owns the widget.

**A widget shows raw shortcode text instead of the real thing.** Check the feature it belongs to is turned on, and that you're looking at the published page rather than the editor.

**I want to copy a page.** There's no copy button. A plugin such as "Duplicate Page" does it, or rebuild the page — widgets are quick to add.

## Related guides

- [Theme Presets](theme-presets.md) — the design tokens that drive widget rendering
- [Setup Wizard](setup-wizard.md) — gets the design defaults in place
- Every other module guide — references its widget(s) at the bottom
