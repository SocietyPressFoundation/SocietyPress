# SocietyPress - Development Context

Membership management plugin and theme for WordPress, targeting genealogical and historical societies.

**Current Versions:**
- **Plugin:** 0.23d
- **Theme:** 1.22d

(Development versioning: increment by 0.01 with each change)

## Claude Code Configuration

A `.claude-settings.json` file is configured to auto-approve common operations for this project:
- Read/write/edit operations in project paths
- Rsync operations between XAMPP and source
- Git operations
- File search operations (glob, grep)

This reduces permission prompts while maintaining safety for destructive operations.

## Project Locations

- **Source:** `~/Documents/Development/Web/WordPress/SocietyPress/`
- **XAMPP:** `/Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypress/`
- **XAMPP Theme:** `/Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/themes/societypress/`
- **GitHub:** `https://github.com/charles-stricklin/SocietyPress`

## Deployment Workflow

**IMPORTANT:** XAMPP is the master version. Make all code changes in:
- `/Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypress/`
- `/Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/themes/societypress/`

To sync back to source (for git commits):
```bash
cd ~/Documents/Development/Web/WordPress/SocietyPress
rsync -av --delete /Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypress/ plugin/ --exclude='.git'
rsync -av --delete /Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/themes/societypress/ theme/ --exclude='.git'
```

## Tech Stack

### Plugin
- WordPress plugin (PHP 8.0+)
- Custom database tables (14 tables with `{prefix}sp_` prefix)
- WordPress Settings API for configuration
- WP_List_Table for admin lists
- AES-256-GCM encryption for sensitive data
- GPL v2+ with commercial license validation
- Custom post types (sp_event for events)

### Theme
- WordPress standalone theme (not child theme)
- Custom template parts for events
- Swiper.js for hero carousel
- CSS custom properties for theming
- Responsive design with mobile-first approach

## Key Plugin Classes

| Class | File | Purpose |
|-------|------|---------|
| `SocietyPress` | societypress.php | Main plugin singleton |
| `SocietyPress_Admin` | admin/class-admin.php | Admin controller, menus, pages |
| `SocietyPress_Members` | includes/class-members.php | Member CRUD operations |
| `SocietyPress_Tiers` | includes/class-tiers.php | Membership tier management |
| `SocietyPress_Events` | includes/class-events.php | Events CPT and metadata |
| `SocietyPress_Import` | admin/class-import.php | CSV import with field mapping |
| `SocietyPress_Database` | includes/class-database.php | Schema installation |
| `SocietyPress_Encryption` | includes/class-encryption.php | AES-256-GCM encryption |
| `SocietyPress_Members_List_Table` | admin/class-members-list-table.php | WP_List_Table implementation |
| `SocietyPress_License` | includes/class-license.php | License validation |
| `SocietyPress_Updater` | includes/class-updater.php | Plugin auto-updates |
| `SocietyPress_Theme_Updater` | includes/class-theme-updater.php | Theme auto-updates |

## Database Tables

**Members:**
- `sp_members` - Core member data
- `sp_member_contact` - Contact information
- `sp_member_meta` - Additional metadata
- `sp_member_surnames` - Surnames being researched
- `sp_member_research_areas` - Geographic research areas
- `sp_member_relationships` - Family relationships

**Organization:**
- `sp_membership_tiers` - Membership levels/pricing
- `sp_payments` - Payment history
- `sp_renewal_reminders` - Automated reminders
- `sp_positions` - Leadership positions
- `sp_position_holders` - Who holds positions
- `sp_committees` - Committee definitions
- `sp_committee_members` - Committee membership
- `sp_audit_log` - Change tracking

## Events System

The plugin provides a custom post type (`sp_event`) for managing society events.

**Event Types:**
- Classes
- Workshops
- General Membership Meetings
- Committee Meetings
- Leadership Meetings

**Event Features:**
- Date and time
- Location and address (defaults to "Dwyer Center Classroom" and SAGHS address)
- Instructors field
- Registration required flag
- Recurring events (weekly or monthly nth-day patterns)
- Featured images
- Event categories taxonomy
- Duplicate event functionality

**Recurring Events:**
- Weekly: Repeats every week on the same day
- Monthly: Repeats on the nth weekday (e.g., "2nd Tuesday" or "Last Friday")
- Optional end date for recurrence

## Admin Menu Structure

Flat structure under SocietyPress main menu:
1. Dashboard
2. Leadership (placeholder)
3. Committees (placeholder)
4. Calendar (events list)
5. Add New Event
6. Members
7. Add New Member
8. Import Members
9. Member Levels
10. Library (placeholder)
11. Settings (includes license management)

