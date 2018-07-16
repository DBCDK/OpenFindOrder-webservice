#!/bin/bash

DIR=$APACHE_ROOT
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

    while IFS='=' read -r name value ; do
      echo "$name $value"
      sed -i "s/@${name}@/$(echo $value | sed -e 's/\//\\\//g; s/&/\\\&/g')/g" $INI
    done < <(env)

fi

service memcached start
/usr/sbin/apache2ctl -D FOREGROUND
