<?php
/**
 * Admin Interface
 *
 * Handles all WordPress admin functionality: menus, pages, scripts, styles.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SocietyPress_Admin
 *
 * Main admin controller for SocietyPress.
 */
class SocietyPress_Admin {

	/**
	 * Members list table instance.
	 *
	 * @var SocietyPress_Members_List_Table|null
	 */
	private ?SocietyPress_Members_List_Table $members_table = null;

	/**
	 * Import handler instance.
	 *
	 * @var SocietyPress_Import|null
	 */
	private ?SocietyPress_Import $import = null;

	/**
	 * Event import handler instance.
	 *
	 * @var SocietyPress_Import_Events|null
	 */
	private ?SocietyPress_Import_Events $import_events = null;

	/**
	 * Dashboard widgets instance.
	 *
	 * @var SocietyPress_Dashboard_Widgets|null
	 */
	private ?SocietyPress_Dashboard_Widgets $widgets = null;

	/**
	 * Available genealogy services that can be enabled for member profiles.
	 *
	 * Each service has a unique key used for storage, a display label,
	 * and a placeholder hint showing the expected format for the field.
	 *
	 * @var array
	 */
	public const GENEALOGY_SERVICES = array(
		'wikitree' => array(
			'label'       => 'WikiTree',
			'placeholder' => 'WikiTree ID (e.g., Smith-12345)',
		),
		'familysearch' => array(
			'label'       => 'FamilySearch',
			'placeholder' => 'Person ID (e.g., XXXX-XXX)',
		),
		'geni' => array(
			'label'       => 'Geni.com',
			'placeholder' => 'Profile URL',
		),
		'werelate' => array(
			'label'       => 'WeRelate',
			'placeholder' => 'Person page name',
		),
		'ancestry' => array(
			'label'       => 'Ancestry',
			'placeholder' => 'Profile URL (public trees only)',
		),
		'myheritage' => array(
			'label'       => 'MyHeritage',
			'placeholder' => 'Profile URL (public trees only)',
		),
		'findagrave' => array(
			'label'       => 'Find A Grave',
			'placeholder' => 'Memorial ID or URL',
		),
		'23andme' => array(
			'label'       => '23andMe',
			'placeholder' => 'Display name or profile identifier',
		),
	);

	/**
	 * Default genealogy services to enable for new installations.
	 *
	 * @var array
	 */
	public const DEFAULT_GENEALOGY_SERVICES = array(
		'wikitree',
		'familysearch',
		'geni',
		'werelate',
		'ancestry',
		'myheritage',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->import = new SocietyPress_Import();
		$this->import_events = new SocietyPress_Import_Events();
		$this->widgets = new SocietyPress_Dashboard_Widgets();
		$this->init_hooks();
	}

