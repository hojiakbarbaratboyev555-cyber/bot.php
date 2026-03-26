# PHP 8.2 CLI + Apache
FROM php:8.2-apache

# Kerakli extensionlar
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y curl unzip

# Apache rewrite qo'llab-quvvatlash
RUN a2enmod rewrite

# Ishchi papka
WORKDIR /var/www/html

# Loyihani konteynerga nusxalash
COPY . /var/www/html

# Fayl huquqlarini to‘g‘irlash
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Apache port
EXPOSE 80

# Apache ishga tushadi
CMD ["apache2-foreground"]
