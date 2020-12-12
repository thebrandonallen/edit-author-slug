module.exports = function ( grunt ) {
	const BUILD_DIR = 'build/',
		EAS_EXCLUDED_MISC = [
			'!**/assets/**',
			'!**/bin/**',
			'!**/build/**',
			'!**/coverage/**',
			'!**/node_modules/**',
			'!**/tests/**',
			'!**/vendor/**',
			'!composer.*',
			'!Gruntfile.js*',
			'!package.json*',
			'!package-lock.json*',
			'!phpcs.xml*',
			'!phpunit.xml*',
			'!.*',
		];

	// Load tasks.
	require( 'matchdep' )
		.filterDev( [ 'grunt-*', '!grunt-legacy-util' ] )
		.forEach( grunt.loadNpmTasks );

	// Load legacy utils
	grunt.util = require( 'grunt-legacy-util' );

	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
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
					'_nx_noop:1,2,3c,4d',
				],
			},
			files: {
				src: [ '**/*.php' ].concat( EAS_EXCLUDED_MISC ),
				expand: true,
			},
		},
		clean: {
			all: [ BUILD_DIR ],
			dynamic: {
				cwd: BUILD_DIR,
				dot: true,
				expand: true,
				src: [],
			},
		},
		concat: {
			options: {
				stripBanners: true,
				banner:
					'/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
					'https://github.com/thebrandonallen/edit-author-slug/ */\n',
			},
			dist: {
				src: [ 'js/edit-author-slug.min.js' ],
				dest: 'js/edit-author-slug.min.js',
			},
		},
		copy: {
			files: {
				files: [
					{
						cwd: '',
						dest: 'build/',
						dot: true,
						expand: true,
						src: [ '**', '!**/.{svn,git}/**' ].concat(
							EAS_EXCLUDED_MISC
						),
					},
				],
			},
		},
		makepot: {
			target: {
				options: {
					domainPath: '/languages',
					mainFile: 'edit-author-slug.php',
					potComments:
						'Copyright (C) 2009-<%= grunt.template.today("UTC:yyyy") %> Brandon Allen\nThis file is distributed under the same license as the Edit Author Slug package.\nSubmit translations to https://translate.wordpress.org/projects/wp-plugins/edit-author-slug.',
					potFilename: 'edit-author-slug.pot',
					potHeaders: {
						poedit: true,
						'report-msgid-bugs-to':
							'https://github.com/thebrandonallen/edit-author-slug/issues',
						'last-translator':
							'BRANDON ALLEN <plugins@brandonallen.me>',
						'language-team': 'ENGLISH <plugins@brandonallen.me>',
					},
					processPot( pot ) {
						let translation; // Exclude meta data from pot.
						const excludedMeta = [
							'Plugin Name of the plugin/theme',
							'Plugin URI of the plugin/theme',
							'Author of the plugin/theme',
							'Author URI of the plugin/theme',
						];

						for ( translation in pot.translations[ '' ] ) {
							if (
								'undefined' !==
								typeof pot.translations[ '' ][ translation ]
									.comments.extracted
							) {
								if (
									0 <=
									excludedMeta.indexOf(
										pot.translations[ '' ][ translation ]
											.comments.extracted
									)
								) {
									// eslint-disable-next-line
									console.log(
										'Excluded meta: ' +
											pot.translations[ '' ][
												translation
											].comments.extracted
									);
									delete pot.translations[ '' ][
										translation
									];
								}
							}
						}

						return pot;
					},
					type: 'wp-plugin',
				},
			},
		},
		phpunit: {
			default: {
				cmd: 'phpunit',
				args: [ '-c', 'phpunit.xml.dist' ],
			},
			codecoverage: {
				cmd: 'phpunit',
				args: [
					'-c',
					'phpunit.xml.dist',
					'--coverage-clover=coverage.clover',
				],
			},
		},
		'string-replace': {
			dev: {
				files: {
					'edit-author-slug.php': 'edit-author-slug.php',
				},
				options: {
					replacements: [
						{
							pattern: /(const\sVERSION.*)'(.*)';/gm, // For plugin version variable
							replacement: "$1'<%= pkg.version %>';",
						},
						{
							pattern: /(\*\sVersion:\s+).*/gm, // For plugin header
							replacement: '$1<%= pkg.version %>',
						},
						{
							pattern: /(\*\s@version\s+).*/gm, // For plugin header
							replacement: '$1<%= pkg.version %>',
						},
					],
				},
			},
			build: {
				files: {
					'CHANGELOG.md': 'CHANGELOG.md',
					'edit-author-slug.php': 'edit-author-slug.php',
					'includes/classes/class-edit-author-slug.php':
						'includes/classes/class-edit-author-slug.php',
					'readme.txt': 'readme.txt',
				},
				options: {
					replacements: [
						{
							pattern: /(const\sVERSION.*)'(.*)';/gm, // For plugin version variable
							replacement: "$1'<%= pkg.version %>';",
						},
						{
							pattern: /(\*\sVersion:\s+).*/gm, // For plugin header
							replacement: '$1<%= pkg.version %>',
						},
						{
							pattern: /(\*\s@version\s+).*/gm, // For plugin header
							replacement: '$1<%= pkg.version %>',
						},
						{
							pattern: /(Stable\stag:\s+).*/gm, // For readme.txt
							replacement: '$1<%= pkg.version %>',
						},
						{
							pattern: /(Copyright\s\(C\)\s2009-)[0-9]{4}(.*)/gm, // For Copyright.
							replacement:
								'$1<%= grunt.template.today("UTC:yyyy") %>$2',
						},
						{
							pattern: /(\*\sRelease\sdate:\s)(TBD|TBA|TDB)$/gm,
							replacement:
								'$1<%= grunt.template.today("yyyy-mm-dd") %>',
						},
						{
							pattern: /^(##\s.*\s-\s)(TBD|TBA|TDB)$/gm,
							replacement:
								'$1<%= grunt.template.today("yyyy-mm-dd") %>',
						},
					],
				},
			},
			readme: {
				files: {
					'README.md': 'README.md',
				},
				options: {
					replacements: [
						{
							pattern: /# Edit Author Slug #/gim,
							replacement:
								'# Edit Author Slug [![Build Status](https://travis-ci.org/thebrandonallen/edit-author-slug.svg?branch=master)](https://travis-ci.org/thebrandonallen/edit-author-slug) #',
						},
					],
				},
			},
		},
		watch: {
			js: {
				files: [ 'Gruntfile.js' ],
				tasks: [ 'jshint' ],
			},
		},
		wp_readme_to_markdown: {
			core: {
				files: {
					'README.md': 'readme.txt',
				},
			},
		},
	} );

	// Build tasks.
	grunt.registerTask( 'readme', [
		'wp_readme_to_markdown',
		'string-replace:readme',
	] );
	grunt.registerTask(
		'eslint:fix',
		'Runs ESLint on JavaScript files.',
		function () {
			grunt.util.spawn(
				{
					cmd: 'npm',
					args: [ 'run', 'eslint:fix', 'js/edit-author-slug.js' ],
					opts: { stdio: 'inherit' },
				},
				this.async()
			);
		}
	);
	grunt.registerTask(
		'js:build',
		'Runs Terser on JavaScript files.',
		function () {
			const banner = grunt.template.process(
				'/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("UTC:yyyy-mm-dd h:MM:ss TT Z") %> - ' +
					'https://github.com/thebrandonallen/edit-author-slug/ */'
			);
			grunt.util.spawn(
				{
					cmd: 'npm',
					args: [
						'run',
						'build:js',
						'--',
						'js/edit-author-slug.js',
						'--copmress',
						'--mangle',
						'--output',
						'js/edit-author-slug.min.js',
						'--format',
						`preamble='${ banner }'`,
					],
					opts: { stdio: 'inherit' },
				},
				this.async()
			);
		}
	);
	grunt.registerTask(
		'i18n:build',
		'Runs the WP-CLI i18n command to generate the pot file.',
		function () {
			const banner = grunt.template.process(
				'Copyright (C) 2009-<%= grunt.template.today("UTC:yyyy") %> Brandon Allen\n' +
					'This file is distributed under the same license as the Edit Author Slug package.\n' +
					'Submit translations to https://translate.wordpress.org/projects/wp-plugins/edit-author-slug.'
			);
			const keywords = [
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
				'_nx_noop:1,2,3c,4d',
			];
			const headers = {
				'Report-Msgid-Bugs-To':
					'https://github.com/thebrandonallen/edit-author-slug/issues',
				'X-Poedit-KeywordsList': `${ keywords.join( ';' ) }`,
			};
			grunt.util.spawn(
				{
					cmd: 'wp',
					args: [
						'i18n',
						'make-pot',
						'.',
						'languages/edit-author-slug.pot',
						`--headers=${ JSON.stringify( headers ) }`,
						`--file-comment=${ banner }`,
						'--exclude=build',
					],
					opts: { stdio: 'inherit' },
				},
				this.async()
			);
		}
	);
	grunt.registerTask( 'build', [
		'clean:all',
		'checktextdomain',
		'string-replace:build',
		'readme',
		'js:build',
		'i18n:build',
		'copy:files',
	] );

	// PHPUnit test task.
	grunt.registerMultiTask(
		'phpunit',
		'Runs PHPUnit tests, including the multisite tests.',
		function () {
			grunt.util.spawn(
				{
					cmd: this.data.cmd,
					args: this.data.args,
					opts: { stdio: 'inherit' },
				},
				this.async()
			);
		}
	);

	// Travis CI Tasks.
	grunt.registerTask( 'travis:phpunit', [ 'phpunit:default' ] );
	grunt.registerTask(
		'travis:codecoverage',
		'Runs PHPUnit tasks with code-coverage generation.',
		[ 'phpunit:codecoverage' ]
	);

	// Register the default tasks.
	grunt.registerTask( 'default', [ 'watch' ] );
};
