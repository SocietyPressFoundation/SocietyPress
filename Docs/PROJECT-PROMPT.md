# SocietyPress — Project Recreation Prompt

Use this prompt to bootstrap a new Claude Code session from scratch. Copy the entire contents into your first message or load it as context.

---

## What Is SocietyPress?

SocietyPress is a free, open-source, GPL-licensed WordPress plugin + theme platform built for genealogical and historical society administrators. No pricing, no paid tiers, no upgrades — community software, freely given.

The project exists to replace EasyNetSites (ENS), the legacy vendor that most genealogical societies currently use but that is aging badly. SocietyPress is designed for non-technical admins — volunteers who run these organizations.

## Architecture

- **Single-file plugin:** `societypress.php` (~44,000 lines, function-based, inline JS/CSS, no external dependencies)
- **Parent theme:** `societypress/` (classic PHP, CSS custom properties, vanilla JS — no jQuery, no frameworks, no Gutenberg, no FSE)
- **Child theme:** `society/` (for example.org — the society)
- **Database:** 39 custom tables with `{prefix}sp_` naming
- **Settings:** Single `societypress_settings` option array (68 keys)
- **License:** GPL-2.0-or-later

## Key Files & Locations

| Location | Path |
|----------|------|
| Local git clone | `~/Documents/Development/Web/WordPress/SocietyPress/` |
| Plugin file | `plugin/societypress.php` |
| Parent theme | `theme/societypress/` |
| Child theme | `theme-society/society/` |
| Task tracking | `TO-DO.md` |
| Version history | `WORKLOG.md` |
| Architecture ref | `Docs/ARCHITECTURE.md` |
| Feature list | `Docs/FEATURES.md` |
| Known issues | `Docs/KNOWN-ISSUES.md` |
| This prompt | `Docs/PROJECT-PROMPT.md` |

## Deploy Target

**ALWAYS deploy to example.org — NEVER to getsocietypress.org**

```
SSH: ssh -i ~/.ssh/claude_code_rsa charle24@axm97k5-compute.skystra.com
Plugin: ~/domains/example.org/public_html/cms/wp-content/plugins/societypress/
Theme:  ~/domains/example.org/public_html/cms/wp-content/themes/societypress/
Child:  ~/domains/example.org/public_html/cms/wp-content/themes/society/
wp-cli: /usr/local/bin/wp (run from ~/domains/example.org/public_html/cms/)
PHP:    8.3 | WP_DEBUG=true | WP_DEBUG_LOG=true
```

NEVER trust PHP `-r` one-liners via SSH — use `wp eval` instead.

## GitHub

Repository: `charles-stricklin/SocietyPress`
Structure: `plugin/societypress.php` + `theme/` + `theme-society/`

## Version Numbering

Current version: **0.30d**
- Increment by 0.01 per bump (e.g., 0.30d → 0.31d)
- "d" suffix = development
- Record version at top of WORKLOG.md after every bump

## What's Built (0.30d)

17+ modules, 39 database tables, 19 page builder widget types, 34+ AJAX endpoints, 5 cron jobs, 10 frontend page templates, 2 shortcodes.

**Built modules:** Members (CRUD, directory, import/export, portal), Events (calendar, registration, recurring, speakers), Library (19,418 items, OPAC-style catalog, Open Library enrichment), Newsletters (PDF archive, Imagick covers), Resource Links, Committees & Leadership, Volunteers (opportunities, signups, hours), Donations & Campaigns, Blast Email, Genealogical Records (EAV system, 13 templates), Store (frontend only), Page Builder, Design System, Email System (logging + transactional + blast), Join Form (Stripe), Reports, Unified Search, GDPR compliance.

**Not built yet:** Shopping cart/checkout, PayPal, full payment processing, email template editor, style presets, starter content, feature toggle system, demo site, ENS migration guide, AI Q&A, Mailchimp/GA/Zoom integrations.

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
- Settings are a single array option. Access via `get_option('societypress_settings', [])`.
- Page builder widgets have paired functions: `sp_builder_fields_{type}()` for admin, `sp_render_builder_widget_{type}()` for frontend.
- Library categories table exists but isn't used — real taxonomy lives in `media_type` and `subject` columns on `sp_library_items`.
- Two merge tag syntaxes exist (known issue): `{{double}}` for transactional, `{single}` for blast.
- XChaCha20-Poly1305 encryption via libsodium is built but not applied broadly yet.
- `pre_wp_mail` filter intercepts ALL outgoing emails for logging.

### getsocietypress.org (separate project, on hold)
- Marketing/documentation website for SocietyPress
- Own theme at `~/domains/getsocietypress.org/public_html/cms/wp-content/themes/getsocietypress/`
- Classic PHP theme, same constraints (no jQuery, no frameworks, no Gutenberg)
- Currently on hold until plugin features are more complete

## How to Read the Codebase

The plugin file is ~44,000 lines. Here's the rough layout:

```
Lines 1-500:        Plugin header, constants, activation/deactivation hooks
Lines 500-2000:     Table creation (dbDelta), seed data, encryption functions
Lines 2000-5500:    Admin menu registration, admin bar cleanup, flyout menus, lockdown, login, dashboard
Lines 5500-8000:    Member list table, member edit page
Lines 8000-11000:   CSV import/export, settings registration, expiration logic
Lines 11000-14000:  Setup wizard, relationships, audit log, groups, pages, finances
Lines 14000-18000:  Settings page renderers (7 tabs), theme chooser, design system, directory frontend
Lines 18000-21000:  Page builder system (fields functions for 19 widgets), time slots, events list table
Lines 21000-28000:  Event admin, calendar, event frontend, registration AJAX, join form, Stripe
Lines 28000-35000:  User manager, email logging, speakers, recurring, widget renders, reports, library admin
Lines 35000-37000:  Resource links, volunteer system
Lines 37000-38500:  Email system (renewal, welcome, merge tags, blast email)
Lines 38500-39800:  Donations, campaigns, GDPR exporters/erasers
Lines 39800-41200:  Library AJAX/enrichment, newsletter archive
Lines 41200-43500:  Unified search, genealogical records module
Lines 43500-43745:  Store frontend
```

## Known Issues to Be Aware Of

See `Docs/KNOWN-ISSUES.md` for the full list. The top 3:
1. Version mismatch: plugin header says 0.25d, constant says 0.30d
2. Attendance NULL bug in `sp_event_attendance_count()`
3. Join form creates member record before payment confirms

## Reference Documents

- Feature spec: `~/Documents/Sort/societypress-feature-spec.docx` (Feb 2026)
- TO-DO: `~/Documents/Development/Web/WordPress/SocietyPress/TO-DO.md`
- WORKLOG: `WORKLOG.md` in repo root + server copy at `~/domains/getsocietypress.org/public_html/Documentation/WORKLOG.md`
- CLAUDE.md: `~/CLAUDE.md` (server access, getsocietypress.org theme details)
