<?php
/**
 * Member Import Handler
 *
 * Handles CSV import with field mapping for migrating members
 * from other systems (Wild Apricot, spreadsheets, etc.).
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class SocietyPress_Import
 *
 * Manages the member import process including CSV parsing,
 * field mapping, validation, and batch processing.
 */
class SocietyPress_Import {

    /**
     * Option name for saved field mappings.
     */
    private const MAPPING_OPTION = 'societypress_import_mapping';

    /**
     * SocietyPress fields available for mapping (static fields only).
     *
     * Genealogy fields are added dynamically based on enabled services.
     * Organized by category for easier UI presentation.
     *
     * @var array
     */
    private array $base_destination_fields = array(
        'basic' => array(
            'full_name'          => 'Full Name (Last, First)',
            'first_name'         => 'First Name',
            'last_name'          => 'Last Name',
            'organization'       => 'Organization/Company',
            'membership_tier'    => 'Membership Tier',
            'status'             => 'Status',
            'join_date'          => 'Join Date',
            'expiration_date'    => 'Expiration Date',
            'birth_date'         => 'Birth Date',
            'directory_visible'  => 'Show in Directory',
            'auto_renew'         => 'Auto Renew',
            'communication_pref' => 'Communication Preference',
        ),
        'contact' => array(
            'primary_email'      => 'Primary Email',
            'secondary_email'    => 'Secondary Email',
            'home_phone'         => 'Home Phone',
            'cell_phone'         => 'Cell Phone',
            'work_phone'         => 'Work Phone',
            'street_address'     => 'Street Address',
            'address_line_2'     => 'Address Line 2',
            'city'               => 'City',
            'state_province'     => 'State/Province',
            'postal_code'        => 'Postal Code',
            'country'            => 'Country',
        ),
        'research' => array(
            'surnames'           => 'Surnames Researched',
            'research_areas'     => 'Research Areas',
        ),
        'other' => array(
            'how_heard'          => 'How Heard About Us',
            'notes'              => 'Notes',
            'user_id'            => 'WordPress User ID',
        ),
    );

    /**
     * Build complete destination fields including enabled genealogy services.
     *
     * @return array Complete field definitions with dynamic genealogy section.
     */
    private function get_complete_destination_fields(): array {
        $fields = $this->base_destination_fields;

        // Build genealogy section from enabled services in settings
        $enabled_services = SocietyPress_Admin::get_enabled_genealogy_services();
        $genealogy_fields = array();

        foreach ( $enabled_services as $key => $service ) {
            $genealogy_fields[ 'genealogy_' . $key ] = $service['label'];
        }

        // Insert genealogy section before 'other' if there are enabled services
        if ( ! empty( $genealogy_fields ) ) {
            // Rebuild array to insert genealogy before 'other'
            $result = array();
            foreach ( $fields as $group => $group_fields ) {
                if ( 'other' === $group ) {
                    $result['genealogy'] = $genealogy_fields;
                }
                $result[ $group ] = $group_fields;
            }
            $fields = $result;
        }

        return $fields;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_societypress_upload_csv', array( $this, 'ajax_upload_csv' ) );
        add_action( 'wp_ajax_societypress_preview_import', array( $this, 'ajax_preview_import' ) );
        add_action( 'wp_ajax_societypress_run_import', array( $this, 'ajax_run_import' ) );
        add_action( 'wp_ajax_societypress_save_mapping', array( $this, 'ajax_save_mapping' ) );
        add_action( 'wp_ajax_societypress_commit_problem_rows', array( $this, 'ajax_commit_problem_rows' ) );
    }

    /**
     * Get all destination fields flattened.
     *
     * @return array Field key => label pairs.
     */
    public function get_destination_fields(): array {
        $flat = array( '' => '— Do not import —' );

        foreach ( $this->get_complete_destination_fields() as $group => $fields ) {
            foreach ( $fields as $key => $label ) {
                $flat[ $key ] = $label;
            }
        }

        return $flat;
    }

    /**
     * Get destination fields grouped by category.
     *
     * @return array Grouped fields for optgroup display.
     */
    public function get_grouped_destination_fields(): array {
        return $this->get_complete_destination_fields();
    }

    /**
     * Get saved field mappings.
     *
     * @return array Saved mappings.
     */
    public function get_saved_mapping(): array {
        return get_option( self::MAPPING_OPTION, array() );
    }

    /**
     * Save field mapping.
     *
     * @param array $mapping Field mapping array.
     * @return bool Success.
     */
    public function save_mapping( array $mapping ): bool {
        return update_option( self::MAPPING_OPTION, $mapping, false );
    }

