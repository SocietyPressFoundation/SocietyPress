#!/bin/bash
# scripts/deploy.local.example.sh
#
# Template for configuring a secondary deploy target alongside the public
# demo site. Copy this file to scripts/deploy.local.sh (which is gitignored)
# and fill in the values for your private site. When that file exists, every
# deploy.sh target will also push to the secondary location.
#
# Intended use: personal staging sites, private testbeds, or anywhere you
# want to mirror the canonical demo deploy without committing your private
# site details to the public repo.

# SSH host alias (see ~/.ssh/config) for the secondary server. Optional —
# if omitted, the secondary deploy reuses the primary HOST from deploy.sh.
# Set this when the secondary site lives on a different server than the demo.
SECONDARY_HOST="your-ssh-alias"

# Tilde-prefixed wp-content path on the SECONDARY_HOST server. Leave the
# base/label values empty to skip the secondary deploy entirely.
SECONDARY_BASE="~/domains/yourprivatesite.example/public_html/wp-content"
SECONDARY_LABEL="yourprivatesite.example"
