/**
 * @file
 *
 * @type {Gulp}
 */

var gulp = require('gulp');
var sass = require('gulp-sass');
var svgSprite = require('gulp-svg-sprite');

gulp.task('styles', function () {
  gulp.src('scss/**/*.scss')
      .pipe(sass().on('error', sass.logError))
      .pipe(gulp.dest('./css/'))
});

// Configuration for sprite.
var config = {
  mode: {
    css: {
      dest: '.',
      sprite: '../images/sprite.svg',
      bust: true,
      prefix: ".fleur-%s",
      render: {
        scss: {
          dest:'icons/_fleur_icons.scss',
          template: 'assets/sass/templates/_fleur_icon_sprite.scss',
        },
      },
    },
  }
};

gulp.task('sprite', function () {
  gulp.src('assets/icons/*.svg')
      .pipe(svgSprite(config))
      .pipe(gulp.dest('scss'));
});

// Watch task.
gulp.task('default',function () {
  gulp.watch('scss/**/*.scss',['styles']);
  gulp.watch('assets/icons/*.svg',['sprite']);
});
