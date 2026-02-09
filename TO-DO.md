# SocietyPress To-Do List

## Completed ✅

### Core Features
- [x] Core member CRUD operations
- [x] Membership tier management (full CRUD)
- [x] CSV import with field auto-mapping
- [x] CSV export with filter support
- [x] Admin UI with WP_List_Table
- [x] Bulk actions (delete, activate, deactivate)
- [x] "Clear Database" bulk action
- [x] "Select all across pages" functionality
- [x] Filter-aware bulk actions
- [x] Search across contact fields (city, state, address, postal code)
- [x] Centralized Settings page
- [x] PHP 8 compatibility
- [x] Import field mapping fix (State vs Status)
- [x] AES-256-GCM encryption for sensitive data
- [x] License validation with developer mode
- [x] Auto-update system (plugin and theme)

### Events System
- [x] Events custom post type (sp_event)
- [x] Event categories taxonomy
- [x] Event metadata (date, time, location, address, instructors)
- [x] Recurring events (weekly and monthly nth-day patterns)
- [x] Registration required flag
- [x] Event duplicate functionality
- [x] Default location/address for new events
- [x] Event display templates

### Theme
- [x] Standalone WordPress theme
- [x] Hero slider on homepage (Swiper.js)
- [x] Fixed header with admin bar compensation
- [x] Custom 404 page (genealogy-themed)
- [x] Event template parts with metadata display
- [x] Three navigation menus
- [x] Footer widget areas
- [x] Responsive design

### Admin Interface
- [x] Grouped menu structure: SocietyPress (hub), Members, Events (CPT), Organization
- [x] Placeholder pages (Leadership, Committees, Library)
- [x] Dashboard widgets skeleton
- [x] Default starter pages on activation (Home, About, Membership, Events, Contact, Portal, Newsletter, Resources, Leadership)
- [x] WordPress defaults cleanup on activation (Hello World, Sample Page, default comment)

## In Progress 🔄

- [ ] Volunteer opportunities system (plan created, not yet implemented)
- [ ] Library features (placeholder page created)
- [ ] Documentation updates

## Short-Term Priority (MVP)

### Member Features
- [x] Public member directory (shortcode + AJAX search/filter)
- [x] Member self-service portal (profile, events, volunteer commitments)
- [x] Member profile editing (auto-save fields, rate-limited)
- [x] Privacy controls (directory_visible opt-in, field-level visibility)
- [x] Format phone numbers on input
- [x] Email address validation
- [x] Middle name/initial field
- [x] Birth date field
- [x] Address line 2 field

### Events
- [x] Calendar view for events (month view with AJAX navigation)
- [x] Event time slots with capacity
- [x] Event registration form (frontend)
- [x] Event waitlist with auto-promotion
- [x] Admin slots meta box with repeatable rows
- [x] Portal "My Events" widget
- [x] Email notifications for event updates
- [x] Email notification when promoted from waitlist
- [x] iCal export (download .ics from single event pages)

### Communication
- [x] Email notifications (welcome, renewal reminders, expired notices)
- [x] Membership renewal reminders (daily cron, configurable intervals)
- [x] Customizable email templates (pre-filled defaults, HTML support, merge tags)
- [x] Event registration confirmation
- [x] Email notification when promoted from waitlist

### Admin Dashboard
- [x] Dashboard widgets (expiring members, recent signups, upcoming events)
- [x] Quick stats overview (stat cards + efficient GROUP BY query)
- [x] Activity feed (audit log + email log combined)

### Frontend Widgets & Homepage Content
- [x] Welcome new members widget (with consent setting)
- [ ] Member milestones widget (25-year members, research breakthroughs)
- [x] In memoriam section
- [x] Upcoming events widget (next 3-5 events)
- [x] Next general meeting countdown (block widget with category filter)
- [ ] "Registration open" callouts for classes
- [x] Latest newsletter teaser widget
- [x] Research tip of the month
- [ ] Featured library resource widget
- [ ] "This day in local history" widget
- [x] Volunteer spotlight
- [ ] DNA success stories
- [ ] "Members researching [surname]" connection engine
- [x] Hours & location widget
- [x] Quick links widget (renew, directory, contact)
- [x] Announcements banner/widget

## Medium-Term

### Payment Integration
- [ ] Stripe integration
- [ ] PayPal integration
- [ ] Payment gateway selection
- [ ] Online membership renewal
- [ ] Event registration payments
- [ ] Receipt generation

### Automation
- [ ] Renewal automation workflow
- [ ] Failed payment handling
- [ ] Grace period configuration
- [ ] Auto-expire memberships

### Leadership & Committees
- [x] Leadership positions management UI
- [x] Officer term tracking
- [x] Historical leadership records
- [x] Committee management interface
- [x] Committee chair and member assignment
- [x] AJAX member search for assigning positions/committee members
- [ ] Committee meeting scheduling

### Library
- [ ] Library catalog system
- [ ] Check-out tracking
- [ ] Overdue notifications
- [ ] Digital resources index

