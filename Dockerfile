# =============================================================================
# PHP-FPM : base commune
# =============================================================================
FROM php:8.2-fpm AS php_base

WORKDIR /app

# Outils système : libfcgi-bin (cgi-fcgi) pour le healthcheck, git/unzip pour Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends libfcgi-bin git unzip \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP (+ Composer) via mlocati/php-extension-installer
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions @composer intl opcache pdo_mysql

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PATH="${PATH}:/app/vendor/bin"

COPY --link docker/php/conf.d/10-app.ini $PHP_INI_DIR/conf.d/
COPY --link docker/php/php-fpm.d/zz-app.conf /usr/local/etc/php-fpm.d/
COPY --link --chmod=755 docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link --chmod=755 docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

# =============================================================================
# PHP-FPM : développement (code monté en volume, Xdebug)
# =============================================================================
FROM php_base AS php_dev

ARG HOST_UID=1000
ARG HOST_GID=1000

ENV APP_ENV=dev \
    XDEBUG_MODE=off

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions xdebug \
    # www-data prend l'UID/GID de l'utilisateur hôte : pas de fichiers root dans le projet
    && groupmod -o -g "${HOST_GID}" www-data \
    && usermod -o -u "${HOST_UID}" -g www-data www-data \
    && mkdir -p "${COMPOSER_HOME}" \
    && chown -R www-data:www-data /app "${COMPOSER_HOME}"

COPY --link docker/php/conf.d/20-app.dev.ini $PHP_INI_DIR/conf.d/

USER www-data

# =============================================================================
# PHP-FPM : production (code et vendor embarqués dans l'image)
# =============================================================================
FROM php_base AS php_prod

ENV APP_ENV=prod \
    APP_DEBUG=0

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --link docker/php/conf.d/20-app.prod.ini $PHP_INI_DIR/conf.d/

# Dépendances d'abord, pour profiter du cache de build
COPY --link app/composer.* app/symfony.* ./
RUN composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

# Seul le projet Symfony (./app) est copié dans l'image
COPY --link app/ ./
RUN mkdir -p var/cache var/log \
    && composer dump-autoload --classmap-authoritative --no-dev \
    && composer dump-env prod \
    && composer run-script --no-dev post-install-cmd \
    && chmod +x bin/console \
    && chown -R www-data:www-data var

USER www-data

# =============================================================================
# Nginx
# =============================================================================
# En dev, l'image nginx:1.28 est utilisée telle quelle (config montée en volume)
FROM nginx:1.28 AS nginx_prod

COPY --link docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Seuls les fichiers publics (index.php, assets, bundles) sont nécessaires côté nginx
COPY --from=php_prod --link /app/public /app/public
