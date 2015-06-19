<?php
class img {
	/**
	 * 压缩图片
	 *
	 * @param string $img 图片路径
	 * @param int $width 宽度
	 * @param int $height 高度
	 * @param int $quality 范围从0(最差质量,文件更小)到100(最佳质量,文件最大), 默认75 
	 * 
	 */
	public static function thumb($img, $width=100, $height=100, $quality=75){
		//读取已经上传图片
		$image = imagecreatefromjpeg($img);
		$size = getimagesize($img);
		$newwidth = $w=$size[0];
		$newheight = $h=$size[1];

		if($w > $width){
			$newwidth = $width;
		}
		if($h > $height){
			$newheight = $height;
		}
		if($newheight!=$h || $newwidth!=$w){
			$newimage = imagecreatetruecolor($width, $height);
			imagecopyresized($newimage, $image, 0, 0, 0, 0, $newwidth, $newheight, $w, $h);
		}else{
			$newimage = $image;
		}
		$filename = $img;
		return imagejpeg($newimage, $filename, $quality); 
	}
	
	
	
	
	
}