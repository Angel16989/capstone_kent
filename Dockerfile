# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache document root
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy application files
COPY . /var/www/html/

# Install PHP dependencies (if composer.json exists)
# RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Install Node.js dependencies (if package.json exists)
# RUN if [ -f package.json ]; then npm install && npm run build; fi

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]