# SocietyPress Work Log

Breadcrumbs for picking up where we left off after conversation clears.

---

## 2026-01-30 (Session 2)

### Plugin 0.45d: Organization Info Shortcodes

**Added:** Five new shortcodes for embedding organization info anywhere.

| Shortcode | Output | Attributes |
|-----------|--------|------------|
| `[sp_address]` | Organization address | `link="yes"` for Google Maps link |
| `[sp_email]` | Organization email | `link="yes"` for mailto: link (default) |
| `[sp_phone]` | Organization phone | `link="yes"` for tel: link (default) |
| `[sp_website]` | Organization website | `link="yes"` (default), `text="Click here"` |
| `[sp_modified]` | Page last modified date | `format="F j, Y"` (PHP date format) |

**WHY:** Privacy Policy and other pages need dynamic org info that updates automatically when settings change.

**File:** `societypress.php` — added shortcode registrations and handler methods

---

### Privacy Policy Created

Created Privacy Policy page at https://getsocietypress.org/privacy-policy/ using the new shortcodes:
- `[sp_address link="yes"]` for contact address
- `[sp_email]` for contact email
- `[sp_modified]` for "Last Updated" date

**WHY:** Legal requirement, plus demonstrates shortcode usage for other societies.

---

### SSH Access Established

Finally got SSH access to production server working from local Mac.

**Setup:**
1. Created SSH key in cPanel → Security → SSH Access → Manage Keys
2. Downloaded private key to `~/.ssh/claude_code_rsa`
3. Set permissions: `chmod 600 ~/.ssh/claude_code_rsa`

**Commands:**
```bash
# SSH into server
ssh -i ~/.ssh/claude_code_rsa charle24@axm97k5-compute.skystra.com

# Upload files
scp -i ~/.ssh/claude_code_rsa /local/file charle24@axm97k5-compute.skystra.com:~/domains/getsocietypress.org/path/
```

**WHY:** Can now deploy directly without copy-paste or cPanel File Manager.

---

### GitHub Issue #3: Default Location Fields

Created issue for auto-populating 210 area code, San Antonio, and TX when adding new members.

**URL:** https://github.com/charles-stricklin/SocietyPress/issues/3

---

### Welcome Email: NOT Implementing

User decided to set passwords manually for new member accounts rather than send automated welcome emails. Makes sense for small societies where personal touch matters.

Current flow works fine:
1. Create member → link/create WordPress user (random password generated)
2. Admin goes to Users → finds them → sets password or sends reset link
3. Tells member in person or by phone

---

## 2026-01-30 (Session 1)

### Theme 1.33d: Newsletter Sidebar Toggle

**Added:** Configurable sidebar toggle for newsletter pages.

**Features:**
- **Customizer setting:** Appearance → Customize → Layout Settings → "Show Sidebar on Newsletter Pages" (default: on)
- **Page-level override:** When editing a newsletter page, sidebar → "Newsletter Page Options" meta box with dropdown:
  - "Use default" (follows Customizer setting)
  - "Show sidebar"
  - "Hide sidebar (full width)"

**WHY:** Different societies may prefer different layouts. Customizer sets site-wide default, page override allows exceptions.

**Files changed:**
- `inc/customizer.php` - Added `societypress_newsletter_sidebar` setting
- `functions.php` - Added meta box and `societypress_show_newsletter_sidebar()` helper
- `templates/template-newsletters.php` - Uses toggle to conditionally show sidebar

---

### Theme 1.32d: Newsletter Template Sidebar

**Changed:** Added sidebar to `templates/template-newsletters.php`

**WHY:** Consistent layout with other pages, sidebar provides navigation and additional content.

---

### Theme 1.31d: Newsletter Archive Template

**Added:** `templates/template-newsletters.php`

**WHY:** Members-only newsletter archive that displays PDF covers automatically without manual image uploads.

**Features:**
- Scans `wp-content/newsletters/` directory for PDFs
- Parses filenames for year/month (e.g., `2025_02_February_Newsletter.pdf`)
- Handles combined issues (e.g., `2025_07-08_Newsletter_Final.pdf` → "July–August 2025")
- Uses PDF.js to render first page as cover thumbnail — no manual cover uploads needed
- **Members:** Click cover to download PDF
- **Non-members:** See covers and titles but no download links; CTA to log in or join

**Design considerations:**
- Large click targets (entire card is clickable)
- Clear visual feedback on hover
- Responsive grid layout
- Octogenarian-friendly: minimal UI, obvious actions

**Usage:**
1. Upload PDFs to `wp-content/newsletters/` with naming convention `YYYY_MM_Month_Newsletter.pdf`
2. Create/edit a page, select "Newsletters" template
3. Done — newsletters appear automatically

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
- Should org address become default for new events instead of hardcoded SAGHS address?

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

### Completed
1. ~~Organization settings fields~~ ✓
2. ~~Breadcrumb navigation system~~ ✓
3. ~~Hide "Join" menu for logged-in users~~ ✓
4. ~~Use org address as default for new events~~ ✓ (Plugin 0.30d)
5. ~~Link to existing WordPress user dropdown~~ ✓ (Plugin 0.35d)
6. ~~Remove license system → replace with "Support this project"~~ ✓ (Plugin 0.31d)
7. ~~Membership Directory under About Us (members-only)~~ ✓ (Theme 1.27d)
8. ~~Member import: CSV, TSV, XLSX support~~ ✓ (Plugin 0.33d)
9. ~~Event import: CSV, TSV, XLSX support~~ ✓ (Plugin 0.34d)
10. ~~Newsletter archive template~~ ✓ (Theme 1.31d–1.33d)
11. ~~Organization info shortcodes~~ ✓ (Plugin 0.45d)
12. ~~Privacy Policy page~~ ✓
13. ~~SSH access to production~~ ✓

### Pending
14. Auto-populate default location fields (210, San Antonio, TX) — GitHub Issue #3
15. Payment integration
16. Automated renewal reminders
17. Calendar view for events
18. Member portal improvements
19. Public member directory enhancements
20. Build theme style preset system (Parchment, Slate, Ledger, Hearth, Archive, Chronicle) — **ON HOLD until SAGHS launch**

---