### Advanced Features
- [ ] Query builder / advanced search
- [ ] Custom reports
- [ ] Analytics dashboard
- [ ] Member relationships UI improvements
- [ ] Bulk email campaigns

### Genealogy Features
- [ ] Surname/research area connection engine ("Members also researching...")
- [ ] GEDCOM import (auto-populate surnames, locations)
- [ ] Basic tree visualization (read-only pedigree chart)
- [ ] DNA match notifications

## Long-Term (Roadmap)

### Integrations
- [ ] REST API for external integrations
- [ ] Zapier/webhook integration
- [ ] Mailchimp integration
- [ ] Constant Contact integration
- [ ] Google Calendar sync
- [ ] Zoom meeting integration
- [ ] QuickBooks integration

### Advanced Platform
- [ ] Multi-site support
- [ ] Chapter management (for organizations with local chapters)
- [ ] White-label options
- [ ] Mobile app companion

### Content Management
- [ ] Event speaker/presenter management
- [ ] Volunteer hour tracking
- [ ] Donor management
- [ ] Grant tracking
- [ ] Publication management (newsletters, journals)
- [ ] Research repository
- [ ] Conference/seminar management

## Technical Debt

### Testing
- [ ] Unit tests for core classes
- [ ] Integration tests
- [ ] End-to-end tests
- [ ] Browser compatibility testing
- [ ] Mobile responsiveness testing
- [ ] Accessibility audit (WCAG 2.1 AA)

### Code Quality
- [ ] PHPDoc documentation completion
- [ ] Code style consistency check
- [ ] Performance optimization for large datasets
- [ ] Database query optimization
- [ ] Caching strategy implementation

### Documentation
- [ ] User handbook
- [ ] Admin guide
- [ ] Developer API documentation
- [ ] Hook and filter reference
- [ ] Video tutorials
- [ ] FAQ section

### Internationalization
- [ ] Generate .pot file
- [ ] Add language packs
- [ ] RTL language support

### Security
- [ ] Two-factor authentication
- [ ] Rate limiting on login
- [ ] GDPR compliance tools
- [ ] Data export for members
- [ ] Data deletion requests
- [ ] Security audit logging

## Known Issues

- 404 page image uses hardcoded upload path (2026/01/404.jpg)

## Version History

### 0.63d (2026-02-08)
- **"Registration Open" Callout Widget:**
  - New `societypress/registration-open` block widget
  - Shows events with open registration and remaining spots
  - Urgency coloring (green/orange/red) based on capacity
  - Links directly to event pages for easy registration
- **Member Milestones Widget:**
  - New `societypress/member-milestones` block widget
  - Celebrates 5/10/15/20/25/30/40/50-year member anniversaries
  - Gold badge design with milestone year counts
- **Event Update Email Notifications:**
  - Automatic email to all registered members when event date, time, or location changes
  - Captures old meta values before save, compares after save
  - Detailed change list in email (exactly what changed, old → new)
  - Only fires for published events with confirmed registrations
  - Respects member communication preferences

### 0.62d (2026-02-08)
- **Event Calendar View:**
  - New `[societypress_calendar]` shortcode with month-view grid
  - AJAX month navigation (no page reload)
  - Events shown as clickable links on their dates
  - Today highlighted with blue circle
  - Responsive: collapses to dot indicators on mobile
  - New files: `class-calendar.php`, `calendar.css`, `calendar.js`
- **Next Meeting Countdown Widget:**
  - New `societypress/meeting-countdown` block widget
  - Shows days until next event with big countdown number
  - Supports event category filtering (e.g., "meetings" only)
  - Shows event title, date, time, and location
  - Special "Today!" and "Tomorrow!" states

### 0.61d (2026-02-08)
- **Enriched SocietyPress Dashboard:**
  - Replaced 4 separate get_members() calls with efficient GROUP BY query via dashboard widgets
  - Added Memberships Expiring Soon section (color-coded: red ≤7 days, orange >7)
  - Added Recent New Members section with linked names
  - Added Upcoming Events section (next 5 events, linked to edit)
  - Added Recent Activity feed (combined audit log + email log)
  - Expanded Quick Actions (Add Member, Import, Add Event, Settings)
  - Two-column responsive layout with .dashboard-row CSS
- **Event Registration Confirmation Email:**
  - Automatic email on successful event registration
  - Includes event title, date, time, location, and event page link
  - Fires via new `societypress_event_registered` action hook
- **Waitlist Promotion Email:**
  - Automatic email when member is promoted from waitlist to confirmed
  - Hooks into existing `societypress_waitlist_promoted` action
  - Includes full event details and link to event page
- **iCal Export:**
  - Download .ics files for any published event via `?sp_ical={id}`
  - "Add to Calendar" link on single event pages
  - RFC 5545 compliant, works with Google Calendar, Apple Calendar, Outlook
  - Static helper: `SocietyPress_Events::get_ical_url()`
- Made dashboard widget data methods public (get_expiring_members, get_recent_signups, get_member_stats)

