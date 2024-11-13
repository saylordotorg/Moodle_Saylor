#!/usr/bin/env groovy

/* Comments */

def mysql_source_dbname = 'moodle'
def mysql_dest_dbname = 'moodle_test'

def moodle_version = 'MOODLE_311_STABLE'

def plugins = [
    [
        "name" : 'mod_journal',
        "url" : 'https://github.com/dmonllao/moodle-mod_journal.git',
        "branch" : 'master',
        "dest" : 'mod/journal'
    ],
    [
        "name" : 'format_flexsections',
        "url" : 'https://github.com/marinaglancy/moodle-format_flexsections.git',
        "branch" : 'MOODLE_311_STABLE',
        "dest" : 'course/format/flexsections'
    ],
    [
        "name" : 'format_grid',
        "url" : 'https://github.com/gjb2048/moodle-format_grid.git',
        "branch" : 'MOODLE_311',
        "dest" : 'course/format/grid'
    ],
    [
        "name" : 'format_topcoll',
        "url" : 'https://github.com/gjb2048/moodle-format_topcoll.git',
        "branch" : 'MOODLE_311',
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
        "branch" : 'v1.7.4',
        "dest" : 'mod/accredible'
    ],
    [
        "name" : 'report_myfeedback',
        "url" : 'https://github.com/jgramp/moodle-report_myfeedback.git',
        "branch" : 'main',
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
        "url" : 'https://github.com/saylordotorg/moodle-block_completion_progress',
        "branch" : 'fix/missing-progress-bar',
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
        "branch" : 'MOODLE_311_STABLE',
        "dest" : 'local/boostnavigation'
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
        "branch" : 'MOODLE_3X_STABLE',
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
        "branch" : 'master',
        "dest" : 'blocks/accredibledashboard'
    ],
    [
        "name" : 'auth_nsdc',
        "url" : 'https://github.com/saylordotorg/moodle-auth_nsdc.git',
        "branch" : 'master',
        "dest" : 'auth/nsdc'
    ],
    [
        "name" : 'block_quickmail',
        "url" : 'https://github.com/lsuits/lsu-block_quickmail.git',
        "branch" : 'master',
        "dest" : 'blocks/quickmail'
    ],
    [
      "name" : 'block_advnotifications',
      "url" : 'https://github.com/learningworks/moodle-block_advnotifications.git',
      "branch" : 'master',
      "dest" : 'blocks/advnotifications'
    ],
    [
        "name" : 'enrol_programs',
        "url" : 'https://github.com/open-lms-open-source/moodle-enrol_programs.git',
        "branch" : 'MOODLE_311_STABLE',
        "dest" : 'enrol/programs'
    ],
    [
        "name" : 'block_myprograms',
        "url" : 'https://github.com/open-lms-open-source/moodle-block_myprograms.git',
        "branch" : 'MOODLE_311_STABLE',
        "dest" : 'blocks/myprograms'
    ],
    [
        "name" : 'local_openlms',
        "url" : 'https://github.com/open-lms-open-source/moodle-local_openlms.git',
        "branch" : 'MOODLE_311_STABLE',
        "dest" : 'local/openlms'
    ]
]

def StashMoodle(moodle_version) {
    node {
        deleteDir()
            try {
                git([url: 'https://github.com/moodle/moodle.git', branch: "${moodle_version}"])
            }
            catch(err) {
                    echo "Unable to retrieve Moodle: ${err}"
            }
        // Remove the Moodle .git folder.
        sh "rm -r .git"
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
                                    depth: 1,
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
        sh 'rm config.php'

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
}
catch (err) {
    echo "Caught: ${err}"
    throw err
}

return // End the pipeline for testing purposes
