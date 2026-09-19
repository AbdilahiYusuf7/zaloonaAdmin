#!/bin/sh
set -e

# Render (and most container hosts) inject PORT and expect the app to listen
# on it; Apache's images default to 80, so rewrite the listen/vhost config.
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
