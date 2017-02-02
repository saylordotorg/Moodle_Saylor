#!/usr/bin/env groovy

/* Comments */


def plugins = [
    [
        "name" : 'theme_saylor',
        "url" : 'https://github.com/saylordotorg/moodle-theme_saylor.git',
        "branch" : env.BRANCH_NAME,
        "dest" : 'theme/saylor'
    ],
    [
        "name" : 'mod_journal',
        "url" : 'https://github.com/dmonllao/moodle-mod_journal.git',
        "branch" : 'MOODLE_31_STABLE',
        "dest" : 'mod/journal'
    ]

]

def StashMoodle() {
    node {
        deleteDir()
        git([url: 'https://github.com/moodle/moodle.git', branch: 'MOODLE_31_STABLE'])
        stash([name: 'moodle'])
    }
}

def StashPlugins(plugins) {
    for (int i = 0; i < plugins.size(); i++) {
        def x = i
        node {
            deleteDir()
            git([url: (plugins[x].get("url")), branch: (plugins[x].get("branch"))])
            echo("Stashing: ${plugins[x].get("name")}")
            stash([name: (plugins[x].get("name"))])
        }
    }
}

def UnstashPlugins(plugins) {
    for (int i = 0; i < plugins.size(); i++) {
        def x = i
        node {
            echo("Unstashing: ${plugins[x].get("name")}")
            sh "mkdir -p ${plugins[x].get("dest")}"
            dir("${plugins[x].get("dest")}") {
                unstash([name: (plugins[x].get("name"))])
            }
        }
    }
}

/* Start build process */
try {
    stage('Stash Repos') {
        echo("Beginning stashing operations")

        StashMoodle()
        StashPlugins(plugins)

        echo("Finished stashing operations")
    }
    stage('Build') {
        node {
            deleteDir()
            echo("Checking out SCM")
            checkout scm

            echo("Beginning unstashing operations")

            unstash name: 'moodle'
            UnstashPlugins(plugins)

            echo("Finished unstashing operations")

        }

    } 
    stage('Test - Create Test DB') {
        node {
            /* Load up necessary credentials */

            withCredentials([usernamePassword(credentialsId: 'mysql__user_npc-build', passwordVariable: 'mysql_password', usernameVariable: 'mysql_user')]) {
                sh "export MYSQL_PWD=${mysql_password}"

                if(env.BRANCH_NAME == 'master') {
                    withCredentials([string(credentialsId: 'mysql-prod-01_host', variable: 'mysql_host')]) {
                        // If we're on the master branch, this is probably for production so test against the current prod db
                        echo("Setting mysql_host to production database: ${env.mysql_host}")

                    echo("Dumping moodle database from ${env.mysql_host}")
                    sh "mysqldump --host ${env.mysql_host} -u ${mysql_user} moodle > /tmp/moodle_dump.sql"
                    }
                }
                else {
                    withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_host')]) {
                        // Grab the development database to test if this isn't for the master branch (production environment)
                        echo("Setting mysql_host to development database: ${env.mysql_host}")

                    echo("Dumping moodle database from ${env.mysql_host}")
                    sh "mysqldump --host ${env.mysql_host} -u ${mysql_user} moodle > /tmp/moodle_dump.sql"
                    }
                }

            }


        }

    }
    stage('Test - Run Upgrade') {

    }
}

catch (exc) {
    echo "Caught: ${exc}"
    throw exc
}