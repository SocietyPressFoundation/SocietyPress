# SocietyPress — Architecture Reference

Last updated: May 10, 2026
Plugin version: 1.0.72 | ~86,200 lines | Single-file, function-based

---

## File Structure

```
SocietyPress/
├── Code/                             # All deployable code
│   ├── plugin/
│   │   └── societypress.php          # The entire plugin (~86,200 lines)
│   ├── theme/                        # Parent theme (CSS vars, fallback defaults)
│   ├── theme-coastline/              # Child theme
│   ├── theme-heritage/               # Child theme
│   ├── theme-ledger/                 # Child theme
│   ├── theme-parlor/                 # Child theme
│   ├── theme-prairie/                # Child theme
│   ├── installer/                    # One-click installer (sp-installer.php)
│   └── softaculous/                  # Softaculous packaging
├── docs/
│   ├── ARCHITECTURE.md               # This file
│   ├── FEATURES.md                   # Complete feature inventory
│   └── ENS-MIGRATION-GUIDE.md        # Migrating from EasyNetSites
├── .github/                          # Issue/PR templates, funding, workflows
├── scripts/                          # Shell scripts (deploy, nuke, reset, build)
│   ├── deploy.sh
│   ├── nuke-demo.sh
│   ├── reset-demo.sh
│   └── build-softaculous.sh
├── CHANGELOG.md                      # Release history
├── CODE_OF_CONDUCT.md                # Contributor Covenant
├── CONTRIBUTING.md                   # Contribution policy
├── ROADMAP.md                        # Planned work
├── SECURITY.md                       # Vulnerability reporting
├── SUPPORT.md                        # Where to get help
├── README.md                         # Project overview
├── LICENSE                           # GPL-2.0-or-later
└── .gitignore
```

---

## Constants

```php
SOCIETYPRESS_VERSION      // '1.0.72' (current release)
SOCIETYPRESS_PLUGIN_DIR   // plugin_dir_path(__FILE__)
SOCIETYPRESS_PLUGIN_URL   // plugin_dir_url(__FILE__)
SOCIETYPRESS_PLUGIN_FILE  // __FILE__
SOCIETYPRESS_GITHUB_REPO  // 'SocietyPressFoundation/SocietyPress'
```

---

## Settings

Single option: `get_option('societypress_settings', [])`

70+ keys across 8 tabs:
1. **Website** — site_name, tagline, logo, favicon, lockdown toggle, login page, analytics_google_id, analytics_exclude_admins, breadcrumb settings
2. **Organization** — org_name, org_address, org_phone, org_email, org_type, founded_year, store_acq_code, store_intro_text
3. **Membership** — default_tier, auto_expire, grace_period, require_approval, genealogy service toggles (8), email template subjects
4. **Directory** — visibility, searchable fields, privacy defaults, show_surnames, profile_changes_require_approval
5. **Events** — default_capacity, allow_waitlist, reminder_days, calendar options
6. **Privacy** — data_retention, cookie_notice, GDPR toggles
7. **Design** — 7 color pickers, font_family, font_size, heading_font, content_width, sidebar_width, custom_css, style presets
8. **Modules** — enable/disable 15 toggleable feature modules (card grid with toggle switches, Enable All / Disable All); Members is always-on so total = 16

Settings sanitized via `sp_sanitize_settings()` callback registered with `register_setting()`.

Additional standalone options:
- `societypress_db_version` — tracks schema version for migrations
- `sp_wizard_completed` — marks setup wizard as done
- `sp_enabled_modules` — array of enabled module slugs (15 toggleable; Members always-on)

---

## Database Tables (~60)

All prefixed with `{$wpdb->prefix}sp_`.

### Members (6 tables)
| Table | Description |
|-------|-------------|
| `sp_members` | Core member records (individual + org) |
| `sp_member_groups` | Named groups for organizing members |
| `sp_member_group_assignments` | Many-to-many: members ↔ groups |
| `sp_member_surnames` | Surnames being researched (per member) |
| `sp_member_research_areas` | Geographic research areas with time periods |
| `sp_member_relationships` | Member-to-member relationships (spouse, family, referral) |

### Membership (1 table)
| Table | Description |
|-------|-------------|
| `sp_membership_tiers` | Tier definitions with pricing and duration |

