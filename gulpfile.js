'use strict';

const gulp = require('gulp');
const concat = require('gulp-concat');
const spawn = require('child_process').spawn;

var exec = require('child_process').exec;

function execCommand(command, args, cb) {
    var cmd = spawn(command, args, {stdio: 'inherit'});
    cmd.on('close', function (code) {
        console.log(`Child process exited with code ${code}`);
        cb(code);
    });
};

gulp.task('npm-install', function (cb) {
    return execCommand('npm', ['--force', '--prefix', './vendor/projectmirador/mirador-integration', 'install'], cb);
});

gulp.task('vanilla', gulp.series(
    function () {
        return gulp.src([
            'vendor/projectmirador/mirador-integration/node_modules/mirador/dist/mirador.min.js',
            'vendor/projectmirador/mirador-integration/node_modules/mirador/dist/mirador.min.js.LICENSE.txt',
        ])
        .pipe(gulp.dest('asset/vendor/mirador'));
    }
));

gulp.task('bundles', gulp.series(
    function (cb) {
        return execCommand('npm', ['--prefix', './vendor/projectmirador/mirador-integration', 'run', 'webpack'], cb);
    },
    function () {
        return gulp.src([
            'vendor/projectmirador/mirador-integration/webpack/dist/*.js',
        ])
        .pipe(gulp.dest('asset/vendor/mirador'));
    }
));

/**
 * The branch iiifv3 cannot compile and the official media-type "application/alto+xml"
 * is missing. So to avoid a fork, git clone it and rebase iiifv3 on node-16 first.
 *
 * There may be a warning on missing dependencies, but don't add them to keep
 * build files small (don't add material-ui core and icons).
 *
 * The build should not be done inside node_modules, so use /tmp. It may take
 * some minutes.
 *
 * This fix is no more needed: the repository was forked.
 */
/*
gulp.task('build-textoverlay', gulp.series(
    function (cb) {
        exec('cd /tmp && rm -rf mirador-textoverlay && git clone https://github.com/dbmdz/mirador-textoverlay && cd mirador-textoverlay && git checkout iiifv3 && git rebase main && npm install && npx nwb build-react-component', function (err, stdout, stderr) {
            console.log(stdout);
            console.log(stderr);
            cb(err);
        });
    },
    function (cb) {
        exec('rm -rf vendor/projectmirador/mirador-integration/node_modules/mirador-textoverlay && mv /tmp/mirador-textoverlay vendor/projectmirador/mirador-integration/node_modules/', function (err, stdout, stderr) {
            console.log(stdout);
            console.log(stderr);
            cb(err);
        });
    }
));
*/

gulp.task('install', gulp.series('npm-install', 'build-textoverlay', 'vanilla', 'bundles'));

gulp.task('update', gulp.series('install'));

gulp.task('init-vanilla', gulp.series('npm-install', 'vanilla'));

//gulp.task('init-bundles', gulp.series('npm-install', 'build-textoverlay', 'bundles'));
gulp.task('init-bundles', gulp.series('npm-install', 'bundles'));

gulp.task('default', gulp.series('install'));
