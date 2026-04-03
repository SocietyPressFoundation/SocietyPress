# SocietyPress — Known Issues & Technical Debt

Originally compiled from full codebase audit, March 8, 2026 (v0.30d).
Updated April 3, 2026 — all 43 items resolved and fixed.

---

## Critical — All Fixed

### 1. Version Mismatch — FIXED (v0.30d)
- **Fix:** Updated plugin header to match the constant.

### 2. Attendance Count NULL Bug — FIXED (v0.30d)
- **Fix:** Added proper attendance query (`WHERE attended = 1`) and "Attended" stat card to annual report.

### 3. Join Form Creates Member Before Payment — FIXED (v0.30d)
- **Fix:** Added pending status guard in `sp_send_welcome_email()`. Welcome email fires on admin approval, not form submission.

### 4. DB Version Never Saved After Upgrade — FIXED (v0.38d)
- **Location:** `admin_init` upgrade hook (line ~2058)
- **Impact:** `sp_create_tables()` and all 6 seeding functions ran on every admin page load — silent performance killer.
- **Fix:** Added `update_option( 'societypress_db_version', SOCIETYPRESS_VERSION )` after upgrade completes.

### 5. Record Collection Field ID Orphaning — FIXED (v0.38d)
- **Location:** Collection edit save handler (line ~48596)
- **Impact:** Editing a collection's field schema deleted all fields and re-inserted with new auto-increment IDs, orphaning all `sp_record_values.field_id` references. Editing fields after importing data silently broke all existing records.
- **Fix:** Replaced delete-all + re-insert with update-in-place pattern. Existing fields are updated by ID, new fields inserted, only removed fields deleted. Hidden `field_id[]` input added to form to round-trip IDs.

### 6. Incomplete Cascade Delete on Member Removal — FIXED (v0.38d)
- **Location:** 3 delete handlers (single, bulk, "Delete All Others") around line 9023
- **Impact:** Only 4 of 16+ related tables were cleaned up, leaving orphaned rows in event registrations, volunteer data, research areas, donations, orders, etc.
- **Fix:** Extracted `sp_cascade_delete_member_data()` helper that cleans up all 16 related tables. All 3 delete paths now call it.

---

## Security — All Fixed

### 7. Document Download Bypasses Access Control — FIXED (v0.38d)
- **Impact:** Frontend document links went directly to `wp_get_attachment_url()`. Anyone with the URL could download members-only documents.
- **Fix:** Added `sp_ajax_document_download()` AJAX handler with access control. Frontend links now route through the handler.

### 8. Guest Registration Cancel Ownership Gap — FIXED (v0.38d)
- **Impact:** Any logged-in member could cancel a guest's event registration by knowing the ID.
- **Fix:** Added null user_id check — only admins can cancel guest registrations.

### 9. Missing Nonce on Blast Recipient Count — FIXED (v0.38d)
- **Fix:** Added `check_ajax_referer()` to `sp_ajax_blast_recipient_count()`.

### 10. Hardcoded SAGHS Email in Plugin — FIXED (v0.38d)
- **Impact:** `President@txsaghs.org` hardcoded in post-login acknowledgment modal.
- **Fix:** Replaced with `$settings['org_email']` from settings.

### 11. Unescaped get_the_title() XSS — FIXED (v0.38d)
- **Fix:** Wrapped in `esc_html()` at line ~23608 and verified other instances.

---

## Should Fix — Resolved

### 12. Merge Tag Syntax Split — FIXED (v0.30d)
### 13. GDPR Gap — Donations — FIXED (v0.30d)
### 14. Library Item Detail AJAX — FIXED (v0.30d)
### 15. Deprecated get_page_by_title() — FIXED (v0.30d)
### 16. auto_update_plugin Filter Scope — FIXED (v0.30d)
### 17. Rate Limiting on Join Form — FIXED (v0.30d)
### 18. Help Request Notifications — FIXED (v0.30d)

---

## Code Quality — Fixed in v0.38d

### 19. date() Used Instead of wp_date() — FIXED
- **Impact:** ~20 instances used PHP `date()` for display, ignoring site timezone.
- **Fix:** Replaced all display-context `date()` calls with `wp_date()`.

