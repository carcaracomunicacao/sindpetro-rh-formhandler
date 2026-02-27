# Base image for PHP with Apache
FROM php:8.3-apache

# Install PDO and MySQLi extensions for database connections
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable mod_rewrite for handling .htaccess files
RUN a2enmod rewrite