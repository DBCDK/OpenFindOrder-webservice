#!/bin/bash

# replace variables in openuserinfo.ini with environment vars

versions=(2.5 next_2.5 test_2.5 2.6 next_2.6 test_2.6)
for u in "${versions[@]}"
do
    echo "replace variables in version $u"
    DIR=$APACHE_ROOT/$u
    INI=$DIR/openfindorder.ini
    INSTALL=$INI"_INSTALL"
    cp $DIR/openfindorder.wsdl_INSTALL $DIR/openfindorder.wsdl
    if [ ! -f $INI ] ; then
        cp $INSTALL $INI
        sed -i "s#@OPENAGENCY_AGENCY_LIST@#$OPENAGENCY_AGENCY_LIST_PROD#g" $INI
        sed -i "s#@ORS2_URL@#$ORS2_URL_PROD#g" $INI
        sed -i "s#@CACHE_SETTINGS@#$CACHE_SETTINGS#g" $INI
        sed -i "s#@MY_DOMAIN@#$MY_DOMAIN#g" $INI
        sed -i "s#@MY_DOMAIN_IP_LIST@#$MY_DOMAIN_IP_LIST#g" $INI
        sed -i "s#@AAA_FORS_RIGHTS@#$AAA_FORS_RIGHTS#g" $INI
        sed -i "s#@LOGFILE@#$LOGFILE#g" $INI
        sed -i "s#@VERBOSE_LEVEL@#$VERBOSE_LEVEL#g" $INI
    fi
done

if [ "$1" == '' ]; then
	service memcached start
	/usr/sbin/apache2ctl -D FOREGROUND
else
	exec "$@"
fi
