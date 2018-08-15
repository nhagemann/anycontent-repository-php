FROM php:5.6-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip \
    && a2enmod rewrite

RUN cd /; curl --silent --show-error https://getcomposer.org/installer | php

RUN docker-php-ext-install pdo_mysql
