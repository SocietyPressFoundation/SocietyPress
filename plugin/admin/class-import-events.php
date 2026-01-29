<?php
/**
 * Event Import Handler
 *
 * Handles CSV, TSV, and XLSX import for events.
 *
 * WHY: Allows bulk import of events from spreadsheets, useful for
 *      migrating from other systems or setting up a semester of classes.
 *
 * @package SocietyPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load SimpleXLSX if not already loaded
if ( ! class_exists( '\Shuchkin\SimpleXLSX' ) ) {
    require_once SOCIETYPRESS_PATH . 'vendor/SimpleXLSX.php';
}

/**
 * Class SocietyPress_Import_Events
 *
 * Manages the event import process.
 */
class SocietyPress_Import_Events {

    /**
     * Importable fields and their labels.
     *
     * @var array
     */
    private array $import_fields = array(
        'title'                 => 'Event Title',
        'description'           => 'Description',
        'date'                  => 'Event Date',
        'time'                  => 'Start Time',
        'end_time'              => 'End Time',
        'location'              => 'Location/Venue',
        'address'               => 'Address',
        'instructors'           => 'Instructor(s)',
        'category'              => 'Category/Type',
        'registration_url'      => 'Registration URL',
        'registration_required' => 'Registration Required',
    );

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_societypress_upload_events', array( $this, 'ajax_upload_file' ) );
        add_action( 'wp_ajax_societypress_preview_events', array( $this, 'ajax_preview_import' ) );
        add_action( 'wp_ajax_societypress_import_events', array( $this, 'ajax_run_import' ) );
    }

    /**
     * Get importable fields.
     *
     * @return array Field key => label pairs.
     */
    public function get_import_fields(): array {
        return $this->import_fields;
    }

    /**
     * Handle file upload via AJAX.
     */
    public function ajax_upload_file(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'societypress' ) ) );
        }

        if ( empty( $_FILES['import_file'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'societypress' ) ) );
        }

        $file = $_FILES['import_file'];

        // Validate file type
        $allowed_types = array(
            'text/csv',
            'text/plain',
            'text/tab-separated-values',
            'application/csv',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );
        $allowed_extensions = array( 'csv', 'tsv', 'txt', 'xlsx' );

        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $file['tmp_name'] );
        finfo_close( $finfo );

        $ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $mime_type, $allowed_types, true ) && ! in_array( $ext, $allowed_extensions, true ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid file type. Please upload a CSV, TSV, or XLSX file.', 'societypress' ) ) );
        }

        // Save file
        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/societypress-imports';

        if ( ! file_exists( $import_dir ) ) {
            wp_mkdir_p( $import_dir );
            file_put_contents( $import_dir . '/.htaccess', 'deny from all' );
            file_put_contents( $import_dir . '/index.php', '<?php // Silence is golden' );
        }

        $filename = 'events-import-' . wp_generate_uuid4() . '.' . $ext;
        $filepath = $import_dir . '/' . $filename;

        if ( ! move_uploaded_file( $file['tmp_name'], $filepath ) ) {
            wp_send_json_error( array( 'message' => __( 'Could not save uploaded file.', 'societypress' ) ) );
        }

        // Parse the file
        $parsed = $this->parse_file( $filepath, 10 );

        if ( is_wp_error( $parsed ) ) {
            unlink( $filepath );
            wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
        }

        // Auto-detect mappings
        $suggested_mapping = $this->auto_detect_mapping( $parsed['headers'] );

        wp_send_json_success( array(
            'file'              => $filename,
            'headers'           => $parsed['headers'],
            'sample_rows'       => $parsed['rows'],
            'total_rows'        => $parsed['total_rows'],
            'suggested_mapping' => $suggested_mapping,
        ) );
    }

    /**
     * Parse a file based on its extension.
     *
     * @param string $file_path   Path to the import file.
     * @param int    $sample_rows Number of sample rows to return.
     * @return array|WP_Error Parsed data or error.
     */
    private function parse_file( string $file_path, int $sample_rows = 5 ) {
        $ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

        if ( 'xlsx' === $ext ) {
            return $this->parse_xlsx( $file_path, $sample_rows );
        }

        return $this->parse_csv( $file_path, $sample_rows );
    }

    /**
     * Parse a CSV/TSV file.
     *
     * @param string $file_path   Path to file.
     * @param int    $sample_rows Number of sample rows to return.
     * @return array|WP_Error Parsed data or error.
     */
    private function parse_csv( string $file_path, int $sample_rows = 5 ) {
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'File not found.', 'societypress' ) );
        }

        $handle = fopen( $file_path, 'r' );
        if ( false === $handle ) {
            return new WP_Error( 'file_open_error', __( 'Could not open file.', 'societypress' ) );
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
        if ( false === $headers ) {
            fclose( $handle );
            return new WP_Error( 'no_headers', __( 'Could not read file headers.', 'societypress' ) );
        }

        // Clean headers
        $headers = array_map( function( $header ) {
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
        );
    }

    /**
     * Parse an XLSX file.
     *
     * @param string $file_path   Path to XLSX file.
     * @param int    $sample_rows Number of sample rows to return.
     * @return array|WP_Error Parsed data or error.
     */
    private function parse_xlsx( string $file_path, int $sample_rows = 5 ) {
        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', __( 'XLSX file not found.', 'societypress' ) );
        }

        $xlsx = \Shuchkin\SimpleXLSX::parse( $file_path );

        if ( ! $xlsx ) {
            return new WP_Error( 'parse_error', __( 'Could not parse XLSX file: ', 'societypress' ) . \Shuchkin\SimpleXLSX::parseError() );
        }

        $all_rows = $xlsx->rows();

        if ( empty( $all_rows ) ) {
            return new WP_Error( 'empty_file', __( 'XLSX file is empty.', 'societypress' ) );
        }

        $headers = array_map( function( $h ) { return trim( (string) $h ); }, array_shift( $all_rows ) );

        $rows = array();
        $total_rows = count( $all_rows );

        for ( $i = 0; $i < min( $sample_rows, $total_rows ); $i++ ) {
            $row = $all_rows[ $i ];
            while ( count( $row ) < count( $headers ) ) {
                $row[] = '';
            }
            $row = array_map( function( $cell ) { return trim( (string) $cell ); }, $row );
            $rows[] = array_slice( $row, 0, count( $headers ) );
        }

        return array(
            'headers'    => $headers,
            'rows'       => $rows,
            'total_rows' => $total_rows,
        );
    }

    /**
     * Auto-detect field mappings based on column headers.
     *
     * @param array $headers Column headers.
     * @return array Suggested mappings.
     */
    private function auto_detect_mapping( array $headers ): array {
        $mapping = array();

        $patterns = array(
            'title' => array(
                'title', 'event title', 'event name', 'name', 'event', 'subject',
            ),
            'description' => array(
                'description', 'desc', 'details', 'content', 'body', 'notes', 'summary',
            ),
            'date' => array(
                'date', 'event date', 'start date', 'when', 'day',
            ),
            'time' => array(
                'time', 'start time', 'start', 'begins', 'from',
            ),
            'end_time' => array(
                'end time', 'end', 'ends', 'until', 'to', 'finish',
            ),
            'location' => array(
                'location', 'venue', 'place', 'room', 'where',
            ),
            'address' => array(
                'address', 'street', 'full address',
            ),
            'instructors' => array(
                'instructor', 'instructors', 'teacher', 'presenter', 'speaker', 'host', 'leader', 'facilitator',
            ),
            'category' => array(
                'category', 'type', 'event type', 'class', 'kind',
            ),
            'registration_url' => array(
                'registration url', 'registration link', 'signup url', 'sign up link', 'register url', 'url', 'link',
            ),
            'registration_required' => array(
                'registration required', 'registration', 'rsvp', 'signup required', 'requires registration',
            ),
        );

        foreach ( $headers as $index => $header ) {
            $header_lower = strtolower( $header );

            foreach ( $patterns as $field => $variations ) {
                foreach ( $variations as $variation ) {
                    if ( $header_lower === $variation || strpos( $header_lower, $variation ) !== false ) {
                        $mapping[ $index ] = $field;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Preview import via AJAX.
     */
    public function ajax_preview_import(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

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

        $parsed = $this->parse_file( $filepath, 5 );

        if ( is_wp_error( $parsed ) ) {
            wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
        }

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
     * Map a row to field keys.
     *
     * @param array $row     Row data.
     * @param array $headers Headers.
     * @param array $mapping Mapping (header index => field key).
     * @return array Mapped data.
     */
    private function map_row( array $row, array $headers, array $mapping ): array {
        $data = array();

        foreach ( $mapping as $index => $field ) {
            if ( empty( $field ) || '--' === $field ) {
                continue;
            }
            $data[ $field ] = $row[ $index ] ?? '';
        }

        return $data;
    }

    /**
     * Run the actual import via AJAX.
     */
    public function ajax_run_import(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

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

        $result = $this->process_import( $filepath, $mapping );

        unlink( $filepath );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Process the import.
     *
     * @param string $filepath Path to import file.
     * @param array  $mapping  Field mapping.
     * @return array|WP_Error Results or error.
     */
    private function process_import( string $filepath, array $mapping ) {
        $ext = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
        $is_xlsx = ( 'xlsx' === $ext );

        $handle = null;
        $xlsx_rows = null;
        $xlsx_index = 0;
        $headers = array();
        $delimiter = ',';

        if ( $is_xlsx ) {
            $xlsx = \Shuchkin\SimpleXLSX::parse( $filepath );
            if ( ! $xlsx ) {
                return new WP_Error( 'file_error', __( 'Could not parse XLSX file.', 'societypress' ) );
            }
            $xlsx_rows = $xlsx->rows();
            if ( empty( $xlsx_rows ) ) {
                return new WP_Error( 'empty_file', __( 'File is empty.', 'societypress' ) );
            }
            $headers = array_map( function( $h ) { return trim( (string) $h ); }, array_shift( $xlsx_rows ) );
        } else {
            $handle = fopen( $filepath, 'r' );
            if ( false === $handle ) {
                return new WP_Error( 'file_error', __( 'Could not open file.', 'societypress' ) );
            }

            $first_line = fgets( $handle );
            rewind( $handle );

            if ( substr_count( $first_line, ';' ) > substr_count( $first_line, ',' ) ) {
                $delimiter = ';';
            } elseif ( substr_count( $first_line, "\t" ) > substr_count( $first_line, ',' ) ) {
                $delimiter = "\t";
            }

            $headers = fgetcsv( $handle, 0, $delimiter );
            $headers = array_map( 'trim', $headers );
        }

        $results = array(
            'imported' => 0,
            'skipped'  => 0,
            'errors'   => array(),
        );

        $row_num = 1;

        while ( true ) {
            if ( $is_xlsx ) {
                if ( $xlsx_index >= count( $xlsx_rows ) ) {
                    break;
                }
                $row = $xlsx_rows[ $xlsx_index++ ];
                $row = array_map( function( $cell ) { return trim( (string) $cell ); }, $row );
            } else {
                $row = fgetcsv( $handle, 0, $delimiter );
                if ( false === $row ) {
                    break;
                }
            }

            $row_num++;

            while ( count( $row ) < count( $headers ) ) {
                $row[] = '';
            }

            $data = $this->map_row( $row, $headers, $mapping );

            // Validate required fields
            if ( empty( $data['title'] ) ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: Missing event title. Skipped.', 'societypress' ),
                    $row_num
                );
                $results['skipped']++;
                continue;
            }

            // Create the event
            $event_id = $this->create_event( $data );

            if ( is_wp_error( $event_id ) ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: %s', 'societypress' ),
                    $row_num,
                    $event_id->get_error_message()
                );
                $results['skipped']++;
                continue;
            }

            $results['imported']++;
        }

        if ( ! $is_xlsx && $handle ) {
            fclose( $handle );
        }

        return $results;
    }

    /**
     * Create an event from imported data.
     *
     * @param array $data Mapped event data.
     * @return int|WP_Error Event ID or error.
     */
    private function create_event( array $data ) {
        // Prepare post data
        $post_data = array(
            'post_title'   => sanitize_text_field( $data['title'] ),
            'post_content' => wp_kses_post( $data['description'] ?? '' ),
            'post_status'  => 'publish',
            'post_type'    => 'sp_event',
        );

        $event_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $event_id ) ) {
            return $event_id;
        }

        // Save meta fields
        if ( ! empty( $data['date'] ) ) {
            $date = $this->parse_date( $data['date'] );
            if ( $date ) {
                update_post_meta( $event_id, 'sp_event_date', $date );
            }
        }

        if ( ! empty( $data['time'] ) ) {
            $time = $this->parse_time( $data['time'] );
            if ( $time ) {
                update_post_meta( $event_id, 'sp_event_time', $time );
            }
        }

        if ( ! empty( $data['end_time'] ) ) {
            $end_time = $this->parse_time( $data['end_time'] );
            if ( $end_time ) {
                update_post_meta( $event_id, 'sp_event_end_time', $end_time );
            }
        }

        if ( ! empty( $data['location'] ) ) {
            update_post_meta( $event_id, 'sp_event_location', sanitize_text_field( $data['location'] ) );
        }

        if ( ! empty( $data['address'] ) ) {
            update_post_meta( $event_id, 'sp_event_address', sanitize_textarea_field( $data['address'] ) );
        }

        if ( ! empty( $data['instructors'] ) ) {
            update_post_meta( $event_id, 'sp_event_instructors', sanitize_text_field( $data['instructors'] ) );
        }

        if ( ! empty( $data['registration_url'] ) ) {
            update_post_meta( $event_id, 'sp_event_registration_url', esc_url_raw( $data['registration_url'] ) );
        }

        if ( ! empty( $data['registration_required'] ) ) {
            $required = $this->parse_boolean( $data['registration_required'] );
            update_post_meta( $event_id, 'sp_event_registration_required', $required ? '1' : '0' );
        }

        // Handle category
        if ( ! empty( $data['category'] ) ) {
            $category = sanitize_text_field( $data['category'] );
            $term = term_exists( $category, 'sp_event_category' );
            if ( ! $term ) {
                $term = wp_insert_term( $category, 'sp_event_category' );
            }
            if ( ! is_wp_error( $term ) ) {
                wp_set_object_terms( $event_id, (int) $term['term_id'], 'sp_event_category' );
            }
        }

        return $event_id;
    }

    /**
     * Parse a date string into Y-m-d format.
     *
     * @param string $date_string Date string.
     * @return string|null Formatted date or null.
     */
    private function parse_date( string $date_string ): ?string {
        $date_string = trim( $date_string );
        if ( empty( $date_string ) ) {
            return null;
        }

        // Try parsing with strtotime
        $timestamp = strtotime( $date_string );
        if ( false !== $timestamp ) {
            return date( 'Y-m-d', $timestamp );
        }

        return null;
    }

    /**
     * Parse a time string into H:i format.
     *
     * @param string $time_string Time string.
     * @return string|null Formatted time or null.
     */
    private function parse_time( string $time_string ): ?string {
        $time_string = trim( $time_string );
        if ( empty( $time_string ) ) {
            return null;
        }

        // Try parsing with strtotime
        $timestamp = strtotime( $time_string );
        if ( false !== $timestamp ) {
            return date( 'H:i', $timestamp );
        }

        return null;
    }

    /**
     * Parse a boolean value from various string representations.
     *
     * @param string $value Value to parse.
     * @return bool Boolean result.
     */
    private function parse_boolean( string $value ): bool {
        $value = strtolower( trim( $value ) );
        return in_array( $value, array( '1', 'yes', 'true', 'y', 'x', 'required' ), true );
    }

    /**
     * Render the import page.
     */
    public function render_import_page(): void {
        ?>
        <div class="wrap societypress-import-events">
            <h1><?php esc_html_e( 'Import Events', 'societypress' ); ?></h1>

            <div class="societypress-import-steps">
                <!-- Step 1: Upload -->
                <div class="import-step" id="step-upload">
                    <h2><?php esc_html_e( 'Step 1: Upload Event List', 'societypress' ); ?></h2>
                    <p class="description">
                        <?php esc_html_e( 'Upload a spreadsheet containing your events.', 'societypress' ); ?>
                        <br><strong><?php esc_html_e( 'Supported formats:', 'societypress' ); ?></strong> CSV, TSV, XLSX (Excel)
                    </p>

                    <form id="events-upload-form" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'societypress_import_events', 'import_nonce' ); ?>

                        <div class="upload-area" id="upload-area">
                            <input type="file" name="import_file" id="import-file" accept=".csv,.tsv,.txt,.xlsx">
                            <label for="import-file">
                                <span class="dashicons dashicons-upload"></span>
                                <span class="upload-text"><?php esc_html_e( 'Click to select file or drag and drop', 'societypress' ); ?></span>
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
                        <?php esc_html_e( 'Match your spreadsheet columns to event fields.', 'societypress' ); ?>
                    </p>

                    <form id="events-mapping-form">
                        <table class="wp-list-table widefat striped" id="mapping-table">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e( 'Your Column', 'societypress' ); ?></th>
                                    <th><?php esc_html_e( 'Sample Data', 'societypress' ); ?></th>
                                    <th><?php esc_html_e( 'Map To', 'societypress' ); ?></th>
                                </tr>
                            </thead>
                            <tbody id="mapping-body">
                            </tbody>
                        </table>

                        <p class="submit">
                            <button type="button" class="button" id="back-to-upload">
                                <?php esc_html_e( 'Back', 'societypress' ); ?>
                            </button>
                            <button type="submit" class="button button-primary" id="preview-btn">
                                <?php esc_html_e( 'Preview Import', 'societypress' ); ?>
                            </button>
                        </p>
                    </form>
                </div>

                <!-- Step 3: Preview & Import -->
                <div class="import-step" id="step-preview" style="display: none;">
                    <h2><?php esc_html_e( 'Step 3: Preview & Import', 'societypress' ); ?></h2>
                    <p class="description" id="preview-count"></p>

                    <table class="wp-list-table widefat striped" id="preview-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Title', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Time', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Location', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Category', 'societypress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="preview-body">
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="button" class="button" id="back-to-mapping">
                            <?php esc_html_e( 'Back', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="run-import-btn">
                            <?php esc_html_e( 'Import Events', 'societypress' ); ?>
                        </button>
                    </p>
                </div>

                <!-- Step 4: Results -->
                <div class="import-step" id="step-results" style="display: none;">
                    <h2><?php esc_html_e( 'Import Complete', 'societypress' ); ?></h2>
                    <div id="import-results"></div>
                    <p class="submit">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sp_event' ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'View Events', 'societypress' ); ?>
                        </a>
                        <button type="button" class="button" id="import-more">
                            <?php esc_html_e( 'Import More', 'societypress' ); ?>
                        </button>
                    </p>
                </div>
            </div>
        </div>

        <script>
        jQuery(function($) {
            var importFile = '';
            var importHeaders = [];
            var importMapping = {};
            var importFields = <?php echo wp_json_encode( $this->import_fields ); ?>;

            // File selection
            $('#import-file').on('change', function() {
                var file = this.files[0];
                if (file) {
                    $('.upload-text').text(file.name);
                    $('#upload-btn').prop('disabled', false);
                } else {
                    $('.upload-text').text('<?php esc_html_e( 'Click to select file or drag and drop', 'societypress' ); ?>');
                    $('#upload-btn').prop('disabled', true);
                }
            });

            // Upload form
            $('#events-upload-form').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'societypress_upload_events');
                formData.append('nonce', $('#import_nonce').val());

                $('#upload-btn').prop('disabled', true).text('<?php esc_html_e( 'Uploading...', 'societypress' ); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            importFile = response.data.file;
                            importHeaders = response.data.headers;
                            buildMappingTable(response.data.headers, response.data.sample_rows, response.data.suggested_mapping);
                            $('#step-upload').hide();
                            $('#step-mapping').show();
                        } else {
                            alert(response.data.message);
                        }
                        $('#upload-btn').prop('disabled', false).text('<?php esc_html_e( 'Upload and Continue', 'societypress' ); ?>');
                    },
                    error: function() {
                        alert('<?php esc_html_e( 'Upload failed. Please try again.', 'societypress' ); ?>');
                        $('#upload-btn').prop('disabled', false).text('<?php esc_html_e( 'Upload and Continue', 'societypress' ); ?>');
                    }
                });
            });

            function buildMappingTable(headers, rows, suggested) {
                var html = '';
                headers.forEach(function(header, index) {
                    var sample = rows.length > 0 ? (rows[0][index] || '') : '';
                    var selected = suggested[index] || '';

                    html += '<tr>';
                    html += '<td><strong>' + escapeHtml(header) + '</strong></td>';
                    html += '<td><code>' + escapeHtml(sample) + '</code></td>';
                    html += '<td><select name="mapping[' + index + ']" class="mapping-select">';
                    html += '<option value="--"><?php esc_html_e( '-- Do not import --', 'societypress' ); ?></option>';

                    for (var key in importFields) {
                        var sel = (key === selected) ? ' selected' : '';
                        html += '<option value="' + key + '"' + sel + '>' + importFields[key] + '</option>';
                    }

                    html += '</select></td>';
                    html += '</tr>';
                });
                $('#mapping-body').html(html);
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Back buttons
            $('#back-to-upload').on('click', function() {
                $('#step-mapping').hide();
                $('#step-upload').show();
            });

            $('#back-to-mapping').on('click', function() {
                $('#step-preview').hide();
                $('#step-mapping').show();
            });

            // Preview
            $('#events-mapping-form').on('submit', function(e) {
                e.preventDefault();

                importMapping = {};
                $('.mapping-select').each(function() {
                    var index = $(this).attr('name').match(/\d+/)[0];
                    importMapping[index] = $(this).val();
                });

                $('#preview-btn').prop('disabled', true).text('<?php esc_html_e( 'Loading...', 'societypress' ); ?>');

                $.post(ajaxurl, {
                    action: 'societypress_preview_events',
                    nonce: $('#import_nonce').val(),
                    file: importFile,
                    mapping: importMapping
                }, function(response) {
                    if (response.success) {
                        buildPreviewTable(response.data.preview);
                        $('#preview-count').text(
                            '<?php esc_html_e( 'Found', 'societypress' ); ?> ' +
                            response.data.total_rows +
                            ' <?php esc_html_e( 'events to import. Showing first 5:', 'societypress' ); ?>'
                        );
                        $('#step-mapping').hide();
                        $('#step-preview').show();
                    } else {
                        alert(response.data.message);
                    }
                    $('#preview-btn').prop('disabled', false).text('<?php esc_html_e( 'Preview Import', 'societypress' ); ?>');
                });
            });

            function buildPreviewTable(rows) {
                var html = '';
                rows.forEach(function(row) {
                    html += '<tr>';
                    html += '<td>' + escapeHtml(row.title || '') + '</td>';
                    html += '<td>' + escapeHtml(row.date || '') + '</td>';
                    html += '<td>' + escapeHtml(row.time || '') + '</td>';
                    html += '<td>' + escapeHtml(row.location || '') + '</td>';
                    html += '<td>' + escapeHtml(row.category || '') + '</td>';
                    html += '</tr>';
                });
                $('#preview-body').html(html);
            }

            // Run import
            $('#run-import-btn').on('click', function() {
                $(this).prop('disabled', true).text('<?php esc_html_e( 'Importing...', 'societypress' ); ?>');

                $.post(ajaxurl, {
                    action: 'societypress_import_events',
                    nonce: $('#import_nonce').val(),
                    file: importFile,
                    mapping: importMapping
                }, function(response) {
                    if (response.success) {
                        var html = '<div class="notice notice-success"><p>';
                        html += '<strong><?php esc_html_e( 'Successfully imported:', 'societypress' ); ?></strong> ' + response.data.imported + ' <?php esc_html_e( 'events', 'societypress' ); ?>';
                        if (response.data.skipped > 0) {
                            html += '<br><strong><?php esc_html_e( 'Skipped:', 'societypress' ); ?></strong> ' + response.data.skipped;
                        }
                        html += '</p></div>';

                        if (response.data.errors && response.data.errors.length > 0) {
                            html += '<div class="notice notice-warning"><p><strong><?php esc_html_e( 'Warnings:', 'societypress' ); ?></strong></p><ul>';
                            response.data.errors.forEach(function(err) {
                                html += '<li>' + escapeHtml(err) + '</li>';
                            });
                            html += '</ul></div>';
                        }

                        $('#import-results').html(html);
                        $('#step-preview').hide();
                        $('#step-results').show();
                    } else {
                        alert(response.data.message);
                        $('#run-import-btn').prop('disabled', false).text('<?php esc_html_e( 'Import Events', 'societypress' ); ?>');
                    }
                });
            });

            // Import more
            $('#import-more').on('click', function() {
                location.reload();
            });
        });
        </script>

        <style>
        .societypress-import-events .upload-area {
            border: 2px dashed #ccc;
            padding: 40px;
            text-align: center;
            background: #fafafa;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .societypress-import-events .upload-area:hover {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        .societypress-import-events .upload-area input[type="file"] {
            display: none;
        }
        .societypress-import-events .upload-area label {
            cursor: pointer;
            display: block;
        }
        .societypress-import-events .upload-area .dashicons {
            font-size: 48px;
            width: 48px;
            height: 48px;
            color: #2271b1;
        }
        .societypress-import-events .upload-text {
            display: block;
            margin-top: 10px;
            font-size: 14px;
        }
        .societypress-import-events .mapping-select {
            width: 100%;
        }
        .societypress-import-events #preview-table code {
            background: #f0f0f0;
            padding: 2px 6px;
        }
        </style>
        <?php
    }
}
