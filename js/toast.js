$(document).ready(function() {
   //显示一个消息，会在2秒钟后自动消失
   //toast参数可选，三种是文字、时间、以及class的类
    $.toast = function(msg, duration, extraclass) {
    	if(!duration){
    		duration = 2000;
    	}
        var $toast = $('<div class="modal toast ' + (extraclass || '') + '">' + msg + 	   '</div>').appendTo(document.body);
        var aa=$toast.offset();
        var left=-(aa.width)/2;
 //样式会设置toast的padding，因此要动态获取宽度，然后margin回去 才能居中
       $toast.css("margin-left",left);
        setTimeout(function(){
            $toast.remove();
        },duration);
    };
    /*添加行数据*/
    // $.toast("没有更多了");
    //加载器
    $.showPreloader=function(){
        var $showPreloader= $('<div class="modal2 loader">' + '<img src="{{StaticHelper.getStaDomain()}}images/showLoading.gif" class="u_ic_imgLoading">'+'</div>').appendTo(document.body);
        $showPreloader.css("display","block");
    }
    $.hidePreloader=function(){
        $("body").find(".loader").css("display","none").remove();
    }
});