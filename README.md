# SocietyPress

**Free, open-source WordPress platform for genealogical societies, historical societies, and heritage organizations.**

No pricing. No paid tiers. No upgrades. Community software, freely given.

**Current version: 0.25d**

---

## What It Does

Membership management built for the volunteers who actually run these organizations. Senior-friendly by default. Your society, your server, your control.

### Members
- Full member CRUD with custom fields for genealogical research
- Individual and organizational member support
- Membership tiers, statuses (Active, Expired, Pending, Cancelled, Deceased)
- CSV import with auto-mapping, CSV export with filters
- Searchable member directory (frontend, members-only, privacy layers)

### Events
- Event management with categories, locations, time slots
- Recurring events (weekly, monthly nth-day)
- Registration system with capacity limits and waitlists
- Walk-in tracking and attendance management
- iCal export

### Library Catalog
- Full catalog management for society research libraries (19,000+ items tested)
- CSV import/export with field mapping
- Frontend catalog with expandable item details (AJAX)
- Faceted search and filtering (media type, subject, acquisition source, sort)
- Clickable subject and surname tags
- Admin stats dashboard (collection value, breakdowns by type/source)

### Newsletters
- PDF newsletter archive with automatic cover image thumbnails
- Admin card grid for managing the collection
- Frontend grid with inline PDF viewer modal
- Access-controlled downloads (members only)
- Search across the newsletter archive

### Resource Links
- CSV import (supports EasyNetSites format)
- Automatic category creation from imported data
- Frontend directory with search and category filtering
- Integrated with the site-wide unified search

### Committees & Leadership
- Committee management with delegated permissions
- Chairpersons manage their own committees from the frontend
- Officer positions and terms tracking
- Co-chair support

### Page Builder
- 10 widget types for building society pages
- Hero slider with per-line text styling (size, weight, color per line)
- Events calendar, member directory, library catalog, contact form, and more
- No Gutenberg required — works with the classic editor

### Design System
- CSS custom properties throughout
- 7 color pickers, font/size/width controls
- Live preview in admin
- Theme uses design system values with sensible fallback defaults

### Admin
- Unified sidebar menu with flyout groups
- WordPress branding hidden — your members see your society's name, not WordPress
- Custom login page
- Site lockdown (logged-in users only for frontend, admin-only for backend)

---

## Architecture

SocietyPress is a single-file plugin (`societypress.php`) paired with a companion theme. The plugin handles all business logic; the theme handles presentation.

- **Plugin:** `plugin/societypress.php` — function-based, inline JS/CSS, no external dependencies
- **Theme:** `theme/` — classic PHP WordPress theme, CSS custom properties, vanilla JS only
- **Child themes:** Drop a child theme in `theme/` to customize for your society (see `theme/society/` for an example)
- **Database:** Custom tables with `{prefix}sp_` naming (members, events, library, committees, etc.)
- **Settings:** Single `societypress_settings` option array

No jQuery. No CSS frameworks. No Gutenberg. No block editor. No Full Site Editing.

---

## Requirements

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Imagick PHP extension (for newsletter cover thumbnails)

---

## Installation

1. Upload the `societypress` folder from `plugin/` to `wp-content/plugins/`
2. Upload the `societypress` folder from `theme/` to `wp-content/themes/`
3. Activate the plugin, then activate the theme
4. Visit the SocietyPress settings page to configure your society

---

## License

SocietyPress is released under the [GNU General Public License v2.0](LICENSE) (or later).

Free as in freedom. Free as in beer.
