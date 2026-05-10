# Developer Reference

For technically-inclined webmasters who want to extend SocietyPress beyond what the admin lets you do — add a custom page-builder widget, hook the society sidebar to add an entry, build a child theme that talks to plugin internals, or just understand the surface area.

If you're not a developer, skip this. The other [module guides](README.md) cover everything you need to run the platform.

## Architecture in one paragraph

SocietyPress is a single-file plugin (`societypress.php`, ~85,000 lines) plus a parent theme (`Code/theme/`) plus five child themes. Function-based PHP — there are a couple of unavoidable classes (the nav walker, the WP_List_Table subclasses, the Walker_Nav_Menu requirement) but the rest is functions. ~65 custom database tables prefixed `sp_`. Inline JS — no jQuery dependency except where wpColorPicker forces it. Inline CSS in admin pages, separate CSS file for the parent theme.

## Shortcodes

Every public-facing module surfaces a shortcode. Page-builder widgets are wrappers around these, so you can use either.

| Shortcode | What it does |
|---|---|
| `[societypress_join]` | Join form / new-member signup |
| `[societypress_volunteers]` | Volunteer-opportunity board |
| `[sp_donate]` | Public donation form (Stripe + PayPal) |
| `[sp_database_subscriptions]` | Members-area gateway to paid databases |
| `[sp_help_request_submit]` | Public Help Request submission form |
| `[sp_help_requests_archive]` | Public archive of help requests |
| `[sp_lineage_apply]` | Lineage-program application form |
| `[sp_lineage_my_applications]` | Logged-in member's lineage applications |
| `[sp_lineage_roster]` | Public roster of approved lineage members |
| `[sp_my_research_assignments]` | Researcher dashboard (claimed cases + open queue) |
| `[sp_my_research_cases]` | Member's submitted research cases |
| `[sp_my_volunteer_hours]` | Member's volunteer-hours summary |
| `[sp_picture_wall]` | Public Picture Wall (member-submitted ancestor portraits) |
| `[sp_picture_wall_submit]` | Picture Wall submission form |
| `[sp_research_request]` | Paid Research Services intake |
| `[sp_society_sidebar]` | Auto-assembled member-portal nav |

Every shortcode accepts attributes documented in its module guide.

## Page-builder widgets

Same surface, dragged via the page builder. Attributes on a widget become attributes on the corresponding shortcode.

Widget types are registered through the `sp_builder_widget_types` filter (see Hooks below). To add your own widget in a child theme, hook the filter and register a render function.

## Filters

SocietyPress exposes three filters that child themes and add-ons can hook.

### `sp_society_sidebar_items` — array

Filters the auto-assembled member-portal sidebar items. Each item is an associative array with `label`, `url`, `module`, `icon`, `login_required`. Hook to add, remove, rename, or re-order entries.

```php
add_filter( 'sp_society_sidebar_items', function ( $items ) {
    // Add a custom entry
    $items[] = [
        'label'          => __( 'Annual Banquet', 'my-child-theme' ),
        'url'            => home_url( '/banquet/' ),
        'module'         => null,
        'icon'           => 'dashicons-cake',
        'login_required' => false,
    ];

    // Remove the Store entry
    $items = array_filter( $items, fn( $i ) => $i['url'] !== home_url( '/store/' ) );

    return array_values( $items );
} );
```

### `sp_admin_capability_map` — array

Maps admin menu slugs to required capabilities. Hook to grant a custom capability access to a SocietyPress admin page.

### `sp_insights_panels` — array

Filters the registry of stat panels on the SocietyPress → Insights page. Hook to add a custom card (a metric your child theme tracks, an aggregate from a partner system, anything). Each entry has `name`, `icon` (Dashicon class), and `callback` — a function name receiving the resolved window array and returning `[label, value, value_kind, sparkline]`. Set `always_on => true` to bypass the per-module enabled gate.

```php
add_filter( 'sp_insights_panels', function ( array $panels ) {
    $panels['banquet_rsvps'] = [
        'name'      => 'Banquet RSVPs',
        'icon'      => 'dashicons-cake',
        'callback'  => 'my_child_theme_banquet_stats',
        'always_on' => true,
    ];
    return $panels;
} );

function my_child_theme_banquet_stats( array $window ): array {
    global $wpdb;
    $total = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM my_banquet_rsvps WHERE created_at BETWEEN %s AND %s",
        $window['start'] . ' 00:00:00',
        $window['end']   . ' 23:59:59'
    ) );
    return [
        'label'      => 'RSVPs received',
        'value'      => $total,
        'value_kind' => 'count',           // 'count' | 'currency' | 'hours'
        'sparkline'  => [],                 // bucket counts; empty hides the trendline
    ];
}
```

