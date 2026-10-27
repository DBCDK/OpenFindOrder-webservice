FROM docker.dbc.dk/dbc-apache-php8:latest
# FROM docker.dbc.dk/dbc-apache-php7
MAINTAINER D-scrum <d-scrum@dbc.dk>

# RUN apt-get update && apt-get -q -y install php-mbstring ca-certificates php-memcached

# RUN rm -r /var/www
ENV VERSION="2.6"
COPY --chown=sideejer:sideejer ./www/2.6 "${DBC_PHP_INSTALL_DIR}/${VERSION}"
COPY --chown=sideejer:sideejer ./www/next_2.6 "${DBC_PHP_INSTALL_DIR}/next_${VERSION}"
COPY --chown=sideejer:sideejer ./www/test_2.6 "${DBC_PHP_INSTALL_DIR}/test_${VERSION}"
WORKDIR ${DBC_PHP_INSTALL_DIR}/

RUN ls -la /var/www
RUN ls -la /var/www/html
RUN ls -la /var/www/html/app
RUN ls -la /var/www/html/app/2.6
RUN php -v

EXPOSE 8080

# COPY apache_security.conf /etc/apache2/conf-enabled/
# COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY --chown=sideejer:sideejer config.sh "${DBC_PHP_INSTALL_DIR}/${VERSION}"
USER ROOT
COPY config.sh /
USER www-data