### 20. admin_url() Without esc_url() — FIXED
- **Impact:** ~40+ unescaped `admin_url()` calls in HTML output contexts.
- **Fix:** Wrapped all in `esc_url()`.

### 21. Stripe Refund Doesn't Handle Pending Status — FIXED
- **Fix:** Changed to accept both 'succeeded' and 'pending', with distinct messages.

### 22. ICS Line Folding Breaks Multibyte Characters — FIXED
- **Fix:** Replaced `strlen()`/`substr()` with `mb_strlen()`/`mb_substr()`.

### 23. preferred_name Fallback PHP 8.x Notice — FIXED
- **Fix:** Added null coalescing before the ternary operator.

### 24. wp_enqueue_media() Called After Output — FIXED
- **Fix:** Moved to top of `sp_render_speaker_edit_page()`.

### 25. N+1 Query on Frontend Event Listing — FIXED
- **Fix:** Added batch query for registration counts before the card loop.

### 26. Library Stats Transient Never Invalidated — FIXED
- **Fix:** Added `delete_transient()` calls after item create/update/delete/import.

### 27. Email Template Hardcodes WP-Blue — FIXED
- **Fix:** `sp_build_email_html()` now uses `design_color_primary` from settings.

### 28. Dashicons Loaded for All Visitors — FIXED
- **Fix:** Wrapped in `is_user_logged_in()` check.

### 29. Merge Tag Documentation Inconsistency — FIXED
- **Fix:** Blast compose page now documents `{{double_braces}}` matching the template editor.

### 30. Blank Template /tmp/ Race Condition — FIXED
- **Fix:** Changed from `sys_get_temp_dir()` to `wp_upload_dir()['basedir']`.

### 31. N+1 Query in Events Admin List Table — FIXED (v0.30d)
### 32. Missing Deactivation Cleanup for Email Log Cron — FIXED (v0.30d)
### 33. Wizard Lifetime Settings Key Mismatch — FIXED (v0.30d)
### 34. Breadcrumb Settings Without UI — FIXED (v0.30d)
### 35. Hardcoded SAGHS References in Store — FIXED (v0.30d)
### 36. Blank Page Template Created at Runtime — FIXED (v0.30d)

---

## i18n — FIXED (v0.38d)

Comprehensive i18n pass completed March 18, 2026. ~500+ strings wrapped across the entire plugin. Text domain `societypress` now appears 2,564+ times. Coverage estimated at ~95%.

---

## Previously Deferred — Now Fixed

### 37. jQuery Usage Violations — FIXED (v0.49d)
- **Location:** Contact form widget frontend, album edit page, page builder admin
- **Fix:** All 3 jQuery violations rewritten to vanilla JS. Only wpColorPicker init remains (unavoidable WP dependency).

### 38. Server Path Exposure in Import Flows — FIXED (v0.41d)
- **Location:** 5 import flows exposed server temp file paths in hidden form fields
- **Fix:** Hidden fields now store basename only, readback validates with realpath + directory containment check.

---

## Resolved — Verified v0.47d

### 39. Hardcoded USD Currency Symbol — FIXED
- **Fix:** `sp_format_currency()` helper (line ~2577) with configurable symbol placement via settings. 22+ call sites converted.

### 40. GET-Based Destructive Actions — FIXED
- **Fix:** Group/page/payment deletes converted from GET links to POST forms with `wp_nonce_field()` / `check_admin_referer()`.

### 41. Duplicate Code (Font Map, Status Lists) — FIXED
- **Fix:** Extracted `sp_get_font_family_css()` (line ~4440) and `sp_get_member_statuses()` (line ~4469) helpers. All call sites updated.

### 42. Dual Library Catalog Implementations — FIXED
- **Fix:** Unified to single `sp_render_builder_widget_library_catalog()` OPAC-style widget with tabbed search, faceted filters, and stats.

### 43. Donation Acknowledgment Email Not Customizable — FIXED
- **Fix:** Integrated into email template editor system. Customizable subject + body via Settings → Email Templates, with donation merge tags.
