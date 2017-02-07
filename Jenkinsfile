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
    // Will leave the moodle folder + plugins as an artifact.
        // Cleanup dummy moodledata folder from artifact
        sh 'rm -rf moodledata'

}

def CopyDatabase(mysql_user, mysql_password, mysql_source_host, mysql_source_dbname, mysql_dest_dbname) {
    // Drop previous test db and transfer source db
    // Test db host is mysql-dev-01
    withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_dest_host')]) {
        echo("Dropping test database and transferring source data")
        sh "mysql -h ${mysql_dest_host} -u ${mysql_user} --password=${mysql_password} --execute=\"drop database ${mysql_dest_dbname}\""
        sh "mysql -h ${mysql_dest_host} -u ${mysql_user} --password=${mysql_password} --execute=\"create database ${mysql_dest_dbname}\""
        // Piping output of dump directly to mysql to increase speed of transfer. 
        sh "mysqldump --single-transaction --host ${mysql_source_host} -u ${mysql_user} --password=${mysql_password} ${mysql_source_dbname} | mysql -h ${mysql_dest_host} -u ${mysql_user} --password=${mysql_password} ${mysql_dest_dbname}"
    }

}

def NotifyOnComplete() {
    // What to do when build is successful.
    def message = "Build completed successfully: ${env.JOB_NAME} ${env.BUILD_NUMBER} on ${env.BRANCH_NAME}"

    // For now, we post in slack.
    slackSend color: 'good', message: "${message}"
}

def NotifyOnFail(err) {
    // What to do when a build is unsuccessful.
    def message = "Build failed: ${env.JOB_NAME} ${env.BUILD_NUMBER} on ${env.BRANCH_NAME} -> ${err}"

    // List of people to @ in Slack since this is important.
    def slack_recipients = '@ja @sharmi'

    //Slack
    slackSend color: 'danger', message: "${slack_recipients} ${message}"

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
                withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_test_host')]) {
                    configFileProvider([configFile(fileId: 'moodle-test-config', replaceTokens: true, targetLocation: 'config.php')]) {
                        // Run sed to substitute the proper variables in the config.php file
                        sh "sed -i -e \'s/{{mysql_dest_host}}/${mysql_test_host}/\' -e \'s/{{mysql_dest_dbname}}/${mysql_dest_dbname}/\' -e \'s/{{mysql_user}}/${mysql_user}/\' -e \'s/{{mysql_password}}/${mysql_password}/\' config.php"

                        echo("Beginning upgrade")
                        try {
                            // Make a dummy moodledata folder for moodle
                            sh 'mkdir moodledata'
                            sh '/usr/bin/php admin/cli/upgrade.php --non-interactive'
                        }
                        catch(err) {
                            echo "Moodle upgrade failed: ${err}"

                            NotifyOnFail("Moodle upgrade failed: ${err}")
                            Cleanup()

                            throw err
                        }
                        echo("Finished upgrade")
                    }
                }
            }

        }

    }
    stage('Push changes') {
        // git add .
        // git commit -m "MESSAGE"
        def commitMessage = "Build #${env.BUILD_NUMBER} - Automated Commit Message"

        Cleanup()

        try {
            echo("Adding changed files")
            sh("git add .")

            echo("Commiting changes")
            sh("git commit -m \"${commitMessage}\"")
        }
        catch(err) {
            echo("Failed to commit changes: ${err}")

            // Note: this might fail if no changes have been made since last build
            NotifyOnFail("Failed to commit changes: ${err}")

            throw err
        }
        try {
            echo("Pushing to GitHub")
            sshagent(['6728e4b0-6b97-4d5b-96d0-c47cf4510ece']) {
                // some block
                sh("git push origin HEAD:${env.BRANCH_NAME}")
            }
        }
        catch(err) {
            echo("Failed to push to GitHub: ${err}")

            // Note: this might fail if no changes have been made since last build
            NotifyOnFail("Failed to push to GitHub: ${err}")

            throw err            
        }

        NotifyOnComplete()
    }
}

catch (err) {
    echo "Caught: ${err}"
    throw err
}