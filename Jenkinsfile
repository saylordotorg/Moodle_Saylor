#!/usr/bin/env groovy

/* Comments */

def mysql_source_dbname = 'moodle'
def mysql_dest_dbname = 'moodle_test'

def moodle_version = 'MOODLE_310_STABLE'

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
        "branch" : 'master',
        "dest" : 'mod/journal'
    ],
    [
        "name" : 'format_flexsections',
        "url" : 'https://github.com/marinaglancy/moodle-format_flexsections.git',
        "branch" : 'master',
        "dest" : 'course/format/flexsections'
    ],
    [
        "name" : 'format_grid',
        "url" : 'https://github.com/gjb2048/moodle-format_grid.git',
        "branch" : 'master',
        "dest" : 'course/format/grid'
    ],
    [
        "name" : 'format_topcoll',
        "url" : 'https://github.com/gjb2048/moodle-format_topcoll.git',
        "branch" : 'MOODLE_310',
        "dest" : 'course/format/topcoll'
    ],
    [
        "name" : 'mod_hotpot',
        "url" : 'https://github.com/gbateson/moodle-mod_hotpot.git',
        "branch" : 'master',
        "dest" : 'mod/hotpot'
    ],
    [
        "name" : 'mod_checklist',
        "url" : 'https://github.com/davosmith/moodle-checklist.git',
        "branch" : 'master',
        "dest" : 'mod/checklist'
    ],
    [
        "name" : 'block_checklist',
        "url" : 'https://github.com/davosmith/moodle-block_checklist.git',
        "branch" : 'master',
        "dest" : 'blocks/checklist'
    ],
    [
        "name" : 'grade_checklist',
        "url" : 'https://github.com/davosmith/moodle-grade_checklist.git',
        "branch" : 'master',
        "dest" : 'grade/export/checklist'
    ],
    [
        "name" : 'block_workflow',
        "url" : 'https://github.com/moodleou/moodle-block_workflow.git',
        "branch" : 'main',
        "dest" : 'blocks/workflow'
    ],
    [
        "name" : 'mod_accredible',
        "url" : 'https://github.com/accredible/moodle-mod_accredible.git',
        "branch" : 'master',
        "dest" : 'mod/accredible'
    ],
    [
        "name" : 'report_myfeedback',
        "url" : 'https://github.com/jgramp/moodle-report_myfeedback.git',
        "branch" : 'master',
        "dest" : 'report/myfeedback'
    ],
    [
        "name" : 'qtype_jme',
        "url" : 'https://github.com/jmvedrine/moodle-qtype_jme.git',
        "branch" : 'master',
        "dest" : 'question/type/jme'
    ],
    [
        "name" : 'qtype_jme_JSME',
        "url" : 'https://github.com/saylordotorg/JSME-deploy.git',
        "branch" : 'master',
        "dest" : 'question/type/jme/jsme'
    ],
    [
        "name" : 'qtype_algebra',
        "url" : 'https://github.com/jmvedrine/moodle-qtype_algebra.git',
        "branch" : 'master',
        "dest" : 'question/type/algebra'
    ],
    [
        "name" : 'qtype_pmatch',
        "url" : 'https://github.com/moodleou/moodle-qtype_pmatch.git',
        "branch" : 'main',
        "dest" : 'question/type/pmatch'
    ],
    [
        "name" : 'qbehaviour_adaptivehints',
        "url" : 'https://github.com/saylordotorg/moodle-qbehaviour_adaptivehints.git',
        "branch" : 'master',
        "dest" : 'question/behaviour/adaptivehints'
    ],
    [
        "name" : 'qbehaviour_adaptivehintsnopenalties',
        "url" : 'https://github.com/saylordotorg/moodle-qbehaviour_adaptivehintsnopenalties.git',
        "branch" : 'master',
        "dest" : 'question/behaviour/adaptivehintsnopenalties'
    ],
    [
        "name" : 'qbehaviour_interactivehints',
        "url" : 'https://github.com/saylordotorg/moodle-qbehaviour_interactivehints.git',
        "branch" : 'master',
        "dest" : 'question/behaviour/interactivehints'
    ],
    [
        "name" : 'qtype_poasquestion',
        "url" : 'https://github.com/saylordotorg/moodle-qtype_poasquestion.git',
        "branch" : 'master',
        "dest" : 'question/type/poasquestion'
    ],
    [
        "name" : 'local_wsfunc',
        "url" : 'https://github.com/saylordotorg/moodle-local_wsfunc.git',
        "branch" : 'master',
        "dest" : 'local/wsfunc'
    ],
    [
        "name" : 'qtype_ordering',
        "url" : 'https://github.com/gbateson/moodle-qtype_ordering.git',
        "branch" : 'master',
        "dest" : 'question/type/ordering'
    ],
    [
        "name" : 'block_heatmap',
        "url" : 'https://github.com/deraadt/moodle-block_heatmap.git',
        "branch" : 'master',
        "dest" : 'blocks/heatmap'
    ],
    [
        "name" : 'local_unusedquestions',
        "url" : 'https://github.com/morrisr2/moodle_local_unusedquestions.git',
        "branch" : 'master',
        "dest" : 'local/unusedquestions'
    ],
    [
        "name" : 'auth_mcae',
        "url" : 'https://github.com/kamat/moodle-auth_mcae.git',
        "branch" : 'master',
        "dest" : 'auth/mcae'
    ],
    [
        "name" : 'mod_reengagement',
        "url" : 'https://github.com/catalyst/moodle-mod_reengagement.git',
        "branch" : 'master',
        "dest" : 'mod/reengagement'
    ],
    [
        "name" : 'local_discoursesso',
        "url" : 'https://github.com/saylordotorg/moodle-local_discoursesso.git',
        "branch" : 'master',
        "dest" : 'local/discoursesso'
    ],
    [
        "name" : 'block_sayloroverview',
        "url" : 'https://github.com/saylordotorg/moodle-block_sayloroverview.git',
        "branch" : 'master',
        "dest" : 'blocks/sayloroverview'
    ],
    [
        "name" : 'block_completion_progress',
        "url" : 'https://github.com/deraadt/moodle-block_completion_progress',
        "branch" : 'master',
        "dest" : 'blocks/completion_progress'
    ],
    [
        "name" : 'local_abtesting',
        "url" : 'https://github.com/saylordotorg/moodle-local_abtesting.git',
        "branch" : 'master',
        "dest" : 'local/abtesting'
    ],
    [
        "name" : 'local_boostnavigation',
        "url" : 'https://github.com/moodleuulm/moodle-local_boostnavigation',
        "branch" : 'master',
        "dest" : 'local/boostnavigation'
    ],
    [
        "name" : 'qtype_gapfill',
        "url" : 'https://github.com/marcusgreen/moodle-qtype_gapfill',
        "branch" : 'master',
        "dest" : 'question/type/gapfill'
    ],
    [
        "name" : 'local_affiliations',
        "url" : 'https://github.com/saylordotorg/moodle-local_affiliations',
        "branch" : 'master',
        "dest" : 'local/affiliations'
    ],
    [
        "name" : 'qtype_coderunner',
        "url" : 'https://github.com/trampgeek/moodle-qtype_coderunner.git',
        "branch" : 'master',
        "dest" : 'question/type/coderunner'
    ],
    [
        "name" : 'qbehaviour_adaptive_adapted_for_coderunner',
        "url" : 'https://github.com/trampgeek/moodle-qbehaviour_adaptive_adapted_for_coderunner.git',
        "branch" : 'master',
        "dest" : 'question/behaviour/adaptive_adapted_for_coderunner'
    ],
    [
        "name" : 'filter_generico',
        "url" : 'https://github.com/justinhunt/moodle-filter_generico.git',
        "branch" : 'master',
        "dest" : 'filter/generico'
    ],
    [
        "name" : 'atto_generico',
        "url" : 'https://github.com/justinhunt/moodle-atto_generico.git',
        "branch" : 'master',
        "dest" : 'lib/editor/atto/plugins/generico'
    ],
    [
        "name" : 'block_configurable_reports',
        "url" : 'https://github.com/jleyva/moodle-block_configurablereports.git',
        "branch" : 'MOODLE_36_STABLE',
        "dest" : 'blocks/configurable_reports'
    ],
    [
        "name" : 'mod_hvp',
        "url" : 'https://github.com/h5p/moodle-mod_hvp',
        "branch" : 'stable',
        "dest" : 'mod/hvp'
    ],
    [
        "name" : 'report_completionoverview',
        "url" : 'https://github.com/Twoscope/moodle-report_completionoverview',
        "branch" : 'master',
        "dest" : 'report/completionoverview'
    ],
    [
        "name" : 'block_accredibledashboard',
        "url" : 'https://github.com/saylordotorg/moodle-block_accredibledashboard',
        "branch" : 'development',
        "dest" : 'blocks/accredibledashboard'
    ],
    [
        "name" : 'auth_nsdc',
        "url" : 'https://github.com/saylordotorg/moodle-auth_nsdc.git',
        "branch" : 'development',
        "dest" : 'auth/nsdc'
    ],
    [
        "name" : 'block_sharing_cart',
        "url" : 'https://github.com/donhinkelman/moodle-block_sharing_cart.git',
        "branch" : 'master',
        "dest" : 'blocks/sharing_cart'
    ],
    [
        "name" : 'block_quickmail',
        "url" : 'https://github.com/lsuits/lsu-block_quickmail.git',
        "branch" : 'master',
        "dest" : 'blocks/quickmail'
    ],
    [
        "name" : 'mod_questionnaire',
        "url" : 'https://github.com/PoetOS/moodle-mod_questionnaire.git',
        "branch" : 'MOODLE_310_STABLE',
        "dest" : 'mod/questionnaire'
    ],
    [
        "name" : 'tool_mfa',
        "url" : 'https://github.com/catalyst/moodle-tool_mfa.git',
        "branch" : 'master',
        "dest" : 'tool/mfa'
    ],
    [
        "name" : 'mod_solo',
        "url" : 'https://github.com/justinhunt/moodle-mod_solo.git',
        "branch" : 'main',
        "dest" : 'mod/solo'
    ],
    [
        "name" : 'filter_poodll',
        "url" : 'https://github.com/justinhunt/moodle-filter_poodll.git',
        "branch" : 'poodll3',
        "dest" : 'filter/poodll'
    ],
    [
        "name" : 'atto_poodll',
        "url" : 'https://github.com/justinhunt/moodle-atto_poodll.git',
        "branch" : 'poodll3',
        "dest" : 'lib/editor/atto/plugins/poodll'
    ],
    [
        "name" : 'qtype_poodllrecording',
        "url" : 'https://github.com/justinhunt/moodle-qtype_poodllrecording.git',
        "branch" : 'poodll3',
        "dest" : 'question/type/poodllrecording'
    ],
    [
        "name" : 'mod_minilesson',
        "url" : 'https://github.com/justinhunt/moodle-mod_minilesson.git',
        "branch" : 'master',
        "dest" : 'mod/minilesson'
    ],
    [
        "name" : 'auth_basic',
        "url" : 'https://github.com/catalyst/moodle-auth_basic.git',
        "branch" : 'master',
        "dest" : 'auth/basic'
    ],
    [
        "name" : 'tool_crawler',
        "url" : 'https://github.com/catalyst/moodle-tool_crawler.git',
        "branch" : 'master',
        "dest" : 'tool/crawler'
    ],
    [
        "name" : 'atto_linkadv',
        "url" : 'https://github.com/sharpchi/moodle-atto_linkadv.git',
        "branch" : 'master',
        "dest" : 'lib/editor/atto/plugins/linkadv'
    ],
    [
        "name" : 'mod_wordcards',
        "url" : 'https://github.com/justinhunt/moodle-mod_wordcards.git',
        "branch" : 'master',
        "dest" : 'mod/wordcards'
    ],
    [
        "name" : 'mod_minilesson',
        "url" : 'https://github.com/justinhunt/moodle-mod_minilesson.git',
        "branch" : 'master',
        "dest" : 'mod/minilesson'
    ],
    [
        "name" : 'mod_readaloud',
        "url" : 'https://github.com/justinhunt/moodle-mod_readaloud.git',
        "branch" : 'master',
        "dest" : 'mod/readaloud'
    ],
    [
        "name" : 'filter_filtercodes',
        "url" : 'https://github.com/michael-milette/moodle-filter_filtercodes.git',
        "branch": 'master',
        "dest" : 'filter/filtercodes'
    ],
    [
        "name" : 'mod_simplecertificate',
        "url" : 'https://github.com/bozoh/moodle-mod_simplecertificate.git',
        "branch" : 'master',
        "dest" : 'mod/simplecertificate'
    ],
    [
        "name" : 'availability_mobileapp',
        "url" : 'https://github.com/moodlehq/moodle-availability_mobileapp.git',
        "branch" : 'master',
        "dest" : 'availability/condition/mobileapp'
    ],
    [
      "name" : 'availability_cohort',
      "url" : 'https://github.com/moodleuulm/moodle-availability_cohort.git',
      "branch" : 'master',
      "dest" : 'availability/condition/cohort'
    ],
    [
      "name" : 'format_board',
      "url" : 'https://github.com/brandaorodrigo/moodle-format_board.git',
      "branch" : 'master',
      "dest" : 'course/format/board'
    ]
]

