<?php
/**
 * Event Import Handler
 *
 * Handles CSV, TSV, and XLSX import for events with support for:
 * - Basic event fields (title, date, time, location, etc.)
 * - Recurring events (weekly/monthly patterns)
 * - Time slots with capacity
 * - Problem row handling for rows that need user review
 * - Duplicate detection with skip/update options
 * - Downloadable CSV template
 *
 * WHY: Allows bulk import of events from spreadsheets, useful for
 *      migrating from other systems or setting up a semester of classes.
 *      The recurring and slot support handles complex events like
 *      "DNA Consult Hours" that have multiple sessions per event.
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
     * WHY: Organized with basic fields first, then recurring, then slots.
     *      This order makes the mapping UI intuitive for users who may
     *      not need the advanced recurring/slot features.
     *
     * @var array
     */
    private array $import_fields = array(
        // Basic event fields
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
        'notice_only'           => 'Notice Only (no detail page)',
        // Recurring event fields
        'recurring_type'        => 'Recurrence (weekly/monthly)',
        'recurring_week'        => 'Recurring Week (1st, 2nd, etc.)',
        'recurring_day'         => 'Recurring Day (Monday, etc.)',
        'recurring_end'         => 'Recurrence End Date',
        // Time slots field
        'slots'                 => 'Time Slots',
    );

    /**
     * Constructor.
     *
     * WHY: Register all AJAX handlers on init so they're available when called.
     *      The template download uses a separate action for direct file output.
     */
    public function __construct() {
        add_action( 'wp_ajax_societypress_upload_events', array( $this, 'ajax_upload_file' ) );
        add_action( 'wp_ajax_societypress_preview_events', array( $this, 'ajax_preview_import' ) );
        add_action( 'wp_ajax_societypress_import_events', array( $this, 'ajax_run_import' ) );
        add_action( 'wp_ajax_societypress_commit_event_problem_rows', array( $this, 'ajax_commit_problem_rows' ) );
        add_action( 'wp_ajax_societypress_download_events_template', array( $this, 'ajax_download_template' ) );
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
     *
     * WHY: Validates file type, stores it temporarily, parses headers and
     *      sample rows, and auto-detects field mappings to save user time.
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

        // Validate file type - accept common spreadsheet formats
        // WHY: Users might have CSV, TSV, or Excel files depending on their
        //      source system. Supporting all three reduces friction.
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

        // Save file to protected uploads directory
        // WHY: Using a dedicated directory with .htaccess protection ensures
        //      the import files aren't publicly accessible during processing.
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

        // Parse the file to get headers and sample data
        $parsed = $this->parse_file( $filepath, 10 );

        if ( is_wp_error( $parsed ) ) {
            unlink( $filepath );
            wp_send_json_error( array( 'message' => $parsed->get_error_message() ) );
        }

        // Auto-detect mappings based on column headers
        // WHY: Most users have predictable column names. Auto-detection
        //      saves them from manually mapping every single field.
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
     * WHY: Unified entry point that auto-detects format and calls the right parser.
     *      This keeps the upload handler simple and consistent.
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
     * WHY: Auto-detects delimiter (comma, semicolon, tab) so users don't
     *      have to worry about which format their export is in.
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

        // Detect delimiter by counting occurrences in first line
        // WHY: Different systems export with different delimiters.
        //      Comma is most common, but some European systems use semicolon,
        //      and TSV files use tab.
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

        // Clean headers - remove BOM and trim whitespace
        // WHY: Excel often adds a UTF-8 BOM (byte order mark) that can
        //      break header matching if not removed.
        $headers = array_map( function( $header ) {
            $header = preg_replace( '/^\xEF\xBB\xBF/', '', $header );
            return trim( $header );
        }, $headers );

        // Read sample rows for preview
        $rows = array();
        $row_count = 0;
        $total_rows = 0;

        while ( ( $row = fgetcsv( $handle, 0, $delimiter ) ) !== false ) {
            $total_rows++;
            if ( $row_count < $sample_rows ) {
                // Pad row to match header count if needed
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
     * WHY: Many organizations maintain event schedules in Excel. Direct
     *      import saves them the "Save As CSV" step.
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
     * WHY: Saves users time by pre-filling mappings for common column names.
     *      The patterns cover variations from different calendar/event systems.
     *
     * @param array $headers Column headers.
     * @return array Suggested mappings (header index => field key).
     */
    private function auto_detect_mapping( array $headers ): array {
        $mapping = array();

        // Common variations of field names from different event systems
        // WHY: Each pattern array includes common synonyms and variations
        //      that might appear in exports from Google Calendar, Outlook,
        //      other event management systems, or hand-made spreadsheets.
        $patterns = array(
            'title' => array(
                'title', 'event title', 'event name', 'name', 'event', 'subject', 'summary',
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
            'notice_only' => array(
                'notice only', 'notice', 'no detail', 'calendar only', 'closure',
            ),
            // Recurring event patterns
            'recurring_type' => array(
                'recurring', 'recurrence', 'repeat', 'frequency', 'recurring type', 'recurrence type',
            ),
            'recurring_week' => array(
                'week', 'which week', 'ordinal', 'recurring week', 'week of month',
            ),
            'recurring_day' => array(
                'day of week', 'weekday', 'recurring day', 'day name',
            ),
            'recurring_end' => array(
                'end date', 'until', 'recurrence end', 'recurring end', 'repeat until',
            ),
            // Time slots pattern
            'slots' => array(
                'slots', 'time slots', 'sessions', 'times', 'slot times',
            ),
        );

        foreach ( $headers as $index => $header ) {
            $header_lower = strtolower( $header );

            foreach ( $patterns as $field => $variations ) {
                foreach ( $variations as $variation ) {
                    // Match exact or substring
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
     *
     * WHY: Shows users what the import will look like before committing,
     *      allowing them to catch mapping errors early.
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
     * WHY: Transforms raw CSV row data into an associative array keyed by
     *      our field names, making it easy to work with during import.
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
     *
     * WHY: Main entry point for processing the import. Returns results
     *      including success counts and any problem rows for review.
     */
    public function ajax_run_import(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

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

        $result = $this->process_import( $filepath, $mapping, $options );

        unlink( $filepath );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Process the import.
     *
     * WHY: Core import logic that handles all the complexity:
     *      - CSV vs XLSX parsing
     *      - Field mapping and validation
     *      - Duplicate detection
     *      - Recurring event setup
     *      - Time slot creation
     *      - Problem row collection
     *
     * @param string $filepath Path to import file.
     * @param array  $mapping  Field mapping.
     * @param array  $options  Import options (skip_duplicates, update_existing).
     * @return array|WP_Error Results or error.
     */
    private function process_import( string $filepath, array $mapping, array $options = array() ) {
        $ext = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
        $is_xlsx = ( 'xlsx' === $ext );

        $handle = null;
        $xlsx_rows = null;
        $xlsx_index = 0;
        $headers = array();
        $delimiter = ',';

        // Open file and read headers based on type
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

        // Initialize results tracking
        $results = array(
            'imported'     => 0,
            'updated'      => 0,
            'skipped'      => 0,
            'errors'       => array(),
            'problem_rows' => array(),
        );

        // Parse options
        $skip_duplicates = ! empty( $options['skip_duplicates'] );
        $update_existing = ! empty( $options['update_existing'] );

        $row_num = 1;

        // Process each row
        while ( true ) {
            // Get next row based on file type
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

            // Pad row to match headers
            while ( count( $row ) < count( $headers ) ) {
                $row[] = '';
            }

            $data = $this->map_row( $row, $headers, $mapping );

            // Validate required fields - title is required
            // WHY: An event without a title is useless. We collect these
            //      as problem rows so users can fix them rather than losing data.
            if ( empty( $data['title'] ) ) {
                $results['problem_rows'][] = array(
                    'row_num' => $row_num,
                    'issue'   => __( 'Missing event title', 'societypress' ),
                    'data'    => $data,
                    'raw_row' => $row,
                );
                $results['skipped']++;
                continue;
            }

            // Check for duplicates by title + date
            // WHY: Two events with the same title on the same date are
            //      almost certainly duplicates. Let user choose to skip or update.
            if ( $skip_duplicates || $update_existing ) {
                $existing = $this->find_existing_event( $data['title'], $data['date'] ?? '' );
                if ( $existing ) {
                    if ( $skip_duplicates ) {
                        $results['skipped']++;
                        continue;
                    }
                    if ( $update_existing ) {
                        // Update existing event instead of creating new
                        $this->update_event( $existing, $data );
                        $results['updated']++;
                        continue;
                    }
                }
            }

            // Create the event
            $event_id = $this->create_event( $data );

            if ( is_wp_error( $event_id ) ) {
                $results['problem_rows'][] = array(
                    'row_num' => $row_num,
                    'issue'   => $event_id->get_error_message(),
                    'data'    => $data,
                    'raw_row' => $row,
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
     * Find an existing event by title and date.
     *
     * WHY: Used for duplicate detection. Matches on both title and date
     *      to avoid false positives (same title, different dates).
     *
     * @param string $title Event title.
     * @param string $date  Event date (optional).
     * @return int|null Event ID if found, null otherwise.
     */
    private function find_existing_event( string $title, string $date = '' ): ?int {
        $args = array(
            'post_type'      => 'sp_event',
            'title'          => $title,
            'posts_per_page' => 1,
            'post_status'    => array( 'publish', 'draft', 'pending' ),
            'fields'         => 'ids',
        );

        // If date provided, also match on date meta
        if ( ! empty( $date ) ) {
            $parsed_date = $this->parse_date( $date );
            if ( $parsed_date ) {
                $args['meta_query'] = array(
                    array(
                        'key'   => 'sp_event_date',
                        'value' => $parsed_date,
                    ),
                );
            }
        }

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            return $query->posts[0];
        }

        return null;
    }

    /**
     * Update an existing event with import data.
     *
     * WHY: When user chooses "update existing" for duplicates, we update
     *      the event rather than creating a new one to avoid clutter.
     *
     * @param int   $event_id Event post ID.
     * @param array $data     Mapped event data.
     */
    private function update_event( int $event_id, array $data ): void {
        // Update post data
        $post_data = array(
            'ID' => $event_id,
        );

        if ( ! empty( $data['title'] ) ) {
            $post_data['post_title'] = sanitize_text_field( $data['title'] );
        }

        if ( isset( $data['description'] ) ) {
            $post_data['post_content'] = wp_kses_post( $data['description'] );
        }

        wp_update_post( $post_data );

        // Update meta fields
        $this->save_event_meta( $event_id, $data );
    }

    /**
     * Create an event from imported data.
     *
     * WHY: Creates the event post and all associated data (meta, category,
     *      recurring settings, time slots) in one place.
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

        // Save all meta fields
        $this->save_event_meta( $event_id, $data );

        return $event_id;
    }

    /**
     * Save event meta fields including recurring and slots.
     *
     * WHY: Centralized meta saving used by both create and update operations.
     *      Handles basic fields, recurring settings, and time slot creation.
     *
     * @param int   $event_id Event post ID.
     * @param array $data     Mapped event data.
     */
    private function save_event_meta( int $event_id, array $data ): void {
        // Basic event meta fields
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

        if ( ! empty( $data['notice_only'] ) ) {
            $notice = $this->parse_boolean( $data['notice_only'] );
            update_post_meta( $event_id, 'sp_event_notice_only', $notice ? '1' : '0' );
        }

        // Handle category assignment
        // WHY: Creates the category if it doesn't exist, making import seamless
        //      without requiring pre-creation of all categories.
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

        // Save recurring event fields
        // WHY: Recurring events are common for society activities (monthly meetings,
        //      weekly classes). Supporting this in import saves manual setup.
        if ( ! empty( $data['recurring_type'] ) ) {
            $type = $this->parse_recurring_type( $data['recurring_type'] );
            if ( $type ) {
                update_post_meta( $event_id, 'sp_event_recurring', $type );

                // Monthly recurring needs week and day
                if ( 'monthly' === $type ) {
                    if ( ! empty( $data['recurring_week'] ) ) {
                        $week = $this->parse_recurring_week( $data['recurring_week'] );
                        if ( $week ) {
                            update_post_meta( $event_id, 'sp_event_recurring_week', $week );
                        }
                    }
                    if ( ! empty( $data['recurring_day'] ) ) {
                        $day = $this->parse_recurring_day( $data['recurring_day'] );
                        if ( $day !== '' ) {
                            update_post_meta( $event_id, 'sp_event_recurring_day', $day );
                        }
                    }
                }

                // End date for recurrence
                if ( ! empty( $data['recurring_end'] ) ) {
                    $end = $this->parse_date( $data['recurring_end'] );
                    if ( $end ) {
                        update_post_meta( $event_id, 'sp_event_recurring_end', $end );
                    }
                }
            }
        }

        // Create time slots from slots field
        // WHY: Events like "DNA Consult Hours" have multiple time slots
        //      (10-11, 11-12, 12-1) that members can register for individually.
        if ( ! empty( $data['slots'] ) ) {
            $this->create_event_slots( $event_id, $data['slots'] );
        }
    }

    /**
     * Create time slots for an event from the slots string.
     *
     * Format: Pipe-separated entries, semicolon between slots.
     * Each slot: start-end|capacity|description
     * Example: "10:00-11:00|20|Morning;11:00-12:00|20|Midday;12:00-13:00|20|Afternoon"
     *
     * WHY: This compact format allows multiple slots in a single CSV cell,
     *      which is easier for users than multiple columns or rows per event.
     *
     * @param int    $event_id     Event post ID.
     * @param string $slots_string Formatted slots string.
     */
    private function create_event_slots( int $event_id, string $slots_string ): void {
        // Get the slots class if available
        if ( ! class_exists( 'SocietyPress_Event_Slots' ) ) {
            return;
        }

        $slots_class = new SocietyPress_Event_Slots();
        $slots = explode( ';', $slots_string );
        $sort_order = 0;

        foreach ( $slots as $slot_str ) {
            $slot_str = trim( $slot_str );
            if ( empty( $slot_str ) ) {
                continue;
            }

            $parts = explode( '|', $slot_str );
            if ( empty( $parts[0] ) ) {
                continue;
            }

            // Parse time range (10:00-11:00 or 10:00 AM-11:00 AM)
            // WHY: Support both 24-hour and AM/PM formats since users
            //      might have either in their spreadsheets.
            $time_range = $parts[0];
            $times = explode( '-', $time_range, 2 );

            $start = $this->parse_time( trim( $times[0] ) );
            $end = isset( $times[1] ) ? $this->parse_time( trim( $times[1] ) ) : null;

            if ( ! $start ) {
                continue;
            }

            // Create the slot
            $slot_data = array(
                'start_time'  => $start . ':00',
                'end_time'    => ( $end ?? $start ) . ':00',
                'capacity'    => ! empty( $parts[1] ) ? absint( $parts[1] ) : null,
                'description' => isset( $parts[2] ) ? sanitize_text_field( $parts[2] ) : '',
                'sort_order'  => $sort_order++,
                'is_active'   => 1,
            );

            $slots_class->create( $event_id, $slot_data );
        }
    }

    /**
     * Parse recurring type value.
     *
     * WHY: Accepts various common phrasings so users don't have to use
     *      exact keywords.
     *
     * @param string $value Raw recurring type value.
     * @return string Normalized type ('weekly', 'monthly', or empty).
     */
    private function parse_recurring_type( string $value ): string {
        $value = strtolower( trim( $value ) );

        $weekly_values = array( 'weekly', 'every week', 'week', 'w' );
        $monthly_values = array( 'monthly', 'every month', 'month', 'm' );

        if ( in_array( $value, $weekly_values, true ) ) {
            return 'weekly';
        }

        if ( in_array( $value, $monthly_values, true ) ) {
            return 'monthly';
        }

        return '';
    }

    /**
     * Parse recurring week value (which week of the month).
     *
     * WHY: Handles ordinal numbers, words, and abbreviations for maximum
     *      flexibility in import data formats.
     *
     * @param string $value Raw week value.
     * @return string Normalized week ('1', '2', '3', '4', 'last', or empty).
     */
    private function parse_recurring_week( string $value ): string {
        $value = strtolower( trim( $value ) );

        $map = array(
            // First week variations
            '1st'     => '1',
            'first'   => '1',
            '1'       => '1',
            // Second week variations
            '2nd'     => '2',
            'second'  => '2',
            '2'       => '2',
            // Third week variations
            '3rd'     => '3',
            'third'   => '3',
            '3'       => '3',
            // Fourth week variations
            '4th'     => '4',
            'fourth'  => '4',
            '4'       => '4',
            // Last week variations
            'last'    => 'last',
            'final'   => 'last',
            '5th'     => 'last',
            'fifth'   => 'last',
        );

        return $map[ $value ] ?? '';
    }

    /**
     * Parse recurring day value (day of the week).
     *
     * WHY: Accepts day names, abbreviations, and numbers for flexibility.
     *      Returns numeric string to match WordPress's day numbering.
     *
     * @param string $value Raw day value.
     * @return string Normalized day ('0'-'6' for Sun-Sat, or empty).
     */
    private function parse_recurring_day( string $value ): string {
        $value = strtolower( trim( $value ) );

        $map = array(
            // Sunday
            'sunday'    => '0',
            'sun'       => '0',
            '0'         => '0',
            // Monday
            'monday'    => '1',
            'mon'       => '1',
            '1'         => '1',
            // Tuesday
            'tuesday'   => '2',
            'tue'       => '2',
            'tues'      => '2',
            '2'         => '2',
            // Wednesday
            'wednesday' => '3',
            'wed'       => '3',
            '3'         => '3',
            // Thursday
            'thursday'  => '4',
            'thu'       => '4',
            'thur'      => '4',
            'thurs'     => '4',
            '4'         => '4',
            // Friday
            'friday'    => '5',
            'fri'       => '5',
            '5'         => '5',
            // Saturday
            'saturday'  => '6',
            'sat'       => '6',
            '6'         => '6',
        );

        return $map[ $value ] ?? '';
    }

    /**
     * Parse a date string into Y-m-d format.
     *
     * WHY: Handles various date formats that might appear in spreadsheets
     *      from different systems or regions.
     *
     * @param string $date_string Date string.
     * @return string|null Formatted date or null.
     */
    private function parse_date( string $date_string ): ?string {
        $date_string = trim( $date_string );
        if ( empty( $date_string ) ) {
            return null;
        }

        // Try parsing with strtotime - handles most formats
        $timestamp = strtotime( $date_string );
        if ( false !== $timestamp ) {
            return date( 'Y-m-d', $timestamp );
        }

        return null;
    }

    /**
     * Parse a time string into H:i format.
     *
     * WHY: Handles both 24-hour and 12-hour time formats since users
     *      might have either in their spreadsheets.
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
     * WHY: Different systems represent booleans differently. This handles
     *      common variations to be user-friendly.
     *
     * @param string $value Value to parse.
     * @return bool Boolean result.
     */
    private function parse_boolean( string $value ): bool {
        $value = strtolower( trim( $value ) );
        return in_array( $value, array( '1', 'yes', 'true', 'y', 'x', 'required' ), true );
    }

    /**
     * Commit fixed problem rows via AJAX.
     *
     * WHY: After the main import, users can fix problem rows (add missing
     *      titles, etc.) and import them without re-uploading the file.
     */
    public function ajax_commit_problem_rows(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

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

        foreach ( $rows as $row ) {
            // Skip rows marked as discarded
            if ( ! empty( $row['discard'] ) ) {
                $results['skipped']++;
                continue;
            }

            $data = $row['data'] ?? array();
            $row_num = $row['row_num'] ?? 0;

            // Validate required fields
            if ( empty( $data['title'] ) ) {
                $results['errors'][] = sprintf(
                    __( 'Row %d: Still missing event title.', 'societypress' ),
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

        wp_send_json_success( $results );
    }

    /**
     * Download CSV template via AJAX.
     *
     * WHY: Gives users a properly formatted template with example data,
     *      reducing errors from incorrect formatting (especially for slots).
     */
    public function ajax_download_template(): void {
        check_ajax_referer( 'societypress_import_events', 'nonce' );

        if ( ! current_user_can( 'manage_society_members' ) ) {
            wp_die( __( 'Permission denied.', 'societypress' ) );
        }

        // Set headers for CSV download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=events-import-template.csv' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // Write header row with all field names
        $headers = array(
            'title',
            'description',
            'date',
            'time',
            'end_time',
            'location',
            'address',
            'instructors',
            'category',
            'registration_required',
            'recurring_type',
            'recurring_week',
            'recurring_day',
            'recurring_end',
            'slots',
        );
        fputcsv( $output, $headers );

        // Write example rows showing different scenarios
        // Example 1: Simple one-time event
        fputcsv( $output, array(
            'General Membership Meeting',
            'Monthly meeting for all members',
            '2026-03-21',
            '10:00',
            '12:00',
            'Dwyer Center Roosevelt Auditorium',
            '123 Main St, Springfield, IL 62701',
            '',
            'Meetings',
            'no',
            '',
            '',
            '',
            '',
            '',
        ) );

        // Example 2: Monthly recurring event with specific day
        fputcsv( $output, array(
            'Board Meeting',
            'Monthly board of directors meeting',
            '2026-02-14',
            '14:00',
            '16:00',
            'Conference Room',
            '',
            '',
            'Leadership',
            'yes',
            'monthly',
            '2nd',
            'Friday',
            '2026-12-31',
            '',
        ) );

        // Example 3: Event with time slots (DNA Consult Hours style)
        fputcsv( $output, array(
            'DNA Consult Hours',
            'One-on-one DNA consultation sessions',
            '2026-02-14',
            '10:00',
            '13:00',
            'Dwyer Center Classroom',
            '123 Main St, Springfield, IL 62701',
            'Jane Smith',
            'Classes',
            'yes',
            'monthly',
            '2nd',
            'Saturday',
            '2026-12-31',
            '10:00-11:00|20|Session 1;11:00-12:00|20|Session 2;12:00-13:00|20|Session 3',
        ) );

        fclose( $output );
        exit;
    }

    /**
     * Render the import page.
     *
     * WHY: Multi-step wizard interface guides users through the import process,
     *      making it accessible even for non-technical society administrators.
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

                    <p class="template-download">
                        <button type="button" class="button" id="download-template-btn">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Download Template CSV', 'societypress' ); ?>
                        </button>
                        <span class="description"><?php esc_html_e( 'Get a sample file with all supported columns and example data.', 'societypress' ); ?></span>
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
                                <th><?php esc_html_e( 'Recurring', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Slots', 'societypress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody id="preview-body">
                        </tbody>
                    </table>

                    <div class="import-options">
                        <h3><?php esc_html_e( 'Import Options', 'societypress' ); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th><?php esc_html_e( 'Duplicates', 'societypress' ); ?></th>
                                <td>
                                    <label>
                                        <input type="radio" name="duplicate_handling" value="skip" checked>
                                        <?php esc_html_e( 'Skip duplicates (same title and date)', 'societypress' ); ?>
                                    </label><br>
                                    <label>
                                        <input type="radio" name="duplicate_handling" value="update">
                                        <?php esc_html_e( 'Update existing events', 'societypress' ); ?>
                                    </label><br>
                                    <label>
                                        <input type="radio" name="duplicate_handling" value="create">
                                        <?php esc_html_e( 'Create all (allow duplicates)', 'societypress' ); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <p class="submit">
                        <button type="button" class="button" id="back-to-mapping">
                            <?php esc_html_e( 'Back', 'societypress' ); ?>
                        </button>
                        <button type="button" class="button button-primary button-large" id="run-import-btn">
                            <?php esc_html_e( 'Import Events', 'societypress' ); ?>
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
                        <!-- Populated by JavaScript -->
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
            var problemRows = [];
            var importedCount = 0;

            // Download template button
            $('#download-template-btn').on('click', function() {
                window.location.href = ajaxurl + '?action=societypress_download_events_template&nonce=' + $('#import_nonce').val();
            });

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

            // Drag and drop support
            var uploadArea = $('#upload-area');
            uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#import-file')[0].files = files;
                    $('#import-file').trigger('change');
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
                    html += '<td><code>' + escapeHtml(sample.substring(0, 50)) + (sample.length > 50 ? '...' : '') + '</code></td>';
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
                    // Format recurring display
                    var recurring = '';
                    if (row.recurring_type) {
                        recurring = row.recurring_type;
                        if (row.recurring_week && row.recurring_day) {
                            recurring += ' (' + row.recurring_week + ' ' + row.recurring_day + ')';
                        }
                    }

                    // Format slots display
                    var slots = '';
                    if (row.slots) {
                        var slotCount = (row.slots.match(/;/g) || []).length + 1;
                        slots = slotCount + ' <?php esc_html_e( 'slot(s)', 'societypress' ); ?>';
                    }

                    html += '<tr>';
                    html += '<td>' + escapeHtml(row.title || '') + '</td>';
                    html += '<td>' + escapeHtml(row.date || '') + '</td>';
                    html += '<td>' + escapeHtml(row.time || '') + (row.end_time ? ' - ' + escapeHtml(row.end_time) : '') + '</td>';
                    html += '<td>' + escapeHtml(row.location || '') + '</td>';
                    html += '<td>' + escapeHtml(row.category || '') + '</td>';
                    html += '<td>' + escapeHtml(recurring) + '</td>';
                    html += '<td>' + escapeHtml(slots) + '</td>';
                    html += '</tr>';
                });
                $('#preview-body').html(html);
            }

            // Run import
            $('#run-import-btn').on('click', function() {
                $(this).prop('disabled', true).text('<?php esc_html_e( 'Importing...', 'societypress' ); ?>');

                var duplicateHandling = $('input[name="duplicate_handling"]:checked').val();

                $.post(ajaxurl, {
                    action: 'societypress_import_events',
                    nonce: $('#import_nonce').val(),
                    file: importFile,
                    mapping: importMapping,
                    options: {
                        skip_duplicates: duplicateHandling === 'skip',
                        update_existing: duplicateHandling === 'update'
                    }
                }, function(response) {
                    if (response.success) {
                        importedCount = response.data.imported;
                        problemRows = response.data.problem_rows || [];

                        if (problemRows.length > 0) {
                            // Show problem rows for review
                            showProblemRowsStep(response.data);
                        } else {
                            // No problems, show results
                            showResults(response.data);
                        }
                    } else {
                        alert(response.data.message);
                        $('#run-import-btn').prop('disabled', false).text('<?php esc_html_e( 'Import Events', 'societypress' ); ?>');
                    }
                });
            });

            function showProblemRowsStep(data) {
                var summaryHtml = '<div class="notice notice-warning"><p>';
                summaryHtml += '<strong>' + data.imported + '</strong> <?php esc_html_e( 'events imported successfully.', 'societypress' ); ?> ';
                summaryHtml += '<strong>' + data.problem_rows.length + '</strong> <?php esc_html_e( 'rows need review.', 'societypress' ); ?>';
                if (data.updated > 0) {
                    summaryHtml += ' <strong>' + data.updated + '</strong> <?php esc_html_e( 'events updated.', 'societypress' ); ?>';
                }
                summaryHtml += '</p></div>';
                $('#review-summary').html(summaryHtml);

                var rowsHtml = '';
                data.problem_rows.forEach(function(row, idx) {
                    rowsHtml += '<div class="problem-row" data-index="' + idx + '">';
                    rowsHtml += '<div class="problem-row-header">';
                    rowsHtml += '<strong><?php esc_html_e( 'Row', 'societypress' ); ?> ' + row.row_num + ':</strong> ';
                    rowsHtml += '<span class="problem-issue">' + escapeHtml(row.issue) + '</span>';
                    rowsHtml += '<button type="button" class="button button-small discard-row-btn"><?php esc_html_e( 'Discard', 'societypress' ); ?></button>';
                    rowsHtml += '</div>';
                    rowsHtml += '<div class="problem-row-fields">';
                    rowsHtml += '<label><?php esc_html_e( 'Title:', 'societypress' ); ?> <input type="text" class="regular-text" name="title" value="' + escapeHtml(row.data.title || '') + '"></label><br>';
                    rowsHtml += '<label><?php esc_html_e( 'Date:', 'societypress' ); ?> <input type="text" name="date" value="' + escapeHtml(row.data.date || '') + '"></label>';
                    rowsHtml += '<label><?php esc_html_e( 'Time:', 'societypress' ); ?> <input type="text" name="time" value="' + escapeHtml(row.data.time || '') + '"></label>';
                    rowsHtml += '<label><?php esc_html_e( 'Location:', 'societypress' ); ?> <input type="text" name="location" value="' + escapeHtml(row.data.location || '') + '"></label>';
                    rowsHtml += '</div>';
                    rowsHtml += '</div>';
                });
                $('#problem-rows-container').html(rowsHtml);

                $('#step-preview').hide();
                $('#step-review').show();
            }

            // Discard/restore problem row
            $(document).on('click', '.discard-row-btn', function() {
                var $row = $(this).closest('.problem-row');
                var idx = $row.data('index');

                if ($row.hasClass('discarded')) {
                    $row.removeClass('discarded');
                    $(this).text('<?php esc_html_e( 'Discard', 'societypress' ); ?>');
                    problemRows[idx].discard = false;
                } else {
                    $row.addClass('discarded');
                    $(this).text('<?php esc_html_e( 'Restore', 'societypress' ); ?>');
                    problemRows[idx].discard = true;
                }
            });

            // Skip all problem rows
            $('#skip-problem-rows-btn').on('click', function() {
                if (confirm('<?php esc_html_e( 'Skip all problem rows and finish?', 'societypress' ); ?>')) {
                    showResults({
                        imported: importedCount,
                        skipped: problemRows.length,
                        errors: []
                    });
                }
            });

            // Commit fixed problem rows
            $('#commit-fixes-btn').on('click', function() {
                // Gather fixed data from form
                var fixedRows = [];
                $('.problem-row').each(function() {
                    var idx = $(this).data('index');
                    var originalRow = problemRows[idx];
                    var $fields = $(this).find('.problem-row-fields');

                    var fixedData = $.extend({}, originalRow.data);
                    fixedData.title = $fields.find('input[name="title"]').val();
                    fixedData.date = $fields.find('input[name="date"]').val();
                    fixedData.time = $fields.find('input[name="time"]').val();
                    fixedData.location = $fields.find('input[name="location"]').val();

                    fixedRows.push({
                        row_num: originalRow.row_num,
                        data: fixedData,
                        discard: $(this).hasClass('discarded')
                    });
                });

                $(this).prop('disabled', true).text('<?php esc_html_e( 'Importing...', 'societypress' ); ?>');

                $.post(ajaxurl, {
                    action: 'societypress_commit_event_problem_rows',
                    nonce: $('#import_nonce').val(),
                    rows: fixedRows
                }, function(response) {
                    if (response.success) {
                        showResults({
                            imported: importedCount + response.data.imported,
                            skipped: response.data.skipped,
                            errors: response.data.errors
                        });
                    } else {
                        alert(response.data.message);
                        $('#commit-fixes-btn').prop('disabled', false).text('<?php esc_html_e( 'Import Fixed Rows', 'societypress' ); ?>');
                    }
                });
            });

            function showResults(data) {
                var html = '<div class="notice notice-success"><p>';
                html += '<strong><?php esc_html_e( 'Successfully imported:', 'societypress' ); ?></strong> ' + data.imported + ' <?php esc_html_e( 'events', 'societypress' ); ?>';
                if (data.updated > 0) {
                    html += '<br><strong><?php esc_html_e( 'Updated:', 'societypress' ); ?></strong> ' + data.updated;
                }
                if (data.skipped > 0) {
                    html += '<br><strong><?php esc_html_e( 'Skipped:', 'societypress' ); ?></strong> ' + data.skipped;
                }
                html += '</p></div>';

                if (data.errors && data.errors.length > 0) {
                    html += '<div class="notice notice-warning"><p><strong><?php esc_html_e( 'Warnings:', 'societypress' ); ?></strong></p><ul>';
                    data.errors.forEach(function(err) {
                        html += '<li>' + escapeHtml(err) + '</li>';
                    });
                    html += '</ul></div>';
                }

                $('#import-results').html(html);
                $('#step-review').hide();
                $('#step-preview').hide();
                $('#step-results').show();
            }

            // Import more button
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
        .societypress-import-events .upload-area:hover,
        .societypress-import-events .upload-area.dragover {
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
        .societypress-import-events .template-download {
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f6fc;
            border-left: 4px solid #2271b1;
        }
        .societypress-import-events .template-download .button {
            margin-right: 10px;
        }
        .societypress-import-events .template-download .button .dashicons {
            margin-right: 5px;
            vertical-align: text-bottom;
        }
        .societypress-import-events .import-options {
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        .societypress-import-events .import-options h3 {
            margin-top: 0;
        }
        .societypress-import-events .problem-row {
            margin-bottom: 15px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-left: 4px solid #dba617;
        }
        .societypress-import-events .problem-row.discarded {
            opacity: 0.5;
            border-left-color: #999;
        }
        .societypress-import-events .problem-row-header {
            margin-bottom: 10px;
        }
        .societypress-import-events .problem-issue {
            color: #996800;
            margin-right: 10px;
        }
        .societypress-import-events .problem-row-fields label {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        .societypress-import-events .problem-row-fields input {
            margin-left: 5px;
        }
        .societypress-import-events .discard-row-btn {
            float: right;
        }
        .societypress-import-events .societypress-review-summary {
            margin-bottom: 20px;
        }
        </style>
        <?php
    }
}
