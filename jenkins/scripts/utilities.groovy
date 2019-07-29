#!/usr/bin/env groovy

def  copyDockerFiles(String version = '2.5') {
    // create folders
    dir('docker/webservice/www') {
        sh """
            mkdir ${version}
            """
    }

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

    // make index.php symbolic link
    dir("docker/webservice/www/${version}") {
        sh """
            ln -s server.php index.php
            """
    }
}

return this
