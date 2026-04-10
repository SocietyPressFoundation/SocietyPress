#!/bin/bash
# SocietyPress Deploy Script
# Deploys plugin and/or themes to demo.getsocietypress.org and samplesociety.com.
#
# Usage:
#   ./scripts/deploy.sh              Deploy plugin only
#   ./scripts/deploy.sh plugin       Deploy plugin only
#   ./scripts/deploy.sh theme        Deploy parent theme only
#   ./scripts/deploy.sh all          Deploy everything
#   ./scripts/deploy.sh <theme-name> Deploy a specific child theme (heritage, coastline, prairie, ledger, parlor)
#   ./scripts/deploy.sh installer    Deploy the one-click installer
#   ./scripts/deploy.sh bundle       Build and upload societypress-latest.zip for the installer
#   ./scripts/deploy.sh marketing    Deploy the getsocietypress.org theme
#
# WHY the structure: each target deploys to BOTH active sites (demo + upstream-society)
# via a per-site helper function. Helpers check the scp exit code so a failed
# copy is reported as FAILED rather than silently swallowed — an earlier
# version of this script echoed success on every call regardless of scp's
# return code, which masked the fact that the demo site is currently empty
# and every demo deploy was silently failing.

set -o pipefail

HOST="skystra"
DEMO_BASE="~/domains/getsocietypress.org/public_html/demo/wp-content"
the society_BASE="~/domains/samplesociety.com/public_html/wp-content"
LOCAL_BASE="$(cd "$(dirname "$0")/.." && pwd)"

# Track overall exit status — any failed site-level deploy flips this to 1
# so the script exits non-zero at the end. Individual per-site failures still
# print a clear FAILED line but don't abort the rest of the run (so a dead
# demo doesn't prevent the upstream-society deploy from completing).
OVERALL_STATUS=0

# ----------------------------------------------------------------------------
# Per-site plugin deploy.
# ----------------------------------------------------------------------------
# $1 = target wp-content base path on the server (tilde-prefixed, ssh-side)
# $2 = human label for log output
# ----------------------------------------------------------------------------
deploy_plugin_to() {
    local target_base="$1"
    local label="$2"
    echo "Deploying plugin to $label..."

    # Main plugin PHP file. If the parent directory doesn't exist on the
    # server (e.g. the site isn't provisioned), scp will fail with
    # "No such file or directory" — catch that and mark the deploy as
    # FAILED so the script exit code reflects reality.
    if ! scp "$LOCAL_BASE/Code/plugin/societypress.php" "$HOST:$target_base/plugins/societypress/societypress.php"; then
        echo "  FAILED: $label plugin scp did not complete."
        OVERALL_STATUS=1
        return 1
    fi

    # Optional POT file — not required for deploy success (suppress errors)
    scp "$LOCAL_BASE/Code/plugin/languages/societypress.pot" "$HOST:$target_base/plugins/societypress/languages/societypress.pot" 2>/dev/null || true

    # Optional assets directory — same (suppress errors)
    if [ -d "$LOCAL_BASE/Code/plugin/assets" ]; then
        scp -r "$LOCAL_BASE/Code/plugin/assets/"* "$HOST:$target_base/plugins/societypress/assets/" 2>/dev/null || true
    fi

    echo "  OK: plugin deployed to $label."
}

# ----------------------------------------------------------------------------
# Per-site theme deploy (parent OR child).
# ----------------------------------------------------------------------------
# $1 = local directory name under Code/ (e.g. "theme" or "theme-heritage")
# $2 = theme directory name on the server (e.g. "societypress" or "heritage")
# $3 = target wp-content base path on the server
# $4 = human label for log output
# ----------------------------------------------------------------------------
deploy_theme_to() {
    local local_dir="$1"
    local theme_name="$2"
    local target_base="$3"
    local label="$4"
    echo "Deploying $theme_name theme to $label..."

    if [ ! -d "$LOCAL_BASE/Code/$local_dir" ]; then
        echo "  SKIP: local directory Code/$local_dir does not exist."
        return 0
    fi

    if ! scp -r "$LOCAL_BASE/Code/$local_dir/"* "$HOST:$target_base/themes/$theme_name/"; then
        echo "  FAILED: $theme_name theme scp to $label did not complete."
        OVERALL_STATUS=1
        return 1
    fi

    echo "  OK: $theme_name theme deployed to $label."
}

# ----------------------------------------------------------------------------
# Both-sites wrappers. These call the per-site helpers for demo + upstream-society so
# every target naturally hits both environments without special-casing.
# ----------------------------------------------------------------------------
deploy_plugin_all_sites() {
    deploy_plugin_to "$DEMO_BASE" "demo.getsocietypress.org"
    deploy_plugin_to "$the society_BASE" "samplesociety.com"
}

deploy_theme_all_sites() {
    local local_dir="$1"
    local theme_name="$2"
    deploy_theme_to "$local_dir" "$theme_name" "$DEMO_BASE" "demo.getsocietypress.org"
    deploy_theme_to "$local_dir" "$theme_name" "$the society_BASE" "samplesociety.com"
}

