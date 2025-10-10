# Use the Ubuntu 24.04 image as the base image
#FROM ubuntu:24.04
FROM php:8.2.28-apache-bookworm

# Disable popups asking for geography area
ARG DEBIAN_FRONTEND=noninteractive

# Set working directory
WORKDIR /home

# Added for Xdebug
# install the Xdebug extension and enable it
RUN apt-get update \
    && apt-get install -y autoconf build-essential \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# copy in your custom xdebug settings
COPY xdebug.ini /usr/local/etc/php/conf.d/20-xdebug.ini

# Install necessary packages
# RUN apt-get update && apt-get install -y \
#     bash \
#     apache2 \
#     php \
#     libapache2-mod-php \
#     ufw \
#     git \
#     openssl \
#     vim

# Enable required Apache modules
#RUN a2enmod php8.2

# Allow Apache through the firewall
#RUN ufw allow 'Apache'

# Install PHP extensions
#RUN apt-get update && apt-get install -y \
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd mbstring zip curl exif dom \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Clone the GitHub repository
# RUN cd /var/www/html && git clone -b summer-2023 --single-branch https://github.com/BU-Spark/se-Symbiota-portal.git

# Copy symbiota config files to the apache2 directory
COPY 002-symbiota.conf /etc/apache2/sites-available

# Enable symbiota site
RUN a2dissite 000-default.conf
RUN a2ensite 002-symbiota.conf

# Expose port 80 for web traffic
EXPOSE 80

# Set environment variables
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_PID_FILE /var/run/apache2/apache2.pid

# Make sure the required directories exist, if they didn't already exist
RUN mkdir -p $APACHE_RUN_DIR $APACHE_LOCK_DIR $APACHE_LOG_DIR

# Start bash
# CMD ["/bin/bash"]
# Start apache
ENTRYPOINT ["/usr/sbin/apache2", "-DFOREGROUND"]