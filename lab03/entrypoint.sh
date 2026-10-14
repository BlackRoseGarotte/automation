#!/bin/sh
set -eu
if [ -n "${TZ:-}" ] && [ -f "/usr/share/zoneinfo/$TZ" ]; then
    ln -snf "/usr/share/zoneinfo/$TZ" /etc/localtime
fi
printenv | grep -v '^PWD=' > /etc/environment || true
touch /var/log/cron.log
chmod 666 /var/log/cron.log
echo '=== Starting cron daemon ==='
tail -n 0 -F /var/log/cron.log &
exec cron -f
