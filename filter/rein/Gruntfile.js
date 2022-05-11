//
// Setup instructions
// Install node
// sudo apt-get install node-legacy  // Ubuntu
// sudo apt-get install -g npm
// Install grunt-cli globally
// npm install -g grunt-cli // I needed sudo to do this.
// cd [dir of relevant plugin]
// npm install --save-dev grunt grunt-exec grunt-contrib-jshint grunt-contrib-uglify
//
'use strict';
module.exports = function(grunt) {
    // Import modules.
    var path = require('path');

    // PHP strings for exec task.
    var moodleroot = path.dirname(path.dirname(__dirname)), // jshint ignore:line
    configfile = '',
    decachephp = '',
        dirrootopt = grunt.option('dirroot') || process.env.MOODLE_DIR || ''; // jshint ignore:line

    // Allow user to explicitly define Moodle root dir.
    if ('' !== dirrootopt) {
        moodleroot = path.resolve(dirrootopt);
    }

    var PWD = process.cwd(); // jshint ignore:line
    configfile = path.join(moodleroot, 'config.php');

    decachephp += 'define(\'CLI_SCRIPT\', true);';
    decachephp += 'require(\'' + configfile + '\');';
    decachephp += 'theme_reset_all_caches();';

    grunt.initConfig({
        exec: {
            // decache: {
            //     cmd: 'php -r "' + decachephp + '"',
            //     callback: function(error) {
            //         // The exec will output error messages.
            //         // Just add one to confirm success.
            //         if (!error) {
            //             grunt.log.writeln("Moodle theme cache reset.");
            //         }
            //     }
            // }
        },
        jshint: {
            options: {jshintrc: moodleroot + '/.jshintrc'},
            files: ['**/amd/src/*.js']
        },
        uglify: {
            dynamic_mappings: {
                files: grunt.file.expandMapping(
                    ['**/src/*.js', '!**/node_modules/**'],
                    '',
                    {
                        cwd: PWD,
                        rename: function(destBase, destPath) {
                            destPath = destPath.replace('src', 'build');
                            destPath = destPath.replace('.js', '.min.js');
                            destPath = path.resolve(PWD, destPath);
                            return destPath;
                        }
                    }
                )
            }
        },
    watch: {
      js: {
        files: '<%= uglify.build.src %>',
        tasks: ['uglify']
      }
    }
});
    // Will want to add a LESS task here if we switch to LESS.
    // Load contrib tasks.
    grunt.loadNpmTasks("grunt-exec");

    // Load core tasks.
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');

    // Register tasks.
    grunt.registerTask("default", ["amd"]);
    // grunt.registerTask("decache", ["exec:decache"]);

    // grunt.registerTask("amd", ["jshint", "uglify", "decache"]);
    grunt.registerTask("amd", ["jshint", "uglify"]);
};