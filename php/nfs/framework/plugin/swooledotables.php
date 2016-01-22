<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelDoTables
 *
 * @author JsonChen
 */
class ModelSwooleDoTables {
	//1,2,3,1000,1002
	public function doWritetbl($type) {
		switch ($type) {
			case 1:
				foreach (array(0, 1, 2) as $ttype) {
					($ttype == 2) && oo::tables()->writeCache($ttype);
					oo::tables()->setTablesByType($ttype);
				}
				//更新快速开始缓存
				if(oo::$config['openNewFastTable']){
					oo::tablesNew()->buildFastTableListCache();
				}
				break;
			case 2:
				foreach (array(3, 6) as $ttype) {
					$aRun = oo::tables()->writeCache($ttype);
					oo::tables()->setTablesByType($ttype);
				}
				if(oo::$config['beautyroom']){//美女房
					foreach (array(41) as $ttype){
						$aRun = oo::tables()->writeCache($ttype);
						oo::tables()->setTablesByType( $ttype);
					}
				}
				if(oo::$config['nofriendsRoomFilterTables']){//美女房
					foreach (array(43) as $ttype){
						$aRun = oo::tables()->writeCache($ttype);
						oo::tables()->setTablesByType( $ttype);
					}
				}
				
				//更新快速开始缓存
				if(oo::$config['openNewFastTable']){
					oo::tablesNew()->buildFastTableListCache();
				}
				$aRun = oo::tables()->writeCache(4);
				if(oo::$config['sngPlayNum']){//优化在线人数处理
					oo::wof()->setRun(4);
				}else{
					//oo::sngtables()->setMatchZoneTablesByType(4);
				}
				oo::sngtables()->setMatchZoneTablesByType(4);
				break;
			case 3:
				if(oo::$config['openWof']){
					$aRun = oo::tables()->writeCache(40);
					oo::wof()->setRun(40);
				}
				if (oo::$config['act365_zgs']) {
					foreach (array(15) as $ttype) {
						$aRun = oo::tables()->writeCache($ttype);
						oo::tables()->setTablesByType($ttype);
					}
				}
				if (oo::$config['act736_table'] && oo::act(736)->showTable()) {
					$_ttype = 15;
					$aRun = oo::tables()->writeCache($_ttype);
					oo::tables()->setTablesByType($_ttype);
				}
				if (oo::$config['act743_table'] && (oo::act(743)->showTable() || oo::act(743)->cfg['testMids'])) {
					$_ttype = 18;
					$aRun = oo::tables()->writeCache($_ttype);
					oo::tables()->setTablesByType($_ttype);
				}
				if (in_array(oo::$config['sid'], array(13))) {
					$_ttype = 19; //必下桌
					$aRun = oo::tables()->writeCache($_ttype);
					oo::tables()->setTablesByType($_ttype);
				}
				if (oo::$config['prechipRoom']) {
					$_ttype = 19; //必下桌
					$aRun = oo::tables()->writeCache($_ttype);
					oo::tables()->setTablesByType($_ttype);
				}
				if (oo::$config['openBonus']) {
					foreach (array(20) as $ttype) {
						$aRun = oo::tables()->writeCache($ttype);
						oo::tables()->setTablesByType($ttype);
					}
				}
				if (oo::$config['openSpeedTbl']) {//急速玩法
					foreach (array(17) as $ttype) {
						oo::tables()->setTablesByType($ttype);
					}
				}
				if (oo::$config['openshoot']) {
					foreach (array(71, 72, 73) as $ttype) {
						oo::sngtables()->setMatchZoneTablesByType($ttype);
					}
				}
				if (oo::$config['openTour']) {//巡回赛新加tab
					foreach (array(34) as $ttype) {
						oo::tables()->setTablesByType($ttype);
					}
				}
				if (oo::$config['act813']) {//多人坐满即玩
					foreach (array(39) as $ttype) {
						oo::tables()->setTablesByType($ttype);
					}
				}
				break;
			case 1004:	//快速找位
				if(oo::$config['fastseat']){//每跑一次		
					oo::fastseat()->makeTable();					
				}
				break;
			default://5
				break;
		}
	}
}
