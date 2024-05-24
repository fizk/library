FROM php:8.3.7-cli-bookworm

RUN apt-get update; \
    apt-get upgrade; \
    apt-get install git zip -y;

RUN pecl install xdebug-3.3.1; \
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

WORKDIR /var/app

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.6

COPY ./composer.json /var/app/composer.json
COPY ./composer.lock /var/app/composer.lock

RUN composer i

COPY ./src /var/app/src
COPY ./test /var/app/test

CMD [ "bash", "-c", "composer test && composer sniff"]