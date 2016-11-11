const gulp = require('gulp');
const concat = require('gulp-concat');
const minify = require('gulp-minify');

gulp.task('scripts', function () {
    gulp.src([
        'bower_components/jquery/dist/jquery.js',
        'bower_components/jquery-migrate/jquery-migrate.js',
        'bower_components/jquery-ui/jquery-ui.js',
        'bower_components/jquery.cookie/jquery.cookie.js',
        'bower_components/jquery-livequery/dist/jquery.livequery.js',
        'bower_components/jPlayer/dist/jplayer/jquery.jplayer.js',

        'bower_components/angular/angular.js',
        'bower_components/angular-route/angular-route.js',
        'bower_components/angular-animate/angular-animate.js',
        'bower_components/angular-contenteditable/angular-contenteditable.js',
        'bower_components/mixpanel/mixpanel-jslib-snippet.js',
        'bower_components/angular-mixpanel/src/angular-mixpanel.js',
    ])
        .pipe(minify())
        .pipe(concat('dist.min.js'))
        .pipe(gulp.dest('public/js'))

});