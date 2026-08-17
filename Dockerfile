FROM php:8.3-apache

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Gerekli PHP eklentileri
RUN apt-get update && apt-get install -y libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql xml

RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_file_uploads = 20" >> /usr/local/etc/php/conf.d/uploads.ini

RUN docker-php-ext-install mcrypt pdo_mysql

# Install system dependencies
RUN apt-get update && apt-get install -y libmcrypt-dev mysql-client

# Install PHP extensions

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user

RUN a2enmod rewrite


RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Set the user
USER $user

# Copy your files
COPY . /var/www/html/

WORKDIR /var/www/html
