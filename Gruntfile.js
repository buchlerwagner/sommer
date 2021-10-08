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
                    'web/public/assets/css/bellaria.style.css':  'web/resources/sass/bellaria.main.scss',
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
                    'web/public/assets/css/bellaria.style.min.css': 'web/public/assets/css/bellaria.style.css',
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
                    'web/resources/src/shop-init.js',
                    'web/resources/src/shop-tables.js'
                ],
                dest: 'web/public/assets/js/shop.js'
            },

            bellaria: {
                src: [
                    'web/public/vendor/revolution/js/jquery.themepunch.revolution.js',
                    'web/public/vendor/revolution/js/jquery.themepunch.tools.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.actions.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.carousel.js',
                    'web/public/vendor/revolution/js/extensions/revolution.extension.layeranimation.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.kenburn.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.migration.js',
                    'web/public/vendor/revolution/js/extensions/revolution.extension.navigation.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.parallax.js',
                    'web/public/vendor/revolution/js/extensions/revolution.extension.slideanims.js',
                    //'web/public/vendor/revolution/js/extensions/revolution.extension.video.js',

                    'web/public/vendor/select2/js/select2.js',
                    'web/public/vendor/cookiebar/jquery.cookiebar.js',
                    'web/public/vendor/fancybox/jquery.fancybox.js',

                    'web/resources/src/bellaria/main-slider-script.js',
                    'web/resources/src/bellaria/owl.js',
                    'web/resources/src/bellaria/wow.js',
                    'web/resources/src/bellaria/appear.js',
                    'web/resources/src/bellaria/sticky_sidebar.min.js',
                    'web/resources/src/bellaria/script.js'
                ],
                dest: 'web/public/assets/js/bellaria.js'

            }
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
            bellaria: {
                files: {
                    'web/public/assets/js/bellaria.min.js': ['<%= concat.bellaria.dest %>'],
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