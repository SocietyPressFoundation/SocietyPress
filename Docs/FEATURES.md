# SocietyPress — Feature Inventory

Complete feature list based on full codebase audit, March 8, 2026 — updated March 18, 2026.
Version 0.38d — ~52,500 lines — 43 database tables.

---

## Modules

### 1. Members
**Status: Built** | Tables: 6 | Admin pages: ~8 | AJAX: 4

- Individual and organizational member support
- 5 statuses: Active, Expired, Pending, Cancelled, Deceased
- 14 membership tiers with configurable pricing and duration
- Custom fields for genealogical research (surnames, research areas, relationships)
- Named member groups with bulk assignment
- CSV import with 86+ EasyNetSites column mappings and auto-field-mapping
- CSV export with AJAX live count and filter pass-through
- Bulk actions: status change, group assign, delete
- Searchable member directory (frontend, members-only, configurable privacy layers)
- Member detail modal via AJAX
- Frontend email obfuscation (base64 split + JS reassembly)
- Member portal (My Account):
  - Profile photo upload
  - Personal info, contact, address, seasonal address
  - Communication preferences (including blast email opt-out)
  - Directory privacy settings
  - Interests & skills
  - Research surnames (county/state/country/year range)
  - My registered events
  - Change password

### 2. Events
**Status: Built** | Tables: 6 | Admin pages: ~6 | AJAX: 3

- Full event CRUD with featured image picker
- Event categories (many-to-many)
- Recurring events: weekly, monthly nth-day patterns
- Registration system with capacity limits
- Waitlist support
- Walk-in tracking and attendance management
- Dual pricing display (member + non-member rates)
- Time slots: multiple per event with CRUD
- Speakers: profile, bio, photo per event
- Event CSV import with field mapping
- iCal export
- Monthly calendar grid (shared renderer, usable as page builder widget or standalone template)
- Category filtering on calendar

### 3. Library Catalog
**Status: Built** | Tables: 2 | Admin pages: ~7 | AJAX: 3

- Full catalog management (tested with 19,418 items)
- OPAC-style frontend:
  - Collection stats header with transient caching
  - Tabbed search: keyword, title, author, subject, call number
  - Browse-by-collection type cards with SVG icons
  - Popular subjects tag cloud (top 24)
  - Sortable column headers
  - Expandable detail rows (AJAX)
  - Smart pagination
  - Responsive mobile layout
- Admin: sortable/filterable list table, item editor, categories, stats dashboard
- CSV import with field mapping, CSV export
- 6 media types: Book, Vertical File, Periodicals, Map, eBook, Rare Books
- 6 acquisition codes: Gift, Donation, Purchase, Memorial, Exchange, SAGHS Publication
- Open Library API enrichment: batch LCCN/title+author lookup, cover images, admin progress bar

### 4. Newsletter Archive
**Status: Built** | Tables: 1 | Admin pages: 2 | AJAX: 2

- PDF upload with wp.media picker
- Automatic cover thumbnail generation via Imagick (150 DPI)
- Admin card grid with edit/delete
- Frontend grid with search
- Inline PDF viewer modal (iframe with zoom/page navigation)
- Access-controlled downloads (members only for full PDF)

### 5. Resource Links
**Status: Built** | Tables: 2 | Admin pages: 3

- CSV import supporting EasyNetSites format
- Automatic category creation from imported data
- Frontend directory with search + category dropdown filter
- Integrated with unified site search
- Category CRUD with sort order

### 6. Committees & Leadership
**Status: Built (backend complete, admin UI needs dedicated menu)** | Tables: 4

- Committee definitions with descriptions and status
- Committee member assignments with roles (member, chair, co-chair)
- Chairperson frontend management capabilities
- Named leadership positions (President, VP, Secretary, etc.)
- Term tracking with date ranges
- Co-chair support

### 7. Volunteer System
**Status: Built** | Tables: 4 | Admin pages: 4 | AJAX: 2 | Shortcodes: 1

- Volunteer opportunities: title, description, location, type (one-time/recurring/ongoing), date, capacity, skills needed, status, committee association
- Frontend card grid with AJAX signup/cancel (`[societypress_volunteers]`)
- Waitlist with automatic promotion when spots open
- Signup lifecycle: Confirmed → Waitlist → Completed → Cancelled → No-Show
- Admin volunteer roster with inline add/edit
- Hours tracking: logged per signup, summary stats, CSV export

### 8. Donations & Campaigns
**Status: Built** | Tables: 2 | Admin pages: 4

- Individual donation tracking: amount, type (cash/check/online/in-kind), date, donor, anonymous flag
- Campaign management: name, description, goal, date range, status
- Progress bars (raised vs. goal) in admin and frontend
- Bulk acknowledgment emails with merge tags
- In-kind donation support with description
- Reports integration

### 9. Blast Email
**Status: Built** | Tables: 2 | Admin pages: 2 | AJAX: 1

