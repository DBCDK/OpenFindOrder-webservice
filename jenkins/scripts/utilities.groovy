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
        sh """
            sed -i "s#@RUN-OFO@#testhest:${version}#g" run-ofo.sh
            """
    }
}

return this
