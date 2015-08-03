$(function () {
    var current = 0;
    var isPlay = true; //默认播放
    //进度条
    function increment() {
        current++;
        $('.j-loading-text').html(current + '%');
        if (current == 100) {

            $(".loading").html("");
            $('.wp-inner').fullpage(
                //fullpage回调
//                {
//                afterChange: function (obj) {
//                    if (obj.next == 2) {
//                        $("#test").tap(function () {
//                         //   e.preventDefault();
//                           window.location.href = 'index.html';
//                        });
//                    }
//
//                }
//            }
            );
            $(".page1").addClass("cur");
        }
    }

    setInterval(increment, 10);

    // 音乐
    $(".music-anim").click(function (e) {
        e.preventDefault();
        if (isPlay) {
            isPlay = false;
            $(".music-anim").removeClass("active");
            $(".music-tip").css("opacity", 1);
            document.getElementById('music').pause();
            return false;
        } else {
            isPlay = true;
            $(".music-anim").addClass("active");
            $(".music-tip").css("opacity", 0);
            document.getElementById('music').play();
            return false;

        }
    });

    $(".go-page").tap(function () {
        window.location.href = 'index.html';
    });

});