- Compose with wp_editor() rich text
- Recipient targeting: all members, by group, by tier
- Batch sending via WP cron (configurable batch size)
- Merge tags in body (`{first_name}`, `{last_name}`, etc.)
- Per-recipient delivery tracking (sent/failed)
- Opt-out support (respected via communication preferences)
- Admin list with status filters and delivery stats
- Read-only detail view for sent blasts

### 10. Genealogical Records
**Status: Built (needs data)** | Tables: 4 | Admin pages: 5 | Page builder widget: 1

- EAV-based architecture (Entity-Attribute-Value) for flexible record schemas
- 13 record type templates with default field definitions:
  Cemetery, Census, Church, Court, Immigration, Land, Marriage, Military, Newspaper, Obituary, Probate, Tax, Vital
- Admin collection manager with drag-reorder field configurator
- Record browser/editor within each collection
- CSV import with column-to-field mapping
- Concatenated `search_text` column for fast full-text search without EAV joins
- Frontend search: collection filter, text search, responsive table, expandable detail rows (client-side from pre-loaded data)
- Per-field `is_public` access control (non-members see restricted view)
- Page builder widget: `records_search`

### 11. Store
**Status: Built** | Tables: 2 (sp_store_orders, sp_store_order_items) | Admin pages: 2 | AJAX: 6

- Public storefront with category sidebar and product card grid
- Products sourced from library catalog (configurable `store_acq_code` setting, `item_value > 0`)
- 8 auto-categorized store categories
- Quantity selector per product
- Shopping cart: user_meta storage, AJAX CRUD, responsive cart page with quantity controls, header badge with live count
- Checkout: Stripe Checkout Sessions with multi-line-item support, order confirmation email
- Admin order management: orders list with status filters (all/paid/pending/shipped/completed/refunded), order detail with fulfillment controls
- Integrated into Finances flyout menu

### 12. Documents
**Status: Built** | Tables: 2 (sp_documents, sp_document_categories) | Admin pages: 3 | AJAX: 1

- Document management with admin CRUD (list/add/edit/delete)
- Category management with seeded defaults (Meeting Minutes, Society Documents)
- WP media library file picker for uploads
- Bulk upload: multi-select media picker, shared category/access/status settings, auto-generated titles from filenames, auto-detected dates from filename patterns, per-file review before submit
- Per-document access control (public/members_only)
- Frontend page template (sp-documents) with category grouping and lock icons for restricted docs
- Access-controlled download via AJAX handler (raw upload URLs never exposed)

### 13. Page Builder
**Status: Built** | 19 widget types

- Admin meta box on page editor with widget stack
- Drag-reorder widget ordering
- Per-widget settings via modal/inline form
- Frontend rendering engine
- Widget types: text_block, hero_slider, event_list, event_calendar, member_directory, library_catalog, contact_form, newsletter_archive, resource_links, gallery, records_search, donations, volunteer_opportunities, store, custom_html, spacer, divider, heading, image

### 14. Design System
**Status: Built**

- CSS custom properties throughout plugin and theme
- 7 color pickers: primary, secondary, accent, background, text, heading, border
- Font controls: family, size, weight, line-height
- Content width and sidebar width
- Custom CSS field
- Live preview in admin
- Theme reads design system values with sensible fallback defaults
- 6 named style presets (Parchment, Slate, Ledger, Hearth, Archive, Chronicle) — clickable cards fill all 7 colors + 2 fonts, instant preview, highlighted selection state

### 15. Email System
**Status: Built (transactional + blast + template editor)**

- `pre_wp_mail` interceptor logs ALL outgoing emails
- Dev mode: blocks actual sending, logs with `blocked` status
- Configurable From name, From email, Reply-To
- Email log admin with stat cards, filters, search, detail view
- Tabbed email template editor (Welcome, Renewal Reminder, Expiration Notice) with wp_editor, merge tag reference sidebar with click-to-copy, reset-to-default
- Unified merge tag syntax: `{{double_braces}}` everywhere (legacy `{single_braces}` fallback in blast emails)
- Email types:
  - Welcome (on member creation)
  - Renewal reminders (30/15/7 days, daily cron with dedup)
  - Expiration notice (post-expiration, daily cron)
  - Registration confirmation (confirmed + waitlisted)
  - Waitlist promotion (on cancellation promoting waitlisted registrant)
  - Event reminders (daily cron)
  - Event change/cancellation notifications
  - Donation acknowledgments
  - Order confirmation (store checkout)
  - Blast emails (batch via cron)
  - Help request notifications

### 16. Join Form
**Status: Built (Stripe only)** | Shortcodes: 1

- Public signup via `[societypress_join]`
- Tier selection with pricing display (including joint membership support)
- Personal information collection
- Stripe Checkout Sessions (direct REST API, no SDK)
- Member record created as Pending, activated on admin approval
- Rate limiting: 3 attempts per IP per hour

### 17. Reports
**Status: Built**

- Reports dashboard with summary statistics
- Annual report generation
- Donation reports with campaign breakdown
- Library stats dashboard (totals, by media type, by acq code, recent additions, collection value)

