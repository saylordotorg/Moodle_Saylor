#!/usr/bin/env groovy

/* Only keep the 10 most recent builds. */
def projectProperties = [
    [$class: 'BuildDiscarderProperty',strategy: [$class: 'LogRotator', numToKeepStr: '5']],
]

if (!env.CHANGE_ID) {
    if (env.BRANCH_NAME == null) {
        projectProperties.add(pipelineTriggers([cron('H/30 * * * *')]))
    }
}

properties(projectProperties)


try {
        stage ('Stash Repos') {
            node {
                git url: 'https://github.com/saylordotorg/moodle-theme_saylor.git', branch: env.BRANCH_NAME
                stash name: theme_saylor
            }
            node {
                git url: 'https://github.com/moodle/moodle.git'
                stash name: 'moodle'
            }
            echo "Stashed"
        }
        stage('Build') {

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
catch (exc) {
    echo "Caught: ${exc}"
}