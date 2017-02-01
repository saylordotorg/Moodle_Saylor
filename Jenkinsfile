#!/usr/bin/env groovy

/* Only keep the 10 most recent builds. */
def projectProperties = [
    [$class: 'BuildDiscarderProperty',strategy: [$class: 'LogRotator', numToKeepStr: '5']],
]

properties(projectProperties)


try {
    stage ('Stash Repos') {
        parallel {
            "moodle" : {
                node {
                    git url: 'https://github.com/moodle/moodle.git'
                    stash name: 'moodle'
                }
            },
            "theme" : {
                node {
                    git url: 'https://github.com/saylordotorg/moodle-theme_saylor.git', branch: env.BRANCH_NAME
                    stash name: 'theme_saylor'
                }
            }
        }
    }
    node {
        stage('Build') {
            deleteDir()
            checkout scm

            unstash name: 'moodle'

            sh 'mkdir -p theme/saylor'
            dir("theme/saylor") {
                unstash name: 'theme_saylor'
            }

            sh 'ls -halt'
            echo env.BRANCH_NAME
        }

    }
}

catch (exc) {
    echo "Caught: ${exc}"
}