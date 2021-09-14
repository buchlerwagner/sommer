module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        'dart-sass': {
            target: {
                options: {
                    sourceMap: true,
                },
                files: {
                    'web/public/assets/css/mimity.style.css':   'web/resources/sass/mimity.main.scss',
                    //'web/public/assets/css/ace.style.css':      'web/resources/sass/ace.main.scss',
                }
            }
        },

        cssmin: {
            options: {
                sourceMap: true,
            },
            target: {
                files: {
                    'web/public/assets/css/mimity.style.min.css': 'web/public/assets/css/mimity.style.css',
                    //'web/public/assets/css/ace.style.min.css': 'web/public/assets/css/ace.style.css',
                }
            }
        },

        concat: {
            options: {
                separator: ';'
            },
            admin: {
                src: [
                    'web/resources/src/admin-theme.js',
                    'web/resources/src/toastr.js',
                    'web/resources/src/admin-init.js',
                    'web/resources/src/tables.js'
                ],
                dest: 'web/public/assets/js/admin.js'
            },

            shop: {
                src: [
                    'web/resources/src/shop-theme.js',
                    'web/resources/src/shop-init.js',
                ],
                dest: 'web/public/assets/js/shop.js'
            },
        },

        uglify: {
            options: {
                sourceMap: true
            },
            admin: {
                files: {
                    'web/public/assets/js/admin.min.js': ['<%= concat.admin.dest %>'],
                    'web/public/assets/js/dictionary.min.js': ['web/resources/src/dictionary.js']
                }
            },
            shop: {
                files: {
                    'web/public/assets/js/shop.min.js': ['<%= concat.shop.dest %>'],
                }
            },
        },

        svgstore: {
            options: {
                prefix : 'svg-', // This will prefix each ID
                svg: {
                    // will be added as attributes to the resulting SVG
                    xmlns: 'http://www.w3.org/2000/svg'
                }
            },
            default : {
                files: {
                    'web/public/images/sprite.svg': ['web/resources/svg/*.svg']
                }
            }
        },

        watch: {
            css: {
                files: 'web/resources/sass/**/*.scss',
                tasks: ['dart-sass', 'cssmin']
            },
            js: {
                files: 'web/resources/src/**/*.js',
                tasks: ['concat', 'uglify']
            }
        }

    });

    grunt.loadNpmTasks('grunt-dart-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-concat-css');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-svgstore');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('watch', ['watch']);
    grunt.registerTask('svg', ['svgstore']);
    grunt.registerTask('css', ['dart-sass', 'cssmin']);
    grunt.registerTask('js', ['concat', 'uglify']);

    grunt.registerTask('build', ['dart-sass', 'cssmin', 'uglify']);
    grunt.registerTask('default', ['dart-sass', 'cssmin', 'uglify', 'svgstore']);

};