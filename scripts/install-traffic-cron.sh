#!/bin/bash
# SocietyPress — install (or remove) the daily traffic snapshot job
#
# WHY LAUNCHD AND NOT CRON: on macOS, a plain crontab entry does not run if the
# machine is asleep at the scheduled minute and it never catches up — the run is
# simply skipped. launchd with RunAtLoad plus StartCalendarInterval will fire the
# missed job once the Mac wakes. Given GitHub discards traffic data after 14
# days, a silently skipped run is real data loss, so the catch-up behaviour
# matters more than it normally would.
#
# Usage:
#   ./scripts/install-traffic-cron.sh            Install and start the job
#   ./scripts/install-traffic-cron.sh --remove   Stop and uninstall it
#   ./scripts/install-traffic-cron.sh --status   Show whether it is loaded

set -o pipefail

LABEL="org.societypress.traffic-snapshot"
PLIST="$HOME/Library/LaunchAgents/$LABEL.plist"
REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
SOURCE_SCRIPT="$REPO_DIR/scripts/snapshot-traffic.sh"
LOG_DIR="${SP_ANALYTICS_DIR:-$HOME/Library/Application Support/SocietyPress/analytics}"

# WHY THE SCRIPT IS COPIED OUT OF THE REPO INSTEAD OF RUN IN PLACE:
# the clone lives under ~/Documents, which macOS protects with TCC. A launchd
# agent has none of Terminal's granted permissions, so pointing the job at the
# repo path fails with "Operation not permitted" and LastExitStatus 32256 — the
# job loads, reports success, and silently never produces a snapshot.
#
# The alternative is granting Full Disk Access to /bin/bash, which hands every
# script on the machine access to everything. Copying the one script into
# ~/Library/Application Support (outside TCC's protected set) is a far smaller
# blast radius. Re-run this installer after editing snapshot-traffic.sh to
# refresh the copy.
RUNTIME_DIR="$HOME/Library/Application Support/SocietyPress/bin"
SCRIPT="$RUNTIME_DIR/snapshot-traffic.sh"

case "$1" in
    --remove)
        launchctl unload "$PLIST" 2>/dev/null
        rm -f "$PLIST"
        echo "Removed $LABEL"
        exit 0
        ;;
    --status)
        if launchctl list | grep -q "$LABEL"; then
            echo "LOADED: $LABEL"
            launchctl list "$LABEL" 2>/dev/null | grep -E 'LastExitStatus|PID' || true
        else
            echo "NOT LOADED: $LABEL"
        fi
        echo "plist: $PLIST"
        exit 0
        ;;
esac

if [ ! -x "$SOURCE_SCRIPT" ]; then
    echo "ERROR: $SOURCE_SCRIPT is missing or not executable." >&2
    exit 1
fi

mkdir -p "$HOME/Library/LaunchAgents" "$LOG_DIR" "$RUNTIME_DIR"

# Refresh the runtime copy from the repo on every install.
cp "$SOURCE_SCRIPT" "$SCRIPT"
chmod +x "$SCRIPT"

# PATH is set explicitly because launchd jobs do NOT inherit the interactive
# shell's environment. Without it, `gh` and `jq` are not found and every run
# fails with a message nobody reads.
cat > "$PLIST" <<PLISTEOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>Label</key>
    <string>$LABEL</string>

    <key>ProgramArguments</key>
    <array>
        <string>$SCRIPT</string>
    </array>

    <key>EnvironmentVariables</key>
    <dict>
        <key>PATH</key>
        <string>/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin</string>
    </dict>

    <!-- Run once a day at 09:00. GitHub's window is 14 days, so daily leaves a
         wide margin: the history stays complete even after a fortnight away. -->
    <key>StartCalendarInterval</key>
    <dict>
        <key>Hour</key>
        <integer>9</integer>
        <key>Minute</key>
        <integer>0</integer>
    </dict>

    <!-- Fire on load too, so installing the job immediately proves it works
         rather than leaving you waiting until tomorrow to find out it doesn't. -->
    <key>RunAtLoad</key>
    <true/>

    <key>StandardOutPath</key>
    <string>$LOG_DIR/snapshot.log</string>
    <key>StandardErrorPath</key>
    <string>$LOG_DIR/snapshot.err</string>
</dict>
</plist>
PLISTEOF

launchctl unload "$PLIST" 2>/dev/null
if launchctl load "$PLIST" 2>/dev/null; then
    echo "Installed and loaded: $LABEL"
    echo "  runs daily at 09:00 (and once now)"
    echo "  script: $SCRIPT"
    echo "  logs:   $LOG_DIR/snapshot.log"
    echo
    echo "Check it with:  ./scripts/install-traffic-cron.sh --status"
    echo "Remove it with: ./scripts/install-traffic-cron.sh --remove"
else
    echo "ERROR: launchctl load failed for $PLIST" >&2
    exit 1
fi
