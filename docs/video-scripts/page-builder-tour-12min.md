# Page Builder Tour — 12-Minute Deep Dive

**Target length:** 12 minutes
**Audience:** Society administrators who've installed SocietyPress and
want to actually build their public pages. Assumes the 5-minute Getting
Started video is already watched.
**Tone:** Hands-on, practical, encouraging. The page builder is the most
creatively flexible part of the plugin; this video should demystify it
without overwhelming.

---

## Pre-flight checklist before recording

- [ ] Fresh or clean SocietyPress install with the setup wizard completed
- [ ] Sample member records imported (or seeded from the demo) so the
      directory widget has something to render
- [ ] At least 2 upcoming events created so the events-list widget has
      content
- [ ] 1 society image ready to upload for the hero widget
- [ ] A PDF newsletter uploaded so the newsletter-archive widget
      populates
- [ ] Browser at 110% zoom, 1400×900 window

---

## Scene 1 — Cold open + concept (0:00–1:00)

**ON SCREEN:** The admin screen for Pages → Your Homepage, empty, showing
the big "Add Widget" button.

**NARRATION:**
> SocietyPress comes with a drag-and-drop page builder. Twenty-one
> widget types, a visual design system, and no shortcodes to memorize.
> This video walks through building a real society homepage from
> scratch — no placeholders, no filler, just a working page by the end.

*(Beat.)*

> The mental model: every public page on your site is a stack of
> widgets. A widget is a block — a piece of content or a feature.
> You add widgets, reorder them, and configure each one. That's it.
> No theme files. No HTML. No CSS required.

---

## Scene 2 — The 37 widgets, grouped (1:00–2:30)

**ON SCREEN:** The "Add Widget" picker, showing all 37 widgets grouped.

**NARRATION:**
> Let's look at what's available before we start placing things.
> Thirty-seven widgets break down into four groups.

*(Point to each group as you name it.)*

> **Content widgets** — the basics. Text block, heading, image,
> gallery, PDF embed, HTML block, button, divider, spacer.

> **Directory widgets** — pull data from the plugin's modules. Member
> directory, event list, event calendar, library catalog, newsletter
> archive, resource links, volunteer opportunities, photo gallery,
> donation form.

> **Hero widgets** — big visual blocks for the top of a page. Hero
> slider, hero banner.

> **Layout widgets** — structure your page. Columns, accordion.

*(Switch back to the empty page.)*

> We'll use nine of these in this video. Don't worry about memorizing
> the list — the picker is always one click away, and widgets are
> easy to add, remove, and replace.

---

## Scene 3 — Hero (2:30–4:00)

**ON SCREEN:** Click "Add Widget" → choose "Hero Banner."

**NARRATION:**
> Every society homepage starts with a hero — a big image or video at
> the top with your society's name and tagline overlaid. We'll use
> Hero Banner — the simpler of the two hero widgets.

*(Configure the hero.)*

> Upload your background image. Society photos work best — a landmark,
> your library interior, a meeting in progress. Avoid stock photos;
> your members can tell.

*(Fill in the overlay text.)*

> Overlay text. Your society name and tagline. Keep it short — eight
> to ten words total across both fields. Anything longer gets lost
> over a busy image.

*(Point to the alignment and color controls.)*

> Text alignment — left, center, right. Text color — dark for light
> images, light for dark. If you're not sure, there's a contrast
> checker built in; SocietyPress will warn you if your text won't
> read against your background.

*(Save the widget.)*

> Click Save. The hero appears in the preview iframe. Hard refresh if
> you don't see it — sometimes the iframe caches.

---

## Scene 4 — Introduction text (4:00–5:00)

**ON SCREEN:** Add Widget → Text Block.

**NARRATION:**
> Under the hero, every homepage needs a one-paragraph introduction.
> Who are you, what do you do, why should a visitor care.

*(Add the widget. Fill in the text area.)*

> The text editor is the standard WordPress classic editor. Bold,
> italic, links, headings, bulleted lists. No block editor — we
> explicitly disabled Gutenberg because it conflicts with the
> page builder.

*(Paste in a sample intro paragraph — 40–60 words, specific.)*

> Write two to four sentences. Your founding date, your size, what
> kind of research your members do. Concrete, not generic.

---

## Scene 5 — Upcoming events (5:00–6:30)

**ON SCREEN:** Add Widget → Event List.

**NARRATION:**
> Upcoming events are the single most powerful signal that your
> society is active. Put them high on the homepage, not buried in a
> menu.

*(Add the widget.)*

> Event List widget. Pick how many events to show — three is a good
> default. Show-upcoming-only, yes. Filter by category — optional;
> leave blank to show everything.

*(Scroll the widget settings.)*

> Display style: "cards with dates" works well on a homepage. "Compact
> list" is better for sidebars. Click Save and the widget renders the
> actual upcoming events you've already created.

