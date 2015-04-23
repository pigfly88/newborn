# 代码规范 #
- **命名方式要言简意赅，在能够表达意思的基础上保持短小精悍**  
>要表示一个水平速度  
bad: horizon_speed  
good: xspeed
//wn:必须改..   

- **变量名、函数名、文件名一律采用小写字母+下划线的形式**  
>var一个变量  
bad: var xSpeed = 600;
1.这种方式多了一次大小写键盘切换的开销
2.JS是区分大小写的，当发生问题的时候这种情况大小写不容易察觉。  
good: var xspeed = 600;
//wn:传说中驼峰式命名啊..  

>定义一个函数  
bad: function drawRects()
理由同第二点  
good: function draw_rect();
采用这种下划线的方式很容易并且更形象地将单词与单词之间区分开来，更容易抒写和阅读
// wn:下划线不错..以后统一用这个方式命名吧！  

>文件名  
bad: dice dancing.html
字符和字符之间一定要连起来，不然移植到别的系统中可能会有意想不到的后果  
good: dice_dancing.html
// wn:必须改..  
bad: cannonball-1.html
统一采用_(下划线)的方式  
good: cannonball_1.html(而且这个cannonball是什么？就叫ball不好吗？)
// wn:炮弹的英文..  
bad: Jumping.html  
good: jumping.html
// wn:必须改..

>命名的艺术  
bad: var imga = new Image();  
good: var img = new Image();  
任何时候都不要忘了：言简意赅，多个a是干什么？！
// wn:书上看的..  
bad: function drawAnImage()  
good: function draw_img()  
动作draw（画）和名词img（画）一眼就区分开来，形象而且易于书写和阅读
// wn:改..  
bad: function MyRectangle()  
good: function rect()  
这个方法是新建一个矩形的对象,my在这里不是重点，任何时候都不要忘了：言简意赅
// wn:学习了..