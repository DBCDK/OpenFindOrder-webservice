#!groovy

def PRODUCT = 'openfindorder'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def BRANCH = BRANCH_NAME.replaceAll("feature/", "").replace("_", "-")
def IMAGENAME = 'docker-dscrum.dbc.dk/openfindorder' + BRANCH + ':' + currentBuild.number
def BUILDNAME = PRODUCT + ' :: ' + BRANCH

print "Parameter: Version_2_5 = ${VERSION_2_5}"
print "Parameter: Version_2_6 = ${VERSION_2_6}"

// the image to use on different stages
def IMAGE

pipeline {
  agent {
      node { label 'd8-php7-builder' }
  }
  environment {
    DOCKER_HOST = 'tcp://dscrum-is:2375'
  }
  parameters {
    booleanParam(name: 'VERSION_2_5', defaultValue: 'true', description: 'Fetch version 2.5')
    booleanParam(name: 'VERSION_2_6', defaultValue: 'true', description: 'Fetch version 2.6')
  }
  options {
    buildDiscarder(logRotator(artifactDaysToKeepStr: "", artifactNumToKeepStr: "", daysToKeepStr: "", numToKeepStr: "5"))
    timestamps()
    gitLabConnection('gitlab.dbc.dk')
    disableConcurrentBuilds()
  }
  stages {

    stage('GIT: checkout code') {
      steps {
        checkout scm
        // get externals
        dir('src/OLS_class_lib') {
          git url: 'https://github.com/DBCDK/class_lib-webservice', branch: 'master'
        }
      }
    }

    stage('SetUp') {
      steps {
        // We'll want to work from the current branch,
        // not the release branches which will get checked out later.
        dir('docker') {
          sh """
            rm -rf webservice/
            cp -rp install/ webservice/
            ls -al
          """
        }
      }
    }

    stage("SVN: checkout externals") {
      steps {
        // Check out OpenVersionWrapper
        dir('docker/webservice') {
          sh """
            rm -rf www
            svn co https://svn.dbc.dk/repos/php/OpenLibrary/OpenVersionWrapper/trunk/ www
            cp OpenVersionWrapper.install/* www/
            ls -al
          """
        }
      }
    }

    stage("prepare website build (version 2.5)") {
      steps {
        script {
          if (VERSION_2_5) {
            // checkout release
            sh """
              git checkout release/2.5
              git pull
            """
            // Create folders & copy files needed for docker image.
            sh """
              mkdir 'docker/webservice/www/2.5'
              mkdir 'docker/webservice/www/next_2.5'
              mkdir 'docker/webservice/www/test_2.5'
              cp -r src/ docker/webservice/www/2.5/
              cp -r src/ docker/webservice/www/next_2.5/
              cp -r src/ docker/webservice/www/test_2.5/
            """
          }
          else {
            sh """
              echo 'skipping release/2.5'
            """
          }
        }
      }
    }

    stage("prepare website build (version 2.6)") {
      steps {
        script {
          if (VERSION_2_6) {
            // checkout release
            sh """
              git checkout release/2.5
              git pull
            """
            // Create folders & copy files needed for docker image.
            sh """
              mkdir 'docker/webservice/www/2.6'
              mkdir 'docker/webservice/www/next_2.6'
              mkdir 'docker/webservice/www/test_2.6'
              cp -r src/ docker/webservice/www/2.6/
              cp -r src/ docker/webservice/www/next_2.6/
              cp -r src/ docker/webservice/www/test_2.6/
            """
          }
          else {
            sh """
              echo 'skipping release/2.6'
            """
          }
        }
      }
    }

    stage("Set OpenVersionWrapper link") {
      steps {
        script {
          if (VERSION_2_5 || VERSION_2_6) {
            // make index.php symbolic link
            dir('docker/webservice/www') {
              sh """
                ln -s versions.php index.php
                ls -al
              """
            }
          }
          else {
            sh """
              echo 'No releases selected. '
            """
          }
        }
      }
    }

    stage("Docker: build image") {
      steps {
        dir('docker/webservice') {
          script {
            IMAGE = docker.build(IMAGENAME)
          }
        }
      }
    }

    stage('Push to artifactory ') {
      steps {
        script {
          def artyServer = Artifactory.server 'arty'
          def artyDocker = Artifactory.docker server: artyServer, host: env.DOCKER_HOST
          def buildInfo  = Artifactory.newBuildInfo()

          buildInfo.name = BUILDNAME
          buildInfo = artyDocker.push(IMAGENAME, 'docker-dscrum', buildInfo)
          buildInfo.env.capture = true
          buildInfo.env.collect()

          artyServer.publishBuildInfo buildInfo

          sh """
            docker rmi ${IMAGENAME}
          """
        }
      }
    }

  }

  post {
    success {
      script {
        def BUILD = DOCKER_REPO + '/' + PRODUCT + ':' +  currentBuild.number
        echo BUILD
      }
    }
    failure {
      // @TODO do something meaningfull
      echo 'FAIL'
    }
  }
}
