#!groovy
@Library('frontend-dscrum')

def PRODUCT = 'openfindorder'
def BRANCH = BRANCH_NAME.replaceAll(/[\/._ ]/, "-")
// def VERSION = '1.5'

// Docker setup
def DOCKER_HOST = 'tcp://dscrum-is:2375'
def DOCKER_REPO = 'docker-dscrum.dbc.dk'
def ARTIFACTORY_SERVER = Artifactory.server 'arty'
def ARTIFACTORY_DOCKER = Artifactory.docker server: ARTIFACTORY_SERVER, host: env.DOCKER_HOST
def IMAGENAME = 'docker-dscrum.dbc.dk/openfindorder-' + BRANCH + ':' + currentBuild.number

// Artifactory setup
def BUILDNAME = PRODUCT + ' :: ' + BRANCH
def NAMESPACE = (BRANCH == 'master') ? 'staging' : 'features'

// Post stages
def URL = 'http://' + PRODUCT  + '-' + BRANCH + '.' + "frontend-" + NAMESPACE + '.svc.cloud.dbc.dk' + '/'
// def MAIL_RECIPIENTS = 'lkh@dbc.dk, pjo@dbc.dk, jgn@dbc.dk, nwi@dbc.dk'

pipeline {
  agent {
    node { label 'devel10-head' }
  }
  options {
    buildDiscarder(logRotator(artifactDaysToKeepStr: "", artifactNumToKeepStr: "", daysToKeepStr: "", numToKeepStr: "5"))
    timestamps()
    gitLabConnection('gitlab.dbc.dk')
    disableConcurrentBuilds()
  }
  triggers {
		gitlab(
			triggerOnPush: true,
			triggerOnMergeRequest: true,
			branchFilterType: 'All',
			addVoteOnMergeRequest: true
		)
	}

  stages {
    stage('GIT: checkout AuditTrail') {
      steps {
        dir('src') {
          git (url:'gitlab@gitlab.dbc.dk:pu/audit/audit-trail-php-library.git', branch: 'master', credentialsId: 'gitlab-isworker')
          sh "ls -lat"
          sh "ls -lat src"
        }
      }
    }
    stage('SVN: checkout OLS_class_lib') {
			steps {
				dir('src') {
					sh """
						svn co https://svn.dbc.dk/repos/php/OpenLibrary/class_lib/trunk/ OLS_class_lib
					"""
				}
			}
		}

    stage("SVN: checkout OpenVersionWrapper") {
			steps {
				// Check out OpenVersionWrapper
				dir('docker/install') {
					sh """
						svn co https://svn.dbc.dk/repos/php/OpenLibrary/OpenVersionWrapper/trunk/ www
						cp OpenVersionWrapper.install/* www/
					"""
				}
			}
		}

    stage("prepare website build (version 2.5)") {
      steps {
        script {
          // checkout release
          sh """
            git checkout release/2.5
          """
          // Create folders & copy files needed for docker image.
          sh """
            mkdir 'docker/install/www/2.5'
            mkdir 'docker/install/www/next_2.5'
            mkdir 'docker/install/www/test_2.5'
            cp -r src/* docker/install/www/2.5
            cp -r src/* docker/install/www/next_2.5
            cp -r src/* docker/install/www/test_2.5
            ln -s server.php docker/install/www/2.5/index.php
            ln -s server.php docker/install/www/test_2.5/index.php
            ln -s server.php docker/install/www/next_2.5/index.php
          """
        }
      }
    }

    stage("prepare website build (version 2.6)") {
      steps {
        script {
          // checkout current version
          sh """
            git checkout $BRANCH_NAME
          """
          // Create folders & copy files needed for docker image.
          sh """
            mkdir 'docker/install/www/2.6'
            mkdir 'docker/install/www/next_2.6'
            mkdir 'docker/install/www/test_2.6'
            cp -r src/* docker/install/www/2.6/
            cp -r src/* docker/install/www/next_2.6/
            cp -r src/* docker/install/www/test_2.6/
            ln -s server.php docker/install/www/2.6/index.php
            ln -s server.php docker/install/www/test_2.6/index.php
            ln -s server.php docker/install/www/next_2.6/index.php
          """
        }
      }
    }

    stage("Set OpenVersionWrapper link") {
      steps {
        script {
          // make index.php symbolic link
          dir('docker/install/www') {
            sh """
              ln -s versions.php index.php
            """
          }
        }
      }
    }

    stage("Docker: build image") {
      steps {
        dir('docker/install') {
          script {
            def image = docker.build(IMAGENAME)
          }
        }
      }
    }

    stage('Push to artifactory ') {
      steps {
        script {
          def buildInfo  = Artifactory.newBuildInfo()

          buildInfo.name = BUILDNAME
          buildInfo = ARTIFACTORY_DOCKER.push(IMAGENAME, 'docker-dscrum', buildInfo)

          ARTIFACTORY_SERVER.publishBuildInfo buildInfo

          sh """
            docker rmi ${IMAGENAME}
          """
        }
      }
    }

    stage('Deploy') {
      steps {
        script {
          // Deploy to Kubernetes frontend-staging namespace.
          if (BRANCH_NAME == 'master') {
            build job: 'PHP Webservices/OpenFindOrder/openfindorder-deploy/staging', parameters: [
              string(name: 'Branch', value: 'master'),
              string(name: 'BuildId', value: currentBuild.number.toString()),
              string(name: 'Namespace', value: 'staging'),
            ]
          }
          // Deploy to Kubernetes frontend-features namespace.
          else {
            build job: 'PHP Webservices/OpenFindOrder/openfindorder-deploy/features', parameters: [
              string(name: 'Branch', value: BRANCH_NAME),
              string(name: 'BuildId', value: currentBuild.number.toString()),
              string(name: 'Namespace', value: 'features'),
            ]
          }
        }
      }
    }
  }

  post {
    success {
      script {
				echo URL
        def BUILD = DOCKER_REPO + '/' + PRODUCT + ':' +  currentBuild.number.toString()
        echo BUILD
      }
    }
    failure {
      // @TODO do something meaningfull
      echo 'FAIL'
    }
		always {
      echo 'Clean up workspace.'
      deleteDir()
      cleanWs()
    }
  }
}