*(Preview the rendered widget.)*

> Each event links to its detail page. If you've turned on online
> registration, a "Register" button shows under each event.

---

## Scene 6 — Two-column layout (6:30–8:00)

**ON SCREEN:** Add Widget → Columns.

**NARRATION:**
> Now let's show off the Columns widget, which is how you get side-by-
> side content.

*(Add a two-column row.)*

> Columns, two columns, 60/40 split. Save.

*(The columns appear as two empty placeholders.)*

> Each column is itself a container for widgets. Click "Add Widget"
> inside the left column — let's put a text block introducing our
> member directory.

*(Configure left column.)*

> "Find your ancestors in our records." Short pitch paragraph. Save.

*(Configure right column with Member Directory widget.)*

> Right column: Member Directory widget. Show "Members researching
> surnames" — that's the view of the directory filtered to people
> with surname-research entries. Save.

*(Preview.)*

> Two columns, side by side, text on the left, live member search on
> the right. Pure drag and drop.

---

## Scene 7 — Newsletter archive (8:00–9:00)

**ON SCREEN:** Add Widget → Newsletter Archive.

**NARRATION:**
> If you're publishing a newsletter, put the archive on the homepage.
> Members will find recent issues themselves, and non-members will
> see you have a serious publication program.

*(Add the widget.)*

> Newsletter Archive widget. Show the last three issues. "Members
> only" access — yes. When a non-member tries to download, they see
> a login prompt.

*(Preview.)*

> Three cover thumbnails, dates, titles. The thumbnails came from
> the PDF first-page render automatically when you uploaded the
> newsletters. Nothing to configure manually.

---

## Scene 8 — Donation call-to-action (9:00–10:00)

**ON SCREEN:** Add Widget → Donation Form.

**NARRATION:**
> If your society takes donations — and most do — put a donation
> widget on the homepage. Don't bury it three clicks deep on a Donate
> page nobody visits.

*(Add the widget.)*

> Donation Form widget. Preset amounts — $25, $50, $100, $250,
> other. Recurring option — monthly toggle. Campaign attribution —
> pick "Annual Fund" or whichever campaign you've set up in the
> donation module.

*(Preview.)*

> Inline donation form. Stripe or PayPal, whichever your society has
> connected. No redirect to an external processor's branded page —
> everything stays on your site.

---

## Scene 9 — Reordering and removing (10:00–11:00)

**ON SCREEN:** Back to the page builder outline view.

**NARRATION:**
> Before we save and publish, let me show you the two things people
> struggle with most at first.

*(Hover over a widget, show the handle.)*

> Reordering. Every widget has a drag handle on the left. Grab it,
> drag to a new position, drop. The preview iframe updates.

*(If keyboard-only users are relevant, show the Move Up / Move Down
buttons.)*

> If drag-and-drop isn't working for you — slow connection, keyboard
> user, accessibility software — every widget also has a Move Up and
> Move Down button in its settings. Same result, different input.

*(Hover over a widget, show the remove option.)*

> Removing a widget: hover, click the X, confirm. The widget is gone
> from the page but the content of directory-type widgets — the
> actual members, events, newsletters — stays intact. You're
> removing the display, not the data.

---

## Scene 10 — Design system and publish (11:00–12:00)

**ON SCREEN:** Appearance → Design System.

**NARRATION:**
> One last stop before we publish: the design system. This is where
> you set colors, fonts, and sizes globally — so every widget on
> every page follows your society's branding.

*(Open the design panel, show the 7 color pickers.)*

> Seven color pickers. Primary, accent, text, background, nav,
> footer, links. Every widget uses these instead of hardcoded colors,
> so changing your primary color here changes it everywhere.

*(Pick a font, show the live preview iframe.)*

> Font families for body and headings, separately. Base font size.
> Content width. All live-previewed in the iframe to the right before
> you save.

*(Save design system. Go back to page, hit Publish.)*

> Publish the page. That's a working society homepage — hero, intro,
> upcoming events, two-column directory, newsletter archive,
> donation form — built in under twelve minutes from nothing.

*(End card)*

> More page-builder examples at getsocietypress.org/docs. Questions
> in the community forum at getsocietypress.org/forums.

---

## Post-production notes

- **Preview iframe** — show it in every scene after the first widget,
  so viewers internalize the live-preview workflow.
- **Text callouts** over widget settings panels are especially useful
  in this video — settings are dense and callouts let us skip narrating
  every field.
- **Segment for short versions:** Scene 3 (Hero) and Scene 6 (Columns)
  each make standalone 2-minute videos if people want them separately.
- **Don't demonstrate custom CSS.** Someone will ask — the answer is
  "yes, child themes can customize, but the design system covers 95%
  of what most societies need, so try the design system first."