	/**
	 * Register hooks.
	 */
	private function init_hooks(): void {
		add_action( 'admin_menu', array( $this, 'add_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_row_meta', array( $this, 'modify_plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Modify plugin row meta links on the plugins page.
	 *
	 * WHY: Changes "Visit plugin site" to "View details" for clearer UX.
	 *
	 * @param array  $links Plugin meta links.
	 * @param string $file  Plugin file path.
	 * @return array Modified links.
	 */
	public function modify_plugin_row_meta( array $links, string $file ): array {
		if ( SOCIETYPRESS_BASENAME !== $file ) {
			return $links;
		}

		// Replace "Visit plugin site" with "View details"
		foreach ( $links as $key => $link ) {
			if ( strpos( $link, 'getsocietypress.org' ) !== false ) {
				$links[ $key ] = '<a href="https://getsocietypress.org" target="_blank">' . __( 'View details', 'societypress' ) . '</a>';
			}
		}

		return $links;
	}

	/**
	 * Register plugin settings using the WordPress Settings API.
	 *
	 * All SocietyPress settings are stored in a single option: 'societypress_settings'
	 */
	public function register_settings(): void {
		// Register the main settings option
		register_setting(
			'societypress_settings_group',
			'societypress_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_default_settings(),
			)
		);

		// Display Settings Section
		add_settings_section(
			'societypress_display_section',
			__( 'Display Settings', 'societypress' ),
			array( $this, 'render_display_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'members_per_page',
			__( 'Members per page', 'societypress' ),
			array( $this, 'render_members_per_page_field' ),
			'societypress-settings',
			'societypress_display_section'
		);

		add_settings_field(
			'member_photos_enabled',
			__( 'Member Photos', 'societypress' ),
			array( $this, 'render_member_photos_enabled_field' ),
			'societypress-settings',
			'societypress_display_section'
		);

		add_settings_field(
			'dashboard_widgets_enabled',
			__( 'Dashboard Widgets', 'societypress' ),
			array( $this, 'render_dashboard_widgets_enabled_field' ),
			'societypress-settings',
			'societypress_display_section'
		);

		add_settings_field(
			'dashboard_expiring_days',
			__( 'Expiring Soon Days', 'societypress' ),
			array( $this, 'render_dashboard_expiring_days_field' ),
			'societypress-settings',
			'societypress_display_section'
		);

		add_settings_field(
			'dashboard_recent_days',
			__( 'Recent Signups Days', 'societypress' ),
			array( $this, 'render_dashboard_recent_days_field' ),
			'societypress-settings',
			'societypress_display_section'
		);

		// Organization Settings Section
		add_settings_section(
			'societypress_organization_section',
			__( 'Organization', 'societypress' ),
			array( $this, 'render_organization_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'organization_name',
			__( 'Organization Name', 'societypress' ),
			array( $this, 'render_organization_name_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_address',
			__( 'Address', 'societypress' ),
			array( $this, 'render_organization_address_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_phone',
			__( 'Phone', 'societypress' ),
			array( $this, 'render_organization_phone_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_email',
			__( 'Email', 'societypress' ),
			array( $this, 'render_organization_email_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_hours',
			__( 'Hours', 'societypress' ),
			array( $this, 'render_organization_hours_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_social',
			__( 'Social Media', 'societypress' ),
			array( $this, 'render_organization_social_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_holidays',
			__( 'Holiday Closures', 'societypress' ),
			array( $this, 'render_organization_holidays_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_directions',
			__( 'Directions', 'societypress' ),
			array( $this, 'render_organization_directions_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_parking',
			__( 'Parking', 'societypress' ),
			array( $this, 'render_organization_parking_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		add_settings_field(
			'organization_facilities',
			__( 'Facilities', 'societypress' ),
			array( $this, 'render_organization_facilities_field' ),
			'societypress-settings',
			'societypress_organization_section'
		);

		// Breadcrumb Settings Section
		add_settings_section(
			'societypress_breadcrumbs_section',
			__( 'Breadcrumbs', 'societypress' ),
			array( $this, 'render_breadcrumbs_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'breadcrumb_separator',
			__( 'Separator', 'societypress' ),
			array( $this, 'render_breadcrumb_separator_field' ),
			'societypress-settings',
			'societypress_breadcrumbs_section'
		);

		add_settings_field(
			'breadcrumb_home_icon',
			__( 'Home Icon', 'societypress' ),
			array( $this, 'render_breadcrumb_home_icon_field' ),
			'societypress-settings',
			'societypress_breadcrumbs_section'
		);

		add_settings_field(
			'breadcrumb_home_text',
			__( 'Home Text', 'societypress' ),
			array( $this, 'render_breadcrumb_home_text_field' ),
			'societypress-settings',
			'societypress_breadcrumbs_section'
		);

		// Membership Settings Section
		add_settings_section(
			'societypress_membership_section',
			__( 'Membership Settings', 'societypress' ),
			array( $this, 'render_membership_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'expiration_model',
			__( 'Expiration Model', 'societypress' ),
			array( $this, 'render_expiration_model_field' ),
			'societypress-settings',
			'societypress_membership_section'
		);

		// Payment Settings Section
		add_settings_section(
			'societypress_payment_section',
			__( 'Payment Settings', 'societypress' ),
			array( $this, 'render_payment_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'payment_mode',
			__( 'Payment Mode', 'societypress' ),
			array( $this, 'render_payment_mode_field' ),
			'societypress-settings',
			'societypress_payment_section'
		);

		add_settings_field(
			'paypal_mode',
			__( 'PayPal Environment', 'societypress' ),
			array( $this, 'render_paypal_mode_field' ),
			'societypress-settings',
			'societypress_payment_section'
		);

		add_settings_field(
			'paypal_client_id',
			__( 'PayPal Client ID', 'societypress' ),
			array( $this, 'render_paypal_client_id_field' ),
			'societypress-settings',
			'societypress_payment_section'
		);

		add_settings_field(
			'paypal_secret',
			__( 'PayPal Secret', 'societypress' ),
			array( $this, 'render_paypal_secret_field' ),
			'societypress-settings',
			'societypress_payment_section'
		);

		add_settings_field(
			'payment_methods',
			__( 'Accepted Payment Methods', 'societypress' ),
			array( $this, 'render_payment_methods_field' ),
			'societypress-settings',
			'societypress_payment_section'
		);

		// Email Settings Section
		add_settings_section(
			'societypress_email_section',
			__( 'Email Settings', 'societypress' ),
			array( $this, 'render_email_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'admin_email',
			__( 'Admin Email', 'societypress' ),
			array( $this, 'render_admin_email_field' ),
			'societypress-settings',
			'societypress_email_section'
		);

		add_settings_field(
			'email_from_name',
			__( 'From Name', 'societypress' ),
			array( $this, 'render_email_from_name_field' ),
			'societypress-settings',
			'societypress_email_section'
		);

		add_settings_field(
			'email_from_email',
			__( 'From Email', 'societypress' ),
			array( $this, 'render_email_from_email_field' ),
			'societypress-settings',
			'societypress_email_section'
		);

		// Email Notifications Section
		add_settings_section(
			'societypress_notifications_section',
			__( 'Email Notifications', 'societypress' ),
			array( $this, 'render_notifications_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'welcome_email_enabled',
			__( 'Welcome Email', 'societypress' ),
			array( $this, 'render_welcome_email_field' ),
			'societypress-settings',
			'societypress_notifications_section'
		);

		add_settings_field(
			'reminder_email_enabled',
			__( 'Renewal Reminders', 'societypress' ),
			array( $this, 'render_reminder_email_field' ),
			'societypress-settings',
			'societypress_notifications_section'
		);

		add_settings_field(
			'expired_email_enabled',
			__( 'Expired Notice', 'societypress' ),
			array( $this, 'render_expired_email_field' ),
			'societypress-settings',
			'societypress_notifications_section'
		);

		// Directory Settings Section
		add_settings_section(
			'societypress_directory_section',
			__( 'Public Directory', 'societypress' ),
			array( $this, 'render_directory_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'directory_fields',
			__( 'Visible Fields', 'societypress' ),
			array( $this, 'render_directory_fields_field' ),
			'societypress-settings',
			'societypress_directory_section'
		);

		add_settings_field(
			'directory_default_view',
			__( 'Default View', 'societypress' ),
			array( $this, 'render_directory_default_view_field' ),
			'societypress-settings',
			'societypress_directory_section'
		);

		add_settings_field(
			'directory_per_page',
			__( 'Members Per Page', 'societypress' ),
			array( $this, 'render_directory_per_page_field' ),
			'societypress-settings',
			'societypress_directory_section'
		);

		add_settings_field(
			'directory_enable_search',
			__( 'Enable Search', 'societypress' ),
			array( $this, 'render_directory_enable_search_field' ),
			'societypress-settings',
			'societypress_directory_section'
		);

		add_settings_field(
			'directory_enable_filters',
			__( 'Enable Filters', 'societypress' ),
			array( $this, 'render_directory_enable_filters_field' ),
			'societypress-settings',
			'societypress_directory_section'
		);

		// Portal Settings Section
		add_settings_section(
			'societypress_portal_section',
			__( 'Member Portal', 'societypress' ),
			array( $this, 'render_portal_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'portal_enabled',
			__( 'Enable Portal', 'societypress' ),
			array( $this, 'render_portal_enabled_field' ),
			'societypress-settings',
			'societypress_portal_section'
		);

		add_settings_field(
			'portal_page_id',
			__( 'Portal Page', 'societypress' ),
			array( $this, 'render_portal_page_id_field' ),
			'societypress-settings',
			'societypress_portal_section'
		);

		add_settings_field(
			'portal_editable_fields',
			__( 'Editable Fields', 'societypress' ),
			array( $this, 'render_portal_editable_fields_field' ),
			'societypress-settings',
			'societypress_portal_section'
		);

		add_settings_field(
			'portal_require_approval',
			__( 'Require Approval', 'societypress' ),
			array( $this, 'render_portal_require_approval_field' ),
			'societypress-settings',
			'societypress_portal_section'
		);

		// Genealogy Services Section
		add_settings_section(
			'societypress_genealogy_section',
			__( 'Genealogy Services', 'societypress' ),
			array( $this, 'render_genealogy_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'genealogy_services',
			__( 'Enabled Services', 'societypress' ),
			array( $this, 'render_genealogy_services_field' ),
			'societypress-settings',
			'societypress_genealogy_section'
		);

		// System Settings Section
		add_settings_section(
			'societypress_system_section',
			__( 'System Settings', 'societypress' ),
			array( $this, 'render_system_section' ),
			'societypress-settings'
		);

		// Support Section (shareware model - no license enforcement)
		add_settings_section(
			'societypress_license_section',
			__( 'Support', 'societypress' ),
			array( $this, 'render_license_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'license_key',
			__( 'Support This Project', 'societypress' ),
			array( $this, 'render_license_field' ),
			'societypress-settings',
			'societypress_license_section'
		);

		// Community Directory Section
		add_settings_section(
			'societypress_community_section',
			__( 'Community Directory', 'societypress' ),
			array( $this, 'render_community_section' ),
			'societypress-settings'
		);

		add_settings_field(
			'directory_listing_enabled',
			__( 'List My Society', 'societypress' ),
			array( $this, 'render_directory_listing_enabled_field' ),
			'societypress-settings',
			'societypress_community_section'
		);

		add_settings_field(
			'directory_listing_info',
			__( 'Society Information', 'societypress' ),
			array( $this, 'render_directory_listing_info_field' ),
			'societypress-settings',
			'societypress_community_section'
		);
	}

	/**
	 * Get default settings values.
	 *
	 * @return array Default settings.
	 */
	public function get_default_settings(): array {
		return array(
			// Display
			'members_per_page'            => 20,
			'member_photos_enabled'       => true,
			'dashboard_widgets_enabled'   => true,
			'dashboard_expiring_days'     => 30,
			'dashboard_recent_days'       => 30,

			// Organization
			'organization_name'           => get_bloginfo( 'name' ),

			// Email
			'admin_email'                 => get_option( 'admin_email' ),
			'email_from_name'             => get_bloginfo( 'name' ),
			'email_from_email'            => get_option( 'admin_email' ),

			// Email Notifications
			'email_notifications'         => array(
				'welcome_enabled'         => true,
				'welcome_subject'         => 'Welcome to {{organization_name}}!',
				'welcome_message'         => "Hi {{first_name}},\n\nWelcome to {{organization_name}}! Your membership is now active.\n\nYou can access the member portal here: {{portal_url}}\n\nThanks!",
				'reminder_enabled'        => true,
				'reminder_days_before'    => array( 30, 14, 7, 1 ),
				'reminder_subject'        => 'Your membership expires in {{days_until_expiration}} days',
				'reminder_message'        => "Hi {{first_name}},\n\nYour {{tier}} membership expires on {{expiration_date}}.\n\nPlease renew soon to continue enjoying member benefits.\n\nThanks!",
				'expired_enabled'         => true,
				'expired_subject'         => 'Your membership has expired',
				'expired_message'         => "Hi {{first_name}},\n\nYour membership expired on {{expiration_date}}.\n\nPlease contact us to renew.\n\nThanks!",
			),

			// Membership
			'expiration_model'            => 'calendar_year', // 'calendar_year' or 'anniversary'

			// Payments
			'payment_mode'                => 'disabled', // 'disabled', 'required', 'optional'
			'paypal_mode'                 => 'sandbox',  // 'sandbox' or 'live'
			'paypal_client_id'            => '',
			'paypal_secret'               => '',
			'payment_methods'             => array( 'paypal', 'venmo', 'card' ), // Enabled by default

			// Directory
			'directory_fields'            => array( 'name', 'location', 'tier', 'surnames' ),
			'directory_default_view'      => 'grid',
			'directory_per_page'          => 24,
			'directory_enable_search'     => true,
			'directory_enable_filters'    => true,

			// Portal
			'portal_enabled'              => true,
			'portal_page_id'              => 0,
			'portal_editable_fields'      => array( 'email', 'phone', 'address', 'surnames', 'research_areas' ),
			'portal_require_approval'     => false,

			// License
			'license_key'                 => '',
			'license_email'               => '',
			'license_hide_notice'         => false,

			// Genealogy
			'genealogy_services'          => self::DEFAULT_GENEALOGY_SERVICES,

			// Community Directory (public listing on societypress.com)
			'directory_listing_enabled'   => false,
			'directory_listing'           => array(
				'society_name'     => '',
				'website_url'      => '',
				'location'         => '', // City, State/Country
				'description'      => '', // Brief tagline
				'established'      => '', // Year founded
				'logo_url'         => '',
			),
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input from form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		// Membership settings
		$sanitized['expiration_model'] = isset( $input['expiration_model'] ) && in_array( $input['expiration_model'], array( 'calendar_year', 'anniversary' ), true )
			? $input['expiration_model']
			: 'calendar_year';

		// Payment settings
		$sanitized['payment_mode'] = isset( $input['payment_mode'] ) && in_array( $input['payment_mode'], array( 'disabled', 'required', 'optional' ), true )
			? $input['payment_mode']
			: 'disabled';

		$sanitized['paypal_mode'] = isset( $input['paypal_mode'] ) && in_array( $input['paypal_mode'], array( 'sandbox', 'live' ), true )
			? $input['paypal_mode']
			: 'sandbox';

		$sanitized['paypal_client_id'] = isset( $input['paypal_client_id'] )
			? sanitize_text_field( $input['paypal_client_id'] )
			: '';

		$sanitized['paypal_secret'] = isset( $input['paypal_secret'] )
			? sanitize_text_field( $input['paypal_secret'] )
			: '';

		// Payment methods - validate against allowed methods
		$allowed_methods = array( 'paypal', 'venmo', 'card', 'paylater' );
		$sanitized['payment_methods'] = array();
		if ( isset( $input['payment_methods'] ) && is_array( $input['payment_methods'] ) ) {
			foreach ( $input['payment_methods'] as $method ) {
				if ( in_array( $method, $allowed_methods, true ) ) {
					$sanitized['payment_methods'][] = $method;
				}
			}
		}
		// PayPal is always required if any payment method is selected
		if ( ! empty( $sanitized['payment_methods'] ) && ! in_array( 'paypal', $sanitized['payment_methods'], true ) ) {
			array_unshift( $sanitized['payment_methods'], 'paypal' );
		}

		// Display settings
		$sanitized['members_per_page'] = isset( $input['members_per_page'] )
			? max( 1, absint( $input['members_per_page'] ) )
			: 20;

		$sanitized['member_photos_enabled'] = ! empty( $input['member_photos_enabled'] );

		$sanitized['dashboard_widgets_enabled'] = ! empty( $input['dashboard_widgets_enabled'] );

		$sanitized['dashboard_expiring_days'] = isset( $input['dashboard_expiring_days'] )
			? max( 1, absint( $input['dashboard_expiring_days'] ) )
			: 30;

		$sanitized['dashboard_recent_days'] = isset( $input['dashboard_recent_days'] )
			? max( 1, absint( $input['dashboard_recent_days'] ) )
			: 30;

		// Organization settings
		$sanitized['organization_name'] = isset( $input['organization_name'] )
			? sanitize_text_field( $input['organization_name'] )
			: '';

		$sanitized['organization_address'] = isset( $input['organization_address'] )
			? sanitize_textarea_field( $input['organization_address'] )
			: '';

		$sanitized['organization_phone'] = isset( $input['organization_phone'] )
			? sanitize_text_field( $input['organization_phone'] )
			: '';

		$sanitized['organization_email'] = isset( $input['organization_email'] )
			? sanitize_email( $input['organization_email'] )
			: '';

		$sanitized['organization_hours'] = isset( $input['organization_hours'] )
			? sanitize_textarea_field( $input['organization_hours'] )
			: '';

		// Social media links - sanitize each URL
		$sanitized['organization_social'] = array();
		if ( isset( $input['organization_social'] ) && is_array( $input['organization_social'] ) ) {
			$valid_platforms = array( 'facebook', 'twitter', 'instagram', 'youtube', 'linkedin' );
			foreach ( $valid_platforms as $platform ) {
				if ( ! empty( $input['organization_social'][ $platform ] ) ) {
					$sanitized['organization_social'][ $platform ] = esc_url_raw( $input['organization_social'][ $platform ] );
				}
			}
		}

		// Additional organization info
		$sanitized['organization_holidays'] = isset( $input['organization_holidays'] )
			? sanitize_textarea_field( $input['organization_holidays'] )
			: '';

		$sanitized['organization_directions'] = isset( $input['organization_directions'] )
			? sanitize_textarea_field( $input['organization_directions'] )
			: '';

		$sanitized['organization_parking'] = isset( $input['organization_parking'] )
			? sanitize_textarea_field( $input['organization_parking'] )
			: '';

		$sanitized['organization_facilities'] = isset( $input['organization_facilities'] )
			? sanitize_textarea_field( $input['organization_facilities'] )
			: '';

		// Breadcrumb settings
		$valid_separators = array( '>', '/', '›', '»', '|', '-' );
		$sanitized['breadcrumb_separator'] = isset( $input['breadcrumb_separator'] ) && in_array( $input['breadcrumb_separator'], $valid_separators, true )
			? $input['breadcrumb_separator']
			: '>';

		$sanitized['breadcrumb_home_icon'] = ! empty( $input['breadcrumb_home_icon'] );

		$sanitized['breadcrumb_home_text'] = isset( $input['breadcrumb_home_text'] )
			? sanitize_text_field( $input['breadcrumb_home_text'] )
			: 'Home';

		// Email settings
		$sanitized['admin_email'] = isset( $input['admin_email'] )
			? sanitize_email( $input['admin_email'] )
			: get_option( 'admin_email' );

		$sanitized['email_from_name'] = isset( $input['email_from_name'] )
			? sanitize_text_field( $input['email_from_name'] )
			: get_bloginfo( 'name' );

		$sanitized['email_from_email'] = isset( $input['email_from_email'] )
			? sanitize_email( $input['email_from_email'] )
			: get_option( 'admin_email' );

		// Email notifications
		$sanitized['email_notifications'] = array(
			'welcome_enabled'      => ! empty( $input['email_notifications']['welcome_enabled'] ),
			'welcome_subject'      => sanitize_text_field( $input['email_notifications']['welcome_subject'] ?? '' ),
			'welcome_message'      => sanitize_textarea_field( $input['email_notifications']['welcome_message'] ?? '' ),
			'reminder_enabled'     => ! empty( $input['email_notifications']['reminder_enabled'] ),
			'reminder_days_before' => isset( $input['email_notifications']['reminder_days_before'] )
				? array_map( 'absint', (array) $input['email_notifications']['reminder_days_before'] )
				: array( 30, 14, 7, 1 ),
			'reminder_subject'     => sanitize_text_field( $input['email_notifications']['reminder_subject'] ?? '' ),
			'reminder_message'     => sanitize_textarea_field( $input['email_notifications']['reminder_message'] ?? '' ),
			'expired_enabled'      => ! empty( $input['email_notifications']['expired_enabled'] ),
			'expired_subject'      => sanitize_text_field( $input['email_notifications']['expired_subject'] ?? '' ),
			'expired_message'      => sanitize_textarea_field( $input['email_notifications']['expired_message'] ?? '' ),
		);

		// Directory settings
		$valid_directory_fields = array( 'name', 'location', 'tier', 'surnames', 'email' );
		$sanitized['directory_fields'] = array();
		if ( isset( $input['directory_fields'] ) && is_array( $input['directory_fields'] ) ) {
			foreach ( $input['directory_fields'] as $field ) {
				if ( in_array( $field, $valid_directory_fields, true ) ) {
					$sanitized['directory_fields'][] = $field;
				}
			}
		}

		$sanitized['directory_default_view'] = in_array( $input['directory_default_view'] ?? '', array( 'grid', 'list' ), true )
			? $input['directory_default_view']
			: 'grid';

		$sanitized['directory_per_page'] = isset( $input['directory_per_page'] )
			? max( 1, absint( $input['directory_per_page'] ) )
			: 24;

		$sanitized['directory_enable_search'] = ! empty( $input['directory_enable_search'] );
		$sanitized['directory_enable_filters'] = ! empty( $input['directory_enable_filters'] );

		// Portal settings
		$sanitized['portal_enabled'] = ! empty( $input['portal_enabled'] );
		$sanitized['portal_page_id'] = isset( $input['portal_page_id'] ) ? absint( $input['portal_page_id'] ) : 0;

		$valid_portal_fields = array( 'email', 'phone', 'address', 'surnames', 'research_areas' );
		$sanitized['portal_editable_fields'] = array();
		if ( isset( $input['portal_editable_fields'] ) && is_array( $input['portal_editable_fields'] ) ) {
			foreach ( $input['portal_editable_fields'] as $field ) {
				if ( in_array( $field, $valid_portal_fields, true ) ) {
					$sanitized['portal_editable_fields'][] = $field;
				}
			}
		}

		$sanitized['portal_require_approval'] = ! empty( $input['portal_require_approval'] );

		// License settings (don't save here - handled by license class)
		$sanitized['license_key'] = isset( $input['license_key'] ) ? sanitize_text_field( $input['license_key'] ) : '';
		$sanitized['license_email'] = isset( $input['license_email'] ) ? sanitize_email( $input['license_email'] ) : '';
		$sanitized['license_hide_notice'] = ! empty( $input['license_hide_notice'] );

		// Sanitize genealogy services - only allow valid service keys
		$valid_services = array_keys( self::GENEALOGY_SERVICES );
		$sanitized['genealogy_services'] = array();

		if ( isset( $input['genealogy_services'] ) && is_array( $input['genealogy_services'] ) ) {
			foreach ( $input['genealogy_services'] as $service ) {
				if ( in_array( $service, $valid_services, true ) ) {
					$sanitized['genealogy_services'][] = $service;
				}
			}
		}

		// Community Directory listing
		$sanitized['directory_listing_enabled'] = ! empty( $input['directory_listing_enabled'] );
		$sanitized['directory_listing'] = array(
			'society_name' => isset( $input['directory_listing']['society_name'] )
				? sanitize_text_field( $input['directory_listing']['society_name'] )
				: '',
			'website_url' => isset( $input['directory_listing']['website_url'] )
				? esc_url_raw( $input['directory_listing']['website_url'] )
				: '',
			'location' => isset( $input['directory_listing']['location'] )
				? sanitize_text_field( $input['directory_listing']['location'] )
				: '',
			'description' => isset( $input['directory_listing']['description'] )
				? sanitize_text_field( $input['directory_listing']['description'] )
				: '',
			'established' => isset( $input['directory_listing']['established'] )
				? sanitize_text_field( $input['directory_listing']['established'] )
				: '',
			'logo_url' => isset( $input['directory_listing']['logo_url'] )
				? esc_url_raw( $input['directory_listing']['logo_url'] )
				: '',
		);

		return $sanitized;
	}

	/**
	 * Get a single setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed Setting value.
	 */
	public static function get_setting( string $key, $default = null ) {
		$settings = get_option( 'societypress_settings', array() );
		$defaults = ( new self() )->get_default_settings();

		if ( null === $default && isset( $defaults[ $key ] ) ) {
			$default = $defaults[ $key ];
		}

		return $settings[ $key ] ?? $default;
	}

	/**
	 * Get enabled genealogy services with their full configuration.
	 *
	 * Returns only the services that are enabled in settings, with their
	 * label and placeholder text for display in forms.
	 *
	 * @return array Enabled services with their configuration.
	 */
	public static function get_enabled_genealogy_services(): array {
		$enabled_keys = self::get_setting( 'genealogy_services', self::DEFAULT_GENEALOGY_SERVICES );
		$enabled = array();

		foreach ( $enabled_keys as $key ) {
			if ( isset( self::GENEALOGY_SERVICES[ $key ] ) ) {
				$enabled[ $key ] = self::GENEALOGY_SERVICES[ $key ];
			}
		}

		return $enabled;
	}

	/**
	 * Render Display section description.
	 */
	public function render_display_section(): void {
		echo '<p>' . esc_html__( 'Configure how data is displayed in the admin area.', 'societypress' ) . '</p>';
	}

	/**
	 * Render Organization section description.
	 */
	public function render_organization_section(): void {
		echo '<p>' . esc_html__( 'Your organization\'s contact information. Used on the Contact page, in emails, and throughout the site.', 'societypress' ) . '</p>';
		echo '<p><code>[societypress_contact]</code> ' . esc_html__( 'displays this information on any page.', 'societypress' ) . '</p>';
	}

	/**
	 * Render Membership section description.
	 */
	public function render_membership_section(): void {
		echo '<p>' . esc_html__( 'Configure how memberships and renewals work.', 'societypress' ) . '</p>';
	}

	/**
	 * Render Email section description.
	 */
	public function render_email_section(): void {
		echo '<p>' . esc_html__( 'Email notification settings.', 'societypress' ) . '</p>';
	}

	/**
	 * Render Email Notifications section description.
	 */
	public function render_notifications_section(): void {
		echo '<p>' . esc_html__( 'Configure automated email notifications for members. Available merge tags: {{first_name}}, {{last_name}}, {{organization_name}}, {{tier}}, {{expiration_date}}, {{days_until_expiration}}, {{portal_url}}', 'societypress' ) . '</p>';
	}

	/**
	 * Render Directory section description.
	 */
	public function render_directory_section(): void {
		echo '<p>' . esc_html__( 'Configure the public member directory. Use shortcode: [societypress_directory]', 'societypress' ) . '</p>';
	}

	/**
	 * Render Portal section description.
	 */
	public function render_portal_section(): void {
		echo '<p>' . esc_html__( 'Configure the member self-service portal. Use shortcode: [societypress_portal]', 'societypress' ) . '</p>';
	}

	/**
	 * Render Genealogy section description.
	 */
	public function render_genealogy_section(): void {
		echo '<p>' . esc_html__( 'Select which genealogy research platforms to show on member profiles. Members can enter their profile links or IDs for enabled services.', 'societypress' ) . '</p>';
	}

	/**
	 * Render members_per_page field.
	 */
	public function render_members_per_page_field(): void {
		$value = self::get_setting( 'members_per_page', 20 );
		?>
		<input type="number" name="societypress_settings[members_per_page]"
		       value="<?php echo esc_attr( $value ); ?>" min="1" max="9999" class="small-text">
		<p class="description">
			<?php esc_html_e( 'Number of members to show per page in the admin list.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render member_photos_enabled field.
	 */
	public function render_member_photos_enabled_field(): void {
		$value = self::get_setting( 'member_photos_enabled', true );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[member_photos_enabled]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Allow uploading photos for member profiles', 'societypress' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Disable to save storage space. Photos are stored in the WordPress Media Library.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render dashboard_widgets_enabled field.
	 */
	public function render_dashboard_widgets_enabled_field(): void {
		$value = self::get_setting( 'dashboard_widgets_enabled', true );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[dashboard_widgets_enabled]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Show dashboard widgets for expiring memberships and recent signups', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render dashboard_expiring_days field.
	 */
	public function render_dashboard_expiring_days_field(): void {
		$value = self::get_setting( 'dashboard_expiring_days', 30 );
		?>
		<input type="number" name="societypress_settings[dashboard_expiring_days]"
		       value="<?php echo esc_attr( $value ); ?>" min="1" max="365" class="small-text">
		<p class="description">
			<?php esc_html_e( 'Show memberships expiring within this many days on the dashboard.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render dashboard_recent_days field.
	 */
	public function render_dashboard_recent_days_field(): void {
		$value = self::get_setting( 'dashboard_recent_days', 30 );
		?>
		<input type="number" name="societypress_settings[dashboard_recent_days]"
		       value="<?php echo esc_attr( $value ); ?>" min="1" max="365" class="small-text">
		<p class="description">
			<?php esc_html_e( 'Show members who joined within this many days on the dashboard.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_name field.
	 */
	public function render_organization_name_field(): void {
		$value = self::get_setting( 'organization_name', get_bloginfo( 'name' ) );
		?>
		<input type="text" name="societypress_settings[organization_name]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Used in emails and member-facing pages.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_address field.
	 *
	 * WHY: Stores physical address for Contact page, footer, emails, etc.
	 */
	public function render_organization_address_field(): void {
		$value = self::get_setting( 'organization_address', '' );
		?>
		<textarea name="societypress_settings[organization_address]"
		          rows="3" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Physical address (library, office, or meeting location). Each line will be displayed separately.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_phone field.
	 */
	public function render_organization_phone_field(): void {
		$value = self::get_setting( 'organization_phone', '' );
		?>
		<input type="tel" name="societypress_settings[organization_phone]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text"
		       placeholder="(210) 555-1234">
		<p class="description">
			<?php esc_html_e( 'Main contact phone number.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_email field.
	 */
	public function render_organization_email_field(): void {
		$value = self::get_setting( 'organization_email', get_option( 'admin_email' ) );
		?>
		<input type="email" name="societypress_settings[organization_email]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text"
		       placeholder="info@example.org">
		<p class="description">
			<?php esc_html_e( 'Public contact email address.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_hours field.
	 *
	 * WHY: Stores hours of operation for library/office in a flexible text format.
	 */
	public function render_organization_hours_field(): void {
		$value = self::get_setting( 'organization_hours', '' );
		?>
		<textarea name="societypress_settings[organization_hours]"
		          rows="4" class="large-text"
		          placeholder="Monday - Friday: 10am - 4pm&#10;Saturday: 10am - 2pm&#10;Sunday: Closed"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Hours of operation. Enter each day or time range on a new line.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_social field.
	 *
	 * WHY: Stores social media links in a structured format for easy display.
	 */
	public function render_organization_social_field(): void {
		$social = self::get_setting( 'organization_social', array() );
		$platforms = array(
			'facebook'  => __( 'Facebook', 'societypress' ),
			'twitter'   => __( 'X (Twitter)', 'societypress' ),
			'instagram' => __( 'Instagram', 'societypress' ),
			'youtube'   => __( 'YouTube', 'societypress' ),
			'linkedin'  => __( 'LinkedIn', 'societypress' ),
		);
		?>
		<div class="sp-social-fields">
			<?php foreach ( $platforms as $key => $label ) : ?>
				<p>
					<label>
						<strong><?php echo esc_html( $label ); ?>:</strong><br>
						<input type="url" name="societypress_settings[organization_social][<?php echo esc_attr( $key ); ?>]"
						       value="<?php echo esc_url( $social[ $key ] ?? '' ); ?>" class="regular-text"
						       placeholder="https://">
					</label>
				</p>
			<?php endforeach; ?>
		</div>
		<p class="description">
			<?php esc_html_e( 'Enter the full URL for each social media profile.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_holidays field.
	 *
	 * WHY: Lists days the organization is closed so visitors know when not to come.
	 *      Displayed on Contact page and can be shown in footer or elsewhere.
	 */
	public function render_organization_holidays_field(): void {
		$value = self::get_setting( 'organization_holidays', '' );
		?>
		<textarea name="societypress_settings[organization_holidays]"
		          rows="3" class="large-text"
		          placeholder="New Year's Day, Easter Sunday, Memorial Day, Independence Day, Labor Day, Thanksgiving, Christmas Eve and Day"><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Days your organization is closed. Comma-separated or one per line.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_directions field.
	 *
	 * WHY: Driving directions help visitors find the location, especially
	 *      for societies in less obvious locations or business parks.
	 */
	public function render_organization_directions_field(): void {
		$value = self::get_setting( 'organization_directions', '' );
		?>
		<textarea name="societypress_settings[organization_directions]"
		          rows="3" class="large-text"
		          placeholder="From I-410, exit onto Blanco Rd north. Go 3/4 mile and turn left onto Melissa Dr."><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Driving directions to your location.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_parking field.
	 *
	 * WHY: Parking info reduces visitor confusion, especially for societies
	 *      in shared buildings or with limited/specific parking areas.
	 */
	public function render_organization_parking_field(): void {
		$value = self::get_setting( 'organization_parking', '' );
		?>
		<textarea name="societypress_settings[organization_parking]"
		          rows="2" class="large-text"
		          placeholder="Free parking available in front lot. Use the first driveway."><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Parking availability and instructions.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render organization_facilities field.
	 *
	 * WHY: Many societies have multiple buildings or rooms (library vs meeting room).
	 *      This field explains what each space is for, reducing visitor confusion.
	 */
	public function render_organization_facilities_field(): void {
		$value = self::get_setting( 'organization_facilities', '' );
		?>
		<textarea name="societypress_settings[organization_facilities]"
		          rows="4" class="large-text"
		          placeholder="Our campus has two buildings: The Dwyer Center (first building) hosts classes and meetings. The Library (second building) is open during regular hours for research."><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'Describe your facilities, buildings, or spaces. Helpful if you have multiple areas visitors might confuse.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render breadcrumbs section description.
	 *
	 * WHY: Explains what breadcrumbs are and how they help navigation.
	 */
	public function render_breadcrumbs_section(): void {
		echo '<p>' . esc_html__( 'Breadcrumbs show visitors where they are in the site hierarchy (e.g., Home > Events > Workshop Name). Enable them in Appearance > Customize or use the Breadcrumbs widget.', 'societypress' ) . '</p>';
	}

	/**
	 * Render breadcrumb_separator field.
	 *
	 * WHY: Lets organizations choose their preferred visual separator style.
	 */
	public function render_breadcrumb_separator_field(): void {
		$value = self::get_setting( 'breadcrumb_separator', '>' );
		$options = array(
			'>'  => '> (greater than)',
			'/'  => '/ (slash)',
			'›'  => '› (single arrow)',
			'»'  => '» (double arrow)',
			'|'  => '| (pipe)',
			'-'  => '- (dash)',
		);
		?>
		<select name="societypress_settings[breadcrumb_separator]">
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Character displayed between breadcrumb items.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render breadcrumb_home_icon field.
	 *
	 * WHY: Some sites prefer a home icon instead of or alongside the word "Home".
	 */
	public function render_breadcrumb_home_icon_field(): void {
		$value = self::get_setting( 'breadcrumb_home_icon', false );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[breadcrumb_home_icon]" value="1" <?php checked( $value ); ?>>
			<?php esc_html_e( 'Show home icon (🏠) before home link', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render breadcrumb_home_text field.
	 *
	 * WHY: Lets organizations customize the home link text (e.g., "Home", "Start", or their org name).
	 */
	public function render_breadcrumb_home_text_field(): void {
		$value = self::get_setting( 'breadcrumb_home_text', 'Home' );
		?>
		<input type="text" name="societypress_settings[breadcrumb_home_text]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text"
		       placeholder="Home">
		<p class="description">
			<?php esc_html_e( 'Text for the home link. Leave empty to use "Home".', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render expiration_model field.
	 */
	public function render_expiration_model_field(): void {
		$value = self::get_setting( 'expiration_model', 'calendar_year' );
		?>
		<select name="societypress_settings[expiration_model]">
			<option value="calendar_year" <?php selected( $value, 'calendar_year' ); ?>>
				<?php esc_html_e( 'Calendar Year (Expires 12/31 of join year)', 'societypress' ); ?>
			</option>
			<option value="anniversary" <?php selected( $value, 'anniversary' ); ?>>
				<?php esc_html_e( 'Anniversary (Expires on join date + duration)', 'societypress' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Determines how membership expiration dates are calculated. Calendar Year: all memberships expire December 31 of the year they joined. Anniversary: expiration is based on join date plus tier duration.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render payment section description.
	 */
	public function render_payment_section(): void {
		?>
		<p><?php esc_html_e( 'Configure PayPal payment integration for membership dues.', 'societypress' ); ?></p>
		<?php
	}

	/**
	 * Render payment_mode field.
	 */
	public function render_payment_mode_field(): void {
		$value = self::get_setting( 'payment_mode', 'disabled' );
		?>
		<select name="societypress_settings[payment_mode]">
			<option value="disabled" <?php selected( $value, 'disabled' ); ?>>
				<?php esc_html_e( 'Disabled — No online payments', 'societypress' ); ?>
			</option>
			<option value="required" <?php selected( $value, 'required' ); ?>>
				<?php esc_html_e( 'Required — Must pay during signup', 'societypress' ); ?>
			</option>
			<option value="optional" <?php selected( $value, 'optional' ); ?>>
				<?php esc_html_e( 'Optional — Can pay now or later', 'societypress' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Required: Payment must be completed to submit membership form. Optional: Members can pay during signup or be invoiced later.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render paypal_mode field.
	 */
	public function render_paypal_mode_field(): void {
		$value = self::get_setting( 'paypal_mode', 'sandbox' );
		?>
		<select name="societypress_settings[paypal_mode]">
			<option value="sandbox" <?php selected( $value, 'sandbox' ); ?>>
				<?php esc_html_e( 'Sandbox — Test mode (no real charges)', 'societypress' ); ?>
			</option>
			<option value="live" <?php selected( $value, 'live' ); ?>>
				<?php esc_html_e( 'Live — Production mode (real payments)', 'societypress' ); ?>
			</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'Use Sandbox for testing. Switch to Live when ready to accept real payments.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render paypal_client_id field.
	 */
	public function render_paypal_client_id_field(): void {
		$value = self::get_setting( 'paypal_client_id', '' );
		?>
		<input type="text" name="societypress_settings[paypal_client_id]"
		       value="<?php echo esc_attr( $value ); ?>" class="large-text">
		<p class="description">
			<?php esc_html_e( 'From PayPal Developer Dashboard → Apps & Credentials.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render paypal_secret field.
	 */
	public function render_paypal_secret_field(): void {
		$value = self::get_setting( 'paypal_secret', '' );
		?>
		<input type="password" name="societypress_settings[paypal_secret]"
		       value="<?php echo esc_attr( $value ); ?>" class="large-text">
		<p class="description">
			<?php esc_html_e( 'Keep this secret. Never share it publicly.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render payment_methods field.
	 *
	 * WHY: Allows admin to choose which payment methods to accept.
	 *      PayPal SDK supports multiple funding sources beyond just PayPal.
	 */
	public function render_payment_methods_field(): void {
		$value = self::get_setting( 'payment_methods', array( 'paypal', 'venmo', 'card' ) );
		if ( ! is_array( $value ) ) {
			$value = array();
		}

		// Available payment methods with labels and descriptions
		$methods = array(
			'paypal' => array(
				'label' => __( 'PayPal', 'societypress' ),
				'desc'  => __( 'Pay with PayPal account', 'societypress' ),
				'icon'  => 'paypal',
			),
			'venmo' => array(
				'label' => __( 'Venmo', 'societypress' ),
				'desc'  => __( 'US only — popular with younger members', 'societypress' ),
				'icon'  => 'venmo',
			),
			'card' => array(
				'label' => __( 'Credit / Debit Card', 'societypress' ),
				'desc'  => __( 'Guest checkout without PayPal account', 'societypress' ),
				'icon'  => 'card',
			),
			'paylater' => array(
				'label' => __( 'Pay Later', 'societypress' ),
				'desc'  => __( 'PayPal financing options for buyers', 'societypress' ),
				'icon'  => 'paylater',
			),
		);
		?>
		<fieldset>
			<?php foreach ( $methods as $key => $method ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input type="checkbox"
					       name="societypress_settings[payment_methods][]"
					       value="<?php echo esc_attr( $key ); ?>"
					       <?php checked( in_array( $key, $value, true ) ); ?>
					       <?php echo 'paypal' === $key ? 'onclick="return false;" checked' : ''; ?>>
					<strong><?php echo esc_html( $method['label'] ); ?></strong>
					<span style="color: #666; margin-left: 5px;">— <?php echo esc_html( $method['desc'] ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description" style="margin-top: 10px;">
			<?php esc_html_e( 'PayPal is always required. Other methods appear based on buyer eligibility.', 'societypress' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Note: Apple Pay and Google Pay require additional setup in PayPal Dashboard.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render admin_email field.
	 */
	public function render_admin_email_field(): void {
		$value = self::get_setting( 'admin_email', get_option( 'admin_email' ) );
		?>
		<input type="email" name="societypress_settings[admin_email]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Receives membership notifications and admin alerts.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render email_from_name field.
	 */
	public function render_email_from_name_field(): void {
		$value = self::get_setting( 'email_from_name', get_bloginfo( 'name' ) );
		?>
		<input type="text" name="societypress_settings[email_from_name]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Name shown as sender on automated emails.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render email_from_email field.
	 */
	public function render_email_from_email_field(): void {
		$value = self::get_setting( 'email_from_email', get_option( 'admin_email' ) );
		?>
		<input type="email" name="societypress_settings[email_from_email]"
		       value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<p class="description">
			<?php esc_html_e( 'Email address shown as sender on automated emails.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render welcome email settings field.
	 */
	public function render_welcome_email_field(): void {
		$notifications = self::get_setting( 'email_notifications', array() );
		$enabled = $notifications['welcome_enabled'] ?? true;
		$subject = $notifications['welcome_subject'] ?? 'Welcome to {{organization_name}}!';
		$message = $notifications['welcome_message'] ?? '';
		?>
		<label>
			<input type="checkbox" name="societypress_settings[email_notifications][welcome_enabled]" value="1"
				<?php checked( $enabled, true ); ?>>
			<?php esc_html_e( 'Send welcome email to new members', 'societypress' ); ?>
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Subject:', 'societypress' ); ?><br>
			<input type="text" name="societypress_settings[email_notifications][welcome_subject]"
			       value="<?php echo esc_attr( $subject ); ?>" class="large-text">
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Message:', 'societypress' ); ?><br>
			<textarea name="societypress_settings[email_notifications][welcome_message]"
			          rows="6" class="large-text"><?php echo esc_textarea( $message ); ?></textarea>
		</label>
		<?php
	}

	/**
	 * Render renewal reminder settings field.
	 */
	public function render_reminder_email_field(): void {
		$notifications = self::get_setting( 'email_notifications', array() );
		$enabled = $notifications['reminder_enabled'] ?? true;
		$subject = $notifications['reminder_subject'] ?? 'Your membership expires in {{days_until_expiration}} days';
		$message = $notifications['reminder_message'] ?? '';
		$days = $notifications['reminder_days_before'] ?? array( 30, 14, 7, 1 );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[email_notifications][reminder_enabled]" value="1"
				<?php checked( $enabled, true ); ?>>
			<?php esc_html_e( 'Send renewal reminders before expiration', 'societypress' ); ?>
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Send reminders (days before expiration):', 'societypress' ); ?><br>
			<input type="text" name="societypress_settings[email_notifications][reminder_days_before]"
			       value="<?php echo esc_attr( implode( ', ', $days ) ); ?>" class="regular-text">
			<p class="description"><?php esc_html_e( 'Comma-separated list (e.g., 30, 14, 7, 1)', 'societypress' ); ?></p>
		</label>
		<br>
		<label>
			<?php esc_html_e( 'Subject:', 'societypress' ); ?><br>
			<input type="text" name="societypress_settings[email_notifications][reminder_subject]"
			       value="<?php echo esc_attr( $subject ); ?>" class="large-text">
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Message:', 'societypress' ); ?><br>
			<textarea name="societypress_settings[email_notifications][reminder_message]"
			          rows="6" class="large-text"><?php echo esc_textarea( $message ); ?></textarea>
		</label>
		<?php
	}

	/**
	 * Render expired notice settings field.
	 */
	public function render_expired_email_field(): void {
		$notifications = self::get_setting( 'email_notifications', array() );
		$enabled = $notifications['expired_enabled'] ?? true;
		$subject = $notifications['expired_subject'] ?? 'Your membership has expired';
		$message = $notifications['expired_message'] ?? '';
		?>
		<label>
			<input type="checkbox" name="societypress_settings[email_notifications][expired_enabled]" value="1"
				<?php checked( $enabled, true ); ?>>
			<?php esc_html_e( 'Send notice after membership expires', 'societypress' ); ?>
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Subject:', 'societypress' ); ?><br>
			<input type="text" name="societypress_settings[email_notifications][expired_subject]"
			       value="<?php echo esc_attr( $subject ); ?>" class="large-text">
		</label>
		<br><br>
		<label>
			<?php esc_html_e( 'Message:', 'societypress' ); ?><br>
			<textarea name="societypress_settings[email_notifications][expired_message]"
			          rows="6" class="large-text"><?php echo esc_textarea( $message ); ?></textarea>
		</label>
		<?php
	}

	/**
	 * Render directory fields selection.
	 */
	public function render_directory_fields_field(): void {
		$selected = self::get_setting( 'directory_fields', array( 'name', 'location', 'tier', 'surnames' ) );
		$available = array(
			'name'     => __( 'Name', 'societypress' ),
			'location' => __( 'Location (City, State)', 'societypress' ),
			'tier'     => __( 'Membership Tier', 'societypress' ),
			'surnames' => __( 'Surnames Researched', 'societypress' ),
			'email'    => __( 'Email (obfuscated)', 'societypress' ),
		);
		?>
		<fieldset>
			<?php foreach ( $available as $key => $label ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input type="checkbox" name="societypress_settings[directory_fields][]" value="<?php echo esc_attr( $key ); ?>"
						<?php checked( in_array( $key, $selected, true ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Select which fields to display in the public directory.', 'societypress' ); ?></p>
		<?php
	}

	/**
	 * Render directory default view field.
	 */
	public function render_directory_default_view_field(): void {
		$value = self::get_setting( 'directory_default_view', 'grid' );
		?>
		<select name="societypress_settings[directory_default_view]">
			<option value="grid" <?php selected( $value, 'grid' ); ?>><?php esc_html_e( 'Grid View', 'societypress' ); ?></option>
			<option value="list" <?php selected( $value, 'list' ); ?>><?php esc_html_e( 'List View', 'societypress' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Render directory per page field.
	 */
	public function render_directory_per_page_field(): void {
		$value = self::get_setting( 'directory_per_page', 24 );
		?>
		<input type="number" name="societypress_settings[directory_per_page]"
		       value="<?php echo esc_attr( $value ); ?>" min="1" max="100" class="small-text">
		<p class="description"><?php esc_html_e( 'Number of members to show per page in the directory.', 'societypress' ); ?></p>
		<?php
	}

	/**
	 * Render directory enable search field.
	 */
	public function render_directory_enable_search_field(): void {
		$value = self::get_setting( 'directory_enable_search', true );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[directory_enable_search]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Enable search box in directory', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render directory enable filters field.
	 */
	public function render_directory_enable_filters_field(): void {
		$value = self::get_setting( 'directory_enable_filters', true );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[directory_enable_filters]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Enable tier and location filters in directory', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render portal enabled field.
	 */
	public function render_portal_enabled_field(): void {
		$value = self::get_setting( 'portal_enabled', true );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[portal_enabled]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Enable member self-service portal', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render portal page selection field.
	 */
	public function render_portal_page_id_field(): void {
		$value = self::get_setting( 'portal_page_id', 0 );
		wp_dropdown_pages(
			array(
				'name'              => 'societypress_settings[portal_page_id]',
				'selected'          => $value,
				'show_option_none'  => __( '— Select —', 'societypress' ),
				'option_none_value' => 0,
			)
		);
		?>
		<p class="description">
			<?php esc_html_e( 'Page containing the [societypress_portal] shortcode. Leave empty to use any page.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render portal editable fields selection.
	 */
	public function render_portal_editable_fields_field(): void {
		$selected = self::get_setting( 'portal_editable_fields', array( 'email', 'phone', 'address', 'surnames', 'research_areas' ) );
		$available = array(
			'email'          => __( 'Email Address', 'societypress' ),
			'phone'          => __( 'Phone Number', 'societypress' ),
			'address'        => __( 'Mailing Address', 'societypress' ),
			'surnames'       => __( 'Surnames Researched', 'societypress' ),
			'research_areas' => __( 'Research Areas', 'societypress' ),
		);
		?>
		<fieldset>
			<?php foreach ( $available as $key => $label ) : ?>
				<label style="display: block; margin-bottom: 8px;">
					<input type="checkbox" name="societypress_settings[portal_editable_fields][]" value="<?php echo esc_attr( $key ); ?>"
						<?php checked( in_array( $key, $selected, true ) ); ?>>
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Select which fields members can edit in the portal.', 'societypress' ); ?></p>
		<?php
	}

	/**
	 * Render portal require approval field.
	 */
	public function render_portal_require_approval_field(): void {
		$value = self::get_setting( 'portal_require_approval', false );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[portal_require_approval]" value="1"
				<?php checked( $value, true ); ?>>
			<?php esc_html_e( 'Require admin approval for member profile changes', 'societypress' ); ?>
		</label>
		<?php
	}

	/**
	 * Render genealogy_services field.
	 *
	 * Displays checkboxes for each available genealogy service,
	 * allowing admins to choose which services appear in member profiles.
	 */
	public function render_genealogy_services_field(): void {
		$enabled = self::get_setting( 'genealogy_services', self::DEFAULT_GENEALOGY_SERVICES );

		echo '<fieldset>';

		foreach ( self::GENEALOGY_SERVICES as $key => $service ) {
			$checked = in_array( $key, $enabled, true ) ? 'checked' : '';
			printf(
				'<label style="display: block; margin-bottom: 8px;">
                    <input type="checkbox" name="societypress_settings[genealogy_services][]" value="%s" %s>
                    <strong>%s</strong>
                    <span class="description" style="margin-left: 5px; color: #666;">— %s</span>
                </label>',
				esc_attr( $key ),
				esc_attr( $checked ),
				esc_html( $service['label'] ),
				esc_html( $service['placeholder'] )
			);
		}

		echo '</fieldset>';
		echo '<p class="description" style="margin-top: 10px;">';
		esc_html_e( 'Only checked services will appear in member edit forms and CSV exports.', 'societypress' );
		echo '</p>';
	}

	/**
	 * Render System Settings section description.
	 */
	public function render_system_section(): void {
		echo '<p>';
		esc_html_e( 'Configure automatic updates and system preferences.', 'societypress' );
		echo '</p>';
	}

	/**
	 * Render Support section description.
	 *
	 * WHY: Shareware model - encourage donations without enforcement.
	 */
	public function render_license_section(): void {
		echo '<p>';
		esc_html_e( 'SocietyPress is shareware. All features work without payment.', 'societypress' );
		echo '</p>';
	}

	/**
	 * Render license key field.
	 */
	public function render_license_field(): void {
		if ( societypress()->license ) {
			// Let the license class handle the actual rendering
			societypress()->license->render_license_field();
		} else {
			echo '<p class="description">';
			esc_html_e( 'License manager not available.', 'societypress' );
			echo '</p>';
		}
	}

	/**
	 * Render Community Directory section description.
	 */
	public function render_community_section(): void {
		?>
		<p>
			<?php esc_html_e( 'Get a free listing on the SocietyPress community directory! Showcase your society and connect with others.', 'societypress' ); ?>
		</p>
		<p>
			<a href="https://societypress.com/community/" target="_blank"><?php esc_html_e( 'View the Community Directory →', 'societypress' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Render directory listing enabled field.
	 */
	public function render_directory_listing_enabled_field(): void {
		$enabled = self::get_setting( 'directory_listing_enabled', false );
		?>
		<label>
			<input type="checkbox" name="societypress_settings[directory_listing_enabled]" value="1"
			       <?php checked( $enabled ); ?> id="directory-listing-toggle">
			<?php esc_html_e( 'Yes, list our society in the SocietyPress Community Directory', 'societypress' ); ?>
		</label>
		<p class="description">
			<?php esc_html_e( 'Your listing will appear on societypress.com after review.', 'societypress' ); ?>
		</p>
		<?php
	}

	/**
	 * Render directory listing info fields.
	 */
	public function render_directory_listing_info_field(): void {
		$listing = self::get_setting( 'directory_listing', array() );
		$org_name = self::get_setting( 'organization_name', get_bloginfo( 'name' ) );

		// Default to organization name if not set
		$listing = wp_parse_args( $listing, array(
			'society_name' => $org_name,
			'website_url'  => home_url(),
			'location'     => '',
			'description'  => '',
			'established'  => '',
			'logo_url'     => '',
		) );
		?>
		<div id="directory-listing-fields">
			<table class="form-table" style="margin-top: 0;">
				<tr>
					<th scope="row"><label for="listing-society-name"><?php esc_html_e( 'Society Name', 'societypress' ); ?></label></th>
					<td>
						<input type="text" name="societypress_settings[directory_listing][society_name]"
						       id="listing-society-name" class="regular-text"
						       value="<?php echo esc_attr( $listing['society_name'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="listing-website-url"><?php esc_html_e( 'Website URL', 'societypress' ); ?></label></th>
					<td>
						<input type="url" name="societypress_settings[directory_listing][website_url]"
						       id="listing-website-url" class="regular-text"
						       value="<?php echo esc_attr( $listing['website_url'] ); ?>"
						       placeholder="https://yoursite.org">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="listing-location"><?php esc_html_e( 'Location', 'societypress' ); ?></label></th>
					<td>
						<input type="text" name="societypress_settings[directory_listing][location]"
						       id="listing-location" class="regular-text"
						       value="<?php echo esc_attr( $listing['location'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Springfield, Texas', 'societypress' ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="listing-description"><?php esc_html_e( 'Tagline', 'societypress' ); ?></label></th>
					<td>
						<input type="text" name="societypress_settings[directory_listing][description]"
						       id="listing-description" class="large-text"
						       value="<?php echo esc_attr( $listing['description'] ); ?>"
						       placeholder="<?php esc_attr_e( 'Preserving our heritage since 1965', 'societypress' ); ?>"
						       maxlength="100">
						<p class="description"><?php esc_html_e( 'Brief tagline (100 characters max).', 'societypress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="listing-established"><?php esc_html_e( 'Year Established', 'societypress' ); ?></label></th>
					<td>
						<input type="text" name="societypress_settings[directory_listing][established]"
						       id="listing-established" class="small-text"
						       value="<?php echo esc_attr( $listing['established'] ); ?>"
						       placeholder="1965" maxlength="4">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="listing-logo-url"><?php esc_html_e( 'Logo URL', 'societypress' ); ?></label></th>
					<td>
						<input type="url" name="societypress_settings[directory_listing][logo_url]"
						       id="listing-logo-url" class="large-text"
						       value="<?php echo esc_attr( $listing['logo_url'] ); ?>"
						       placeholder="https://yoursite.org/wp-content/uploads/logo.png">
						<p class="description"><?php esc_html_e( 'Square logo recommended (200x200px or larger).', 'societypress' ); ?></p>
					</td>
				</tr>
			</table>
		</div>
		<script>
		jQuery(document).ready(function($) {
			function toggleListingFields() {
				var enabled = $('#directory-listing-toggle').is(':checked');
				$('#directory-listing-fields').toggle(enabled);
			}
			toggleListingFields();
			$('#directory-listing-toggle').on('change', toggleListingFields);
		});
		</script>
		<?php
	}

	/**
	 * Add admin menu pages.
	 *
	 * Creates the main SocietyPress menu and submenus.
	 */
	public function add_menus(): void {
		// Main menu
		add_menu_page(
			__( 'SocietyPress', 'societypress' ),
			__( 'SocietyPress', 'societypress' ),
			'manage_society_members',
			'societypress',
			array( $this, 'render_dashboard' ),
			'dashicons-groups',
			30
		);

		// Dashboard
		add_submenu_page(
			'societypress',
			__( 'Dashboard', 'societypress' ),
			__( 'Dashboard', 'societypress' ),
			'manage_society_members',
			'societypress',
			array( $this, 'render_dashboard' )
		);

		// Leadership
		add_submenu_page(
			'societypress',
			__( 'Leadership', 'societypress' ),
			__( 'Leadership', 'societypress' ),
			'manage_society_members',
			'societypress-leadership',
			array( $this, 'render_placeholder_page' )
		);

		// Committees
		add_submenu_page(
			'societypress',
			__( 'Committees', 'societypress' ),
			__( 'Committees', 'societypress' ),
			'manage_society_members',
			'societypress-committees',
			array( $this, 'render_placeholder_page' )
		);

		// Calendar (events list)
		add_submenu_page(
			'societypress',
			__( 'Calendar', 'societypress' ),
			__( 'Calendar', 'societypress' ),
			'manage_society_members',
			'edit.php?post_type=sp_event'
		);

		// Add New Event
		add_submenu_page(
			'societypress',
			__( 'Add New Event', 'societypress' ),
			__( 'Add New Event', 'societypress' ),
			'manage_society_members',
			'post-new.php?post_type=sp_event'
		);

		// Members
		add_submenu_page(
			'societypress',
			__( 'Members', 'societypress' ),
			__( 'Members', 'societypress' ),
			'manage_society_members',
			'societypress-members',
			array( $this, 'render_members_page' )
		);

		// Add New Member
		add_submenu_page(
			'societypress',
			__( 'Add New Member', 'societypress' ),
			__( 'Add New Member', 'societypress' ),
			'manage_society_members',
			'societypress-member-edit',
			array( $this, 'render_member_edit' )
		);

		// Import Members
		add_submenu_page(
			'societypress',
			__( 'Import Members', 'societypress' ),
			__( 'Import Members', 'societypress' ),
			'manage_society_members',
			'societypress-import',
			array( $this, 'render_import_page' )
		);

		// Import Events
		add_submenu_page(
			'societypress',
			__( 'Import Events', 'societypress' ),
			__( 'Import Events', 'societypress' ),
			'manage_society_members',
			'societypress-import-events',
			array( $this, 'render_import_events_page' )
		);

		// Member Levels
		add_submenu_page(
			'societypress',
			__( 'Member Levels', 'societypress' ),
			__( 'Member Levels', 'societypress' ),
			'manage_society_members',
			'societypress-tiers',
			array( $this, 'render_tiers_page' )
		);

		// Library
		add_submenu_page(
			'societypress',
			__( 'Library', 'societypress' ),
			__( 'Library', 'societypress' ),
			'manage_society_members',
			'societypress-library',
			array( $this, 'render_placeholder_page' )
		);

		// Settings
		add_submenu_page(
			'societypress',
			__( 'Settings', 'societypress' ),
			__( 'Settings', 'societypress' ),
			'manage_society_members',
			'societypress-settings',
			array( $this, 'render_settings_page' )
		);

		// Hidden pages (accessible via URL only)
		// Add/Edit tier
		add_submenu_page(
			'',
			__( 'Edit Tier', 'societypress' ),
			__( 'Edit Tier', 'societypress' ),
			'manage_society_members',
			'societypress-tier-edit',
			array( $this, 'render_tier_edit' )
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( ?string $hook ): void {
		// Only load on our pages
		if ( null === $hook || strpos( $hook, 'societypress' ) === false ) {
			return;
		}

		// Admin styles
		wp_enqueue_style(
			'societypress-admin',
			SOCIETYPRESS_URL . 'assets/css/admin.css',
			array(),
			SOCIETYPRESS_VERSION
		);

		// Admin scripts
		wp_enqueue_script(
			'societypress-admin',
			SOCIETYPRESS_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			SOCIETYPRESS_VERSION,
			true
		);

		// Enqueue media uploader for member edit pages
		if ( strpos( $hook, 'societypress-add-member' ) !== false ||
		     ( strpos( $hook, 'societypress-members' ) !== false && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) ) {
			wp_enqueue_media();
		}

		// Get membership tiers for expiration calculation
		$tiers_handler = societypress()->tiers;
		$tiers = $tiers_handler->get_all();
		$tiers_data = array();
		foreach ( $tiers as $tier ) {
			$tiers_data[ $tier->id ] = array(
				'id'              => $tier->id,
				'name'            => $tier->name,
				'duration_months' => $tier->duration_months,
			);
		}

		// Localize script with data
		wp_localize_script(
			'societypress-admin',
			'societypressAdmin',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'societypress_admin' ),
				'expirationModel' => self::get_setting( 'expiration_model', 'calendar_year' ),
				'tiers'           => $tiers_data,
				'strings'         => array(
					'confirmDelete'            => __( 'Are you sure you want to delete this member? This cannot be undone.', 'societypress' ),
					'confirmDeleteAll'         => __( 'WARNING: This will PERMANENTLY DELETE ALL MEMBERS from the database. This action CANNOT be undone. Are you absolutely sure?', 'societypress' ),
					'confirmDeleteAllSelected' => __( 'You are about to delete ALL members across all pages. Are you sure?', 'societypress' ),
					'saving'                   => __( 'Saving...', 'societypress' ),
					'saved'                    => __( 'Saved!', 'societypress' ),
					'error'                    => __( 'An error occurred. Please try again.', 'societypress' ),
					'photoUploadTitle'         => __( 'Select Member Photo', 'societypress' ),
					'photoUploadButton'        => __( 'Use this photo', 'societypress' ),
					'photoChangeButton'        => __( 'Change Photo', 'societypress' ),
					'photoUploadButtonText'    => __( 'Upload Photo', 'societypress' ),
					'photoTooLarge'            => __( 'Photo must be less than 1MB.', 'societypress' ),
				),
			)
		);

		// Add inline script for member edit form functionality
		if ( strpos( $hook, 'societypress-add-member' ) !== false ||
		     strpos( $hook, 'societypress-member-edit' ) !== false ||
		     ( strpos( $hook, 'societypress-members' ) !== false && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) ) {
			wp_add_inline_script(
				'societypress-admin',
				"
				jQuery(document).ready(function($) {
					// Toggle email required based on 'no online access' checkbox
					$('#no_online_access').on('change', function() {
						var noAccess = $(this).is(':checked');
						var \$email = $('#primary_email');
						var \$indicator = $('#email-required-indicator');
						var \$note = $('#email-optional-note');

						if (noAccess) {
							\$email.prop('required', false);
							\$indicator.text('');
							\$note.show();
						} else {
							\$email.prop('required', true);
							\$indicator.text(' *');
							\$note.hide();
						}
					});
				});
				"
			);
		}

		// Load import script on import page
		if ( strpos( $hook, 'societypress-import' ) !== false ) {
			wp_enqueue_script(
				'societypress-import',
				SOCIETYPRESS_URL . 'assets/js/import.js',
				array( 'jquery' ),
				SOCIETYPRESS_VERSION,
				true
			);
		}
	}

	/**
	 * Handle admin actions (form submissions, etc.).
	 */
	public function handle_actions(): void {
		// Check for member form submission
		if ( isset( $_POST['societypress_action'] ) && 'save_member' === $_POST['societypress_action'] ) {
			$this->save_member();
		}

		// Check for tier form submission
		if ( isset( $_POST['societypress_action'] ) && 'save_tier' === $_POST['societypress_action'] ) {
			$this->save_tier();
		}

		// Check for tier delete
		if ( isset( $_GET['action'] ) && 'delete_tier' === $_GET['action'] && isset( $_GET['tier'] ) ) {
			$this->delete_tier();
		}

		// Check for single member delete
		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['member'] ) ) {
			$this->delete_member();
		}

		// Check for export action
		if ( isset( $_GET['action'] ) && 'export' === $_GET['action'] && isset( $_GET['page'] ) && 'societypress-members' === $_GET['page'] ) {
			$this->export_members();
		}

		// Check for bulk actions
		if ( isset( $_POST['action'] ) && -1 !== (int) $_POST['action'] ) {
			$this->handle_bulk_action( sanitize_text_field( $_POST['action'] ) );
		} elseif ( isset( $_POST['action2'] ) && -1 !== (int) $_POST['action2'] ) {
			$this->handle_bulk_action( sanitize_text_field( $_POST['action2'] ) );
		}
	}

	/**
	 * Export members to CSV.
	 *
	 * Respects current filters (status, tier, search).
	 */
	private function export_members(): void {
		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'export_members' ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to export members.', 'societypress' ) );
		}

		// Build filter args
		$args = array( 'limit' => 0 );

		if ( ! empty( $_GET['status'] ) ) {
			$args['status'] = sanitize_text_field( $_GET['status'] );
		}
		if ( ! empty( $_GET['tier'] ) ) {
			$args['tier_id'] = absint( $_GET['tier'] );
		}
		if ( ! empty( $_GET['s'] ) ) {
			$args['search'] = sanitize_text_field( $_GET['s'] );
		}

		$members_handler = societypress()->members;
		$tiers_handler = societypress()->tiers;
		$members = $members_handler->get_members( $args );

		// Set headers for CSV download
		$filename = 'societypress-members-' . gmdate( 'Y-m-d' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Write UTF-8 BOM for Excel compatibility
		fwrite( $output, "\xEF\xBB\xBF" );

		// Get enabled genealogy services for export
		$enabled_services = self::get_enabled_genealogy_services();

		// Build header row - base columns plus enabled genealogy services
		$headers = array(
			'First Name',
			'Middle Name',
			'Last Name',
			'Birth Date',
			'Email',
			'Membership Tier',
			'Status',
			'Join Date',
			'Expiration Date',
			'Phone',
			'Home Phone',
			'Street Address',
			'Address Line 2',
			'City',
			'State/Province',
			'Postal Code',
			'Country',
			'Directory Visible',
			'Auto Renew',
		);

		// Add header for each enabled genealogy service
		foreach ( $enabled_services as $service ) {
			$headers[] = $service['label'];
		}

		fputcsv( $output, $headers );

		// Write data rows
		foreach ( $members as $member ) {
			$contact = $members_handler->get_contact( $member->id );
			$tier = $tiers_handler->get( $member->membership_tier_id );

			// Build base row data
			$row = array(
				$member->first_name,
				$member->middle_name ?? '',
				$member->last_name,
				$member->birth_date ?? '',
				$contact->primary_email ?? '',
				$tier->name ?? '',
				ucfirst( $member->status ),
				$member->join_date,
				$member->expiration_date ?? '',
				$contact->cell_phone ?? '',
				$contact->home_phone ?? '',
				$contact->street_address ?? '',
				$contact->address_line_2 ?? '',
				$contact->city ?? '',
				$contact->state_province ?? '',
				$contact->postal_code ?? '',
				$contact->country ?? '',
				$member->directory_visible ? 'Yes' : 'No',
				$member->auto_renew ? 'Yes' : 'No',
			);

			// Add genealogy service values for each enabled service
			foreach ( array_keys( $enabled_services ) as $service_key ) {
				$row[] = $members_handler->get_meta( $member->id, 'genealogy_' . $service_key ) ?? '';
			}

			fputcsv( $output, $row );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Delete a single member.
	 */
	private function delete_member(): void {
		$member_id = absint( $_GET['member'] ?? 0 );

		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_member_' . $member_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to delete members.', 'societypress' ) );
		}

		$members = societypress()->members;
		$member = $members->get( $member_id );

		if ( ! $member ) {
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=not_found' ) );
			exit;
		}

		$result = $members->delete( $member_id );

		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=deleted' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=delete_error' ) );
		}
		exit;
	}

	/**
	 * Handle bulk actions on members.
	 *
	 * @param string $action The bulk action to perform.
	 */
	private function handle_bulk_action( string $action ): void {
		// Only process on members page
		if ( ! isset( $_GET['page'] ) || 'societypress-members' !== $_GET['page'] ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			return;
		}

		// Verify bulk action nonce
		check_admin_referer( 'bulk-members' );

		$members = societypress()->members;
		$count = 0;

		// Handle "Delete ALL Members" action - this doesn't require selected members
		if ( 'delete_all' === $action ) {
			// Get all member IDs
			$all_members = $members->get_members( array( 'limit' => 0 ) );

			foreach ( $all_members as $member ) {
				if ( $members->delete( $member->id ) ) {
					$count++;
				}
			}

			wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=all_deleted&count=' . $count ) );
			exit;
		}

		// For other bulk actions, we need selected member IDs
		// Check if "select all" was triggered (hidden field set by JavaScript)
		if ( isset( $_POST['societypress_select_all'] ) && '1' === $_POST['societypress_select_all'] ) {
			// Build filter args to match what was displayed (respects current filters)
			$filter_args = array( 'limit' => 0 );

			if ( ! empty( $_POST['societypress_filter_status'] ) ) {
				$filter_args['status'] = sanitize_text_field( $_POST['societypress_filter_status'] );
			}
			if ( ! empty( $_POST['societypress_filter_tier'] ) ) {
				$filter_args['tier_id'] = absint( $_POST['societypress_filter_tier'] );
			}
			if ( ! empty( $_POST['societypress_filter_search'] ) ) {
				$filter_args['search'] = sanitize_text_field( $_POST['societypress_filter_search'] );
			}

			// Get filtered member IDs from the database
			$all_members = $members->get_members( $filter_args );
			$member_ids = array_map( function( $m ) { return $m->id; }, $all_members );
		} else {
			// Use the selected checkboxes
			$member_ids = array_map( 'absint', $_POST['member'] ?? array() );
		}

		if ( empty( $member_ids ) ) {
			return;
		}

		switch ( $action ) {
			case 'delete':
				foreach ( $member_ids as $id ) {
					if ( $members->delete( $id ) ) {
						$count++;
					}
				}
				$message = 'bulk_deleted';
				break;

			case 'activate':
				foreach ( $member_ids as $id ) {
					if ( $members->update_status( $id, 'active' ) ) {
						$count++;
					}
				}
				$message = 'bulk_activated';
				break;

			case 'deactivate':
				foreach ( $member_ids as $id ) {
					if ( $members->update_status( $id, 'expired' ) ) {
						$count++;
					}
				}
				$message = 'bulk_deactivated';
				break;

			case 'create_users':
				$user_manager = societypress()->user_manager;
				$results      = $user_manager->bulk_create_users( $member_ids );
				$count        = $results['created'] + $results['linked'];

				// Add error messages to notices if any
				if ( ! empty( $results['errors'] ) ) {
					foreach ( $results['errors'] as $error ) {
						$this->add_admin_notice( $error, 'error' );
					}
				}

				// Build message with details
				$message = 'users_created&created=' . $results['created'] . '&linked=' . $results['linked'] . '&skipped=' . $results['skipped'];
				break;

			default:
				return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=societypress-members&message=' . $message . '&count=' . $count ) );
		exit;
	}

	/**
	 * Render the dashboard page.
	 */
	public function render_dashboard(): void {
		// Get some basic stats
		$members = societypress()->members;
		$tiers   = societypress()->tiers;

		$stats = array(
			'total'   => count( $members->get_members( array( 'limit' => 0 ) ) ),
			'active'  => count( $members->get_members( array( 'status' => 'active', 'limit' => 0 ) ) ),
			'expired' => count( $members->get_members( array( 'status' => 'expired', 'limit' => 0 ) ) ),
			'pending' => count( $members->get_members( array( 'status' => 'pending', 'limit' => 0 ) ) ),
		);

		$tier_counts = $tiers->get_member_counts();
		$all_tiers   = $tiers->get_all();

		?>
		<div class="wrap societypress-admin">
			<h1><?php esc_html_e( 'SocietyPress Dashboard', 'societypress' ); ?></h1>

			<div class="societypress-dashboard-stats">
				<div class="stat-card">
					<span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Total Members', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-active">
					<span class="stat-number"><?php echo esc_html( $stats['active'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-expired">
					<span class="stat-number"><?php echo esc_html( $stats['expired'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Expired', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-pending">
					<span class="stat-number"><?php echo esc_html( $stats['pending'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Pending', 'societypress' ); ?></span>
				</div>
			</div>

			<div class="societypress-dashboard-sections">
				<div class="dashboard-section">
					<h2><?php esc_html_e( 'Members by Tier', 'societypress' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Tier', 'societypress' ); ?></th>
								<th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
								<th><?php esc_html_e( 'Price', 'societypress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_tiers as $tier ) : ?>
								<tr>
									<td><?php echo esc_html( $tier->name ); ?></td>
									<td><?php echo esc_html( $tier_counts[ $tier->id ] ?? 0 ); ?></td>
									<td>$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="dashboard-section">
					<h2><?php esc_html_e( 'Quick Actions', 'societypress' ); ?></h2>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-member-edit' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Add New Member', 'societypress' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button">
							<?php esc_html_e( 'View All Members', 'societypress' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the membership overview page.
	 *
	 * WHY: Provides a central hub for all membership-related tasks.
	 */
	public function render_membership_page(): void {
		$members = societypress()->members;
		$tiers   = societypress()->tiers;

		$stats = array(
			'total'   => count( $members->get_members( array( 'limit' => 0 ) ) ),
			'active'  => count( $members->get_members( array( 'status' => 'active', 'limit' => 0 ) ) ),
			'expired' => count( $members->get_members( array( 'status' => 'expired', 'limit' => 0 ) ) ),
			'pending' => count( $members->get_members( array( 'status' => 'pending', 'limit' => 0 ) ) ),
		);

		$tier_counts = $tiers->get_member_counts();
		$all_tiers   = $tiers->get_all();
		?>
		<div class="wrap societypress-admin">
			<h1><?php esc_html_e( 'Membership Management', 'societypress' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Manage your society members, membership tiers, and import member data.', 'societypress' ); ?>
			</p>

			<div class="societypress-dashboard-stats">
				<div class="stat-card">
					<span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Total Members', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-active">
					<span class="stat-number"><?php echo esc_html( $stats['active'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-expired">
					<span class="stat-number"><?php echo esc_html( $stats['expired'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Expired', 'societypress' ); ?></span>
				</div>
				<div class="stat-card stat-pending">
					<span class="stat-number"><?php echo esc_html( $stats['pending'] ); ?></span>
					<span class="stat-label"><?php esc_html_e( 'Pending', 'societypress' ); ?></span>
				</div>
			</div>

			<div class="societypress-dashboard-sections">
				<div class="dashboard-section">
					<h2><?php esc_html_e( 'Quick Actions', 'societypress' ); ?></h2>
					<p>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button button-primary button-large">
							<span class="dashicons dashicons-groups" style="margin-top: 3px;"></span>
							<?php esc_html_e( 'View All Members', 'societypress' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-member-edit' ) ); ?>" class="button button-large">
							<span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
							<?php esc_html_e( 'Add New Member', 'societypress' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-import' ) ); ?>" class="button button-large">
							<span class="dashicons dashicons-upload" style="margin-top: 3px;"></span>
							<?php esc_html_e( 'Import Members', 'societypress' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-tiers' ) ); ?>" class="button button-large">
							<span class="dashicons dashicons-tag" style="margin-top: 3px;"></span>
							<?php esc_html_e( 'Manage Tiers', 'societypress' ); ?>
						</a>
					</p>
				</div>

				<div class="dashboard-section">
					<h2><?php esc_html_e( 'Members by Tier', 'societypress' ); ?></h2>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Tier', 'societypress' ); ?></th>
								<th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
								<th><?php esc_html_e( 'Price', 'societypress' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'societypress' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_tiers as $tier ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $tier->name ); ?></strong></td>
									<td><?php echo esc_html( $tier_counts[ $tier->id ] ?? 0 ); ?></td>
									<td>$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?></td>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-tier-edit&id=' . $tier->id ) ); ?>">
											<?php esc_html_e( 'Edit', 'societypress' ); ?>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the members list page.
	 */
	public function render_members_page(): void {
		$this->members_table = new SocietyPress_Members_List_Table();
		$this->members_table->prepare_items();

		// Build filter args to match what's currently being displayed
		$filter_args = array( 'limit' => 0 );
		$current_status = '';
		$current_tier = '';
		$current_search = '';

		if ( ! empty( $_REQUEST['status'] ) ) {
			$current_status = sanitize_text_field( $_REQUEST['status'] );
			$filter_args['status'] = $current_status;
		}
		if ( ! empty( $_REQUEST['tier'] ) ) {
			$current_tier = absint( $_REQUEST['tier'] );
			$filter_args['tier_id'] = $current_tier;
		}
		if ( ! empty( $_REQUEST['s'] ) ) {
			$current_search = sanitize_text_field( $_REQUEST['s'] );
			$filter_args['search'] = $current_search;
		}

		// Get filtered total for "select all" functionality
		$total_members = count( societypress()->members->get_members( $filter_args ) );
		$items_on_page = count( $this->members_table->items );

		// Build export URL with current filters
		$export_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'   => 'societypress-members',
					'action' => 'export',
					'status' => $current_status,
					'tier'   => $current_tier,
					's'      => $current_search,
				),
				admin_url( 'admin.php' )
			),
			'export_members'
		);

		?>
		<div class="wrap societypress-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Members', 'societypress' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-member-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New', 'societypress' ); ?>
			</a>
			<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">
				<?php esc_html_e( 'Export CSV', 'societypress' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notices(); ?>

			<form method="post" id="societypress-members-form">
				<input type="hidden" name="page" value="societypress-members">
				<input type="hidden" name="societypress_select_all" id="societypress_select_all" value="0">
				<!-- Pass current filters so bulk actions respect them -->
				<input type="hidden" name="societypress_filter_status" value="<?php echo esc_attr( $current_status ); ?>">
				<input type="hidden" name="societypress_filter_tier" value="<?php echo esc_attr( $current_tier ); ?>">
				<input type="hidden" name="societypress_filter_search" value="<?php echo esc_attr( $current_search ); ?>">
				<?php
				wp_nonce_field( 'bulk-members' );
				$this->members_table->search_box( __( 'Search Members', 'societypress' ), 'member' );
				?>

				<!-- Select All Banner (hidden by default, shown via JS) -->
				<?php if ( $total_members > $items_on_page ) : ?>
				<div id="societypress-select-all-banner" class="notice notice-info inline" style="display: none; margin: 10px 0;">
					<p>
						<span id="societypress-select-all-page-msg">
							<?php
							printf(
								/* translators: %d: number of items on current page */
								esc_html__( 'All %d items on this page are selected.', 'societypress' ),
								$items_on_page
							);
							?>
							<a href="#" id="societypress-select-all-link">
								<?php
								printf(
									/* translators: %d: total number of members matching current filters */
									esc_html__( 'Select all %d members', 'societypress' ),
									$total_members
								);
								?>
							</a>
						</span>
						<span id="societypress-all-selected-msg" style="display: none;">
							<?php
							printf(
								/* translators: %d: total number of members matching current filters */
								esc_html__( 'All %d members are selected.', 'societypress' ),
								$total_members
							);
							?>
							<a href="#" id="societypress-clear-selection-link">
								<?php esc_html_e( 'Clear selection', 'societypress' ); ?>
							</a>
						</span>
					</p>
				</div>
				<?php endif; ?>

				<?php $this->members_table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the member edit/add page.
	 */
	public function render_member_edit(): void {
		$member_id = isset( $_GET['member_id'] ) ? absint( $_GET['member_id'] ) : 0;
		$member    = null;
		$contact   = null;

		if ( $member_id ) {
			$member  = societypress()->members->get( $member_id );
			$contact = societypress()->members->get_contact( $member_id );
		}

		$tiers = societypress()->tiers->get_active();

		?>
		<div class="wrap societypress-admin">
			<h1>
				<?php
				echo $member_id
					? esc_html__( 'Edit Member', 'societypress' )
					: esc_html__( 'Add New Member', 'societypress' );
				?>
			</h1>

			<?php
			// Show "Edit WordPress User" link if member has linked user account
			if ( $member_id && $member && $member->user_id ) {
				$edit_user_url = admin_url( 'user-edit.php?user_id=' . $member->user_id );
				$user          = get_userdata( $member->user_id );
				if ( $user ) {
					echo '<p><a href="' . esc_url( $edit_user_url ) . '" class="button">' .
					     sprintf(
						     /* translators: %s: username */
						     esc_html__( 'Edit WordPress User: %s', 'societypress' ),
						     $user->user_login
					     ) .
					     '</a></p>';
				}
			}
			?>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-member-form">
				<?php wp_nonce_field( 'societypress_member', 'societypress_member_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="save_member">
				<input type="hidden" name="member_id" value="<?php echo esc_attr( $member_id ); ?>">

				<div class="societypress-form-sections">
					<!-- Basic Information -->
					<div class="societypress-form-section">
						<h2><?php esc_html_e( 'Basic Information', 'societypress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><label for="first_name"><?php esc_html_e( 'First Name', 'societypress' ); ?> *</label></th>
								<td>
									<input type="text" name="first_name" id="first_name" class="regular-text"
									       value="<?php echo esc_attr( $member->first_name ?? '' ); ?>" required>
								</td>
							</tr>
							<tr>
								<th><label for="middle_name"><?php esc_html_e( 'Middle Name', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="middle_name" id="middle_name" class="regular-text"
									       value="<?php echo esc_attr( $member->middle_name ?? '' ); ?>">
									<p class="description"><?php esc_html_e( 'Full middle name or initial (optional)', 'societypress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="last_name"><?php esc_html_e( 'Last Name', 'societypress' ); ?> *</label></th>
								<td>
									<input type="text" name="last_name" id="last_name" class="regular-text"
									       value="<?php echo esc_attr( $member->last_name ?? '' ); ?>" required>
								</td>
							</tr>
							<?php if ( self::get_setting( 'member_photos_enabled', true ) ) : ?>
							<tr>
								<th><label for="member_photo"><?php esc_html_e( 'Photo', 'societypress' ); ?></label></th>
								<td>
									<?php
									$photo_id  = $member->photo_id ?? 0;
									$photo_url = $photo_id ? wp_get_attachment_image_url( $photo_id, 'thumbnail' ) : '';
									?>
									<div class="sp-member-photo-wrapper">
										<div class="sp-member-photo-preview" id="sp-member-photo-preview" style="<?php echo $photo_url ? '' : 'display:none;'; ?>">
											<img src="<?php echo esc_url( $photo_url ); ?>" alt="" id="sp-member-photo-img">
											<button type="button" class="sp-member-photo-remove" id="sp-member-photo-remove" title="<?php esc_attr_e( 'Remove photo', 'societypress' ); ?>">&times;</button>
										</div>
										<input type="hidden" name="photo_id" id="photo_id" value="<?php echo esc_attr( $photo_id ); ?>">
										<button type="button" class="button" id="sp-member-photo-upload">
											<?php echo $photo_id ? esc_html__( 'Change Photo', 'societypress' ) : esc_html__( 'Upload Photo', 'societypress' ); ?>
										</button>
									</div>
									<p class="description"><?php esc_html_e( 'Square image recommended, max 1MB. Will display as circle.', 'societypress' ); ?></p>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<th><label for="birth_date"><?php esc_html_e( 'Birth Date', 'societypress' ); ?></label></th>
								<td>
									<input type="date" name="birth_date" id="birth_date"
									       value="<?php echo esc_attr( $member->birth_date ?? '' ); ?>">
									<p class="description"><?php esc_html_e( 'Optional - for birthday notifications and directory display', 'societypress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="membership_tier_id"><?php esc_html_e( 'Membership Tier', 'societypress' ); ?> *</label></th>
								<td>
									<select name="membership_tier_id" id="membership_tier_id" required>
										<?php foreach ( $tiers as $tier ) : ?>
											<option value="<?php echo esc_attr( $tier->id ); ?>"
												<?php selected( $member->membership_tier_id ?? '', $tier->id ); ?>>
												<?php echo esc_html( $tier->name ); ?> - $<?php echo esc_html( number_format( $tier->price, 2 ) ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="status"><?php esc_html_e( 'Status', 'societypress' ); ?></label></th>
								<td>
									<select name="status" id="status">
										<?php foreach ( SocietyPress_Members::STATUSES as $status ) : ?>
											<option value="<?php echo esc_attr( $status ); ?>"
												<?php selected( $member->status ?? 'pending', $status ); ?>>
												<?php echo esc_html( ucfirst( $status ) ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="join_date"><?php esc_html_e( 'Join Date', 'societypress' ); ?></label></th>
								<td>
									<input type="date" name="join_date" id="join_date"
									       value="<?php echo esc_attr( $member->join_date ?? gmdate( 'Y-m-d' ) ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="expiration_date"><?php esc_html_e( 'Expiration Date', 'societypress' ); ?></label></th>
								<td>
									<input type="date" name="expiration_date" id="expiration_date"
									       value="<?php echo esc_attr( $member->expiration_date ?? ( gmdate( 'Y' ) . '-12-31' ) ); ?>">
									<p class="description"><?php esc_html_e( 'Leave blank for lifetime memberships.', 'societypress' ); ?></p>
								</td>
							</tr>
						</table>
					</div>

					<!-- Contact Information -->
					<div class="societypress-form-section">
						<h2><?php esc_html_e( 'Contact Information', 'societypress' ); ?></h2>
						<table class="form-table">
							<?php
							// Check if member has a linked WordPress user
							$has_wp_user = false;
							$no_online_access = false;
							if ( ! empty( $member->id ) ) {
								$has_wp_user = (bool) societypress()->members->get_meta( $member->id, 'wp_user_id' );
								$no_online_access = (bool) societypress()->members->get_meta( $member->id, 'no_online_access' );
							}
							?>
							<tr>
								<th>
									<label for="primary_email">
										<?php esc_html_e( 'Email', 'societypress' ); ?>
										<span id="email-required-indicator"><?php echo $no_online_access ? '' : ' *'; ?></span>
									</label>
								</th>
								<td>
									<input type="email" name="primary_email" id="primary_email" class="regular-text"
									       value="<?php echo esc_attr( $contact->primary_email ?? '' ); ?>"
									       <?php echo $no_online_access ? '' : 'required'; ?>>
									<p class="description" id="email-optional-note" style="<?php echo $no_online_access ? '' : 'display:none;'; ?>">
										<?php esc_html_e( 'Email is optional for members without online access.', 'societypress' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Online Access', 'societypress' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="no_online_access" id="no_online_access" value="1"
										       <?php checked( $no_online_access ); ?>
										       <?php echo $has_wp_user ? 'disabled' : ''; ?>>
										<?php esc_html_e( 'No online access needed (skip WordPress account)', 'societypress' ); ?>
									</label>
									<?php if ( $has_wp_user ) : ?>
										<p class="description"><?php esc_html_e( 'This member already has a WordPress account.', 'societypress' ); ?></p>
									<?php else : ?>
										<p class="description"><?php esc_html_e( 'Check this for members who won\'t use the member portal (e.g., paper-only members).', 'societypress' ); ?></p>
									<?php endif; ?>
								</td>
							</tr>
							<?php if ( $member_id ) : ?>
							<tr>
								<th><label for="link_wp_user"><?php esc_html_e( 'WordPress Account', 'societypress' ); ?></label></th>
								<td>
									<?php
									// Get currently linked user ID from member record
									$linked_user_id = $member->user_id ?? 0;

									// Get all WordPress users for the dropdown
									$wp_users = get_users( array(
										'orderby' => 'display_name',
										'order'   => 'ASC',
										'fields'  => array( 'ID', 'display_name', 'user_email' ),
									) );
									?>
									<select name="link_wp_user" id="link_wp_user">
										<option value=""><?php esc_html_e( '— No WordPress account linked —', 'societypress' ); ?></option>
										<?php foreach ( $wp_users as $wp_user ) : ?>
											<option value="<?php echo esc_attr( $wp_user->ID ); ?>" <?php selected( $linked_user_id, $wp_user->ID ); ?>>
												<?php echo esc_html( $wp_user->display_name . ' (' . $wp_user->user_email . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">
										<?php esc_html_e( 'Link this member to an existing WordPress user account for portal access.', 'societypress' ); ?>
									</p>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<th><label for="cell_phone"><?php esc_html_e( 'Phone', 'societypress' ); ?></label></th>
								<td>
									<input type="tel" name="cell_phone" id="cell_phone" class="regular-text"
									       value="<?php echo esc_attr( $contact->cell_phone ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="street_address"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="street_address" id="street_address" class="large-text"
									       value="<?php echo esc_attr( $contact->street_address ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="address_line_2"><?php esc_html_e( 'Address Line 2', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="address_line_2" id="address_line_2" class="large-text"
									       value="<?php echo esc_attr( $contact->address_line_2 ?? '' ); ?>">
									<p class="description"><?php esc_html_e( 'Apartment, suite, unit, building, floor, etc.', 'societypress' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="city"><?php esc_html_e( 'City', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="city" id="city" class="regular-text"
									       value="<?php echo esc_attr( $contact->city ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="state_province"><?php esc_html_e( 'State/Province', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="state_province" id="state_province" class="regular-text"
									       value="<?php echo esc_attr( $contact->state_province ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="postal_code"><?php esc_html_e( 'Postal Code', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="postal_code" id="postal_code"
									       value="<?php echo esc_attr( $contact->postal_code ?? '' ); ?>">
								</td>
							</tr>
							<tr>
								<th><label for="country"><?php esc_html_e( 'Country', 'societypress' ); ?></label></th>
								<td>
									<input type="text" name="country" id="country" class="regular-text"
									       value="<?php echo esc_attr( $contact->country ?? 'USA' ); ?>">
								</td>
							</tr>
						</table>
					</div>

					<!-- Options -->
					<div class="societypress-form-section">
						<h2><?php esc_html_e( 'Options', 'societypress' ); ?></h2>
						<table class="form-table">
							<tr>
								<th><?php esc_html_e( 'Directory', 'societypress' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="directory_visible" value="1"
											<?php checked( $member->directory_visible ?? 1, 1 ); ?>>
										<?php esc_html_e( 'Show in member directory', 'societypress' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Auto-Renew', 'societypress' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="auto_renew" value="1"
											<?php checked( $member->auto_renew ?? 0, 1 ); ?>>
										<?php esc_html_e( 'Enable automatic renewal', 'societypress' ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th><label for="communication_preference"><?php esc_html_e( 'Communication Preference', 'societypress' ); ?></label></th>
								<td>
									<select name="communication_preference" id="communication_preference">
										<option value="email" <?php selected( $member->communication_preference ?? 'email', 'email' ); ?>>
											<?php esc_html_e( 'Email', 'societypress' ); ?>
										</option>
										<option value="mail" <?php selected( $member->communication_preference ?? '', 'mail' ); ?>>
											<?php esc_html_e( 'Postal Mail', 'societypress' ); ?>
										</option>
										<option value="both" <?php selected( $member->communication_preference ?? '', 'both' ); ?>>
											<?php esc_html_e( 'Both', 'societypress' ); ?>
										</option>
									</select>
								</td>
							</tr>
						</table>
					</div>

					<!-- Genealogy Services -->
					<?php
					// Get enabled genealogy services from settings
					$enabled_services = self::get_enabled_genealogy_services();

					// Only show section if at least one service is enabled
					if ( ! empty( $enabled_services ) ) :
						// Load genealogy service meta values for existing members
						$genealogy_meta = array();
						if ( $member_id ) {
							foreach ( array_keys( $enabled_services ) as $service_key ) {
								$genealogy_meta[ $service_key ] = societypress()->members->get_meta( $member_id, 'genealogy_' . $service_key ) ?? '';
							}
						}
					?>
					<div class="societypress-form-section">
						<h2><?php esc_html_e( 'Genealogy Services', 'societypress' ); ?></h2>
						<p class="description" style="margin-bottom: 15px;">
							<?php esc_html_e( 'Links to this member\'s profiles on genealogy research platforms. Enter profile IDs, usernames, or full URLs.', 'societypress' ); ?>
						</p>
						<table class="form-table">
							<?php foreach ( $enabled_services as $service_key => $service ) : ?>
							<tr>
								<th><label for="genealogy_<?php echo esc_attr( $service_key ); ?>"><?php echo esc_html( $service['label'] ); ?></label></th>
								<td>
									<input type="text" name="genealogy_<?php echo esc_attr( $service_key ); ?>"
									       id="genealogy_<?php echo esc_attr( $service_key ); ?>" class="regular-text"
									       value="<?php echo esc_attr( $genealogy_meta[ $service_key ] ?? '' ); ?>"
									       placeholder="<?php echo esc_attr( $service['placeholder'] ); ?>">
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
					<?php endif; ?>
				</div>

				<p class="submit">
					<input type="submit" name="societypress_save_member" class="button button-primary button-large"
					       value="<?php esc_attr_e( 'Save Member', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button button-large">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the tiers management page.
	 */
	public function render_tiers_page(): void {
		$tiers = societypress()->tiers->get_all();
		$counts = societypress()->tiers->get_member_counts();

		?>
		<div class="wrap societypress-admin">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Membership Tiers', 'societypress' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-tier-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New', 'societypress' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php $this->render_admin_notices(); ?>

			<table class="widefat striped societypress-tiers-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Price', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Duration', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Max Members', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
						<th><?php esc_html_e( 'Members', 'societypress' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $tiers as $tier ) :
						$edit_url = admin_url( 'admin.php?page=societypress-tier-edit&tier_id=' . $tier->id );
						$delete_url = wp_nonce_url(
							admin_url( 'admin.php?page=societypress-tiers&action=delete_tier&tier=' . $tier->id ),
							'delete_tier_' . $tier->id
						);
						$member_count = $counts[ $tier->id ] ?? 0;
						?>
						<tr>
							<td>
								<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $tier->name ); ?></a></strong>
								<br><code><?php echo esc_html( $tier->slug ); ?></code>
								<div class="row-actions">
									<span class="edit">
										<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'societypress' ); ?></a> |
									</span>
									<?php if ( 0 === $member_count ) : ?>
										<span class="delete">
											<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this tier?', 'societypress' ) ); ?>');">
												<?php esc_html_e( 'Delete', 'societypress' ); ?>
											</a>
										</span>
									<?php else : ?>
										<span class="delete" title="<?php esc_attr_e( 'Cannot delete tier with members', 'societypress' ); ?>">
											<?php esc_html_e( 'Delete', 'societypress' ); ?>
										</span>
									<?php endif; ?>
								</div>
							</td>
							<td>$<?php echo esc_html( number_format( $tier->price, 2 ) ); ?></td>
							<td>
								<?php
								if ( 0 === (int) $tier->duration_months ) {
									esc_html_e( 'Lifetime', 'societypress' );
								} else {
									printf(
										/* translators: %d: number of months */
										esc_html( _n( '%d month', '%d months', $tier->duration_months, 'societypress' ) ),
										esc_html( $tier->duration_months )
									);
								}
								?>
							</td>
							<td><?php echo esc_html( $tier->max_members ); ?></td>
							<td>
								<?php if ( $tier->is_active ) : ?>
									<span class="societypress-status-active"><?php esc_html_e( 'Active', 'societypress' ); ?></span>
								<?php else : ?>
									<span class="societypress-status-inactive"><?php esc_html_e( 'Inactive', 'societypress' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $member_count > 0 ) : ?>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members&tier=' . $tier->id ) ); ?>">
										<?php echo esc_html( $member_count ); ?>
									</a>
								<?php else : ?>
									0
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render the tier edit/add page.
	 */
	public function render_tier_edit(): void {
		$tier_id = isset( $_GET['tier_id'] ) ? absint( $_GET['tier_id'] ) : 0;
		$tier = null;

		if ( $tier_id ) {
			$tier = societypress()->tiers->get( $tier_id );
		}

		?>
		<div class="wrap societypress-admin">
			<h1>
				<?php
				echo $tier_id
					? esc_html__( 'Edit Tier', 'societypress' )
					: esc_html__( 'Add New Tier', 'societypress' );
				?>
			</h1>

			<?php $this->render_admin_notices(); ?>

			<form method="post" action="" class="societypress-tier-form">
				<?php wp_nonce_field( 'societypress_tier', 'societypress_tier_nonce' ); ?>
				<input type="hidden" name="societypress_action" value="save_tier">
				<input type="hidden" name="tier_id" value="<?php echo esc_attr( $tier_id ); ?>">

				<table class="form-table">
					<tr>
						<th><label for="name"><?php esc_html_e( 'Name', 'societypress' ); ?> *</label></th>
						<td>
							<input type="text" name="name" id="name" class="regular-text"
							       value="<?php echo esc_attr( $tier->name ?? '' ); ?>" required>
							<p class="description"><?php esc_html_e( 'Display name for this tier (e.g., "Individual", "Family").', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="slug"><?php esc_html_e( 'Slug', 'societypress' ); ?></label></th>
						<td>
							<input type="text" name="slug" id="slug" class="regular-text"
							       value="<?php echo esc_attr( $tier->slug ?? '' ); ?>">
							<p class="description"><?php esc_html_e( 'URL-friendly identifier. Leave blank to auto-generate from name.', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="description"><?php esc_html_e( 'Description', 'societypress' ); ?></label></th>
						<td>
							<textarea name="description" id="description" class="large-text" rows="3"><?php echo esc_textarea( $tier->description ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Shown to members during signup.', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="price"><?php esc_html_e( 'Price', 'societypress' ); ?> *</label></th>
						<td>
							<input type="number" name="price" id="price" step="0.01" min="0"
							       value="<?php echo esc_attr( $tier->price ?? '0.00' ); ?>" required>
							<p class="description"><?php esc_html_e( 'Annual membership fee in dollars.', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="duration_months"><?php esc_html_e( 'Duration (months)', 'societypress' ); ?></label></th>
						<td>
							<input type="number" name="duration_months" id="duration_months" min="0"
							       value="<?php echo esc_attr( $tier->duration_months ?? 12 ); ?>">
							<p class="description"><?php esc_html_e( 'Membership length in months. Enter 0 for lifetime memberships.', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="max_members"><?php esc_html_e( 'Max Members', 'societypress' ); ?></label></th>
						<td>
							<input type="number" name="max_members" id="max_members" min="1"
							       value="<?php echo esc_attr( $tier->max_members ?? 1 ); ?>">
							<p class="description"><?php esc_html_e( 'How many people this membership covers (e.g., 2 for family).', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="sort_order"><?php esc_html_e( 'Sort Order', 'societypress' ); ?></label></th>
						<td>
							<input type="number" name="sort_order" id="sort_order" min="0"
							       value="<?php echo esc_attr( $tier->sort_order ?? 0 ); ?>">
							<p class="description"><?php esc_html_e( 'Display order in lists. Lower numbers appear first.', 'societypress' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="is_active" value="1"
									<?php checked( $tier->is_active ?? 1, 1 ); ?>>
								<?php esc_html_e( 'Active (available for new memberships)', 'societypress' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<input type="submit" name="societypress_save_tier" class="button button-primary button-large"
					       value="<?php esc_attr_e( 'Save Tier', 'societypress' ); ?>">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-tiers' ) ); ?>" class="button button-large">
						<?php esc_html_e( 'Cancel', 'societypress' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Save tier from form submission.
	 */
	private function save_tier(): void {
		// Verify nonce
		if ( ! isset( $_POST['societypress_tier_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['societypress_tier_nonce'], 'societypress_tier' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			return;
		}

		$tier_id = isset( $_POST['tier_id'] ) ? absint( $_POST['tier_id'] ) : 0;
		$tiers = societypress()->tiers;

		// Prepare tier data
		$tier_data = array(
			'name'            => sanitize_text_field( $_POST['name'] ?? '' ),
			'slug'            => sanitize_title( $_POST['slug'] ?? $_POST['name'] ?? '' ),
			'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
			'price'           => floatval( $_POST['price'] ?? 0 ),
			'duration_months' => absint( $_POST['duration_months'] ?? 12 ),
			'max_members'     => absint( $_POST['max_members'] ?? 1 ),
			'sort_order'      => absint( $_POST['sort_order'] ?? 0 ),
			'is_active'       => isset( $_POST['is_active'] ) ? 1 : 0,
		);

		if ( $tier_id ) {
			// Update existing tier
			$result = $tiers->update( $tier_id, $tier_data );
			if ( $result ) {
				$this->add_admin_notice( __( 'Tier updated successfully.', 'societypress' ), 'success' );
			} else {
				$this->add_admin_notice( __( 'Error updating tier. Slug may already exist.', 'societypress' ), 'error' );
			}
		} else {
			// Create new tier
			$new_id = $tiers->create( $tier_data );
			if ( $new_id ) {
				$this->add_admin_notice( __( 'Tier created successfully.', 'societypress' ), 'success' );
				// Redirect to edit page for the new tier
				wp_safe_redirect( admin_url( 'admin.php?page=societypress-tier-edit&tier_id=' . $new_id . '&message=created' ) );
				exit;
			} else {
				$this->add_admin_notice( __( 'Error creating tier. Slug may already exist.', 'societypress' ), 'error' );
			}
		}
	}

	/**
	 * Delete a tier.
	 */
	private function delete_tier(): void {
		$tier_id = absint( $_GET['tier'] ?? 0 );

		// Verify nonce
		if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'delete_tier_' . $tier_id ) ) {
			wp_die( __( 'Security check failed.', 'societypress' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			wp_die( __( 'You do not have permission to delete tiers.', 'societypress' ) );
		}

		$tiers = societypress()->tiers;
		$result = $tiers->delete( $tier_id );

		if ( $result ) {
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-tiers&message=tier_deleted' ) );
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=societypress-tiers&message=tier_delete_error' ) );
		}
		exit;
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page(): void {
		// Check if settings were just saved
		if ( isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) {
			add_settings_error(
				'societypress_settings',
				'settings_saved',
				__( 'Settings saved.', 'societypress' ),
				'success'
			);
		}

		?>
		<div class="wrap societypress-admin">
			<h1><?php esc_html_e( 'SocietyPress Settings', 'societypress' ); ?></h1>

			<?php settings_errors( 'societypress_settings' ); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'societypress_settings_group' );
				do_settings_sections( 'societypress-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the license page.
	 */
	/**
	 * Render placeholder page for future features.
	 *
	 * WHY: Leadership, Library, and Committees features will be added later.
	 */
	public function render_placeholder_page(): void {
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$page_title = '';

		switch ( $current_page ) {
			case 'societypress-leadership':
				$page_title = __( 'Leadership', 'societypress' );
				break;
			case 'societypress-library':
				$page_title = __( 'Library', 'societypress' );
				break;
			case 'societypress-committees':
				$page_title = __( 'Committees', 'societypress' );
				break;
			default:
				$page_title = __( 'Coming Soon', 'societypress' );
		}

		?>
		<div class="wrap societypress-admin">
			<h1><?php echo esc_html( $page_title ); ?></h1>
			<div class="notice notice-info">
				<p><?php esc_html_e( 'This feature is coming soon. We\'ll add more functionality here later.', 'societypress' ); ?></p>
			</div>
		</div>
		<?php
	}

	public function render_license_page(): void {
		// The license class will handle the actual rendering
		if ( societypress()->license ) {
			societypress()->license->render_license_page();
		} else {
			?>
			<div class="wrap societypress-admin">
				<h1><?php esc_html_e( 'License', 'societypress' ); ?></h1>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'License manager not initialized.', 'societypress' ); ?></p>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Render the import page.
	 */
	public function render_import_page(): void {
		$this->import->render_page();
	}

	/**
	 * Render the event import page.
	 */
	public function render_import_events_page(): void {
		$this->import_events->render_import_page();
	}

	/**
	 * Save member from form submission.
	 */
	private function save_member(): void {
		// Verify nonce
		if ( ! isset( $_POST['societypress_member_nonce'] ) ||
		     ! wp_verify_nonce( $_POST['societypress_member_nonce'], 'societypress_member' ) ) {
			return;
		}

		// Check permissions
		if ( ! current_user_can( 'manage_society_members' ) ) {
			return;
		}

		$member_id = isset( $_POST['member_id'] ) ? absint( $_POST['member_id'] ) : 0;
		$members   = societypress()->members;

		// Prepare member data
		$member_data = array(
			'first_name'               => sanitize_text_field( $_POST['first_name'] ?? '' ),
			'middle_name'              => sanitize_text_field( $_POST['middle_name'] ?? '' ) ?: null,
			'last_name'                => sanitize_text_field( $_POST['last_name'] ?? '' ),
			'photo_id'                 => ! empty( $_POST['photo_id'] ) ? absint( $_POST['photo_id'] ) : null,
			'birth_date'               => sanitize_text_field( $_POST['birth_date'] ?? '' ) ?: null,
			'membership_tier_id'       => absint( $_POST['membership_tier_id'] ?? 0 ),
			'status'                   => sanitize_text_field( $_POST['status'] ?? 'pending' ),
			'join_date'                => sanitize_text_field( $_POST['join_date'] ?? '' ),
			'expiration_date'          => sanitize_text_field( $_POST['expiration_date'] ?? '' ) ?: null,
			'directory_visible'        => isset( $_POST['directory_visible'] ) ? 1 : 0,
			'auto_renew'               => isset( $_POST['auto_renew'] ) ? 1 : 0,
			'communication_preference' => sanitize_text_field( $_POST['communication_preference'] ?? 'email' ),
		);

		// Validate and prepare contact data
		// Email is optional if "no online access" is checked
		$no_online_access = ! empty( $_POST['no_online_access'] );
		$email = sanitize_email( $_POST['primary_email'] ?? '' );

		if ( ! $no_online_access && ( empty( $email ) || ! is_email( $email ) ) ) {
			wp_die(
				esc_html__( 'Please enter a valid email address.', 'societypress' ),
				esc_html__( 'Invalid Email', 'societypress' ),
				array( 'back_link' => true )
			);
		}

		// If email is provided, validate it's properly formatted
		if ( ! empty( $_POST['primary_email'] ) && ! is_email( $email ) ) {
			wp_die(
				esc_html__( 'The email address format is invalid.', 'societypress' ),
				esc_html__( 'Invalid Email', 'societypress' ),
				array( 'back_link' => true )
			);
		}

		$contact_data = array(
			'primary_email'   => $email,
			'cell_phone'      => $this->sanitize_phone( $_POST['cell_phone'] ?? '' ),
			'street_address'  => sanitize_text_field( $_POST['street_address'] ?? '' ),
			'address_line_2'  => sanitize_text_field( $_POST['address_line_2'] ?? '' ),
			'city'            => sanitize_text_field( $_POST['city'] ?? '' ),
			'state_province'  => sanitize_text_field( $_POST['state_province'] ?? '' ),
			'postal_code'     => sanitize_text_field( $_POST['postal_code'] ?? '' ),
			'country'         => sanitize_text_field( $_POST['country'] ?? 'USA' ),
		);

		if ( $member_id ) {
			// Update existing member
			$result = $members->update( $member_id, $member_data );
			if ( $result ) {
				$members->update_contact( $member_id, $contact_data );
				$this->save_genealogy_meta( $members, $member_id );

				// Save no_online_access preference
				$members->save_meta( $member_id, 'no_online_access', $no_online_access ? 1 : 0 );

				// Handle manual WordPress user linking from dropdown
				$user_manager = societypress()->user_manager;
				if ( isset( $_POST['link_wp_user'] ) ) {
					$selected_user_id = absint( $_POST['link_wp_user'] );
					$current_member   = $members->get( $member_id );
					$current_user_id  = $current_member->user_id ?? 0;

					// Only update if the selection changed
					if ( $selected_user_id !== $current_user_id ) {
						// Remove old user's member link if there was one
						if ( $current_user_id ) {
							delete_user_meta( $current_user_id, 'sp_member_id' );
						}

						if ( $selected_user_id ) {
							// Link to new user
							$user_manager->link_member_to_user( $member_id, $selected_user_id );
							update_user_meta( $selected_user_id, 'sp_member_id', $member_id );
						} else {
							// Unlink (set user_id to null)
							$user_manager->link_member_to_user( $member_id, 0 );
						}
					}
				} elseif ( ! $no_online_access && ! empty( $email ) && ! $user_manager->member_has_user( $member_id ) ) {
					// Auto-create/link WordPress user if not already linked and online access is needed
					$user_result = $user_manager->create_or_link_user(
						$member_id,
						$email,
						$member_data['first_name'],
						$member_data['last_name']
					);

					if ( is_wp_error( $user_result ) ) {
						$this->add_admin_notice(
							sprintf(
								/* translators: %s: error message */
								__( 'Member updated but user account creation failed: %s', 'societypress' ),
								$user_result->get_error_message()
							),
							'warning'
						);
					}
				}

				$this->add_admin_notice( __( 'Member updated successfully.', 'societypress' ), 'success' );
			} else {
				$this->add_admin_notice( __( 'Error updating member.', 'societypress' ), 'error' );
			}
		} else {
			// Create new member
			$new_id = $members->create( $member_data );
			if ( $new_id ) {
				$contact_data['member_id'] = $new_id;
				$members->update_contact( $new_id, $contact_data );
				$this->save_genealogy_meta( $members, $new_id );

				// Save no_online_access preference if set
				if ( $no_online_access ) {
					$members->save_meta( $new_id, 'no_online_access', 1 );
				}

				// Create or link WordPress user (unless online access is disabled or no email)
				if ( ! $no_online_access && ! empty( $email ) ) {
					$user_manager = societypress()->user_manager;
					$user_result  = $user_manager->create_or_link_user(
						$new_id,
						$email,
						$member_data['first_name'],
						$member_data['last_name']
					);

					if ( is_wp_error( $user_result ) ) {
						$this->add_admin_notice(
							sprintf(
								/* translators: %s: error message */
								__( 'Member created but user account creation failed: %s', 'societypress' ),
								$user_result->get_error_message()
							),
							'warning'
						);
					}
				}

				$this->add_admin_notice( __( 'Member created successfully.', 'societypress' ), 'success' );

				// Redirect to edit page for the new member
				wp_safe_redirect( admin_url( 'admin.php?page=societypress-member-edit&member_id=' . $new_id . '&message=created' ) );
				exit;
			} else {
				$this->add_admin_notice( __( 'Error creating member.', 'societypress' ), 'error' );
			}
		}
	}

	/**
	 * Save genealogy service meta fields for a member.
	 *
	 * Stores links/handles to external genealogy platforms (WikiTree, Ancestry, etc.)
	 * in the member_meta table. Empty values are still saved to allow clearing fields.
	 *
	 * @param SocietyPress_Members $members   The members handler instance.
	 * @param int                  $member_id The member ID to save meta for.
	 */
	private function save_genealogy_meta( SocietyPress_Members $members, int $member_id ): void {
		// Get the list of enabled genealogy services from settings
		// We only save values for services that are currently enabled
		$enabled_services = self::get_enabled_genealogy_services();

		foreach ( array_keys( $enabled_services ) as $service_key ) {
			$post_key = 'genealogy_' . $service_key;
			$meta_key = 'genealogy_' . $service_key;

			// Get the submitted value, defaulting to empty string if not set
			// We sanitize as text field since these are URLs, usernames, or IDs
			$value = isset( $_POST[ $post_key ] ) ? sanitize_text_field( $_POST[ $post_key ] ) : '';

			// Save the meta value (including empty strings to allow clearing)
			$members->save_meta( $member_id, $meta_key, $value );
		}
	}

	/**
	 * Add an admin notice to be displayed.
	 *
	 * @param string $message Notice message.
	 * @param string $type    Notice type (success, error, warning, info).
	 */
	private function add_admin_notice( string $message, string $type = 'info' ): void {
		set_transient(
			'societypress_admin_notice_' . get_current_user_id(),
			array(
				'message' => $message,
				'type'    => $type,
			),
			30
		);
	}

	/**
	 * Render any pending admin notices.
	 */
	private function render_admin_notices(): void {
		$notice = get_transient( 'societypress_admin_notice_' . get_current_user_id() );

		if ( $notice ) {
			delete_transient( 'societypress_admin_notice_' . get_current_user_id() );
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice['type'] ),
				esc_html( $notice['message'] )
			);
		}

		// Check for URL message parameter
		if ( isset( $_GET['message'] ) ) {
			$count = absint( $_GET['count'] ?? 0 );

			switch ( $_GET['message'] ) {
				case 'created':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     esc_html__( 'Member created successfully.', 'societypress' ) .
					     '</p></div>';
					break;

				case 'deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     esc_html__( 'Member deleted successfully.', 'societypress' ) .
					     '</p></div>';
					break;

				case 'delete_error':
					echo '<div class="notice notice-error is-dismissible"><p>' .
					     esc_html__( 'Error deleting member.', 'societypress' ) .
					     '</p></div>';
					break;

				case 'not_found':
					echo '<div class="notice notice-warning is-dismissible"><p>' .
					     esc_html__( 'Member not found.', 'societypress' ) .
					     '</p></div>';
					break;

				case 'bulk_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     sprintf(
						     /* translators: %d: number of members deleted */
						     esc_html( _n( '%d member deleted.', '%d members deleted.', $count, 'societypress' ) ),
						     $count
					     ) .
					     '</p></div>';
					break;

				case 'bulk_activated':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     sprintf(
						     /* translators: %d: number of members activated */
						     esc_html( _n( '%d member set to active.', '%d members set to active.', $count, 'societypress' ) ),
						     $count
					     ) .
					     '</p></div>';
					break;

				case 'bulk_deactivated':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     sprintf(
						     /* translators: %d: number of members deactivated */
						     esc_html( _n( '%d member set to expired.', '%d members set to expired.', $count, 'societypress' ) ),
						     $count
					     ) .
					     '</p></div>';
					break;

				case 'users_created':
					$created = isset( $_GET['created'] ) ? absint( $_GET['created'] ) : 0;
					$linked  = isset( $_GET['linked'] ) ? absint( $_GET['linked'] ) : 0;
					$skipped = isset( $_GET['skipped'] ) ? absint( $_GET['skipped'] ) : 0;

					$message_parts = array();
					if ( $created > 0 ) {
						$message_parts[] = sprintf(
							/* translators: %d: number of users created */
							_n( '%d user created', '%d users created', $created, 'societypress' ),
							$created
						);
					}
					if ( $linked > 0 ) {
						$message_parts[] = sprintf(
							/* translators: %d: number of users linked */
							_n( '%d linked to existing user', '%d linked to existing users', $linked, 'societypress' ),
							$linked
						);
					}
					if ( $skipped > 0 ) {
						$message_parts[] = sprintf(
							/* translators: %d: number of members skipped */
							_n( '%d already had account', '%d already had accounts', $skipped, 'societypress' ),
							$skipped
						);
					}

					if ( ! empty( $message_parts ) ) {
						echo '<div class="notice notice-success is-dismissible"><p>' .
						     esc_html( implode( ', ', $message_parts ) . '.' ) .
						     '</p></div>';
					}
					break;

				case 'all_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     sprintf(
						     /* translators: %d: number of members deleted */
						     esc_html__( 'All %d members have been deleted.', 'societypress' ),
						     $count
					     ) .
					     '</p></div>';
					break;

				case 'tier_deleted':
					echo '<div class="notice notice-success is-dismissible"><p>' .
					     esc_html__( 'Tier deleted successfully.', 'societypress' ) .
					     '</p></div>';
					break;

				case 'tier_delete_error':
					echo '<div class="notice notice-error is-dismissible"><p>' .
					     esc_html__( 'Cannot delete tier. It may have members assigned.', 'societypress' ) .
					     '</p></div>';
					break;
			}
		}
	}

	/**
	 * Sanitize phone number by stripping all non-digits.
	 *
	 * Takes formatted phone like "(210) 913-3458" and returns "2109133458".
	 * This ensures only digits are stored in the database.
	 *
	 * @since 0.21d
	 *
	 * @param string $phone Phone number (formatted or unformatted).
	 * @return string Phone number with only digits.
	 */
	private function sanitize_phone( string $phone ): string {
		return preg_replace( '/\D/', '', $phone );
	}
}
