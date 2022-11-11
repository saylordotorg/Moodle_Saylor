// Requiring gulp variable.
var gulp = require('gulp');

// Sass/CSS stuff.
var sass = require('gulp-sass');
var cssbeautify = require ('gulp-cssbeautify');
var concat = require('gulp-concat');
var prefix = require('gulp-autoprefixer');
var minifycss = require('gulp-minify-css');
const exec = require('gulp-exec');
const notify = require("gulp-notify");

const PRODUCTION = process.argv.includes('-production');

// JS stuff.
var minify = require('gulp-minify');


var sassOptions = {
    includePaths: ['./sass'],
    outputStyle: PRODUCTION == true ? 'compressed' : false
};

// Compile all your Sass.
gulp.task('sass', function (){
    return gulp.src(['./scss/*.scss'])
    .pipe(sass(sassOptions))
    .pipe(prefix(
        "last 1 version", "> 1%", "ie 8", "ie 7"
        ))
    .pipe(concat('styles.css'))
    .pipe(gulp.dest('./'))
});


var minifyOptions = {
    ext: {
        min: '.js'
    }
};
minifyOptions.mangle = PRODUCTION;
minifyOptions.compress = PRODUCTION;
minifyOptions.noSource = PRODUCTION;
if (PRODUCTION == false) {
    minifyOptions.preserveComments = 'all';
}

gulp.task('compress', function() {
    return gulp.src('./amd/src/*.js')
    .pipe(minify(minifyOptions))
    .pipe(gulp.dest('./amd/build'));
    done();
});

gulp.task('purge', function(done) {
    return gulp.src('.')
    .pipe(shell.task('php ../../admin/cli/purge_caches.php'))
    .pipe(notify('Purged js cache.'))
    .pip(gulp.dest('.'));
});

const moodlepath = '/var/www/html/m37dev/';

gulp.task('purge', function() {
    return gulp.src('../../admin/cli/')
    .pipe(exec('php ../../admin/cli/purge_caches.php'))
    .pipe(notify('Purged caches.'));
});

gulp.task('watch', function(done) {
    gulp.watch('./amd/src/*.js', gulp.series('compress', 'purge'));
    gulp.watch('./scss/*.scss', gulp.series('sass', 'purge'));
    gulp.watch('./lang/**/*', gulp.series('purge'));
    gulp.watch('./templates/*.mustache', gulp.series('purge'));
    done();
});

gulp.task('default', gulp.series('watch', 'compress', 'sass', 'purge'));
