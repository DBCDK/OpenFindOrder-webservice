FROM docker.dbc.dk/dbc-apache-php7

RUN apt-get update && apt-get -q -y install postgresql-client php-pgsql php-mbstring ca-certificates

RUN rm -r /var/www
ADD www /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

ENV CACHE_SETTINGS="cache_host = localhost\ncache_port = 11211\ncache_expire = 600" \
    #SECURITY_IP_LIST="172.16.0.0-172.31.255.255;193.111.162.0-193.111.162.255"\
    #MY_DOMAIN=".dbc.dk" \
    MY_DOMAIN_IP_LIST="127.0.0.1;172.16.0.0-172.31.255.255;193.111.162.0-193.111.162.255" \
    LOGFILE="php://stdout" \
    #SERVICE_LOCATION="" \
    VERBOSE_LEVEL="WARNING+ERROR+FATAL+STAT+TIMER+TRACE"

COPY apache_security.conf /etc/apache2/conf-enabled/
COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY run-ofo.sh /
RUN chmod 755 /run-ofo.sh

ENTRYPOINT ["/run-ofo.sh"]



