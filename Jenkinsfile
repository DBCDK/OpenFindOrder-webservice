#!groovy

def PRODUCT = 'openfindorder'

def DOCKER_HOST = 'tcp://dscrum-is:2375'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def MAIL_RECIPIENTS = 'lkh@dbc.dk, pjo@dbc.dk, jgn@dbc.dk'
def WORKSPACE = "workspace/$PRODUCT"
// the image to use on different stages
def ofoImage

//node("d8-php7-builder") {
node("master") {
    ws(WORKSPACE) {
        withEnv(["DOCKER_HOST=${DOCKER_HOST}"]) {
            stage('GIT: checkout code') {
                checkout scm
                // get externals
                sh """
                  svn co 'https://svn.dbc.dk/repos/php/OpenLibrary/class_lib/trunk/' OLS_class_lib
                  """
            }

            stage("prepare website build (copy OpenVersionWrapper)") {
                // Prepare the build
                // Check out OpenVersionWrapper into a www folder
                dir('docker/webservice') {
                    sh """
	                    rm -rf www
	                    """
                    sh """
	                    svn co https://svn.dbc.dk/repos/php/OpenLibrary/OpenVersionWrapper/trunk/ www
	                    """
                    sh """
                      cp OpenVersionWrapper/* www/
	                    """
                      }

            }

            stage("prepare website build (version 2.5 & 2.6)") {
                // cd www folder
                // make index.php symbolic link
                dir('docker/webservice/www') {
                    sh """
	                    mkdir 2.5
	                    """
                    sh """
	                    mkdir next_2.5
	                    """
                    sh """
	                    mkdir test_2.5
	                    """
                    sh """
	                    mkdir 2.6
	                    """
                    sh """
	                    mkdir next_2.6
	                    """
                    sh """
	                    mkdir test_2.6
	                    """
                    sh """
                      ln -s versions.php index.php
	                    """
                }

            }

            stage('GIT: checkout version 2.5') {
                // get externals
                sh """
                  git checkout release/2.5
                  """
            }

            stage("copy files") {
                // copy files needed for docker image
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
	                docker/webservice/www/2.5/
	                """

                // copy files needed for docker image
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
	                docker/webservice/www/next_2.5/
	                """

                // copy files needed for docker image
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
	                docker/webservice/www/test_2.5/
	                """

                // make index.php symbolic link
                dir('docker/webservice/www/2.5') {
                    sh """
                      ln -s server.php index.php
	                    """
                }

                // make index.php symbolic link
                dir('docker/webservice/www/next_2.5') {
                    sh """
                      ln -s server.php index.php
	                    """
                }

                // make index.php symbolic link
                dir('docker/webservice/www/test_2.5') {
                    sh """
                      ln -s server.php index.php
	                    """
                }
            }

            stage("Docker: build image") {
                dir("docker/webservice") {
                    // build the image
                    //ouiImage = docker.build("docker-dscrum.dbc.dk/oui:latest")
                    ofoImage = docker.build("${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}")
                }
            }

            stage('Docker: push and cleanup') {
                // drop artifactory update while fooling around
                // docker.withRegistry('https://' + DOCKER_REPO, 'artifactory-api-key') {
                //     ofoImage.push()
                // }

                sh """
                   docker rmi ${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}
                    """
            }
        }
    }
}
