# Admin Information Architecture — Audit

**Date:** 2026-08-12
**Audited against:** `societypress.php` v1.1.5, 113,273 lines — the copy running on `txsaghs.com`,
which is byte-identical to `origin/main` (md5 `0ca98e1f`).

---

## Status — all defects fixed, 2026-08-12

**Every defect in this audit is now fixed.** §3, §4, §5, §6, §7, §8 and §10 are done. §9 was a
false finding and is withdrawn. §2 recorded a clean bill of health on permissions that turned out
to be wrong — the defect it missed was found later, is described in place, and is also fixed.

| § | Defect | Status |
|---|---|---|
| 9 | "Repo 177 lines behind txsaghs" | ⛔ **Not a real finding** — see §9 |
| 3 | Two "Menus" in the sidebar | ✅ Registration removed |
| 5 | Admin sidebar editor filed under public appearance | ✅ Moved to Settings, renamed **Admin Sidebar** |
| 6 | Customize opens an empty Customizer | ✅ Removed entirely |
| 7 | "Website" naming two things | ✅ Settings screen renamed **Site Basics** |
| 4 | `sp-record-payment` placed but hidden | ✅ Removed from group config |
| 10 | "Add Images" mislabelled | ✅ Renamed **Photos & Albums** |
| 8 | "How it looks" split across four doors | ✅ One **Appearance** screen, three tabs |

Menu integrity after the changes: **76 visible screens, zero orphans, zero ghosts** (was 80
visible, 1 orphan, 1 ghost). Two screens were removed outright; three more were absorbed into the
single Appearance screen and stay reachable by their old URLs through a redirect.

Everything below is the audit as originally written, preserved as the record of what was found.

---

## The question this audit answers

The back office feels less intuitive as it grows, especially around *appearance* — both how the
admin itself is arranged and how a society controls what visitors see. This audit establishes
what is actually there, what is working, and where the structure breaks down.

## Headline finding

**The information architecture is not missing — it is largely sound, and six specific defects are
making it feel broken.**

That distinction matters, because it changes the work. This does not need a ground-up redesign.
`sp_default_menu_config()` (line 10988) already defines a deliberate 11-group structure with
in-group headings, documented reasoning, and an upgrade path for retired groups. Of 80 visible
screens, **79 are correctly placed**. The capability layer is coherent. What is wrong is a short
list of concrete errors, and every one of them is in or adjacent to the appearance surface — which
is exactly where the friction is being felt.

---

## 1. What is actually there

| Measure | Count |
|---|---|
| `add_submenu_page()` calls | 114 |
| Visible sidebar screens | 80 |
| Hidden detail/edit screens (`null` parent) | 34 |
| Groups in `sp_default_menu_config()` | 11 |
| Screens placed in a group | 80 |
| Capability-map entries | 107 |
| Separate Settings pages | 9 |

Group sizes, largest first:

| Group | Label | Screens | Headings |
|---|---|---|---|
| `appearance` | Website | 16 | 2 |
| `research` | Research | 14 | 3 |
| `settings` | Settings | 13 | 1 |
| `members` | Members | 8 | 1 |
| `governance` | Meetings & Board | 8 | 1 |
| `finances` | Money | 7 | 1 |
| `communications` | Newsletters & Email | 7 | 1 |
| `events` | Events | 6 | 0 |
| `library` | Library | 6 | 1 |
| `reports` | Reports | 4 | 0 |
| `volunteers` | Volunteers | 3 | 0 |

The two largest groups — Website (16) and Research (14) — are the two carrying the most
structural strain. Research is at least internally organised by three headings. Website has two
headings for sixteen items and mixes four different kinds of work.

## 2. What is working, and should not be touched

Worth stating plainly so none of it gets "fixed" during cleanup:

- **The 11-group model is deliberate and documented.** The comments at lines 11024 and 11044
  explain *why* Research consolidated three former groups, and *why* the website's content and
  its look sit together. That reasoning is sound and the code reflects it.
- **Retired-group folding works** (`sp_retired_menu_groups()`, `sp_fold_retired_menu_groups()`).
  A society that customized its layout gets an upgrade that reads as a rearrangement rather than
  duplicated screens. It is read-only until the admin next saves. This is careful work.
