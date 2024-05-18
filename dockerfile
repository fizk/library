FROM php:8.3.7-cli-bookworm

RUN apt-get update; \
    apt-get upgrade; \
    apt-get install git zip -y;

RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer --version=2.6.6

WORKDIR /var/app

COPY ./src /var/app/src
COPY ./test /var/app/test