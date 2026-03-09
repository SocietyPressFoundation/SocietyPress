# SocietyPress — Known Issues & Technical Debt

Compiled from full codebase audit, March 8, 2026.
Plugin version at time of audit: 0.30d (43,745 lines).

---

## Critical

### 1. Version Mismatch
- **Location:** Plugin header (line ~8) says `Version: 0.25d`, constant `SOCIETYPRESS_VERSION` (line ~30) says `0.30d`
- **Impact:** WordPress shows wrong version in Plugins list; update checks use header version
- **Fix:** Update plugin header to match `SOCIETYPRESS_VERSION`

### 2. Attendance Count NULL Bug
- **Location:** `sp_event_attendance_count()` (around line 24000)
- **Impact:** The `attended` column in `sp_event_registrations` allows NULL. The count query doesn't account for NULL values, so attendance numbers are incorrect for events where attendance hasn't been marked.
- **Fix:** Use `WHERE attended IS NOT NULL AND attended = 1` or `COALESCE(attended, 0)`

### 3. Join Form Creates Member Before Payment
- **Location:** `[societypress_join]` shortcode handler (around line 27500)
- **Impact:** A member record is created and a welcome email is sent before the Stripe PaymentIntent succeeds. If the user abandons payment, there's an orphaned member record with no payment.
- **Fix:** Create member in `pending` status, only activate + send welcome email after Stripe webhook confirms payment

---

## Should Fix

