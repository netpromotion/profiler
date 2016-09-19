FROM php:5.4-apache
COPY . /var/www/
COPY ./demo /var/www/html/
