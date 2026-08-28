//////////////////////////////////////////////////////////////
// SocietyPress — Softaculous Install Validation
//
// WHY: Softaculous calls formcheck() before submitting the install
// form. Catching an empty or malformed field here means Harold sees a
// plain sentence about what to fix, instead of a server-side failure
// partway through an install.
//
// The alert text is interpolated from info.xml's <languages> block via
// {{key}} rather than written in English here — Softaculous renders its
// panel in the host's language, and hardcoded strings would be the one
// English thing on an otherwise translated page.
//////////////////////////////////////////////////////////////

function formcheck() {

	var user  = document.getElementsByName('admin_username')[0];
	var pass  = document.getElementsByName('admin_pass')[0];
	var email = document.getElementsByName('admin_email')[0];

	if ( !user || !user.value.trim() ) {
		alert('{{err_adname}}');
		return false;
	}

	if ( !pass || !pass.value.trim() ) {
		alert('{{err_adpass}}');
		return false;
	}

	if ( pass.value.length < 8 ) {
		alert('{{err_adpass_short}}');
		return false;
	}

	if ( !email || !email.value.trim() ) {
		alert('{{err_ademail}}');
		return false;
	}

	// Softaculous ships check_punycode() for internationalised domains; use it
	// when present so an address at a non-ASCII domain is not wrongly rejected,
	// and fall back to a shape check when it is not.
	if ( window.check_punycode ) {
		if ( !check_punycode( email.value ) ) {
			alert('{{err_ademail}}');
			return false;
		}
	} else if ( !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email.value ) ) {
		alert('{{err_ademail}}');
		return false;
	}

	return true;
}
