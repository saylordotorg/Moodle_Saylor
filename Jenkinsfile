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
    node {
        stage('Test') {
            sh 'ls -halt'
            echo env.BRANCH_NAME
        }

    }
}
catch (exc) {
    echo "Caught: ${exc}"
}