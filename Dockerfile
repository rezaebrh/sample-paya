# مرحله ۱: نصب composer و dependencyها
FROM composer:2.7 AS vendor

WORKDIR /app

# فقط فایل‌های composer رو کپی می‌کنیم تا کش مؤثر بشه
COPY composer.json composer.lock ./

# نصب dependencyها بدون dev
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist

# مرحله ۲: PHP-FPM برای اجرای Laravel
FROM php:8.2-fpm AS app

# نصب افزونه‌های لازم PHP برای Laravel
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# تنظیمات کار
WORKDIR /var/www/html

# کپی کد پروژه
COPY . .

# کپی vendor از مرحله قبل (برای سریع‌تر شدن)
COPY --from=vendor /app/vendor ./vendor

# تنظیم مجوزها
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# باز کردن پورت
EXPOSE 9000

# اجرای php-fpm
CMD ["php-fpm"]

