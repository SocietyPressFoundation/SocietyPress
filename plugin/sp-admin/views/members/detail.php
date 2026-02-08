<?php
/**
 * SocietyPress Admin - Member Detail/Edit View
 *
 * Displays and allows editing of a single member's information.
 * Also handles the "new" action for adding new members.
 *
 * WHY THIS DESIGN:
 * - Grouped sections (Name, Membership, Contact, Preferences)
 * - Clear labels on every field
 * - Large form inputs for easy interaction
 * - Save button always visible
 *
 * @package SocietyPress
 * @since 0.59
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Determine if we're editing or viewing
$member_id = $router->get_param( 'id' );
$action    = $router->get_param( 'action', '' );
$is_new    = ( $action === 'new' );
$is_edit   = ( $action === 'edit' ) || $is_new;

// Get member data if editing existing
$member  = null;
$contact = null;

if ( $member_id && ! $is_new ) {
    $members_table = $wpdb->prefix . 'sp_members';
    $contact_table = $wpdb->prefix . 'sp_member_contact';

    $member = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$members_table} WHERE id = %d",
        $member_id
    ) );

    if ( $member ) {
        $contact = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$contact_table} WHERE member_id = %d",
            $member_id
        ) );
    }

    if ( ! $member ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Member not found.</div>';
        return;
    }
}

// Get all tiers for dropdown
$tiers_table = $wpdb->prefix . 'sp_membership_tiers';
$all_tiers = $wpdb->get_results( "SELECT id, name, price FROM {$tiers_table} WHERE is_active = 1 ORDER BY sort_order" );

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['sp_member_nonce'] ) ) {
    if ( ! wp_verify_nonce( $_POST['sp_member_nonce'], 'sp_save_member' ) ) {
        echo '<div class="sp-admin-notice sp-admin-notice--error">Security check failed. Please try again.</div>';
    } else {
        // Collect and sanitize data
        $member_data = [
            'first_name'         => sanitize_text_field( $_POST['first_name'] ?? '' ),
            'middle_name'        => sanitize_text_field( $_POST['middle_name'] ?? '' ),
            'last_name'          => sanitize_text_field( $_POST['last_name'] ?? '' ),
            'membership_tier_id' => absint( $_POST['membership_tier_id'] ?? 1 ),
            'status'             => sanitize_key( $_POST['status'] ?? 'pending' ),
            'join_date'          => sanitize_text_field( $_POST['join_date'] ?? date( 'Y-m-d' ) ),
            'expiration_date'    => sanitize_text_field( $_POST['expiration_date'] ?? '' ) ?: null,
            'directory_visible'  => isset( $_POST['directory_visible'] ) ? 1 : 0,
            'communication_preference' => sanitize_key( $_POST['communication_preference'] ?? 'email' ),
        ];

        $contact_data = [
            'primary_email'   => sanitize_email( $_POST['primary_email'] ?? '' ),
            'secondary_email' => sanitize_email( $_POST['secondary_email'] ?? '' ),
            'home_phone'      => sanitize_text_field( $_POST['home_phone'] ?? '' ),
            'cell_phone'      => sanitize_text_field( $_POST['cell_phone'] ?? '' ),
            'street_address'  => sanitize_textarea_field( $_POST['street_address'] ?? '' ),
            'address_line_2'  => sanitize_text_field( $_POST['address_line_2'] ?? '' ),
            'city'            => sanitize_text_field( $_POST['city'] ?? '' ),
            'state_province'  => sanitize_text_field( $_POST['state_province'] ?? '' ),
            'postal_code'     => sanitize_text_field( $_POST['postal_code'] ?? '' ),
            'country'         => sanitize_text_field( $_POST['country'] ?? 'USA' ),
        ];

        // Validate required fields
        $errors = [];
        if ( empty( $member_data['first_name'] ) ) {
            $errors[] = 'First name is required.';
        }
        if ( empty( $member_data['last_name'] ) ) {
            $errors[] = 'Last name is required.';
        }
        if ( empty( $contact_data['primary_email'] ) ) {
            $errors[] = 'Email is required.';
        } elseif ( ! is_email( $contact_data['primary_email'] ) ) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ( ! empty( $errors ) ) {
            echo '<div class="sp-admin-notice sp-admin-notice--error">' . implode( '<br>', array_map( 'esc_html', $errors ) ) . '</div>';
        } else {
            // Save member
            if ( $is_new ) {
                // Insert new member
                $member_data['created_at'] = current_time( 'mysql' );
                $member_data['updated_at'] = current_time( 'mysql' );

                $wpdb->insert( $wpdb->prefix . 'sp_members', $member_data );
                $member_id = $wpdb->insert_id;

                if ( $member_id ) {
                    // Insert contact
                    $contact_data['member_id'] = $member_id;
                    $wpdb->insert( $wpdb->prefix . 'sp_member_contact', $contact_data );

                    // Redirect to the new member
                    wp_redirect( $router->url( 'members', [ 'id' => $member_id ] ) . '?success=created' );
                    exit;
                }
            } else {
                // Update existing member
                $member_data['updated_at'] = current_time( 'mysql' );

                $wpdb->update(
                    $wpdb->prefix . 'sp_members',
                    $member_data,
                    [ 'id' => $member_id ]
                );

                // Update or insert contact
                if ( $contact ) {
                    $wpdb->update(
                        $wpdb->prefix . 'sp_member_contact',
                        $contact_data,
                        [ 'member_id' => $member_id ]
                    );
                } else {
                    $contact_data['member_id'] = $member_id;
                    $wpdb->insert( $wpdb->prefix . 'sp_member_contact', $contact_data );
                }

                // Redirect back with success message
                wp_redirect( $router->url( 'members', [ 'id' => $member_id ] ) . '?success=saved' );
                exit;
            }
        }
    }
}

// Default values for new member
if ( $is_new ) {
    $member = (object) [
        'id'               => 0,
        'first_name'       => '',
        'middle_name'      => '',
        'last_name'        => '',
        'membership_tier_id' => 1,
        'status'           => 'active',
        'join_date'        => date( 'Y-m-d' ),
        'expiration_date'  => date( 'Y-12-31' ),
        'directory_visible' => 1,
        'communication_preference' => 'email',
    ];
    $contact = (object) [
        'primary_email'   => '',
        'secondary_email' => '',
        'home_phone'      => '',
        'cell_phone'      => '',
        'street_address'  => '',
        'address_line_2'  => '',
        'city'            => 'Springfield',
        'state_province'  => 'TX',
        'postal_code'     => '',
        'country'         => 'USA',
    ];
}

// Page title
$page_title = $is_new ? 'Add New Member' : ( $is_edit ? 'Edit Member' : 'Member Details' );
$member_name = $member ? trim( $member->first_name . ' ' . $member->last_name ) : 'New Member';
?>

<a href="<?php echo esc_url( $router->url( 'members' ) ); ?>" class="sp-back-link">
    ← Back to Members
</a>

<header class="sp-admin-page-header">
    <h1 class="sp-admin-page-title">
        <?php echo $is_new ? 'Add New Member' : esc_html( $member_name ); ?>
    </h1>
    <?php if ( ! $is_new && ! $is_edit ) : ?>
        <a href="<?php echo esc_url( $router->url( 'members', [ 'id' => $member_id, 'action' => 'edit' ] ) ); ?>"
           class="sp-button sp-button--primary">
            Edit Member
        </a>
    <?php endif; ?>
</header>

<?php if ( $is_edit ) : ?>
<form method="post" action="" data-sp-form>
    <?php wp_nonce_field( 'sp_save_member', 'sp_member_nonce' ); ?>
<?php endif; ?>

    <!-- Name Section -->
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Name</legend>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="first_name" class="sp-form-label sp-form-label--required">First Name</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="first_name" name="first_name" class="sp-input"
                           value="<?php echo esc_attr( $member->first_name ); ?>" required>
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $member->first_name ); ?></div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="middle_name" class="sp-form-label">Middle Name</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="middle_name" name="middle_name" class="sp-input"
                           value="<?php echo esc_attr( $member->middle_name ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $member->middle_name ?: '—' ); ?></div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="last_name" class="sp-form-label sp-form-label--required">Last Name</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="last_name" name="last_name" class="sp-input"
                           value="<?php echo esc_attr( $member->last_name ); ?>" required>
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $member->last_name ); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <!-- Membership Section -->
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Membership</legend>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="status" class="sp-form-label">Status</label>
                <?php if ( $is_edit ) : ?>
                    <select id="status" name="status" class="sp-select">
                        <option value="active" <?php selected( $member->status, 'active' ); ?>>Active</option>
                        <option value="expired" <?php selected( $member->status, 'expired' ); ?>>Expired</option>
                        <option value="pending" <?php selected( $member->status, 'pending' ); ?>>Pending</option>
                        <option value="cancelled" <?php selected( $member->status, 'cancelled' ); ?>>Cancelled</option>
                        <option value="deceased" <?php selected( $member->status, 'deceased' ); ?>>Deceased</option>
                    </select>
                <?php else : ?>
                    <div class="sp-form-value">
                        <span class="sp-status sp-status--<?php echo esc_attr( $member->status ); ?>">
                            <?php echo esc_html( ucfirst( $member->status ) ); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="membership_tier_id" class="sp-form-label">Membership Level</label>
                <?php if ( $is_edit ) : ?>
                    <select id="membership_tier_id" name="membership_tier_id" class="sp-select">
                        <?php foreach ( $all_tiers as $tier ) : ?>
                            <option value="<?php echo $tier->id; ?>" <?php selected( $member->membership_tier_id, $tier->id ); ?>>
                                <?php echo esc_html( $tier->name ); ?>
                                <?php if ( $tier->price > 0 ) : ?>
                                    ($<?php echo number_format( $tier->price, 2 ); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <?php
                    $tier_name = '—';
                    foreach ( $all_tiers as $tier ) {
                        if ( $tier->id == $member->membership_tier_id ) {
                            $tier_name = $tier->name;
                            break;
                        }
                    }
                    ?>
                    <div class="sp-form-value"><?php echo esc_html( $tier_name ); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="join_date" class="sp-form-label">Join Date</label>
                <?php if ( $is_edit ) : ?>
                    <input type="date" id="join_date" name="join_date" class="sp-input"
                           value="<?php echo esc_attr( $member->join_date ); ?>">
                <?php else : ?>
                    <div class="sp-form-value">
                        <?php echo $member->join_date ? date( 'F j, Y', strtotime( $member->join_date ) ) : '—'; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="expiration_date" class="sp-form-label">Expiration Date</label>
                <?php if ( $is_edit ) : ?>
                    <input type="date" id="expiration_date" name="expiration_date" class="sp-input"
                           value="<?php echo esc_attr( $member->expiration_date ); ?>">
                <?php else : ?>
                    <div class="sp-form-value">
                        <?php echo $member->expiration_date ? date( 'F j, Y', strtotime( $member->expiration_date ) ) : '—'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <!-- Contact Section -->
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Contact Information</legend>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="primary_email" class="sp-form-label sp-form-label--required">Email</label>
                <?php if ( $is_edit ) : ?>
                    <input type="email" id="primary_email" name="primary_email" class="sp-input"
                           value="<?php echo esc_attr( $contact->primary_email ?? '' ); ?>" required>
                <?php else : ?>
                    <div class="sp-form-value">
                        <?php if ( ! empty( $contact->primary_email ) ) : ?>
                            <a href="mailto:<?php echo esc_attr( $contact->primary_email ); ?>">
                                <?php echo esc_html( $contact->primary_email ); ?>
                            </a>
                        <?php else : ?>
                            —
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="secondary_email" class="sp-form-label">Secondary Email</label>
                <?php if ( $is_edit ) : ?>
                    <input type="email" id="secondary_email" name="secondary_email" class="sp-input"
                           value="<?php echo esc_attr( $contact->secondary_email ?? '' ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->secondary_email ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="cell_phone" class="sp-form-label">Cell Phone</label>
                <?php if ( $is_edit ) : ?>
                    <input type="tel" id="cell_phone" name="cell_phone" class="sp-input"
                           value="<?php echo esc_attr( $contact->cell_phone ?? '' ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->cell_phone ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="home_phone" class="sp-form-label">Home Phone</label>
                <?php if ( $is_edit ) : ?>
                    <input type="tel" id="home_phone" name="home_phone" class="sp-input"
                           value="<?php echo esc_attr( $contact->home_phone ?? '' ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->home_phone ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="sp-form-group">
            <label for="street_address" class="sp-form-label">Street Address</label>
            <?php if ( $is_edit ) : ?>
                <input type="text" id="street_address" name="street_address" class="sp-input"
                       value="<?php echo esc_attr( $contact->street_address ?? '' ); ?>">
            <?php else : ?>
                <div class="sp-form-value"><?php echo esc_html( $contact->street_address ?? '' ) ?: '—'; ?></div>
            <?php endif; ?>
        </div>

        <div class="sp-form-group">
            <label for="address_line_2" class="sp-form-label">Address Line 2</label>
            <?php if ( $is_edit ) : ?>
                <input type="text" id="address_line_2" name="address_line_2" class="sp-input"
                       value="<?php echo esc_attr( $contact->address_line_2 ?? '' ); ?>">
            <?php else : ?>
                <div class="sp-form-value"><?php echo esc_html( $contact->address_line_2 ?? '' ) ?: '—'; ?></div>
            <?php endif; ?>
        </div>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label for="city" class="sp-form-label">City</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="city" name="city" class="sp-input"
                           value="<?php echo esc_attr( $contact->city ?? '' ); ?>">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->city ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="state_province" class="sp-form-label">State</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="state_province" name="state_province" class="sp-input"
                           value="<?php echo esc_attr( $contact->state_province ?? '' ); ?>"
                           maxlength="2" style="width: 80px;">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->state_province ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <label for="postal_code" class="sp-form-label">ZIP Code</label>
                <?php if ( $is_edit ) : ?>
                    <input type="text" id="postal_code" name="postal_code" class="sp-input"
                           value="<?php echo esc_attr( $contact->postal_code ?? '' ); ?>" style="width: 120px;">
                <?php else : ?>
                    <div class="sp-form-value"><?php echo esc_html( $contact->postal_code ?? '' ) ?: '—'; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <!-- Preferences Section -->
    <fieldset class="sp-fieldset">
        <legend class="sp-legend">Preferences</legend>

        <div class="sp-form-row">
            <div class="sp-form-group">
                <label class="sp-form-label">Communication Preference</label>
                <?php if ( $is_edit ) : ?>
                    <select id="communication_preference" name="communication_preference" class="sp-select">
                        <option value="email" <?php selected( $member->communication_preference ?? '', 'email' ); ?>>Email Only</option>
                        <option value="mail" <?php selected( $member->communication_preference ?? '', 'mail' ); ?>>Mail Only</option>
                        <option value="both" <?php selected( $member->communication_preference ?? '', 'both' ); ?>>Email and Mail</option>
                    </select>
                <?php else : ?>
                    <div class="sp-form-value">
                        <?php
                        $prefs = [
                            'email' => 'Email Only',
                            'mail' => 'Mail Only',
                            'both' => 'Email and Mail',
                        ];
                        echo esc_html( $prefs[ $member->communication_preference ?? 'email' ] ?? 'Email Only' );
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sp-form-group">
                <?php if ( $is_edit ) : ?>
                    <label class="sp-checkbox-label">
                        <input type="checkbox" name="directory_visible" value="1" class="sp-checkbox"
                               <?php checked( $member->directory_visible ?? 1, 1 ); ?>>
                        Show in Member Directory
                    </label>
                <?php else : ?>
                    <label class="sp-form-label">Directory Visibility</label>
                    <div class="sp-form-value">
                        <?php echo ( $member->directory_visible ?? 1 ) ? 'Visible' : 'Hidden'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </fieldset>

    <?php if ( $is_edit ) : ?>
    <div class="sp-form-actions" style="display: flex; gap: var(--sp-spacing-md); margin-top: var(--sp-spacing-xl);">
        <button type="submit" class="sp-button sp-button--primary sp-button--large">
            <?php echo $is_new ? 'Add Member' : 'Save Changes'; ?>
        </button>
        <a href="<?php echo esc_url( $is_new ? $router->url( 'members' ) : $router->url( 'members', [ 'id' => $member_id ] ) ); ?>"
           class="sp-button sp-button--secondary sp-button--large">
            Cancel
        </a>
    </div>
</form>
<?php endif; ?>

<style>
.sp-form-value {
    padding: var(--sp-spacing-sm) 0;
    font-size: var(--sp-font-size-base);
    color: var(--sp-gray-800);
}
</style>
