FROM docker.dbc.dk/dbc-apache-php8:latest
# FROM docker.dbc.dk/dbc-apache-php7
MAINTAINER D-scrum <d-scrum@dbc.dk>

# RUN apt-get update && apt-get -q -y install php-mbstring ca-certificates php-memcached

# RUN rm -r /var/www
ADD www /var/www/html
# RUN chown -R www-data:www-data /var/www/html
RUN ls -la /var/www
RUN ls -la /var/www/html
RUN ls -la /var/www/html/2.6
RUN ls -la /var/www/html/app
RUN php -v

EXPOSE 80

COPY apache_security.conf /etc/apache2/conf-enabled/
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY config.sh /