### Events (6 tables)
| Table | Description |
|-------|-------------|
| `sp_events` | Event records |
| `sp_event_categories` | Event category taxonomy |
| `sp_event_category_assignments` | Many-to-many: events ↔ categories |
| `sp_event_registrations` | Registration records with status + attendance |
| `sp_event_speakers` | Speaker profiles per event |
| `sp_event_time_slots` | Multiple time slots per event |

### Library (2 tables)
| Table | Description |
|-------|-------------|
| `sp_library_items` | Catalog items (books, maps, periodicals, etc.) |
| `sp_library_categories` | Library categories (unused — real taxonomy is media_type/subject fields) |

### Committees & Leadership (4 tables)
| Table | Description |
|-------|-------------|
| `sp_committees` | Committee definitions |
| `sp_committee_members` | Many-to-many: members ↔ committees with roles |
| `sp_leadership_positions` | Named positions (President, VP, etc.) |
| `sp_leadership_terms` | Term records linking members to positions with date ranges |

### Volunteers (4 tables)
| Table | Description |
|-------|-------------|
| `sp_volunteer_opportunities` | Posted volunteer opportunities |
| `sp_volunteer_signups` | Member signups for opportunities (with status) |
| `sp_volunteer_roles` | Standing volunteer role assignments |
| `sp_volunteer_hours` | Logged volunteer hours per member |

### Communications (4 tables)
| Table | Description |
|-------|-------------|
| `sp_email_log` | All outgoing emails logged via `pre_wp_mail` |
| `sp_blast_emails` | Blast email campaigns |
| `sp_blast_email_recipients` | Per-recipient delivery tracking for blasts |
| `sp_renewal_reminders` | Dedup table for renewal reminder emails |

### Finances (2 tables)
| Table | Description |
|-------|-------------|
| `sp_donations` | Individual donation records |
| `sp_campaigns` | Fundraising campaign definitions |

### Content (4 tables)
| Table | Description |
|-------|-------------|
| `sp_newsletters` | Newsletter archive (PDF + cover metadata) |
| `sp_resources` | External resource links |
| `sp_resource_categories` | Resource link categories |
| `sp_pages` | Plugin-managed pages (internal tracking) |

### Genealogical Records (4 tables, EAV architecture)
| Table | Description |
|-------|-------------|
| `sp_record_collections` | Collection definitions (Cemetery, Census, etc.) |
| `sp_record_collection_fields` | Field definitions per collection (drag-reorderable) |
| `sp_records` | Individual record entries with `search_text` column |
| `sp_record_values` | EAV value storage: record_id + field_id + value |

### Documents (2 tables)
| Table | Description |
|-------|-------------|
| `sp_documents` | Document records with file metadata, access level, category |
| `sp_document_categories` | Document categories (Meeting Minutes, Society Documents, etc.) |

### Store (2 tables)
| Table | Description |
|-------|-------------|
| `sp_store_orders` | Order records with status, totals, Stripe session/payment IDs |
| `sp_store_order_items` | Line items per order (product, quantity, price) |

### Members — Additional (1 table)
| Table | Description |
|-------|-------------|
| `sp_pending_profile_changes` | Queued profile changes awaiting admin approval |

### Voting (4 tables)
| Table | Description |
|-------|-------------|
| `sp_ballots` | Ballot definitions with open/close windows |
| `sp_ballot_questions` | Questions per ballot |
| `sp_ballot_choices` | Choices per question (yes/no, single, multi) |
| `sp_ballot_votes` | Per-voter cast records (dedup-enforced) |

### Lineage Programs (3 tables)
| Table | Description |
|-------|-------------|
| `sp_lineage_programs` | Program definitions (First Families, Pioneer Settlers, etc.) |
| `sp_lineage_applications` | Member applications with status workflow |
| `sp_lineage_proofs` | Uploaded proof documents per application |

### Help Requests & Research Services (5 tables)
| Table | Description |
|-------|-------------|
| `sp_help_requests` | Public Q&A submissions (free comradery model) |
| `sp_help_responses` | Member responses with time-tracked hours |
| `sp_research_cases` | Paid research case records |
| `sp_research_invoices` | Stripe-billed invoices per case |
| `sp_research_messages` | In-system case messaging with attachments |

### Photos & Videos (2 tables)
| Table | Description |
|-------|-------------|
| `sp_photo_albums` | Nested album folders |
| `sp_photo_album_items` | Image/video items per album |

