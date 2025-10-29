#!/usr/bin/env bash
# Upload WooCommerce markup plugin via lftp (FTP/FTPS)
# Usage:
#   # Option 1: Use .env file (recommended)
#   bash upload_plugin_lftp.sh
#
#   # Option 2: Export environment variables
#   export FTP_PASS='your_password'
#   bash upload_plugin_lftp.sh

set -euo pipefail

# Load credentials from .env file if it exists
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
if [[ -f "$SCRIPT_DIR/.env" ]]; then
  echo "[INFO] Loading credentials from .env file..."
  # shellcheck disable=SC1090
  set -a
  source "$SCRIPT_DIR/.env"
  set +a
fi

# --------------------------- CONFIG ---------------------------------
HOST=${FTP_HOST:-"147.79.122.118"}            # FTP host or IP
USER=${FTP_USER:-"u659513315.thrwdist"}       # FTP username
REMOTE_PLUGINS_PATH=${REMOTE_PLUGINS_PATH:-"wp-content/plugins"}
REMOTE_PLUGIN_SLUG=${REMOTE_PLUGIN_SLUG:-"category-markup"}
LOCAL_PLUGIN_FILE=${LOCAL_PLUGIN_FILE:-"category-markup/category-markup.php"}
REMOTE_PLUGIN_FILE=${REMOTE_PLUGIN_FILE:-"category-markup.php"}
FORCE_TLS=${FORCE_TLS:-true}                   # Require FTPS (explicit TLS)
SKIP_CERT_CHECK=${SKIP_CERT_CHECK:-auto}       # true|false|auto (auto skips when HOST is an IP)
# --------------------------------------------------------------------

FTP_PASS=${FTP_PASS:-}
if [[ -z "$FTP_PASS" ]]; then
  echo "[ERROR] FTP_PASS not found. Either:" >&2
  echo "  1. Create a .env file with FTP_PASS=your_password" >&2
  echo "  2. Export FTP_PASS environment variable" >&2
  exit 1
fi

if [[ ! -f "$LOCAL_PLUGIN_FILE" ]]; then
  echo "[ERROR] Local plugin file '$LOCAL_PLUGIN_FILE' not found in $(pwd)." >&2
  exit 1
fi

LFTP_CMD="set cmd:fail-exit true; set net:max-retries 2; set net:timeout 20;"
if [[ "$FORCE_TLS" == true ]]; then
  LFTP_CMD+=" set ftp:ssl-force true; set ftp:ssl-protect-data true; set ftp:passive-mode true;"

  should_skip_cert=false
  if [[ "$SKIP_CERT_CHECK" == true ]]; then
    should_skip_cert=true
  elif [[ "$SKIP_CERT_CHECK" == auto && "$HOST" =~ ^([0-9]{1,3}\.){3}[0-9]{1,3}$ ]]; then
    echo "[WARN] HOST '$HOST' looks like a raw IP; disabling TLS hostname verification to avoid certificate mismatch." >&2
    should_skip_cert=true
  fi

  if [[ "$should_skip_cert" == true ]]; then
    LFTP_CMD+=" set ssl:check-hostname false; set ssl:verify-certificate no;"
  fi
fi

TMP_SCRIPT=$(mktemp)
trap 'rm -f "$TMP_SCRIPT"' EXIT

cat > "$TMP_SCRIPT" <<EOF
$LFTP_CMD
open -u "$USER","$FTP_PASS" "$HOST"
set cmd:fail-exit false
mkdir -p $REMOTE_PLUGINS_PATH
mkdir -p $REMOTE_PLUGINS_PATH/$REMOTE_PLUGIN_SLUG
set cmd:fail-exit true
cd $REMOTE_PLUGINS_PATH/$REMOTE_PLUGIN_SLUG
put -O . "$LOCAL_PLUGIN_FILE" -o $REMOTE_PLUGIN_FILE
bye
EOF

echo "[INFO] Uploading plugin file as $REMOTE_PLUGIN_FILE to /$REMOTE_PLUGINS_PATH/$REMOTE_PLUGIN_SLUG ..."
if lftp -f "$TMP_SCRIPT"; then
  echo "[SUCCESS] Upload complete."
  echo "Next steps:"
  echo "  1. Log into WP Admin -> Plugins."
  echo "  2. Locate 'Category Markup' and activate (or update)."
  echo "  3. (Optional) Edit Products > Categories > 'brabus' to override markup percent."
else
  echo "[ERROR] Upload failed." >&2
  exit 1
fi
