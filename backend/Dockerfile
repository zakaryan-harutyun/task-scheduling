FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libicu-dev \
 && docker-php-ext-install pdo pdo_mysql intl \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY composer.json composer.lock /app/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
 && rm composer-setup.php \
 && composer install --no-interaction --prefer-dist --no-scripts --no-dev \
 || true

COPY . /app
RUN composer install --no-interaction --prefer-dist && php artisan key:generate

EXPOSE 8000


