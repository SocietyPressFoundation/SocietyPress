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
- [x] Flat menu structure (Dashboard → Leadership → Committees → Calendar → Add New Event → Members → Add New Member → Import Members → Member Levels → Library → Settings)
- [x] Calendar menu item (events list)
- [x] Placeholder pages (Leadership, Committees, Library)
- [x] Dashboard widgets skeleton

## Tomorrow's Priority 📋

### Verify Event Registration System (0.47d)
1. Deactivate and reactivate plugin to create new database tables
2. Create test event with 3 time slots (10-11, 11-12, 12-1), capacity 1 each
3. Log in as member, register for first slot
4. Verify "Registered" badge shows, other slots still available
5. Check portal "My Events" widget shows the registration
6. Register second member, verify first slot shows "Full"
7. Third member sees "Join Waitlist" for first slot
8. Cancel first registration → verify waitlist auto-promotes
9. Cancel from portal → verify removed from list

### If Time Permits
- Test registration URL field still works (external registration)
- Test with capacity NULL (unlimited)
- Test recurring event with slots

---

## In Progress 🔄

- [x] Event slots and registration system (0.47d - verified working)
- [x] Leadership management (full admin UI with member search)
- [x] Committees management (full admin UI with member search)
- [ ] Volunteer opportunities system (plan created, not yet implemented)
- [ ] Library features (placeholder page created)
- [ ] Documentation updates

## Short-Term Priority (MVP)

### Member Features
- [ ] Public member directory (shortcode)
- [ ] Member self-service portal
- [ ] Member profile editing
- [ ] Privacy controls (show/hide fields publicly)
- [x] Format phone numbers on input
- [x] Email address validation
- [x] Middle name/initial field
- [x] Birth date field
- [x] Address line 2 field

### Events
- [ ] Calendar view for events (month/week/day)
- [x] Event time slots with capacity
- [x] Event registration form (frontend)
- [x] Event waitlist with auto-promotion
- [x] Admin slots meta box with repeatable rows
- [x] Portal "My Events" widget
- [ ] Email notifications for event updates
- [ ] Email notification when promoted from waitlist
- [ ] iCal export

### Communication
- [ ] Email notifications (welcome, renewal reminders)
- [ ] Event registration confirmation
- [ ] Membership renewal reminders
- [ ] Customizable email templates

### Admin Dashboard
- [ ] Dashboard widgets (expiring members, recent signups, upcoming events)
- [ ] Quick stats overview
- [ ] Activity feed

### Frontend Widgets & Homepage Content
- [ ] Welcome new members widget (with consent setting)
- [ ] Member milestones widget (25-year members, research breakthroughs)
- [ ] In memoriam section
- [ ] Upcoming events widget (next 3-5 events)
- [ ] Next general meeting countdown
- [ ] "Registration open" callouts for classes
- [ ] Latest newsletter teaser widget
- [ ] Research tip of the month
- [ ] Featured library resource widget
- [ ] "This day in local history" widget
- [ ] Volunteer spotlight
- [ ] DNA success stories
- [ ] "Members researching [surname]" connection engine
- [ ] Hours & location widget
- [ ] Quick links widget (renew, directory, contact)
- [ ] Holiday closures / announcements banner

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

- WordPress admin menu system does not support nested flyout submenus on submenu items (platform limitation - resolved with flat menu structure)
- 404 page image uses hardcoded upload path (2026/01/404.jpg)
- Events CPT set to `show_in_menu => false` to prevent duplicate menu items

## Version History

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
