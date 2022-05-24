#!groovy
@Library('frontend-dscrum')

def WORKER_NODE = "devel10"
def PRODUCT = 'openfindorder'
def BRANCH = BRANCH_NAME.replaceAll(/[\/._ ]/, "-")

// Docker setup
def DOCKER_REPO = 'docker-fbiscrum.artifacts.dbccloud.dk'
def DOCKER_IMAGENAME = "${DOCKER_REPO}/${PRODUCT}-${BRANCH}:${BUILD_NUMBER}"
def NAMESPACE = (BRANCH == 'master') ? 'staging' : 'features'
def URL = 'http://' + PRODUCT  + '-' + BRANCH + '.' + "frontend-" + NAMESPACE + '.svc.cloud.dbc.dk' + '/'

//slack
def SLACK_CHANNEL_SUCCESS = "fe-jenkins"
def SLACK_CHANNEL_ERROR = "fe-fbi"

print "Parameter: PRODUCT = " + PRODUCT +
    "\n           BRANCH_NAME = " + BRANCH_NAME +
    "\n           DOCKER_REPO = " + DOCKER_REPO +
    "\n           DOCKER_IMAGENAME = " + DOCKER_IMAGENAME +
    "\n           NAMESPACE = " + NAMESPACE +
    "\n           URL = " + URL +
    "\n           BUILD_NUMBER = " + BUILD_NUMBER +
    "\n           WORKER_NODE = " + WORKER_NODE

pipeline {
  agent {
    node { label WORKER_NODE }
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
    stage('GIT: checkout VipCore and AuditTrail') {
      steps {
        dir('src') {
          sh """
            composer update --with-dependencies -v || die "Unable to run composer to get php dependencies"
          """
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
            def image = docker.build(DOCKER_IMAGENAME)
          }
        }
      }
    }

    stage('Push to artifactory ') {
      steps {
        script {
          docker.image("${DOCKER_IMAGENAME}").push("${BUILD_NUMBER}")
          sh "docker rmi ${DOCKER_IMAGENAME}"
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
              string(name: 'BuildId', value: BUILD_NUMBER.toString()),
              string(name: 'Namespace', value: 'staging'),
            ]
          }
          // Deploy to Kubernetes frontend-features namespace.
          else {
            build job: 'PHP Webservices/OpenFindOrder/openfindorder-deploy/features', parameters: [
              string(name: 'Branch', value: BRANCH_NAME),
              string(name: 'BuildId', value: BUILD_NUMBER.toString()),
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
        echo DOCKER_IMAGENAME
        slackSend(channel: SLACK_CHANNEL_SUCCESS,
          color: 'good',
          message: "${JOB_NAME} #${BUILD_NUMBER} completed, and pushed ${DOCKER_IMAGENAME} to artifactory.",
          tokenCredentialId: 'slack-global-integration-token')
      }
    }
    failure {
      script {
        slackSend(channel: SLACK_CHANNEL_ERROR,
          color: 'warning',
          message: "${JOB_NAME} #${BUILD_NUMBER} failed and needs attention: ${BUILD_URL}",
          tokenCredentialId: 'slack-global-integration-token')
        echo 'FAIL'
      }
    }
	always {
      echo 'Clean up workspace.'
      deleteDir()
      cleanWs()
    }
  }
}
