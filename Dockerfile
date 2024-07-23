FROM php:8.2-apache
RUN a2enmod rewrite

# yaml not available using docker-php-ext-configure
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions yaml

# hostname for images
ARG IMG_HOST
ENV IMG_HOST $IMG_HOST

COPY apache/imghost.conf /etc/apache2/sites-enabled/
COPY apache/custom.conf /etc/apache2/conf-enabled/xxx-custom.conf

COPY src /var/www
