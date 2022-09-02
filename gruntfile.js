module.exports = function (grunt) {
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.initConfig({
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'truelayer-for-woocommerce.php',
					potFilename: 'truelayer-for-woocommerce.pot',
					processPot: function (pot, options) {
						// add header options
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		// minify css
		cssmin: {
			target: {
				files: [{
					expand: true,
					cwd: './assets/css',
					src: ['*.css', '!*.min.css'],
					dest: './assets/css',
					ext: '.min.css'
				}]
			}
		}
	});
}