### Calendar Integration (2 tables)
| Table | Description |
|-------|-------------|
| `sp_ical_feeds` | External iCal subscription feeds |
| `sp_event_reminders` | Sent-reminder dedup ledger |

### System (2 tables)
| Table | Description |
|-------|-------------|
| `sp_audit_log` | Audit trail for member/status/position changes |
| `sp_access_log` | Access-control event log |

---

## Admin Menu Structure

Top-level: **SocietyPress** (dashboard icon)

### Flyout Groups
```
SocietyPress
├── Dashboard
├── Members ─────────────────── Flyout
│   ├── All Members
│   ├── Add New
│   ├── Groups
│   ├── Import Members
│   └── Export Members
├── Events ──────────────────── Flyout
│   ├── All Events
│   ├── Add New
│   ├── Categories
│   ├── Import Events
│   └── Registrations
├── Library ─────────────────── Flyout
│   ├── Catalog
│   ├── Add Item
│   ├── Categories
│   ├── Import
│   ├── Export
│   ├── Stats
│   └── Enrich (Open Library)
├── Newsletters
├── Resource Links ──────────── Flyout
│   ├── All Links
│   ├── Categories
│   └── Import
├── Genealogical Records ────── Flyout
│   ├── Collections
│   └── Import Records
├── Governance ──────────────── Flyout
│   ├── Committees
│   ├── Leadership Positions
│   ├── Volunteer Opportunities
│   ├── Volunteer Roster
│   └── Volunteer Hours
├── Documents ──────────────── Flyout
│   ├── All Documents
│   ├── Categories
│   └── Add New
├── Communications ──────────── Flyout
│   ├── Blast Email
│   ├── Compose
│   ├── Email Templates
│   └── Email Log
├── Finances ────────────────── Flyout
│   ├── Donations
│   ├── Campaigns
│   ├── Store Orders
│   └── Reports
├── Pages
├── Users (Access Control)
├── Help Requests
└── Settings (8 tabs + Modules)
```

---

## AJAX Endpoints (85+)

All registered via `wp_ajax_` (and `wp_ajax_nopriv_` where noted).

### Member / Directory
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_member_detail` | logged-in | Returns member detail modal HTML for directory |
| `sp_save_account` | logged-in | AJAX save for My Account profile sections |
| `sp_surname_contact` | logged-in | Sends contact email to a member researching a surname |
| `sp_add_relationship` | admin | Adds a member-to-member relationship |
| `sp_remove_relationship` | admin | Removes a member-to-member relationship |
| `sp_join_group` | admin | Adds a member to a group |
| `sp_leave_group` | admin | Removes a member from a group |
| `sp_export_count` | admin | Returns count for CSV export with current filters |

### Events
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_register_for_event` | both | Registers user for an event (members + guests) |
| `sp_cancel_registration` | logged-in | Cancels user's event registration |
| `sp_admin_add_walkin` | admin | Records a walk-in attendee |
| `sp_admin_cancel_registration` | admin | Admin cancels a registration |
| `sp_admin_update_attendance` | admin | Updates attendance status for a registrant |
| `sp_admin_bulk_attendance` | admin | Bulk attendance update |
| `sp_export_registrations` | admin | CSV export of event registrations |
| `sp_save_event_category` | admin | Creates/updates an event category |
| `sp_delete_event_category` | admin | Deletes an event category |
| `sp_save_membership_tier` | admin | Creates/updates a membership tier |
| `sp_delete_membership_tier` | admin | Deletes a membership tier |
| `sp_regenerate_occurrences` | admin | Regenerates recurring event occurrences |
| `sp_detach_from_series` | admin | Detaches an event from its recurring series |

### Library
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_library_item_detail` | both | Returns expanded detail HTML for catalog row |
| `sp_export_library` | admin | Streams CSV export of library items |
| `sp_library_enrich_batch` | admin | Processes batch against Open Library API |

### Newsletters
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_generate_newsletter_cover` | admin | Generates cover thumbnail from PDF via Imagick |
| `sp_newsletter_download` | both | Access-controlled PDF download (members for download) |

