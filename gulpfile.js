'use strict';

const gulp = require('gulp');
const concat = require('gulp-concat');
const spawn = require('child_process').spawn;

function execCommand(command, args, cb) {
    var cmd = spawn(command, args, {stdio: 'inherit'});
    cmd.on('close', function (code) {
        console.log(`Child process exited with code ${code}`);
        cb(code);
    });
};

gulp.task('npm-install', function (cb) {
    return execCommand('npm', ['--prefix', './vendor/projectmirador/mirador-integration', 'install'], cb);
});

gulp.task('vanilla', gulp.series(
    function () {
        return gulp.src([
                'vendor/projectmirador/mirador-integration/node_modules/mirador/dist/mirador.min.js',
                'vendor/projectmirador/mirador-integration/node_modules/mirador/dist/mirador.min.js.LICENSE.txt',
            ])
            .pipe(gulp.dest('asset/vendor/mirador'));
    },
));

gulp.task('bundle-full', gulp.series(
    function (cb) {
        return execCommand('npm', ['--prefix', './vendor/projectmirador/mirador-integration', 'run', 'webpack'], cb);
    },
    function () {
        return gulp.src('vendor/projectmirador/mirador-integration/webpack/dist/*.js')
            .pipe(concat('mirador-bundle.min.js'))
            .pipe(gulp.dest('asset/vendor/mirador/'));
    },
));

gulp.task('bundle-usual', gulp.series(
    function (cb) {
        return execCommand('npm', ['--prefix', './vendor/projectmirador/mirador-integration', 'run', 'webpack-usual'], cb);
    },
    function () {
        return gulp.src('vendor/projectmirador/mirador-integration/webpack/dist/*.js')
            .pipe(concat('mirador-pack.min.js'))
            .pipe(gulp.dest('asset/vendor/mirador/'));
    },
));

gulp.task('install', gulp.series('npm-install', 'vanilla', 'bundle-full', 'bundle-usual'));

gulp.task('update', gulp.series('install'));

gulp.task('init-vanilla', gulp.series('npm-install', 'vanilla'));

gulp.task('init-bundle-full', gulp.series('npm-install', 'bundle-full'));

gulp.task('init-bundle-usual', gulp.series('npm-install', 'bundle-usual'));

gulp.task('default', gulp.series('install'));
