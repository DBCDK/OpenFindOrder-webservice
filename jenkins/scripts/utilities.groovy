#!/usr/bin/env groovy

def  copyDockerFiles(String version = '2.5') {
    // Create folders.
    dir('docker/webservice/www') {
        sh """
            mkdir ${version}
            """
    }

    // Copy openFindOrder code to folders.
    dir('') {
        sh """
            cp -r \
            openfindorder.wsdl_INSTALL \
            openfindorder.xsd \
            openfindorder.ini_INSTALL \
            OLS_class_lib/ \
            server.php \
            howRU.php \
            xsdparse.php \
            orsAgency.php \
            orsClass.php \
            openFindOrder.php \
            ofoAaa.php \
            ofoAuthentication.php \
            NEWS.html \
            license.txt \
            xml/ \
            docker/webservice/www/${version}/
            """
    }

    // Make index.php symbolic link.
    dir("docker/webservice/www/${version}") {
        sh """
            ln -s server.php index.php
            """
    }

    // Make index.php symbolic link.
    dir("docker/webservice") {
        if (${version} == '2.6') {
            def SED_STRING = '
                DIR=$APACHE_ROOT/' + ${version} + '
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
                fi'
        }
        sh """
            print "Parameter: SED_STRING = ${SED_STRING}"
            sed -i "s#@RUN-OFO@#${SED_STRING}#g" run-ofo.sh
            """
    }
}

return this
