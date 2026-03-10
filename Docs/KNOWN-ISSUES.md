# SocietyPress — Known Issues & Technical Debt

Compiled from full codebase audit, March 8, 2026.
Plugin version at time of audit: 0.30d (43,745 lines).

---

## Critical

### 1. Version Mismatch — FIXED
- **Location:** Plugin header (line ~8) and constant `SOCIETYPRESS_VERSION` (line ~30)
- **Impact:** WordPress showed wrong version in Plugins list; update checks used header version
- **Fix:** Updated plugin header to `0.30d` to match the constant

### 2. Attendance Count NULL Bug — FIXED
- **Location:** Annual report (around line 32880)
- **Impact:** The `attended` column in `sp_event_registrations` allows NULL (not yet marked), 1 (attended), 0 (no-show). The annual report had no attendance stat at all — it only counted confirmed registrations, not who actually showed up.
- **Fix:** Added a proper attendance query (`WHERE attended = 1`) and "Attended" stat card to the annual report. The original `sp_event_attendance_count()` function referenced here never existed.

### 3. Join Form Creates Member Before Payment — FIXED
- **Location:** `[societypress_join]` shortcode handler (around line 27500) and `sp_send_welcome_email()` (around line 37080)
- **Impact:** A member record was created as 'pending' (correct) but the welcome email fired immediately, before any payment or admin approval.
- **Fix:** Added a `pending` status guard in `sp_send_welcome_email()` — pending members no longer receive the welcome email. Added a pending→active transition check in the member save handler that sends the welcome email when an admin approves the member. Also added welcome email for admin-created members who start as active.

---

## Should Fix

### 4. Merge Tag Syntax Split — FIXED
- **Location:** `sp_blast_process_merge_tags()` (line ~38240)
- **Fix:** Unified blast emails to `{{double_braces}}` syntax, with legacy `{single_braces}` fallback for existing saved templates. Also removed duplicate `{{organization_name}}` key in `sp_replace_merge_tags()`.

### 5. GDPR Gap — Donations — FIXED
- **Location:** Privacy exporters/erasers
- **Fix:** Added 6th exporter (`sp_privacy_export_donation_data`) and 6th eraser (`sp_privacy_erase_donation_data`). Exports donor name/email/amount/campaign; eraser anonymizes donor info while retaining financial records for tax reporting.

### 6. Library Item Detail AJAX — FIXED
- **Fix:** Added `wp_ajax_nopriv_sp_library_item_detail` action so catalog expand works for non-logged-in users when the widget is public.

### 7. jQuery Usage Violations — DEFERRED
- **Location:** Contact form widget frontend (line ~19413), album edit page (line ~32717), page builder admin (line ~18592)
- **Impact:** Project policy is vanilla JS only. These use jQuery. The page builder admin is hundreds of lines of jQuery — substantial rewrite.
- **Status:** Deferred — requires significant effort, low user-facing impact since jQuery is always available in WP admin.

### 8. Deprecated `get_page_by_title()` — FIXED
- **Fix:** Replaced all 3 instances with `new WP_Query()` using `title` parameter.

### 9. `auto_update_plugin` Filter Scope — FIXED
- **Fix:** Replaced `__return_true` with scoped callbacks that only affect SocietyPress plugin/theme, leaving other plugins' auto-update behavior alone.

### 10. Event Delete / Time Slots — WAS ALREADY FIXED
- Both single and bulk delete paths already call `sp_slots_delete_by_event()`. The audit missed this.

### 11. Rate Limiting on Join Form — FIXED
- **Fix:** Added transient-based rate limiting: 3 submissions per IP per hour. Silently blocks after threshold.

### 12. Server Path Exposure in Event Import — DEFERRED
- **Location:** 5 import flows expose server temp file paths in hidden form fields
- **Status:** Requires refactoring all import flows to use transient keys. Admin-only pages, low risk. Deferred.

### 13. Help Request Notifications — FIXED
- **Fix:** Changed from emailing ALL active members to only emailing administrators (users with `manage_options` capability). Setting still controls whether notifications are sent at all.

---

## Cosmetic / Low Priority

### 14. Duplicate `Deceased` Key in CSV Import Map — FIXED
- **Fix:** Removed the duplicate `__meta` mapping. The `'deceased'` field mapping (for status logic) now works correctly without being overwritten.

### 15. Duplicate `{{organization_name}}` Merge Tag — FIXED
- **Fix:** Removed the duplicate entry in `sp_replace_merge_tags()`.

### 16. Orphaned Import Temp Files — WAS ALREADY FIXED
- All 5 CSV import flows (members, events, library, resource links, records) already call `@unlink( $temp_file )` after processing completes. The audit missed this. Only edge case: abandoned imports (preview but never execute) — temp files linger until the OS cleans them.

### 17. Breadcrumb Settings Without UI — FIXED
- **Fix:** Added breadcrumb controls to Settings → Website: enable/disable checkbox, home label text field, separator character field. Added sanitizer entries and page-key registration so they save correctly. Updated `sp_breadcrumbs()` default separator to match the settings UI default (literal `›` instead of `&rsaquo;`).

### 18. Hardcoded the society References in Store — FIXED
- **Fix:** Replaced hardcoded `acq_code = 'Society Publication'` with configurable `store_acq_code` setting (Settings → Organization → Store). Store intro text also configurable via `store_intro_text` setting. Blank acq_code shows all priced library items.

### 19. Blank Page Template Created at Runtime — FIXED
- **Fix:** Replaced `init`-hook `file_put_contents()` in the plugin directory with `sp_get_blank_template_path()` that uses `sys_get_temp_dir()` and only creates the file lazily when actually needed by `template_include`. Plugin no longer writes to its own directory at runtime.

### 20. N+1 Query in Events List Table — FIXED
- **Fix:** Main query in `prepare_items()` now JOINs the categories table and uses a subquery for registration counts. `column_category()` and `column_registrations()` use pre-fetched data instead of per-row queries. Eliminates ~2N extra queries per page load.

### 21. Missing Deactivation Cleanup for Email Log Cron — FIXED
- **Fix:** Added `wp_clear_scheduled_hook('sp_email_log_cleanup_cron')` to the existing deactivation hook.

### 22. Wizard Lifetime Settings Key Mismatch — FIXED
- **Fix:** Added `'lifetime'` to the allowed values in the `membership_period_type` sanitizer (was only `annual`/`rolling`). Also added "Lifetime" radio option to the Membership settings page to match the wizard's dropdown.

---

## i18n Gaps

These sections have user-facing strings NOT wrapped in WordPress i18n functions:

- Volunteer system (admin + frontend): opportunity management, signup messages, hours logging
- Store frontend: "Add to Cart", "Publications", "Browse our collection"
- Resource links admin: "No resources yet.", "Category saved.", "Category deleted."
- Many admin notice/success messages throughout the plugin
- Event import page strings
- Various error messages in AJAX handlers

**Plan:** Retroactive i18n pass on all strings before beta release.