The window array provides `start` (Y-m-d), `end` (Y-m-d), `buckets` (int), `unit` (`days`/`week`/`month`), and `label`. The `sp_insights_bucket_edges()` helper converts a window into per-bucket [start_ts, end_ts] tuples for sparkline queries.

### `sp_builder_widget_types` — array

Register page-builder widgets. Each entry has `label`, `description`, and a `fields` array describing its settings.

```php
add_filter( 'sp_builder_widget_types', function ( array $types ) {
    $types['my_custom_widget'] = [
        'label'       => 'My Custom Widget',
        'description' => 'Does my custom thing.',
        'fields'      => [
            'show_header' => [
                'label'   => 'Show header',
                'type'    => 'checkbox',
                'default' => true,
            ],
        ],
    ];
    return $types;
} );

function sp_render_builder_widget_my_custom_widget( array $s ): void {
    if ( ! empty( $s['show_header'] ) ) echo '<h2>Hello!</h2>';
    // ... your render ...
}
```

The render function name follows the convention `sp_render_builder_widget_{type}` — must be globally available.

## REST endpoints

| Endpoint | Method | What |
|---|---|---|
| `/wp-json/societypress/v1/health` | GET | Returns plugin version, db schema version, enabled modules. Useful for monitoring. |
| `/wp-json/societypress/v1/webhooks/stripe` | POST | Stripe webhook receiver. Signature-verified via the stripe_webhook_secret setting. Handles `checkout.session.completed`, `invoice.paid`, `customer.subscription.deleted`. |

## AJAX endpoints

80+ AJAX endpoints under `wp_ajax_sp_*`. Most are admin-only; some have `wp_ajax_nopriv_*` counterparts for public flows. Patterns:

- **Cart actions:** `sp_cart_add`, `sp_cart_get`, `sp_cart_update`, `sp_cart_remove`
- **Donations:** `sp_donate_paypal_create_order`, `sp_donate_paypal_capture`
- **Help Requests:** `sp_help_endorse`, `sp_help_mark_resolved`
- **Member admin:** `sp_member_detail`, `sp_save_account`, `sp_delete_members_batch`, `sp_import_members_batch`
- **Events:** `sp_register_for_event`, `sp_admin_update_attendance`, `sp_admin_bulk_attendance`, `sp_admin_cancel_registration`
- **Library:** `sp_library_item_detail`, `sp_library_enrich_batch`
- **Voting:** `sp_submit_vote`, `sp_export_ballot_results`
- **Surname Research:** `sp_surname_contact`
- **Search:** `sp_unified_search` (cross-module search)
- **Exports:** `sp_export_*` family (members, donations, library, events, etc.)
- **Stripe / PayPal:** `sp_store_create_payment_intent`, `sp_store_create_paypal_order`, `sp_store_capture_paypal_order`, `sp_store_finalize_stripe`, `sp_admin_refund_payment`, `sp_admin_refund_store_order`

Each endpoint expects a nonce. Look at the corresponding shortcode / admin page render to see the nonce action name.

## Module-enabled API

Every module is checked via `sp_module_enabled( 'slug' )`:

```php
if ( sp_module_enabled( 'donations' ) ) {
    // Donations module is active
}
```

Module slugs: `events`, `library`, `newsletters`, `resources`, `governance`, `store`, `donations`, `blast_email`, `gallery`, `records`, `help_requests`, `documents`, `voting`, `lineage`, `research_services`. Always-on: `members` (no flag — always returns true).

## Custom tables

`{$wpdb->prefix}sp_*` — about 60 tables. Highlights:

