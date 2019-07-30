#!groovy

properties([buildDiscarder(logRotator(artifactDaysToKeepStr: '', artifactNumToKeepStr: '', daysToKeepStr: '', numToKeepStr: dscrumDefaults.numToKeepStr())),
            pipelineTriggers([cron('H 6 * * *')]),
            parameters([
                    booleanParam(defaultValue: false, description: 'fetch version 2.5', name: 'Version_2_5'),
                    booleanParam(defaultValue: false, description: 'fetch version 2.6', name: 'Version_2_6')]),
            disableConcurrentBuilds(),
])

print "Parameter: Version_2_5 = ${Version_2_5}"
print "Parameter: Version_2_6 = ${Version_2_6}"

def PRODUCT = 'openfindorder'
def DOCKER_HOST = 'tcp://dscrum-is:2375'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def MAIL_RECIPIENTS = 'lkh@dbc.dk, pjo@dbc.dk, jgn@dbc.dk, niw@dbc.dk'
def WORKSPACE = "workspace/$PRODUCT"
def VERSION_2_5 = params.Version_2_5
def VERSION_2_6 = params.Version_2_6

def util

print "Parameter: PRODUCT = " + PRODUCT
print "Parameter: DOCKER_HOST = ${DOCKER_HOST}"
print "Parameter: DOCKER_REPO = ${DOCKER_REPO}"
print "Parameter: MAIL_RECIPIENTS = ${MAIL_RECIPIENTS}"
print "Parameter: WORKSPACE = ${WORKSPACE}"

// the image to use on different stages
def ofoImage

//node("d8-php7-builder") {
node("master") {
    ws(WORKSPACE) {
        withEnv(["DOCKER_HOST=${DOCKER_HOST}"]) {
            stage('GIT: checkout code') {
                checkout scm
            }

            stage('SetUp') {
                script {
                    util = load("jenkins/scripts/utilities.groovy")
                }
            }

            stage("SVN: checkout externals") {
                // get externals
                // Check out OpenVersionWrapper & class_lib
                sh """
                    svn co 'https://svn.dbc.dk/repos/php/OpenLibrary/class_lib/trunk/' OLS_class_lib
                    """
                dir('docker/webservice') {
                    sh """
	                      rm -rf www
	                      svn co https://svn.dbc.dk/repos/php/OpenLibrary/OpenVersionWrapper/trunk/ www
                        cp OpenVersionWrapper/* www/
	                      """
                    sh """
                        ls -al
                        cp foo.test_INSTALL foo.test
                        """
                }

            }

            stage("prepare website build (version 2.5)") {
                if (VERSION_2_5) {
                    // checkout release
                    sh """
                      git checkout feature/release_2_5
                      """
                    // copy files needed for docker image
                    util.copyDockerFiles('2.5')
                    util.copyDockerFiles('next_2.5')
                    util.copyDockerFiles('test_2.5')
                }
                else {
                    sh """
                        echo 'skipping release/2.5'
                    """
                }
            }

            stage("prepare website build (version 2.6)") {
                if (VERSION_2_6) {
                    // checkout release
                    sh """
                      git checkout feature/release_2_6
                      """
                    // copy files needed for docker image
                    util.copyDockerFiles('2.6')
                    util.copyDockerFiles('next_2.6')
                    util.copyDockerFiles('test_2.6')
                }
                else {
                    sh """
                        echo 'skipping release/2.6'
                    """
                }
            }

            stage("Set OpenVersionWrapper link") {
                if (VERSION_2_5 || VERSION_2_6) {
                    // make index.php symbolic link
                    dir('docker/webservice/www') {
                        sh """
                            ln -s versions.php index.php
                            """
                    }
                }
                else {
                    sh """
                        echo 'No releases selected. '
                    """
                }

            }

            stage("Docker: build image") {
                dir("docker/webservice") {
                    // build the image
                    // ouiImage = docker.build("docker-dscrum.dbc.dk/oui:latest")
                    ofoImage = docker.build("${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}")
                }
            }

            stage('Docker: push and cleanup') {
                // drop artifactory update while fooling around
                // docker.withRegistry('https://' + DOCKER_REPO, 'artifactory-api-key') {
                //   ofoImage.push()
                // }

                sh """
                    docker rmi ${DOCKER_REPO}/${PRODUCT}:${currentBuild.number}
                    """
            }
        }
    }
}
