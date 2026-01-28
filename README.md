# SocietyPress

**Commercial WordPress plugin for genealogical societies, historical societies, and heritage organizations.**

A valid license key is required. [Purchase at stricklindevelopment.com/studiopress/societypress](https://stricklindevelopment.com/studiopress/societypress)

---

## Overview

Membership management built for the volunteers who actually run these organizations: dues and renewals, searchable member directories, committee management, and governance tracking.

Senior-friendly by default. No subscriptions required to use your own data. Your society, your server, your control.

## Features

### Member Management
- Full CRUD operations with custom fields for genealogical research
- Surnames researched and geographic research areas
- Multiple statuses: Active, Expired, Pending, Cancelled, Deceased
- Contact info with multiple emails, phones, and addresses
- Directory visibility and communication preferences

### Membership Tiers
- Configurable levels (Individual, Family, Student, Lifetime, Institutional)
- Custom pricing, duration, and member limits per tier
- Active/inactive tier management

### Import/Export
- CSV import with intelligent field auto-mapping
- Supports Wild Apricot, Excel, and other membership systems
- Filter-aware CSV export

### Admin Interface
- WordPress-native UI with WP_List_Table
- Sortable, searchable, filterable member lists
- Bulk actions with "select all across pages"
- Centralized settings page

### Security
- AES-256-GCM encryption for sensitive data
- Prepared statements, nonce verification, capability checks
- Full audit logging

## Requirements

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Valid SocietyPress license key

## Installation

1. Purchase a license at [stricklindevelopment.com/studiopress/societypress](https://stricklindevelopment.com/studiopress/societypress)
2. Download the plugin from your account
3. Upload to `/wp-content/plugins/societypress/`
4. Activate in WordPress admin
5. Enter your license key in Settings > SocietyPress

## File Structure

```
plugin/
├── societypress.php              # Main plugin file
├── admin/
│   ├── class-admin.php           # Admin controller
│   ├── class-import.php          # CSV import handler
│   └── class-members-list-table.php
├── includes/
│   ├── class-database.php        # Schema installation
│   ├── class-encryption.php      # AES-256-GCM
│   ├── class-members.php         # Member operations
│   └── class-tiers.php           # Tier management
└── assets/
    ├── css/admin.css
    └── js/{admin,import}.js
```

## License

This is **commercial software**. The source code is licensed under GPL v2+, but a valid license key is required for use. See [LICENSE](LICENSE) for details.

"SocietyPress" is a trademark of Charles Stricklin.

## Support

Licensed users receive support at [stricklindevelopment.com/studiopress/support](https://stricklindevelopment.com/studiopress/support)

## Author

Charles Stricklin
[stricklindevelopment.com/studiopress](https://stricklindevelopment.com/studiopress/)
