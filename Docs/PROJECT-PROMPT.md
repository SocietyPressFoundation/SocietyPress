# SocietyPress — Project Recreation Prompt

Use this prompt to bootstrap a new Claude Code session from scratch. Copy the entire contents into your first message or load it as context.

---

## What Is SocietyPress?

SocietyPress is a free, open-source, GPL-licensed WordPress plugin + theme platform built for genealogical and historical society administrators. No pricing, no paid tiers, no upgrades — community software, freely given.

The project exists to replace EasyNetSites (ENS), the legacy vendor that most genealogical societies currently use but that is aging badly. SocietyPress is designed for non-technical admins — volunteers who run these organizations.

## Architecture

- **Single-file plugin:** `societypress.php` (~52,500 lines, function-based, inline JS/CSS, no external dependencies)
- **Parent theme:** `societypress/` (classic PHP, CSS custom properties, vanilla JS — no jQuery, no frameworks, no Gutenberg, no FSE)
- **Child theme:** `saghs/` (for kndgs.org — San Antonio Genealogical & Historical Society)
- **Database:** 43 custom tables with `{prefix}sp_` naming
- **Settings:** Single `societypress_settings` option array (70+ keys, 8 tabs including Modules)
- **License:** GPL-2.0-or-later

## Key Files & Locations

| Location | Path |
|----------|------|
| Local git clone | `~/Documents/Development/Web/WordPress/SocietyPress/` |
| Plugin file | `plugin/societypress.php` |
| Parent theme | `theme/societypress/` |
| Child theme | `theme-saghs/saghs/` |
| Task tracking | `TO-DO.md` |
| Version history | `WORKLOG.md` |
| Architecture ref | `Docs/ARCHITECTURE.md` |
| Feature list | `Docs/FEATURES.md` |
| Known issues | `Docs/KNOWN-ISSUES.md` |
| This prompt | `Docs/PROJECT-PROMPT.md` |

## Deploy Target

**ALWAYS deploy to kndgs.org — NEVER to getsocietypress.org**

```
SSH: ssh -i ~/.ssh/claude_code_rsa charle24@axm97k5-compute.skystra.com
Plugin: ~/domains/kndgs.org/public_html/cms/wp-content/plugins/societypress/
Theme:  ~/domains/kndgs.org/public_html/cms/wp-content/themes/societypress/
Child:  ~/domains/kndgs.org/public_html/cms/wp-content/themes/saghs/
wp-cli: /usr/local/bin/wp (run from ~/domains/kndgs.org/public_html/cms/)
PHP:    8.3 | WP_DEBUG=true | WP_DEBUG_LOG=true
```

NEVER trust PHP `-r` one-liners via SSH — use `wp eval` instead.

## GitHub

Repository: `charles-stricklin/SocietyPress`
Structure: `plugin/societypress.php` + `theme/` + `theme-saghs/`

## Version Numbering

Current version: **0.38d**
- Increment by 0.01 per bump (e.g., 0.37d → 0.38d)
- "d" suffix = development
- Record version at top of WORKLOG.md after every bump

## What's Built (0.38d)

20+ modules, 43 database tables, 19 page builder widget types, 46 AJAX endpoints, 5 cron jobs, 16 frontend page templates, 2 shortcodes, 12-module toggle system.

**Built modules:** Members (CRUD, directory, import/export, portal, encryption, couples/household, pending profile changes), Events (calendar, registration, recurring, speakers, .ics export, notice-only events), Library (19,418 items, OPAC-style catalog, Open Library enrichment), Newsletters (PDF archive, Imagick covers), Resource Links, Committees & Leadership, Volunteers (opportunities, signups, hours), Donations & Campaigns, Blast Email, Genealogical Records (EAV system, 13 templates), Store (cart, Stripe checkout, order management), Documents (bulk upload, access control), Page Builder, Design System (6 style presets), Email System (logging + transactional + blast + template editor), Join Form (Stripe), Reports, Unified Search, GDPR compliance (6 exporters + 6 erasers), Roles & Permissions (10 access areas, 8 role templates), GitHub update system (plugin + parent theme + child theme gallery), Google Analytics, Starter Content (15 auto-created pages), Feature Toggles (12 modules).

**Not built yet:** PayPal integration, voting & elections, PWA, demo site, ENS migration guide, AI Q&A, Mailchimp/Zoom integrations, full data portability exports, one-click installer.

See `Docs/FEATURES.md` for the complete inventory and `TO-DO.md` for remaining work.

## Critical Context

