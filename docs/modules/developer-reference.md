# Developer Reference

For technically-inclined webmasters who want to extend SocietyPress beyond what the admin lets you do — add a custom page-builder widget, hook the society sidebar to add an entry, build a child theme that talks to plugin internals, or just understand the surface area.

If you're not a developer, skip this. The other [module guides](README.md) cover everything you need to run the platform.

## Architecture in one paragraph

SocietyPress is a single-file plugin (`societypress.php`, about 135,000 lines) plus a parent theme (`Code/theme/`) plus five child themes. Function-based PHP — the only classes are the ones WordPress requires (WP_List_Table subclasses, nav walkers, a classic widget). About 90 custom database tables prefixed `sp_`. Vanilla JS — jQuery only where wpColorPicker forces it. Inline CSS in admin pages, a stylesheet for the parent theme.

## Shortcodes

Most public-facing features have a shortcode, for use outside a builder page. Many have a matching page-builder widget, but not all.

| Shortcode | What it does |
|---|---|
| `[societypress_join]` | Join form / new-member signup |
| `[societypress_subscribe]` | Mailing-list signup for non-members |
| `[societypress_form]` | A form built under Website → Forms |
| `[societypress_records]` | Genealogical records search (`collection`, `login_required`) |
| `[societypress_volunteers]` | Volunteer-opportunity board |
| `[societypress_officers]` | Active officers with their positions |
| `[societypress_committees]` | Active committees with their chairs |
| `[sp_donate]` | Public donation form (Stripe + PayPal) |
| `[sp_database_subscriptions]` | Members-area gateway to paid databases |
| `[sp_help_request_submit]` | Research Help question form |
| `[sp_help_requests_archive]` | Archive of Research Help questions |
| `[sp_lineage_apply]` | Lineage-program application form |
| `[sp_lineage_my_applications]` | Signed-in member's lineage applications |
| `[sp_lineage_roster]` | Public roster of approved lineage applications |
| `[sp_my_research_assignments]` | Researcher dashboard (claimed cases + open queue) |
| `[sp_my_research_cases]` | Member's submitted research cases |
| `[sp_my_volunteer_hours]` | Member's volunteer hours this year |
| `[sp_picture_wall]` | Picture Wall gallery |
| `[sp_picture_wall_submit]` | Picture Wall submission form |
| `[sp_research_request]` | Paid Research Services intake |
| `[sp_society_sidebar]` | Auto-assembled member-portal navigation |

Each feature's guide lists its shortcode's attributes.

## Page-builder widgets

The core widgets live in `sp_get_widget_registry()`. Features add theirs through the `sp_builder_widget_types` filter (below), and the registry hides a feature's widgets while that feature is off.

## Filters

