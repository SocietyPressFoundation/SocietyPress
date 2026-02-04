# SocietyPress - Development Context

Membership management plugin and theme for WordPress, targeting genealogical and historical societies.

**Current Versions:**
- **Plugin:** 0.58d
- **Theme:** 1.37d

(Development versioning: increment by 0.01 with each change)

## Project Locations

- **Source/Git:** `~/Documents/Development/Web/WordPress/SocietyPress/`
- **GitHub:** `https://github.com/charles-stricklin/SocietyPress`

### getsocietypress.org Local Development
- **WordPress Root:** `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/`
- **Plugin:** `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/wp-content/plugins/societypress/`
- **Theme:** `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/wp-content/themes/societypress/`
- **Released Plugin ZIPs:** `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/api/v1/plugins/societypress/`
- **Released Theme ZIPs:** `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/api/v1/themes/societypress/`
- **Other SocietyPress Files:** `~/Documents/Development/Web/My Sites/getsocietypress.org/SocietyPress/`

## Production Server (getsocietypress.org)

**Hosting:** Skystra (cPanel)
- **cPanel URL:** `https://cp.axm97k5-compute.skystra.com:2222`
- **cPanel Username:** `charle24`
- **Server:** `axm97k5-compute.skystra.com`

**SSH Access (from Mac):**
```bash
ssh -i ~/.ssh/claude_code_rsa charle24@axm97k5-compute.skystra.com
```

**SCP uploads:**
```bash
scp -i ~/.ssh/claude_code_rsa /local/file charle24@axm97k5-compute.skystra.com:~/domains/getsocietypress.org/path/
```

**Key location:** `~/.ssh/claude_code_rsa`

**Alternative:** cPanel web Terminal (System Info & Files → Terminal)

**Server Paths:**
| Location | Path |
|----------|------|
| Home directory | `/home/charle24/` |
| getsocietypress.org root | `~/domains/getsocietypress.org/public_html/` |
| WordPress | `~/domains/getsocietypress.org/public_html/cms/` |
| SocietyPress plugin | `~/domains/getsocietypress.org/public_html/cms/wp-content/plugins/societypress/` |
| SocietyPress theme | `~/domains/getsocietypress.org/public_html/cms/wp-content/themes/societypress/` |

**Other Domains on Same Account:**
- stricklindevelopment.com (update server API)
- charlesstricklin.com
- alamocitycatcafe.com
- appcrafting.dev
- everydaytechguide.com
- wellnesscheck.dev
- wellnesswatch.dev

**Database Access:** phpMyAdmin via cPanel → Tools → phpMyAdmin

**File Management Options:**
1. cPanel Terminal (command line)
2. cPanel File Manager (GUI)
3. Copy/paste file contents through chat

## Deployment Workflow

**Local development** is done in:
- `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/wp-content/plugins/societypress/`
- `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/wp-content/themes/societypress/`

**Newsletters directory:**
- Local: `~/Documents/Development/Web/My Sites/getsocietypress.org/public_html/cms/wp-content/newsletters/`
- Production: `~/domains/getsocietypress.org/public_html/cms/wp-content/newsletters/`
- Naming: `YYYY_MM_Month_Newsletter.pdf` (e.g., `2025_02_February_Newsletter.pdf`)
- Combined issues: `YYYY_MM-MM_Newsletter.pdf` (e.g., `2025_07-08_Newsletter.pdf`)

**To create releases:**
```bash
# Plugin
cd ~/Documents/Development/Web/My\ Sites/getsocietypress.org/public_html/cms/wp-content/plugins
zip -r "~/Documents/Development/Web/My Sites/getsocietypress.org/SocietyPress/societypress-{version}.zip" societypress -x "*.git*" -x "*.DS_Store"

# Theme
cd ~/Documents/Development/Web/My\ Sites/getsocietypress.org/public_html/cms/wp-content/themes
zip -r "~/Documents/Development/Web/My Sites/getsocietypress.org/SocietyPress/societypress-theme-{version}.zip" societypress -x "*.git*" -x "*.DS_Store"
```

**To deploy to production:**
Upload ZIP via cPanel File Manager, extract, set permissions.

## Tech Stack

### Plugin
- WordPress plugin (PHP 8.0+)
- Custom database tables (16 tables with `{prefix}sp_` prefix)
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
| `SocietyPress_Leadership` | includes/class-leadership.php | Positions and holders |
| `SocietyPress_Leadership_Admin` | admin/class-leadership-admin.php | Leadership admin UI |
| `SocietyPress_Committees` | includes/class-committees.php | Committees and members |
| `SocietyPress_Committees_Admin` | admin/class-committees-admin.php | Committees admin UI |
| `SocietyPress_Event_Slots` | includes/class-event-slots.php | Event time slots CRUD |
| `SocietyPress_Event_Registrations` | includes/class-event-registrations.php | Registration and waitlist |
| `SocietyPress_Event_Registration_Frontend` | public/class-event-registration-frontend.php | Frontend registration UI |
| `SocietyPress_Widgets` | public/class-widgets.php | Block-based public widgets |
| `SocietyPress_Email_Log` | includes/class-email-log.php | Email logging and admin viewer |

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

