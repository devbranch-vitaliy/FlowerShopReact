/**
 * @file
 *
 * @type {Gulp}
 */

var gulp = require('gulp');
var sass = require('gulp-sass');
var svgSprite = require('gulp-svg-sprite');
var iconfont = require('gulp-iconfont');
var iconfontCss = require('gulp-iconfont-css');
var fontName = 'FleurIcons';

// Building css style from scss.
gulp.task('styles', function () {
  gulp.src('scss/**/*.scss')
      .pipe(sass().on('error', sass.logError))
      .pipe(gulp.dest('./css/'))
});

// Creat new font from SVGs icons.
gulp.task('iconfont', function () {
  gulp.src(['assets/icons-convert/*.svg'])
      .pipe(iconfontCss({
        fontName: fontName,
        path: 'assets/sass/templates/_fleur_icon_style.scss',
        targetPath: '../../scss/icons/_fleur_icons_font.scss',
        fontPath: '#{$theme-root}/fonts/FleurIcons/',
        cssClass: 'fleur-font'
      }))
      .pipe(iconfont({
        fontName: fontName,
        formats: ['svg', 'ttf', 'eot', 'woff'],
        normalize: true,
        fontHeight: 1001
      }))
      .pipe(gulp.dest('fonts/FleurIcons'))
});

// Configuration for sprite.
var config = {
  mode: {
    css: {
      dest: '.',
      sprite: '../images/sprite.svg',
      bust: false,
      prefix: ".fleur-%s",
      mixin: 'sprite-content',
      render: {
        scss: {
          dest:'icons/_fleur_icons.scss',
          template: 'assets/sass/templates/_fleur_icon_sprite.scss',
        },
      },
    },
  }
};

// Creat sprite from SVGs icons.
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
