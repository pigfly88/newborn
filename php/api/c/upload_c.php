<?php
class upload_c extends base_c {
	public function pic($name){
		$folder = 'data/pics/';
		$upload = oo::base('file')->upload($name, $folder);

		//压缩图片尺寸, 压缩质量减小图片的容量
		if(is_array($upload['success'])){
			foreach ($upload['success'] as $v){
				oo::helper('img')->thumb($folder.$v, 300, 450);
			}
		}
		
		return $upload;
	}
	
}