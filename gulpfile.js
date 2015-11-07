var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('sass', function () {
	gulp.src('./src/Museomix/Resources/scss/styles.scss')
		.pipe(sass().on('error', sass.logError))
		.pipe(gulp.dest('./web/assets/css'));
});

gulp.task('sass:watch', function () {
	gulp.watch('./src/Museomix/Resources/scss/*.scss', ['sass']);
});

gulp.task('default', ['sass:watch']);
