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
        //  if (${version}.equals('2.6')) {
            def SED_STRING = 'DIR=$APACHE_ROOT/2.6\n' +
                'INI=$DIR/openfindorder.ini\n' +
                'INSTALL=$INI"_INSTALL"\n' +
                'cp \$DIR/openfindorder.wsdl_INSTALL \$DIR/openfindorder.wsdl\n' +
                'if [ ! -f \$INI ] ; then\n' +
                '    cp \$INSTALL \$INI\n' +
                '    sed -i "s#@OPENAGENCY_AGENCY_LIST@#\$OPENAGENCY_AGENCY_LIST_PROD#g" \$INI\n' +
                '    sed -i "s#@ORS2_URL@#\$ORS2_URL_PROD#g" \$INI\n' +
                '    sed -i "s#@CACHE_SETTINGS@#\$CACHE_SETTINGS#g" \$INI\n' +
                '    sed -i "s#@MY_DOMAIN@#\$MY_DOMAIN#g" \$INI\n' +
                '    sed -i "s#@MY_DOMAIN_IP_LIST@#\$MY_DOMAIN_IP_LIST#g" \$INI\n' +
                '    sed -i "s#@AAA_FORS_RIGHTS@#\$AAA_FORS_RIGHTS#g" \$INI\n' +
                '    sed -i "s#@LOGFILE@#v\$LOGFILE#g" \$INI\n' +
                '    sed -i "s#@VERBOSE_LEVEL@#\$VERBOSE_LEVEL#g" \$INI\n' +
                'fi\n'
        // }
        sh """
            // sed -i "s#@RUN-OFO@#${SED_STRING}#g" foo.test
            cp foo.test www/foo.test
            """
    }
}

return this
