module.exports = function (grunt) {

// load all grunt tasks
    require('load-grunt-tasks')(grunt);
    grunt.file.defaultEncoding = 'utf-8';

    var src = 'src/',
        dest = 'dest/';
    grunt.initConfig({
        // 文件夹删除
        clean: {
            release: ['dest']
        },

        // 复制src文件夹
        copy: {
            html: {
                expand: true,
                cwd: src,
                src: ['**'],
                dest: dest
            }
        },

        //css文件压缩
        cssmin:{
            dev:{
                files: [{
                    expand: true,
                    cwd: 'src/css/',
                    src: ['*.css', '!*.min.css'],
                    dest: 'dest/css/',
                    ext: '.min.css'
                }]
            }
        },

        //压缩js文件
        uglify:{
            options: {
                banner: ''
            },
            dev:{
                files:[{
                    expand:true,
                    cwd:'src/js/',
                    src:'*.js',
                    dest:'dest/js/',
                    ext: '.min.js'
                }]
            }
        },

        // 文件合并 todo
        concat: {
            options: {
                separator: '\n'
            }
        }

    });

    grunt.registerTask('default', ['clean', 'copy', "cssmin", "uglify","concat"]);


};