FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy source code
COPY . /var/www/html/
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Set permissions
RUN chown -R www-data:www-data /var/www/html