---

## Cross-Cutting Features

### Security
- XChaCha20-Poly1305 encryption via libsodium
- Nonce verification on all admin forms and AJAX endpoints
- Capability checks on admin pages (`manage_options` or delegated)
- Frontend email obfuscation
- Site lockdown (login required)
- Custom login page

### GDPR / Privacy
- 6 data exporters + 6 data erasers registered with WordPress Privacy framework (members, registrations, speakers, volunteers, help requests, donations)
- Privacy policy content auto-registration
- Dynamic privacy policy page template (`sp-privacy-policy`) that adapts content based on enabled modules and integrations

### Search
- Unified search across 6 modules: events, library, resources, members, newsletters, WP pages — cross-module scope
- Frontend template (`sp-search`) + AJAX JSON endpoint
- Per-module result sections with appropriate access controls

### Feature Toggles
- 12-module toggle system: Events, Library, Newsletters, Resources, Governance, Store, Records, Donations, Blast Email, Gallery, Research Help, Documents
- Setup wizard step for initial selection + Settings → Modules page for ongoing changes
- Disabled modules: admin menus hidden, page templates removed from dropdown, frontend pages show "Feature Not Available"
- Tables stay intact when disabled — toggling off is never destructive
- Members module is always enabled

### Starter Content
- Auto-creates 15 pages on fresh activation (Home, About, Membership, Events, Calendar, Directory, My Account, Join, Newsletters, Library, Search, Contact, Resources, Leadership, News + Privacy Policy)
- Removes default WP post/page/comment, sets static front page + blog page
- Creates Primary Menu with all key pages assigned
- Smart nav behavior: hides Join for logged-in, Directory/My Account for logged-out

### Roles & Permissions
- 10 access areas: Members, Events, Library, Finances, Communications, Records, Governance, Content, Settings, Reports
- 8 pre-built role templates: Webmaster, Membership Manager, Treasurer, Event Coordinator, Librarian, Communications Director, Records Manager, Content Editor
- `sp_user_can()` helper + `user_has_cap` filter — WP admins auto-get all SP capabilities
- User Access admin page: assign roles, customize per-user access, revoke access

### Update System
- GitHub update checker for plugin: `pre_set_site_transient_update_plugins`, one-click AJAX update from dashboard
- Parent theme update checker: `pre_set_site_transient_update_themes`, one-click update
- Child theme gallery: built-in catalog, install/update from GitHub, version comparison
- Dashboard update banners: plugin (blue), theme (amber), auto-hidden when current

### Admin
- Unified sidebar with flyout submenus
- WordPress branding completely hidden
- Custom login page with society branding
- Dashboard with stat cards, upcoming events, expiring members, recent signups, activity feed (15 recent audit log entries)
- Setup wizard (4 steps): Org Info → Membership → Feature Selection → Appearance/Email

### Internationalization
- Text domain: `societypress`
- Comprehensive i18n pass completed: ~350+ strings wrapped across all admin/frontend surfaces
- All 8 settings tabs, admin notices, edit forms, page builder fields, design settings, setup wizard, import notices — all wrapped
- Remaining: ~200 edge-case strings in a 52K-line file

---

## Module Summary

| Module | Status | Tables | Admin Pages | AJAX | Shortcodes | Builder Widget |
|--------|--------|--------|-------------|------|------------|----------------|
| Members | Built | 6 | ~8 | 4 | — | member_directory |
| Events | Built | 6 | ~6 | 10 | — | event_list, event_calendar |
| Library | Built | 2 | ~7 | 3 | — | library_catalog |
| Newsletters | Built | 1 | 2 | 2 | — | newsletter_archive |
| Resources | Built | 2 | 3 | — | — | resource_links |
| Committees | Built* | 4 | ~3 | — | — | — |
| Volunteers | Built | 4 | 4 | 2 | 1 | volunteer_opportunities |
| Donations | Built | 2 | 4 | — | — | donations |
| Blast Email | Built | 2 | 3 | 1 | — | — |
| Records | Built† | 4 | 5 | — | — | records_search |
| Store | Built | 2 | 2 | 6 | — | store |
| Documents | Built | 2 | 3 | 1 | — | — |
| Page Builder | Built | 0 | — | — | — | (19 types) |
| Design System | Built | 0 | 1 | — | — | — |
| Email System | Built | 1 | 2 | 1 | — | — |
| Join Form | Built | 0 | 0 | — | 1 | — |
| Reports | Built | 0 | 1 | — | — | — |
| Search | Built | 0 | 0 | 1 | — | — |
| GDPR | Built | 0 | 0 | — | — | — |
| Roles & Perms | Built | 0 | 1 | — | — | — |
| Update System | Built | 0 | 0 | 3 | — | — |

\* Backend complete, needs dedicated admin menu entry
† Needs real data imported

### Planned Modules
- **Voting & Elections** — ballot/election system for officer elections, secret ballot, configurable voting window
- **PWA** — Progressive Web App layer (manifest, service worker, offline caching, push notifications)
