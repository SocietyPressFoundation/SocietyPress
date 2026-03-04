# SocietyPress — WORKLOG

## v0.27d — 2026-03-03

### Member Portal (My Account)
- Created WP page (ID 148, slug `my-account`, template `page-my-account.php`)
- Fixed broken Research Surnames: replaced nonexistent `location` column with real schema (`county`, `state`, `country`, `year_from`, `year_to`) in both template and plugin handler
- Added Interests & Skills section (two textareas, new `update_interests` handler)
- Added Blast Email Opt-out checkbox to Communication Preferences
- Moved all inline styles (surnames, events sections) to scoped CSS classes
- Full i18n pass: all user-facing strings wrapped in `__()` / `esc_html__()` / `esc_attr__()` with `societypress` text domain
- Added success messages for interests, surnames, and event cancellation

### Directory
- Added `wp_nav_menu_objects` filter to hide Directory nav link for logged-out visitors (template-based, not hardcoded to menu item ID)
- Changed surname filter label from "Surname" to "Surname Being Researched"
- Set matching height (38px + box-sizing) on filter selects and inputs

### User Cleanup
- Merged duplicate `charleswstricklin` accounts (282 + 3958): moved member record to 282, fixed email typo (txfsghs → txsaghs), deleted empty 3958

### Known Issues
- Calendar width inconsistency: current month full-width, other months narrower. Server HTML is identical — needs browser-side investigation.
