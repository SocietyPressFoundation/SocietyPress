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
 *   6. Communication Preferences — print newsletter, email categories, blast opt-out
 *   7. Directory Privacy — which fields appear in the membership directory
 *   8. Interests & Skills — free-text fields for member expertise and hobbies
 *  8a. Research Surnames — surnames with county/state/country/year range + notes
 *  8b. Research Areas — geographic areas with type/year range/notes
 *  8c. Family Connections — read-only display of member relationships
 *   9. My Events — upcoming with cancel, past 6 months
 *  10. Change Password — current + new + confirm
 *
 * Fields the member CANNOT change (admin-only):
 *   - member_number, status, tier_id, household_id
 *   - join_date, expiration_date
 *   - created_at, updated_at
 *
 * All form processing happens in societypress.php via sp_handle_account_forms()
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
// Decrypt sensitive contact/address fields that are encrypted at rest.
// WHY: We need plaintext values for the form fields so the member can see
// and edit their own phone numbers and addresses.
if ( function_exists( 'sp_member_decrypt_row' ) ) {
    $member = sp_member_decrypt_row( $member );
}

// Profile photo: check sp_members.photo_url first, then WP user meta, then Gravatar
$custom_photo = '';
if ( $member && ! empty( $member['photo_url'] ) ) {
    $custom_photo = $member['photo_url'];
} else {
    $custom_photo = get_user_meta( $user->ID, 'sp_profile_photo_url', true );
}
$photo_url = $custom_photo ? $custom_photo : get_avatar_url( $user->ID, [ 'size' => 150 ] );

// Flash messages set by sp_handle_account_forms() in societypress.php
$success = isset( $_GET['sp-updated'] ) ? sanitize_text_field( $_GET['sp-updated'] ) : '';
$error   = isset( $_GET['sp-error'] )   ? sanitize_text_field( $_GET['sp-error'] )   : '';
$pending = isset( $_GET['sp-pending'] ) ? true : false;

// Helper: safely get a member field or empty string
// WHY: Avoids repeated isset() checks throughout the template
function sp_m( $member, $field ) {
    return ( $member && isset( $member[ $field ] ) ) ? $member[ $field ] : '';
}
?>

<!--
    WHY: Scoped styles for sections that were previously using inline styles
    (surnames, events). Keeps presentation in CSS where it belongs while staying
    self-contained in this template — consistent with how the rest of the page
    uses class-based styling defined in the theme's style.css.