## Theme Features

- Fixed header with admin bar compensation
- Hero slider on homepage (Swiper.js)
- Custom 404 page (genealogy-themed)
- Event display templates with metadata
- Three navigation menus (Primary, Footer, Utility)
- Three footer widget areas
- Custom image sizes for events and members
- Responsive embeds support

**Theme Helper Functions:**
- `sp_get_event_date()` - Get event date
- `sp_get_event_time()` - Get event time
- `sp_get_event_location()` - Get event location
- `sp_get_event_instructors()` - Get event instructors
- `sp_get_formatted_datetime()` - Formatted date/time
- `sp_is_registration_required()` - Check if registration required
- `sp_is_recurring()` - Check if event is recurring
- `sp_get_recurring_description()` - Get recurrence description

## Target Market

Organizations needing specialized genealogical fields that generic membership plugins don't provide:
- Genealogical societies
- Historical societies
- Heritage organizations
- Family history groups

## Competitors

- **MemberClicks** - Association management
- **GrowthZone** - Chamber/association management
- **MemberLeap** - Membership management
- **Neon CRM** - Nonprofit CRM
- **Wild Apricot** - Membership management
- **StarChapter** - Chapter management

None specifically target genealogical/historical societies with specialized fields (surnames researched, research areas).

## PHP 8 Compatibility Notes

- Use `''` instead of `null` for hidden `add_submenu_page()` parents
- The `$hook` parameter in `admin_enqueue_scripts` can be null
- Always use strict type checking with null coalescing
- Use strict types in function signatures where possible

## Current State

### Completed Features
- ✅ Core member CRUD
- ✅ Tier management
- ✅ CSV Import with auto-mapping
- ✅ CSV Export with filter support
- ✅ Admin UI
- ✅ License validation with developer mode
- ✅ Auto-updates (plugin and theme)
- ✅ Events custom post type
- ✅ Recurring events (weekly and monthly)
- ✅ Event categories
- ✅ Event duplicate functionality
- ✅ Theme with event display
- ✅ Fixed header positioning
- ✅ Custom 404 page

### In Progress
- 🔄 Leadership management (placeholder)
- 🔄 Committees management (placeholder)
- 🔄 Library features (placeholder)

### Not Yet Implemented
- ⏸️ Public member directory
- ⏸️ Member portal
- ⏸️ Payment integration
- ⏸️ Automated renewal reminders
- ⏸️ Calendar view for events

## Auto-Update System

**WordPress Client:**
- `includes/class-updater.php` - Plugin updater
- `includes/class-theme-updater.php` - Theme updater
- Checks StricklinDevelopment.com for updates twice daily
- Downloads authenticated with license key and temporary tokens

**Update Server (deploy to StricklinDevelopment.com):**
- Reference implementation in `server-reference/`
- Plugin endpoints: `/api/v1/plugins/societypress/{update-check,info,download}`
- Theme endpoints: `/api/v1/themes/societypress/{update-check,info,download,generate-token}`
- Database tables: `licenses`, `license_activations`, `download_tokens`
- Install with: `php install.php` (then delete file)
- Test with: `php test-endpoints.php https://stricklindevelopment.com`

**Upload to Server:**
- Plugin ZIPs go in: `versions/societypress-{version}.zip`
- Theme ZIPs go in: `versions/themes/societypress-{version}.zip`
- Update `versions/latest.json` for plugin version
- Update `versions/themes/societypress-latest.json` for theme version

## Developer License

On localhost or when `WP_DEBUG` is true, license checks are automatically bypassed and show "Developer License Active" status.

## Known Issues

- WordPress admin menu system does not support nested flyout submenus on submenu items (platform limitation)
- Events CPT set to `show_in_menu => false` to prevent duplicate menu items
- 404 page image uses hardcoded upload path (2026/01/404.jpg)

## Testing Notes

**Test Site:**
- URL: http://localhost/cms/
- WordPress at: /Applications/XAMPP/xamppfiles/htdocs/cms/
- Site runs at root via index.php redirect
- .htaccess at root level required for permalink rewriting

**Default Event Data:**
- Location: "Dwyer Center Classroom"
- Address: "San Antonio Genealogical and Historical Society\n911 Melissa Dr\nSan Antonio, TX 78213-2024"

## Git Workflow

1. Make changes in XAMPP installation
2. Test thoroughly
3. Run rsync commands to copy to source repo
4. Review changes with `git status` and `git diff`
5. Stage changes: `git add .`
6. Commit: `git commit -m "Description"`
7. Push: `git push origin main`