### Code Style — Non-Negotiable
- Complete files ONLY — never snippets, never partial files
- Detailed comments explaining WHY, not just what
- Vanilla JS only — no jQuery
- No Gutenberg, no FSE, no theme.json, no CSS frameworks
- Don't reference "WordPress" in user-facing UI — the admin shouldn't know what powers the site
- All new/changed code uses i18n functions with `societypress` text domain

### Charles' Preferences
- Possessive: "Charles'" (not "Charles's")
- Never ask "shall I build this?" or prompt to continue — Charles drives, I build
- Never suggest stopping, sleeping, eating, or any personal activity
- Open source ≠ open to contributions
- "End session" routine: update TO-DO.md → update WORKLOG.md → bump version → commit → push

### Important Technical Notes
- The plugin is a SINGLE FILE. All functions, CSS, and JS are inline. No external dependencies.
- Settings are a single array option (70+ keys). Access via `get_option('societypress_settings', [])`.
- 12 feature modules can be toggled on/off via Settings → Modules. Members is always enabled.
- Page builder widgets have paired functions: `sp_builder_fields_{type}()` for admin, `sp_render_builder_widget_{type}()` for frontend.
- Library categories table exists but isn't used — real taxonomy lives in `media_type` and `subject` columns on `sp_library_items`.
- Merge tags unified to `{{double_braces}}` syntax everywhere. Legacy `{single}` fallback in blast emails.
- XChaCha20-Poly1305 encryption via libsodium applied to 7 sensitive member contact fields.
- `pre_wp_mail` filter intercepts ALL outgoing emails for logging.
- Email template editor (tabbed) allows customizing Welcome, Renewal Reminder, and Expiration Notice templates with merge tags.
- 6 style presets (Parchment, Slate, Ledger, Hearth, Archive, Chronicle) on the Design settings page.
- Starter content auto-creates 15 pages on fresh activation.
- 10 access areas + 8 role templates for granular staff permissions via `sp_user_can()`.

### getsocietypress.org (separate project, on hold)
- Marketing/documentation website for SocietyPress
- Own theme at `~/domains/getsocietypress.org/public_html/cms/wp-content/themes/getsocietypress/`
- Classic PHP theme, same constraints (no jQuery, no frameworks, no Gutenberg)
- Currently on hold until plugin features are more complete

## How to Read the Codebase

The plugin file is ~52,500 lines. Here's the rough layout:

```
Lines 1-500:        Plugin header, constants, activation/deactivation hooks, starter content
Lines 500-2500:     Table creation (43 tables via dbDelta), seed data, encryption functions, module toggle system
Lines 2500-6000:    Admin menu registration, admin bar cleanup, flyout menus, roles & permissions, lockdown, login, dashboard
Lines 6000-8000:    Member list table, member edit page
Lines 8000-11000:   CSV import/export, settings registration, expiration logic
Lines 11000-15000:  Setup wizard, relationships, audit log, groups, pages, finances, pending profile changes
Lines 15000-19000:  Settings page renderers (8 tabs), theme chooser, design system, style presets, directory frontend
Lines 19000-22000:  Page builder system (fields functions for 19 widgets), time slots, events list table
Lines 22000-28000:  Event admin, calendar, event frontend, registration AJAX
Lines 28000-37000:  Join form, Stripe, user manager, email logging, speakers, recurring, widget renders, reports, library admin
Lines 37000-42000:  Resource links, volunteer system, email system (renewal, welcome, merge tags, template editor, blast email)
Lines 42000-46000:  Donations, campaigns, GDPR exporters/erasers (6+6), library AJAX/enrichment, newsletter archive
Lines 46000-49000:  Unified search, genealogical records module
Lines 49000-52500:  Store (storefront, cart, checkout, Stripe sessions, order management), documents module
```

## Known Issues to Be Aware Of

See `Docs/KNOWN-ISSUES.md` for the full list. The previous top 3 critical bugs are all fixed (version mismatch, attendance NULL, join form payment order). Current remaining issues:
1. jQuery usage in contact form widget, album edit page, and page builder admin (project policy: vanilla JS only) — substantial rewrite, deferred
2. Server path exposure in event import hidden fields — requires refactoring 5 import flows to use transients, deferred
3. Calendar bug: current month renders full-width, other months render narrower — same HTML/CSS from server, likely browser rendering/caching

## Reference Documents

- Feature spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
- TO-DO: `~/Documents/Development/Web/WordPress/SocietyPress/TO-DO.md`
- WORKLOG: `WORKLOG.md` in repo root + server copy at `~/domains/getsocietypress.org/public_html/Documentation/WORKLOG.md`
- CLAUDE.md: `~/CLAUDE.md` (server access, getsocietypress.org theme details)
