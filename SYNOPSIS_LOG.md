# SocietyPress Development Log

## Session: January 22, 2026

### Summary
Continued development of SocietyPress WordPress plugin. Completed several feature implementations and fixed a critical PHP 8 compatibility issue.

### Features Implemented

#### 1. Clear Database Bulk Action
- Added "Clear Database" option to bulk actions dropdown
- Single confirmation dialog (not double)
- Positioned at bottom of bulk actions menu

#### 2. Centralized Settings Page
- Created Settings page using WordPress Settings API
- Settings stored in single `societypress_settings` option
- Current settings: `members_per_page`, `organization_name`, `admin_email`
- Replaced Screen Options approach with centralized preferences

#### 3. Expanded Search Functionality
- Search now includes contact fields: city, state, street address, postal code
- Previously only searched first name, last name, and email

#### 4. Filter-Aware Bulk Actions
- "Select all across pages" now respects current filters
- Hidden form fields pass filter state (status, tier, search)
- Bulk actions apply to filtered results, not entire database

#### 5. CSV Export
- Added "Export CSV" button to members page
- Exports respect current filters
- UTF-8 BOM for Excel compatibility
- Includes all member and contact fields

#### 6. Tier Management CRUD
- Full create/read/update/delete for membership tiers
- Tier edit page with all fields (name, slug, price, duration, etc.)
- Delete protection for tiers with assigned members
- Member count links to filtered member list

#### 7. Import Field Mapping Fix
- Fixed auto-detect mapping bug: "State" was incorrectly mapping to "Status"
- Removed 'state' from status detection patterns
- State column now correctly maps to State/Province

### Bug Fixes

#### PHP 8 Deprecation Warnings (Critical)
**Problem:** Two deprecation warnings on every admin page load:
- `strpos(): Passing null to parameter #1 ($haystack)`
- `str_replace(): Passing null to parameter #3 ($subject)`

**Root Cause:** `add_submenu_page(null, ...)` for hidden tier edit page. WordPress passes the parent slug through `wp_normalize_path()` which calls `wp_is_stream()` - both use `strpos()`/`str_replace()` internally. PHP 8 throws deprecation warnings when these receive `null`.

**Solution:** Changed `null` to `''` (empty string) in `add_submenu_page()` call at class-admin.php:314. Both create hidden menu items, but empty string is PHP 8 compatible.

**Debug Process:**
1. Disabled entire admin class - warnings gone (confirmed admin is source)
2. Re-enabled admin, disabled all hooks except `add_menus()` - warnings present
3. Found `add_submenu_page(null, ...)` as culprit

#### Fatal Error: Duplicate Method
- `save_tier()` was declared twice causing fatal error
- Removed placeholder version, kept full implementation

### Files Modified

- `plugin/admin/class-admin.php` - Major changes (settings, export, tier CRUD, bulk actions, PHP 8 fix)
- `plugin/admin/class-members-list-table.php` - Clear Database action, settings integration
- `plugin/admin/class-import.php` - Fixed State/Status mapping bug
- `plugin/includes/class-members.php` - Expanded search fields
- `plugin/assets/js/admin.js` - Select all across pages, bulk delete confirmation
- `CLAUDE.md` - Updated with project context and competitors

### Technical Notes

- WordPress passes `add_submenu_page()` parent slug through path normalization functions
- PHP 8 strict null handling breaks WordPress patterns that worked in PHP 7
- Use `''` instead of `null` for hidden admin pages in PHP 8+
- The `$hook` parameter in `admin_enqueue_scripts` can also be null - add explicit check

### Next Steps (Roadmap)

From GitHub Issue #1:
- Query builder / advanced search
- Custom reports
- License validation system
- Public member directory
- Payment gateway integration