def StashMoodle(moodle_version) {
    node {
        deleteDir()
            try {
                git([url: 'https://github.com/moodle/moodle.git', branch: "${moodle_version}"])
            }
            catch(err) {
                NotifyOnFail("Unable to retrieve Moodle: ${err}")
            }
        // Remove the Moodle .git and .github folder.
        sh "rm -rf .git"
        sh "rm -rf .github"
        stash([name: 'moodle'])
    }
}

def StashPlugins(plugins) {
    for (int i = 0; i < plugins.size(); i++) {
        def x = i
        node {
            deleteDir()
            try {
                //git([url: (plugins[x].get("url")), branch: (plugins[x].get("branch"))])
                checkout([$class: 'GitSCM', 
                    branches: [[name: plugins[x].get("branch")]],
                    userRemoteConfigs: [[url: plugins[x].get("url")]],
                    doGenerateSubmoduleConfigurations: false,
                    extensions: [[$class: 'CleanBeforeCheckout'],
                                [$class: 'CloneOption',
                                    depth: 0,
                                    shallow: true
                                ],
                                [$class: 'SubmoduleOption',
                                disableSubmodules: false,
                                parentCredentials: true,
                                recursiveSubmodules: true,
                                reference: '',
                                trackingSubmodules: false
                                ]
                                ], 
                    submoduleCfg: []
                ])
            }
            catch(err) {
                def failmessage = "Unable to retrieve plugin ${plugins[x].get('name')}: ${err}"
                NotifyOnFail(failmessage)
            }
            sh "rm -rf .git"
            sh "rm -rf .github"
            echo("Stashing: ${plugins[x].get("name")}")
            stash([name: (plugins[x].get("name"))])
        }
    }
}

