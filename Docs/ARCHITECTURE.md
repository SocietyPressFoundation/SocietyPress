# SocietyPress — Architecture Reference

Last updated: March 8, 2026 (from full codebase audit)
Plugin version: 0.30d | 43,745 lines | Single-file, function-based

---

## File Structure

```
SocietyPress/
├── plugin/
│   └── societypress.php          # The entire plugin (43,745 lines)
├── theme/
│   └── societypress/             # Parent theme (CSS vars, fallback defaults)
├── theme-saghs/
│   └── saghs/                    # Child theme for kndgs.org (SAGHS-specific styling)
├── Docs/
│   ├── ARCHITECTURE.md           # This file
│   ├── FEATURES.md               # Complete feature inventory
│   ├── KNOWN-ISSUES.md           # Bugs and technical debt
│   └── PROJECT-PROMPT.md         # Recreation prompt for new Claude sessions
├── TO-DO.md                      # Task tracking
├── WORKLOG.md                    # Version history and session notes
├── README.md                     # Project overview
├── LICENSE                       # GPL-2.0-or-later
└── .gitignore
```

---

## Constants

```php
SOCIETYPRESS_VERSION     // '0.30d'
SOCIETYPRESS_PLUGIN_DIR  // plugin_dir_path(__FILE__)
SOCIETYPRESS_PLUGIN_URL  // plugin_dir_url(__FILE__)
SOCIETYPRESS_PLUGIN_FILE // __FILE__
```

---

## Settings

Single option: `get_option('societypress_settings', [])`

68 keys across 7 tabs:
1. **Website** — site_name, tagline, logo, favicon, lockdown toggle, login page
2. **Organization** — org_name, org_address, org_phone, org_email, org_type, founded_year
3. **Membership** — default_tier, auto_expire, grace_period, require_approval, genealogy service toggles (8)
4. **Directory** — visibility, searchable fields, privacy defaults, show_surnames
5. **Events** — default_capacity, allow_waitlist, reminder_days, calendar options
6. **Privacy** — data_retention, cookie_notice, GDPR toggles
7. **Design** — 7 color pickers, font_family, font_size, heading_font, content_width, sidebar_width, custom_css

Settings sanitized via `sp_sanitize_settings()` callback registered with `register_setting()`.

Additional standalone options:
- `societypress_db_version` — tracks schema version for migrations
- `sp_wizard_completed` — marks setup wizard as done
- `sp_enabled_modules` — (planned) array of enabled module slugs

---

## Database Tables (39)

All prefixed with `{$wpdb->prefix}sp_`.

### Members (6 tables)
| Table | Description | Row Count (kndgs.org) |
|-------|-------------|----------------------|
| `sp_members` | Core member records (individual + org) | 386 |
| `sp_member_groups` | Named groups for organizing members | ~5 |
| `sp_member_group_assignments` | Many-to-many: members ↔ groups | varies |
| `sp_member_surnames` | Surnames being researched (per member) | varies |
| `sp_member_research_areas` | Geographic research areas with time periods | varies |
| `sp_member_relationships` | Member-to-member relationships (spouse, family, referral) | 0 |

### Membership (1 table)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_membership_tiers` | Tier definitions with pricing and duration | 14 |

### Events (6 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_events` | Event records | ~20 |
| `sp_event_categories` | Event category taxonomy | ~5 |
| `sp_event_category_assignments` | Many-to-many: events ↔ categories | varies |
| `sp_event_registrations` | Registration records with status + attendance | varies |
| `sp_event_speakers` | Speaker profiles per event | varies |
| `sp_event_time_slots` | Multiple time slots per event | varies |

### Library (2 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_library_items` | Catalog items (books, maps, periodicals, etc.) | 19,418 |
| `sp_library_categories` | Library categories (unused — real taxonomy is media_type/subject fields) | 7 (0 items assigned) |

### Committees & Leadership (4 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_committees` | Committee definitions | varies |
| `sp_committee_members` | Many-to-many: members ↔ committees with roles | varies |
| `sp_leadership_positions` | Named positions (President, VP, etc.) | varies |
| `sp_leadership_terms` | Term records linking members to positions with date ranges | varies |

### Volunteers (4 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_volunteer_opportunities` | Posted volunteer opportunities | varies |
| `sp_volunteer_signups` | Member signups for opportunities (with status) | varies |
| `sp_volunteer_roles` | Standing volunteer role assignments | varies |
| `sp_volunteer_hours` | Logged volunteer hours per member | varies |

### Communications (4 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_email_log` | All outgoing emails logged via `pre_wp_mail` | varies |
| `sp_blast_emails` | Blast email campaigns | varies |
| `sp_blast_email_recipients` | Per-recipient delivery tracking for blasts | varies |
| `sp_renewal_reminders` | Dedup table for renewal reminder emails | varies |

### Finances (2 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_donations` | Individual donation records | varies |
| `sp_campaigns` | Fundraising campaign definitions | varies |

### Content (4 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_newsletters` | Newsletter archive (PDF + cover metadata) | 13 |
| `sp_resources` | External resource links | 157 |
| `sp_resource_categories` | Resource link categories | ~15 |
| `sp_pages` | Plugin-managed pages (internal tracking) | varies |

### Genealogical Records (4 tables, EAV architecture)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_record_collections` | Collection definitions (Cemetery, Census, etc.) | 0 |
| `sp_record_collection_fields` | Field definitions per collection (drag-reorderable) | 0 |
| `sp_records` | Individual record entries with `search_text` column | 0 |
| `sp_record_values` | EAV value storage: record_id + field_id + value | 0 |

