#!/bin/bash
# SocietyPress Deploy Script
# Deploys plugin and/or themes to demo.getsocietypress.org
#
# Usage:
#   ./deploy.sh              Deploy plugin only
#   ./deploy.sh plugin       Deploy plugin only
#   ./deploy.sh theme        Deploy parent theme only
#   ./deploy.sh saghs        Deploy SAGHS child theme only
#   ./deploy.sh all          Deploy everything
#   ./deploy.sh <theme-name> Deploy a specific child theme (e.g., heritage, coastline)

HOST="skystra"
DEMO_BASE="~/domains/getsocietypress.org/public_html/demo/wp-content"
KNDGS_BASE="~/domains/kndgs.org/public_html/cms/wp-content"
LOCAL_BASE="$(cd "$(dirname "$0")" && pwd)"

deploy_plugin() {
    echo "Deploying plugin to demo site..."
    scp "$LOCAL_BASE/plugin/societypress.php" "$HOST:$DEMO_BASE/plugins/societypress/societypress.php"
    echo "Plugin deployed."
}

deploy_theme() {
    local theme_dir="$1"
    local theme_name="$2"
    local target_base="$3"
    echo "Deploying $theme_name theme..."
    scp -r "$LOCAL_BASE/$theme_dir/"* "$HOST:$target_base/themes/$theme_name/"
    echo "$theme_name theme deployed."
}

case "${1:-plugin}" in
    plugin)
        deploy_plugin
        ;;
    theme)
        deploy_theme "theme" "societypress" "$DEMO_BASE"
        ;;
    saghs)
        deploy_theme "theme-saghs" "saghs" "$KNDGS_BASE"
        ;;
    heritage|coastline|prairie|ledger|parlor)
        deploy_theme "theme-$1" "societypress-$1" "$DEMO_BASE"
        ;;
    all)
        deploy_plugin
        deploy_theme "theme" "societypress" "$DEMO_BASE"
        for t in heritage coastline prairie ledger parlor; do
            if [ -d "$LOCAL_BASE/theme-$t" ]; then
                deploy_theme "theme-$t" "societypress-$t" "$DEMO_BASE"
            fi
        done
        ;;
    *)
        echo "Usage: $0 [plugin|theme|saghs|heritage|coastline|prairie|ledger|parlor|all]"
        exit 1
        ;;
esac
