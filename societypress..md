SocietyPress Project Overview

Purpose

A commercial WordPress plugin for membership management in genealogical
societies, historical societies, and heritage organizations. It differentiates
from generic plugins with specialized genealogy fields (surnames researched,
research areas) and governance tracking (committees, officer positions).

Target Market: Organizations needing genealogy-specific features.
Website: getsocietypress.org

---
Paths

Location: Source (Git)
Path: ~/Documents/Development/Web/WordPress/SocietyPress/

Location: Plugin Code
Path: ~/Documents/Development/Web/WordPress/SocietyPress/plugin/

Location: Theme Code
Path: ~/Documents/Development/Web/WordPress/SocietyPress/theme/

Location: Local Dev Site
Path: ~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/

Location: GitHub
Path: https://github.com/charles-stricklin/SocietyPress

---
Current Versions

- Plugin: 0.53d
- Theme: 1.36d

---
Environment

- WordPress 6.0+, PHP 8.0+, MySQL 5.7+
- 16 custom database tables with sp_ prefix
- AES-256-GCM encryption for sensitive data
- OOP architecture with singleton main class
- Shareware model (works without license)

---
Completed Work

Core Features:
- Full member CRUD with multiple statuses
- Membership tier management
- CSV/TSV/XLSX import with intelligent field mapping
- CSV export respecting filters
- WP_List_Table admin interface (sortable, searchable, filterable)
- Bulk actions with "select all across pages"
- Genealogy fields (surnames, research areas)
- Encryption, audit logging, capability checks
- PHP 8 compatibility
- WordPress user account linking

Events System:
- Events custom post type with categories
- Event metadata (date, time, location, instructors)
- Recurring events (weekly and monthly patterns)
- **Event time slots with capacity limits**
- **Member registration for slots**
- **Waitlist with auto-promotion**
- Duplicate event functionality

Leadership & Committees:
- Positions management with term tracking
- Committee management with roles (Chair, Vice-Chair, Member)
- Default positions/committees seeding

Member Portal:
- Self-service profile editing
- "My Events" widget showing registrations
- Cancel registrations from portal

Theme:
- Standalone WordPress theme
- Hero slider (Swiper.js)
- Fixed header with admin bar compensation
- Newsletter archive template (PDF.js auto-thumbnails)
- Smart menu filtering (hide Join for logged-in users)
- Breadcrumb navigation system

---
Recent Work (0.47d)

Event Slots & Registration System:
- Multiple time slots per event
- Capacity limits with waitlist
- Frontend registration on single event pages
- Portal integration for viewing/cancelling
- Admin meta box with repeatable rows
- Auto-promotion from waitlist

New database tables: sp_event_slots, sp_event_registrations

---
Next Up

1. Verify event registration system works end-to-end
2. Email notification when promoted from waitlist
3. Calendar view for events
4. Payment gateway integration

---
Technical Debt

- Unit/integration tests needed
- i18n .pot file generation
- Accessibility audit
- PHPDoc documentation
- Performance optimization for large datasets

---
Known Issues

- 404 page image uses hardcoded upload path (2026/01/404.jpg)
