#!/bin/bash
# SocietyPress Deploy Script
# Deploys plugin and/or themes to demo.getsocietypress.org
#
# Usage:
#   ./scripts/deploy.sh              Deploy plugin only
#   ./scripts/deploy.sh plugin       Deploy plugin only
#   ./scripts/deploy.sh theme        Deploy parent theme only
#   ./scripts/deploy.sh all          Deploy everything
#   ./scripts/deploy.sh <theme-name> Deploy a specific child theme (e.g., heritage, coastline)
#   ./scripts/deploy.sh installer   Deploy the one-click installer
#   ./scripts/deploy.sh marketing   Deploy the getsocietypress.org theme

HOST="skystra"
DEMO_BASE="~/domains/getsocietypress.org/public_html/demo/wp-content"
the society_BASE="~/domains/samplesociety.com/public_html/wp-content"
LOCAL_BASE="$(cd "$(dirname "$0")/.." && pwd)"

deploy_plugin_to() {
    local target_base="$1"
    local label="$2"
    echo "Deploying plugin to $label..."
    scp "$LOCAL_BASE/Code/plugin/societypress.php" "$HOST:$target_base/plugins/societypress/societypress.php"
    scp "$LOCAL_BASE/Code/plugin/languages/societypress.pot" "$HOST:$target_base/plugins/societypress/languages/societypress.pot" 2>/dev/null
    scp -r "$LOCAL_BASE/Code/plugin/assets/"* "$HOST:$target_base/plugins/societypress/assets/" 2>/dev/null
    echo "Plugin deployed to $label."
}

deploy_plugin() {
    deploy_plugin_to "$DEMO_BASE" "demo site"
    deploy_plugin_to "$the society_BASE" "samplesociety.com"
}

deploy_theme() {
    local theme_dir="$1"
    local theme_name="$2"
    local target_base="$3"
    echo "Deploying $theme_name theme..."
    scp -r "$LOCAL_BASE/Code/$theme_dir/"* "$HOST:$target_base/themes/$theme_name/"
    echo "$theme_name theme deployed."
}

case "${1:-plugin}" in
    plugin)
        deploy_plugin
        ;;
    theme)
        deploy_theme "theme" "societypress" "$DEMO_BASE"
        ;;
    heritage|coastline|prairie|ledger|parlor)
        deploy_theme "theme-$1" "societypress-$1" "$DEMO_BASE"
        ;;
    all)
        deploy_plugin
        for base in "$DEMO_BASE" "$the society_BASE"; do
            deploy_theme "theme" "societypress" "$base"
            for t in heritage coastline prairie ledger parlor; do
                if [ -d "$LOCAL_BASE/Code/theme-$t" ]; then
                    deploy_theme "theme-$t" "societypress-$t" "$base"
                fi
            done
        done
        ;;
    installer)
        echo "Deploying installer..."
        scp "$LOCAL_BASE/Code/installer/sp-installer.php" "$HOST:~/domains/getsocietypress.org/public_html/sp-installer.php"
        echo "Installer deployed."
        ;;
    marketing)
        echo "Deploying marketing site theme..."
        scp -r "$LOCAL_BASE/Code/marketing-theme/"* "$HOST:~/domains/getsocietypress.org/public_html/cms/wp-content/themes/getsocietypress/" 2>/dev/null
        echo "Marketing theme deployed."
        ;;
    *)
        echo "Usage: $0 [plugin|theme|heritage|coastline|prairie|ledger|parlor|installer|marketing|all]"
        exit 1
        ;;
esac
