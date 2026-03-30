/**
 * SocietyPress — Softaculous Upgrade Validation
 *
 * WHY: Softaculous calls formcheck() before running an upgrade.
 * Upgrades don't need any user input — files are just replaced —
 * so we always return true.
 */
function formcheck() {
    return true;
}
