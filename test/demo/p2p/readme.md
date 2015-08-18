##H5页面制作步骤

- 1、分析设计图
  - 尺寸：1125*3000，分辨率：144px
  - 边距：左右40px
  - 浮层：上下各一个
  - 效果：1个tab切换，1个banner
  - 图片:logo,Icon*5，小图片*2，banner图
  - 模块：
		- header
			- logo
			- 免费注册，登录
		- body
			- 企业
				- banner
				- 图文两列模块：企业业务
				- 图文两列模块：联盟合作
				- 图文一列模块：移动橙e
				- 文字模块：今日头条
				- 文字模块：加入我们
			- 个人
		- footer
			- 常见问题，网页版
		- 浮动底部

- 2、加标签，填充文字
  - 外部模块标签，<header></header><footer></footer> 
  - 超链接标签<a href=""></a>
  - img等其他标签
- 3、制作svg图标
  - 将ps中的矢量图片导入至ai，生成svg格式
  - 再通过iconfont.cn上
- 4、完成头部，底部，底部浮动部分
  - 用弹性布局(box-flex)代替浮动（float）完成1行2列
  - 用rgba属性代替opacity
- 5、完成公用模块的标题
- 6、完成文字列表