- ~~**The capability layer is coherent.**~~ **WRONG — see the correction below.** The audit
  originally recorded that the 75 screens registering with `manage_options` were harmlessly
  remapped by an `admin_menu` pass, and concluded "no permissions defect found". The map existed
  and was correct; the pass that applies it did not run in time.

  **What was actually happening:** the remap was hooked at priority **998**, and the block that
  registers the ~75 main screens runs at priority **999**. A lower priority runs *first*, so the
  remap fired before those menus existed and silently did nothing to them. Only the handful of
  screens registered in their own earlier callbacks — Menus, Theme Presets, Insights, the audit
  logs — were ever remapped, which is exactly why spot-checking the map looked reassuring. The
  in-code comment asserted the opposite ("Priority 998 = after all menus registered (999)"), and
  the audit took it at face value instead of testing it.

  **The effect:** the ten access areas did almost nothing for the sidebar. A Treasurer holding
  `sp_manage_finances`, or a Librarian holding `sp_manage_library`, still faced screens demanding
  a WordPress administrator's `manage_options`, so they saw an all-but-empty menu. The role
  templates were real, granted correctly, and then overruled by the menu.

  **Fixed 2026-08-12** by moving the remap to priority 1001 — after the menu block at 999 and
  after the module filter at 1000, which only removes items. Verified live on demo: screens
  registering `manage_options` fell from 75 to 2 (`sp-menu-layout` and `sp-user-access`, both
  correctly administrator-only), with the rest spread across `sp_manage_content` (18),
  `sp_manage_settings` (15), `sp_manage_members` (7), `sp_manage_governance` (7),
  `sp_manage_communications` (6), `sp_manage_library` (5), `sp_manage_events` (5),
  `sp_manage_finances` (5), `sp_view_reports` (4) and `sp_manage_records` (3).

  **The lesson:** a comment asserting an invariant is not evidence the invariant holds. This one
  was confidently worded, wrong, and load-bearing for a whole permissions feature. Test the
  behavior, especially when the claim is what lets you skip testing it.
- **Placement coverage is near-total.** One orphan, one ghost, no duplicates, nothing registered
  twice.

---

## 3. Defect — two "Menus", both reachable

**Severity: high. This is the clearest single cause of the confusion.**

`nav-menus.php` is registered as a visible submenu titled **"Menus"** under the SocietyPress
parent (line 6646). It is also the *only* orphan in the entire menu — it appears in no group, and
orphans are appended below the groups rather than dropped.

The result: the Website group contains **Menus** (`sp-menus`, the SocietyPress editor), and a
second **Menus** (WordPress core) hangs loose at the bottom of the sidebar.

This directly contradicts the plugin's own documented intent. From line 11047:

> *WHY sp-menus and not nav-menus.php: the SocietyPress Menus screen supersedes WordPress's
> Appearance → Menus for everyday editing... Showing both would put two doors on one room and let
> a volunteer make a change on the WordPress screen that the SP screen never explains.*

The config author removed `nav-menus.php` from the group. The registration was never removed. The
exact failure the comment warns about is live.

**Fix:** drop the `add_submenu_page()` call at line 6646. `nav-menus.php` remains reachable
directly for the redirect at line 94994 and the two "create a menu" buttons that link to it.

## 4. Defect — `sp-record-payment` placed but not visibly registered

**Severity: medium.**

The Money group lists `sp-record-payment` between Payment History and Donations (line 10998). But
the screen is registered with a `null` parent (line 6458) — deliberately, per its own comment:
*"Hidden page for recording a new payment (linked from finances page)."*

So the group config references a screen that will never render in it. Two intents in conflict: one
author wanted it in the sidebar, another wanted it reachable only from the Payment History button
at line 30868.

**Fix:** pick one. Recommend removing it from the group config — it is an action taken *from*
Payment History, not a place. Leaving it in the config is a silent no-op that will confuse the
next person to read the file.

## 5. Defect — the admin sidebar editor is filed under public appearance

**Severity: high. This is the "internal vs public" confusion, precisely located.**

`sp-menu-layout` sits in the **Website** group, under the **"How it looks"** heading, alongside
Themes, Theme Presets, Design and Customize — all four of which control the *public* site.

