FROM composer:2 AS vendor
WORKDIR /app
COPY monitor-metricas/composer.json ./composer.json
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

FROM php:8.2-cli
WORKDIR /var/www/html

RUN apt-get update \
  && apt-get install -y --no-install-recommends libpq-dev libcurl4-openssl-dev unzip \
  && docker-php-ext-install pdo_pgsql curl \
  && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app/vendor ./vendor
COPY monitor-metricas/ .

ENV APP_ENV=production
ENV PORT=10000
EXPOSE 10000

CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-10000} -t public public/index.php"]