### 4. Merge Tag Syntax Split
- **Location:** `sp_replace_merge_tags()` (line ~37138) uses `{{double_braces}}`, `sp_blast_process_merge_tags()` (line ~38195) uses `{single_braces}`
- **Impact:** Two different syntaxes for the same kind of data substitution. Admins will be confused when one syntax doesn't work in the other context.
- **Fix:** Unify to one syntax (recommend `{{double_braces}}` since it's less likely to conflict with normal text)

### 5. GDPR Gap — Donations Not Covered
- **Location:** Privacy exporters/erasers (lines ~39115-39754) cover members, registrations, speakers, volunteers, help requests — but NOT donations
- **Impact:** Donor name and email in `sp_donations` would be missed in a GDPR privacy/erasure request
- **Fix:** Add a 6th exporter and 6th eraser for `sp_donations`

### 6. Library Item Detail AJAX Missing `nopriv`
- **Location:** `sp_ajax_library_item_detail()` (line ~39764) only registers `wp_ajax_` hook, not `wp_ajax_nopriv_`
- **Impact:** If the library catalog is made public (no login required), non-logged-in users can see catalog rows but can't expand them for detail. The AJAX call silently fails.
- **Fix:** Add `wp_ajax_nopriv_sp_library_item_detail` action, respecting the widget's `login_required` setting

### 7. jQuery Usage Violations
- **Location:** Contact form page builder widget (line ~20100), album/gallery edit page (line ~30500)
- **Impact:** Project policy is vanilla JS only. These use `jQuery()` which works because WP loads jQuery in admin, but breaks the rule and adds an unnecessary dependency.
- **Fix:** Rewrite both to vanilla JS

### 8. Deprecated `get_page_by_title()`
- **Location:** Multiple places in placeholder page registration (around line 8300)
- **Impact:** Deprecated since WP 6.2 (we're on 6.x). Generates deprecation notices in debug log.
- **Fix:** Replace with `new WP_Query( ['title' => $title, 'post_type' => 'page', 'posts_per_page' => 1] )`

### 9. `auto_update_plugin` Filter Scope
- **Location:** Early in plugin file (around line 100)
- **Impact:** The filter callback returns `false` for ALL plugins, not just SocietyPress. This disables auto-updates for every plugin on the site.
- **Fix:** Check `$item->plugin === plugin_basename(__FILE__)` before returning false

### 10. Event Delete Doesn't Clean Up Time Slots
- **Location:** Event delete handler (around line 20800)
- **Impact:** Deleting an event leaves orphaned rows in `sp_event_time_slots`. No foreign key constraints to cascade.
- **Fix:** Add `DELETE FROM sp_event_time_slots WHERE event_id = %d` before deleting the event

### 11. No Rate Limiting on Join Form
- **Location:** `[societypress_join]` POST handler
- **Impact:** No throttling on form submissions — potential for spam member creation or Stripe API abuse
- **Fix:** Add WP transient-based rate limiting (e.g., 3 submissions per IP per hour)

### 12. Server Path Exposure in Event Import
- **Location:** Event import page (around line 21900) — server-side file path in a hidden form field
- **Impact:** Exposes server directory structure to anyone who can access the import page (admin only, but still bad practice)
- **Fix:** Use a transient or session-based key instead of the raw path

### 13. Help Request Notifications Blast All Members
- **Location:** Help request submission handler (around line 32800)
- **Impact:** When a member submits a help request, email notifications go to ALL members with no opt-out mechanism
- **Fix:** Send only to admins or to a configurable notification email address

---

## Cosmetic / Low Priority

### 14. Duplicate `Deceased` Key in CSV Import Map
- **Location:** Import column mapping array (around line 8900)
- **Impact:** Two entries for `Deceased` — the second silently overwrites the first. Both map the same way, so no functional impact.
- **Fix:** Remove the duplicate

### 15. Duplicate `{{organization_name}}` Merge Tag
- **Location:** `sp_replace_merge_tags()` (line ~37170 and ~37173)
- **Impact:** Same key appears twice in the `$tags` array. Identical values, so no functional bug — just dead code.
- **Fix:** Remove the duplicate entry

### 16. Orphaned Import Temp Files
- **Location:** CSV import handlers for members, events, library, records
- **Impact:** Uploaded CSV files stored in `/tmp/` or WP uploads are not cleaned up after import completes
- **Fix:** `unlink()` the temp file after successful processing

### 17. Breadcrumb Settings Without UI
- **Location:** Breadcrumb-related settings keys exist in code, but no admin page renders controls for them
- **Impact:** Settings can only be changed via database — no user-facing way to configure breadcrumbs
- **Fix:** Either add breadcrumb controls to a settings tab or remove the dead settings code

### 18. Hardcoded SAGHS References in Store
- **Location:** `sp_render_store_frontend()` (line ~43455) — filters by `acq_code = 'SAGHS Publication'` and uses SAGHS-specific intro copy
- **Impact:** Other societies deploying SocietyPress would need to modify this code
- **Fix:** Use a settings value for the store's source acq_code and intro text

### 19. Blank Page Template Created at Runtime
- **Location:** Page builder system (around line 19800)
- **Impact:** A blank `sp-builder.php` template file gets created in the theme directory at runtime if it doesn't exist. This writes to the filesystem from the plugin, which is unexpected.
- **Fix:** Register the template via `theme_page_templates` filter only (already done); remove the file-creation fallback

### 20. N+1 Query in Events List Table
- **Location:** `SP_Events_List_Table::prepare_items()` (around line 20500)
- **Impact:** Each event row triggers a separate query for category names, registration count, etc. With many events, this creates excessive database queries.
- **Fix:** JOIN or batch-load related data in the main query

### 21. Missing Deactivation Cleanup for Email Log Cron
- **Location:** `sp_email_log_cleanup` cron is scheduled but never unscheduled on plugin deactivation
- **Impact:** After deactivating SocietyPress, the cron event remains scheduled and throws errors
- **Fix:** Add `wp_clear_scheduled_hook('sp_email_log_cleanup')` to the deactivation hook

### 22. Wizard Lifetime Settings Key Mismatch
- **Location:** Setup wizard (around line 11500) — uses different key names between the save handler and the sanitizer
- **Impact:** Minor — the sanitizer may not correctly validate a wizard-submitted value
- **Fix:** Align key names between wizard save and settings sanitize callback

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
