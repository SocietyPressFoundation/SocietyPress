# SocietyPress - Development Context

Membership management plugin for WordPress, targeting genealogical and historical societies.

## Project Locations

- **Source:** `~/Documents/Development/Web/WordPress/SocietyPress/`
- **XAMPP:** `/Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypress-core/`
- **GitHub:** `https://github.com/charles-stricklin/SocietyPress`

## Deployment

To deploy changes to XAMPP for testing:
```bash
cp -R ~/Documents/Development/Web/WordPress/SocietyPress/plugin/* /Applications/XAMPP/xamppfiles/htdocs/cms/wp-content/plugins/societypress-core/
```

## Tech Stack

- WordPress plugin (PHP 8.0+)
- Custom database tables (14 tables with `{prefix}sp_` prefix)
- WordPress Settings API for configuration
- WP_List_Table for admin lists
- AES-256-GCM encryption for sensitive data
- GPL v2+ with commercial license validation

## Key Classes

| Class | File | Purpose |
|-------|------|---------|
| `SocietyPress_Core` | societypress-core.php | Main plugin singleton |
| `SocietyPress_Admin` | admin/class-admin.php | Admin controller, menus, pages |
| `SocietyPress_Members` | includes/class-members.php | Member CRUD operations |
| `SocietyPress_Tiers` | includes/class-tiers.php | Membership tier management |
| `SocietyPress_Import` | admin/class-import.php | CSV import with field mapping |
| `SocietyPress_Database` | includes/class-database.php | Schema installation |
| `SocietyPress_Encryption` | includes/class-encryption.php | AES-256-GCM encryption |
| `SocietyPress_Members_List_Table` | admin/class-members-list-table.php | WP_List_Table implementation |

## Database Tables

Members: `sp_members`, `sp_member_contact`, `sp_member_meta`, `sp_member_surnames`, `sp_member_research_areas`, `sp_member_relationships`

Organization: `sp_membership_tiers`, `sp_payments`, `sp_renewal_reminders`, `sp_positions`, `sp_position_holders`, `sp_committees`, `sp_committee_members`, `sp_audit_log`

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

## Current State

- Core member CRUD: Complete
- Tier management: Complete
- CSV Import: Complete with auto-mapping
- CSV Export: Complete with filter support
- Admin UI: Complete
- Settings: Basic implementation
- License validation: Not yet implemented
- Public directory: Not yet implemented
- Payment integration: Not yet implemented
