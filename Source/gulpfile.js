/* jshint node: true */
'use strict';

/**
 * Get arguments from commandline
 */
function getArg(key) {
	var index = process.argv.indexOf(key);
	var next = process.argv[index + 1];
	return (index < 0) ? null : (!next || '-' === next[0]) ? true : next;
}

var gulp = require('gulp');
var sass = require('gulp-sass');
var plumber = require('gulp-plumber');
var debug = getArg('--debug');

var project = {
	css: __dirname + '/..'
};

// SCSS zu css
gulp.task('css', function() {
	var config = {};
	if (debug) {
		config.sourceMap = 'inline';
		config.sourceMapEmbed = true;
	}
	if (!debug) {
		config.outputStyle = 'compressed';
	}

	gulp.src(__dirname + '/Sass/*.scss')
		.pipe(plumber())
		.pipe(sass(config))
		.pipe(gulp.dest(project.css));
});

/*********************************
 *         Watch Tasks
 *********************************/
gulp.task('watch', function() {
	gulp.watch(__dirname + '/Sass/**/*.scss', ['css'])
});
