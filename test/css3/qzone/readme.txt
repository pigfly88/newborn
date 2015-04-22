一、grunt使用

  1、安装node环境
  2、安装npm环境
  3、安装grunt-cli （npm install -g grunt-cli）
  4、执行grunt

二、Grunt常用插件：

  1).grunt-contrib-uglify：压缩js代码
  2).grunt-contrib-concat：合并js文件
  3).grunt-contrib-qunit：单元测试
  4).grunt-contrib-jshint：js代码检查
  5).grunt-contrib-watch：文件监控

三、文件说明：
  package.json    ：项目包，定义grunt依赖
  Gruntfile.js    ：grunt打包脚本
  npm-debug.log   ：npm命令日志
  |---- node_modules： grunt使用的模块
  |---- src   本地开发源文件夹
  |---- dest  打包压缩、合并后的文件夹
