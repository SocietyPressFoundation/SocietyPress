# SocietyPress — Feature Inventory

Complete feature list based on full codebase audit, March 8, 2026.
Version 0.30d — 43,745 lines — 39 database tables.

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
- 6 acquisition codes: Gift, Donation, Purchase, Memorial, Exchange, Society Publication
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
**Status: Frontend built, needs payments** | Tables: 0 (uses library_items) | Admin pages: 0

- Public storefront with category sidebar and product card grid
- Products sourced from library catalog (`acq_code = 'Society Publication'`, `item_value > 0`)
- 8 auto-categorized store categories
- Quantity selector per product
- Placeholder "Add to Cart" buttons (no checkout flow yet)

### 12. Page Builder
**Status: Built** | 19 widget types

- Admin meta box on page editor with widget stack
- Drag-reorder widget ordering
- Per-widget settings via modal/inline form
- Frontend rendering engine
- Widget types: text_block, hero_slider, event_list, event_calendar, member_directory, library_catalog, contact_form, newsletter_archive, resource_links, gallery, records_search, donations, volunteer_opportunities, store, custom_html, spacer, divider, heading, image

### 13. Design System
**Status: Built**

- CSS custom properties throughout plugin and theme
- 7 color pickers: primary, secondary, accent, background, text, heading, border
- Font controls: family, size, weight, line-height
- Content width and sidebar width
- Custom CSS field
- Live preview in admin
- Theme reads design system values with sensible fallback defaults

### 14. Email System
**Status: Built (transactional + blast)**

- `pre_wp_mail` interceptor logs ALL outgoing emails
- Dev mode: blocks actual sending, logs with `blocked` status
- Configurable From name, From email, Reply-To
- Email log admin with stat cards, filters, search, detail view
- Email types:
  - Welcome (on member creation)
  - Renewal reminders (30/15/7 days, daily cron with dedup)
  - Registration confirmation (confirmed + waitlisted)
  - Event reminders (daily cron)
  - Donation acknowledgments
  - Blast emails (batch via cron)
  - Help request notifications

### 15. Join Form
**Status: Built (Stripe only)** | Shortcodes: 1

- Public signup via `[societypress_join]`
- Tier selection with pricing display
- Personal information collection
- Stripe PaymentIntent checkout (direct REST API, no SDK)
- Member record creation on submission

### 16. Reports
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
- 5 data exporters + 5 data erasers registered with WordPress Privacy framework
- Privacy policy content auto-registration
- Gap: donations not covered (see Known Issues)

### Search
- Unified search across 6 modules: events, library, resources, members, newsletters, WP pages
- Frontend template (`sp-search`) + AJAX JSON endpoint
- Per-module result sections with appropriate access controls

### Admin
- Unified sidebar with flyout submenus
- WordPress branding completely hidden
- Custom login page with society branding
- Dashboard with stat cards, upcoming events, expiring members, recent signups
- Setup wizard (3 steps): Org Info → Membership → Appearance/Email

### Internationalization
- Text domain: `societypress`
- My Account page fully wrapped in i18n functions
- Remainder of plugin needs retroactive i18n pass

---

## Module Summary

| Module | Status | Tables | Admin Pages | AJAX | Shortcodes | Builder Widget |
|--------|--------|--------|-------------|------|------------|----------------|
| Members | Built | 6 | ~8 | 4 | — | member_directory |
| Events | Built | 6 | ~6 | 3 | — | event_list, event_calendar |
| Library | Built | 2 | ~7 | 3 | — | library_catalog |
| Newsletters | Built | 1 | 2 | 2 | — | newsletter_archive |
| Resources | Built | 2 | 3 | — | — | resource_links |
| Committees | Built* | 4 | ~3 | — | — | — |
| Volunteers | Built | 4 | 4 | 2 | 1 | volunteer_opportunities |
| Donations | Built | 2 | 4 | — | — | donations |
| Blast Email | Built | 2 | 2 | 1 | — | — |
| Records | Built† | 4 | 5 | — | — | records_search |
| Store | Partial‡ | 0 | 0 | — | — | store |
| Page Builder | Built | 0 | — | — | — | (19 types) |
| Design System | Built | 0 | 1 | — | — | — |
| Email System | Built | 1 | 1 | — | — | — |
| Join Form | Built | 0 | 0 | 2 | 1 | — |
| Reports | Built | 0 | 1 | — | — | — |
| Search | Built | 0 | 0 | 1 | — | — |
| GDPR | Built | 0 | 0 | — | — | — |

\* Backend complete, needs dedicated admin menu entry
† Needs real data imported
‡ Frontend only, no cart/checkout/payments
