FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    unzip \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# CMD ["tail", "-f", "/dev/null"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
