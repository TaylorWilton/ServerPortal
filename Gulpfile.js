var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var cssnano = require('gulp-cssnano');
var pixrem = require('gulp-pixrem');
var postcss = require('gulp-postcss');
var flexbugs = require('postcss-flexbugs-fixes');
var objectfitimages = require('postcss-object-fit-images');
var livereload = require('gulp-livereload');
var concat = require('gulp-concat');

gulp.task('sass', function () {

    var processors = [
        flexbugs(),
        objectfitimages()
    ];

    return gulp.src("./build/styles/main.scss")
        .pipe(sass().on('error', sass.logError))
        .pipe(pixrem())
        .pipe(cssnano())
        .pipe(postcss(processors))
        .pipe(autoprefixer())
        .pipe(gulp.dest('./assets/'))
        .pipe(livereload())
});

gulp.task('html', function () {
    livereload();
});

gulp.task('script-build', function () {
    return gulp.src('./build/js/*.js')
        .pipe(concat('all.js'))
        .pipe(gulp.dest('./assets'))
});


gulp.task('watch', function () {
    livereload.listen();
    gulp.watch('./build/**/**/*.scss', ['sass']);
});

gulp.task('default', ['watch']);