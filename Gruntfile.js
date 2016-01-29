/* jshint node:true */
module.exports = function(grunt) {
	var SOURCE_DIR = '',
		BUILD_DIR = 'build/',
		CURRENT_TIME = new Date(),
		CURRENT_YEAR = CURRENT_TIME.getFullYear(),

		EAS_JS = [
			'js/*.js'
		],

		EAS_EXCLUDED_JS = [
			'!js/*.min.js'
		],

		EAS_EXCLUDED_MISC = [
			'!**/.idea/**',
			'!**/bin/**',
			'!**/build/**',
			'!**/coverage/**',
			'!**/node_modules/**',
			'!**/tests/**',
			'!Gruntfile.js*',
			'!package.json*',
			'!phpunit.xml*',
			'!.{editorconfig,gitignore,jshintrc,travis.yml,DS_Store}'
		];

	// Load tasks.
	require( 'matchdep' ).filterDev([ 'grunt-*', '!grunt-legacy-util' ]).forEach( grunt.loadNpmTasks );

	// Load legacy utils
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		checktextdomain: {
			options: {
				text_domain: 'edit-author-slug',
				correct_domain: false,
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'_n:1,2,4d',
					'_ex:1,2c,3d',
					'_nx:1,2,4c,5d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: [ '**/*.php' ].concat( EAS_EXCLUDED_MISC ),
				expand: true
			}
		},
		clean: {
			all: [ BUILD_DIR ],
			dynamic: {
				cwd: BUILD_DIR,
				dot: true,
				expand: true,
				src: []
			}
		},
		copy: {
			files: {
				files: [
					{
						cwd: '',
						dest: 'build/',
						dot: true,
						expand: true,
						src: ['**', '!**/.{svn,git}/**'].concat( EAS_EXCLUDED_MISC )
					}
				]
			}
		},
		jshint: {
			options: grunt.file.readJSON( '.jshintrc' ),
			grunt: {
				src: ['Gruntfile.js']
			},
			core: {
				expand: true,
				cwd: SOURCE_DIR,
				src: [EAS_JS].concat( EAS_EXCLUDED_JS ),

				/**
				 * Limit JSHint's run to a single specified file:
				 *
				 * grunt jshint:core --file=filename.js
				 *
				 * Optionally, include the file path:
				 *
				 * grunt jshint:core --file=path/to/filename.js
				 *
				 * @param {String} filepath
				 * @returns {Bool}
				 */
				filter: function( filepath ) {
					var index, file = grunt.option( 'file' );

					// Don't filter when no target file is specified
					if ( ! file ) {
						return true;
					}

					// Normalise filepath for Windows
					filepath = filepath.replace( /\\/g, '/' );
					index = filepath.lastIndexOf( '/' + file );

					// Match only the filename passed from cli
					if ( filepath === file || ( -1 !== index && index === filepath.length - ( file.length + 1 ) ) ) {
						return true;
					}

					return false;
				}
			}
		},
		jsvalidate:{
			options:{
				globals: {},
				esprimaOptions:{},
				verbose: false
			},
			build: {
				files: {
					src: [BUILD_DIR + EAS_JS]
				}
			},
			src: {
				files: {
					src: [SOURCE_DIR + EAS_JS]
				}
			}
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'edit-author-slug.php',
					potComments: 'Copyright (C) ' + CURRENT_YEAR + ' Brandon Allen\nThis file is distributed under the same license as the Edit Author Slug package.\nSubmit translations to https://translate.wordpress.org/projects/wp-plugins/edit-author-slug.',
					potFilename: 'edit-author-slug.pot',
					potHeaders: {
						poedit: true,
						'report-msgid-bugs-to': 'https://github.com/thebrandonallen/edit-author-slug/issues',
						'last-translator': 'BRANDON ALLEN <plugins@brandonallen.me>',
						'language-team': 'ENGLISH <plugins@brandonallen.me>'
					},
					processPot: function( pot ) {
						var translation, // Exclude meta data from pot.
							excluded_meta = [
								'Plugin Name of the plugin/theme',
								'Plugin URI of the plugin/theme',
								'Author of the plugin/theme',
								'Author URI of the plugin/theme'
								];
									for ( translation in pot.translations[''] ) {
										if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
											if ( excluded_meta.indexOf( pot.translations[''][ translation ].comments.extracted ) >= 0 ) {
												console.log( 'Excluded meta: ' + pot.translations[''][ translation ].comments.extracted );
													delete pot.translations[''][ translation ];
												}
											}
										}
						return pot;
					},
					type: 'wp-plugin'
				}
			}
		},
		phpunit: {
			'default': {
				cmd: 'phpunit',
				args: ['-c', 'phpunit.xml.dist']
			},
			multisite: {
				cmd: 'phpunit',
				args: ['-c', 'tests/phpunit/multisite.xml']
			}
		},
		'string-replace': {
			dev: {
				files: {
					'edit-author-slug.php': 'edit-author-slug.php'
				},
				options: {
					replacements: [{
						pattern: /(\$this->version.*)'(.*)';/gm, // For plugin version variable
						replacement: '$1\'<%= pkg.version %>\';'
					},
					{
						pattern: /(\* Version:\s*)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					}]
				}
			},
			build: {
				files: {
					'edit-author-slug.php': 'edit-author-slug.php',
					'readme.md': 'readme.md',
					'readme.txt': 'readme.txt'
				},
				options: {
					replacements: [{
						pattern: /(\$this->version.*)'(.*)';/gm, // For plugin version variable
						replacement: '$1\'<%= pkg.version %>\';'
					},
					{
						pattern: /(\* Version:\s*)(.*)$/gm, // For plugin header
						replacement: '$1<%= pkg.version %>'
					},
					{
						pattern: /(Stable tag:[\*\ ]*)(.*\S)/gim, // For readme.*
						replacement: '$1<%= pkg.version %>'
					}]
				}
			},
			readme: {
				files: {
					'readme.md': 'readme.md'
				},
				options: {
					replacements: [{
						pattern: /# Edit Author Slug #/gim,
						replacement: '# Edit Author Slug [![Build Status](https://travis-ci.org/thebrandonallen/edit-author-slug.svg?branch=master)](https://travis-ci.org/thebrandonallen/edit-author-slug) #'
					}]
				}
			}
		},
		uglify: {
			core: {
				cwd: SOURCE_DIR,
				dest: SOURCE_DIR,
				extDot: 'last',
				expand: true,
				ext: '.min.js',
				src: [EAS_JS].concat( EAS_EXCLUDED_JS )
			},
			options: {
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
				'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
				'https://github.com/thebrandonallen/edit-author-slug/ */\n'
			}
		},
		watch: {
			js: {
				files: ['Gruntfile.js'],
				tasks: ['jshint']
			}
		},
		wp_readme_to_markdown: {
			core: {
				files: {
					'readme.md': 'readme.txt'
				}
			}
		}
	});

	// Build tasks.
	grunt.registerTask( 'readme', [ 'wp_readme_to_markdown', 'string-replace:readme' ] );
	grunt.registerTask( 'src',    [ 'jsvalidate:src', 'jshint:core' ] );
	grunt.registerTask( 'build',  [ 'clean:all', 'checktextdomain', 'string-replace:build', 'readme', 'uglify', 'makepot', 'copy:files', 'jsvalidate:build' ] );

	// PHPUnit test task.
	grunt.registerMultiTask( 'phpunit', 'Runs PHPUnit tests, including the multisite tests.', function() {
		grunt.util.spawn( {
			cmd: this.data.cmd,
			args: this.data.args,
			opts: { stdio: 'inherit' }
		}, this.async() );
	} );

	// Register the default tasks.
	grunt.registerTask('default', ['watch']);

};