`sp-menu-layout` controls the *back office sidebar*. Its own page text says so: *"This sets the
menu for everyone who signs in to the back office."*

A volunteer who wants to change how the website looks opens the group labelled Website, reads the
heading "How it looks", clicks "Menu Layout" — and reorders the admin sidebar for every staff
member in the society. The label, the group, and the heading all point the wrong way.

It is also the only item in the Website group still gated on raw `manage_options` rather than an
`sp_*` capability, which is correct for a structural screen and further evidence it does not
belong on a public-appearance shelf.

**Fix:** move it out of Website. Either a new "Back office" heading in Settings, or rename it
**Admin Sidebar** and file it under Settings. Recommend both — the rename removes the collision
with "Menus" as well.

## 6. Defect — "Customize" opens an empty WordPress Customizer

**Severity: medium-high.**

`customize.php` is on the "How it looks" shelf. But:

- `wp_customize` references in `societypress.php`: **0**
- `wp_customize` references in the parent theme's `functions.php`: **0**

Nothing in SocietyPress populates the Customizer. Clicking it drops the user into a
WordPress-branded screen offering core-only controls (site identity, homepage settings) that
either duplicate Settings → Design or conflict with it. It also punches a hole straight through
"WordPress branding completely hidden in admin."

**Fix:** remove `customize.php` from the group. It offers nothing the Design screen does not do
better, and it teaches volunteers a second, contradictory way to change their site.

## 7. Defect — "Website" names two different things

**Severity: medium.**

- The sidebar group is labelled **Website** (`appearance`)
- Inside Settings there is **Website** (`sp-settings-website`, 13 fields: site title, tagline,
  admin email, timezone, date format, time format, homepage, search engines, require login, admin
  toolbar, social media, breadcrumbs, Google Analytics)

Both are reachable, differently scoped, and identically named. A volunteer told to "check the
website settings" has two plausible destinations.

**Fix:** rename one. Recommend the Settings screen become **Site Basics**, leaving the group to
own the word. Its contents are basics — title, tagline, email, timezone, formats, homepage,
visibility — not the look, which is Website → Design.

## 8. Defect — "how it looks" is split across four doors with no principle

**Severity: medium. This is the structural half of the appearance complaint.**

Where the actual controls live:

| Screen | Lines | What it really contains |
|---|---|---|
| **Design** (`sp-settings-design`) | 1,100 | **23 controls** — all 7 colors, both fonts, font size, heading scale, header height/padding, logo, nav sizing/spacing/weight, content width, custom CSS |
| **Themes** (`sp-themes`) | 1,555 | **1 control** — `sp_activate_theme`. 1,555 lines of theme cards to press one button |
| **Theme Presets** (`sp-theme-presets`) | — | Import/export the whole look as a portable file |
| **Customize** (`customize.php`) | — | Nothing (see §6) |

So: pick a theme *here*, change its colors *there*, save the result as a file *somewhere else*,
and a fourth door that does nothing. Each screen is individually defensible. Together they mean a
volunteer must already know which of four places owns the knob they want.

**Fix:** one **Appearance** screen with tabs — *Theme* / *Colors & Fonts* / *Presets*. Same code,
one door. This is the largest of the recommended changes and should follow the small fixes above,
not precede them.

## 9. Withdrawn — "repo drift" was a stale local clone

**This finding was wrong. It is kept here rather than deleted because the mistake is instructive.**

The audit originally reported that txsaghs (113,273 lines) and the repo (113,096) were the same
version but different files, and recommended pulling the server copy into the repo before doing
any other work.