### Volunteers
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_volunteer_signup` | logged-in | Signs up current member for volunteer opportunity |
| `sp_volunteer_cancel` | logged-in | Cancels volunteer signup |

### Communications
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_blast_recipient_count` | admin | Returns recipient count for blast compose preview |
| `sp_get_default_template` | admin | Returns default email template content for reset |

### Search
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_unified_search` | both | Returns JSON search results across all modules |

### Store / Payments
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_cart_add` | logged-in | Adds item to shopping cart |
| `sp_cart_get` | logged-in | Returns current cart contents and count |
| `sp_cart_update` | logged-in | Updates item quantity in cart |
| `sp_cart_remove` | logged-in | Removes item from cart |
| `sp_store_checkout` | logged-in | Creates Stripe Checkout Session for store purchase |
| `sp_test_stripe_connection` | admin | Tests Stripe API key validity |
| `sp_admin_refund_payment` | admin | Processes a payment refund |

### Documents
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_document_download` | both | Access-controlled document download |

### Admin / System
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_run_update` | admin | One-click plugin update from GitHub |
| `sp_check_update` | admin | Checks for available plugin update |
| `sp_update_parent_theme` | admin | One-click parent theme update |
| `sp_install_theme` | admin | Installs a child theme from the gallery |
| `sp_dismiss_login_ack` | admin | Dismisses login acknowledgment notice |
| `sp_quick_edit_page` | admin | Quick-edits a page from the Pages admin |
| `sp_builder_contact_form` | both | Processes contact form submissions from page builder |

---

## Shortcodes

| Shortcode | Function | Description |
|-----------|----------|-------------|
| `[societypress_join]` | Join form | Public signup with tier selection + Stripe checkout |
| `[societypress_volunteers]` | Volunteer opportunities | Frontend card grid with AJAX signup/cancel |

---

## Cron Jobs (5)

| Hook | Schedule | Description |
|------|----------|-------------|
| `sp_renewal_reminder_cron` | daily | Sends 30/15/7-day renewal reminders, processes expired notices |
| `sp_event_reminder_cron` | daily | Sends reminders for upcoming events (gated by `events` module) |
| `sp_email_log_cleanup_cron` | daily | Purges old email log entries |
| `sp_daily_maintenance` | daily | Prunes audit log entries |
| `sp_blast_email_send_batch` | one-shot | Sends next batch of a blast email, reschedules if more remain |

All crons are unscheduled on plugin deactivation (including `sp_email_log_cleanup_cron`, which was previously orphaned — fixed).

---

## Frontend Page Templates (16)

Registered via `theme_page_templates` filter, intercepted via `template_include`.
Disabled module templates auto-hidden from Edit Page dropdown via `sp_template_module_map()`.

| Template Slug | Module | Description |
|---------------|--------|-------------|
| `sp-builder` | — | Page Builder — renders widget stack |
| `sp-directory` | members | Membership Directory |
| `sp-my-account` | members | Member portal (My Account) |
| `sp-events` | events | Events listing |
| `sp-calendar` | events | Events Calendar (standalone) |
| `sp-library-catalog` | library | Library Catalog (OPAC-style) |
| `sp-newsletter-archive` | newsletters | Newsletter Archive grid |
| `sp-resources` | resources | Resource Links directory |
| `sp-groups` | governance | Interest Groups |
| `sp-store` | store | Public storefront |
| `sp-cart` | store | Shopping cart page |
| `sp-records` | records | Genealogical Records search |
| `sp-documents` | documents | Document archive with category grouping |
| `sp-help-requests` | help_requests | Research Help Requests |
| `sp-search` | — | Unified Search results page |
| `sp-privacy-policy` | — | Dynamic privacy policy based on site config |

---

## Page Builder Widget Types (37)

Each widget type has two functions:
- `sp_builder_fields_{type}($index, $settings)` — admin settings form
- `sp_render_builder_widget_{type}($settings)` — frontend rendering

| Widget Type | Description |
|-------------|-------------|
| `text_block` | Rich text content (wp_editor) |
| `hero_slider` | Image slider with per-line text styling |
| `event_list` | Upcoming events listing |
| `event_calendar` | Monthly calendar grid with category filter |
| `member_directory` | Searchable member directory |
| `library_catalog` | OPAC-style library catalog with tabbed search |
| `contact_form` | Simple contact form |
| `newsletter_archive` | Newsletter grid with PDF viewer |
| `resource_links` | Resource directory with search + category filter |
| `gallery` | Photo gallery grid |
| `records_search` | Genealogical records search interface |
| `donations` | Donation campaign display with progress bars |
| `volunteer_opportunities` | Volunteer opportunity cards |
| `store` | Product grid storefront |
| `custom_html` | Raw HTML block |
| `spacer` | Vertical spacing |
| `divider` | Horizontal rule |
| `heading` | Section heading |
| `image` | Single image |

