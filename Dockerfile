# Boshlang'ich image: PHP CLI
FROM php:8.2-cli

# PHP uchun MySQL extension o'rnatish
RUN docker-php-ext-install mysqli

# Ish papkasini yaratish
WORKDIR /app

# Local fayllarni konteynerga nusxalash
COPY . /app

# Composer o'rnatish (agar kerak bo'lsa)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# Agar composer.json bo'lsa, dependencylarni o'rnatish
RUN composer install

# Server portini ochish
EXPOSE 10000

# PHP built-in serverni ishga tushirish
CMD ["php", "-S", "0.0.0.0:10000"]