- **Members:** `sp_members`, `sp_member_meta`, `sp_member_notes`, `sp_membership_tiers`, `sp_member_payments`, `sp_member_relationships`, `sp_member_research_areas`, `sp_member_surnames`, `sp_pending_profile_changes`, `sp_renewal_reminders`
- **Events:** `sp_events`, `sp_event_categories`, `sp_event_registrations`, `sp_event_reminders`, `sp_event_slots`, `sp_event_speakers`, `sp_event_speaker_assignments`, `sp_ical_feeds`
- **Library:** `sp_library_items`, `sp_library_categories`
- **Records:** `sp_records`, `sp_record_collections`, `sp_record_collection_fields`, `sp_record_values`
- **Donations:** `sp_donations`, `sp_campaigns`
- **Store:** `sp_orders`, `sp_order_items`, `sp_store_products`
- **Voting:** `sp_ballots`, `sp_ballot_questions`, `sp_ballot_choices`, `sp_ballot_votes`
- **Governance:** `sp_committees`, `sp_meetings`, `sp_volunteer_opportunities`, `sp_volunteer_signups`, `sp_volunteer_roles`, `sp_volunteer_hours`, `sp_groups`, `sp_group_members`
- **Help / Research:** `sp_help_requests`, `sp_help_responses`, `sp_research_cases`, `sp_research_invoices`, `sp_research_messages`
- **Lineage:** `sp_lineage_programs`, `sp_lineage_applications`, `sp_lineage_proofs`
- **Other:** `sp_documents`, `sp_document_categories`, `sp_newsletters`, `sp_resources`, `sp_resource_categories`, `sp_blast_emails`, `sp_email_log`, `sp_photo_albums`, `sp_photo_album_items`, `sp_audit_log`, `sp_access_log`, `sp_backups`

Use `$wpdb->prefix . 'sp_<table>'` to reference. Always use `$wpdb->prepare()` for any user-input interpolation.

## Capabilities

Custom capabilities map to admin sections:

- `sp_manage_settings` — Settings, Modules, User Access
- `sp_manage_members` — Members, Member Levels, Imports
- `sp_manage_content` — Events, Library, Newsletters, Documents, Help Requests, Lineage, Research Cases, Picture Wall
- `sp_manage_records` — Records collections + import
- `sp_manage_finances` — Donations, Campaigns, Orders, Store Products, Finances
- `sp_manage_governance` — Committees, Meetings, Volunteer Roster, Volunteer Opportunities
- `sp_manage_voting` — Ballots, Voting Results
- `sp_view_reports` — Reports, Annual Report, Audit Log

Roles are configured at SocietyPress → User Access. Admin (manage_options) inherits all sp_* capabilities automatically.

## Volunteer-hours source-linking

Every helping action writes to `sp_volunteer_hours` with two attribution columns:

- `source_type` — one of: `help_request`, `research_case`, `committee`, `event`, `meeting`, `library_duty`, `other`
- `source_id` — the FK to whichever table the source row lives in

To add a new source type from a child theme or add-on, just write to the table with your custom `source_type` string. The volunteer-hours summary widget groups by source_type, so your custom type will show up alongside the built-ins.

## Encryption

Sensitive settings (Stripe secret key, PayPal secret, SMTP password) are encrypted at rest via libsodium (XChaCha20-Poly1305). API:

- `sp_encrypt( $plaintext )` — returns base64-encoded ciphertext.
- `sp_decrypt( $ciphertext )` — returns plaintext or empty string.
- `sp_setting_encrypt( $key, $value )` — encrypt and save to societypress_settings.
- `sp_setting_decrypt( $key )` — pull from societypress_settings, decrypt, return.

The encryption key is derived from `AUTH_KEY` + `SECURE_AUTH_KEY` in `wp-config.php`. Don't change those constants unless you're prepared to lose access to encrypted secrets — there's a fallback path that generates a random key when wp-config secrets are missing, but it's not the recommended path.

## Translation

All user-facing strings are wrapped in `__()`, `esc_html__()`, `esc_attr__()`, or `sprintf()` with text domain `societypress`. The `.pot` file lives at `Code/plugin/languages/societypress.pot` (~330KB, ~4,500+ translatable strings).

To translate: copy the `.pot` to your locale (`societypress-{locale}.po`), translate via Poedit / Loco Translate / your tool of choice, save the `.mo` next to it. WordPress picks it up automatically.

## Coding conventions

- WordPress coding standards (snake_case function names, `sp_*` prefix on plugin functions).
- Functions, not classes (with the unavoidable exceptions noted above).
- `wpdb->prepare()` for any user-input interpolation. Bare interpolation only for `$wpdb->prefix` and constants.
- Nonces on every form. `check_admin_referer` for admin POSTs, `check_ajax_referer` for AJAX.
- `current_user_can( 'sp_manage_*' )` (or `manage_options` for admin) at the top of every admin handler.
- All output through `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`. Never echo user input raw.
- Inline CSS allowed (the project is single-file by design); avoid inline styles in PHP templates that aren't dynamically computed.

## Where to file bugs / contribute

Bug reports: GitHub Issues at `SocietyPressFoundation/SocietyPress`.

This is a solo-maintained project. Contributions are reviewed but the bar is high — see `CONTRIBUTING.md` in the repo root.