What actually happened: the comparison was made against the **local working clone without running
`git fetch` first**. `origin/main` — the source of truth — already contained every one of the six
"server-only" functions, pushed on 2026-08-09 in commit `f5b5387` ("work the ticket queue —
galleries, menus, editor images, tables"), and its copy of `societypress.php` is byte-identical to
txsaghs (md5 `0ca98e1f`, 113,273 lines). The `sp-editor-image.js` asset was present there too.

So there was no drift between the project and its testbed. There was one unpulled commit in one
local clone. A pull commit was made on that false premise and then dropped automatically during
rebase — Git reported *"patch contents already upstream."*

**The lesson, which does generalise:** `CLAUDE.md` already requires checking the clone against
`origin/main` before working in it, precisely because this repository has a history of stale and
orphaned clones. That check answers "is my clone current?" — and it must be run *before* using the
clone as the baseline for any comparison, not only before pushing. Comparing a server against an
unfetched clone measures the clone's staleness, not the server's divergence.

## 10. Minor notes

- `sp-library` is titled **"Resource Links"**; the actual library catalogue is
  `sp-library-catalog`. Slug and purpose disagree. A maintenance trap, not user-facing.
- `sp-gallery` is titled **"Add Images"** — an action label where a place label belongs. It is the
  albums manager. Recommend **Photos & Albums**.

---

## Recommended order of work

Small, high-confidence fixes first. Each is independently shippable.

1. ~~Pull txsaghs → repo~~ — withdrawn, no drift existed (§9). Do run `git fetch` first.
2. Remove the duplicate `nav-menus.php` registration (§3) — one deleted call, removes a whole door
3. Move and rename `sp-menu-layout` → **Admin Sidebar**, into Settings (§5)
4. Remove `customize.php` from the Website group (§6)
5. Resolve `sp-record-payment` — remove from group config (§4)
6. Rename Settings → Website (§7), retitle `sp-gallery` (§10)
7. **Then** consolidate Themes / Design / Presets into one tabbed Appearance screen (§8)

Steps 2–6 are small and would measurably reduce the "which door?" problem on their own. Step 7 is
the real structural improvement and deserves its own session.

Two things explicitly **not** recommended: rebuilding the group model (it is sound), and touching
the capability layer (it is correct).

---

## Appendix — full screen inventory by group

Effective capability shown is post-remap (`sp_get_menu_capability_map()`, applied at priority 998).

### Members  `members`

| Screen | Slug | Effective capability |
|---|---|---|
| Membership | `sp-members` | `sp_manage_members` |
| Membership Plans | `sp-member-tiers` | `sp_manage_members` |
| Groups | `sp-groups` | `sp_manage_members` |
| $pending_label | `sp-pending-changes` | `sp_manage_members` |
| Shared Addresses | `sp-shared-addresses` | `sp_manage_members` |
| **— Moving data in and out —** | | |
| Import Members | `sp-import` | `sp_manage_members` |
| Export Members | `sp-export` | `sp_manage_members` |

### Money  `finances`

| Screen | Slug | Effective capability |
|---|---|---|
| Payment History | `sp-finances` | `sp_manage_finances` |
| Donations | `sp-donations` | `sp_manage_finances` |
| Campaigns | `sp-campaigns` | `sp_manage_finances` |
| **— Store —** | | |
| Store Products | `sp-store-products` | `sp_manage_finances` |
| Store Orders | `sp-orders` | `sp_manage_finances` |

### Events  `events`

| Screen | Slug | Effective capability |
|---|---|---|
| Events | `sp-events` | `sp_manage_events` |
| Event Categories | `sp-event-categories` | `sp_manage_events` |
| Speakers | `sp-speakers` | `sp_manage_events` |
| External Calendars | `sp-external-calendars` | `sp_manage_events` |
| Import Events | `sp-import-events` | `sp_manage_events` |

### Meetings & Board  `governance`

| Screen | Slug | Effective capability |
|---|---|---|
| My Committee | `sp-chair` | `sp_chair` |
| Meetings & Minutes | `sp-meetings` | `sp_manage_governance` |
| Leadership & Committees | `sp-governance` | `sp_manage_governance` |
| Committees | `sp-committees` | `sp_manage_governance` |
| Ballots | `sp-ballots` | `sp_manage_governance` |
| **— Documents —** | | |
| Documents | `sp-documents` | `sp_manage_content` |
| Document Categories | `sp-document-categories` | `sp_manage_content` |

### Volunteers  `volunteers`

| Screen | Slug | Effective capability |
|---|---|---|
| Volunteer Roster | `sp-volunteer-roster` | `sp_manage_governance` |
| Volunteer Hours | `sp-volunteer-hours` | `sp_manage_governance` |
| Opportunities | `sp-volunteer-opportunities` | `sp_manage_governance` |

### Library  `library`

| Screen | Slug | Effective capability |
|---|---|---|
| Library Catalog | `sp-library-catalog` | `sp_manage_library` |
| Library Categories | `sp-library-categories` | `sp_manage_library` |
| Database Subscriptions | `sp-database-subscriptions` | `sp_manage_library` |
| **— Moving data in and out —** | | |
| Import Library | `sp-import-library` | `sp_manage_library` |
| Library Enrichment | `sp-library-enrich` | `sp_manage_library` |

### Research  `research`

| Screen | Slug | Effective capability |
|---|---|---|
| Research Help | `sp-help-requests` | `sp_manage_content` |
| Research Cases | `sp-research-cases` | `sp_manage_content` |
| Research Guides | `sp-research-guides` | `sp_manage_content` |
| **— Records —** | | |
| Record Collections | `sp-record-collections` | `sp_manage_records` |
| Import Records | `sp-import-records` | `sp_manage_records` |
| Bulk Records Import | `sp-import-records-bulk` | `manage_options` |
| **— Lineage programs —** | | |
| Lineage Programs | `sp-lineage-programs` | `sp_manage_content` |
| Lineage Applications | `sp-lineage-applications` | `sp_manage_content` |
| **— Links for researchers —** | | |
| Resource Links | `sp-library` | `sp_manage_content` |
| Resource Categories | `sp-resource-categories` | `sp_manage_content` |
| Import Links | `sp-import-links` | `sp_manage_content` |

### Newsletters & Email  `communications`

| Screen | Slug | Effective capability |
|---|---|---|
| Blast Email | `sp-blast-email` | `sp_manage_communications` |
| Subscribers | `sp-subscribers` | `sp_manage_communications` |
| Email Templates | `sp-email-templates` | `sp_manage_communications` |
| Email Log | `sp-email-log` | `sp_manage_communications` |
| **— Newsletters —** | | |
| Newsletter Archive | `sp-newsletter-archive` | `sp_manage_communications` |
| Import Newsletters | `sp-import-newsletters` | `manage_options` |

### Website  `appearance`

| Screen | Slug | Effective capability |
|---|---|---|
| Pages | `sp-pages` | `sp_manage_content` |
| Menus | `sp-menus` | `sp_manage_content` |
| Forms | `sp-forms` | `sp_manage_content` |
| Photos & Albums | `sp-gallery` | `sp_manage_content` |
| Media | `upload.php` | `sp_manage_content` |
| Widgets | `widgets.php` | `sp_manage_settings` |
| Short Links | `sp-short-links` | `sp_manage_content` |
| **— How it looks —** | | |
| Themes | `sp-themes` | `sp_manage_settings` |
| Theme Presets | `sp-theme-presets` | `sp_manage_settings` |
| Design | `sp-settings-design` | `sp_manage_settings` |
| **— Moving data in and out —** | | |
| Import ENS Pages | `sp-import-ens-pages` | `sp_manage_content` |
| Import Gallery | `sp-import-gallery` | `sp_manage_content` |

### Reports  `reports`

| Screen | Slug | Effective capability |
|---|---|---|
| Overview | `sp-reports` | `sp_view_reports` |
| Insights | `sp-insights` | `manage_options` |
| Annual Report | `sp-annual-report` | `sp_view_reports` |
| Membership Reports | `sp-membership-reports` | `sp_view_reports` |

### Settings  `settings`

| Screen | Slug | Effective capability |
|---|---|---|
| Site Basics | `sp-settings-website` | `sp_manage_settings` |
| Organization | `sp-settings-organization` | `sp_manage_settings` |
| Membership | `sp-settings-membership` | `sp_manage_settings` |
| Directory | `sp-settings-directory` | `sp_manage_settings` |
| Events | `sp-settings-events` | `sp_manage_settings` |
| Privacy | `sp-settings-privacy` | `sp_manage_settings` |
| Privacy Policy Builder | `privacy-policy-guide.php` | `sp_manage_settings` |
| Export & Backup | `sp-settings-export` | `sp_manage_settings` |
| Modules | `sp-settings-modules` | `sp_manage_settings` |
| User Access | `sp-user-access` | `manage_options` |
| **— Back office —** | | |
| Admin Sidebar | `sp-menu-layout` | `manage_options` |
| **— History —** | | |
| Audit Log | `sp-audit-log` | `sp_manage_settings` |
| Access Log | `sp-access-log` | `sp_manage_settings` |
