#!/bin/sh
set -e

# Permet de passer des options à php-fpm directement (ex: docker run ... -F)
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    if [ "$APP_ENV" != 'prod' ] && [ -f composer.json ] && [ -z "$(ls -A vendor 2>/dev/null)" ]; then
        composer install --prefer-dist --no-progress --no-interaction
    fi
fi

exec docker-php-entrypoint "$@"
