#!/bin/bash
# SocietyPress — read the getsocietypress.org download log
#
# The log is written by the gsp-download-log mu-plugin on the marketing site and
# lives outside the web root, so it can only be read over SSH. This script
# fetches and summarises it.
#
# WHY THE BOT SPLIT IS THE HEADLINE: a raw download total cannot tell you
# whether real societies have found the project. Crawlers, mirrors and scanners
# all increment it. The plugin flags obvious automation at write time, so the
# only number worth reading — how many actual people downloaded this — is the
# non-bot count.
#
# Usage:
#   ./scripts/download-report.sh           Summary
#   ./scripts/download-report.sh --raw     Dump every log line
#   ./scripts/download-report.sh --humans  Only entries that look like people

set -o pipefail

HOST="skystra"
REMOTE_LOG="~/domains/getsocietypress.org/gsp-analytics/downloads.jsonl"
TMP="$(mktemp -t spdl)"
trap 'rm -f "$TMP"' EXIT

if ! ssh "$HOST" "cat $REMOTE_LOG" > "$TMP" 2>/dev/null; then
    echo "Could not read the download log on $HOST."
    echo "If nothing has been downloaded since logging was installed, the file"
    echo "will not exist yet — that is normal, not an error."
    exit 1
fi

if [ ! -s "$TMP" ]; then
    echo "Download log is empty — nothing has been downloaded since logging started."
    exit 0
fi

case "$1" in
    --raw)
        jq -r '.' "$TMP"
        exit 0
        ;;
    --humans)
        jq -r 'select(.bot == false) | "\(.at)  \(.file)  \(.ip)  \(.ua)"' "$TMP"
        exit 0
        ;;
esac

TOTAL="$(wc -l < "$TMP" | tr -d ' ')"
HUMANS="$(jq -s '[.[] | select(.bot == false)] | length' "$TMP")"
BOTS="$(jq -s '[.[] | select(.bot == true)] | length' "$TMP")"
UNIQUE_IP="$(jq -r '.ip' "$TMP" | sort -u | wc -l | tr -d ' ')"
HUMAN_IP="$(jq -r 'select(.bot == false) | .ip' "$TMP" | sort -u | wc -l | tr -d ' ')"
FIRST="$(head -1 "$TMP" | jq -r '.at')"
LAST="$(tail -1 "$TMP" | jq -r '.at')"

echo "getsocietypress.org downloads"
echo "  logging since: $FIRST"
echo "  most recent:   $LAST"
echo
echo "  total downloads:   $TOTAL"
echo "  looked human:      $HUMANS   (from $HUMAN_IP distinct addresses)"
echo "  looked automated:  $BOTS"
echo "  distinct addresses: $UNIQUE_IP"
echo
echo "By file:"
jq -r '.file' "$TMP" | sort | uniq -c | sort -rn | sed 's/^/  /'
echo
echo "Human downloads by day:"
jq -r 'select(.bot == false) | .at[0:10]' "$TMP" | sort | uniq -c | sed 's/^/  /'
echo
echo "Top referers (human only):"
jq -r 'select(.bot == false and .referer != "") | .referer' "$TMP" | sort | uniq -c | sort -rn | head -10 | sed 's/^/  /'
echo
echo "Note: the bot flag is a user-agent heuristic. Automation that presents a"
echo "browser user agent is counted as human, so treat the human number as an"
echo "upper bound rather than a precise count."
