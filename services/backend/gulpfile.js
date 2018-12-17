const gulp = require('gulp');
const concat = require('gulp-concat');
const minify = require('gulp-minify');

gulp.task('scripts', function () {
    gulp.src([
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/jquery-migrate/jquery-migrate.min.js',
        'bower_components/jquery-ui/jquery-ui.min.js',
        'bower_components/jquery.cookie/jquery.cookie.js',
        'bower_components/jquery-livequery/dist/jquery.livequery.min.js',
        'bower_components/jPlayer/dist/jplayer/jquery.jplayer.min.js',

        'bower_components/angular/angular.min.js',
        'bower_components/angular-route/angular-route.min.js',
        'bower_components/angular-animate/angular-animate.min.js',
        'bower_components/angular-contenteditable/angular-contenteditable.js',
        'bower_components/mixpanel/mixpanel-jslib-snippet.min.js',
        'bower_components/angular-mixpanel/dist/angular-mixpanel.min.js',
    ])
        .pipe(minify())
        .pipe(concat('dist.min.js'))
        .pipe(gulp.dest('public/js'))

});