### System (2 tables)
| Table | Description | Row Count |
|-------|-------------|-----------|
| `sp_audit_log` | Audit trail for member/status/position changes | varies |
| `sp_help_requests` | Member help request submissions | varies |

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
├── Communications ──────────── Flyout
│   ├── Blast Email
│   ├── Compose
│   └── Email Log
├── Finances ────────────────── Flyout
│   ├── Donations
│   ├── Campaigns
│   └── Reports
├── Pages
├── Users
├── Help Requests
└── Settings
```

---

## AJAX Endpoints

All registered via `wp_ajax_` (and `wp_ajax_nopriv_` where noted).

### Member / Directory
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_member_detail` | logged-in | Returns member detail modal HTML for directory |
| `sp_save_profile_field` | logged-in | Saves individual profile field from My Account |
| `sp_upload_profile_photo` | logged-in | Handles profile photo upload |
| `sp_surname_contact` | logged-in | Sends contact email to a member researching a surname |

### Events
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_event_register` | logged-in | Registers current user for an event |
| `sp_event_cancel_registration` | logged-in | Cancels user's event registration |
| `sp_event_waitlist_promote` | admin | Promotes a waitlisted registrant to confirmed |

### Library
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_library_item_detail` | logged-in* | Returns expanded detail HTML for catalog row |
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

### Search
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_unified_search` | both | Returns JSON search results across all modules |

### Store / Payments
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_stripe_create_intent` | public | Creates Stripe PaymentIntent for join form |
| `sp_stripe_confirm_payment` | public | Confirms payment and activates member |

### Help
| Endpoint | Auth | Description |
|----------|------|-------------|
| `sp_help_request_submit` | logged-in | Submits a help request from frontend |

*`sp_library_item_detail` is missing `nopriv` — see Known Issues #6

---

## Shortcodes

| Shortcode | Function | Description |
|-----------|----------|-------------|
| `[societypress_join]` | Join form | Public signup with tier selection + Stripe checkout |
| `[societypress_volunteers]` | Volunteer opportunities | Frontend card grid with AJAX signup/cancel |

---

## Cron Jobs

| Hook | Schedule | Description |
|------|----------|-------------|
| `sp_renewal_reminder_cron` | daily | Sends 30/15/7-day renewal reminders, processes expired notices |
| `sp_event_reminder_cron` | daily | Sends reminders for upcoming events |
| `sp_email_log_cleanup` | daily | Purges old email log entries |
| `sp_blast_email_send_batch` | one-shot | Sends next batch of a blast email, reschedules if more remain |

---

## Frontend Page Templates

Registered via `theme_page_templates` filter, intercepted via `template_include`.

| Template Slug | Description |
|---------------|-------------|
| `sp-builder` | Page Builder — renders widget stack |
| `sp-members` | Member Directory (legacy, may overlap with builder widget) |
| `sp-library` | Library Catalog (legacy, may overlap with builder widget) |
| `sp-calendar` | Events Calendar (standalone) |
| `sp-search` | Unified Search results page |
| `sp-newsletter-archive` | Newsletter Archive grid |
| `sp-my-account` | Member portal (My Account) |
| `sp-join` | Join/signup form (may use shortcode instead) |
| `sp-store` | Public storefront |
| `sp-records` | Genealogical Records search (may use builder widget) |

---

## Page Builder Widget Types (19)

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

- Algorithm: XChaCha20-Poly1305 via PHP `sodium_*` functions
- Key stored as WordPress option (generated on first use)
- Currently used for: sensitive member contact fields (when encryption is enabled)
- Functions: `sp_encrypt()`, `sp_decrypt()`, `sp_get_encryption_key()`

---

## Email System

### Logging
- `pre_wp_mail` filter intercepts ALL outgoing emails
- Logs to `sp_email_log`: recipient, subject, body, headers, status, type, timestamp
- Dev mode: blocks actual sending, logs with `blocked` status
- Admin UI: searchable log with stat cards, detail view with sandboxed iframe

### Merge Tags
Two separate systems (should be unified — see Known Issues #4):
- **Transactional emails** (`sp_replace_merge_tags`): `{{first_name}}`, `{{last_name}}`, `{{organization_name}}`, `{{membership_tier}}`, `{{expiration_date}}`, etc.
- **Blast emails** (`sp_blast_process_merge_tags`): `{first_name}`, `{last_name}`, `{organization_name}`, etc.

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

### Data Exporters (5)
1. `societypress-members` — Personal data from member record
2. `societypress-registrations` — Event registration history
3. `societypress-speakers` — Speaker profile data
4. `societypress-volunteers` — Volunteer roles and hours
5. `societypress-help-requests` — Help request submissions

### Data Erasers (5)
1. `societypress-members` — Deletes member record
2. `societypress-registrations` — Anonymizes registration records
3. `societypress-speakers` — Anonymizes speaker records
4. `societypress-volunteers` — Deletes volunteer data
5. `societypress-help-requests` — Deletes help request records

**Gap:** Donations (`sp_donations`) not covered. See Known Issues #5.

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
- Creates all 39 tables via `dbDelta()`
- Seeds default settings (~40 keys)
- Seeds default membership tiers
- Schedules cron jobs
- Sets `societypress_db_version`

### Deactivation
- Unschedules cron jobs (except `sp_email_log_cleanup` — see Known Issues #21)
- Does NOT drop tables or delete options (data preservation)
