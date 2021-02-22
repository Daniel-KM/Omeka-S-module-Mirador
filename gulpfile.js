'use strict';

const del = require('del');
const gulp = require('gulp');
const gulpif = require('gulp-if');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');

const bundle = [
    {
        'source': 'node_modules/mirador/dist/**',
        'dest': 'asset/vendor/mirador',
    },
    {
        'source': 'node_modules/mirador-annotations/umd/mirador-annotations.min.js',
        'dest': 'asset/vendor/mirador-plugins/annotations',
    },
    {
        'source': 'node_modules/mirador-dl-plugin/umd/mirador-dl-plugin.min.js',
        'dest': 'asset/vendor/mirador-plugins/dl',
    },
    {
        'source': 'node_modules/mirador-image-tools/umd/mirador-image-tools.min.js',
        'dest': 'asset/vendor/mirador-plugins/image-tools',
    },
    {
        'source': 'node_modules/mirador-ruler-plugin/dist/**',
        'dest': 'asset/vendor/mirador-plugins/ruler',
    },
    {
        'source': 'node_modules/mirador-share-plugin/umd/mirador-share-plugin.min.js',
        'dest': 'asset/vendor/mirador-plugins/share',
    },
    {
        'source': 'node_modules/mirador-textoverlay/umd/mirador-textoverlay.min.js',
        'dest': 'asset/vendor/mirador-plugins/textoverlay',
    },
];

gulp.task('clean', function(done) {
    bundle.forEach(function (module) {
        return del.sync(module.dest);
    });
    done();
});

gulp.task('sync', function (done) {
    bundle.forEach(function (module) {
        gulp.src(module.source)
            .pipe(gulpif(module.rename, rename({suffix:'.min'})))
            .pipe(gulpif(module.uglify, uglify()))
            .pipe(gulp.dest(module.dest));
    });
    done();
});

gulp.task('default', gulp.series('clean', 'sync'));

gulp.task('install', gulp.task('default'));

gulp.task('update', gulp.task('default'));
