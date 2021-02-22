'use strict';

const gulp = require('gulp');
const concat = require('gulp-concat');

gulp.task('scripts', function(done) {
    gulp.src('vendor/projectmirador/mirador-integration/webpack/dist/*.js')
        .pipe(concat('mirador-pack.js'))
        .pipe(gulp.dest('asset/vendor/mirador/'));
    done();
});

gulp.task('default', gulp.series('scripts'));

gulp.task('scripts', gulp.task('scripts'));
