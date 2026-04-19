#!/bin/bash
# scripts/build.local.example.sh
#
# Template for configuring environment-specific patterns that should never
# appear in a shippable SocietyPress bundle. Copy this file to
# scripts/build.local.sh (which is gitignored) and list any strings that
# are specific to your development environment — personal email addresses,
# private domain names, server usernames, etc. The Softaculous build will
# fail loudly if it finds any of these in the assembled plugin file.
#
# Format: LEAK_PATTERNS is a grep-style alternation string. Escape dots in
# domains so they don't match arbitrary characters.

LEAK_PATTERNS="yourname@\|yourhost.example.com\|your-private-domain\.com"
