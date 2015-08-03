>#2014.5.3#
>>上线
>
> #2015.5.5 update#
>>1. 使用transform: translate(-50%,-50%);进行水平垂直居中
>>2. 减少无用的div嵌套
>>3. 使用伪类来清除浮动:after{content: " ";display: table;}
>>4. 去除fl，fr类，不使用浮动，用display:inline-block
>>5. 可以用img缩放代替边距，transform: scale(.95);删除外侧div,!css3属性一定要加浏览器标识
>>6. 弹出阴影层不用js获取高度，用四边绝对定位 top: 0; left: 0; right: 0;bottom: 0;
*/