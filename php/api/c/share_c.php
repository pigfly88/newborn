<?php
class share_c extends base_c {
	
	public function _init(){
		db::driver('pdo', 1);
	}
	
	
	public function index(){
		/*
		$r = $this->m->where('id=1')->get();
		p($r);
		$r = $this->m->table('tbl_article')->fields('title')->where('id=1')->get();
		p($r);
		
		$r = $this->m->table('tbl_article')->where('id=1')->update(array('title'=>'newborn'));
		p($r);
		
		$r = $this->m->table('tbl_article')->where('id=1')->delete();
		p($r);
		
		$r = $this->m->table('tbl_article')->insert(array('title'=>'newborn', 'content'=>'h5'));
		p($r);
		
		echo $this->m->last_sql;exit;
		*/
		$list = $this->m->getall();
		$this->display($list);
	}
	
	/**
	 * resful
	 */
	public function _post(){
		$upload_res = oo::c('upload')->pic('images');
		$upload_status = $upload_res['ok'] ? 1 : 0;

		if(is_array($upload_res['success'])){
			$data = array();
			$data['comment'] = $this->req('comment');
			$data['pics'] = $upload_res['success'];
			$res = $this->m->insert($data);
		}
		$res = $upload_status && $res ? 1 : 0;
		$this->response($res);
	}
	
	/**
	 * resful
	 */
	public function _get(){
		if($id = $this->req('id')) {
			$res = $this->m->findOne(array('_id'=>$id));
		}else{
			$res = $this->m->find();
		}
		
		$res['pic_root'] = '/data/pics/';//图片路径前缀
		$this->response($res);
	}
    
    public function update(){
    	$id = $this->req('id', null, 'fail');
    	$data['comment'] = $this->req('comment', null, 'fail'); 	
    	$res = $this->m->update(array('_id'=>$id), $data);
		$this->response($res);
    }
    
    public function delete(){
    	$id = $this->req('id', null, array(array($this,'fail')));
    	$res = $this->m->delete($id);
		$this->response($res);
    }

}


