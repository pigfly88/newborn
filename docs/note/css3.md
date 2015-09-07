# 大纲 #
- 1、css3介绍
- 2、边框
- 3、颜色
- 4、文字
- 5、背景
- 6、选择器
- 7、变形与动画
- 8、布局
- 9、响应式
- 10、用户界面

### 一、css3介绍 
	1、兼容性
		IE9以上、chrome、safari、firefox、opera
	2、前缀
		chrome&safari: -webkit
		firefox: -moz
		ie: -ms
		opera: -o
### 二、边框
	1、border-radius(圆角)
		一个值(四边)
		四个值（top,right,bottom,left）
	2、box-shadow（阴影）
		外阴影
			box-shadow: x y [模糊半径] [扩展半径] [颜色]
		内阴影
			box-shadow: x y [模糊半径] [扩展半径] [颜色] inset
		多个阴影
			逗号分隔，上右下左
	3、border-images（边框背景）
		border-images:url(border.png) 70 round（平铺）/repeat(重复）/stretch(拉伸)
### 三、颜色
	1、RGBA（半透明）
		color/background-color:rgba(R,G,B,A)
	2、linear-gradient（渐变）
		linear-gradient(to 方向,#start,#end)
		方向（to top,to right,to bottom,to left,to top left,to top right）
### 四、字体
	1、超出省略号
		overflow:hidden;
		white-space:nowrap;
		text-overflow:ellipsis;
	2、文本换行
		word-wrap:normal | break-word ?????
	3、@font-face(嵌入字体)
		定义：
		@font-face{
			font-family:字体名称
			src:字体文件路径
		}
		使用：
		font-family："字体名称"
	4、text-shadow(文字阴影)
		text-shadow:x,y,blur,color
### 五、背景
	


		
	
	



