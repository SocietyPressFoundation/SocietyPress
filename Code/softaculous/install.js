/**
 * SocietyPress — Softaculous Install Validation
 *
 * WHY: Softaculous calls formcheck() before submitting the install form.
 * We validate that the required fields are filled in so Harold doesn't
 * get a cryptic server error.
 */
function formcheck() {
    var user  = document.getElementsByName('admin_username')[0];
    var pass  = document.getElementsByName('admin_pass')[0];
    var email = document.getElementsByName('admin_email')[0];

    if ( !user || !user.value.trim() ) {
        alert('Please enter an admin username.');
        return false;
    }

    if ( !pass || !pass.value.trim() ) {
        alert('Please enter an admin password.');
        return false;
    }

    if ( pass.value.length < 8 ) {
        alert('Admin password must be at least 8 characters.');
        return false;
    }

    if ( !email || !email.value.trim() ) {
        alert('Please enter an admin email address.');
        return false;
    }

    // Basic email validation
    if ( !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email.value ) ) {
        alert('Please enter a valid email address.');
        return false;
    }

    return true;
}