---

## Encryption

- Algorithm: XChaCha20-Poly1305 via PHP `sodium_*` functions (libsodium)
- Key stored as WordPress option (generated on first use)
- Applied to 7 sensitive member fields: cell, work_phone, alt_phone, fax, address_1, address_2, seasonal_address_1
- Phone (home) and city/state left plaintext for directory search/sort
- Helper functions: `sp_encrypt()`, `sp_decrypt()`, `sp_get_encryption_key()`, `sp_member_encrypt_fields()`, `sp_member_decrypt_row()`, `sp_member_decrypt_rows()`
- Applied at all 9 write points and all 10 read points
- One-time migration `sp_maybe_migrate_encrypt_contacts()` on activation (batched by 100, idempotent)
- Graceful fallback: if decryption fails on plaintext data, value passes through unchanged

---

## Email System

### Logging
- `pre_wp_mail` filter intercepts ALL outgoing emails
- Logs to `sp_email_log`: recipient, subject, body, headers, status, type, timestamp
- Dev mode: blocks actual sending, logs with `blocked` status
- Admin UI: searchable log with stat cards, detail view with sandboxed iframe

### Merge Tags
Unified to `{{double_braces}}` syntax everywhere. Legacy `{single_braces}` fallback retained in blast emails for backward compatibility.
- `sp_replace_merge_tags()`: `{{first_name}}`, `{{last_name}}`, `{{organization_name}}`, `{{membership_tier}}`, `{{expiration_date}}`, etc.
- Blast emails also process `{{double_braces}}` via the same function, with `{single}` fallback

### Email Types
- Welcome email (on member creation)
- Renewal reminders (30/15/7 days, daily cron)
- Expiration notice (post-expiration, daily cron)
- Registration confirmation (on event signup)
- Event reminders (daily cron)
- Blast emails (batch via cron)
- Donation acknowledgments
- Help request notifications

---

## GDPR / Privacy Compliance

### Data Exporters (6)
1. `societypress-members` — Personal data from member record (decrypted for export)
2. `societypress-registrations` — Event registration history
3. `societypress-speakers` — Speaker profile data
4. `societypress-volunteers` — Volunteer roles and hours
5. `societypress-help-requests` — Help request submissions
6. `societypress-donations` — Donation history

### Data Erasers (6)
1. `societypress-members` — Deletes member record
2. `societypress-registrations` — Anonymizes registration records
3. `societypress-speakers` — Anonymizes speaker records
4. `societypress-volunteers` — Deletes volunteer data
5. `societypress-help-requests` — Deletes help request records
6. `societypress-donations` — Anonymizes donor info, retains financial records

---

## Key Patterns

### Frontend Email Obfuscation
All frontend emails rendered via `sp_obfuscate_email()`:
- PHP splits email into base64-encoded halves stored in `data-*` attributes
- JS on page load reassembles and creates clickable `mailto:` links
- Blocks email scrapers/harvesters

### Transient Caching
- Library catalog stats: `sp_library_stats` (1 hour)
- Search page URL: `sp_search_page_url` (cleared on page save)
- Popular subjects: `sp_popular_subjects` (1 hour)

### Site Lockdown
- Frontend: redirects non-logged-in users to login page (except login/cron/AJAX/REST)
- Backend: redirects non-admin users away from `wp-admin` (except profile/AJAX)
- Custom login page with society branding

### Activation
- Creates all ~60 tables via `dbDelta()`
- Seeds default settings (~40 keys)
- Seeds default membership tiers
- Schedules cron jobs
- Sets `societypress_db_version`
- Auto-creates 15 starter content pages (on fresh install only)
- Runs encryption migration for contact fields (idempotent, batched)
- Assigns privacy policy template to Privacy Policy page

### Deactivation
- Unschedules all cron jobs (including `sp_email_log_cleanup_cron` — previously orphaned, now fixed)
- Does NOT drop tables or delete options (data preservation)
