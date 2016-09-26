FROM php:5.4-apache
RUN docker-php-ext-install mbstring
COPY . /var/www/
COPY ./demo /var/www/html/
