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
LOCAL_BASE="$(cd "$(dirname "$0")/.." && pwd)"

deploy_plugin() {
    echo "Deploying plugin to demo site..."
    # Main plugin file
    scp "$LOCAL_BASE/Code/plugin/societypress.php" "$HOST:$DEMO_BASE/plugins/societypress/societypress.php"
    # Translation template
    scp "$LOCAL_BASE/Code/plugin/languages/societypress.pot" "$HOST:$DEMO_BASE/plugins/societypress/languages/societypress.pot" 2>/dev/null
    # PWA icons and other assets
    scp -r "$LOCAL_BASE/Code/plugin/assets/"* "$HOST:$DEMO_BASE/plugins/societypress/assets/" 2>/dev/null
    echo "Plugin deployed."
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
        deploy_theme "theme" "societypress" "$DEMO_BASE"
        for t in heritage coastline prairie ledger parlor; do
            if [ -d "$LOCAL_BASE/Code/theme-$t" ]; then
                deploy_theme "theme-$t" "societypress-$t" "$DEMO_BASE"
            fi
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