**Events:**
- `sp_event_slots` - Event time slots with capacity
- `sp_event_registrations` - Member registrations for slots

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
- Location and address (defaults to "Dwyer Center Classroom" and the society address)
- Instructors field
- Registration required flag
- Recurring events (weekly or monthly nth-day patterns)
- Featured images
- Event categories taxonomy
- Duplicate event functionality
- **Time slots with capacity** — multiple sessions per event (e.g., 10-11 AM, 11-12 PM)
- **Member registration** — members register for specific slots from event page
- **Waitlist** — auto-promotes when spots open from cancellations

**Registration System:**
- Slots admin meta box with repeatable rows (start/end time, capacity, description)
- Frontend registration table on single event pages
- Portal "My Events" widget showing upcoming registrations
- Cancel from event page or portal
- `do_action('sp_event_after_content', $event_id)` hook for registration display

**Recurring Events:**
- Weekly: Repeats every week on the same day
- Monthly: Repeats on the nth weekday (e.g., "2nd Tuesday" or "Last Friday")
- Optional end date for recurrence

## Admin Menu Structure

Flat structure under SocietyPress main menu:
1. Dashboard
2. Leadership (positions and holders management)
3. Committees (committees and members management)
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
- Newsletter archive template (PDF.js auto-thumbnails, members-only downloads)
- Three navigation menus (Primary, Footer, Utility)
- Three footer widget areas
- Custom image sizes for events and members
- Responsive embeds support
- Smart menu filtering (hides Join/Register for logged-in, Directory for logged-out)

**Theme Helper Functions:**
- `sp_get_event_date()` - Get event date
- `sp_get_event_time()` - Get event time
- `sp_get_event_location()` - Get event location
- `sp_get_event_instructors()` - Get event instructors
- `sp_get_formatted_datetime()` - Formatted date/time
- `sp_is_registration_required()` - Check if registration required
- `sp_is_recurring()` - Check if event is recurring
- `sp_get_recurring_description()` - Get recurrence description

**Plugin Shortcodes:**

| Shortcode | Output | Attributes |
|-----------|--------|------------|
| `[societypress_contact]` | Full contact block | `show_form`, `show_hours`, etc. |
| `[sp_address]` | Organization address | `link="yes"` for Maps link |
| `[sp_email]` | Organization email | `link="yes"` (default) |
| `[sp_phone]` | Organization phone | `link="yes"` (default) |
| `[sp_website]` | Organization website | `link="yes"`, `text="..."` |
| `[sp_modified]` | Page last modified | `format="F j, Y"` |

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
- ✅ CSV/TSV/XLSX Import with auto-mapping (members and events)
- ✅ CSV Export with filter support
- ✅ Admin UI
- ✅ Auto-updates (plugin and theme) — shareware model, no license enforcement
- ✅ Events custom post type
- ✅ Recurring events (weekly and monthly)
- ✅ Event categories and duplicate functionality
- ✅ Theme with event display
- ✅ Fixed header positioning
- ✅ Custom 404 page
- ✅ State code validation with autocomplete
- ✅ Organization settings with shortcodes
- ✅ Breadcrumb navigation system
- ✅ Newsletter archive template (PDF.js thumbnails)
- ✅ Smart menu item filtering
- ✅ Link WordPress user to member
- ✅ Leadership management (positions, board of directors, term tracking)
- ✅ Committees management (roles: Chair, Vice-Chair, Member)

### In Progress
- 🔄 Library features (placeholder)

### Not Yet Implemented
- ⏸️ Default location field auto-populate (GitHub Issue #3)
- ⏸️ Payment integration
- ⏸️ Automated renewal reminders
- ⏸️ Calendar view for events
- ⏸️ Theme style presets (ON HOLD until the society launch)

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

**Default Event Data:**
- Location: "Dwyer Center Classroom"
- Address: "the society\n911 Melissa Dr\nSpringfield, TX 78213-2024"

**Default Join Form Data:**
- City: "Springfield"
- State: "TX"
- Phone prefix: "(210) "

## Git Workflow

1. Make changes in local development installation
2. Test thoroughly
3. Review changes with `git status` and `git diff`
4. Stage changes: `git add .`
5. Commit: `git commit -m "Description"`
6. Push: `git push origin main`
