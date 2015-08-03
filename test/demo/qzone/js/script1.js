module.exports = function(grunt) {

    /*
     * grunt
     * for mobile v1.0
     * @simplewei 2014/05/27
     */

// load all grunt tasks
    require('load-grunt-tasks')(grunt);

    grunt.file.defaultEncoding = 'GBK';

    var src = 'src/',
        dest = 'dest/';
    grunt.initConfig({

        clean: {
            release: ['dest', '.temp']
        },

        copy: {
            html: {
                expand: true,
                cwd: src,
                src: ['**'],
                dest: dest
            }
        },
        inline: {
            options: {
                uglify: true
            },
            dist: {
                src: dest + '/inline/*.html'
            },
            dist2: {
                src: dest + '/*.html'
            }
        },
        cssmin: {
            options: {
// noAdvanced，取消CSS属性合并，规避部分浏览器不兼容background-size合并后语法
                noAdvanced: true
            }
// dist: {
// expand: true,
// cwd: src,
// src: ['**/*.css'],
// dest: dest
// }
        },

        concat: {
            options: {
                separator: '\n'
            }
        },

        uglify: {
            options: {
// sourceMap: true,
                "DEBUG": true
            }
        },

        useminPrepare: {
            top: {
                src: [src + '**/*.{shtml,html}', '!'+src+'appbox/**', '!'+src+'wallet/**'],
                options: {
                    dest: dest
                }
            },
            appbox: {
                src: [src+'appbox/**/*.{shtml,html}'],
                options: {
                    root: src+'appbox/v3',
                    dest: dest+'appbox/v3'
                }
            },
            wallet: {
                src: [src+'wallet/**/*.{shtml,html}'],
                options: {
                    root: src+'wallet/v3',
                    dest: dest+'wallet/v3'
                }
            }
        },

        rev: {
            options: {
                algorihtm: 'md5',
                length: 8,
                encoding: 'GBK'
            },
// 约定合并压缩后的文件后缀xx.min.xx
            js: {
                files: [{
                    src: [
                            dest + '/{**,!static_res}/*.min.{js,css}',
// ,png,jpg,gif
                            dest + '/static_res/**/*.{js,css,png,jpg,gif,ico}'
                    ]
                }]
            }
        },

        usemin: {
            options: {

// 资源查找的相对路径
                assetsDirs: [dest, dest+'appbox/v3', dest+'wallet/v3'],
                patterns: {
                    js: [
// [/(\.{1}(gif|jpg|png))/, 'replace image in js']
                        [/(\/[\/\w-]+\.{1}(gif|jpg|png))/g, 'replace image in js']
                    ]
                }
            },
            html: [dest + '/**/*.{html,shtml}'],
            css: [dest + '/static_res/**/*.css'],
            js: [dest + '/static_res/**/*.js']
        },

        replace: {
            dist: {
                options: {
                    usePrefix: false,
                    patterns: [{
                        json: {
                            '/static_res/': 'http://jipiao.qq.com/'
                        }
                    }]
                },
// replace设置成gbk编码，login是utf8
                files: [{
                    expand: true,
                    src: [dest + '**/*.{html,shtml,js,css}', '!**/node_modules/**', '!'+dest + 'login/**']
                }]
            }
        },

        imageoptim: {
            /* 压缩图片大小 */
            dist: {
                options: {
                    optimizationLevel: 1 //定义 PNG 图片优化水平
                },
                files: [{
                    expand: true,
                    cwd: dest,
                    src: ['**/*.{png,jpg,jpeg}'], // 优化 img 目录下所有 png/jpg/jpeg 图片
                    dest: dest // 优化后的图片保存位置，覆盖旧图片，并且不作提示
                }]
            }
        },

        requirejs: {
            options: {
                optimize: 'uglify2',
                generateSourceMaps: true,
                preserveLicenseComments: false
            },
            compile: {
                options: {
                    baseUrl: "src/tests/backbone/scripts",
                    paths: {
                        jquery: 'lib/jquery',
                        underscore: 'lib/underscore',
                        backbone: 'lib/backbone',
                        text: 'lib/text'
                    },
                    shim: {
                        'jquery': {
                            exports: '$'
                        },
                        'underscore': {
                            deps: ['jquery'],
                            exports: '_'
                        },
                        'backbone': {
                            deps: ['underscore', 'jquery'],
                            exports: 'Backbone'
                        }
                    },
                    name: 'init',
                    dir: 'v3/scripts',
                }
            }
        },

        compress: {
            prod: {
                options: {
                    archive: 'pc-<%= grunt.template.today("yyyy-mm-dd-hh-MM") %>.tar.gz'
                },
                expand: true,
                cwd: dest,
                src: ['**', '!doc/**']
            },

            act: {
                options: {
                    archive: 'pc-<%= grunt.template.today("yyyy-mm-dd-hh-MM") %>.tar.gz'
                },
                expand: true,
                cwd: src,
                src: ['act/**' ]
//'echoImages/**' , 'Cooperation/**', 'appbox/**', 'wallet/**',
            },

            static_res:{
                options: {
                    archive: 'pc-static_res-<%= grunt.template.today("yyyy-mm-dd-hh-MM") %>.tar.gz'
                },
                expand: true,
                cwd: dest,
                src: ['static_res/**' ]
            }
        },

        uglify: {
            options:{
                compress: false
            }
        }

    });

    grunt.registerTask('default', ['clean', 'copy', 'inline', 'useminPrepare',
        'concat:generated', 'cssmin', 'uglify:generated', 'rev', 'usemin']);

    grunt.registerTask('release', ['getToken', 'getPurpose', 'default', 'replace', 'compress:prod', 'e2e']);

    grunt.registerTask('releaseAct', ['compress:act']);


// 打包到测试环境目录
// 通过:传入参数
    grunt.registerTask('testCopy', function(arg) {

        var test_conf = grunt.file.readJSON('grunt_task/test.json'),
            task_list = ['copy'];

        grunt.config.set('copy', {
            'copy': test_conf.copy.target
        });

        console.log('test参数：' + JSON.stringify(grunt.config()));
        grunt.task.run('copy');

    });



    var readline = require('readline');

// 获取用户token
    grunt.registerTask('getToken', function() {
        var conf = require('./e2e/user.conf.json');
        if(conf.name){
            grunt.log.error('请在user.conf.json文件中配置账号密码');
        }
        var done = this.async();
        var token;
        var rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });

        rl.question('hi,请输入6位token密码: ', function(answer) {
            grunt.config('token', answer);
            rl.close();
// 结束 grunt async
            done();
        });
    });

// 获取用户发布目的
    grunt.registerTask('getPurpose', function() {
        var done = this.async();
        var desc;
        var rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout
        });

        rl.question('请输入发布目的: ', function(answer) {
            grunt.config('purpose', answer);
            rl.close();
// 结束 grunt async
            done();
        });

    });

// 调用webdriver进行自动化发布
    grunt.registerTask('e2e', function() {

        var done = this.async();

        grunt.util.spawn({
// grunt: true,
            cmd: 'node',
            args: ['e2e/release', grunt.config('token'), grunt.config('purpose')]
        }, function(error, result, code) {

            done();

            if (error){
                grunt.log.error(error)
            } else {
                grunt.log.oklns('success')
                grunt.log.writeln(result)
            }

        });
    });



};