def UnstashPlugins(plugins) {
    for (int i = 0; i < plugins.size(); i++) {
        def x = i

        echo("Unstashing: ${plugins[x].get("name")}")
        sh "mkdir -p ${plugins[x].get("dest")}"
        dir("${plugins[x].get("dest")}") {
            unstash([name: (plugins[x].get("name"))])
        }
    }
}

def Cleanup() {
    // Will leave the moodle folder + plugins as an artifact.
        // Cleanup dummy moodledata folder from artifact
        sh 'rm -rf moodledata'
        //sh 'rm config.php'

}

def CopyDatabase(mysql_user, mysql_password, mysql_source_host, mysql_source_dbname, mysql_dest_dbname) {
    // Drop previous test db and transfer source db
    // Test db host is mysql-dev-01
    withCredentials([string(credentialsId: 'mysql-dev-01_host', variable: 'mysql_dest_host')]) {
        echo("Dropping test database (${mysql_dest_dbname}) and transferring source data")
        // For now, manually putting in the test db name due to an issue with string interpolation.
        sh 'mysql -h $mysql_dest_host -u $mysql_user --password=$mysql_password --execute=' +
            "\"drop database ${mysql_dest_dbname}\"".toString()
        sh 'mysql -h $mysql_dest_host -u $mysql_user --password=$mysql_password --execute=' +
            "\"create database ${mysql_dest_dbname}\"".toString()
        // First, create the database structure.
        sh 'mysqldump --column-statistics=0 --set-gtid-purged=OFF --single-transaction --no-tablespaces --host $mysql_source_host -u $mysql_user --password=$mysql_password --no-data ' +
            "${mysql_source_dbname} ".toString() +
            '| mysql -h $mysql_dest_host -u $mysql_user --password=$mysql_password ' +
            "${mysql_dest_dbname}".toString()
        // Piping output of dump directly to mysql to increase speed of transfer.
        sh 'mysqldump --column-statistics=0 --set-gtid-purged=OFF --single-transaction --no-tablespaces --host $mysql_source_host -u $mysql_user --password=$mysql_password ' +
            "--ignore-table=${mysql_source_dbname}.mdl_logstore_standard_log --ignore-table=${mysql_source_dbname}.mdl_backup_logs --ignore-table=${mysql_source_dbname}.mdl_upgrade_log --ignore-table=${mysql_source_dbname}.mdl_sessions --ignore-table=${mysql_source_dbname}.mdl_stats_daily --ignore-table=${mysql_source_dbname}.mdl_stats_monthly --ignore-table=${mysql_source_dbname}.mdl_stats_weekly --ignore-table=${mysql_source_dbname}.mdl_stats_user_daily --ignore-table=${mysql_source_dbname}.mdl_stats_user_weekly --ignore-table=${mysql_source_dbname}.mdl_stats_user_monthly --ignore-table=${mysql_source_dbname}.mdl_grade_grades_history --ignore-table=${mysql_source_dbname}.mdl_grade_grades --ignore-table=${mysql_source_dbname}.mdl_question_attempts --ignore-table=${mysql_source_dbname}.mdl_question_attempt_steps --ignore-table=${mysql_source_dbname}.mdl_question_attempt_step_data ${mysql_source_dbname} | ".toString() +
            'mysql -h $mysql_dest_host -u $mysql_user --password=$mysql_password ' +
            "${mysql_dest_dbname}".toString()
    }

}

