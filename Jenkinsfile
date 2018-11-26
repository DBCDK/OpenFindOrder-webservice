#!groovy

def PRODUCT = 'openfindorder'

def DOCKER_HOST = 'tcp://dscrum-is:2375'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def MAIL_RECIPIENTS = 'lkh@dbc.dk, pjo@dbc.dk, jgn@dbc.dk, las@dbc.dk'
def WORKSPACE = "workspace/$PRODUCT"
// the image to use on different stages
def ofoImage

//node("d8-php7-builder") {
node("master") {
    ws(WORKSPACE) {
        withEnv(["DOCKER_HOST=${DOCKER_HOST}"]) {
            stage('SVN: checkout code') {
                checkout scm
                // get externals
                sh 'svn up'
            }

            stage("prepare website build (copy files)") {
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
                
                // make a www folder
                // make index.php symbolic link 
                dir('docker/webservice/www') {
                    sh """
	                    mkdir 2.5
	                    """
                    sh """
                      ln -s versions.php index.php
	                    """
                }
                
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
                  ofoAuthentication.php \
                  NEWS.html \
                  license.txt \
	                xml/ \
	                docker/webservice/www/2.5/
	                """
                
                // make index.php symbolic link 
                dir('docker/webservice/www/2.5') {
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
                docker.withRegistry('https://' + DOCKER_REPO, 'artifactory-api-key') {
                    ofoImage.push()
                }

                sh """
                   docker rmi ${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}
                    """
            }
        }
    }
}
