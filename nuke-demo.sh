#!/bin/bash
# SocietyPress Demo Nuke & Reinstall Script
#
# WHY: During installer development we need to repeatedly wipe the demo site
#      back to absolute zero and test a fresh install from scratch. This script
#      drops all database tables, deletes all files, and uploads a fresh
#      sp-installer.php ready to run.
#
# Usage:
#   ./nuke-demo.sh              Nuke everything + upload fresh installer
#   ./nuke-demo.sh --no-prompt  Skip confirmation (for scripted use)

HOST="skystra"
DEMO_PATH="~/domains/getsocietypress.org/public_html/demo"
WP="php -d disable_functions='' /usr/local/bin/wp"
INSTALLER_SRC="$(dirname "$0")/installer/sp-installer.php"
SSH_KEY="$HOME/.ssh/claude_code_rsa"
REMOTE_HOST="charle24@axm97k5-compute.skystra.com"

echo "=== SocietyPress Demo NUKE ==="
echo "Target: demo.getsocietypress.org"
echo ""
echo "This will DESTROY everything:"
echo "  - All database tables"
echo "  - All WordPress files"
echo "  - All uploads, themes, plugins"
echo ""

if [ "$1" != "--no-prompt" ]; then
    read -p "Type 'nuke' to confirm: " confirm
    if [ "$confirm" != "nuke" ]; then
        echo "Aborted."
        exit 0
    fi
fi

echo ""

# Step 1: Reset database (drop all tables)
echo "Dropping all database tables..."
ssh "$HOST" "cd $DEMO_PATH && $WP db reset --yes 2>/dev/null" 2>/dev/null
if [ $? -ne 0 ]; then
    # wp-config.php might not exist yet — that's OK on a truly empty dir
    echo "  (no database to reset — directory may already be empty)"
fi

# Step 2: Delete all files
echo "Deleting all files..."
ssh "$HOST" "rm -rf ${DEMO_PATH}/* ${DEMO_PATH}/.htaccess ${DEMO_PATH}/.maintenance 2>/dev/null"

# Step 3: Verify empty
file_count=$(ssh "$HOST" "ls -1A ${DEMO_PATH}/ 2>/dev/null | wc -l")
if [ "$file_count" -gt 0 ]; then
    echo "WARNING: Directory not empty after cleanup ($file_count files remain)"
    ssh "$HOST" "ls -la ${DEMO_PATH}/"
    exit 1
fi

# Step 4: Upload fresh installer
echo "Uploading fresh installer..."
if [ ! -f "$INSTALLER_SRC" ]; then
    echo "ERROR: Installer not found at $INSTALLER_SRC"
    exit 1
fi
scp -i "$SSH_KEY" "$INSTALLER_SRC" "${REMOTE_HOST}:${DEMO_PATH}/sp-installer.php" 2>/dev/null

echo ""
echo "Done. Fresh installer ready at:"
echo "  https://demo.getsocietypress.org/sp-installer.php"
