/**
 * 分析jquery-2.0.0.js
 * 知识点：
 * 1、匿名函数
 * 2、匿名函数对外提供接口
 * 3、静态方法，实例方法的关系
 * 4、Sizzle的使用
 * 5、回调函数
 * 6、异步调用
 * 7、数据缓存
 * 8、队列管理
 */
(function(){
    // （21行 ~ 94行）  定义了一些变量和函数
    jQuery = function(){};

    // （96行 ~ 283 行）,给jQ对象，添加一些方法和属性
    jQuery.fn = jQuery.prototype = {/**/}
    jQuery.fn.init.prototype = jQuery.fn;

    // （285行 ~ 347行） extend: JQ的继承方法
    jQuery.extend = jQuery.fn.extend = function() {/**/}

    // （349行 ~ 817行） jQuery.extend() 扩展一些工具方法
    jQuery.extend({/**/})

    // (877行 ~ 2806行) Sizzle： 复杂选择器的实现，例如（'ul li + p input.class'）
    (function( window, undefined ) {/**/})
    function createOptions( options ) {/**/}

    // （2830行 ~ 2992行）Callbacks：回调对象 （对函数的统一管理）
    jQuery.Callbacks = function( options ) {/**/}

    // （2993行 ~ 3133行） Deferred: 延迟对象：（对异步的统一管理）
    jQuery.extend({/**/})

    // (3134行 ~ 3245行)  support: 功能检测
    jQuery.support = (function( support ) {/**/})

    // (3258 行~ 3597 行) data() : 数据缓存

    //（3598行 ~ 3743行 ） queue() : 队列管理
    jQuery.fn.extend({/**/})

    // (3749 ~ 4241) attr() prop() val() addClass()等： 对元素属性的操作

    //（4242 ~ 5064） on() trigger() 事件

    // （8752 行） 对外提供了jQuery 和 $ 接口
    window.jQuery = window.$ = jQuery;
})();