def NotifyOnComplete() {
    // What to do when build is successful.
    def message = "Build completed successfully: ${env.JOB_NAME} #${env.BUILD_NUMBER} on ${env.BRANCH_NAME}"

    // For now, we post in slack.
    slackSend color: 'good', message: "${message}"
}

def NotifyOnFail(err) {
    // What to do when a build is unsuccessful.
    def message = "Build failed: ${env.JOB_NAME} ${env.BUILD_NUMBER} on ${env.BRANCH_NAME} -> ${err}"

    // List of people to @ in Slack since this is important.
    def slack_recipients = '@ja'

    //Slack
    slackSend color: 'danger', message: "${slack_recipients} ${message}"

}

/* Start build process */
try {
    stage('Build') {
        echo("Beginning stashing operations")

        StashMoodle(moodle_version)
        StashPlugins(plugins)

        echo("Finished stashing operations")

        node('master') {
            deleteDir()
            echo("Checking out SCM")
            checkout scm

            // We want to remove all the old package files but preserve the .git folder.
            sh'''#!/bin/bash -xe
                    shopt -s extglob
                    rm -r ./!(.git|.|..|Jenkinsfile)
            '''

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
                        sh 'sed -i -e \"s/{{mysql_dest_host}}/$mysql_test_host/\" -e \"s/{{mysql_dest_dbname}}/'+"${mysql_dest_dbname}".toString()+'/\" -e \"s/{{mysql_user}}/$mysql_user/\" -e \"s/{{mysql_password}}/$mysql_password/\" config.php'

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
        node('master') {
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
}

catch (err) {
    echo "Caught: ${err}"
    NotifyOnFail("Failed to build: ${err}")
    throw err
}