### 0.60d (2026-02-08)
- **Admin Menu Restructure:**
  - Split flat SocietyPress menu into 4 grouped top-level menus
  - SocietyPress (Dashboard, Library, Email Log, Settings)
  - Members (All Members, Add New, Import, Member Levels)
  - Events (auto-created by CPT — All Events, Add New, Event Categories, Import Events)
  - Organization (Leadership, Committees)
  - Events CPT now has `show_in_menu => true` with `menu_position => 32`
  - Hero Slider nesting fix (explicit admin_menu registration at priority 20)
- **Default Content on Activation:**
  - New `SocietyPress_Default_Content` class
  - Deletes WordPress defaults (Hello World, Sample Page, default comment)
  - Creates 9 starter pages with guided placeholder content
  - Sets Home as static front page
  - Only runs once (option flag prevents re-runs)
- **Email Template Improvements:**
  - Settings textareas now pre-populated with default templates
  - Template methods changed to public static for Settings page access
  - Sanitization switched from `sanitize_textarea_field` to `wp_kses_post`
  - Textarea rows increased from 6 to 14 for readability
- Removed dead submenu fixup code from `class-leadership-admin.php`

### 0.55d (2026-02-01)
- **Leadership Admin UI:**
  - Full admin interface for managing positions
  - Assign members to positions with term dates
  - View position history, end terms
  - Delete unused positions
- **Committees Admin UI:**
  - Full admin interface for managing committees
  - Add/remove members with roles (Chair, Co-Chair, Member)
  - Inline role dropdown for quick changes
  - Track join/leave dates
- **AJAX Member Search:**
  - Replaced dropdown selects with searchable fields
  - Type 2+ characters to search by first or last name
  - Keyboard navigation (arrow keys, Enter, Escape)
  - Committees search excludes existing members
- **Bug fixes:**
  - Fixed strip_tags() PHP 8.1 deprecation warning on hidden admin pages
  - Fixed SOCIETYPRESS_PLUGIN_URL → SOCIETYPRESS_URL in class-widgets.php

### 0.47d + Theme 1.34d (2026-01-30)
- **Event Slots & Registration System:**
  - Multiple time slots per event with capacity limits
  - Frontend registration on single event pages
  - Waitlist with auto-promotion when spots open
  - Admin meta box with repeatable slot rows
  - Portal "My Events" widget showing registrations
  - Cancel from event page or portal
- **New database tables:** `sp_event_slots`, `sp_event_registrations`
- **New classes:** `SocietyPress_Event_Slots`, `SocietyPress_Event_Registrations`, `SocietyPress_Event_Registration_Frontend`
- **New hook:** `sp_event_after_content` in theme for frontend registration display

### 0.46d (2026-01-30)
- Leadership and Committees management system

### 0.45d (2026-01-30)
- Organization info shortcodes ([sp_address], [sp_email], etc.)

### 0.23d (2026-01-27)
- **WordPress User Integration:** Automatic user account creation for members
- Custom "Society Member" WordPress role with portal access capability
- Auto-creates WordPress users on member creation (manual add and CSV import)
- Links to existing WordPress user if email matches
- Username = email address, auto-generated secure password
- Bulk action: "Create User Accounts" for existing members without accounts
- "Edit WordPress User" link on member edit page when user exists
- Member list table shows detailed results (created, linked, skipped)
- Email sending disabled internally during development/testing

### 0.22d (2026-01-27)
- Added expiration date auto-calculation
- Settings: Choose between Calendar Year (12/31) or Anniversary (join date + duration) models
- JavaScript auto-calculates expiration when join date or tier changes
- Calendar Year model: Expires December 31 of join year
- Anniversary model: Expires on join date + tier duration months
- Expiration field remains editable for manual overrides
- Visual feedback on auto-calculation

### 0.21d (2026-01-27)
- Added middle name field to members
- Added birth date field to members
- Added address line 2 field
- Implemented phone number auto-formatting ((XXX) XXX-XXXX)
- Added comprehensive email validation (frontend and backend)
- Email validation in CSV import
- Join date displays as year only with full date on hover
- Updated CSV export to include new fields

### 0.20d (2026-01-27)
- Added duplicate event functionality
- Restructured admin menu to flat structure
- Added Calendar and Add New Event menu items
- Set Events CPT to not show in menu

### Theme 1.22d (2026-01-27)
- Fixed header positioning (changed to fixed from sticky)
- Added admin bar compensation (32px desktop, 46px mobile)
- Added recurring event display with icon
- Added instructor display with icon
- Set default event location and address

### 0.19d (2026-01-26)
- Added recurring events (weekly and monthly patterns)
- Added instructors field to events
- Added event categories taxonomy
- Admin menu iterations (attempted nested structure)

### 0.18d (2026-01-25)
- Created Events custom post type
- Added event metadata fields
- Created event template parts
- Genealogy-themed 404 page

### 0.17d (2026-01-24)
- Completed auto-update system
- Added theme updater class
- Created server reference implementation

### 0.16d and earlier
- Core membership features
- Import/export functionality
- License validation
- Database schema
- AES-256-GCM encryption

---

*Also see: [GitHub Issues](https://github.com/charles-stricklin/SocietyPress/issues)*