    /**
     * Parse CSV file and return headers and sample rows.
     *
     * @param string $file_path Path to CSV file.
     * @param int    $sample_rows Number of sample rows to return.
     * @return array|WP_Error Parsed data or error.
     */
    public function parse_csv( string $file_path, int $sample_rows = 5 ) {
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'CSV file not found.', 'societypress' ) );
        }

        $handle = fopen( $file_path, 'r' );
        if ( false === $handle ) {
            return new WP_Error( 'file_open_error', __( 'Could not open CSV file.', 'societypress' ) );
        }

        // Detect delimiter (comma, semicolon, or tab)
        $first_line = fgets( $handle );
        rewind( $handle );

        $delimiter = ',';
        if ( substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ) {
            $delimiter = ';';
        } elseif ( substr_count( $first_line, "\t" ) > substr_count( $first_line, ',' ) ) {
            $delimiter = "\t";
        }

        // Read headers
        $headers = fgetcsv( $handle, 0, $delimiter );
        if ( false === $headers ) {
            fclose( $handle );
            return new WP_Error( 'no_headers', __( 'Could not read CSV headers.', 'societypress' ) );
        }

        // Clean headers (remove BOM, trim whitespace)
        $headers = array_map( function( $header ) {
            // Remove UTF-8 BOM if present
            $header = preg_replace( '/^\xEF\xBB\xBF/', '', $header );
            return trim( $header );
        }, $headers );

        // Read sample rows
        $rows = array();
        $row_count = 0;
        $total_rows = 0;

        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $total_rows++;
            if ( $row_count < $sample_rows ) {
                // Pad row to match header count
                while ( count( $row ) < count( $headers ) ) {
                    $row[] = '';
                }
                $rows[] = array_slice( $row, 0, count( $headers ) );
                $row_count++;
            }
        }

        fclose( $handle );

        return array(
            'headers'    => $headers,
            'rows'       => $rows,
            'total_rows' => $total_rows,
            'delimiter'  => $delimiter,
        );
    }

    /**
     * Auto-detect field mappings based on column headers.
     *
     * Attempts to match common column names to SocietyPress fields.
     *
     * @param array $headers CSV column headers.
     * @return array Suggested mappings (header index => field key).
     */
    public function auto_detect_mapping( array $headers ): array {
        $mapping = array();

        // Common variations of field names
        $patterns = array(
            'full_name' => array(
                'name', 'full name', 'fullname', 'member name', 'member',
            ),
            'first_name' => array(
                'first name', 'firstname', 'first', 'given name', 'givenname',
            ),
            'last_name' => array(
                'last name', 'lastname', 'last', 'surname', 'family name',
            ),
            'organization' => array(
                'organization', 'company', 'org', 'business', 'institution', 'employer',
            ),
            'primary_email' => array(
                'email', 'e-mail', 'email address', 'primary email', 'member email',
            ),
            'secondary_email' => array(
                'secondary email', 'alternate email', 'other email', 'email 2',
            ),
            'membership_tier' => array(
                'membership level', 'level', 'tier', 'membership tier', 'membership type', 'type', 'member level',
            ),
            'status' => array(
                'status', 'member status', 'membership status', 'active', 'membership enabled',
            ),
            'join_date' => array(
                'member since', 'join date', 'joined', 'start date', 'membership start', 'join', 'since', 'created on',
            ),
            'expiration_date' => array(
                'renewal due', 'expiration', 'expires', 'expiry', 'renewal date', 'end date', 'renewal', 'due date',
            ),
            'birth_date' => array(
                'birthday', 'birth date', 'date of birth', 'dob', 'birthdate',
            ),
            'home_phone' => array(
                'home phone', 'phone (home)', 'home tel', 'landline',
            ),
            'cell_phone' => array(
                'cell phone', 'mobile', 'mobile phone', 'cell', 'phone (cell)', 'phone (mobile)', 'phone', 'telephone', 'tel',
            ),
            'work_phone' => array(
                'work phone', 'phone (work)', 'business phone', 'office phone',
            ),
            'street_address' => array(
                'address', 'street', 'street address', 'address 1', 'address line 1', 'mailing address',
            ),
            'address_line_2' => array(
                'address 2', 'address line 2', 'apt', 'suite', 'unit',
            ),
            'city' => array(
                'city', 'town',
            ),
            'state_province' => array(
                'state', 'province', 'state/province', 'region',
            ),
            'postal_code' => array(
                'zip', 'zip code', 'postal code', 'postcode', 'post code',
            ),
            'country' => array(
                'country', 'nation',
            ),
            'surnames' => array(
                'surnames', 'surnames researched', 'research surnames', 'family names',
            ),
            'research_areas' => array(
                'research areas', 'areas', 'locations', 'research locations', 'counties',
            ),
            'how_heard' => array(
                'how heard', 'how did you hear', 'referral', 'source',
            ),
            'notes' => array(
                'notes', 'comments', 'memo', 'additional info',
            ),
            'directory_visible' => array(
                'directory', 'show in directory', 'public', 'visible',
            ),
        );

        // Add auto-detection patterns for enabled genealogy services only
        // Each service has common column name variations that might appear in imports
        $genealogy_patterns = array(
            'wikitree' => array(
                'wikitree', 'wiki tree', 'wikitree id', 'wikitree profile',
            ),
            'ancestry' => array(
                'ancestry', 'ancestry.com', 'ancestry profile', 'ancestry username',
            ),
            'familysearch' => array(
                'familysearch', 'family search', 'familysearch id', 'fs id', 'fspid',
            ),
            'geni' => array(
                'geni', 'geni.com', 'geni profile',
            ),
            'werelate' => array(
                'werelate', 'we relate', 'werelate profile',
            ),
            'findagrave' => array(
                'find a grave', 'findagrave', 'find-a-grave', 'memorial id', 'grave',
            ),
            'myheritage' => array(
                'myheritage', 'my heritage', 'myheritage profile',
            ),
            '23andme' => array(
                '23andme', '23 and me', '23&me', 'dna profile',
            ),
        );

        // Only add patterns for services that are currently enabled
        $enabled_services = SocietyPress_Admin::get_enabled_genealogy_services();
        foreach ( array_keys( $enabled_services ) as $service_key ) {
            if ( isset( $genealogy_patterns[ $service_key ] ) ) {
                $patterns[ 'genealogy_' . $service_key ] = $genealogy_patterns[ $service_key ];
            }
        }

        foreach ( $headers as $index => $header ) {
            $header_lower = strtolower( trim( $header ) );

            foreach ( $patterns as $field => $variations ) {
                if ( in_array( $header_lower, $variations, true ) ) {
                    $mapping[ $index ] = $field;
                    break;
                }
            }
        }

        return $mapping;
    }

    /**
     * Handle CSV file upload via AJAX.
     */
    public function ajax_upload_csv(): void {
        check_ajax_referer( 'societypress_import', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        if ( empty( $_FILES['csv_file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'societypress' ) ) );
        }

        $file = $_FILES['csv_file'];

        // Validate file type
        $allowed_types = array( 'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel' );
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );

        // Also check extension
        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $mime_type, $allowed_types, true ) && 'csv' !== $ext ) {
            wp_send_json_error( array( 'message' => __( 'Invalid file type. Please upload a CSV file.', 'societypress' ) ) );
        }

        // Move to uploads directory
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/societypress-imports';

        if ( ! file_exists( $import_dir ) ) {
            wp_mkdir_p( $import_dir );
            // Protect directory
            file_put_contents( $import_dir . '/.htaccess', 'deny from all' );
            file_put_contents( $import_dir . '/index.php', '<?php // Silence is golden' );
        }

        $filename = 'import-' . wp_generate_uuid4() . '.csv';
        $filepath = $import_dir . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
            wp_send_json_error( array( 'message' => __( 'Could not save uploaded file.', 'societypress' ) ) );
        }

        // Parse the CSV
        $parsed = $this->parse_csv( $filepath, 10 );

        if ( is_wp_error( $parsed ) ) {
            unlink( $filepath );
            wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
        }

        // Auto-detect mappings
        $suggested_mapping = $this->auto_detect_mapping( $parsed['headers'] );

        // Get saved mapping if exists
        $saved_mapping = $this->get_saved_mapping();

        wp_send_json_success( array(
            'file'              => $filename,
            'headers'           => $parsed['headers'],
            'sample_rows'       => $parsed['rows'],
            'total_rows'        => $parsed['total_rows'],
            'suggested_mapping' => $suggested_mapping,
            'saved_mapping'     => $saved_mapping,
        ) );
    }

    /**
     * Preview import with current mapping via AJAX.
     */
    public function ajax_preview_import(): void {
        check_ajax_referer( 'societypress_import', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        $file = sanitize_file_name( $_POST['file'] ?? '' );
        $mapping = $_POST['mapping'] ?? array();

        if ( empty( $file ) || empty( $mapping ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing file or mapping.', 'societypress' ) ) );
        }

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/societypress-imports/' . $file;

        if ( ! file_exists( $filepath ) ) {
            wp_send_json_error( array( 'message' => __( 'Import file not found.', 'societypress' ) ) );
        }

        $parsed = $this->parse_csv( $filepath, 5 );

        if ( is_wp_error( $parsed ) ) {
            wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
        }

        // Transform sample rows using mapping
        $preview = array();
        foreach ( $parsed['rows'] as $row ) {
            $mapped_row = $this->map_row( $row, $parsed['headers'], $mapping );
            $preview[] = $mapped_row;
        }

        wp_send_json_success( array(
            'preview'    => $preview,
            'total_rows' => $parsed['total_rows'],
        ) );
    }

    /**
     * Run the actual import via AJAX.
     */
    public function ajax_run_import(): void {
        check_ajax_referer( 'societypress_import', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        $file = sanitize_file_name( $_POST['file'] ?? '' );
        $mapping = $_POST['mapping'] ?? array();
        $options = $_POST['options'] ?? array();

        if ( empty( $file ) || empty( $mapping ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing file or mapping.', 'societypress' ) ) );
        }

        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/societypress-imports/' . $file;

        if ( ! file_exists( $filepath ) ) {
            wp_send_json_error( array( 'message' => __( 'Import file not found.', 'societypress' ) ) );
        }

        // Run import
        $result = $this->process_import( $filepath, $mapping, $options );

        // Clean up file
        unlink( $filepath );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Save field mapping via AJAX.
     */
    public function ajax_save_mapping(): void {
        check_ajax_referer( 'societypress_import', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        $mapping = $_POST['mapping'] ?? array();

        if ( $this->save_mapping( $mapping ) ) {
            wp_send_json_success( array( 'message' => __( 'Mapping saved.', 'societypress' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Could not save mapping.', 'societypress' ) ) );
        }
    }

    /**
     * Commit fixed problem rows via AJAX.
     *
     * Takes user-edited problem row data and attempts to import each one.
     * Rows can be fixed (edited) or discarded by the user.
     */
    public function ajax_commit_problem_rows(): void {
        check_ajax_referer( 'societypress_import', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        $rows = $_POST['rows'] ?? array();
        $options = $_POST['options'] ?? array();

        if ( empty( $rows ) ) {
            wp_send_json_success( array(
                'imported' => 0,
                'skipped'  => 0,
                'errors'   => array(),
            ) );
        }

        $results = array(
            'imported' => 0,
            'skipped'  => 0,
            'errors'   => array(),
        );

        $members = societypress()->members;
        $tiers   = societypress()->tiers;
        $default_tier = absint( $options['default_tier'] ?? 0 );

        foreach ( $rows as $row ) {
            // Skip rows marked as discarded
            if ( ! empty( $row['discard'] ) ) {
                $results['skipped']++;
                continue;
            }

            $data = $row['data'] ?? array();
            $row_num = $row['row_num'] ?? 0;

            // Validate required fields
            if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: Still missing name.', 'societypress' ),
                    $row_num
                );
                $results['skipped']++;
                continue;
            }

            // Resolve membership tier
            $tier_id = $default_tier;
            if ( ! empty( $data['membership_tier'] ) ) {
                $tier = $tiers->get_by_slug( sanitize_title( $data['membership_tier'] ) );
                if ( ! $tier ) {
                    $all_tiers = $tiers->get_all();
                    foreach ( $all_tiers as $t ) {
                        if ( strtolower( $t->name ) === strtolower( $data['membership_tier'] ) ) {
                            $tier = $t;
                            break;
                        }
                    }
                }
                if ( $tier ) {
                    $tier_id = $tier->id;
                }
            }

            if ( ! $tier_id ) {
                $active_tiers = $tiers->get_active();
                $tier_id = $active_tiers[0]->id ?? 0;
            }

            if ( ! $tier_id ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: Still no valid membership tier.', 'societypress' ),
                    $row_num
                );
                $results['skipped']++;
                continue;
            }

            // Create member
            $member_data = array(
                'first_name'               => sanitize_text_field( $data['first_name'] ?? '' ),
                'last_name'                => sanitize_text_field( $data['last_name'] ?? '' ),
                'membership_tier_id'       => $tier_id,
                'status'                   => ! empty( $data['status'] ) ? $data['status'] : 'active',
                'join_date'                => ! empty( $data['join_date'] ) ? $data['join_date'] : gmdate( 'Y-m-d' ),
                'expiration_date'          => ! empty( $data['expiration_date'] ) ? $data['expiration_date'] : null,
                'birth_date'               => ! empty( $data['birth_date'] ) ? $data['birth_date'] : null,
                'directory_visible'        => $data['directory_visible'] ?? 1,
                'auto_renew'               => $data['auto_renew'] ?? 0,
                'communication_preference' => $data['communication_pref'] ?? 'email',
                'how_heard_about_us'       => ! empty( $data['how_heard'] ) ? $data['how_heard'] : null,
            );

            $member_id = $members->create( $member_data );

            if ( ! $member_id ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: Could not create member.', 'societypress' ),
                    $row_num
                );
                $results['skipped']++;
                continue;
            }

            // Validate email addresses
            $primary_email = sanitize_email( $data['primary_email'] ?? '' );
            $secondary_email = sanitize_email( $data['secondary_email'] ?? '' );

            // Primary email is required and must be valid
            if ( empty( $primary_email ) || ! is_email( $primary_email ) ) {
                $this->errors[] = sprintf(
                    /* translators: %d: row number, %s: email value */
                    __( 'Row %d: Invalid or missing primary email address (%s). Member skipped.', 'societypress' ),
                    $row_num,
                    $primary_email
                );
                continue;
            }

            // Secondary email is optional but must be valid if provided
            if ( ! empty( $secondary_email ) && ! is_email( $secondary_email ) ) {
                $secondary_email = ''; // Clear invalid secondary email but continue import
            }

            // Add contact info
            $contact_data = array(
                'member_id'      => $member_id,
                'primary_email'  => $primary_email,
                'secondary_email'=> $secondary_email,
                'home_phone'     => sanitize_text_field( $data['home_phone'] ?? '' ),
                'cell_phone'     => sanitize_text_field( $data['cell_phone'] ?? '' ),
                'work_phone'     => sanitize_text_field( $data['work_phone'] ?? '' ),
                'street_address' => sanitize_text_field( $data['street_address'] ?? '' ),
                'address_line_2' => sanitize_text_field( $data['address_line_2'] ?? '' ),
                'city'           => sanitize_text_field( $data['city'] ?? '' ),
                'state_province' => sanitize_text_field( $data['state_province'] ?? '' ),
                'postal_code'    => sanitize_text_field( $data['postal_code'] ?? '' ),
                'country'        => sanitize_text_field( $data['country'] ?? 'USA' ),
            );

            $members->update_contact( $member_id, $contact_data );

            // Create or link WordPress user account
            $user_manager = societypress()->user_manager;
            $user_result  = $user_manager->create_or_link_user(
                $member_id,
                $primary_email,
                $member_data['first_name'],
                $member_data['last_name']
            );

            if ( is_wp_error( $user_result ) ) {
                // Log error but don't stop import
                $results['errors'][] = sprintf(
                    /* translators: %1$d: row number, %2$s: error message */
                    __( 'Row %1$d: Member imported but user account creation failed: %2$s', 'societypress' ),
                    $row_num,
                    $user_result->get_error_message()
                );
            }

            // Save organization if provided
            if ( ! empty( $data['organization'] ) ) {
                $members->update_meta( $member_id, 'organization', sanitize_text_field( $data['organization'] ) );
            }

            $results['imported']++;
        }

        wp_send_json_success( $results );
    }

    /**
     * Map a CSV row to SocietyPress fields.
     *
     * @param array $row      CSV row data.
     * @param array $headers  CSV headers.
     * @param array $mapping  Field mapping (header => field).
     * @return array Mapped data.
     */
    private function map_row( array $row, array $headers, array $mapping ): array {
        $mapped = array();

        foreach ( $mapping as $header_index => $field ) {
            if ( empty( $field ) || ! isset( $row[ $header_index ] ) ) {
                continue;
            }

            $value = trim( $row[ $header_index ] );

            // Transform value based on field type
            $value = $this->transform_value( $field, $value );

            $mapped[ $field ] = $value;
        }

        return $mapped;
    }

    /**
     * Transform a value based on field type.
     *
     * @param string $field Field key.
     * @param string $value Raw value.
     * @return mixed Transformed value.
     */
    private function transform_value( string $field, string $value ) {
        if ( '' === $value ) {
            return $value;
        }

        switch ( $field ) {
            // Date fields
            case 'join_date':
            case 'expiration_date':
            case 'birth_date':
                return $this->parse_date( $value );

            // Boolean fields
            case 'directory_visible':
            case 'auto_renew':
                return $this->parse_boolean( $value );

            // Status field
            case 'status':
                return $this->normalize_status( $value );

            // Email fields
            case 'primary_email':
            case 'secondary_email':
                return sanitize_email( $value );

            // Phone fields
            case 'home_phone':
            case 'cell_phone':
            case 'work_phone':
                return $this->format_phone( $value );

            // Multi-value fields (comma or semicolon separated)
            case 'surnames':
            case 'research_areas':
                return $value; // Keep as-is, will be split during import

            // Full name - keep as-is, will be parsed during import
            case 'full_name':
                return sanitize_text_field( $value );

            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Parse a full name in "Last, First [Nickname] Middle" format.
     *
     * Handles various formats commonly found in membership exports:
     * - "Smith, John" → first: John, last: Smith
     * - "Smith, John William" → first: John, last: Smith (middle ignored for now)
     * - "Smith, John [Johnny]" → first: John, last: Smith, nickname: Johnny
     * - "Smith, John [Johnny] William" → first: John, last: Smith, nickname: Johnny
     * - "Acme Library" (no comma) → first: Acme Library, last: (empty) - organization
     *
     * @param string $full_name The full name string to parse.
     * @return array Associative array with 'first_name', 'last_name', and optionally 'nickname'.
     */
    private function parse_full_name( string $full_name ): array {
        $result = array(
            'first_name' => '',
            'last_name'  => '',
            'nickname'   => '',
        );

        $full_name = trim( $full_name );
        if ( empty( $full_name ) ) {
            return $result;
        }

        // Check if there's a comma (Last, First format)
        if ( strpos( $full_name, ',' ) !== false ) {
            // Split on first comma only
            $parts = explode( ',', $full_name, 2 );
            $result['last_name'] = trim( $parts[0] );
            $remainder = isset( $parts[1] ) ? trim( $parts[1] ) : '';

            // Extract nickname if present [in brackets]
            if ( preg_match( '/\[([^\]]+)\]/', $remainder, $matches ) ) {
                $result['nickname'] = trim( $matches[1] );
                // Remove the bracketed nickname from remainder
                $remainder = preg_replace( '/\s*\[[^\]]+\]\s*/', ' ', $remainder );
                $remainder = trim( $remainder );
            }

            // What's left is first name (and possibly middle, which we'll include)
            // Just take the first word as first name if there are multiple
            $name_parts = preg_split( '/\s+/', $remainder );
            if ( ! empty( $name_parts ) ) {
                $result['first_name'] = $name_parts[0];
                // If there's more, append it (could be middle name or suffix)
                // For simplicity, we'll just use the first part as first name
            }
        } else {
            // No comma - likely an organization name or "First Last" format
            // Check for "First Last" by seeing if there are exactly 2 words
            $parts = preg_split( '/\s+/', $full_name );
            if ( count( $parts ) === 2 ) {
                // Assume "First Last" format
                $result['first_name'] = $parts[0];
                $result['last_name'] = $parts[1];
            } else {
                // Treat as organization or single name - put in first_name
                $result['first_name'] = $full_name;
            }
        }

        return $result;
    }

    /**
     * Parse various date formats.
     *
     * Handles common date formats from membership systems including:
     * - ISO 8601 with and without time (Wild Apricot: 2024-01-15T00:00:00)
     * - US format (01/15/2024)
     * - European format (15/01/2024)
     * - Human readable (Jan 15, 2024 or January 15, 2024)
     *
     * @param string $value Date string.
     * @return string Date in Y-m-d format or empty.
     */
    private function parse_date( string $value ): string {
        $value = trim( $value );
        if ( empty( $value ) ) {
            return '';
        }

        // Strip time component from ISO 8601 format (2024-01-15T00:00:00 -> 2024-01-15)
        // This handles Wild Apricot's datetime format
        if ( preg_match( '/^(\d{4}-\d{2}-\d{2})T/', $value, $matches ) ) {
            $value = $matches[1];
        }

        // Try common formats (order matters - more specific first)
        $formats = array(
            'Y-m-d',          // ISO standard: 2024-01-15
            'Y/m/d',          // Alternate ISO: 2024/01/15
            'm/d/Y',          // US format: 01/15/2024
            'm-d-Y',          // US with dashes: 01-15-2024
            'd/m/Y',          // European: 15/01/2024
            'd-m-Y',          // European with dashes: 15-01-2024
            'M d, Y',         // Short month: Jan 15, 2024
            'M d Y',          // Short month no comma: Jan 15 2024
            'F d, Y',         // Full month: January 15, 2024
            'F d Y',          // Full month no comma: January 15 2024
            'd M Y',          // European text: 15 Jan 2024
            'd F Y',          // European full text: 15 January 2024
            'n/j/Y',          // US no leading zeros: 1/5/2024
            'j/n/Y',          // European no leading zeros: 5/1/2024
        );

        foreach ( $formats as $format ) {
            $date = DateTime::createFromFormat( $format, $value );
            // Check for valid date (DateTime can create invalid dates like Feb 30)
            if ( $date && $date->format( $format ) === $value ) {
                return $date->format( 'Y-m-d' );
            }
        }

        // Fallback: let PHP try to parse it
        // strtotime is very flexible with formats
        $timestamp = strtotime( $value );
        if ( $timestamp && $timestamp > 0 ) {
            return gmdate( 'Y-m-d', $timestamp );
        }

        return '';
    }

    /**
     * Parse boolean values.
     *
     * @param string $value Boolean-ish string.
     * @return int 1 or 0.
     */
    private function parse_boolean( string $value ): int {
        $true_values = array( 'yes', 'y', 'true', '1', 'on', 'checked', 'x' );
        return in_array( strtolower( trim( $value ) ), $true_values, true ) ? 1 : 0;
    }

    /**
     * Normalize status values.
     *
     * Handles status values from various membership systems including Wild Apricot,
     * which uses statuses like "Active", "Lapsed", "PendingNew", "PendingRenewal".
     *
     * @param string $value Status string.
     * @return string Valid status (one of: active, expired, pending, cancelled, deceased).
     */
    private function normalize_status( string $value ): string {
        $value = strtolower( trim( $value ) );

        // All keys must be lowercase since we lowercase the input
        $status_map = array(
            // Active variations
            'active'          => 'active',
            'current'         => 'active',
            'enabled'         => 'active',
            'yes'             => 'active',
            'true'            => 'active',
            '1'               => 'active',
            // Expired/lapsed variations
            'lapsed'          => 'expired',
            'expired'         => 'expired',
            'overdue'         => 'expired',
            'past due'        => 'expired',
            // Pending variations (Wild Apricot uses PendingNew, PendingRenewal)
            'pending'         => 'pending',
            'pendingnew'      => 'pending',
            'pending new'     => 'pending',
            'pendingrenewal'  => 'pending',
            'pending renewal' => 'pending',
            'pendinglevel'    => 'pending',
            'pending level'   => 'pending',
            'awaiting'        => 'pending',
            // Cancelled variations
            'cancelled'       => 'cancelled',
            'canceled'        => 'cancelled',
            'suspended'       => 'cancelled',
            'inactive'        => 'cancelled',
            'no'              => 'cancelled',
            'false'           => 'cancelled',
            '0'               => 'cancelled',
            // Deceased variations
            'deceased'        => 'deceased',
            'dead'            => 'deceased',
        );

        return $status_map[ $value ] ?? 'pending';
    }

    /**
     * Format phone number.
     *
     * @param string $value Phone number.
     * @return string Formatted phone.
     */
    private function format_phone( string $value ): string {
        // Remove everything except digits
        $digits = preg_replace( '/[^0-9]/', '', $value );

        // Format US numbers
        if ( 10 === strlen( $digits ) ) {
            return sprintf( '(%s) %s-%s',
                substr( $digits, 0, 3 ),
                substr( $digits, 3, 3 ),
                substr( $digits, 6, 4 )
            );
        }

        // Return as-is if not standard US format
        return $value;
    }

    /**
     * Process the full import.
     *
     * @param string $filepath CSV file path.
     * @param array  $mapping  Field mapping.
     * @param array  $options  Import options.
     * @return array|WP_Error Results or error.
     */
    private function process_import( string $filepath, array $mapping, array $options = array() ) {
        $handle = fopen( $filepath, 'r' );
        if ( false === $handle ) {
            return new WP_Error( 'file_error', __( 'Could not open file.', 'societypress' ) );
        }

        // Detect delimiter
        $first_line = fgets( $handle );
        rewind( $handle );

        $delimiter = ',';
        if ( substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ) {
            $delimiter = ';';
        } elseif ( substr_count( $first_line, "\t" ) > substr_count( $first_line, ',' ) ) {
            $delimiter = "\t";
        }

        // Read headers
        $headers = fgetcsv( $handle, 0, $delimiter );
        $headers = array_map( 'trim', $headers );

        $results = array(
            'imported'     => 0,
            'updated'      => 0,
            'skipped'      => 0,
            'errors'       => array(),
            'problem_rows' => array(),  // Rows that need user review/fixing
        );

        $skip_duplicates = ! empty( $options['skip_duplicates'] );
        $update_existing = ! empty( $options['update_existing'] );
        $default_tier    = absint( $options['default_tier'] ?? 0 );

        $members = societypress()->members;
        $tiers   = societypress()->tiers;
        $row_num = 1;

        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $row_num++;

            // Pad row to match headers
            while ( count( $row ) < count( $headers ) ) {
                $row[] = '';
            }

            // Map the row
            $data = $this->map_row( $row, $headers, $mapping );

            // If full_name is provided, parse it into first_name and last_name
            // This handles "Last, First [Nickname] Middle" format common in exports
            if ( ! empty( $data['full_name'] ) ) {
                $parsed = $this->parse_full_name( $data['full_name'] );

                // Only use parsed values if we don't already have explicit first/last names
                if ( empty( $data['first_name'] ) ) {
                    $data['first_name'] = $parsed['first_name'];
                }
                if ( empty( $data['last_name'] ) ) {
                    $data['last_name'] = $parsed['last_name'];
                }

                // Store nickname as meta if parsed
                if ( ! empty( $parsed['nickname'] ) ) {
                    $data['_nickname'] = $parsed['nickname'];
                }
            }

            // Validate required fields - members->create() requires BOTH first AND last name
            // For organizations or single names, we need to handle specially
            if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) ) {
                // If we have one but not the other, try to handle gracefully
                if ( ! empty( $data['first_name'] ) && empty( $data['last_name'] ) ) {
                    // Could be an organization name or single name - use as last name
                    // This handles "Acme Library" type entries where no comma in name
                    $data['last_name'] = $data['first_name'];
                    $data['first_name'] = '(Organization)';
                } elseif ( empty( $data['first_name'] ) && ! empty( $data['last_name'] ) ) {
                    // Has last name only - set placeholder first name
                    $data['first_name'] = '(Unknown)';
                } else {
                    // Both empty - add to problem rows for user review
                    $results['problem_rows'][] = array(
                        'row_num' => $row_num,
                        'issue'   => __( 'Missing name', 'societypress' ),
                        'data'    => $data,
                        'raw_row' => $row,
                    );
                    $results['skipped']++;
                    continue;
                }
            }

            // Check for duplicate by email
            $existing_member = null;
            if ( ! empty( $data['primary_email'] ) ) {
                $existing_member = $members->get_by_email( $data['primary_email'] );
            }

            if ( $existing_member ) {
                if ( $skip_duplicates ) {
                    $results['skipped']++;
                    continue;
                }

                if ( $update_existing ) {
                    // Update existing member
                    $this->update_member_from_import( $existing_member->id, $data );
                    $results['updated']++;
                    continue;
                }
            }

            // Resolve membership tier
            $tier_id = $default_tier;
            if ( ! empty( $data['membership_tier'] ) ) {
                $tier = $tiers->get_by_slug( sanitize_title( $data['membership_tier'] ) );
                if ( ! $tier ) {
                    // Try by name
                    $all_tiers = $tiers->get_all();
                    foreach ( $all_tiers as $t ) {
                        if ( strtolower( $t->name ) === strtolower( $data['membership_tier'] ) ) {
                            $tier = $t;
                            break;
                        }
                    }
                }
                if ( $tier ) {
                    $tier_id = $tier->id;
                }
            }

            if ( ! $tier_id ) {
                // Use first active tier as fallback
                $active_tiers = $tiers->get_active();
                $tier_id = $active_tiers[0]->id ?? 0;
            }

            if ( ! $tier_id ) {
                $results['problem_rows'][] = array(
                    'row_num' => $row_num,
                    'issue'   => __( 'No valid membership tier', 'societypress' ),
                    'data'    => $data,
                    'raw_row' => $row,
                );
                $results['skipped']++;
                continue;
            }

            // Create member
            // Note: Use empty() checks because parse_date() returns '' on failure,
            // and ?? only triggers on null, not empty strings
            $member_data = array(
                'first_name'               => $data['first_name'] ?? '',
                'last_name'                => $data['last_name'] ?? '',
                'membership_tier_id'       => $tier_id,
                'status'                   => ! empty( $data['status'] ) ? $data['status'] : 'active',
                'join_date'                => ! empty( $data['join_date'] ) ? $data['join_date'] : gmdate( 'Y-m-d' ),
                'expiration_date'          => ! empty( $data['expiration_date'] ) ? $data['expiration_date'] : null,
                'birth_date'               => ! empty( $data['birth_date'] ) ? $data['birth_date'] : null,
                'directory_visible'        => $data['directory_visible'] ?? 1,
                'auto_renew'               => $data['auto_renew'] ?? 0,
                'communication_preference' => $data['communication_pref'] ?? 'email',
                'how_heard_about_us'       => ! empty( $data['how_heard'] ) ? $data['how_heard'] : null,
            );

            $member_id = $members->create( $member_data );

            if ( ! $member_id ) {
                $results['problem_rows'][] = array(
                    'row_num' => $row_num,
                    'issue'   => __( 'Could not create member', 'societypress' ),
                    'data'    => $data,
                    'raw_row' => $row,
                );
                $results['skipped']++;
                continue;
            }

            // Add contact info
            $contact_data = array(
                'member_id'      => $member_id,
                'primary_email'  => $data['primary_email'] ?? '',
                'secondary_email'=> $data['secondary_email'] ?? '',
                'home_phone'     => $data['home_phone'] ?? '',
                'cell_phone'     => $data['cell_phone'] ?? '',
                'work_phone'     => $data['work_phone'] ?? '',
                'street_address' => $data['street_address'] ?? '',
                'address_line_2' => $data['address_line_2'] ?? '',
                'city'           => $data['city'] ?? '',
                'state_province' => $data['state_province'] ?? '',
                'postal_code'    => $data['postal_code'] ?? '',
                'country'        => $data['country'] ?? 'USA',
            );

            $members->update_contact( $member_id, $contact_data );

            // Add surnames
            if ( ! empty( $data['surnames'] ) ) {
                $surnames = preg_split( '/[,;]+/', $data['surnames'] );
                foreach ( $surnames as $surname ) {
                    $surname = trim( $surname );
                    if ( $surname ) {
                        $members->add_surname( $member_id, $surname );
                    }
                }
            }

            // Add notes as meta
            if ( ! empty( $data['notes'] ) ) {
                $members->update_meta( $member_id, 'import_notes', $data['notes'] );
            }

            // Save nickname if parsed from full_name
            if ( ! empty( $data['_nickname'] ) ) {
                $members->update_meta( $member_id, 'nickname', sanitize_text_field( $data['_nickname'] ) );
            }

            // Save organization/company name if provided
            if ( ! empty( $data['organization'] ) ) {
                $members->update_meta( $member_id, 'organization', sanitize_text_field( $data['organization'] ) );
            }

            // Save genealogy service links/handles for enabled services
            // These are stored in the member_meta table for flexible storage
            $enabled_services = SocietyPress_Admin::get_enabled_genealogy_services();

            foreach ( array_keys( $enabled_services ) as $service_key ) {
                $field = 'genealogy_' . $service_key;
                if ( ! empty( $data[ $field ] ) ) {
                    $members->update_meta( $member_id, $field, sanitize_text_field( $data[ $field ] ) );
                }
            }

            $results['imported']++;
        }

        fclose( $handle );

        return $results;
    }

    /**
     * Update existing member from import data.
     *
     * @param int   $member_id Member ID.
     * @param array $data      Mapped data.
     */
    private function update_member_from_import( int $member_id, array $data ): void {
        $members = societypress()->members;

        // Parse full_name if provided (same logic as in process_import)
        if ( ! empty( $data['full_name'] ) ) {
            $parsed = $this->parse_full_name( $data['full_name'] );
            if ( empty( $data['first_name'] ) ) {
                $data['first_name'] = $parsed['first_name'];
            }
            if ( empty( $data['last_name'] ) ) {
                $data['last_name'] = $parsed['last_name'];
            }
            if ( ! empty( $parsed['nickname'] ) ) {
                $data['_nickname'] = $parsed['nickname'];
            }
        }

        // Update basic info
        $update_data = array();

        if ( ! empty( $data['first_name'] ) ) {
            $update_data['first_name'] = $data['first_name'];
        }
        if ( ! empty( $data['last_name'] ) ) {
            $update_data['last_name'] = $data['last_name'];
        }
        if ( ! empty( $data['status'] ) ) {
            $update_data['status'] = $data['status'];
        }
        if ( ! empty( $data['expiration_date'] ) ) {
            $update_data['expiration_date'] = $data['expiration_date'];
        }

        if ( ! empty( $update_data ) ) {
            $members->update( $member_id, $update_data );
        }

        // Update contact info
        $contact_data = array();

        foreach ( array( 'primary_email', 'secondary_email', 'home_phone', 'cell_phone', 'work_phone', 'street_address', 'address_line_2', 'city', 'state_province', 'postal_code', 'country' ) as $field ) {
            if ( isset( $data[ $field ] ) && '' !== $data[ $field ] ) {
                $contact_data[ $field ] = $data[ $field ];
            }
        }

        if ( ! empty( $contact_data ) ) {
            $members->update_contact( $member_id, $contact_data );
        }

        // Update nickname if parsed from full_name
        if ( ! empty( $data['_nickname'] ) ) {
            $members->update_meta( $member_id, 'nickname', sanitize_text_field( $data['_nickname'] ) );
        }

        // Update organization/company name if provided
        if ( ! empty( $data['organization'] ) ) {
            $members->update_meta( $member_id, 'organization', sanitize_text_field( $data['organization'] ) );
        }

        // Update genealogy service links/handles for enabled services
        $enabled_services = SocietyPress_Admin::get_enabled_genealogy_services();

        foreach ( array_keys( $enabled_services ) as $service_key ) {
            $field = 'genealogy_' . $service_key;
            if ( isset( $data[ $field ] ) && '' !== $data[ $field ] ) {
                $members->update_meta( $member_id, $field, sanitize_text_field( $data[ $field ] ) );
            }
        }
    }

    /**
     * Render the import page.
     */
    public function render_page(): void {
        $tiers = societypress()->tiers->get_active();
        $destination_fields = $this->get_grouped_destination_fields();

        ?>
        <div class="wrap societypress-admin societypress-import">
            <h1><?php esc_html_e( 'Import Members', 'societypress' ); ?></h1>

            <div class="societypress-import-steps">
                <!-- Step 1: Upload -->
                <div class="import-step" id="step-upload">
                    <h2><?php esc_html_e( 'Step 1: Upload CSV File', 'societypress' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Upload a CSV file exported from Wild Apricot, Excel, or another membership system.', 'societypress' ); ?>
                    </p>

                    <form id="csv-upload-form" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'societypress_import', 'import_nonce' ); ?>

                        <div class="upload-area" id="upload-area">
                            <input type="file" name="csv_file" id="csv-file" accept=".csv,.txt">
                            <label for="csv-file">
                                <span class="dashicons dashicons-upload"></span>
                                <span class="upload-text"><?php esc_html_e( 'Click to select CSV file or drag and drop', 'societypress' ); ?></span>
                            </label>
                        </div>

                        <p class="submit">
                            <button type="submit" class="button button-primary button-large" id="upload-btn" disabled>
                                <?php esc_html_e( 'Upload and Continue', 'societypress' ); ?>
                            </button>
                        </p>
                    </form>
                </div>

                <!-- Step 2: Map Fields -->
                <div class="import-step" id="step-mapping" style="display: none;">
                    <h2><?php esc_html_e( 'Step 2: Map Fields', 'societypress' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Match the columns in your CSV file to SocietyPress member fields.', 'societypress' ); ?>
                    </p>

                    <div class="mapping-info">
                        <span id="file-info"></span>
                    </div>

                    <table class="widefat striped" id="mapping-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'CSV Column', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Sample Data', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Import As', 'societypress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="mapping-body">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>

                    <p class="mapping-actions">
                        <button type="button" class="button" id="save-mapping-btn">
                            <?php esc_html_e( 'Save This Mapping', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button" id="load-mapping-btn" style="display: none;">
                            <?php esc_html_e( 'Load Saved Mapping', 'societypress' ); ?>
                        </button>
                    </p>

                    <p class="submit">
                        <button type="button" class="button" id="back-to-upload">
                            <?php esc_html_e( 'Back', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="preview-btn">
                            <?php esc_html_e( 'Preview Import', 'societypress' ); ?>
                        </button>
                    </p>
                </div>

                <!-- Step 3: Preview & Import -->
                <div class="import-step" id="step-preview" style="display: none;">
                    <h2><?php esc_html_e( 'Step 3: Preview & Import', 'societypress' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Review the mapped data before importing. The first few rows are shown below.', 'societypress' ); ?>
                    </p>

                    <div id="preview-container">
                        <!-- Populated by JavaScript -->
                    </div>

                    <div class="import-options">
                        <h3><?php esc_html_e( 'Import Options', 'societypress' ); ?></h3>

                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Duplicates', 'societypress' ); ?></th>
                                <td>
                                    <label>
                                        <input type="radio" name="duplicate_handling" value="skip" checked>
                                        <?php esc_html_e( 'Skip duplicate emails', 'societypress' ); ?>
                                    </label><br>
                                    <label>
                                        <input type="radio" name="duplicate_handling" value="update">
                                        <?php esc_html_e( 'Update existing members', 'societypress' ); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="default-tier"><?php esc_html_e( 'Default Tier', 'societypress' ); ?></label></th>
                                <td>
                                    <select id="default-tier" name="default_tier">
                                        <option value=""><?php esc_html_e( '— Use from CSV if available —', 'societypress' ); ?></option>
                                        <?php foreach ( $tiers as $tier ) : ?>
                                            <option value="<?php echo esc_attr( $tier->id ); ?>">
                                                <?php echo esc_html( $tier->name ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description"><?php esc_html_e( 'Used when CSV tier cannot be matched.', 'societypress' ); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p class="submit">
                        <button type="button" class="button" id="back-to-mapping">
                            <?php esc_html_e( 'Back', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="run-import-btn">
                            <?php esc_html_e( 'Run Import', 'societypress' ); ?>
                        </button>
                    </p>
                </div>

                <!-- Step 4: Review Problem Rows -->
                <div class="import-step" id="step-review" style="display: none;">
                    <h2><?php esc_html_e( 'Step 4: Review Problem Rows', 'societypress' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'The following rows could not be imported. Fix the issues below or discard rows you don\'t need.', 'societypress' ); ?>
                    </p>

                    <div id="review-summary" class="societypress-review-summary">
                        <!-- Populated by JavaScript: X imported successfully, Y need review -->
                    </div>

                    <div id="problem-rows-container">
                        <!-- Populated by JavaScript -->
                    </div>

                    <p class="submit">
                        <button type="button" class="button" id="skip-problem-rows-btn">
                            <?php esc_html_e( 'Skip All & Finish', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="commit-fixes-btn">
                            <?php esc_html_e( 'Import Fixed Rows', 'societypress' ); ?>
                        </button>
                    </p>
                </div>

                <!-- Step 5: Results -->
                <div class="import-step" id="step-results" style="display: none;">
                    <h2><?php esc_html_e( 'Import Complete', 'societypress' ); ?></h2>

                    <div id="results-container">
                        <!-- Populated by JavaScript -->
                    </div>

                    <p class="submit">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=societypress-members' ) ); ?>" class="button button-primary button-large">
                            <?php esc_html_e( 'View Members', 'societypress' ); ?>
                        </a>
                        <button type="button" class="button button-large" id="new-import-btn">
                            <?php esc_html_e( 'Import Another File', 'societypress' ); ?>
                        </button>
                    </p>
                </div>
            </div>

            <!-- Hidden data for JavaScript -->
            <script type="text/javascript">
                var societypressImport = {
                    ajaxUrl: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                    nonce: '<?php echo esc_js( wp_create_nonce( 'societypress_import' ) ); ?>',
                    destinationFields: <?php echo wp_json_encode( $destination_fields ); ?>,
                    strings: {
                        uploading: '<?php echo esc_js( __( 'Uploading...', 'societypress' ) ); ?>',
                        processing: '<?php echo esc_js( __( 'Processing...', 'societypress' ) ); ?>',
                        importing: '<?php echo esc_js( __( 'Importing...', 'societypress' ) ); ?>',
                        committing: '<?php echo esc_js( __( 'Importing fixed rows...', 'societypress' ) ); ?>',
                        error: '<?php echo esc_js( __( 'An error occurred.', 'societypress' ) ); ?>',
                        mappingSaved: '<?php echo esc_js( __( 'Mapping saved!', 'societypress' ) ); ?>',
                        noFieldsSelected: '<?php echo esc_js( __( 'Please map at least one field.', 'societypress' ) ); ?>',
                        confirmImport: '<?php echo esc_js( __( 'Are you sure you want to import these members?', 'societypress' ) ); ?>',
                        confirmSkipAll: '<?php echo esc_js( __( 'Skip all problem rows and finish?', 'societypress' ) ); ?>',
                        importedSuccess: '<?php echo esc_js( __( 'imported successfully', 'societypress' ) ); ?>',
                        needReview: '<?php echo esc_js( __( 'need review', 'societypress' ) ); ?>',
                        issue: '<?php echo esc_js( __( 'Issue', 'societypress' ) ); ?>',
                        discard: '<?php echo esc_js( __( 'Discard', 'societypress' ) ); ?>',
                        restore: '<?php echo esc_js( __( 'Restore', 'societypress' ) ); ?>',
                    },
                    tiers: <?php echo wp_json_encode( array_map( function( $t ) { return array( 'id' => $t->id, 'name' => $t->name ); }, $tiers ) ); ?>
                };
            </script>
        </div>
        <?php
    }
}