The filters most useful to child themes and add-ons are documented below. Others exist for narrower jobs: `sp_dashboard_tiles`, `sp_dashboard_cards` and `sp_dashboard_actions` (the admin dashboard), `sp_admin_search_index` (the admin's screen search), `sp_import_tier_aliases` and `sp_import_organization_types` (member import), `sp_file_object_types` (the Files screen), and `sp_vertical_files_filter`.

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

Filters the registry of stat panels on the Reports → How We're Doing page. Hook to add a custom card (a metric your child theme tracks, an aggregate from a partner system, anything). Each entry has `name`, `icon` (Dashicon class), and `callback` — a function name receiving the resolved window array and returning `[label, value, value_kind, sparkline]`. Set `always_on => true` to bypass the per-module enabled gate.

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

Register page-builder widgets. Each entry has `label`, `description`, and a `fields` array describing its settings; `icon` (a Dashicon name) and `category` are optional, and default to a generic icon on the **From your features** shelf. Field types are `text`, `number`, `checkbox` and `select` (with `options` as value => label). SocietyPress draws the settings form and sanitizes the values from `fields`, so a widget needs no admin markup of its own.

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

The render function name follows the convention `sp_render_builder_widget_{type}`, receives the saved settings, and must be globally available. Every widget also gets an optional section heading, handled for you.

## REST endpoints

| Endpoint | Method | What |
|---|---|---|
| `/wp-json/societypress/v1/health` | GET | Returns plugin version, db schema version, enabled modules. Useful for monitoring. |
| `/wp-json/societypress/v1/webhooks/stripe` | POST | Stripe webhook receiver, verified with the Webhook Signing Secret. Handles `checkout.session.completed`, `checkout.session.expired`, `invoice.paid` and `customer.subscription.deleted`. |

## AJAX endpoints

More than 100 AJAX endpoints under `wp_ajax_sp_*`. Most are admin-only; some have `wp_ajax_nopriv_*` counterparts for public flows. Patterns:

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

Module slugs: `events`, `library`, `newsletters`, `resources`, `governance`, `store`, `records`, `donations`, `blast_email`, `gallery`, `news`, `help_desk`, `help_requests`, `documents`, `voting`, `lineage`, `research_services`, `forms`, `surveys`. `members` is always on and always returns true.

## Custom tables

`{$wpdb->prefix}sp_*` — about 90 tables. Highlights:

- **Members:** `sp_members`, `sp_member_meta`, `sp_member_notes`, `sp_membership_tiers`, `sp_member_payments`, `sp_member_relationships`, `sp_member_research_areas`, `sp_member_surnames`, `sp_pending_profile_changes`, `sp_renewal_reminders`
- **Events:** `sp_events`, `sp_event_categories`, `sp_event_registrations`, `sp_event_reg_attendees`, `sp_event_reminders`, `sp_event_slots`, `sp_event_speakers`, `sp_event_speaker_assignments`, `sp_ical_feeds`
- **Library:** `sp_library_items`, `sp_library_categories`
- **Records:** `sp_records`, `sp_record_collections`, `sp_record_collection_fields`, `sp_record_values`, `sp_record_reviews`
- **Donations:** `sp_donations`, `sp_campaigns`
- **Store:** `sp_orders`, `sp_order_items`, `sp_store_products`, `sp_store_downloads`
- **Voting:** `sp_ballots`, `sp_ballot_questions`, `sp_ballot_choices`, `sp_ballot_votes`, `sp_ballot_voters`
- **Governance:** `sp_committees`, `sp_meetings`, `sp_volunteer_opportunities`, `sp_volunteer_signups`, `sp_volunteer_roles`, `sp_volunteer_hours`, `sp_groups`, `sp_group_members`
- **Help / Research:** `sp_help_requests`, `sp_help_responses`, `sp_research_cases`, `sp_research_invoices`, `sp_research_messages`
- **Lineage:** `sp_lineage_programs`, `sp_lineage_applications`, `sp_lineage_proofs`
- **Forms & surveys:** `sp_forms`, `sp_form_submissions`, `sp_survey_invites`, `sp_survey_respondents`
- **Other:** `sp_documents`, `sp_document_categories`, `sp_newsletters`, `sp_resources`, `sp_resource_categories`, `sp_blast_emails`, `sp_email_log`, `sp_subscribers`, `sp_photo_albums`, `sp_photo_album_items`, `sp_files`, `sp_file_folders`, `sp_audit_log`, `sp_access_log`, `sp_backups`

Use `$wpdb->prefix . 'sp_<table>'` to reference. Always use `$wpdb->prepare()` for any user-input interpolation.

## Capabilities

Each access area (see [User Access](user-access.md)) is one capability:

- `sp_manage_members` — Members, membership plans, groups, imports and exports
- `sp_manage_events` — Events, categories, speakers, registrations
- `sp_manage_library` — Library catalog, catalog options, research subscriptions
- `sp_manage_finances` — Payment history, donations, campaigns, store
- `sp_manage_communications` — Email to members, templates, sent email, newsletters
- `sp_manage_records` — Record collections and imports
- `sp_manage_governance` — Officers, committees, meetings, ballots, volunteers
- `sp_manage_content` — Pages, photos, documents, resource links, research help and cases, lineage
- `sp_manage_settings` — Settings, appearance, features
- `sp_view_reports` — Reports and Insights

Giving others access (Settings → Who Can Do What) needs `manage_options`. Administrators pass every check. The full screen-to-capability map is filterable through `sp_admin_capability_map`.

## Volunteer-hours source-linking

Every helping action writes to `sp_volunteer_hours` with two attribution columns:

- `source_type` — `help_request` and `research_case` are written automatically; hours logged by hand have none and group as "other"
- `source_id` — the FK to whichever table the source row lives in

To add a new source type from a child theme or add-on, just write to the table with your custom `source_type` string. The volunteer-hours summary widget groups by source_type, so your custom type will show up alongside the built-ins.

## Encryption

Payment secrets in settings, and members' cell, work and alternate phones, fax, street address lines and seasonal street address, are encrypted at rest with libsodium's secretbox (XSalsa20-Poly1305). API:

- `sp_encrypt( $plaintext )` — returns base64-encoded ciphertext.
- `sp_decrypt( $ciphertext )` — returns plaintext, or `false` if it can't be decrypted.
- `sp_setting_encrypt( $key, $value )` — encrypt and save to societypress_settings.
- `sp_setting_decrypt( $key )` — pull from societypress_settings, decrypt, return.

The encryption key is derived from `AUTH_KEY` + `SECURE_AUTH_KEY` in `wp-config.php`. Don't change those constants unless you're prepared to lose access to encrypted secrets — there's a fallback path that generates a random key when wp-config secrets are missing, but it's not the recommended path.

## Translation

All user-facing strings are wrapped in `__()`, `esc_html__()`, `esc_attr__()`, or `sprintf()` with text domain `societypress`. The `.pot` file lives at `Code/plugin/languages/societypress.pot` (about 5,700 strings when last generated; regenerate it before translating).

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
