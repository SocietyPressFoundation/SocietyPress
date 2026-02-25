<?php
/**
 * Template Name: My Account
 *
 * WHY: This is the frontend profile page that replaces wp-admin/profile.php
 * for regular members. It lets users manage every aspect of their own
 * account without ever seeing the WordPress backend.
 *
 * Sections:
 *   1. Profile Photo — upload / change / remove
 *   2. Personal Information — name fields (prefix, first, preferred, middle,
 *      last, maiden, suffix) and date of birth
 *   3. Contact Information — email, phone, cell, website
 *   4. Address — street, city, state, zip, country
 *   5. Seasonal Address — toggle + seasonal address fields + date range
 *   6. Communication Preferences — print newsletter, email categories
 *   7. Directory Privacy — which fields appear in the membership directory
 *   8. Change Password — current + new + confirm
 *
 * Fields the member CANNOT change (admin-only):
 *   - member_number, status, tier_id, household_id
 *   - join_date, expiration_date
 *   - created_at, updated_at
 *
 * All form processing happens in functions.php via sp_handle_account_forms()
 * hooked to template_redirect. By the time this template renders, any
 * submitted changes have already been saved.
 *
 * @package SocietyPress
 */

// Redirect logged-out visitors to login, then back here after they log in
if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();

$user = wp_get_current_user();

// -------------------------------------------------------------------------
// Load the member record from sp_members (if one exists for this user).
// Not every WP user is necessarily a member — the admin account, for example,
// might not have a row in sp_members. We handle that gracefully below.
// -------------------------------------------------------------------------
global $wpdb;
$member = $wpdb->get_row(
    $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}sp_members WHERE user_id = %d", $user->ID ),
    ARRAY_A
);

// Profile photo: check sp_members.photo_url first, then WP user meta, then Gravatar
$custom_photo = '';
if ( $member && ! empty( $member['photo_url'] ) ) {
    $custom_photo = $member['photo_url'];
} else {
    $custom_photo = get_user_meta( $user->ID, 'sp_profile_photo_url', true );
}
$photo_url = $custom_photo ? $custom_photo : get_avatar_url( $user->ID, [ 'size' => 150 ] );

// Flash messages set by sp_handle_account_forms() in functions.php
$success = isset( $_GET['sp-updated'] ) ? sanitize_text_field( $_GET['sp-updated'] ) : '';
$error   = isset( $_GET['sp-error'] )   ? sanitize_text_field( $_GET['sp-error'] )   : '';

// Helper: safely get a member field or empty string
// WHY: Avoids repeated isset() checks throughout the template
function sp_m( $member, $field ) {
    return ( $member && isset( $member[ $field ] ) ) ? $member[ $field ] : '';
}
?>

