FROM docker.dbc.dk/dbc-apache-php8:latest
# FROM docker.dbc.dk/dbc-apache-php7
MAINTAINER D-scrum <d-scrum@dbc.dk>

# RUN apt-get update && apt-get -q -y install php-mbstring ca-certificates php-memcached

# RUN rm -r /var/www
ENV VERSION="2.6"
COPY --chown=sideejer:sideejer ./ "${DBC_PHP_INSTALL_DIR}/${VERSION}"
# ADD www /var/www/html
# RUN chown -R www-data:www-data /var/www/html
RUN ls -la /var/www
RUN ls -la /var/www/html
RUN ls -la /var/www/html/app
RUN ls -la /var/www/html/app/2.6
RUN php -v

EXPOSE 8080

COPY apache_security.conf /etc/apache2/conf-enabled/
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY config.sh /
