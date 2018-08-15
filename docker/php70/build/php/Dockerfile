FROM php:7.0-cli

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip

RUN curl --silent --show-error https://getcomposer.org/installer | php

RUN docker-php-ext-install pdo_mysql


# Endless command to keep container running
CMD tail -f /dev/null