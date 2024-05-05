FROM php:8.3-fpm-alpine

# Set timezone
ENV TZ = Europe/Paris
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

# PHP Extensions
RUN docker-php-ext-install pdo pdo_mysql opcache
COPY php.conf /usr/local/etc/php/conf.d/app.ini

# Create users, directories and update permissions
RUN addgroup -g 1000 --system app \
    && adduser -G www-data --system -D -s /bin/sh -u 1000 app

# Configure pool
RUN sed -i \
    -e 's/^user = www-data*/;user = app/' \
    -e 's/^group = www-data*/;group = app/' \
    -e 's/^;listen.owner = www-data*/listen.owner = app/' \
    -e 's/^;listen.group = www-data*/listen.group = app/' \
    /usr/local/etc/php-fpm.d/www.conf

# Change owner and group
USER app:app

# Expose port 9000
EXPOSE 9000
