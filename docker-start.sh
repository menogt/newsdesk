#!/bin/sh
set -eu
PORT="${PORT:-10000}"
case "$PORT" in
  *[!0-9]*|'') echo 'Invalid PORT; must be numeric' >&2; exit 1 ;;
esac
sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/\*:80>/\*:${PORT}>/" /etc/apache2/sites-available/000-default.conf
exec apache2-foreground
