#!/bin/bash
set -e

# ── Entrypoint for CastoPOST ─────────────────────────────────
# Generates config.php from environment variables if not
# already present in the /data volume, then starts services.

CONFIG="/data/config.php"

# ── Generate config.php from env vars if it hasn't been customized ──
if grep -q "your_secure_password" "$CONFIG" 2>/dev/null || [ ! -f "$CONFIG" ]; then
    echo "[castopost] Generating config.php from environment variables..."

    php -r '
        $cfg = "<?php\n"
            . "define(\x27APP_PASSWORD\x27, " . var_export(getenv("APP_PASSWORD") ?: "change_me_now", true) . ");\n"
            . "define(\x27CASTOPOD_URL\x27, " . var_export(getenv("CASTOPOD_URL") ?: "https://your-castopod.com", true) . ");\n"
            . "define(\x27CASTOPOD_API_USER\x27, " . var_export(getenv("CASTOPOD_API_USER") ?: "api_user", true) . ");\n"
            . "define(\x27CASTOPOD_API_PASSWORD\x27, " . var_export(getenv("CASTOPOD_API_PASSWORD") ?: "api_password", true) . ");\n"
            . "define(\x27CASTOPOD_PODCAST_HANDLE\x27, " . var_export(getenv("CASTOPOD_PODCAST_HANDLE") ?: "my-podcast", true) . ");\n"
            . "define(\x27CASTOPOD_USER_ID\x27, " . (int)(getenv("CASTOPOD_USER_ID") ?: 1) . ");\n"
            . "define(\x27PODCASTS_FILE\x27, __DIR__ . \x27/podcasts.json\x27);\n"
            . "define(\x27DRAFTS_FILE\x27, __DIR__ . \x27/local_drafts.json\x27);\n"
            . "define(\x27TEMPLATES_FILE\x27, __DIR__ . \x27/templates.json\x27);\n"
            . "define(\x27UPLOAD_TMP_DIR\x27, __DIR__ . \x27/tmp\x27);\n"
            . "define(\x27MAX_AUDIO_SIZE\x27, " . (int)(getenv("MAX_AUDIO_SIZE") ?: 536870912) . ");\n"
            . "date_default_timezone_set(" . var_export(getenv("APP_TIMEZONE") ?: "UTC", true) . ");\n"
            . "define(\x27CASTOPOD_DEBUG\x27, " . ((getenv("CASTOPOD_DEBUG") === "true") ? "true" : "false") . ");\n";
        file_put_contents($argv[1], $cfg);
    ' "$CONFIG"

    echo "[castopost] config.php written."
fi

# ── Set PHP timezone ─────────────────────────────────────────
if [ -n "$APP_TIMEZONE" ]; then
    echo "date.timezone = ${APP_TIMEZONE}" >> /usr/local/etc/php/conf.d/castopost.ini
fi

# ── Ensure data files exist ──────────────────────────────────
[ -f /data/podcasts.json ]     || echo '[]'  > /data/podcasts.json
[ -f /data/local_drafts.json ] || echo '{}'  > /data/local_drafts.json
[ -f /data/templates.json ]    || echo '[]'  > /data/templates.json
mkdir -p /data/tmp

chown -R castopost:castopost /data
chmod 700 /data/tmp
chmod 664 /data/podcasts.json /data/local_drafts.json /data/templates.json

# ── Create Nginx temp dirs ───────────────────────────────────
mkdir -p /tmp/nginx_client_body /tmp/nginx_proxy /tmp/nginx_fastcgi \
         /tmp/nginx_uwsgi /tmp/nginx_scgi
chown -R castopost:castopost /tmp/nginx_*

# ── Start PHP-FPM ────────────────────────────────────────────
echo "[castopost] Starting PHP-FPM..."
php-fpm --daemonize --fpm-config /usr/local/etc/php-fpm.conf

# ── Start Nginx ──────────────────────────────────────────────
echo "[castopost] Starting Nginx on port 8080..."
exec nginx -g "daemon off;"
