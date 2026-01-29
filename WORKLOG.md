# SocietyPress Work Log

Breadcrumbs for picking up where we left off after conversation clears.

---

## 2026-01-29

### Session Start
- **Context recovered from:** Reading CLAUDE.md and grepping code
- **Plugin version:** 0.23d
- **Theme version:** 1.22d

### What Was Just Completed
- Added Organization Settings section to Settings page:
  - Organization Name (defaults to blog name)
  - Address (textarea)
  - Phone (tel input)
  - Email (defaults to admin email)
  - Hours (textarea)
  - Social Media links (Facebook, Twitter/X, Instagram, YouTube, LinkedIn)
- Sanitization and defaults in place
- `[societypress_contact]` shortcode mentioned in section description
- Org data available as merge tags in email templates (`{{organization_name}}`)

### Confirmed Working
- `[societypress_contact]` shortcode fully implemented in `public/class-portal.php:280`
  - Shows name, address, phone (tel: link), email (mailto: link), hours, social links
  - Contact form section with plugin detection (WPForms, CF7) or built-in fallback
  - All sections toggleable: `[societypress_contact show_form="no"]`

### Added This Session
- **Four new Organization Settings fields:**
  - Holiday Closures (textarea) - days the org is closed
  - Directions (textarea) - driving directions
  - Parking (textarea) - parking info
  - Facilities (textarea) - explains multiple buildings/spaces (e.g., "Dwyer Center is for meetings, Library is for research")
- **Updated `[societypress_contact]` shortcode** - displays all new fields with toggleable attributes:
  - `show_holidays`, `show_directions`, `show_parking`, `show_facilities`
- **Bumped version:** 0.28d → 0.29d

### Added: Breadcrumb Navigation System
- **Plugin (0.29d):** New "Breadcrumbs" settings section
  - Separator style (>, /, ›, », |, -)
  - Home icon toggle (🏠)
  - Custom home text
- **Theme (1.25d):**
  - `inc/breadcrumbs.php` - breadcrumb generation function + widget class
  - "Below Header" widget area registered
  - Customizer toggle: Appearance > Customize > Breadcrumbs
  - CSS for breadcrumb styling
  - `societypress_breadcrumbs()` template tag available

**Two ways to enable:**
1. Widget: Appearance > Widgets > drag "SocietyPress Breadcrumbs" to "Below Header"
2. Customizer: Appearance > Customize > Breadcrumbs > enable checkbox

### Added: Smart Menu Item Filtering (Theme 1.26d)
- Menu items with "Join", "Become a Member", "Sign Up", or "Register" in the title automatically hide for logged-in users
- No configuration needed - just works
- `functions.php:societypress_filter_menu_items_for_members()`

### Open Questions
- Should org address become default for new events instead of hardcoded the society address?

---

## Future: Theme Style Presets

**Approach:** Single theme with style presets in Customizer. User picks a preset, it populates colors/fonts/layout settings. They can tweak from there.

**Preset Names (approved):**
| Preset | Vibe |
|--------|------|
| **Parchment** | Traditional, serif-heavy, sepia/cream tones |
| **Slate** | Modern, clean, cool grays + accent color |
| **Ledger** | Formal, navy + gold, institutional |
| **Hearth** | Warm earth tones, welcoming, friendly |
| **Archive** | Minimal, whitespace-heavy, research-focused |
| **Chronicle** | Bold headers, editorial feel, high contrast |

**To Build:**
1. Add "Style Preset" dropdown to top of Customizer
2. Define preset arrays (colors, fonts, spacing, header style)
3. When preset selected, populate all Customizer settings
4. User can override individual settings after

**Reference themes reviewed:** Charity Support, SKT Association, VW Charity NGO (wordpress.org)

### Next Up
- Awaiting direction

---

## Task Queue

1. ~~Organization settings fields~~ ✓
2. ~~Breadcrumb navigation system~~ ✓
3. ~~Hide "Join" menu for logged-in users~~ ✓
4. ~~Use org address as default for new events~~ ✓ (Plugin 0.30d)
5. Add "Link to existing WordPress user" dropdown in member edit screen
6. ~~Remove license system → replace with "Support this project"~~ ✓ (Plugin 0.31d)
   - License class simplified to always return valid
   - "License" section renamed to "Support" with donation messaging
   - Updaters simplified - no license auth, freely available downloads
   - License cron job removed
7. ~~Membership Directory under About Us (members-only)~~ ✓ (Theme 1.27d)
   - Menu filter extended to hide "directory" items from logged-out users
   - Removed from My Account dropdown (Plugin 0.32d)
8. ~~Member import: CSV, TSV, XLSX support~~ ✓ (Plugin 0.33d)
   - Added SimpleXLSX library to vendor/
   - Updated import to detect file type and parse accordingly
   - UI updated to show supported formats
9. ~~Event import: CSV, TSV, XLSX support~~ ✓ (Plugin 0.34d)
   - New class `admin/class-import-events.php` with AJAX upload/preview/import
   - Menu item: SocietyPress > Import Events
   - 4-step wizard UI matching member import
   - Auto-detects field mappings from column headers
10. ~~Link to existing WordPress user dropdown~~ ✓ (Plugin 0.35d)
    - Added "WordPress Account" dropdown in member edit form (Contact section)
    - Shows all WP users with display name and email
    - Can link, unlink, or change linked user
    - Updates both member record and user meta
11. Payment integration
12. Automated renewal reminders
13. Calendar view for events
14. Member portal improvements
15. Public member directory enhancements
16. Update URLs to getsocietypress.org when domain is ready (updaters + support link)
17. Build theme style preset system (Parchment, Slate, Ledger, Hearth, Archive, Chronicle) — **ON HOLD until the society launch**

---
