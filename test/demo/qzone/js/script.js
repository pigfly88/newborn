/**
 * Created by wswangning on 2015/3/24.
 */


$(function () {



    //进度条
    var current = 0;

    function increment() {
        current++;
        $('.loading-num').html(current + '%');
        $(".load-progress").css("width", current + "%");
        if (current == 100) {
            $(".loading").html("");
            $(".btn-go").removeClass("hide");
        }
    }

    setInterval(increment, 100);

    // 点击按钮，第一屏消失
    $('.btn-go').click(function (e) {
        e.preventDefault();
        $('.people').css({
            transition: 'all 0.3s ease-in',
            '-webkit-transition': 'all 0.3s ease-in',
            transform: 'translateY(-400px)',
            '-webkit-transform': 'translateY(-400px)'
        });
        $(this).remove();
        $('.star').remove();
        $('.bg-ball').remove();


    });

    //todo 控制音乐播放

});