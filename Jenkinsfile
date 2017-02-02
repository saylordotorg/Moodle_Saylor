#!/usr/bin/env groovy

/* Comments */


//TODO: then build parallel array thingy, then unstash
// println(plugins[0].get("dest")) plugins.size()
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
        "branch" : 'MOODLE_32_STABLE',
        "dest" : 'mod/journal'
    ]

]

def buildParallelPlugins(plugins) {
    def ParallelPlugins = []
    for (int i = 0; i < plugins.size(); i++) {
        def integer = i
        ParallelPlugins[integer] = [
            plugins[integer].get("name") : (
                node {
                    git([url: plugins[integer].get("url"), branch: plugins[integer].get("branch")])
                    stash([name: plugins[integer].get("name")])
                }
            )
        ]
    }

    return ParallelPlugins
}

try {
    stage('Stash Repos') {

        def pluginJobs = buildParallelPlugins(plugins)

        parallel pluginJobs
        // parallel (
        //     "moodle" : {
        //         node {
        //             git url: 'https://github.com/moodle/moodle.git'
        //             stash name: 'moodle'
        //         }
        //     },
        //     "theme" : {
        //         node {
        //             git url: 'https://github.com/saylordotorg/moodle-theme_saylor.git', branch: env.BRANCH_NAME
        //             stash name: 'theme_saylor'
        //         }
        //     }
        // )
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