# ----------------------------------------------------------------------------
# Main dispatch.
# ----------------------------------------------------------------------------
case "${1:-plugin}" in
    plugin)
        deploy_plugin_all_sites
        ;;
    theme)
        # Parent theme deploys to themes/societypress/ on both sites.
        deploy_theme_all_sites "theme" "societypress"
        ;;
    heritage|coastline|prairie|ledger|parlor)
        # Child themes are installed under their short name (e.g. themes/heritage/),
        # not under "societypress-heritage/". The local source lives in
        # Code/theme-heritage/ etc.
        deploy_theme_all_sites "theme-$1" "$1"
        ;;
    all)
        deploy_plugin_all_sites
        deploy_theme_all_sites "theme" "societypress"
        for t in heritage coastline prairie ledger parlor; do
            if [ -d "$LOCAL_BASE/Code/theme-$t" ]; then
                deploy_theme_all_sites "theme-$t" "$t"
            fi
        done
        ;;
    bundle)
        # Build societypress-latest.zip and upload to getsocietypress.org/downloads/
        # WHY: The installer downloads this bundle on fresh installs. If it's stale,
        # new installs get an old plugin version. Run this after every version bump.
        echo "Building SocietyPress bundle..."
        BUNDLE_DIR=$(mktemp -d)
        BUNDLE_STAGING="$BUNDLE_DIR/staging"

        # Plugin → societypress/
        mkdir -p "$BUNDLE_STAGING/societypress"
        cp "$LOCAL_BASE/Code/plugin/societypress.php" "$BUNDLE_STAGING/societypress/"
        cp -r "$LOCAL_BASE/Code/plugin/assets" "$BUNDLE_STAGING/societypress/" 2>/dev/null || true
        mkdir -p "$BUNDLE_STAGING/societypress/languages"
        cp "$LOCAL_BASE/Code/plugin/languages/societypress.pot" "$BUNDLE_STAGING/societypress/languages/" 2>/dev/null || true

        # Parent theme → themes/societypress/
        mkdir -p "$BUNDLE_STAGING/themes/societypress"
        cp -r "$LOCAL_BASE/Code/theme/"* "$BUNDLE_STAGING/themes/societypress/"

        # Child themes → themes/<name>/
        for t in heritage coastline prairie ledger parlor; do
            if [ -d "$LOCAL_BASE/Code/theme-$t" ]; then
                mkdir -p "$BUNDLE_STAGING/themes/$t"
                cp -r "$LOCAL_BASE/Code/theme-$t/"* "$BUNDLE_STAGING/themes/$t/"
            fi
        done

        # Remove .DS_Store files
        find "$BUNDLE_STAGING" -name '.DS_Store' -delete

        # Build the ZIP
        BUNDLE_ZIP="$BUNDLE_DIR/societypress-latest.zip"
        (cd "$BUNDLE_STAGING" && zip -r "$BUNDLE_ZIP" . -x '*.DS_Store')

        # Upload
        ssh "$HOST" "mkdir -p ~/domains/getsocietypress.org/public_html/downloads"
        if ! scp "$BUNDLE_ZIP" "$HOST:~/domains/getsocietypress.org/public_html/downloads/societypress-latest.zip"; then
            echo "  FAILED: bundle upload did not complete."
            OVERALL_STATUS=1
        fi

        # Show version for confirmation
        BUNDLE_VERSION=$(grep "define.*SOCIETYPRESS_VERSION" "$LOCAL_BASE/Code/plugin/societypress.php" | head -1 | sed "s/.*'\\(.*\\)'.*/\\1/")
        echo "Bundle built: societypress-latest.zip (plugin v$BUNDLE_VERSION)"

        # Cleanup
        rm -rf "$BUNDLE_DIR"
        ;;
    installer)
        echo "Deploying installer..."
        if ! scp "$LOCAL_BASE/Code/installer/sp-installer.php" "$HOST:~/domains/getsocietypress.org/public_html/sp-installer.php"; then
            echo "  FAILED: installer scp did not complete."
            OVERALL_STATUS=1
        else
            echo "  OK: installer deployed."
        fi
        ;;
    marketing)
        echo "Deploying marketing site theme..."
        if ! scp -r "$LOCAL_BASE/Code/marketing-theme/"* "$HOST:~/domains/getsocietypress.org/public_html/cms/wp-content/themes/getsocietypress/"; then
            echo "  FAILED: marketing theme scp did not complete."
            OVERALL_STATUS=1
        else
            echo "  OK: marketing theme deployed."
        fi
        ;;
    *)
        echo "Usage: $0 [plugin|theme|heritage|coastline|prairie|ledger|parlor|installer|bundle|marketing|all]"
        exit 1
        ;;
esac

if [ $OVERALL_STATUS -ne 0 ]; then
    echo ""
    echo "One or more deploys FAILED. See messages above."
fi
exit $OVERALL_STATUS