-->
<style>
/* ---- Research Surnames ---- */
.sp-surname-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 6px;
    padding: 8px 12px;
    background: var(--sp-surface, #f9f9f9);
    border: 1px solid var(--sp-border, #e0e0e0);
    border-radius: 4px;
    flex-wrap: wrap;
}
.sp-surname-row strong {
    flex: 0 0 auto;
    min-width: 120px;
}
.sp-surname-row .sp-surname-detail {
    flex: 1;
    color: var(--sp-text-muted, #666);
    font-size: 13px;
}
.sp-surname-row .sp-surname-remove {
    background: none;
    border: none;
    color: var(--sp-danger, #b32d2e);
    cursor: pointer;
    font-size: 16px;
    padding: 2px 6px;
    line-height: 1;
}
.sp-surname-row .sp-surname-remove:hover {
    opacity: 0.7;
}
.sp-surname-list {
    margin-bottom: 16px;
}
.sp-surname-empty {
    color: var(--sp-text-muted, #999);
    font-style: italic;
}
.sp-surname-add-form {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.sp-surname-add-form .sp-form-field {
    flex: 1;
    min-width: 120px;
}
.sp-surname-add-form .sp-form-field--surname {
    flex: 1.5;
}
.sp-surname-add-form .sp-form-field--year {
    flex: 0 0 80px;
    min-width: 80px;
}

/* ---- My Events ---- */
.sp-events-subheading {
    font-size: 16px;
    margin-bottom: 12px;
}
.sp-events-subheading--past {
    margin-top: 20px;
}
.sp-event-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    margin-bottom: 8px;
    background: var(--sp-surface, #f9f9f9);
    border: 1px solid var(--sp-border, #e0e0e0);
    border-radius: 4px;
    flex-wrap: wrap;
    gap: 8px;
}
.sp-event-card__details {
    color: var(--sp-text-muted, #666);
    font-size: 13px;
}
.sp-event-badge--waitlisted {
    display: inline-block;
    margin-left: 8px;
    padding: 1px 8px;
    background: #fef8ee;
    color: #996800;
    border: 1px solid #dba617;
    border-radius: 3px;
    font-size: 12px;
}
.sp-event-card__actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-shrink: 0;
}
.sp-event-ics-btn {
    background: var(--sp-color-primary, #2271b1);
    font-size: 13px;
    padding: 6px 14px;
    text-decoration: none;
}
.sp-event-ics-btn:hover { opacity: 0.85; }
.sp-event-cancel-btn {
    background: var(--sp-danger, #b32d2e);
    font-size: 13px;
    padding: 6px 14px;
}
.sp-events-empty {
    color: var(--sp-text-muted, #999);
    font-style: italic;
}
.sp-events-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.sp-events-table thead tr {
    border-bottom: 2px solid var(--sp-border, #e0e0e0);
    text-align: left;
}
.sp-events-table th,
.sp-events-table td {
    padding: 6px 8px;
}
.sp-events-table tbody tr {
    border-bottom: 1px solid var(--sp-border, #e0e0e0);
}
.sp-status--cancelled { color: var(--sp-danger, #b32d2e); }
.sp-status--attended  { color: #00a32a; }
.sp-status--noshow    { color: #996800; }
.sp-status--registered { color: var(--sp-text-muted, #666); }

/* ---- Section description (reusable) ---- */
.sp-section-hint {
    color: var(--sp-text-muted, #666);
    margin-bottom: 16px;
}

/* ---- AJAX save feedback messages ---- */
/* WHY inline messages instead of a page-top banner: The member might be
   saving address info halfway down the page. A banner at the top wouldn't
   be visible. Inline messages appear right next to the save button. */
.sp-ajax-msg {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 4px;
    font-size: 14px;
    margin-right: 12px;
    animation: sp-fade-in 0.2s ease-out;
}
.sp-ajax-msg--success {
    background: #edfaef;
    color: #00703c;
    border: 1px solid #b8e6c0;
}
.sp-ajax-msg--error {
    background: #fef0f0;
    color: #b32d2e;
    border: 1px solid #f0c0c0;
}
@keyframes sp-fade-in {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div id="main-content" class="site-content">
    <div class="content-area-full">

        <article class="sp-my-account">
            <header class="entry-header">
                <h1 class="entry-title"><?php esc_html_e( 'My Account', 'societypress' ); ?></h1>
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
                            esc_html_e( 'Your information has been updated.', 'societypress' );
                            break;
                        case 'photo':
                            esc_html_e( 'Your profile photo has been updated.', 'societypress' );
                            break;
                        case 'photo-removed':
                            esc_html_e( 'Your profile photo has been removed.', 'societypress' );
                            break;
                        case 'password':
                            esc_html_e( 'Your password has been changed.', 'societypress' );
                            break;
                        case 'preferences':
                            esc_html_e( 'Your preferences have been saved.', 'societypress' );
                            break;
                        case 'privacy':
                            esc_html_e( 'Your directory privacy settings have been saved.', 'societypress' );
                            break;
                        case 'interests':
                            esc_html_e( 'Your interests and skills have been saved.', 'societypress' );
                            break;
                        case 'surnames':
                            esc_html_e( 'Your research surnames have been updated.', 'societypress' );
                            break;
                        case 'event-cancelled':
                            esc_html_e( 'Your event registration has been cancelled.', 'societypress' );
                            break;
                        default:
                            esc_html_e( 'Changes saved.', 'societypress' );
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ( $pending ) : ?>
                <div class="sp-notice sp-notice--info" style="background: #fff8e1; border-left: 4px solid #f9a825; color: #5d4037; padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;">
                    <?php esc_html_e( 'Your changes have been submitted for review. An administrator will approve them shortly.', 'societypress' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( $error ) : ?>
                <div class="sp-notice sp-notice--error">
                    <?php
                    switch ( $error ) {
                        case 'password-mismatch':
                            esc_html_e( 'The new passwords do not match. Please try again.', 'societypress' );
                            break;
                        case 'password-wrong':
                            esc_html_e( 'Your current password is incorrect.', 'societypress' );
                            break;
                        case 'password-short':
                            esc_html_e( 'Your new password must be at least 8 characters.', 'societypress' );
                            break;
                        case 'email-invalid':
                            esc_html_e( 'Please enter a valid email address.', 'societypress' );
                            break;
                        case 'email-taken':
                            esc_html_e( 'That email address is already in use by another account.', 'societypress' );
                            break;
                        case 'photo-type':
                            esc_html_e( 'Please upload a JPG, PNG, or GIF image.', 'societypress' );
                            break;
                        case 'photo-size':
                            esc_html_e( 'The image is too large. Maximum file size is 2 MB.', 'societypress' );
                            break;
                        case 'photo-upload':
                            esc_html_e( 'There was a problem uploading your photo. Please try again.', 'societypress' );
                            break;
                        case 'no-member':
                            esc_html_e( 'Your membership record was not found. Please contact an administrator.', 'societypress' );
                            break;
                        case 'nonce':
                            esc_html_e( 'Your session has expired. Please try again.', 'societypress' );
                            break;
                        default:
                            esc_html_e( 'Something went wrong. Please try again.', 'societypress' );
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 1: PROFILE PHOTO
            // ================================================================

            // Nudge new members who haven't uploaded a photo yet.
            // WHY: Members who add a photo are more recognizable in the directory
            //      and at meetings. A gentle prompt on their first visit helps.
            if ( ! $custom_photo && $member ) :
            ?>
            <div class="sp-account-notice sp-account-notice--info">
                <p><?php esc_html_e( 'Your profile is missing a photo. Adding one helps other members recognize you!', 'societypress' ); ?></p>
                <a href="#photo" class="sp-button sp-button--secondary sp-button--small"><?php esc_html_e( 'Add a Photo', 'societypress' ); ?></a>
            </div>
            <?php endif; ?>

            <section class="sp-account-section" id="photo">
                <h2><?php esc_html_e( 'Profile Photo', 'societypress' ); ?></h2>

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
                                <?php echo $custom_photo ? esc_html__( 'Change Photo', 'societypress' ) : esc_html__( 'Upload Photo', 'societypress' ); ?>
                            </label>
                            <input type="file"
                                   id="sp-photo-upload"
                                   name="sp_profile_photo"
                                   accept="image/jpeg,image/png,image/gif"
                                   capture="user"
                                   class="sp-file-input"
                                   onchange="this.form.submit();" />

                            <input type="hidden" name="sp_action" value="update_photo" />
                        </form>

                        <?php if ( $custom_photo ) : ?>
                            <form method="post" class="sp-photo-remove-form">
                                <?php wp_nonce_field( 'sp_remove_photo', 'sp_remove_photo_nonce' ); ?>
                                <input type="hidden" name="sp_action" value="remove_photo" />
                                <button type="submit" class="sp-button sp-button--text"><?php esc_html_e( 'Remove Photo', 'societypress' ); ?></button>
                            </form>
                        <?php endif; ?>

                        <p class="sp-photo-hint"><?php esc_html_e( 'JPG, PNG, or GIF. Max 2 MB.', 'societypress' ); ?></p>
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
                <h2><?php esc_html_e( 'Personal Information', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_profile', 'sp_profile_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_profile" />

                    <!-- Prefix and Suffix on one row -->
                    <div class="sp-form-row sp-form-row--thirds">
                        <div class="sp-form-field">
                            <label for="sp-prefix"><?php esc_html_e( 'Prefix', 'societypress' ); ?></label>
                            <select id="sp-prefix" name="prefix">
                                <option value="">&mdash;</option>
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
                            <label for="sp-suffix"><?php esc_html_e( 'Suffix', 'societypress' ); ?></label>
                            <select id="sp-suffix" name="suffix">
                                <option value="">&mdash;</option>
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
                            <label for="sp-first-name"><?php esc_html_e( 'First Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-first-name" name="first_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'first_name' ) ?: $user->first_name ); ?>"
                                   required />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-middle-name"><?php esc_html_e( 'Middle Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-middle-name" name="middle_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'middle_name' ) ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-last-name"><?php esc_html_e( 'Last Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-last-name" name="last_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'last_name' ) ?: $user->last_name ); ?>"
                                   required />
                        </div>
                    </div>

                    <!-- Preferred Name / Maiden Name -->
                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-preferred-name"><?php esc_html_e( 'Preferred Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-preferred-name" name="preferred_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'preferred_name' ) ); ?>" />
                            <p class="sp-field-hint"><?php esc_html_e( 'What you\'d like to be called (e.g., "Bob" instead of "Robert").', 'societypress' ); ?></p>
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-maiden-name"><?php esc_html_e( 'Maiden Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-maiden-name" name="maiden_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'maiden_name' ) ); ?>" />
                        </div>
                    </div>

                    <!-- Date of Birth -->
                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-dob"><?php esc_html_e( 'Date of Birth', 'societypress' ); ?></label>
                            <input type="date" id="sp-dob" name="date_of_birth"
                                   value="<?php echo esc_attr( sp_m( $member, 'date_of_birth' ) ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <!-- Spacer so DOB doesn't stretch full width -->
                        </div>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Personal Information', 'societypress' ); ?></button>
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
                <h2><?php esc_html_e( 'Contact Information', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_contact', 'sp_contact_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_contact" />

                    <div class="sp-form-field">
                        <label for="sp-email"><?php esc_html_e( 'Email Address', 'societypress' ); ?></label>
                        <input type="email" id="sp-email" name="user_email"
                               value="<?php echo esc_attr( $user->user_email ); ?>"
                               required />
                        <p class="sp-field-hint"><?php esc_html_e( 'This is used for logging in and receiving communications.', 'societypress' ); ?></p>
                    </div>

                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-phone"><?php esc_html_e( 'Home Phone', 'societypress' ); ?></label>
                            <input type="tel" id="sp-phone" name="phone"
                                   value="<?php echo esc_attr( sp_m( $member, 'phone' ) ); ?>"
                                   placeholder="(210) 555-1234"
                                   class="sp-phone-input" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-cell"><?php esc_html_e( 'Cell Phone', 'societypress' ); ?></label>
                            <input type="tel" id="sp-cell" name="cell"
                                   value="<?php echo esc_attr( sp_m( $member, 'cell' ) ); ?>"
                                   placeholder="(210) 555-1234"
                                   class="sp-phone-input" />
                        </div>
                    </div>

                    <?php
                    // Preferred phone — tells the system which number to use
                    // when Harold needs to call a member or send SMS notifications.
                    // WHY: Many older members have both a landline and cell.
                    //      Rather than guess, we ask which one they prefer.
                    $pref_phone = sp_m( $member, 'preferred_phone' ) ?: 'cell';
                    ?>
                    <div class="sp-form-field">
                        <label><?php esc_html_e( 'Preferred Phone', 'societypress' ); ?></label>
                        <div style="display: flex; gap: 20px; margin-top: 4px;">
                            <label style="font-weight: 400;">
                                <input type="radio" name="preferred_phone" value="phone"
                                       <?php checked( $pref_phone, 'phone' ); ?>>
                                <?php esc_html_e( 'Home', 'societypress' ); ?>
                            </label>
                            <label style="font-weight: 400;">
                                <input type="radio" name="preferred_phone" value="cell"
                                       <?php checked( $pref_phone, 'cell' ); ?>>
                                <?php esc_html_e( 'Cell', 'societypress' ); ?>
                            </label>
                            <label style="font-weight: 400;">
                                <input type="radio" name="preferred_phone" value="work_phone"
                                       <?php checked( $pref_phone, 'work_phone' ); ?>>
                                <?php esc_html_e( 'Work', 'societypress' ); ?>
                            </label>
                        </div>
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-website"><?php esc_html_e( 'Website', 'societypress' ); ?></label>
                        <input type="url" id="sp-website" name="website"
                               value="<?php echo esc_attr( sp_m( $member, 'website' ) ); ?>"
                               placeholder="https://" />
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Contact Information', 'societypress' ); ?></button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 4: ADDRESS
            // ================================================================
            ?>
            <section class="sp-account-section" id="address">
                <h2><?php esc_html_e( 'Mailing Address', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_address', 'sp_address_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_address" />

                    <div class="sp-form-field">
                        <label for="sp-address1"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label>
                        <input type="text" id="sp-address1" name="address_1"
                               value="<?php echo esc_attr( sp_m( $member, 'address_1' ) ); ?>" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-address2"><?php esc_html_e( 'Address Line 2', 'societypress' ); ?></label>
                        <input type="text" id="sp-address2" name="address_2"
                               value="<?php echo esc_attr( sp_m( $member, 'address_2' ) ); ?>"
                               placeholder="<?php esc_attr_e( 'Apt, Suite, Unit, etc.', 'societypress' ); ?>" />
                    </div>

                    <div class="sp-form-row sp-form-row--city-state">
                        <div class="sp-form-field sp-form-field--city">
                            <label for="sp-city"><?php esc_html_e( 'City', 'societypress' ); ?></label>
                            <input type="text" id="sp-city" name="city"
                                   value="<?php echo esc_attr( sp_m( $member, 'city' ) ); ?>" />
                        </div>
                        <div class="sp-form-field sp-form-field--state">
                            <label for="sp-state"><?php esc_html_e( 'State', 'societypress' ); ?></label>
                            <input type="text" id="sp-state" name="state"
                                   value="<?php echo esc_attr( sp_m( $member, 'state' ) ); ?>"
                                   maxlength="100" />
                        </div>
                        <div class="sp-form-field sp-form-field--zip">
                            <label for="sp-postal"><?php esc_html_e( 'Zip / Postal Code', 'societypress' ); ?></label>
                            <input type="text" id="sp-postal" name="postal_code"
                                   value="<?php echo esc_attr( sp_m( $member, 'postal_code' ) ); ?>" />
                        </div>
                    </div>

                    <div class="sp-form-row sp-form-row--half">
                        <div class="sp-form-field">
                            <label for="sp-country"><?php esc_html_e( 'Country', 'societypress' ); ?></label>
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
                            <?php esc_html_e( 'I have a seasonal / alternate address', 'societypress' ); ?>
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

                        <h3 class="sp-subsection-heading"><?php esc_html_e( 'Seasonal Address', 'societypress' ); ?></h3>

                        <div class="sp-form-row sp-form-row--half">
                            <div class="sp-form-field">
                                <label for="sp-seasonal-from"><?php esc_html_e( 'From (Month)', 'societypress' ); ?></label>
                                <select id="sp-seasonal-from" name="seasonal_from">
                                    <option value="">&mdash;</option>
                                    <?php
                                    $months = [
                                        '01' => __( 'January', 'societypress' ),   '02' => __( 'February', 'societypress' ),  '03' => __( 'March', 'societypress' ),
                                        '04' => __( 'April', 'societypress' ),     '05' => __( 'May', 'societypress' ),       '06' => __( 'June', 'societypress' ),
                                        '07' => __( 'July', 'societypress' ),      '08' => __( 'August', 'societypress' ),    '09' => __( 'September', 'societypress' ),
                                        '10' => __( 'October', 'societypress' ),   '11' => __( 'November', 'societypress' ),  '12' => __( 'December', 'societypress' ),
                                    ];
                                    foreach ( $months as $num => $name ) {
                                        $selected = ( sp_m( $member, 'seasonal_from' ) === $num ) ? ' selected' : '';
                                        echo '<option value="' . $num . '"' . $selected . '>' . esc_html( $name ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="sp-form-field">
                                <label for="sp-seasonal-to"><?php esc_html_e( 'To (Month)', 'societypress' ); ?></label>
                                <select id="sp-seasonal-to" name="seasonal_to">
                                    <option value="">&mdash;</option>
                                    <?php
                                    foreach ( $months as $num => $name ) {
                                        $selected = ( sp_m( $member, 'seasonal_to' ) === $num ) ? ' selected' : '';
                                        echo '<option value="' . $num . '"' . $selected . '>' . esc_html( $name ) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="sp-form-field">
                            <label for="sp-seasonal-address1"><?php esc_html_e( 'Street Address', 'societypress' ); ?></label>
                            <input type="text" id="sp-seasonal-address1" name="seasonal_address_1"
                                   value="<?php echo esc_attr( sp_m( $member, 'seasonal_address_1' ) ); ?>" />
                        </div>

                        <div class="sp-form-row sp-form-row--city-state">
                            <div class="sp-form-field sp-form-field--city">
                                <label for="sp-seasonal-city"><?php esc_html_e( 'City', 'societypress' ); ?></label>
                                <input type="text" id="sp-seasonal-city" name="seasonal_city"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_city' ) ); ?>" />
                            </div>
                            <div class="sp-form-field sp-form-field--state">
                                <label for="sp-seasonal-state"><?php esc_html_e( 'State', 'societypress' ); ?></label>
                                <input type="text" id="sp-seasonal-state" name="seasonal_state"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_state' ) ); ?>" />
                            </div>
                            <div class="sp-form-field sp-form-field--zip">
                                <label for="sp-seasonal-postal"><?php esc_html_e( 'Zip / Postal Code', 'societypress' ); ?></label>
                                <input type="text" id="sp-seasonal-postal" name="seasonal_postal_code"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_postal_code' ) ); ?>" />
                            </div>
                        </div>

                        <div class="sp-form-row sp-form-row--half">
                            <div class="sp-form-field">
                                <label for="sp-seasonal-country"><?php esc_html_e( 'Country', 'societypress' ); ?></label>
                                <input type="text" id="sp-seasonal-country" name="seasonal_country"
                                       value="<?php echo esc_attr( sp_m( $member, 'seasonal_country' ) ?: 'US' ); ?>" />
                            </div>
                            <div class="sp-form-field">
                                <!-- spacer -->
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Address', 'societypress' ); ?></button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 4b: JOINT MEMBER (COUPLE / HOUSEHOLD)
            // WHY: Joint memberships let couples share one account. The primary
            // member can view and update their partner's name, email, and phone
            // right here. Both names show in the directory and both people can
            // attend events under this membership.
            //
            // Only shown if the member's tier allows joint membership (the
            // allows_joint flag on sp_membership_tiers).
            // ================================================================
            $show_joint_section = false;
            if ( $member ) {
                // Check if this member's tier allows joint membership
                $member_tier_id = sp_m( $member, 'tier_id' );
                if ( $member_tier_id ) {
                    global $wpdb;
                    $tier_allows_joint = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT allows_joint FROM {$wpdb->prefix}sp_membership_tiers WHERE id = %d",
                        $member_tier_id
                    ) );
                    if ( $tier_allows_joint ) {
                        $show_joint_section = true;
                    }
                }
                // Also show if they already have joint member data (e.g., tier changed later)
                if ( ! $show_joint_section && ! empty( sp_m( $member, 'joint_member' ) ) ) {
                    $show_joint_section = true;
                }
            }
            ?>
            <?php if ( $show_joint_section ) : ?>
            <section class="sp-account-section" id="joint-member">
                <h2><?php esc_html_e( 'Joint Member', 'societypress' ); ?></h2>
                <p class="sp-section-desc">
                    <?php esc_html_e( 'Your membership plan includes a second household member. Both names will appear in the directory and both people can attend events.', 'societypress' ); ?>
                </p>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_joint', 'sp_joint_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_joint" />

                    <div class="sp-form-row">
                        <div class="sp-form-field">
                            <label for="sp-joint-fn"><?php esc_html_e( 'First Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-joint-fn" name="joint_first_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'joint_first_name' ) ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-joint-ln"><?php esc_html_e( 'Last Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-joint-ln" name="joint_last_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'joint_last_name' ) ); ?>" />
                        </div>
                    </div>
                    <div class="sp-form-row">
                        <div class="sp-form-field">
                            <label for="sp-joint-pn"><?php esc_html_e( 'Preferred Name', 'societypress' ); ?></label>
                            <input type="text" id="sp-joint-pn" name="joint_preferred_name"
                                   value="<?php echo esc_attr( sp_m( $member, 'joint_preferred_name' ) ); ?>"
                                   placeholder="<?php esc_attr_e( 'Optional — if different from first name', 'societypress' ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <!-- spacer -->
                        </div>
                    </div>
                    <div class="sp-form-row">
                        <div class="sp-form-field">
                            <label for="sp-joint-em"><?php esc_html_e( 'Email', 'societypress' ); ?></label>
                            <input type="email" id="sp-joint-em" name="joint_email"
                                   value="<?php echo esc_attr( sp_m( $member, 'joint_email' ) ); ?>"
                                   placeholder="<?php esc_attr_e( 'Optional', 'societypress' ); ?>" />
                        </div>
                        <div class="sp-form-field">
                            <label for="sp-joint-ph"><?php esc_html_e( 'Phone', 'societypress' ); ?></label>
                            <input type="tel" id="sp-joint-ph" name="joint_phone"
                                   value="<?php echo esc_attr( sp_m( $member, 'joint_phone' ) ); ?>"
                                   placeholder="<?php esc_attr_e( 'Optional', 'societypress' ); ?>" />
                        </div>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Joint Member', 'societypress' ); ?></button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 5: COMMUNICATION PREFERENCES
            // WHY: Let members control what emails they receive. Respecting
            // preferences builds trust and reduces unsubscribe complaints.
            // Blast email opt-out gives members a way to silence mass emails
            // without turning off targeted category notifications.
            // ================================================================
            ?>
            <?php if ( $member ) : ?>
            <section class="sp-account-section" id="preferences">
                <h2><?php esc_html_e( 'Communication Preferences', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_preferences', 'sp_preferences_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_preferences" />

                    <div class="sp-checkbox-group">
                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="receive_print" value="1"
                                   <?php checked( sp_m( $member, 'receive_print' ), '1' ); ?> />
                            <?php esc_html_e( 'Receive print newsletter by mail', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_notices" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_notices' ), '1' ); ?> />
                            <?php esc_html_e( 'Email me general notices and announcements', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_events" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_events' ), '1' ); ?> />
                            <?php esc_html_e( 'Email me about upcoming events', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_newsletters" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_newsletters' ), '1' ); ?> />
                            <?php esc_html_e( 'Email me when new newsletters are published', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="pref_email_surnames" value="1"
                                   <?php checked( sp_m( $member, 'pref_email_surnames' ), '1' ); ?> />
                            <?php esc_html_e( 'Email me when someone is researching one of my surnames', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="blast_email_opt_out" value="1"
                                   <?php checked( sp_m( $member, 'blast_email_opt_out' ), '1' ); ?> />
                            <?php esc_html_e( 'Opt out of mass/blast emails', 'societypress' ); ?>
                        </label>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Preferences', 'societypress' ); ?></button>
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
                <h2><?php esc_html_e( 'Directory Privacy', 'societypress' ); ?></h2>
                <p class="sp-section-description">
                    <?php esc_html_e( 'Choose which of your details are visible to other members in the membership directory. Unchecked items will be hidden.', 'societypress' ); ?>
                </p>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_privacy', 'sp_privacy_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_privacy" />

                    <div class="sp-checkbox-group">
                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_name" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_name' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my name', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_address" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_address' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my address', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_phone" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_phone' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my phone number', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_email" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_email' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my email address', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_website" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_website' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my website', 'societypress' ); ?>
                        </label>

                        <label class="sp-checkbox-label">
                            <input type="checkbox" name="dir_show_photo" value="1"
                                   <?php checked( sp_m( $member, 'dir_show_photo' ), '1' ); ?> />
                            <?php esc_html_e( 'Show my photo', 'societypress' ); ?>
                        </label>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Privacy Settings', 'societypress' ); ?></button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 7: INTERESTS & SKILLS
            // WHY: Lets members share what they're interested in and what
            // expertise they bring. Society admins can use this to match
            // volunteers to committees, find speakers, or connect members
            // with shared interests. Free-text keeps it flexible — every
            // society's needs are different.
            // ================================================================
            ?>
            <?php if ( $member ) : ?>
            <section class="sp-account-section" id="interests">
                <h2><?php esc_html_e( 'Interests & Skills', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_interests', 'sp_interests_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_interests" />

                    <div class="sp-form-field">
                        <label for="sp-interests"><?php esc_html_e( 'Interests', 'societypress' ); ?></label>
                        <textarea id="sp-interests" name="interests" rows="3"
                                  placeholder="<?php esc_attr_e( 'e.g., Texas Rangers, Civil War records, DNA research, cemetery preservation', 'societypress' ); ?>"
                        ><?php echo esc_textarea( sp_m( $member, 'interests' ) ); ?></textarea>
                        <p class="sp-field-hint"><?php esc_html_e( 'Genealogical topics, time periods, or geographic areas you\'re interested in.', 'societypress' ); ?></p>
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-skills"><?php esc_html_e( 'Skills', 'societypress' ); ?></label>
                        <textarea id="sp-skills" name="skills" rows="3"
                                  placeholder="<?php esc_attr_e( 'e.g., German translation, courthouse research, photo restoration, web design', 'societypress' ); ?>"
                        ><?php echo esc_textarea( sp_m( $member, 'skills' ) ); ?></textarea>
                        <p class="sp-field-hint"><?php esc_html_e( 'Skills or expertise you\'d be willing to share with other members.', 'societypress' ); ?></p>
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Interests & Skills', 'societypress' ); ?></button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 7b: GENEALOGY SERVICE PROFILES
            // WHY: Many members maintain profiles on external genealogy
            //      platforms. Linking them here lets other members find
            //      and connect with them across services — the genealogy
            //      equivalent of sharing your LinkedIn or GitHub.
            //      Stored as user_meta (not sp_members columns) because
            //      these are optional supplemental links, not core data.
            // ================================================================
            ?>
            <?php if ( $member ) : ?>
            <section class="sp-account-section" id="genealogy-services">
                <h2><?php esc_html_e( 'Genealogy Service Profiles', 'societypress' ); ?></h2>
                <p class="sp-section-hint">
                    <?php esc_html_e( 'Link your profiles on genealogy research platforms so other members can find and connect with you.', 'societypress' ); ?>
                </p>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_genealogy_services', 'sp_genealogy_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_genealogy_services" />

                    <?php
                    // The 8 services from the spec, plus their expected URL patterns
                    // as placeholder hints so members know what to enter.
                    $services = [
                        'wikitree'     => [ 'WikiTree',      'https://www.wikitree.com/wiki/YourProfile' ],
                        'familysearch' => [ 'FamilySearch',  'https://www.familysearch.org/tree/person/details/XXXX-XXX' ],
                        'geni'         => [ 'Geni',          'https://www.geni.com/people/Your-Name/123456789' ],
                        'werelate'     => [ 'WeRelate',      'https://www.werelate.org/wiki/Person:Your_Name' ],
                        'ancestry'     => [ 'Ancestry',      'https://www.ancestry.com/family-tree/person/...' ],
                        'myheritage'   => [ 'MyHeritage',    'https://www.myheritage.com/...' ],
                        'findagrave'   => [ 'Find A Grave',  'https://www.findagrave.com/memorial/...' ],
                        '23andme'      => [ '23andMe',       'https://you.23andme.com/...' ],
                    ];
                    foreach ( $services as $key => [ $label, $placeholder ] ) :
                        $meta_key = 'sp_genealogy_' . $key;
                        $value    = get_user_meta( $user->ID, $meta_key, true );
                    ?>
                    <div class="sp-form-field">
                        <label for="sp-gen-<?php echo esc_attr( $key ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </label>
                        <input type="url"
                               id="sp-gen-<?php echo esc_attr( $key ); ?>"
                               name="genealogy_<?php echo esc_attr( $key ); ?>"
                               value="<?php echo esc_attr( $value ); ?>"
                               placeholder="<?php echo esc_attr( $placeholder ); ?>" />
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Save Genealogy Profiles', 'societypress' ); ?></button>
                </form>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 8: RESEARCH SURNAMES
            // WHY: Members register the surnames they're researching so
            // other members researching the same names can connect. This
            // is the core social feature of a genealogical society.
            // ================================================================
            ?>

            <section class="sp-account-section" id="surnames">
                <h2><?php esc_html_e( 'Research Surnames', 'societypress' ); ?></h2>
                <p class="sp-section-hint">
                    <?php esc_html_e( 'Add the surnames you\'re researching so other members can find and contact you.', 'societypress' ); ?>
                </p>

                <?php
                // Load this member's current research surnames with full location columns
                $surnames = $wpdb->get_results( $wpdb->prepare(
                    "SELECT id, surname, county, state, country, year_from, year_to, note
                     FROM {$wpdb->prefix}sp_member_surnames
                     WHERE user_id = %d
                     ORDER BY surname ASC",
                    $user->ID
                ) );
                ?>

                <!-- Existing surnames -->
                <div class="sp-surname-list">
                    <?php if ( ! empty( $surnames ) ) : ?>
                        <?php foreach ( $surnames as $sn ) : ?>
                            <div class="sp-surname-row">
                                <strong><?php echo esc_html( $sn->surname ); ?></strong>
                                <?php
                                // Build a location + year string from the individual columns,
                                // matching the format used in the directory detail view:
                                // "Bexar County, TX, US (1830–1890)"
                                $parts = [];
                                if ( $sn->county )  $parts[] = esc_html( $sn->county );
                                if ( $sn->state )   $parts[] = esc_html( $sn->state );
                                if ( $sn->country && $sn->country !== 'US' ) $parts[] = esc_html( $sn->country );
                                $location_str = implode( ', ', $parts );

                                $year_str = '';
                                if ( $sn->year_from && $sn->year_to ) {
                                    $year_str = '(' . esc_html( $sn->year_from ) . '–' . esc_html( $sn->year_to ) . ')';
                                } elseif ( $sn->year_from ) {
                                    $year_str = '(' . esc_html( $sn->year_from ) . '–)';
                                } elseif ( $sn->year_to ) {
                                    $year_str = '(–' . esc_html( $sn->year_to ) . ')';
                                }

                                $detail = trim( $location_str . ( $year_str ? ' ' . $year_str : '' ) );
                                ?>
                                <?php if ( $detail ) : ?>
                                    <span class="sp-surname-detail"><?php echo $detail; ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $sn->note ) ) : ?>
                                    <span class="sp-surname-note"><?php echo esc_html( $sn->note ); ?></span>
                                <?php endif; ?>
                                <form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Remove this surname?', 'societypress' ) ); ?>');">
                                    <?php wp_nonce_field( 'sp_remove_surname', 'sp_surname_nonce' ); ?>
                                    <input type="hidden" name="sp_action" value="remove_surname">
                                    <input type="hidden" name="surname_id" value="<?php echo esc_attr( $sn->id ); ?>">
                                    <button type="submit" class="sp-surname-remove" aria-label="<?php esc_attr_e( 'Remove surname', 'societypress' ); ?>">&times;</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="sp-surname-empty"><?php esc_html_e( 'No research surnames added yet.', 'societypress' ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Add new surname form -->
                <form method="post" class="sp-surname-add-form">
                    <?php wp_nonce_field( 'sp_add_surname', 'sp_surname_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="add_surname">

                    <div class="sp-form-field sp-form-field--surname">
                        <label for="sp-new-surname"><?php esc_html_e( 'Surname', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-surname" name="new_surname" required
                               placeholder="<?php esc_attr_e( 'e.g., STRICKLIN', 'societypress' ); ?>"
                               style="text-transform: uppercase;" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-county"><?php esc_html_e( 'County', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-county" name="new_county"
                               placeholder="<?php esc_attr_e( 'e.g., Bexar', 'societypress' ); ?>" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-state"><?php esc_html_e( 'State', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-state" name="new_state"
                               placeholder="<?php esc_attr_e( 'e.g., TX', 'societypress' ); ?>" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-country"><?php esc_html_e( 'Country', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-country" name="new_country"
                               placeholder="<?php esc_attr_e( 'e.g., US', 'societypress' ); ?>" />
                    </div>

                    <div class="sp-form-field sp-form-field--year">
                        <label for="sp-new-year-from"><?php esc_html_e( 'From', 'societypress' ); ?></label>
                        <input type="number" id="sp-new-year-from" name="new_year_from"
                               placeholder="<?php esc_attr_e( '1830', 'societypress' ); ?>"
                               min="1000" max="2100" />
                    </div>

                    <div class="sp-form-field sp-form-field--year">
                        <label for="sp-new-year-to"><?php esc_html_e( 'To', 'societypress' ); ?></label>
                        <input type="number" id="sp-new-year-to" name="new_year_to"
                               placeholder="<?php esc_attr_e( '1890', 'societypress' ); ?>"
                               min="1000" max="2100" />
                    </div>

                    <div class="sp-form-field sp-form-field--full">
                        <label for="sp-new-surname-note"><?php esc_html_e( 'Research Notes', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-surname-note" name="new_surname_note"
                               placeholder="<?php esc_attr_e( 'e.g., German immigrants, arrived 1850s via Galveston', 'societypress' ); ?>" />
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Add Surname', 'societypress' ); ?></button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 8b: RESEARCH AREAS
            // WHY: The geographic complement to research surnames. Members
            // register the counties, states, or regions they're researching
            // so others working in the same area can find them. A member
            // might research a dozen surnames all in one county — this
            // surfaces the geographic overlap.
            // ================================================================
            ?>

            <section class="sp-account-section" id="research-areas">
                <h2><?php esc_html_e( 'Research Areas', 'societypress' ); ?></h2>
                <p class="sp-section-hint">
                    <?php esc_html_e( 'Add the geographic areas you\'re researching — counties, states, regions, or countries.', 'societypress' ); ?>
                </p>

                <?php
                $research_areas = $wpdb->get_results( $wpdb->prepare(
                    "SELECT id, area, area_type, year_from, year_to, note
                     FROM {$wpdb->prefix}sp_member_research_areas
                     WHERE user_id = %d
                     ORDER BY area ASC",
                    $user->ID
                ) );
                ?>

                <!-- Existing research areas -->
                <div class="sp-surname-list">
                    <?php if ( ! empty( $research_areas ) ) : ?>
                        <?php foreach ( $research_areas as $ra ) : ?>
                            <div class="sp-surname-row">
                                <strong><?php echo esc_html( $ra->area ); ?></strong>
                                <?php if ( $ra->area_type ) : ?>
                                    <span class="sp-surname-detail"><?php echo esc_html( ucfirst( $ra->area_type ) ); ?></span>
                                <?php endif; ?>
                                <?php
                                $year_str = '';
                                if ( $ra->year_from && $ra->year_to ) {
                                    $year_str = '(' . esc_html( $ra->year_from ) . '–' . esc_html( $ra->year_to ) . ')';
                                } elseif ( $ra->year_from ) {
                                    $year_str = '(' . esc_html( $ra->year_from ) . '–)';
                                } elseif ( $ra->year_to ) {
                                    $year_str = '(–' . esc_html( $ra->year_to ) . ')';
                                }
                                if ( $year_str ) :
                                ?>
                                    <span class="sp-surname-detail"><?php echo $year_str; ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $ra->note ) ) : ?>
                                    <span class="sp-surname-note"><?php echo esc_html( $ra->note ); ?></span>
                                <?php endif; ?>
                                <form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Remove this research area?', 'societypress' ) ); ?>');">
                                    <?php wp_nonce_field( 'sp_remove_research_area', 'sp_research_area_nonce' ); ?>
                                    <input type="hidden" name="sp_action" value="remove_research_area">
                                    <input type="hidden" name="area_id" value="<?php echo esc_attr( $ra->id ); ?>">
                                    <button type="submit" class="sp-surname-remove" aria-label="<?php esc_attr_e( 'Remove research area', 'societypress' ); ?>">&times;</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p class="sp-surname-empty"><?php esc_html_e( 'No research areas added yet.', 'societypress' ); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Add new research area form -->
                <form method="post" class="sp-surname-add-form">
                    <?php wp_nonce_field( 'sp_add_research_area', 'sp_research_area_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="add_research_area">

                    <div class="sp-form-field sp-form-field--surname">
                        <label for="sp-new-area"><?php esc_html_e( 'Area', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-area" name="new_area" required
                               placeholder="<?php esc_attr_e( 'e.g., Bexar County, TX', 'societypress' ); ?>" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-area-type"><?php esc_html_e( 'Type', 'societypress' ); ?></label>
                        <select id="sp-new-area-type" name="new_area_type">
                            <option value=""><?php esc_html_e( '(optional)', 'societypress' ); ?></option>
                            <option value="county"><?php esc_html_e( 'County', 'societypress' ); ?></option>
                            <option value="state"><?php esc_html_e( 'State/Province', 'societypress' ); ?></option>
                            <option value="country"><?php esc_html_e( 'Country', 'societypress' ); ?></option>
                            <option value="region"><?php esc_html_e( 'Region', 'societypress' ); ?></option>
                            <option value="city"><?php esc_html_e( 'City/Town', 'societypress' ); ?></option>
                        </select>
                    </div>

                    <div class="sp-form-field sp-form-field--year">
                        <label for="sp-new-area-year-from"><?php esc_html_e( 'From', 'societypress' ); ?></label>
                        <input type="number" id="sp-new-area-year-from" name="new_area_year_from"
                               placeholder="<?php esc_attr_e( '1830', 'societypress' ); ?>"
                               min="1000" max="2100" />
                    </div>

                    <div class="sp-form-field sp-form-field--year">
                        <label for="sp-new-area-year-to"><?php esc_html_e( 'To', 'societypress' ); ?></label>
                        <input type="number" id="sp-new-area-year-to" name="new_area_year_to"
                               placeholder="<?php esc_attr_e( '1890', 'societypress' ); ?>"
                               min="1000" max="2100" />
                    </div>

                    <div class="sp-form-field sp-form-field--full">
                        <label for="sp-new-area-note"><?php esc_html_e( 'Notes', 'societypress' ); ?></label>
                        <input type="text" id="sp-new-area-note" name="new_area_note"
                               placeholder="<?php esc_attr_e( 'e.g., German settlements along the Guadalupe River', 'societypress' ); ?>" />
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Add Research Area', 'societypress' ); ?></button>
                </form>
            </section>

            <?php
            // ================================================================
            // SECTION 8c: RELATIONSHIPS
            // WHY: Shows the member's family connections — spouse, parent,
            // sibling, etc. Read-only display; the admin manages these
            // from the member edit page. Members can see who they're linked
            // to, which matters for household memberships and billing.
            // ================================================================
            $relationships = sp_get_member_relationships( $user->ID );
            if ( ! empty( $relationships ) ) :
            ?>
            <section class="sp-account-section" id="relationships">
                <h2><?php esc_html_e( 'Family Connections', 'societypress' ); ?></h2>
                <p class="sp-section-hint">
                    <?php esc_html_e( 'Family relationships linked to your account. Contact your society administrator to update these.', 'societypress' ); ?>
                </p>

                <div class="sp-surname-list">
                    <?php foreach ( $relationships as $rel ) : ?>
                        <div class="sp-surname-row">
                            <strong><?php echo esc_html( $rel->related_name ); ?></strong>
                            <span class="sp-surname-detail"><?php echo esc_html( ucfirst( $rel->relationship ) ); ?></span>
                            <?php if ( ! empty( $rel->note ) ) : ?>
                                <span class="sp-surname-note"><?php echo esc_html( $rel->note ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <?php
            // ================================================================
            // SECTION 9: MY EVENTS
            // WHY: Members need to see what events they're registered for
            // and be able to cancel if their plans change. Reduces admin
            // workload — members handle their own registrations.
            // ================================================================
            ?>

            <section class="sp-account-section" id="events">
                <h2><?php esc_html_e( 'My Events', 'societypress' ); ?></h2>

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
                    <h3 class="sp-events-subheading"><?php esc_html_e( 'Upcoming', 'societypress' ); ?></h3>
                    <?php foreach ( $upcoming_regs as $reg ) : ?>
                        <div class="sp-event-card">
                            <div>
                                <strong><?php echo esc_html( $reg->title ); ?></strong><br>
                                <span class="sp-event-card__details">
                                    <?php echo esc_html( date_i18n( 'l, F j, Y', strtotime( $reg->event_date ) ) ); ?>
                                    <?php if ( $reg->start_time ) : ?>
                                        <?php
                                        /* translators: precedes a time, e.g. "at 2:00 PM" */
                                        echo esc_html( sprintf( __( 'at %s', 'societypress' ), date_i18n( 'g:i A', strtotime( $reg->start_time ) ) ) );
                                        ?>
                                    <?php endif; ?>
                                    <?php if ( $reg->location_name ) : ?>
                                        &mdash; <?php echo esc_html( $reg->location_name ); ?>
                                    <?php endif; ?>
                                </span>
                                <?php if ( $reg->reg_status === 'waitlisted' ) : ?>
                                    <span class="sp-event-badge--waitlisted"><?php esc_html_e( 'Waitlisted', 'societypress' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="sp-event-card__actions">
                                <a href="<?php echo esc_url( add_query_arg( 'sp_ics', $reg->event_id, home_url( '/' ) ) ); ?>"
                                   class="sp-button sp-event-ics-btn"
                                   title="<?php esc_attr_e( 'Download .ics file', 'societypress' ); ?>">
                                    <?php esc_html_e( 'Add to Calendar', 'societypress' ); ?>
                                </a>
                                <form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Cancel your registration for this event?', 'societypress' ) ); ?>');">
                                    <?php wp_nonce_field( 'sp_cancel_event_reg', 'sp_event_nonce' ); ?>
                                    <input type="hidden" name="sp_action" value="cancel_event_registration">
                                    <input type="hidden" name="registration_id" value="<?php echo esc_attr( $reg->reg_id ); ?>">
                                    <button type="submit" class="sp-button sp-event-cancel-btn"><?php esc_html_e( 'Cancel', 'societypress' ); ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="sp-events-empty"><?php esc_html_e( 'You\'re not registered for any upcoming events.', 'societypress' ); ?></p>
                <?php endif; ?>

                <?php if ( ! empty( $past_regs ) ) : ?>
                    <h3 class="sp-events-subheading sp-events-subheading--past"><?php esc_html_e( 'Past Events (6 months)', 'societypress' ); ?></h3>
                    <table class="sp-events-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Event', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Date', 'societypress' ); ?></th>
                                <th><?php esc_html_e( 'Status', 'societypress' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $past_regs as $past ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $past->title ); ?></td>
                                    <td><?php echo esc_html( date_i18n( 'M j, Y', strtotime( $past->event_date ) ) ); ?></td>
                                    <td>
                                        <?php
                                        if ( $past->reg_status === 'cancelled' ) {
                                            echo '<span class="sp-status--cancelled">' . esc_html__( 'Cancelled', 'societypress' ) . '</span>';
                                        } elseif ( $past->attended === '1' ) {
                                            echo '<span class="sp-status--attended">' . esc_html__( 'Attended', 'societypress' ) . '</span>';
                                        } elseif ( $past->attended === '0' ) {
                                            echo '<span class="sp-status--noshow">' . esc_html__( 'No-show', 'societypress' ) . '</span>';
                                        } else {
                                            echo '<span class="sp-status--registered">' . esc_html__( 'Registered', 'societypress' ) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>


            <?php
            // ================================================================
            // SECTION 10: CHANGE PASSWORD
            // WHY: Separated from other forms so members can update their
            // info without thinking about passwords, and vice versa.
            // Requires current password as a security measure.
            // ================================================================
            ?>

            <section class="sp-account-section" id="password">
                <h2><?php esc_html_e( 'Change Password', 'societypress' ); ?></h2>

                <form method="post" class="sp-account-form">
                    <?php wp_nonce_field( 'sp_update_password', 'sp_password_nonce' ); ?>
                    <input type="hidden" name="sp_action" value="update_password" />

                    <div class="sp-form-field">
                        <label for="sp-current-password"><?php esc_html_e( 'Current Password', 'societypress' ); ?></label>
                        <input type="password"
                               id="sp-current-password"
                               name="current_password"
                               required
                               autocomplete="current-password" />
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-new-password"><?php esc_html_e( 'New Password', 'societypress' ); ?></label>
                        <input type="password"
                               id="sp-new-password"
                               name="new_password"
                               required
                               minlength="8"
                               autocomplete="new-password" />
                        <p class="sp-field-hint"><?php esc_html_e( 'At least 8 characters.', 'societypress' ); ?></p>
                    </div>

                    <div class="sp-form-field">
                        <label for="sp-confirm-password"><?php esc_html_e( 'Confirm New Password', 'societypress' ); ?></label>
                        <input type="password"
                               id="sp-confirm-password"
                               name="confirm_password"
                               required
                               minlength="8"
                               autocomplete="new-password" />
                    </div>

                    <button type="submit" class="sp-button"><?php esc_html_e( 'Change Password', 'societypress' ); ?></button>
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

    // =========================================================================
    // AJAX SAVE — Intercept form submissions for text-based sections
    // =========================================================================
    // WHY: Full-page POST/redirect for saving a phone number or checking a
    //      preference box is a poor experience. AJAX saves are instant, keep
    //      the member's scroll position, and show inline feedback. The old
    //      POST handlers remain as a no-JS fallback.
    //
    // WHICH forms: Only the 6 text/checkbox sections — not photo (file upload),
    //      password (re-auth sensitive), surnames, research areas, or event
    //      cancellation (those use add/remove patterns with redirects).
    // =========================================================================
    var ajaxUrl = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
    var ajaxActions = [
        'update_profile', 'update_contact', 'update_address',
        'update_preferences', 'update_privacy', 'update_interests',
        'update_genealogy_services'
    ];

    // Find all forms with a matching sp_action and intercept them
    document.querySelectorAll('form').forEach(function(form) {
        var actionInput = form.querySelector('input[name="sp_action"]');
        if (!actionInput || ajaxActions.indexOf(actionInput.value) === -1) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = form.querySelector('button[type="submit"]');
            var origText = btn ? btn.textContent : '';
            if (btn) {
                btn.disabled = true;
                btn.textContent = <?php echo wp_json_encode( __( 'Saving…', 'societypress' ) ); ?>;
            }

            // Remove any existing feedback message in this form
            var oldMsg = form.querySelector('.sp-ajax-msg');
            if (oldMsg) oldMsg.remove();

            var formData = new FormData(form);
            formData.append('action', 'sp_save_account');

            fetch(ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var msg = document.createElement('div');
                    msg.className = 'sp-ajax-msg';
                    msg.setAttribute('role', 'status');

                    if (data.success) {
                        msg.className += ' sp-ajax-msg--success';
                        msg.textContent = data.data.message;
                    } else {
                        msg.className += ' sp-ajax-msg--error';
                        msg.textContent = (data.data && data.data.message)
                            ? data.data.message
                            : <?php echo wp_json_encode( __( 'Something went wrong. Please try again.', 'societypress' ) ); ?>;
                    }

                    // Insert message right before the submit button
                    if (btn) {
                        btn.parentNode.insertBefore(msg, btn);
                    } else {
                        form.appendChild(msg);
                    }

                    // Auto-dismiss after 5 seconds
                    setTimeout(function() { msg.remove(); }, 5000);
                })
                .catch(function() {
                    // Network error — let the form fall through to normal POST
                    form.submit();
                })
                .finally(function() {
                    if (btn) {
                        btn.disabled = false;
                        btn.textContent = origText;
                    }
                });
        });
    });
    // =========================================================================
    // PHONE AUTO-FORMATTING
    // =========================================================================
    // WHY: Genealogy society members are often older and type phone numbers in
    //      all sorts of formats — "2105551234", "210.555.1234", "210-555-1234".
    //      Auto-formatting to (210) 555-1234 on blur keeps the data clean and
    //      consistent without requiring any specific input format. We only
    //      format 10-digit US numbers — international numbers pass through as-is.
    // =========================================================================
    document.querySelectorAll('.sp-phone-input').forEach(function(input) {
        input.addEventListener('blur', function() {
            var digits = this.value.replace(/\D/g, '');
            // Strip leading 1 for US numbers (11 digits starting with 1)
            if (digits.length === 11 && digits[0] === '1') {
                digits = digits.substring(1);
            }
            if (digits.length === 10) {
                this.value = '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6);
            }
            // If not 10 digits, leave the value as-is (international, partial, etc.)
        });
    });

    // =========================================================================
    // EMAIL VALIDATION — Real-time feedback on email fields
    // =========================================================================
    // WHY: Members type their email wrong more often than you'd think — missing
    //      the @ sign, adding spaces, typing .con instead of .com. Catching
    //      these before submit saves Harold from bounced renewal reminders.
    // =========================================================================
    var emailInput = document.getElementById('sp-email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            var val = this.value.trim();
            // Simple but effective: must have @ with chars on both sides, and a dot after @
            var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
            if (val && !valid) {
                this.style.borderColor = '#d63638';
                // Add hint if not already present
                if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('sp-email-hint')) {
                    var hint = document.createElement('p');
                    hint.className = 'sp-field-hint sp-email-hint';
                    hint.style.color = '#d63638';
                    hint.textContent = <?php echo wp_json_encode( __( 'This doesn\'t look like a valid email address.', 'societypress' ) ); ?>;
                    this.parentNode.insertBefore(hint, this.nextSibling);
                }
            } else {
                this.style.borderColor = '';
                var hint = this.parentNode.querySelector('.sp-email-hint');
                if (hint) hint.remove();
            }
        });
    }

})();
</script>

<?php get_footer(); ?>
