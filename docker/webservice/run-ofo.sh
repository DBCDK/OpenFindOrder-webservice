#!/bin/bash

DIR=$APACHE_ROOT/2.5
INI=$DIR/openfindorder.ini
INSTALL=$INI"_INSTALL"

cp $DIR/openfindorder.wsdl_INSTALL $DIR/openfindorder.wsdl

#echo Your container args are: "$@"

# do a little preprocessing - we need to be able to run
# on either culr-staging og culr-drift to make ave happy
# pass the variable in either run command or the COMMAND part
# of the configuration on marathon
#if [ "$1" == 'staging' ]; then
#    #simply delete the prod setting if staging is required
#    sed -i '/@CULR_DRIFT@/d' $INSTALL
#elif [  "$1" == '' ]; then
#    # we run on prod settings default - delete the staging part
#    sed -i '/@CULR_STAGING@/d' $INSTALL
#else
#    exec "$@"
#fi

# replace variables in openuserinfo.ini with environment vars
if [ ! -f $INI ] ; then
    cp $INSTALL $INI
    sed -i "s#@OPENAGENCY_AGENCY_LIST@#$OPENAGENCY_AGENCY_LIST_PROD#g" $INI
    sed -i "s#@ORS2_URL@#$ORS2_URL_PROD#g" $INI
    sed -i "s#@CACHE_SETTINGS@#$CACHE_SETTINGS#g" $INI
    sed -i "s#@MY_DOMAIN@#$MY_DOMAIN#g" $INI
    sed -i "s#@MY_DOMAIN_IP_LIST@#$MY_DOMAIN_IP_LIST#g" $INI
    sed -i "s#@LOGFILE@#$LOGFILE#g" $INI
    sed -i "s#@VERBOSE_LEVEL@#$VERBOSE_LEVEL#g" $INI
    # while IFS='=' read -r name value ; do
    #   echo "$name $value"
    #   sed -i "s/@${name}@/$(echo $value | sed -e 's/\//\\\//g; s/&/\\\&/g')/g" $INI
    # done < <(env)
fi

DIR=$APACHE_ROOT/next_2.5
INI=$DIR/openfindorder.ini
INSTALL=$INI"_INSTALL"
cp $DIR/openfindorder.wsdl_INSTALL $DIR/openfindorder.wsdl
if [ ! -f $INI ] ; then
    cp $INSTALL $INI
    sed -i "s#@OPENAGENCY_AGENCY_LIST@#$OPENAGENCY_AGENCY_LIST_STAGING#g" $INI
    sed -i "s#@ORS2_URL@#$ORS2_URL_STAGING#g" $INI
    sed -i "s#@CACHE_SETTINGS@#$CACHE_SETTINGS#g" $INI
    sed -i "s#@MY_DOMAIN@#$MY_DOMAIN#g" $INI
    sed -i "s#@MY_DOMAIN_IP_LIST@#$MY_DOMAIN_IP_LIST#g" $INI
    sed -i "s#@LOGFILE@#$LOGFILE#g" $INI
    sed -i "s#@VERBOSE_LEVEL@#$VERBOSE_LEVEL#g" $INI
    # while IFS='=' read -r name value ; do
    #   echo "$name $value"
    #   sed -i "s/@${name}@/$(echo $value | sed -e 's/\//\\\//g; s/&/\\\&/g')/g" $INI
    # done < <(env)
fi

DIR=$APACHE_ROOT/test_2.5
INI=$DIR/openfindorder.ini
INSTALL=$INI"_INSTALL"
cp $DIR/openfindorder.wsdl_INSTALL $DIR/openfindorder.wsdl
if [ ! -f $INI ] ; then
    cp $INSTALL $INI
    sed -i "s#@OPENAGENCY_AGENCY_LIST@#$OPENAGENCY_AGENCY_LIST_PROD#g" $INI
    sed -i "s#@ORS2_URL@#$ORS2_URL_STAGING#g" $INI
    sed -i "s#@CACHE_SETTINGS@#$CACHE_SETTINGS#g" $INI
    sed -i "s#@MY_DOMAIN@#$MY_DOMAIN#g" $INI
    sed -i "s#@MY_DOMAIN_IP_LIST@#$MY_DOMAIN_IP_LIST#g" $INI
    sed -i "s#@LOGFILE@#$LOGFILE#g" $INI
    sed -i "s#@VERBOSE_LEVEL@#$VERBOSE_LEVEL#g" $INI
    # while IFS='=' read -r name value ; do
    #   echo "$name $value"
    #   sed -i "s/@${name}@/$(echo $value | sed -e 's/\//\\\//g; s/&/\\\&/g')/g" $INI
    # done < <(env)
fi

if [ "$1" == '' ]; then
	service memcached start
	/usr/sbin/apache2ctl -D FOREGROUND
else
	exec "$@"
fi
