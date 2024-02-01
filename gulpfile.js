var gulp      = require('gulp')
,   minifyCSS = require('gulp-minify-css')
,   debug     = require('gulp-debug')
,   less      = require('gulp-less');

gulp.task('less', function () {
    gulp.src('src/maggu-login-protect/assets/*.less')
    .pipe(debug({title:'less'}))
    .pipe(less())
    .pipe(minifyCSS())
    .pipe(gulp.dest('src/maggu-login-protect/css/'));
});

gulp.task('watch', function() {
	gulp.watch(['src/maggu-login-protect/assets/*.less'],  { interval: 1000, delay: 1000 },['less']);
})

gulp.task('build',['less']);
gulp.task('develop',  ['build','watch']);