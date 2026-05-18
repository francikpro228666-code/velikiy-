FROM php:8.3-apache

# Установка системных зависимостей и расширений PHP
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mysqli zip gd intl \
    && a2enmod rewrite

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы с зависимостями
COPY composer.json composer.lock* ./

# Установка зависимостей без проверки платформы
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader --ignore-platform-reqs; fi

# Копируем весь код
COPY . /var/www/html/

# Перенастройка Apache на папку public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Включение отображения ошибок PHP
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-error.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-error.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-error.ini

# Права на папки
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html/public

EXPOSE 80