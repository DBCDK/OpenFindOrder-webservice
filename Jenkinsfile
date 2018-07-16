#!groovy

def PRODUCT = 'openfindorder'

def DOCKER_HOST = 'tcp://dscrum-is:2375'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def MAIL_RECIPIENTS = 'lkh@dbc.dk, pjo@dbc.dk, jgn@dbc.dk, las@dbc.dk'
def WORKSPACE = "workspace/$PRODUCT"
// the image to use on different stages
def ouiImage

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
                // make a www folder
                dir('docker/webservice') {
                    // prepare the build
                    sh """
	                    rm -rf www
	                    """
                    sh """
	                    mkdir www
	                    """
                }
                // copy files needed for docker image
                sh """
	                cp -r \
	                openfindorder.wsdl \
	                openuserinfo.xsd \
	                openfindorder.ini_INSTALL \
	                OLS_class_lib/ \
	                server.php \
	                xml/ \
	                docker/webservice/www/
	                """
            }

            stage("Docker: build image") {
                dir("docker/webservice") {
                    // build the image
                    //ouiImage = docker.build("docker-dscrum.dbc.dk/oui:latest")
                    ouiImage = docker.build("${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}")
                }
            }
            '''
            stage('Docker: push and cleanup') {
                docker.withRegistry('https://' + DOCKER_REPO, 'artifactory-api-key') {
                    ouiImage.push()
                }

                sh """
                   docker rmi ${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}
                    """
            }
            '''
        }

    }
}