<div class="site-content">
    <div class="content-area-full">

        <article class="sp-my-account">
            <header class="entry-header">
                <h1 class="entry-title">My Account</h1>
            </header>

            <?php
            // ----------------------------------------------------------------
            // SUCCESS / ERROR MESSAGES
            // WHY: We use URL parameters (sp-updated, sp-error) instead of
            // session variables because WordPress doesn't start sessions by
            // default. The Post-Redirect-Get pattern prevents form
            // resubmission on browser refresh.
            // ----------------------------------------------------------------
            ?>
            <?php if ( $success ) : ?>
                <div class="sp-notice sp-notice--success">
                    <?php
                    switch ( $success ) {
                        case 'profile':
                            echo 'Your information has been updated.';
                            break;
                        case 'photo':
                            echo 'Your profile photo has been updated.';
                            break;
                        case 'photo-removed':
                            echo 'Your profile photo has been removed.';
                            break;
                        case 'password':
                            echo 'Your password has been changed.';
                            break;
                        case 'preferences':
                            echo 'Your preferences have been saved.';
                            break;
                        case 'privacy':
                            echo 'Your directory privacy settings have been saved.';
                            break;
                        default:
                            echo 'Changes saved.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ( $error ) : ?>
                <div class="sp-notice sp-notice--error">
                    <?php
                    switch ( $error ) {
                        case 'password-mismatch':
                            echo 'The new passwords do not match. Please try again.';
                            break;
                        case 'password-wrong':
                            echo 'Your current password is incorrect.';
                            break;
                        case 'password-short':
                            echo 'Your new password must be at least 8 characters.';
                            break;
                        case 'email-invalid':
                            echo 'Please enter a valid email address.';
                            break;
                        case 'email-taken':
                            echo 'That email address is already in use by another account.';
                            break;
                        case 'photo-type':
                            echo 'Please upload a JPG, PNG, or GIF image.';
                            break;
                        case 'photo-size':
                            echo 'The image is too large. Maximum file size is 2 MB.';
                            break;
                        case 'photo-upload':
                            echo 'There was a problem uploading your photo. Please try again.';
                            break;
                        case 'no-member':
                            echo 'Your membership record was not found. Please contact an administrator.';
                            break;
                        case 'nonce':
                            echo 'Your session has expired. Please try again.';
                            break;
                        default:
                            echo 'Something went wrong. Please try again.';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 1: PROFILE PHOTO
            // ================================================================
            ?>
            <section class="sp-account-section" id="photo">
                <h2>Profile Photo</h2>

                <div class="sp-photo-section">
                    <div class="sp-photo-current">
                        <img src="<?php echo esc_url( $photo_url ); ?>"
                             alt="<?php echo esc_attr( $user->display_name ); ?>"
                             class="sp-photo-preview" />
                    </div>

                    <div class="sp-photo-actions">
                        <form method="post" enctype="multipart/form-data" class="sp-photo-form">
                            <?php wp_nonce_field( 'sp_update_photo', 'sp_photo_nonce' ); ?>

                            <label for="sp-photo-upload" class="sp-button sp-button--secondary">
                                <?php echo $custom_photo ? 'Change Photo' : 'Upload Photo'; ?>
                            </label>
                            <input type="file"
                                   id="sp-photo-upload"
                                   name="sp_profile_photo"
                                   accept="image/jpeg,image/png,image/gif"
                                   class="sp-file-input"
                                   onchange="this.form.submit();" />

                            <input type="hidden" name="sp_action" value="update_photo" />
                        </form>

                        <?php if ( $custom_photo ) : ?>
                            <form method="post" class="sp-photo-remove-form">
                                <?php wp_nonce_field( 'sp_remove_photo', 'sp_remove_photo_nonce' ); ?>
                                <input type="hidden" name="sp_action" value="remove_photo" />
                                <button type="submit" class="sp-button sp-button--text">Remove Photo</button>
                            </form>
                        <?php endif; ?>

                        <p class="sp-photo-hint">JPG, PNG, or GIF. Max 2 MB.</p>
                    </div>
                </div>
            </section>

            <?php
            // ================================================================
            // SECTION 2: PERSONAL INFORMATION
            // WHY: These are the name fields and DOB. Members may need to
            // update these for marriage, divorce, legal name changes, or
            // just to fix a typo from import.
            // ================================================================
            ?>
            <section class="sp-account-section" id="info">
                <h2>Personal Information</h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_profile', 'sp_profile_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_profile" />

                    <!-- Prefix and Suffix on one row -->
                    <div class="sp-form-row sp-form-row--thirds">
                        <div class="sp-form-field">
                            <label for="sp-prefix">Prefix</label>
                            <select id="sp-prefix" name="prefix">
                                <option value="">—</option>
                                <?php
                                $prefixes = [ 'Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.', 'Rev.', 'Hon.' ];
                                foreach ( $prefixes as $p ) {
                                    $selected = ( sp_m( $member, 'prefix' ) === $p ) ? ' selected' : '';
                                    echo '<option value="' . esc_attr( $p ) . '"' . $selected . '>' . esc_html( $p ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-suffix">Suffix</label>
                            <select id="sp-suffix" name="suffix">
                                <option value="">—</option>
                                <?php
                                $suffixes = [ 'Jr.', 'Sr.', 'II', 'III', 'IV', 'Esq.', 'Ph.D.', 'M.D.' ];
                                foreach ( $suffixes as $s ) {
                                    $selected = ( sp_m( $member, 'suffix' ) === $s ) ? ' selected' : '';
                                    echo '<option value="' . esc_attr( $s ) . '"' . $selected . '>' . esc_html( $s ) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <!-- First / Middle / Last -->
                    <div class="sp-form-row sp-form-row--thirds">
                        <div class="sp-form-field">
                            <label for="sp-first-name">First Name</label>
                            <input type="text" id="sp-first-name" name="first_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'first_name' ) ?: $user->first_name ); ?>"
                                   required />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-middle-name">Middle Name</label>
                            <input type="text" id="sp-middle-name" name="middle_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'middle_name' ) ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-last-name">Last Name</label>
                            <input type="text" id="sp-last-name" name="last_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'last_name' ) ?: $user->last_name ); ?>"
                                   required />
                        </div>
                    </div>

                    <!-- Preferred Name / Maiden Name -->
                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-preferred-name">Preferred Name</label>
                            <input type="text" id="sp-preferred-name" name="preferred_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'preferred_name' ) ); ?>" />
                            <p class="sp-field-hint">What you'd like to be called (e.g., "Bob" instead of "Robert").</p>
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-maiden-name">Maiden Name</label>
                            <input type="text" id="sp-maiden-name" name="maiden_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'maiden_name' ) ); ?>" />
                        </div>
                    </div>

                    <!-- Date of Birth -->
                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-dob">Date of Birth</label>
                            <input type="date" id="sp-dob" name="date_of_birth"
                                   value="<?php echo esc_attr( sp_m( $member, 'date_of_birth' ) ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <!-- Spacer so DOB doesn't stretch full width -->
                        </div>
                    </div>

                    <button type="submit" class="sp-button">Save Personal Information</button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 3: CONTACT INFORMATION
            // WHY: Separated from personal info because contact details
            // change more frequently (new email, new phone) while names
            // are mostly stable.
            // ================================================================
            ?>
            <section class="sp-account-section" id="contact">
                <h2>Contact Information</h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_contact', 'sp_contact_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_contact" />

                    <div class="sp-form-field">
                        <label for="sp-email">Email Address</label>
                        <input type="email" id="sp-email" name="user_email"
                               value="<?php echo esc_attr( $user->user_email ); ?>"
                               required />
                        <p class="sp-field-hint">This is used for logging in and receiving communications.</p>
                    </div>

                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-phone">Home Phone</label>
                            <input type="tel" id="sp-phone" name="phone"
                                   value="<?php echo esc_attr( sp_m( $member, 'phone' ) ); ?>"
                                   placeholder="(210) 555-1234" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-cell">Cell Phone</label>
                            <input type="tel" id="sp-cell" name="cell"
                                   value="<?php echo esc_attr( sp_m( $member, 'cell' ) ); ?>"
                                   placeholder="(210) 555-1234" />
                        </div>
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-website">Website</label>
                        <input type="url" id="sp-website" name="website"
                               value="<?php echo esc_attr( sp_m( $member, 'website' ) ); ?>"
                               placeholder="https://" />
                    </div>

                    <button type="submit" class="sp-button">Save Contact Information</button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 4: ADDRESS
            // ================================================================
            ?>
            <section class="sp-account-section" id="address">
                <h2>Mailing Address</h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_address', 'sp_address_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_address" />

                    <div class="sp-form-field">
                        <label for="sp-address1">Street Address</label>
                        <input type="text" id="sp-address1" name="address_1"
                               value="<?php echo esc_attr( sp_m( $member, 'address_1' ) ); ?>" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-address2">Address Line 2</label>
                        <input type="text" id="sp-address2" name="address_2"
                               value="<?php echo esc_attr( sp_m( $member, 'address_2' ) ); ?>"
                               placeholder="Apt, Suite, Unit, etc." />
                    </div>

                    <div class="sp-form-row sp-form-row--city-state">
                        <div class="sp-form-field sp-form-field--city">
                            <label for="sp-city">City</label>
                            <input type="text" id="sp-city" name="city"
                                   value="<?php echo esc_attr( sp_m( $member, 'city' ) ); ?>" />
                        </div>
                        <div class="sp-form-field sp-form-field--state">
                            <label for="sp-state">State</label>
                            <input type="text" id="sp-state" name="state"
                                   value="<?php echo esc_attr( sp_m( $member, 'state' ) ); ?>"
                                   maxlength="100" />
                        </div>
                        <div class="sp-form-field sp-form-field--zip">
                            <label for="sp-postal">Zip / Postal Code</label>
                            <input type="text" id="sp-postal" name="postal_code"
                                   value="<?php echo esc_attr( sp_m( $member, 'postal_code' ) ); ?>" />
                        </div>
                    </div>

                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-country">Country</label>
                            <input type="text" id="sp-country" name="country"
                                   value="<?php echo esc_attr( sp_m( $member, 'country' ) ?: 'US' ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <!-- spacer -->
                        </div>
                    </div>

                    <!-- Seasonal address toggle -->
                    <div class="sp-form-field sp-seasonal-toggle">
                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="seasonal" value="1"
                                   id="sp-seasonal"
                                   <?php checked( sp_m( $member, 'seasonal' ), '1' ); ?> />
                            I have a seasonal / alternate address
                        </label>
                    </div>

                    <!--
                        WHY the seasonal fields are inside the same form:
                        Keeps it simple — one Save button for all address info.
                        The JS toggle just shows/hides the seasonal fields.
                        If JS is disabled, the fields are always visible (safe fallback).
                    -->
                    <div class="sp-seasonal-fields" id="sp-seasonal-fields"
                         <?php echo sp_m( $member, 'seasonal' ) ? '' : 'style="display:none;"'; ?>>

                        <h3 class="sp-subsection-heading">Seasonal Address</h3>

                        <div class="sp-form-row sp-form-row--half">
                            <div class="sp-form-field">
                                <label for="sp-seasonal-from">From (Month)</label>
                                <select id="sp-seasonal-from" name="seasonal_from">
                                    <option value="">—</option>
                                    <?php
                                    $months = [
                                        '01' => 'January', '02' => 'February', '03' => 'March',
                                        '04' => 'April',   '05' => 'May',      '06' => 'June',
                                        '07' => 'July',    '08' => 'August',   '09' => 'September',
                                        '10' => 'October', '11' => 'November', '12' => 'December',
                                    ];
                                    foreach ( $months as $num => $name ) {
                                        $selected = ( sp_m( $member, 'seasonal_from' ) === $num ) ? ' selected' : '';
                                        echo '<option value="' . $num . '"' . $selected . '>' . $name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="sp-form-field">
                                <label for="sp-seasonal-to">To (Month)</label>
                                <select id="sp-seasonal-to" name="seasonal_to">
                                    <option value="">—</option>
                                    <?php
                                    foreach ( $months as $num => $name ) {
                                        $selected = ( sp_m( $member, 'seasonal_to' ) === $num ) ? ' selected' : '';
                                        echo '<option value="' . $num . '"' . $selected . '>' . $name . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="sp-form-field">
                            <label for="sp-seasonal-address1">Street Address</label>
                            <input type="text" id="sp-seasonal-address1" name="seasonal_address_1"
                                   value="<?php echo esc_attr( sp_m( $member, 'seasonal_address_1' ) ); ?>" />
                        </div>

                        <div class="sp-form-row sp-form-row--city-state">
                            <div class="sp-form-field sp-form-field--city">
                                <label for="sp-seasonal-city">City</label>
                                <input type="text" id="sp-seasonal-city" name="seasonal_city"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_city' ) ); ?>" />
                            </div>
                            <div class="sp-form-field sp-form-field--state">
                                <label for="sp-seasonal-state">State</label>
                                <input type="text" id="sp-seasonal-state" name="seasonal_state"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_state' ) ); ?>" />
                            </div>
                            <div class="sp-form-field sp-form-field--zip">
                                <label for="sp-seasonal-postal">Zip / Postal Code</label>
                                <input type="text" id="sp-seasonal-postal" name="seasonal_postal_code"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_postal_code' ) ); ?>" />
                            </div>
                        </div>

                        <div class="sp-form-row sp-form-row--half">
                            <div class="sp-form-field">
                                <label for="sp-seasonal-country">Country</label>
                                <input type="text" id="sp-seasonal-country" name="seasonal_country"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_country' ) ?: 'US' ); ?>" />
                            </div>
                            <div class="sp-form-field">
                                <!-- spacer -->
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="sp-button">Save Address</button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 5: COMMUNICATION PREFERENCES
            // WHY: Let members control what emails they receive. Respecting
            // preferences builds trust and reduces unsubscribe complaints.
            // ================================================================
            ?>
            <?php if ( $member ) : ?>
            <section class="sp-account-section" id="preferences">
                <h2>Communication Preferences</h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_preferences', 'sp_preferences_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_preferences" />

                    <div class="sp-checkbox-group">
                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="receive_print" value="1"
                                   <?php checked( sp_m( $member, 'receive_print' ), '1' ); ?> />
                            Receive print newsletter by mail
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_notices" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_notices' ), '1' ); ?> />
                            Email me general notices and announcements
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_events" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_events' ), '1' ); ?> />
                            Email me about upcoming events
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_newsletters" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_newsletters' ), '1' ); ?> />
                            Email me when new newsletters are published
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_surnames" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_surnames' ), '1' ); ?> />
                            Email me when someone is researching one of my surnames
                        </label>
                    </div>

                    <button type="submit" class="sp-button">Save Preferences</button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 6: DIRECTORY PRIVACY
            // WHY: Two-layer privacy — the society controls which columns the
            // directory shows at all, and each member controls which of their
            // OWN fields are visible within those columns. This section lets
            // the member manage their layer.
            // ================================================================
            ?>
            <?php if ( $member ) : ?>
            <section class="sp-account-section" id="privacy">
                <h2>Directory Privacy</h2>
                <p class="sp-section-description">
                    Choose which of your details are visible to other members in the membership directory.
                    Unchecked items will be hidden.
                </p>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_privacy', 'sp_privacy_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_privacy" />

                    <div class="sp-checkbox-group">
                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_name" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_name' ), '1' ); ?> />
                            Show my name
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_address" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_address' ), '1' ); ?> />
                            Show my address
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_phone" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_phone' ), '1' ); ?> />
                            Show my phone number
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_email" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_email' ), '1' ); ?> />
                            Show my email address
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_website" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_website' ), '1' ); ?> />
                            Show my website
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_photo" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_photo' ), '1' ); ?> />
                            Show my photo
                        </label>
                    </div>

                    <button type="submit" class="sp-button">Save Privacy Settings</button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 7: CHANGE PASSWORD
            // WHY: Separated from other forms so members can update their
            // info without thinking about passwords, and vice versa.
            // Requires current password as a security measure.
            // ================================================================
            ?>

            <!-- ============================================================ -->
            <!-- RESEARCH SURNAMES                                            -->
            <!-- WHY: Members register the surnames they're researching so    -->
            <!-- other members researching the same names can connect. This   -->
            <!-- is the core social feature of a genealogical society.        -->
            <!-- ============================================================ -->

            <section class="sp-account-section" id="surnames">
                <h2>Research Surnames</h2>
                <p style="color: var(--sp-text-muted, #666); margin-bottom: 16px;">
                    Add the surnames you're researching so other members can find and contact you.
                </p>

                <?php
                // Load this member's current research surnames
                $surnames = $wpdb->get_results( $wpdb->prepare(
                    "SELECT id, surname, location FROM {$wpdb->prefix}sp_member_surnames WHERE user_id = %d ORDER BY surname ASC",
                    $user->ID
                ) );
                ?>

                <!-- Existing surnames -->
                <div id="sp-surname-list" style="margin-bottom: 16px;">
                    <?php if ( ! empty( $surnames ) ) : ?>
                        <?php foreach ( $surnames as $sn ) : ?>
                            <div class="sp-surname-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px; padding: 8px 12px; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px;">
                                <strong style="flex: 1;"><?php echo esc_html( $sn->surname ); ?></strong>
                                <?php if ( $sn->location ) : ?>
                                    <span style="color: #666; font-size: 13px;"><?php echo esc_html( $sn->location ); ?></span>
                                <?php endif; ?>
                                <form method="post" style="margin: 0;" onsubmit="return confirm('Remove this surname?');">
                                    <?php wp_nonce_field( 'sp_remove_surname', 'sp_surname_nonce' ); ?>
                                    <input type="hidden" name="sp_action" value="remove_surname">
                                    <input type="hidden" name="surname_id" value="<?php echo esc_attr( $sn->id ); ?>">
                                    <button type="submit" style="background: none; border: none; color: #b32d2e; cursor: pointer; font-size: 13px; padding: 2px 6px;">&times;</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p style="color: #999; font-style: italic;">No research surnames added yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Add new surname form -->
                <form method="post" class="sp-form" style="display: flex; gap: 8px; align-items: flex-end; flex-wrap: wrap;">
                    <?php wp_nonce_field( 'sp_add_surname', 'sp_surname_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="add_surname">
                    <div style="flex: 1; min-width: 150px;">
                        <label for="sp-new-surname" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Surname</label>
                        <input type="text" id="sp-new-surname" name="new_surname" required placeholder="e.g., STRICKLIN" style="width: 100%; text-transform: uppercase;">
                    </div>
                    <div style="flex: 1; min-width: 150px;">
                        <label for="sp-new-location" style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px;">Location (optional)</label>
                        <input type="text" id="sp-new-location" name="new_location" placeholder="e.g., Bexar County, TX">
                    </div>
                    <button type="submit" class="sp-button" style="white-space: nowrap;">Add Surname</button>
                </form>
            </section>

            <!-- ============================================================ -->
            <!-- MY EVENTS                                                    -->
            <!-- WHY: Members need to see what events they're registered for  -->
            <!-- and be able to cancel if their plans change. Reduces admin   -->
            <!-- workload — members handle their own registrations.           -->
            <!-- ============================================================ -->

            <section class="sp-account-section" id="events">
                <h2>My Events</h2>

                <?php
                // Get upcoming event registrations for this member
                $upcoming_regs = $wpdb->get_results( $wpdb->prepare(
                    "SELECT r.id AS reg_id, r.status AS reg_status, r.party_size, r.fee_amount,
                            r.payment_status, r.registered_at,
                            e.id AS event_id, e.title, e.event_date, e.start_time, e.end_time,
                            e.location_name, e.is_virtual, e.virtual_url
                     FROM {$wpdb->prefix}sp_event_registrations r
                     INNER JOIN {$wpdb->prefix}sp_events e ON r.event_id = e.id
                     WHERE r.user_id = %d
                       AND r.status IN ('confirmed', 'waitlisted')
                       AND e.event_date >= CURDATE()
                     ORDER BY e.event_date ASC, e.start_time ASC",
                    $user->ID
                ) );

                // Get past events (last 6 months)
                $past_regs = $wpdb->get_results( $wpdb->prepare(
                    "SELECT r.id AS reg_id, r.status AS reg_status, r.attended,
                            e.id AS event_id, e.title, e.event_date, e.start_time,
                            e.location_name
                     FROM {$wpdb->prefix}sp_event_registrations r
                     INNER JOIN {$wpdb->prefix}sp_events e ON r.event_id = e.id
                     WHERE r.user_id = %d
                       AND r.status IN ('confirmed', 'cancelled')
                       AND e.event_date < CURDATE()
                       AND e.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                     ORDER BY e.event_date DESC",
                    $user->ID
                ) );
                ?>

                <?php if ( ! empty( $upcoming_regs ) ) : ?>
                    <h3 style="font-size: 16px; margin-bottom: 12px;">Upcoming</h3>
                    <?php foreach ( $upcoming_regs as $reg ) : ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; margin-bottom: 8px; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 4px; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <strong><?php echo esc_html( $reg->title ); ?></strong><br>
                                <span style="color: #666; font-size: 13px;">
                                    <?php echo esc_html( date_i18n( 'l, F j, Y', strtotime( $reg->event_date ) ) ); ?>
                                    <?php if ( $reg->start_time ) : ?>
                                        at <?php echo esc_html( date_i18n( 'g:i A', strtotime( $reg->start_time ) ) ); ?>
                                    <?php endif; ?>
                                    <?php if ( $reg->location_name ) : ?>
                                        &mdash; <?php echo esc_html( $reg->location_name ); ?>
                                    <?php endif; ?>
                                </span>
                                <?php if ( $reg->reg_status === 'waitlisted' ) : ?>
                                    <span style="display: inline-block; margin-left: 8px; padding: 1px 8px; background: #fef8ee; color: #996800; border: 1px solid #dba617; border-radius: 3px; font-size: 12px;">Waitlisted</span>
                                <?php endif; ?>
                            </div>
                            <form method="post" style="margin: 0;" onsubmit="return confirm('Cancel your registration for this event?');">
                                <?php wp_nonce_field( 'sp_cancel_event_reg', 'sp_event_nonce' ); ?>
                                <input type="hidden" name="sp_action" value="cancel_event_registration">
                                <input type="hidden" name="registration_id" value="<?php echo esc_attr( $reg->reg_id ); ?>">
                                <button type="submit" class="sp-button" style="background: #b32d2e; font-size: 13px; padding: 6px 14px;">Cancel</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p style="color: #999; font-style: italic;">You're not registered for any upcoming events.</p>
                <?php endif; ?>

                <?php if ( ! empty( $past_regs ) ) : ?>
                    <h3 style="font-size: 16px; margin: 20px 0 12px;">Past Events (6 months)</h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e0e0e0; text-align: left;">
                                <th style="padding: 6px 8px;">Event</th>
                                <th style="padding: 6px 8px;">Date</th>
                                <th style="padding: 6px 8px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $past_regs as $past ) : ?>
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 6px 8px;"><?php echo esc_html( $past->title ); ?></td>
                                    <td style="padding: 6px 8px;"><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $past->event_date ) ) ); ?></td>
                                    <td style="padding: 6px 8px;">
                                        <?php
                                        if ( $past->reg_status === 'cancelled' ) {
                                            echo '<span style="color: #b32d2e;">Cancelled</span>';
                                        } elseif ( $past->attended === '1' ) {
                                            echo '<span style="color: #00a32a;">Attended</span>';
                                        } elseif ( $past->attended === '0' ) {
                                            echo '<span style="color: #996800;">No-show</span>';
                                        } else {
                                            echo '<span style="color: #666;">Registered</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>


            <section class="sp-account-section" id="password">
                <h2>Change Password</h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_password', 'sp_password_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_password" />

                    <div class="sp-form-field">
                        <label for="sp-current-password">Current Password</label>
                        <input type="password"
                               id="sp-current-password"
                               name="current_password"
                               required
                               autocomplete="current-password" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-password">New Password</label>
                        <input type="password"
                               id="sp-new-password"
                               name="new_password"
                               required
                               minlength="8"
                               autocomplete="new-password" />
                        <p class="sp-field-hint">At least 8 characters.</p>
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-confirm-password">Confirm New Password</label>
                        <input type="password"
                               id="sp-confirm-password"
                               name="confirm_password"
                               required
                               minlength="8"
                               autocomplete="new-password" />
                    </div>

                    <button type="submit" class="sp-button">Change Password</button>
                </form>
            </section>

        </article>

    </div>
</div>

<?php
// =========================================================================
// INLINE JAVASCRIPT
// WHY: Just two small interactions — toggling the seasonal address fields
// and auto-formatting phone numbers. Not worth loading a separate JS file
// for this little code. Placed at the bottom so the DOM is ready.
// =========================================================================
?>
<script>
(function() {
    // Toggle seasonal address fields visibility
    var toggle = document.getElementById('sp-seasonal');
    var fields = document.getElementById('sp-seasonal-fields');
    if (toggle && fields) {
        toggle.addEventListener('change', function() {
            fields.style.display = this.checked ? '' : 'none';
        });
    }

    // Auto-format phone numbers as user types: (210) 555-1234
    // WHY: Our users are octogenarians — they'll type digits and expect
    // the phone number to look right. This does it for them.
    function formatPhone(input) {
        input.addEventListener('input', function() {
            var digits = this.value.replace(/\D/g, '');
            if (digits.length === 0) {
                this.value = '';
            } else if (digits.length <= 3) {
                this.value = '(' + digits;
            } else if (digits.length <= 6) {
                this.value = '(' + digits.substring(0,3) + ') ' + digits.substring(3);
            } else {
                this.value = '(' + digits.substring(0,3) + ') ' + digits.substring(3,6) + '-' + digits.substring(6,10);
            }
        });
    }

    var phoneFields = document.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(formatPhone);
})();
</script>

<?php get_footer(); ?>
