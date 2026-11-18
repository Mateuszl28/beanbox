#!/usr/bin/env bash
# wp-cli dla strony Local „BeanBox” bez otwierania „Site shell” w Local.
# Użycie:  bash narzedzia/wp.sh plugin list
# PHP i wp-cli pochodzą z instalacji Local; baza MySQL strony nasłuchuje na porcie z Local (sites.json).
export MSYS_NO_PATHCONV=1  # Git Bash nie może zamieniać argumentów typu /%postname%/ na ścieżki
PHPDIR="$APPDATA/Local/lightning-services/php-8.2.29+0/bin/win64"
WPCLI="$LOCALAPPDATA/Programs/local/resources/extraResources/bin/wp-cli/wp-cli.phar"
SITE="$USERPROFILE/Local Sites/beanbox/app/public"
PORT="${BEANBOX_DB_PORT:-10018}"
exec "$PHPDIR/php.exe" \
  -d extension_dir="$PHPDIR/ext" -d extension=mysqli -d extension=mbstring -d extension=openssl \
  -d extension=curl -d extension=gd -d extension=intl -d extension=fileinfo -d extension=exif \
  -d extension=zip -d extension=sodium \
  -d mysqli.default_port="$PORT" -d memory_limit=512M \
  "$WPCLI" --path="$SITE" "$@"
