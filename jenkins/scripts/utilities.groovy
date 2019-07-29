#!/usr/bin/env groovy

def  hello(String baz = 'foo') {
  sh """
    echo ${baz}
    """
}

def  copyDockerFiles(String version = '2.5') {
    // ws(WORKSPACE) {
        // create folders
        dir('docker/webservice/www') {
            sh """
                pwd
                mkdir ${version}
                ls -al
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
    // }
}

return this
