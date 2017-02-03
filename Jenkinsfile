#!/usr/bin/env groovy

/* Comments */

def mysql_source_dbname = 'moodle'
def mysql_dest_dbname = 'moodle_test'

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

def Cleanup() {
    // Need to clean up the test config file.
    // Will leave the moodle folder + plugins as an artifact.
    node {
        sh 'rm config.php'
    }

}

def CopyDatabase(mysql_user, mysql_password, mysql_source_host, mysql_source_dbname, mysql_dest_dbname) {
    // Drop previous test db and transfer source db
    // Test db host is mysql-dev-01
    withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_dest_host')]) {
        echo("Dropping test database and transferring source data")
        sh "mysql -h ${mysql_host} -u ${mysql_user} --password=${mysql_password} --execute=\"drop database ${mysql_dest_dbname}\""
        sh "mysql -h ${mysql_host} -u ${mysql_user} --password=${mysql_password} --execute=\"create database ${mysql_dest_dbname}\""
        // Piping output of dump directly to mysql to increase speed of transfer. Also using mysqlpump instead of mysqldump.
        sh "mysqlpump --single-transaction --host ${mysql_source_host} -u ${mysql_user} --password=${mysql_password} ${mysql_source_dbname} | mysql -h ${mysql_dest_host} -u ${mysql_user} --password=${mysql_password} ${mysql_dest_dbname}"
    }

}

/* Start build process */
try {
    stage('Build') {
        echo("Beginning stashing operations")

        StashMoodle()
        StashPlugins(plugins)

        echo("Finished stashing operations")

        node('master') {
            deleteDir()
            echo("Checking out SCM")
            checkout scm

            echo("Beginning unstashing operations")

            unstash name: 'moodle'
            UnstashPlugins(plugins)

            echo("Finished unstashing operations")

        }

    } 
    stage('Test - Set Up Environment') {
        node('master') {
            /* Load up necessary credentials */

            withCredentials([usernamePassword(credentialsId: 'mysql__user_npc-build', passwordVariable: 'mysql_password', usernameVariable: 'mysql_user')]) {
                // TODO: Comment on this block

                if(env.BRANCH_NAME == 'master') {
                    withCredentials([string(credentialsId: 'mysql-prod-01_host', variable: 'mysql_source_host')]) {
                        // If we're on the master branch, this is probably for production so test against the current prod db
                        echo("Setting mysql_source_host to production database.")
                        CopyDatabase(mysql_user, mysql_password, mysql_source_host, mysql_source_dbname, mysql_dest_dbname)
                    }
                }
                else {
                    withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_source_host')]) {
                        // Grab the development database to test if this isn't for the master branch (production environment)
                        echo("Setting mysql_source_host to development database.")
                        CopyDatabase(mysql_user, mysql_password, mysql_source_host, mysql_source_dbname, mysql_dest_dbname)
                    }
                }

            }
        }

    }
    stage('Test - Run Upgrade') {
        node('master') {

            withCredentials([usernamePassword(credentialsId: 'mysql__user_npc-build', passwordVariable: 'mysql_password', usernameVariable: 'mysql_user')]) {
                withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_host')]) {
                    configFileProvider([configFile(fileId: 'moodle-test-config', replaceTokens: true, targetLocation: 'config.php')]) {
                        sh 'cat config.php'
                        echo("Beginning upgrade")

                        sh '/usr/bin/php admin/cli/upgrade.php --non-interactive'

                        echo("Finished upgrade")
                    }
                }
            }
        }

    }
}

catch (exc) {
    echo "Caught: ${exc}"
    Cleanup()
    throw exc
}