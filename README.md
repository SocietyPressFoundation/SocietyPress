# SocietyPress

**Free, open-source WordPress platform for genealogical societies, historical societies, and heritage organizations.**

No pricing. No paid tiers. No upgrades. Community software, freely given.

**Current version: 0.30d**

---

## What It Does

Membership management built for the volunteers who actually run these organizations. Senior-friendly by default. Your society, your server, your control.

### Members
- Full member CRUD with custom fields for genealogical research
- Individual and organizational member support
- 14 membership tiers with configurable pricing and duration
- Statuses: Active, Expired, Pending, Cancelled, Deceased
- CSV import (86+ EasyNetSites column mappings), CSV export with filters
- Searchable member directory (frontend, members-only, configurable privacy)
- Named member groups with bulk assignment
- Member portal (My Account): profile photo, personal info, contact, address, seasonal address, communication preferences, directory privacy, interests & skills, research surnames, registered events, change password
- Frontend email obfuscation (anti-scraper protection)

### Events
- Event management with categories, speakers, time slots
- Recurring events (weekly, monthly nth-day)
- Registration system with capacity limits and waitlists
- Walk-in tracking and attendance management
- Dual pricing display (member + non-member rates)
- Monthly calendar grid (page builder widget or standalone page)
- iCal export, event CSV import

### Library Catalog
- Full catalog management (tested with 19,000+ items)
- OPAC-style frontend: tabbed search (keyword/title/author/subject/call number), browse-by-type cards, popular subjects tag cloud, expandable detail rows, smart pagination
- CSV import/export with field mapping
- Admin stats dashboard (collection value, breakdowns by type/source)
- Open Library API enrichment: batch cover image lookup with admin progress bar

### Newsletters
- PDF newsletter archive with automatic cover image thumbnails (Imagick)
- Admin card grid, frontend grid with inline PDF viewer modal
- Access-controlled downloads (members only)
- Search across the newsletter archive

### Resource Links
- CSV import (supports EasyNetSites format)
- Automatic category creation from imported data
- Frontend directory with search and category filtering
- Integrated with site-wide unified search

### Committees & Leadership
- Committee management with delegated permissions
- Chairperson frontend management, co-chair support
- Leadership positions and terms tracking

### Volunteer System
- Volunteer opportunities with capacity, skills, scheduling
- Frontend signup/cancel via shortcode
- Waitlist with auto-promotion
- Hours tracking with CSV export

### Donations & Campaigns
- Campaign-based donation tracking
- Cash, check, online, in-kind support
- Anonymous donors, progress bars
- Bulk acknowledgment emails

### Blast Email
- Compose and send mass emails to members by group, tier, or all
- Batch sending via WP cron
- Merge tags, opt-out support, per-recipient delivery tracking

### Genealogical Records
- EAV-based flexible record system
- 13 record type templates (Cemetery, Census, Church, Court, Immigration, Land, Marriage, Military, Newspaper, Obituary, Probate, Tax, Vital)
- Admin collection manager with drag-reorder field configurator
- CSV import with field mapping
- Frontend search with per-field access control

### Store
- Public storefront with category sidebar and product grid
- Products sourced from library catalog
- Checkout/payments coming soon

### Page Builder
- 19 widget types for building society pages
- Hero slider with per-line text styling
- Events calendar, member directory, library catalog, and more
- No Gutenberg required — works with the classic editor

### Design System
- CSS custom properties throughout
- 7 color pickers, font/size/width controls, live preview
- Theme uses design system values with sensible fallback defaults

### Email System
- All outgoing emails logged with status tracking
- Dev mode blocks sending (for testing)
- Welcome emails, renewal reminders, registration confirmations, event reminders
- Configurable From/Reply-To headers

### Admin
- Unified sidebar menu with flyout groups
- WordPress branding hidden — your members see your society's name
- Custom login page, site lockdown
- Dashboard with stat cards, upcoming events, expiring members, recent signups
- Setup wizard on first activation

### Security & Privacy
- XChaCha20-Poly1305 encryption for sensitive fields
- GDPR compliance: 5 data exporters + 5 data erasers
- Nonce verification on all forms and AJAX endpoints
- Frontend email obfuscation

### Search
- Unified search across events, library, resources, members, newsletters, and pages
- Per-module result sections with appropriate access controls

---

## Architecture

SocietyPress is a single-file plugin (`societypress.php`) paired with a companion theme. The plugin handles all business logic; the theme handles presentation.

- **Plugin:** `plugin/societypress.php` — function-based, inline JS/CSS, no external dependencies
- **Theme:** `theme/` — classic PHP WordPress theme, CSS custom properties, vanilla JS only
- **Child themes:** Drop a child theme to customize for your society (see `theme-society/` for an example)
- **Database:** 39 custom tables with `{prefix}sp_` naming
- **Settings:** Single `societypress_settings` option array

No jQuery. No CSS frameworks. No Gutenberg. No block editor. No Full Site Editing.

---

## Requirements

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Imagick PHP extension (for newsletter cover thumbnails)
- libsodium PHP extension (for field encryption — included in PHP 7.2+)

---

## Installation

1. Upload the `societypress` folder from `plugin/` to `wp-content/plugins/`
2. Upload the `societypress` folder from `theme/` to `wp-content/themes/`
3. Activate the plugin, then activate the theme
4. Complete the setup wizard to configure your society

---

## Documentation

- `Docs/ARCHITECTURE.md` — Full technical reference (tables, hooks, AJAX, crons, templates)
- `Docs/FEATURES.md` — Complete feature inventory
- `Docs/KNOWN-ISSUES.md` — Known bugs and technical debt
- `Docs/PROJECT-PROMPT.md` — Context prompt for development sessions

---

## License

SocietyPress is released under the [GNU General Public License v2.0](LICENSE) (or later).

Free as in freedom. Free as in beer.
