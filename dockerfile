FROM php:8.4.8-cli-bookworm

ARG XDEBUG=3.4.3
ARG COMPOSER_VERSION=2.8.6

RUN apt-get update; \
    apt-get upgrade; \
    apt-get install git zip -y;

RUN pecl install xdebug-$XDEBUG; \
    docker-php-ext-enable xdebug; \
    echo "error_reporting = E_ALL\n\
display_startup_errors = On\n\
display_errors = On\n\
xdebug.mode = debug\n\
xdebug.start_with_request=yes\n\
xdebug.client_host=host.docker.internal\n\
xdebug.client_port=9003\n\
xdebug.idekey=myKey\n\
xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

RUN apt-get -y update; \
    apt-get install -y libicu-dev; \
    docker-php-ext-configure intl; \
    docker-php-ext-install intl

WORKDIR /var/app

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION

COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

RUN composer install --no-interaction --no-cache; \
    composer dump-autoload;

COPY ./src /var/app/src
COPY ./test /var/app/test

CMD [ "bash", "-c", "composer test && composer sniff"]